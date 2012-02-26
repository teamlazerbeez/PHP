<?php
/*
 * Genius Open Source Libraries Collection
 * Copyright 2010 Team Lazer Beez (http://teamlazerbeez.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
// The PHP Process Control Extensions require ticks to be declared
// http://php.net/manual/en/function.pcntl-signal.php
declare(ticks = 1);

/**
 * A PHP class for parent/child daemon workers.
 *
 * @author Drew Stephens <drew@dinomite.net>
 *
 * Implement doWorkChild() something like this:
 *     protected function doWorkChildImpl() {
 *         global $run;
 *         while ($run) {
 *             gosUtility_parallel::$logger->debug("Child $childNum doing work.");
 *             usleep(3000000);
 *         }
 *     }
 *
 * Feel free to override the logger in your own constructor:
 *     public function __construct($maxWorkers) {
 *         parent::__construct($maxWorkers);
 *
 *         // Redefine the logger
 *         gosUtility_parallel::$logger = Log5PHP_Manager::getLogger('gosParallel.Par');
 *     }
 *
 * Then, run it:
 *     // Start a daemon with 7 workers
 *     $myDaemon = new myDaemonClass(7);
 *     $myDaemon->go();
 *
 * See also:
 * http://stackoverflow.com/questions/752214/php-daemon-worker-environment/752255#752255
 */
abstract class gosUtility_Parallel {
    /**
     * The maximum number of workers to run
     */
    protected $maxWorkers;

    /**
     * If this is a worker, the ID for it.
     */
    protected $workerID;

    /**
     * Keep a logger in our namespace that can be accessed by the signal handler
     */
    public static $logger;

    /**
     * Create a new parallel work context:
     *  - Register signal handlers
     *  - Setup logging
     */
    public function __construct($maxWorkers) {
        $this->maxWorkers = $maxWorkers;

        // Make this long running
        set_time_limit(0);
        ini_set('memory_limit',-1); // no memory limit

        // Global flags for signal handling
        global $run;
        $run = true;
        global $reload;
        $reload = false;

        // Register signal handler
        pcntl_signal(SIGINT, 'gosUtility_Parallel::signalHandler');
        pcntl_signal(SIGTERM, 'gosUtility_Parallel::signalHandler');
        pcntl_signal(SIGHUP, 'gosUtility_Parallel::signalHandler');

        gosUtility_Parallel::$logger = Log5PHP_Manager::getLogger('gosParallel');
    }

    /**
     * Start doing parallel work.
     */
    public function go() {
        $this->startAllWorkers();
        $this->doWorkParent();
    }

    /**
     * Watch for relaod and keep an eye on workers
     */
    private function doWorkParent() {
        Log5PHP_MDC::put('Generation', 'parent');

        global $run;
        global $reload;
        global $workers;

        $this->parentSetup();

        while ($run && count($workers) > 0) {
            if ($reload) {
                gosUtility_parallel::$logger->info("Parent saw reload, restarting workers");

                $reload = false;
                $this->killAllWorkers();
                $this->startAllWorkers();
            } else {
                $this->checkWorkers();
            }

            // Sleep 4 seconds
            usleep(4000000);
        }

        $this->parentCleanup();
        $this->killAllWorkers();
        pcntl_wait($status);
    }

    /**
     * Setup stuff for the parent to do; executed at the beginning of doWorkParent()
     */
    protected function parentSetup() {
    }

    /**
     * Cleanup stuff for the parent to do; executed at the end of doWorkParent()
     */
    protected function parentCleanup() {
    }

    /**
     * Setup the logging context and run the doWorkChildImpl()
     */
    private function doWorkChild() {
        Log5PHP_MDC::put('Generation', 'child');
        $this->doWorkChildImpl();
        $this->childCleanup();
        exit();
    }

    /**
     * Cleanup stuff for the parent to do; executed after doWorkChildImpl() returns
     */
    protected function childCleanup() {
    }

    /**
     * Infinite loop of child work
     */
    abstract protected function doWorkChildImpl();

    /**
     * Start up a worker process, keeping the PID in the parent.
     *
     * @param int $workerID The worker ID for this worker
     */
    private function startWorker($workerID) {
        $pid = pcntl_fork();

        if ($pid) { // Parent
            $this->workerID = -1;

            global $workers;
            $workers[] = $pid;

            gosUtility_parallel::$logger->info("Started worker $workerID (pid $pid)");
            return $pid;
        } else { // Child
            if (posix_setsid() == -1)
                gosUtility_parallel::$logger->fatal("Forked process could not detach from terminal");

            // Close parent's streams
            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);

            $this->workerID = $workerID;
            $this->doWorkChild();
        }
    }

    /**
     * Spin up $maxWorkers.
     */
    private function startAllWorkers() {
        global $workers;
        $workers = array();

        for ($i = 0; $i < $this->maxWorkers; $i++) {
            $this->startWorker($i);

            // Start workers judiciously
            usleep(1000000);
        }
    }

    /**
     * Kill all worker processes.
     */
    private function killAllWorkers() {
        global $workers;

        // Send each worker SIGTERM
        foreach ($workers as $workerID => $pid) {
            gosUtility_parallel::$logger->info("Killing worker $workerID (pid $pid)");
            posix_kill($pid, SIGTERM);
        }

        // Wait for all of them to die
        foreach ($workers as $workerID => $pid) {
            gosUtility_parallel::$logger->info("Waiting on worker $workerID (pid $pid)");
            pcntl_waitpid($pid, $status);
        }

        unset($workers);
    }

    /**
     * Restart any workers that died unfortunate deaths.
     */
    private function checkWorkers() {
        global $workers;
        $living = array();

        // Find any dead workers
        foreach ($workers as $workerID => $pid) {
            gosUtility_parallel::$logger->debug("Checking worker $workerID (pid $pid)");

            // Check if this worker still exists as a process
            if (pcntl_waitpid($pid, $status, WNOHANG|WUNTRACED) === $pid) {
                // If the worker exited normally, stop tracking it
                if (pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0) {
                    gosUtility_parallel::$logger->info("Worker $workerID (pid $pid) exited normally");
                    unset($workers[$workerID]);
                }
            }

            // If it has a session ID, then it's still living
            if (posix_getsid($pid))
                $living[] = $pid;
        }

        // Start new workers to replace dead ones
        $dead = array_diff($workers, $living);
        foreach ($dead as $workerID => $deadPID) {
            gosUtility_parallel::$logger->warn("Worker $workerID (pid $deadPID) died.  Conscripting replacement...");

            unset($workers[$workerID]);
            $this->startWorker($workerID);
        }
    }

    /**
     * Shutdown on SIGINT or SIGTERM, restart all workers on SIGHUP.
     */
    public static function signalHandler($signal) {
        switch($signal) {
        case SIGINT:
            $sig = 'SIGINT';
        case SIGTERM:
            $sig = isset($sig) ? $sig : 'SIGTERM';
            gosUtility_Parallel::$logger->info("Caught $sig, setting \$run = false");
            global $run;
            $run = false;
            break;

        case SIGHUP:
            gosUtility_Parallel::$logger->info("Caught SIGHUP, setting \$reload = true");
            global $reload;
            $reload = true;
            break;

        default:
            gosUtility_Parallel::$logger->warn("Caught signal $signal, don't know what to do!");
            return;
        }

        // Pass handled signals to workers
        global $workers;
        foreach ($workers as $workerID => $pid) {
            gosUtility_Parallel::$logger->debug("Passing signal to worker $workerID (pid $pid)");
            posix_kill($pid, $signal);
        }
    }
}
