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
 * This class is specialized in retrieving loggers by name and also maintaining 
 * the logger hierarchy.
 *
 * <p>The casual user does not have to deal with this class directly.</p>
 *
 * <p>The structure of the logger hierarchy is maintained by the
 * getLogger method. The hierarchy is such that children link
 * to their parent but parents do not have any pointers to their
 * children. Moreover, loggers can be instantiated in any order, in
 * particular descendant before ancestor.</p>
 *
 * <p>In case a descendant is created before a particular ancestor,
 * then it creates a provision node for the ancestor and adds itself
 * to the provision node. Other descendants of the same ancestor add
 * themselves to the previously created provision node.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 */
class Log5PHP_LoggerRepository {

    /**
     * @var object currently unused
     */
    private $defaultFactory;
    
    /**
     * @var boolean activate internal logging
     * @see Log5PHP_InternalLog
     */
    private $debug = false;

    /**
     * @var array hierarchy tree. saves here all loggers
     */
    private $tree = array();
    
    /**
     * @var Log5PHP_Root
     */
    private $root = null;
    
    /**
     * @var Log5PHP_ObjectRenderer_Map
     */
    private $rendererMap;

    /**
     * @var Log5PHP_Level main level threshold
     */
    private $threshold;
    
    /**
     * singleton instance
     */
    private static $instance = null;
    
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/

    static function getInstance()
    {
        if (!isset(self :: $instance))
        {
            self :: $instance = new Log5PHP_LoggerRepository(new Log5PHP_RootLogger());
        }
        return self :: $instance;
    }
    
    /**
     * Create a new logger hierarchy.
     * @param Logger $root the root logger
     */
    function __construct(Log5PHP_Logger $root)
    {
        $this->root = $root;
        // Enable all level levels by default.
        $this->setThreshold(Log5PHP_Level::getLevelAll());
        $this->root->setRepository($this);
        $this->rendererMap = new Log5PHP_ObjectRenderer_Map();
        $this->defaultLoggerFactory = new Log5PHP_Factory_LoggerDefault();        
    }
     
    /**
     * Add an object renderer for a specific class.
     * Not Yet Impl.
     */
    function addRenderer($classToRender, $or)
    {
        $this->rendererMap->put($classToRender, $or);
    } 
    
    /**
     * This call will clear all logger definitions from the internal hashtable.
     */
    function clear()
    {
        $this->tree = array();
    }
      
    /**
     * Check if the named logger exists in the hierarchy.
     * @param string $name
     * @return boolean
     */
    function exists($name)
    {
        return in_array($name, array_keys($this->tree));
    }
    
    /**
     * Returns all the currently defined categories in this hierarchy as an array.
     * @return array
     */  
    function getCurrentLoggers()
    {
        $loggers = array();
        $loggerNames = array_keys($this->tree);
        $enumLoggers = sizeof($loggerNames);
        for ($i = 0; $i < $enumLoggers; $i++) {
            $loggerName = $loggerNames[$i];
            $loggers[] = $this->tree[$loggerName];
        }
        return $loggers; 
    }
    
    /**
     * Return a new logger instance named as the first parameter using the default factory.
     * 
     * @param string $name logger name
     * @return Logger
     */
    function getLogger($name)
    {
        return $this->getLoggerByFactory($name, $this->defaultLoggerFactory);
    } 
    
    /**
     * Return a new logger instance named as the first parameter using the default factory.
     * 
     * @param string $name logger name
     * @return Logger
     * @todo merge with {@link getLogger()}
     */
    function getLoggerByFactory($name, $factory)
    {
        if (!isset($this->tree[$name])) 
        {
            Log5PHP_InternalLog::debug("Log5PHP_LoggerRepository::getLoggerByFactory():name=[$name]:factory=[".get_class($factory)."] creating a new logger...");
            
            $this->tree[$name] = $factory->makeNewLoggerInstance($name);
            $this->tree[$name]->setRepository($this);
            
            $nodes = explode('.', $name);
            $firstNode = array_shift($nodes);
            
            if ( $firstNode != $name and isset($this->tree[$firstNode]))
            {
                Log5PHP_InternalLog::debug("Log5PHP_LoggerRepository::getLogger($name) parent is now [$firstNode]");            
                $this->tree[$name]->setParent($this->tree[$firstNode]);
            }
            else
            {
                Log5PHP_InternalLog::debug("Log5PHP_LoggerRepository::getLogger($name) parent is now [root]");            
                $this->tree[$name]->setParent($this->root);
            } 
            
            if (sizeof($nodes) > 0) 
            {
                $prefix = $firstNode;
                // find parent node
                foreach ($nodes as $node) 
                {
                    $parentNode = "$prefix.$node";
                    if (isset($this->tree[$parentNode]) and $parentNode != $name) 
                    {
                        Log5PHP_InternalLog::debug("Log5PHP_LoggerRepository::getLogger($name) parent is now [$parentNode]");                    
                        $this->tree[$name]->setParent($this->tree[$parentNode]);
                    }
                    $prefix .= ".$node";
                }
            }
            // update children
            /*
            $children = array();
            foreach (array_keys($this->tree) as $nodeName) {
                if ($nodeName != $name and substr($nodeName, 0, strlen($name)) == $name) {
                    $children[] = $nodeName;    
                }
            }
            */
        }            
        return $this->tree[$name];
    }
    
    /**
     * @return Log5PHP_ObjectRenderer_Map Get the renderer map for this hierarchy.
     */
    function getObjectRendererMap()
    {
        return $this->rendererMap;
    }
    
    /**
     * @return Log5PHP_Root Get the root of this hierarchy.
     */ 
    function getRootLogger()
    {
        if (!isset($this->root) or $this->root == null)
        {
            $this->root = new Log5PHP_RootLogger();
        }
        return $this->root;
    }
     
    /**
     * @return Log5PHP_Level Returns the threshold Level.
     */
    function getThreshold()
    {
        return $this->threshold;
    } 

    /**
     * This method will return true if this repository is disabled 
     * for level object passed as parameter and false otherwise.
     * @return boolean
     */
    function isDisabled($level)
    {
        return ($this->threshold->toInt() > $level->toInt());
    }
    
    /**
     * Reset all values contained in this hierarchy instance to their
     * default. 
     *
     * This removes all appenders from all categories, sets
     * the level of all non-root categories to <i>null</i>,
     * sets their additivity flag to <i>true</i> and sets the level
     * of the root logger to {@link LOGGER_LEVEL_DEBUG}.  Moreover,
     * message disabling is set its default "off" value.
     * 
     * <p>Existing categories are not removed. They are just reset.
     *
     * <p>This method should be used sparingly and with care as it will
     * block all logging until it is completed.</p>
     */
    function resetConfiguration()
    {
        $root = $this->getRootLogger();
        
        $root->setLevel(Log5PHP_Level::getLevelDebug());
        $this->setThreshold(Log5PHP_Level::getLevelAll());
        $this->shutDown();
        $loggers = $this->getCurrentLoggers();
        foreach($loggers as $l)
        {
            $l->setLevel(null);
            $l->setAdditivity(true);
            $l->removeAllAppenders();
        }
        $this->rendererMap->clear();
    }
      
    /**
     * Used by subclasses to add a renderer to the hierarchy passed as parameter.
     * @param string $renderedClass a Log5PHP_ObjectRenderer class name
     * @param Log5PHP_ObjectRenderer_Base $renderer
     *
     */
    function setRenderer($renderedClass, Log5PHP_ObjectRenderer_Base $renderer)
    {
        $this->rendererMap->put($renderedClass, $renderer);
    }
    
    /**
     * set a new threshold level
     *
     * @param Log5PHP_Level $l
     */
    function setThreshold(Log5PHP_Level $l)
    {
        if ($l !== null)
            $this->threshold = $l;
    }
    
    /**
     * Set debug output
     * @param string $debug
     */
    function setDebug($debug)
    {
        $this->debug = $debug;
    }
    
    /**
     * Shutting down a hierarchy will <i>safely</i> close and remove
     * all appenders in all categories including the root logger.
     * 
     * <p>Some appenders such as {@link Log5PHP_SocketAppender}
     * need to be closed before the
     * application exists. Otherwise, pending logging events might be
     * lost.
     * 
     * <p>The shutdown method is careful to close nested
     * appenders before closing regular appenders. This is allows
     * configurations where a regular appender is attached to a logger
     * and again to a nested appender.
     */
    function shutdown()
    {
        $this->root->removeAllAppenders();
        $loggers = $this->getCurrentLoggers();
        
        if (sizeof($loggers) > 0) 
        {
            foreach ($loggers as $logger) 
            {
                $logger->removeAllAppenders();
            }
        }
    }  
} 
