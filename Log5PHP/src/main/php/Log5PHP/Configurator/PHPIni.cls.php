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
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX',      "log5php.category.");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX',        "log5php.logger.");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_FACTORY_PREFIX',       "log5php.factory");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ADDITIVITY_PREFIX',    "log5php.additivity.");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_CATEGORY_PREFIX', "log5php.rootCategory");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_LOGGER_PREFIX',   "log5php.rootLogger");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_APPENDER_PREFIX',      "log5php.appender.");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_RENDERER_PREFIX',      "log5php.renderer.");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_THRESHOLD_PREFIX',     "log5php.threshold");

/** 
 * Key for specifying the {@link Log5PHP_Factory}.  
 */
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_FACTORY_KEY',   "log5php.loggerFactory");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_DEBUG_KEY',     "log5php.debug");
define('LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_INTERNAL_ROOT_NAME',   "root");



/**
 * Allows the configuration of log5php from an external file.
 * 
 * See {@link doConfigure()} for the expected format.
 * 
 * <p>It is sometimes useful to see how log5php is reading configuration
 * files. You can enable log5php internal logging by defining the
 * <b>log5php.debug</b> variable.</p>
 *
 * <p>The <i>LoggerPropertyConfigurator</i> does not handle the
 * advanced configuration features supported by the {@link Log5PHP_Configurator_XML} 
 * such as support for {@link Log5PHP_Filter}, 
   custom {@link Log5PHP_ErrorHandlers}, nested appenders such as the 
   {@link Logger AsyncAppender}, 
 * etc.
 * 
 * <p>All option <i>values</i> admit variable substitution. The
 * syntax of variable substitution is similar to that of Unix
 * shells. The string between an opening <b>&quot;${&quot;</b> and
 * closing <b>&quot;}&quot;</b> is interpreted as a key. The value of
 * the substituted variable can be defined as a system property or in
 * the configuration file itself. The value of the key is first
 * searched in the defined constants, in the enviroments variables
 * and if not found there, it is
 * then searched in the configuration file being parsed.  The
 * corresponding value replaces the ${variableName} sequence.</p>
 * <p>For example, if <b>$_ENV['home']</b> env var is set to
 * <b>/home/xyz</b>, then every occurrence of the sequence
 * <b>${home}</b> will be interpreted as
 * <b>/home/xyz</b>. See {@link Log5PHP_Utility_OptionConverter::getSystemProperty()}
 * for details.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @since 0.5 
 */
class Log5PHP_Configurator_PHPIni extends Log5PHP_Configurator {

    /**
     * @var Log5PHP_Factory
     */
    private $loggerFactory = null;
    
    /**
     * Constructor
     */
    function __construct()
    {
        $this->loggerFactory = new Log5PHP_Factory_LoggerDefault();
    }
    
    /**
     * Configure the default repository using the resource pointed by <b>url</b>.
     * <b>Url</b> is any valid resurce as defined in {@link PHP_MANUAL#file} function.
     * Note that the resource will be search with <i>use_include_path</i> parameter 
     * set to "1".
     *
     * @param string $url
     * @return boolean configuration result
     */
    static function configure($url = '')
    {
        $configurator = new Log5PHP_Configurator_PHPIni();
        $repository = Log5PHP_Manager::getLoggerRepository();
        return $configurator->doConfigure($url, $repository);
    }

    /**
     * Read configuration from a file.
     *
     * <p>The function {@link PHP_MANUAL#parse_ini_file} is used to read the
     * file.</p>
     *
     * <b>The existing configuration is not cleared nor reset.</b> 
     * If you require a different behavior, then call 
     * {@link  Log5PHP_Manager::resetConfiguration()} 
     * method before calling {@link doConfigure()}.
     * 
     * <p>The configuration file consists of statements in the format
     * <b>key=value</b>. The syntax of different configuration
     * elements are discussed below.
     * 
     * <p><b>Repository-wide threshold</b></p>
     * 
     * <p>The repository-wide threshold filters logging requests by level
     * regardless of logger. The syntax is:
     * 
     * <pre>
     * log5php.threshold=[level]
     * </pre>
     * 
     * <p>The level value can consist of the string values OFF, FATAL,
     * ERROR, WARN, INFO, DEBUG, ALL or a <i>custom level</i> value. A
     * custom level value can be specified in the form
     * <samp>level#classname</samp>. By default the repository-wide threshold is set
     * to the lowest possible value, namely the level <b>ALL</b>.
     * </p>
     * 
     * 
     * <p><b>Appender configuration</b></p>
     * 
     * <p>Appender configuration syntax is:</p>
     * <pre>
     * ; For appender named <i>appenderName</i>, set its class.
     * ; Note: The appender name can contain dots.
     * log5php.appender.appenderName=name_of_appender_class
     * 
     * ; Set appender specific options.
     * 
     * log5php.appender.appenderName.option1=value1
     * log5php.appender.appenderName.optionN=valueN
     * </pre>
     * 
     * For each named appender you can configure its {@link Log5PHP_Layout}. The
     * syntax for configuring an appender's layout is:
     * <pre>
     * log5php.appender.appenderName.layout=name_of_layout_class
     * log5php.appender.appenderName.layout.option1=value1
     *  ....
     * log5php.appender.appenderName.layout.optionN=valueN
     * </pre>
     * 
     * <p><b>Configuring loggers</b></p>
     * 
     * <p>The syntax for configuring the root logger is:
     * <pre>
     * log5php.rootLogger=[level], appenderName, appenderName, ...
     * </pre>
     * 
     * <p>This syntax means that an optional <i>level</i> can be
     * supplied followed by appender names separated by commas.
     * 
     * <p>The level value can consist of the string values OFF, FATAL,
     * ERROR, WARN, INFO, DEBUG, ALL or a <i>custom level</i> value. A
     * custom level value can be specified in the form</p>
     *
     * <pre>level#classname</pre>
     * 
     * <p>If a level value is specified, then the root level is set
     * to the corresponding level.  If no level value is specified,
     * then the root level remains untouched.
     * 
     * <p>The root logger can be assigned multiple appenders.
     * 
     * <p>Each <i>appenderName</i> (separated by commas) will be added to
     * the root logger. The named appender is defined using the
     * appender syntax defined above.
     * 
     * <p>For non-root categories the syntax is almost the same:
     * <pre>
     * log5php.logger.logger_name=[level|INHERITED|NULL], appenderName, appenderName, ...
     * </pre>
     * 
     * <p>The meaning of the optional level value is discussed above
     * in relation to the root logger. In addition however, the value
     * INHERITED can be specified meaning that the named logger should
     * inherit its level from the logger hierarchy.</p>
     * 
     * <p>If no level value is supplied, then the level of the
     * named logger remains untouched.</p>
     * 
     * <p>By default categories inherit their level from the
     * hierarchy. However, if you set the level of a logger and later
     * decide that that logger should inherit its level, then you should
     * specify INHERITED as the value for the level value. NULL is a
     * synonym for INHERITED.</p>
     * 
     * <p>Similar to the root logger syntax, each <i>appenderName</i>
     * (separated by commas) will be attached to the named logger.</p>
     * 
     * <p>See the <i>appender additivity rule</i> in the user manual for 
     * the meaning of the <b>additivity</b> flag.
     * 
     * <p><b>ObjectRenderers</b></p>
     * 
     * <p>You can customize the way message objects of a given type are
     * converted to String before being logged. This is done by
     * specifying a {@link Log5PHP_ObjectRenderer}
     * for the object type would like to customize.</p>
     * 
     * <p>The syntax is:
     * 
     * <pre>
     * log5php.renderer.name_of_rendered_class=name_of_rendering.class
     * </pre>
     * 
     * As in,
     * <pre>
     * log5php.renderer.myFruit=myFruitRenderer
     * </pre>
     * 
     * <p><b>Logger Factories</b></p>
     * 
     * The usage of custom logger factories is discouraged and no longer
     * documented.
     * 
     * <p><b>Example</b></p>
     * 
     * <p>An example configuration is given below. Other configuration
     * file examples are given in the <b>tests</b> folder.
     * 
     * <pre>
     * ; Set options for appender named "A1".
     * ; Appender "A1" will be a SyslogAppender
     * log5php.appender.A1=SyslogAppender
     * 
     * ; The syslog daemon resides on www.abc.net
     * log5php.appender.A1.SyslogHost=www.abc.net
     * 
     * ; A1's layout is a Log5PHP_PatternLayout, using the conversion pattern
     * ; <b>%r %-5p %c{2} %M.%L %x - %m%n</b>. Thus, the log output will
     * ; include the relative time since the start of the application in
     * ; milliseconds, followed by the level of the log request,
     * ; followed by the two rightmost components of the logger name,
     * ; followed by the callers method name, followed by the line number,
     * ; the nested disgnostic context and finally the message itself.
     * ; Refer to the documentation of Log5PHP_PatternLayout} for further information
     * ; on the syntax of the ConversionPattern key.
     * log5php.appender.A1.layout=LoggerPatternLayout
     * log5php.appender.A1.layout.ConversionPattern="%-4r %-5p %c{2} %M.%L %x - %m%n"
     * 
     * ; Set options for appender named "A2"
     * ; A2 should be a Log5PHP_Appender_RollingFile, with maximum file size of 10 MB
     * ; using at most one backup file. A2's layout is TTCC, using the
     * ; ISO8061 date format with context printing enabled.
     * log5php.appender.A2=LoggerAppenderRollingFile
     * log5php.appender.A2.MaxFileSize=10MB
     * log5php.appender.A2.MaxBackupIndex=1
     * log5php.appender.A2.layout=LoggerLayoutTTCC
     * log5php.appender.A2.layout.ContextPrinting="true"
     * log5php.appender.A2.layout.DateFormat="%c"
     * 
     * ; Root logger set to DEBUG using the A2 appender defined above.
     * log5php.rootLogger=DEBUG, A2
     * 
     * ; Logger definitions:
     * ; The SECURITY logger inherits is level from root. However, it's output
     * ; will go to A1 appender defined above. It's additivity is non-cumulative.
     * log5php.logger.SECURITY=INHERIT, A1
     * log5php.additivity.SECURITY=false
     * 
     * ; Only warnings or above will be logged for the logger "SECURITY.access".
     * ; Output will go to A1.
     * log5php.logger.SECURITY.access=WARN
     * 
     * 
     * ; The logger "class.of.the.day" inherits its level from the
     * ; logger hierarchy.  Output will go to the appender's of the root
     * ; logger, A2 in this case.
     * log5php.logger.class.of.the.day=INHERIT
     * </pre>
     * 
     * <p>Refer to the <b>setOption</b> method in each Appender and
     * Layout for class specific options.</p>
     * 
     * <p>Use the <b>&quot;;&quot;</b> character at the
     * beginning of a line for comments.</p>
     * 
     * @param string $url The name of the configuration file where the
     *                    configuration information is stored.
     * @param Log5PHP_LoggerRepository $repository the repository to apply the
     * configuration
     */
    function doConfigure($url, Log5PHP_LoggerRepository $repository)
    {
        # removed @
        $properties = parse_ini_file($url);
        if ($properties === false) {
            Log5PHP_InternalLog::warn("LoggerPropertyConfigurator::doConfigure() cannot load '$url' configuration.");
            throw new Log5PHP_Error_Runtime('Cannot load configuration at ' . $url);
        }
        return $this->doConfigureProperties($properties, $repository);
    }


    /**
     * Read configuration options from <b>properties</b>.
     *
     * @see doConfigure().
     * @param array $properties
     * @param Log5PHP_LoggerRepository $hierarchy
     */
    function doConfigureProperties($properties, Log5PHP_LoggerRepository $hierarchy)
    {
        # removed @
        $value = $properties[LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_DEBUG_KEY];
        
        if (!empty($value)) {
            Log5PHP_InternalLog::internalDebugging(Log5PHP_Utility_OptionConverter::toBoolean($value, Log5PHP_InternalLog::internalDebugging()));
        }

        # removed @
        $thresholdStr = $properties[LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_THRESHOLD_PREFIX];
        $hierarchy->setThreshold(Log5PHP_Utility_OptionConverter::toLevel($thresholdStr, Log5PHP_Level::getLevelAll()));
        
        $this->configureRootCategory($properties, $hierarchy);
        $this->configureLoggerFactory($properties);
        $this->parseCatsAndRenderers($properties, $hierarchy);

        Log5PHP_InternalLog::debug("Log5PHP_Configurator_Property::doConfigureProperties() Finished configuring.");
        
        return true;
    }

    // --------------------------------------------------------------------------
    // Internal stuff
    // --------------------------------------------------------------------------

    /**
     * Check the provided <b>Properties</b> object for a
     * {@link Log5PHP_Factory} entry specified by 
     * {@link LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_FACTORY_KEY}.
     *  
     * If such an entry exists, an attempt is made to create an instance using 
     * the default constructor.  
     * This instance is used for subsequent Category creations
     * within this configurator.
     *
     * @see parseCatsAndRenderers()
     * @param array $props array of properties
     */
    function configureLoggerFactory($props)
    {
        # removed @
        $factoryFqcn = $props[LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_FACTORY_KEY];
        if(!empty($factoryFqcn)) {
            $factoryClassName = basename($factoryFqcn);
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::configureLoggerFactory() Trying to load factory [" .
                $factoryClassName . 
                "]."
            );
            
            $loggerFactory = new $factoryClassName();

            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::configureLoggerFactory() ".
                "Setting properties for category factory [" . get_class($loggerFactory) . "]."
            );
            
            Log5PHP_Configurator_PropertySetter::setPropertiesByObject($loggerFactory, $props, LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_FACTORY_PREFIX . ".");
        }
    }
    
    /**
     * @param array $props array of properties
     * @param Log5PHP_LoggerRepository $hierarchy
     */
    function configureRootCategory($props, $hierarchy)
    {
        $effectivePrefix = LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_LOGGER_PREFIX;
        # removed @
        $value = $props[LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_LOGGER_PREFIX];

        if(empty($value)) {
            # removed @
            $value = $props[LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_CATEGORY_PREFIX];
            $effectivePrefix = LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_CATEGORY_PREFIX;
        }

        if (empty($value)) {
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::configureRootCategory() ".
                "Could not find root logger information. Is this OK?"
            );
        } else {
            $root = $hierarchy->getRootLogger();
            // synchronized(root) {
            $this->parseCategory(
                $props, 
                $root, 
                $effectivePrefix, 
                LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_INTERNAL_ROOT_NAME, 
                $value
            );
            // }
        }
    }

    /**
     * Parse non-root elements, such non-root categories and renderers.
     *
     * @param array $props array of properties
     * @param Log5PHP_LoggerRepository $hierarchy
     */
    function parseCatsAndRenderers($props, $hierarchy)
    {
        while(list($key,$value) = each($props)) 
        {
            if( strpos($key, LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX) === 0 or 
                strpos($key, LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX) === 0)
            {
                if(strpos($key, LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX) === 0) 
                {
                    $loggerName = substr($key, strlen(LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX));
                }
                elseif (strpos($key, LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX) === 0) 
                {
                    $loggerName = substr($key, strlen(LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX));
                }
                
                $logger = $hierarchy->getLoggerByFactory($loggerName, $this->loggerFactory);
                
                $this->parseCategory($props, $logger, $key, $loggerName, $value);
                $this->parseAdditivityForLogger($props, $logger, $loggerName);
            } 
            elseif (strpos($key, LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_RENDERER_PREFIX) === 0) 
            {
                $renderedClass = substr($key, strlen(LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_RENDERER_PREFIX));
                $renderingClass = $value;
                if (method_exists($hierarchy, 'addrenderer')) 
                {
                    Log5PHP_ObjectRenderer_Map::addRenderer($hierarchy, $renderedClass, $renderingClass);
                }
            }
        }
    }

    /**
     * Parse the additivity option for a non-root category.
     *
     * @param array $props array of properties
     * @param Logger $cat
     * @param string $loggerName
     */
    function parseAdditivityForLogger($props, $cat, $loggerName)
    {
        $value = Log5PHP_Utility_OptionConverter::findAndSubst(
                    LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ADDITIVITY_PREFIX . $loggerName,
                    $props
                 );
        Log5PHP_InternalLog::debug(
            "LoggerPropertyConfigurator::parseAdditivityForLogger() ".
            "Handling " . LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_ADDITIVITY_PREFIX . $loggerName . "=[{$value}]"
        );
        // touch additivity only if necessary
        if(!empty($value)) {
            $additivity = Log5PHP_Utility_OptionConverter::toBoolean($value, true);
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::parseAdditivityForLogger() ".
                "Setting additivity for [{$loggerName}] to [{$additivity}]"
            );
            $cat->setAdditivity($additivity);
        }
    }

    /**
     * This method must work for the root category as well.
     *
     * @param array $props array of properties
     * @param Logger $logger
     * @param string $optionKey
     * @param string $loggerName
     * @param string $value
     * @return Logger
     */
    function parseCategory($props, $logger, $optionKey, $loggerName, $value)
    {
        Log5PHP_InternalLog::debug(
            "LoggerPropertyConfigurator::parseCategory() ".
            "Parsing for [{$loggerName}] with value=[{$value}]."
        );
        
        // We must skip over ',' but not white space
        $st = explode(',', $value);

        // If value is not in the form ", appender.." or "", then we should set
        // the level of the loggeregory.

        if(!(empty($value) || $value[0] == ',' )) {
            // just to be on the safe side...
            if(sizeof($st) == 0)
                return;
                
            $levelStr = current($st);
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::parseCategory() ".
                "Level token is [$levelStr]."
            );

            // If the level value is inherited, set category level value to
            // null. We also check that the user has not specified inherited for the
            // root category.
            if('INHERITED' == strtoupper($levelStr) || 'NULL' == strtoupper($levelStr)) {
                if ($loggerName == LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_INTERNAL_ROOT_NAME) {
                    Log5PHP_InternalLog::warn(
                        "LoggerPropertyConfigurator::parseCategory() ".
                        "The root logger cannot be set to null."
                    );
                } else {
                    $logger->setLevel(null);
                }
            } else {
                $logger->setLevel(Log5PHP_Utility_OptionConverter::toLevel($levelStr, Log5PHP_Level::getLevelDebug()));
            }
        }

        // Begin by removing all existing appenders.
        $logger->removeAllAppenders();
        while($appenderName = next($st)) {
            $appenderName = trim($appenderName);
            if(empty($appenderName))
                continue;
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::parseCategory() ".
                "Parsing appender named [{$appenderName}]."
            );
            $appender = $this->parseAppender($props, $appenderName);
            if($appender !== null) {
                $logger->addAppender($appender);
            }
        }
    }

    /**
     * @param array $props array of properties
     * @param string $appenderName
     * @return Log5PHP_Appender_Appendable
     */
    function parseAppender($props, $appenderName)
    {
        $appenderExists = Log5PHP_Factory_Appender::appenderExists($appenderName);
        if($appenderExists) 
        {
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Appender [{$appenderName}] was already parsed."
            );
            return Log5PHP_Factory_Appender::getAppender($appenderName);
        }
        
        // Appender was not previously initialized.
        $prefix = LOG5PHP_LOGGER_PROPERTY_CONFIGURATOR_APPENDER_PREFIX . $appenderName;
        $layoutPrefix = $prefix . ".layout";
        # removed @
        $appenderClass = $props[$prefix];
        if (!empty($appenderClass)) 
        {
            $appender = Log5PHP_Factory_Appender::createAppenderWithName($appenderClass, $appenderName);
        }
        else
        {
            Log5PHP_InternalLog::warn(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Could not instantiate appender named [$appenderName] with null className."
            );
            return null;
        }
        
        $appender->setName($appenderName);
        if( $appender->requiresLayout() ) {
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Parsing layout section for [$appenderName]."
            );
            # removed @
            $layoutClass = $props[$layoutPrefix];
            $layoutClass = Log5PHP_Utility_OptionConverter::substVars($layoutClass, $props);
            
            if (empty($layoutClass)) 
            {
                Log5PHP_InternalLog::warn(
                    "LoggerPropertyConfigurator::parseAppender() ".
                    "layout class is empty in '$layoutPrefix'. Using Simple layout"
                );
                $layout = Log5PHP_Factory_Layout :: getNewLayout('Log5PHP_Layout_Simple');
            }
            else
            {
                $layout = Log5PHP_Factory_Layout :: getNewLayout($layoutClass);
            }
            
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Parsing layout options for [$appenderName]."
            );
            
            Log5PHP_Configurator_PropertySetter::setPropertiesByObject($layout, $props, $layoutPrefix . ".");                
            Log5PHP_InternalLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "End Parsing layout options for [$appenderName]."
            );
            
            echo "\n-->calling setLayout\n";
            $appender->setLayout($layout);
            echo "--> called setLayout\n";
            
        }
        Log5PHP_Configurator_PropertySetter::setPropertiesByObject($appender, $props, $prefix . ".");
        Log5PHP_InternalLog::debug(
            "LoggerPropertyConfigurator::parseAppender() ".        
            "Parsed [{$appenderName}] options."
        );
        return $appender;        
    }

}
