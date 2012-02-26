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
 * Represents an individual logger (e.g. the root logger, MyClassLogger, etc)
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @see Logger
 */
class Log5PHP_Logger {

    /**
     * Additivity is set to true by default, that is children inherit the 
     * appenders of their ancestors by default.
     * @var boolean
     */
    protected $additive       = true;
    
    /**
     * @var string fully qualified class name
     */  
    protected $fqcn           = 'Log5PHP_Logger';

    /**
     * @var Log5PHP_Level The assigned level of this category.
     */
    protected $level          = null;
    
    /**
     * @var string name of this category.
     */
    protected $name           = '';
    
    /**
     * @var Logger The parent of this category.
     */
    protected $parent         = null;

    /**
     * @var Log5PHP_LoggerRepository the object repository
     */
    protected $repository     = null; 

    /**
     * @var array collection of appenders [name] => [appender]
     * @see Log5PHP_Appender_Appendable
     */
    protected $appenders      = array();
    
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/

    /**
     * Constructor.
     *
     * @param  string  $name  Category name   
     */
    function __construct($name)
    {
        $this->name = $name;
    }
    
    /**
     * Add a new Appender to the list of appenders of this Category instance.
     *
     * @param Log5PHP_Appender_Appendable $newAppender
     */
    function addAppender($newAppender)
    {
        $appenderName = $newAppender->getName();
        $this->appenders[$appenderName] = $newAppender;
    } 
            
    /**
     * If assertion parameter is false, then logs msg as an error statement.
     *
     * @param bool $assertion
     * @param string $msg message to log
     */
    function assertLog($assertion = true, $msg = '')
    {
        if ($assertion == false) {
            $this->error($msg);
        }
    } 

    /**
     * Call the appenders in the hierarchy starting at this.
     *
     * @param Log5PHP_LogEvent $event 
     */
    function callAppenders($event) 
    {
        foreach ($this->appenders as $appenderName => $appender) {
            $appender->doAppend($event);
        }
        
        if ($this->parent != null and $this->getAdditivity()) {
            $this->parent->callAppenders($event);
        }
    }
    
    /**
     * Log a message object with the DEBUG level including the caller.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function debug($message, $caller = null)
    {
        $debugLevel = Log5PHP_Level::getLevelDebug();
        $this->logIfEnabled($debugLevel, $message, $caller);
    } 


    /**
     * Log a message object with the INFO Level.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function info($message, $caller = null)
    {
        $infoLevel = Log5PHP_Level::getLevelInfo();
        $this->logIfEnabled($infoLevel, $message, $caller);
    }

    /**
     * Log a message with the WARN level.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function warn($message, $caller = null)
    {
        $warnLevel = Log5PHP_Level::getLevelWarn();
        $this->logIfEnabled($warnLevel, $message, $caller);
    }

    /**
     * Log a message object with the ERROR level including the caller.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function error($message, $caller = null)
    {
        $errorLevel = Log5PHP_Level::getLevelError();
        $this->logIfEnabled($errorLevel, $message, $caller);
    }
  
    /**
     * Log a message object with the FATAL level including the caller.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function fatal($message, $caller = null)
    {
        $fatalLevel = Log5PHP_Level::getLevelFatal();
        $this->logIfEnabled($fatalLevel, $message, $caller);
    } 
  
    /**
     * This generic form is intended to be used by wrappers.
     *
     * @param Log5PHP_Level $priority a valid level
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    private function logIfEnabled($priority, $message, $caller = null)
    {
        if ($this->repository->isDisabled($priority))
        {
            return;
        }
        if ($priority->isGreaterOrEqual($this->getEffectiveLevel())) 
        {
            $this->forcedLog($this->fqcn, $caller, $priority, $message);
        }
    }
  
    /**
     * This method creates a new logging event and logs the event without further checks.
     *
     * It should not be called directly. Use {@link info()}, {@link debug()}, {@link warn()},
     * {@link error()} and {@link fatal()} wrappers.
     *
     * @param string $fqcn Fully Qualified Class Name of the Logger
     * @param mixed $caller caller object or caller string id
     * @param Log5PHP_Level $level log level     
     * @param mixed $message message
     * @see Log5PHP_LogEvent          
     */
    private function forcedLog($fqcn, $caller, Log5PHP_Level $level, $message)
    {
        // $fqcn = is_object($caller) ? get_class($caller) : (string)$caller;
        $this->callAppenders(new Log5PHP_LogEvent($fqcn, $this, $level, $message));
    } 

    /**
     * Get the additivity flag for this Category instance.
     * @return boolean
     */
    function getAdditivity()
    {
        return $this->additive;
    }
 
    /**
     * Get the appenders contained in this category as an array.
     * @return array collection of appenders
     */
    function getAllAppenders() 
    {        
        $buf = array();
        foreach($this->appenders as $appender)
        {
            $buf[] = $appender;
        }
        return $buf; 
    }
    
    /**
     * Look for the appender named as name.
     * @return Log5PHP_Appender_Appendable
     */
    function getAppender($name) 
    {
        return $this->appenders[$name];
    }
    
    /**
     * Starting from this category, search the category hierarchy for a non-null
     * level and return it. If none can be found, return the ALL level.
     * @see Log5PHP_Level
     * @return Log5PHP_Level
     */
    function getEffectiveLevel()
    {
        for($c = $this; $c != null; $c = $c->parent) 
        {
            if($c->getLevel() !== null)
                return $c->getLevel();
        }
        
        return Log5PHP_Level::getLevelAll();
    }
  
    /**
     * Returns the assigned Level, if any, for this Category.
     * @return Log5PHP_Level or null 
     */
    function getLevel()
    {
        return $this->level;
    } 

    /**
     * Return the the repository where this Category is attached.
     * @return Log5PHP_LoggerRepository
     */
    function getLoggerRepository()
    {
        return $this->repository;
    } 

    /**
     * Return the category name.
     * @return string
     */
    function getName()
    {
        return $this->name;
    } 

    /**
     * Returns the parent of this category.
     * @return Logger
     */
    function getParent() 
    {
        return $this->parent;
    }      

    /**
     * Is the appender passed as parameter attached to this category?
     *
     * @param Log5PHP_Appender_Appendable $appender
     */
    function isAttached($appender)
    {
        return in_array($appender->getName(), array_keys($this->appenders));
    } 
           
    /**
     * Check whether this category is enabled for the DEBUG Level.
     * @return boolean
     */
    function isDebugEnabled()
    {
        $debugLevel = Log5PHP_Level::getLevelDebug(); 
        return $this->isEnabledFor($debugLevel);
    }       

    /**
     * Check whether this category is enabled for the info Level.
     * @return boolean
     * @see Log5PHP_Level
     */
    function isInfoEnabled()
    {
        $infoLevel = Log5PHP_Level::getLevelInfo();
        return $this->isEnabledFor($infoLevel);
    } 

    /**
     * Check whether this category is enabled for a given Level passed as parameter.
     *
     * @param Log5PHP_Level level
     * @return boolean
     */
    function isEnabledFor(Log5PHP_Level $level)
    {
        if ($this->repository->isDisabled($level)) {
            return false;
        }
        return (bool)($level->isGreaterOrEqual($this->getEffectiveLevel()));
    } 

    /**
     * Remove all previously added appenders from this Category instance.
     */
    function removeAllAppenders()
    {
        foreach($this->appenders as $appender)
        {
            $this->removeAppender($appender); 
        }
    } 
            
    /**
     * Remove the appender passed as parameter form the list of appenders.
     *
     * @param Log5PHP_Appender_Appendable $appender a {@link
     * Log5PHP_Appender_Appendable} object
     */
    function removeAppender(Log5PHP_Appender_Appendable $appender)
    {
        $appender->close();
        unset($this->appenders[$appender->getName()]);
    } 

    /**
     * Set the additivity flag for this Category instance.
     *
     * @param boolean $additive
     */
    function setAdditivity($additive) 
    {
        $this->additive = (bool)$additive;
    }

    /**
     * Only the Hiearchy class can set the hiearchy of a
     * category.
     *
     * @param Log5PHP_LoggerRepository $repository
     */
    function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Set the level of this logger.
     *
     * @param mixed $level a level string or a level costant 
     */
    function setLevel($level)
    {
        $this->level = $level;
    } 
    
    /**
     * Set the parent of this Logger.
     *
     * @param Log5PHP_Logger $parent
     */
    function setParent(Log5PHP_Logger $parent)
    {
        $this->parent = $parent;
    } 
           
}  
