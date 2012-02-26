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
 * Use this class to quickly configure the package.
 *
 * <p>For file based configuration see {@link Log5PHP_Configurator_Property}. 
 * <p>For XML based configuration see {@link Log5PHP_Configurator_XML}.
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @since 0.5
 */
class Log5PHP_Configurator_Basic extends Log5PHP_Configurator {

    /**
     * Add a {@link Log5PHP_Appender_Console} that uses 
     * the {@link Log5PHP_LayoutTTCC} to the root logger.
     * 
     * @param string $url not used here
     */
    static function configure($url = null)
    {
        $root = Log5PHP_Manager::getRootLogger();
        
        $appender = Log5PHP_Factory_Appender::createAppenderWithName('Log5PHP_Appender_Console', 'A1');
        $appender->activateOptions();
        $layout = Log5PHP_Factory_Layout::getNewLayout('Log5PHP_Layout_TTCC');
        $appender->setLayout($layout);

        $root->addAppender($appender);
        
        return true;
    }

    /**
     * Reset the default hierarchy to its defaut. 
     * It is equivalent to
     * <code>
     * Log5PHP_Manager::resetConfiguration();
     * </code>
     *
     * @see Log5PHP_LoggerRepository::resetConfiguration()
     */
    static function resetConfiguration()
    {
        Log5PHP_Manager::resetConfiguration();
    }
}
