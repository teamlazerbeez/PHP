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
 * @subpackage src_main_php_Log5PHP_Configurator
 */

/**
 * @ignore 
 */
define('LOG5PHP_LOGGER_DOM_CONFIGURATOR_APPENDER_STATE',    1000);
define('LOG5PHP_LOGGER_DOM_CONFIGURATOR_LAYOUT_STATE',      1010);
define('LOG5PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE',        1020);
define('LOG5PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE',      1030);
define('LOG5PHP_LOGGER_DOM_CONFIGURATOR_FILTER_STATE',      1040);

define('LOG5PHP_LOGGER_DOM_CONFIGURATOR_DEFAULT_FILENAME',  './log5php.xml');

/**
 * @var string the default configuration document
 */
define('LOG5PHP_LOGGER_DOM_CONFIGURATOR_DEFAULT_CONFIGURATION', 
'<?xml version="1.0" ?>
<configuration threshold="all">
    <appender name="A1" class="LoggerAppenderEcho">
        <layout class="LoggerLayoutSimple" />
    </appender>
    <root>
        <level value="debug" />
        <appender_ref ref="A1" />
    </root>
</configuration>');

/**
 * Use this class to initialize the log5php environment using expat parser.
 *
 * <p>Read the log4php.dtd included in the documentation directory. Note that
 * php parser does not validate the document.</p>
 *
 * <p>Sometimes it is useful to see how log5php is reading configuration
 * files. You can enable log5php internal logging by setting the <var>debug</var> 
 * attribute in the <var>configuration</var> element. As in
 * <pre>
 * &lt;configuration <b>debug="true"</b> > ... &lt; /configuration>
 * </pre>
 *
 * <p>There are sample XML files included in the package under <b>tests/</b> 
 * subdirectories.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Configurator
 * @since 0.4 
 */
class Log5PHP_Configurator_XML extends Log5PHP_Configurator {

    /**
     * @var Log5PHP_LoggerRepository
     */
    private $repository;
    
    /**
     * @var array state stack 
     */
    private $state;

    /**
     * @var Logger parsed Logger  
     */
    private $logger;
    
    /**
     * @var Log5PHP_Appender_Appendable parsed Log5PHP_Appender_Appendable 
     */
    private $appender;
    
    /**
     * @var Log5PHP_Filter parsed Log5PHP_Filter 
     */
    private $filter;
    
    /**
     * @var Log5PHP_Layout parsed Log5PHP_Layout 
     */
    private $layout;
    
    /**
     * Constructor
     */
    function __construct()
    {
        $this->state    = array();
        $this->logger   = null;
        $this->appender = null;
        $this->filter   = null;
        $this->layout   = null;
    }
    
    /**
     * Configure the default repository using the resource pointed by <b>url</b>.
     * <b>Url</b> is any valid resurce as defined in {@link PHP_MANUAL#file} function.
     * Note that the resource will be search with <i>use_include_path</i> parameter 
     * set to "1".
     *
     * @param string $url
     */
    static function configure($url = '')
    {
        $configurator = new Log5PHP_Configurator_XML();
        $repository = Log5PHP_Manager::getLoggerRepository();
        return $configurator->doConfigure($url, $repository);
    }
    
    /**
     * Configure the given <b>repository</b> using the resource pointed by <b>url</b>.
     * <b>Url</b> is any valid resurce as defined in {@link PHP_MANUAL#file} function.
     * Note that the resource will be search with <i>use_include_path</i> parameter 
     * set to "1".
     *
     * @param string $url
     * @param Log5PHP_LoggerRepository $repository
     */
    function doConfigure($url = '', Log5PHP_LoggerRepository $repository)
    {
        $xmlData = '';
        if (!empty($url))
            $xmlData = implode('', file($url, 1));
        return $this->doConfigureByString($xmlData, $repository);
    }
    
    /**
     * Configure the given <b>repository</b> using the configuration written in <b>xmlData</b>.
     * Do not call this method directly. Use {@link doConfigure()} instead.
     * @param string $xmlData
     * @param Log5PHP_LoggerRepository $repository
     */
    function doConfigureByString($xmlData, $repository)
    {
        return $this->parse($xmlData, $repository);
    }
    
    /**
     * @param Log5PHP_LoggerRepository $repository
     */
    function doConfigureDefault($repository)
    {
        return $this->doConfigureByString(LOG5PHP_LOGGER_DOM_CONFIGURATOR_DEFAULT_CONFIGURATION, $repository);
    }
    
    /**
     * @param string $xmlData
     */
    function parse($xmlData, $repository)
    {
        // Log5PHP_Manager::resetConfiguration();
        $this->repository = $repository;

        $parser = xml_parser_create_ns();
    
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, "tagOpen", "tagClose");
        
        $result = xml_parse($parser, $xmlData, true);
        if (!$result) {
            $errorCode = xml_get_error_code($parser);
            $errorStr = xml_error_string($errorCode);
            $errorLine = xml_get_current_line_number($parser);
            Log5PHP_InternalLog::warn(
                "Log5PHP_Configurator_XML::parse() ".
                "Parsing error [{$errorCode}] {$errorStr}, line {$errorLine}"
            );
            $this->repository->resetConfiguration();
        } else {
            xml_parser_free($parser);
        }
        return $result;
    }
    
    /**
     * @param mixed $parser
     * @param string $tag
     * @param array $attribs
     *
     * @todo In 'LOGGER' case find a better way to detect 'getLogger()' method
     */
    function tagOpen($parser, $tag, $attribs)
    {
        switch ($tag) {
        
            case 'CONFIGURATION' :
            
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() CONFIGURATION");

                if (isset($attribs['THRESHOLD'])) {
                
                    $this->repository->setThreshold(
                        Log5PHP_Utility_OptionConverter::toLevel(
                            $this->subst($attribs['THRESHOLD']), 
                            $this->repository->getThreshold()
                        )
                    );
                }
                if (isset($attribs['DEBUG'])) {
                    $debug = Log5PHP_Utility_OptionConverter::toBoolean($this->subst($attribs['DEBUG']), Log5PHP_InternalLog::internalDebugging());
                    $this->repository->setDebug($debug);
                    Log5PHP_InternalLog::internalDebugging($debug);
                    Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() CONFIGURATION. Internal Debug turned ".($debug ? 'on':'off'));
                    
                }
                break;
                
            case 'APPENDER' :
            
                unset($this->appender);
                $this->appender = null;
                
                # removed @
                $name  = $this->subst($attribs['NAME']);
                # removed @
                $class = $this->subst($attribs['CLASS']);
                
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen():tag=[$tag]:name=[$name]:class=[$class]");
                
                $this->appender = Log5PHP_Factory_Appender::createAppenderWithName($class, $name);
                
                $this->state[] = LOG5PHP_LOGGER_DOM_CONFIGURATOR_APPENDER_STATE;
                break;
                
            case 'APPENDER_REF' :
            case 'APPENDER-REF' :
            
            
                if (isset($attribs['REF']) and !empty($attribs['REF'])) 
                {
                    $appenderName = $this->subst($attribs['REF']);
                    
                    Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() APPENDER-REF ref='$appenderName'");        
                    
                    $appender = Log5PHP_Factory_Appender::getAppender($appenderName);
                    if ($appender !== null) 
                    {
                        switch (end($this->state)) 
                        {
                            case LOG5PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE:
                            case LOG5PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE:                
                                $this->logger->addAppender($appender);
                                break;
                            default:
                                throw new Log5PHP_Configurator_XMLException('In the wrong state to have an appender-ref element');
                                break;
                        }
                    } 
                    else 
                    {
                        throw new Log5PHP_Configurator_XMLException("APPENDER-REF ref '$appenderName' points to a null appender");
                    }
                }
                else 
                {
                    throw new Log5PHP_Configurator_XMLException("APPENDER-REF ref not set or empty");            
                }                
                break;                
                
            case 'FILTER' :
            
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() FILTER");
                            
                unset($this->filter);
                $this->filter = null;

                # removed @
                $filterName = basename($this->subst($attribs['CLASS']));
                if (!empty($filterName)) {
                    if (!class_exists($filterName)) {
                        # removed @
                        include_once(LOG5PHP_DIR . "/filter/{$filterName}.php");
                    }
                    if (class_exists($filterName)) {
                        $this->filter = new $filterName();
                    } else {
                        Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::tagOpen() FILTER. class '$filterName' doesnt exist");
                    }
                    $this->state[] = LOG5PHP_LOGGER_DOM_CONFIGURATOR_FILTER_STATE;
                } else {
                    Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::tagOpen() FILTER filter name cannot be empty");
                }
                break;
                
            case 'LAYOUT':
            
                if ($this->appender === null)
                {
                    throw new Log5PHP_Configurator_XMLException('Null appender when setting layout');
                }
            
                $class = $attribs['CLASS'];

                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() LAYOUT class='{$class}'");

                $this->layout = Log5PHP_Factory_Layout::getNewLayout($this->subst($class));
                
                $this->state[] = LOG5PHP_LOGGER_DOM_CONFIGURATOR_LAYOUT_STATE;
                break;
            
            case 'LOGGER':
            
                // $this->logger is assigned by reference.
                // Only '$this->logger=null;' destroys referenced object
                unset($this->logger);
                $this->logger = null;
                
                if (!array_key_exists('NAME', $attribs))
                {
                    throw new Log5PHP_Configurator_XMLException("Log5PHP_Configurator_XML::tagOpen() LOGGER. Attribute 'name' is not set or is empty.");
                }
                $loggerName = $this->subst($attribs['NAME']);
                
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() LOGGER. name='$loggerName'");        
                
                $this->logger = $this->repository->getLogger($loggerName);
                
                if ($this->logger !== null and isset($attribs['ADDITIVITY'])) {
                    $additivity = Log5PHP_Utility_OptionConverter::toBoolean($this->subst($attribs['ADDITIVITY']), true);     
                    $this->logger->setAdditivity($additivity);
                }
                $this->state[] = LOG5PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE;;
                break;
            
            case 'LEVEL':
            case 'PRIORITY':
            
                if (!isset($attribs['VALUE'])) {
                    Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() LEVEL value not set");
                    break;
                }
                    
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() LEVEL value={$attribs['VALUE']}");
                
                if ($this->logger === null) { 
                    Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::tagOpen() LEVEL. parent logger is null");
                    break;
                }
        
                switch (end($this->state)) {
                    case LOG5PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE:
                        $this->logger->setLevel(
                            Log5PHP_Utility_OptionConverter::toLevel(
                                $this->subst($attribs['VALUE']), 
                                $this->logger->getLevel()
                            )
                        );
                        Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() LEVEL root level is now '{$attribs['VALUE']}' ");                
                        break;
                    case LOG5PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE:
                        $this->logger->setLevel(
                            Log5PHP_Utility_OptionConverter::toLevel(
                                $this->subst($attribs['VALUE']), 
                                $this->logger->getLevel()
                            )
                        );
                        break;
                    default:
                        Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::tagOpen() LEVEL state '{$this->state}' not allowed here");
                }
                break;
            
            case 'PARAM':

                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() PARAM");
                
                if (!isset($attribs['NAME'])) {
                    Log5PHP_InternalLog::warn(
                        "Log5PHP_Configurator_XML::tagOpen() PARAM. ".
                        "attribute 'name' not defined."
                    );
                    break;
                }
                if (!isset($attribs['VALUE'])) {
                    Log5PHP_InternalLog::warn(
                        "Log5PHP_Configurator_XML::tagOpen() PARAM. ".
                        "attribute 'value' not defined."
                    );
                    break;
                }
                    
                switch (end($this->state)) {
                    case LOG5PHP_LOGGER_DOM_CONFIGURATOR_APPENDER_STATE:
                        if ($this->appender !== null) {
                            $this->setter($this->appender, $this->subst($attribs['NAME']), $this->subst($attribs['VALUE']));
                        } else {
                            Log5PHP_InternalLog::warn(
                                "Log5PHP_Configurator_XML::tagOpen() PARAM. ".
                                " trying to set property to a null appender."
                            );
                        }
                        break;
                    case LOG5PHP_LOGGER_DOM_CONFIGURATOR_LAYOUT_STATE:
                        if ($this->layout !== null) {
                            $this->setter($this->layout, $this->subst($attribs['NAME']), $this->subst($attribs['VALUE']));                
                        } else {
                            Log5PHP_InternalLog::warn(
                                "Log5PHP_Configurator_XML::tagOpen() PARAM. ".
                                " trying to set property to a null layout."
                            );
                        }
                        break;
                    case LOG5PHP_LOGGER_DOM_CONFIGURATOR_FILTER_STATE:
                        if ($this->filter !== null) {
                            $this->setter($this->filter, $this->subst($attribs['NAME']), $this->subst($attribs['VALUE']));
                        } else {
                            Log5PHP_InternalLog::warn(
                                "Log5PHP_Configurator_XML::tagOpen() PARAM. ".
                                " trying to set property to a null filter."
                            );
                        }
                        break;
                    default:
                        Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::tagOpen() PARAM state '{$this->state}' not allowed here");
                }
                break;
            
            case 'RENDERER':

                # removed @
                $renderedClass   = $this->subst($attribs['RENDEREDCLASS']);
                # removed @
                $renderingClass  = $this->subst($attribs['RENDERINGCLASS']);
        
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() RENDERER renderedClass='$renderedClass' renderingClass='$renderingClass'");
        
                if (!empty($renderedClass) and !empty($renderingClass)) {
                    $renderer = Log5PHP_Factory_ObjectRenderer::getObjectRenderer($renderingClass);
                    if ($renderer === null) {
                        Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::tagOpen() RENDERER cannot instantiate '$renderingClass'");
                    } else { 
                        $this->repository->setRenderer($renderedClass, $renderer);
                    }
                } else {
                    Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::tagOpen() RENDERER renderedClass or renderingClass is empty");        
                }
                break;
            
            case 'ROOT':
            
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagOpen() ROOT");
                
                $this->logger = Log5PHP_Manager::getRootLogger();
                
                $this->state[] = LOG5PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE;
                break;
                
        }
         
    }


    /**
     * @param mixed $parser
     * @param string $tag
     */
    function tagClose($parser, $tag)
    {
        switch ($tag) {
        
            case 'CONFIGURATION' : 
          
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagClose() CONFIGURATION");
                break;
                
            case 'APPENDER' :
            
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagClose() APPENDER");
                
                if ($this->appender !== null) {
                    if ($this->appender->requiresLayout() and $this->appender->getLayout() === null) {
                        $appenderName = $this->appender->getName();
                        throw new Log5PHP_Exception_XMLConfigurator(
                            "Log5PHP_Configurator_XML::tagClose() APPENDER. ".
                            "'$appenderName' requires a layout that is not defined. ".
                            "Using a simple layout"
                        );
                        $this->appender->setLayout(Log5PHP_Factory_Layout :: getNewLayout('Log5PHP_LayoutSimple'));
                    }                    
                    $this->appender->activateOptions();
                }        
                else
                {
                    throw new Log5PHP_Exception_XMLConfigurator('Appender was null at tag close');
                }
                array_pop($this->state);        
                break;
                
            case 'FILTER' :
            
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagClose() FILTER");
                            
                if ($this->filter !== null) {
                    $this->filter->activateOptions();
                    $this->appender->addFilter($this->filter);
                    $this->filter = null;
                }
                array_pop($this->state);        
                break;
                
            case 'LAYOUT':

                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagClose() LAYOUT");
                
                if ($this->layout === null)
                {
                    throw new Log5PHP_Configurator_XMLException('Layout did not end up getting set');
                }

                if ($this->appender !== null and $this->layout !== null and $this->appender->requiresLayout()) {
                    $this->layout->activateOptions();
                    $this->appender->setLayout($this->layout);
                    $this->layout = null;
                }
                
                array_pop($this->state);
                break;
            
            case 'LOGGER':
            
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagClose() LOGGER");        

                array_pop($this->state);
                break;
            
            case 'ROOT':
            
                Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::tagClose() ROOT");

                array_pop($this->state);
                break;
        }
    }
    
    /**
     * @param object $object
     * @param string $name
     * @param mixed $value
     */
    function setter($object, $name, $value)
    {
        if (empty($name)) {
            Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::setter() 'name' param cannot be empty");        
            return false;
        }
        $methodName = 'set'.ucfirst($name);
        if (method_exists($object, $methodName)) {
            Log5PHP_InternalLog::debug("Log5PHP_Configurator_XML::setter() Calling ".get_class($object)."::{$methodName}({$value})");
            return call_user_func(array($object, $methodName), $value);
        } else {
            Log5PHP_InternalLog::warn("Log5PHP_Configurator_XML::setter() ".get_class($object)."::{$methodName}() does not exists");
            return false;
        }
    }
    
    function subst($value)
    {
        return Log5PHP_Utility_OptionConverter::substVars($value);
    }

}
