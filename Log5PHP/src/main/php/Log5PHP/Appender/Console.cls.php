<?php

/**
 * log5php is a PHP port of the log4j java logging package.
 * 
 * <p>This framework is based on log4j (see {@link http://jakarta.apache.org/log4j log4j} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by log4j team 
 * (Ceki G�lc� as log4j project founder and 
 * {@link http://jakarta.apache.org/log4j/docs/contributors.html contributors}).</p>
 *
 * <p>PHP port, extensions and modifications by VxR. All rights reserved.<br>
 * For more information, please see {@link http://www.vxr.it/log4php/}.</p>
 *
 * <p>This software is published under the terms of the LGPL License
 * a copy of which has been included with this distribution in the LICENSE file.</p>
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */

/**
 * @ignore 
 */
define('LOG5PHP_LOGGER_APPENDER_CONSOLE_STDOUT', 'php://stdout');
define('LOG5PHP_LOGGER_APPENDER_CONSOLE_STDERR', 'php://stderr');

/**
 * ConsoleAppender appends log events to STDOUT or STDERR using a layout specified by the user. 
 * 
 * <p>Optional parameter is {@link $target}. The default target is Stdout.</p>
 * <p><b>Note</b>: Use this Appender with command-line php scripts. 
 * On web scripts this appender has no effects.</p>
 * <p>This appender requires a layout.</p>  
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_Console extends Log5PHP_Appender_Base
{

    /**
     * Can be 'php://stdout' or 'php://stderr'. But it's better to use keywords <b>STDOUT</b> and <b>STDERR</b> (case insensitive). 
     * Default is STDOUT
     * @var string    
     */
    private $target = LOG5PHP_LOGGER_APPENDER_CONSOLE_STDOUT;

    /**
     * @var boolean
     */
    protected $requiresLayout = true;

    /**
     * @var mixed the resource used to open stdout/stderr
     */
    private $fp = false;

    /**
     * Set console target.
     * @param mixed $value a constant or a string
     */
    function setTarget($value)
    {
        $v = trim($value);
        if ($v == LOG5PHP_LOGGER_APPENDER_CONSOLE_STDOUT or strtoupper($v) == 'STDOUT')
        {
            $this->target = LOG5PHP_LOGGER_APPENDER_CONSOLE_STDOUT;
        }
        elseif ($v == LOG5PHP_LOGGER_APPENDER_CONSOLE_STDERR or strtoupper($v) == 'STDERR')
        {
            $this->target = LOG5PHP_LOGGER_APPENDER_CONSOLE_STDERR;
        }
        else
        {
            // @todo throw exception here
            $this->target = LOG5PHP_LOGGER_APPENDER_CONSOLE_STDOUT;
            Log5PHP_InternalLog :: warn("Log5PHP_Appender_Console::setTarget() " .
            "Invalid target. Using '" . LOG5PHP_LOGGER_APPENDER_CONSOLE_STDOUT . "' by default.");
        }
    }

    function getTarget()
    {
        return $this->target;
    }

    function activateOptions()
    {
        Log5PHP_InternalLog :: debug("Log5PHP_Appender_Console::activateOptions()");

        $this->fp = fopen($this->getTarget(), 'w');

        if ($this->fp and $this->layout !== null)
        {
            fwrite($this->fp, $this->layout->getHeader());
        }

    }

    /**
     * @see Log5PHP_Appender_Appendable::close()
     */
    function close()
    {
        Log5PHP_InternalLog :: debug("Log5PHP_Appender_Console::close()");

        if ($this->fp and $this->layout !== null)
        {
            fwrite($this->fp, $this->layout->getFooter());
        }
        # we only want to close if the pointer is valid
        if ($this->fp)
        {
            fclose($this->fp);
        }
    }

    protected function append(Log5PHP_LogEvent $event)
    {
        if ($this->fp and $this->layout !== null)
        {
            Log5PHP_InternalLog :: debug("Log5PHP_Appender_Console::append()");

            fwrite($this->fp, $this->layout->format($event));
        }
            
    }
}
