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
 * Use the Log5PHP_Manager to get Logger instances.
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @see Logger
 * @todo create a configurator selector  
 */
class Log5PHP_Manager {

    /**
     * check if a given logger exists.
     * 
     * @param string $name logger name 
     * @return boolean
     */
    static function exists($name)
    {
        $repository = Log5PHP_Manager::getLoggerRepository();
        return $repository->exists($name);
    }

    /**
     * Returns an array this whole Logger instances.
     * 
     * @see Logger
     * @return array
     */
    static function getCurrentLoggers()
    {
        $repository = Log5PHP_Manager::getLoggerRepository();
        return $repository->getCurrentLoggers();
    }
    
    /**
     * Returns the root logger.
     * 
     * @return object
     * @see Log5PHP_Root
     */
    static function getRootLogger()
    {
        $repository = Log5PHP_Manager::getLoggerRepository();
        return $repository->getRootLogger();
    }
    
    /**
     * Returns the specified Logger.
     * 
     * @param string $name logger name
     * @return Logger
     */
    static function getLogger($name)
    {
        $repository = Log5PHP_Manager::getLoggerRepository();
        return $repository->getLogger($name);
    }
    
    /**
     * Returns the Log5PHP_LoggerRepository.
     * 
     * @return Log5PHP_LoggerRepository
     */
    static function getLoggerRepository()
    {
        return Log5PHP_LoggerRepository::getInstance();    
    }
    

    /**
     * Destroy loggers object tree.
     * 
     * @return boolean 
     */
    static function resetConfiguration()
    {
        Log5PHP_Factory_Cached::resetInstanceCache();
        $repository = Log5PHP_Manager::getLoggerRepository();    
        return $repository->resetConfiguration();    
    }
    
    /**
     * Safely close all appenders.
     */
    static function shutdown()
    {
        $repository = Log5PHP_Manager::getLoggerRepository();    
        return $repository->shutdown();    
    }
}

// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------

if (!defined('LOG5PHP_DEFAULT_INIT_OVERRIDE')) {
    if (isset($_ENV['log5php_defaultInitOverride'])) {
        /**
         * @ignore
         */
        define('LOG5PHP_DEFAULT_INIT_OVERRIDE', 
            Log5PHP_Utility_OptionConverter::toBoolean($_ENV['log5php_defaultInitOverride'], false)
        );
    } elseif (isset($GLOBALS['log5php.defaultInitOverride'])) {
        /**
         * @ignore
         */
        define('LOG5PHP_DEFAULT_INIT_OVERRIDE', 
            Log5PHP_Utility_OptionConverter::toBoolean($GLOBALS['log5php.defaultInitOverride'], false)
        );
    } else {
        /**
         * Controls init execution
         *
         * With this constant users can skip the default init procedure that is
         * called when this file is included.
         *
         * <p>If it is not user defined, log5php tries to autoconfigure using (in order):</p>
         *
         * - the <code>$_ENV['log5php_defaultInitOverride']</code> variable.
         * - the <code>$GLOBALS['log5php.defaultInitOverride']
         * </code> global variable. - defaults to <i>false</i>
         *
         * @var boolean
         */
        define('LOG5PHP_DEFAULT_INIT_OVERRIDE', false);
    }
}

if (!defined('LOG5PHP_CONFIGURATION')) {
    if (isset($_ENV['log5php_configuration'])) {
        /**
         * @ignore
         */
        define('LOG5PHP_CONFIGURATION', trim($_ENV['log5php_configuration']));
    } else {
        /**
         * Configuration file.
         *
         * <p>This constant tells configurator classes where the configuration
         * file is located.</p>
         * <p>If not set by user, log5php tries to set it automatically using 
         * (in order):</p>
         *
         * - the <code>$_ENV['log5php.configuration']</code> enviroment variable.
         * - defaults to 'log5php.xml'.
         *
         * @var string
         */
        define('LOG5PHP_CONFIGURATION', 'log5php.xml');
    }
}

if (!defined('LOG5PHP_CONFIGURATOR_CLASS')) {
    if ( strtolower(substr( LOG5PHP_CONFIGURATION, -4 )) == '.xml') 
    { 
        /**
         * @ignore
         */
        define('LOG5PHP_CONFIGURATOR_CLASS', 'Log5PHP_Configurator_XML');
    }
    else
    {
        /**
         * Holds the configurator class name.
         *
         * <p>This constant is set with the name of the configurator class that
         * init procedure will use.</p>
         * <p>If not set by user, log5php tries to set it automatically.</p>
         * <p>If {@link LOG5PHP_CONFIGURATION} has '.xml' extension set the 
         * constants to '{@link LOG5PHP_DIR}/xml/{@link Log5PHP_Configurator_XML}'.</p>
         * <p>Otherwise set the constants to 
         * '{@link LOG5PHP_DIR}/{@link Log5PHP_Configurator_Basic}'.</p>
         *
         * @var string
         */
        define('LOG5PHP_CONFIGURATOR_CLASS', 'Log5PHP_Configurator_Basic');
    }
}

if (!LOG5PHP_DEFAULT_INIT_OVERRIDE) {
    if (!Log5PHP_ManagerDefaultInit())
        Log5PHP_InternalLog::warn("LOG5PHP main() Default Init failed.");
}

/**
 * Default init procedure.
 *
 * <p>This procedure tries to configure the {@link Log5PHP_LoggerRepository} using the
 * configurator class defined via {@link LOG5PHP_CONFIGURATOR_CLASS} that tries
 * to load the configurator file defined in {@link LOG5PHP_CONFIGURATION}.
 * If something goes wrong a warn is raised.</p>
 * <p>Users can skip this procedure using {@link LOG5PHP_DEFAULT_INIT_OVERRIDE}
 * constant.</p> 
 *
 * @return boolean
 */
function Log5PHP_ManagerDefaultInit()
{
    if (class_exists(LOG5PHP_CONFIGURATOR_CLASS)) 
    {
        return call_user_func(array(LOG5PHP_CONFIGURATOR_CLASS, 'configure'), LOG5PHP_CONFIGURATION);         
    }
    else 
    {
        Log5PHP_InternalLog::warn("Log5PHP_ManagerDefaultInit() Configurator '{$configuratorClass}' doesnt exists");
        return false;
    }
}

