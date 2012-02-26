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
 */

/**
 * @ignore 
 */

/**
 * Helper class for internal logging
 *
 * <p>It uses php {@link PHP_MANUAL#trigger_error trigger_error()} function
 * to output messages.</p>
 * <p>You need to recode methods to output messages in a different way.</p> 
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 */
class Log5PHP_InternalLog {

    /**
     * Log if debug is enabled.
     *
     * Log using php {@link PHP_MANUAL#trigger_error trigger_error()} function 
     * with E_USER_NOTICE level by default.
     *
     * @param string $message log message
     * @param integer $errLevel level to log
     */
    static function log($message, $errLevel = E_USER_NOTICE)
    {
        if (Log5PHP_InternalLog::internalDebugging())
            trigger_error($message, $errLevel);
    }
    
    static function internalDebugging($value = null)
    {
        static $debug = false;

        if (is_bool($value))
            $debug = $value;
        return $debug;
    }
    
    /**
     * Report a debug message. 
     *
     * @param string $message log message
     * @since 0.3
     */
    static function debug($message)
    {
        Log5PHP_InternalLog::log($message, E_USER_NOTICE);
    }
    
    /**
     * Report an error message. 
     *
     * @param string $message log message
     * @since 0.3
     */
    static function error($message)
    {
        trigger_error($message, E_USER_ERROR);
    }
    
    /**
     * Report a warning message. 
     *
     * @param string $message log message
     * @since 0.3
     */
    static function warn($message)
    {
        trigger_error($message, E_USER_WARNING);
    }

}
