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
 * Log events using php {@link PHP_MANUAL#trigger_error} function and a {@link Log5PHP_LayoutTTCC} default layout.
 *
 * <p>Levels are mapped as follows:</p>
 * - <b>level &lt; WARN</b> mapped to E_USER_NOTICE
 * - <b>WARN &lt;= level &lt; ERROR</b> mapped to E_USER_WARNING
 * - <b>level &gt;= ERROR</b> mapped to E_USER_ERROR  
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */ 
class Log5PHP_Appender_Php extends Log5PHP_Appender_Base {

    /**
     */
    protected $requiresLayout = false;
    
    function activateOptions()
    {
        $this->layout = Log5PHP_Factory_Layout :: getNewLayout('Log5PHP_Layout_TTCC');
    }

    protected function append(Log5PHP_LogEvent $event)
    {
        if ($this->layout !== null) {
            Log5PHP_InternalLog::debug("Log5PHP_Appender_Php::append()");
            $level = $event->getLevel();
            if ($level->isGreaterOrEqual(Log5PHP_Level::getLevelError())) {
                trigger_error($this->layout->format($event), E_USER_ERROR);
            } elseif ($level->isGreaterOrEqual(Log5PHP_Level::getLevelWarn())) {
                trigger_error($this->layout->format($event), E_USER_WARNING);
            } else {
                trigger_error($this->layout->format($event), E_USER_NOTICE);
            }
        }
    }
}
