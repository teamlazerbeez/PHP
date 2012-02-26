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

/**
 * Log5PHP_Appender_Echo uses {@link PHP_MANUAL#echo echo} function to output events. 
 * 
 * <p>This appender requires a layout.</p>  
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_Echo extends Log5PHP_Appender_Base
{

    /**
     */
    protected $requiresLayout = true;

    /**
     * @var boolean used internally to mark first append 
     */
    private $firstAppend = true;

    function activateOptions()
    {
        return;
    }

    function close()
    {
        if (!$this->firstAppend)
            echo $this->layout->getFooter();
    }

    protected function append(Log5PHP_LogEvent $event)
    {
        Log5PHP_InternalLog :: debug("Log5PHP_Appender_Echo::append()");

        if ($this->layout !== null)
        {
            if ($this->firstAppend)
            {
                echo $this->layout->getHeader();
                $this->firstAppend = false;
            }
            echo $this->layout->format($event);
        }
    }
}
