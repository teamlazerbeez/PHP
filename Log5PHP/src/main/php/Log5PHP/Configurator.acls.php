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
 * @subpackage src_main_php_Log5PHP
 */

/**
 * @ignore 
 */

/**
 * Special level value signifying inherited behaviour. The current
 * value of this string constant is <b>inherited</b>. 
 * {@link LOG5PHP_LOGGER_CONFIGURATOR_NULL} is a synonym.  
 */
define('LOG5PHP_LOGGER_CONFIGURATOR_INHERITED', 'inherited');

/**
 * Special level signifying inherited behaviour, same as 
 * {@link LOG5PHP_LOGGER_CONFIGURATOR_INHERITED}. 
 * The current value of this string constant is <b>null</b>. 
 */
define('LOG5PHP_LOGGER_CONFIGURATOR_NULL',      'null');

/**
 * Implemented by classes capable of configuring log5php using a URL.
 *  
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP
 * @since 0.5
 */
abstract class Log5PHP_Configurator {

   /**
    * Interpret a resource pointed by a <var>url</var> and configure accordingly.
    *
    * The configuration is done relative to the <var>repository</var>
    * parameter.
    *
    * @param string $url The URL to parse
    * @param Log5PHP_LoggerRepository $repository The hierarchy to operation upon.
    */
    function doConfigure($url, Log5PHP_LoggerRepository $repository)
    {
        return;
    }
}
