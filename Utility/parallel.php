<?php
// Include the Genius config file
require_once dirname(dirname(__FILE__)) .'/Core/testConfig.inc.php';

class Par extends gosUtility_Parallel {
    public function __construct($maxWorkers) {
        parent::__construct($maxWorkers);

        // Redefine the logger
        gosUtility_Parallel::$logger = Log5PHP_Manager::getLogger('gosParallel.Par');
    }

    protected function doWorkChildImpl() {
        gosUtility_Parallel::$logger->debug("Child " . $this->workerID . " started");

        // Run until told not to
        global $run;
        while ($run) {
            gosUtility_Parallel::$logger->debug("Child " . $this->workerID . " doing work.");
            usleep(2000000);
            if ($this->workerID == 0 && rand(0,10) == 7) {
                gosUtility_Parallel::$logger->info("Child " . $this->workerID . " returning");
                return;
            }
        }
    }

    protected function parentCleanup() {
        gosUtility_Parallel::$logger->debug("Parent cleaning up");
    }

    protected function childCleanup() {
        gosUtility_Parallel::$logger->debug("Child " . $this->workerID . " cleaning up");
    }
}

// Make with the go
$par = new Par(2);
$par->go();
