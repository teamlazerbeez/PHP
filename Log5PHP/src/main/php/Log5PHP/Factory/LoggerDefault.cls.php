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
 * Creates instances of {@link Logger} with a given name.
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @since 0.5 
 */
class Log5PHP_Factory_LoggerDefault extends Log5PHP_Factory_LoggerBase {
    
    /**
     * @param string $name
     * @return Logger
     */
    function makeNewLoggerInstance($name)
    {
        return new Log5PHP_Logger($name);
    }
}

