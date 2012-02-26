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
 * Abstract superclass of the other appenders in the package.
 *  
 * This class provides the code for common functionality, such as
 * support for threshold filtering and support for general filters.
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 */
abstract class Log5PHP_Appender_Base implements Log5PHP_Appender_Appendable {

    /**
     * @var boolean closed appender flag
     */
    protected $closed;
           
   /**
     * Log5PHP_Layout for this appender. It can be null if appender has its own layout
     * @var Log5PHP_Layout
     */
    protected $layout = null; 
           
    /**
     * The first filter in the filter chain
     * @var Log5PHP_Filter
     */
    private $headFilter = null;
            
    /**
     * @var string Appender name
     */
    private $name;
           
    /**
     * The last filter in the filter chain
     * @var Log5PHP_Filter
     */
    private $tailFilter = null; 
           
    /**
     * @var Log5PHP_Level There is no level threshold filtering by default.
     */
    private $threshold = null;
    
    /**
     * @var boolean needs a layout formatting ?
     */
    protected $requiresLayout = false;
    
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
    
    /**
     * Constructor
     *
     * @param string $name appender name
     */
    function __construct($name)
    {
        $this->name = $name;
        $this->clearFilters();
    }

    /**
     * @param Log5PHP_Filter $newFilter add a new Log5PHP_Filter
     * @see Log5PHP_Appender_Appendable::addFilter()
     */
    function addFilter(Log5PHP_Filter $newFilter)
    {
        if($this->headFilter === null) {
            $this->headFilter = $newFilter;
            $this->tailFilter = $this->headFilter;
        } else {
            $this->tailFilter->setNext($newFilter);
            $this->tailFilter = $this->tailFilter->getNext();
        }
    }
    
    /**
     * Common functionality when closing an appender
     */
    function activateOptionsWrapper()
    {
        $this->closed = false;
    }
    
    /**
     * Derived appenders should override this method if option structure
     * requires it.
     */
    function activateOptions() 
    { 
        # no-op implementation
    }
    
    function close()
    {
        # no op
    }
    
    /**
     * Subclasses of {@link Log5PHP_Appender_Base} should implement 
     * this method to perform actual logging.
     *
     * @param Log5PHP_LogEvent $event
     * @see doAppend()
     */
    abstract protected function append(Log5PHP_LogEvent $event);
 
    /**
     * @see Log5PHP_Appender_Appendable::clearFilters()
     */
    function clearFilters()
    {
        unset($this->headFilter);
        unset($this->tailFilter);
        $this->headFilter = null;
        $this->tailFilter = null;
    }
           
    /**
     * Finalize this appender by calling the derived class' <i>close()</i> method.
     */
    final function finalize() 
    {
        // An appender might be closed then garbage collected. There is no
        // point in closing twice.
        if ($this->closed) return;
        
        Log5PHP_InternalLog::debug("Log5PHP_Appender_Base::finalize():name=[{$this->name}].");
        
        $this->close();
        $this->closed = true;
    }
           
    /**
     * @see Log5PHP_Appender_Appendable::getFilter()
     * @return Filter
     */
    function getFilter()
    {
        return $this->headFilter;
    } 

    /** 
     * Return the first filter in the filter chain for this Appender. 
     * The return value may be <i>null</i> if no is filter is set.
     * @return Filter
     */
    final function getFirstFilter()
    {
        return $this->headFilter;
    }
            
    /**
     * @see Log5PHP_Appender_Appendable::getLayout()
     * @return Log5PHP_Layout
     */
    final function getLayout()
    {
        return $this->layout;
    }
           
    /**
     * @see Log5PHP_Appender_Appendable::getName()
     * @return string
     */
    final function getName()
    {
        return $this->name;
    }
    
    /**
     * Returns this appenders threshold level. 
     * See the {@link setThreshold()} method for the meaning of this option.
     * @return Log5PHP_Level
     */
    final function getThreshold()
    { 
        return $this->threshold;
    }
    
    /**
     * Check whether the message level is below the appender's threshold. 
     *
     *
     * If there is no threshold set, then the return value is always <i>true</i>.
     * @param Log5PHP_Level $priority
     * @return boolean true if priority is greater or equal than threshold  
     */
    function isAsSevereAsThreshold(Log5PHP_Level $priority)
    {
        if ($this->threshold === null)
            return true;
            
        return $priority->isGreaterOrEqual($this->getThreshold());
    }
    
    /**
     * @see Log5PHP_Appender_Appendable::doAppend()
     * @param Log5PHP_LogEvent $event
     */
    final function doAppend(Log5PHP_LogEvent $event)
    {
        Log5PHP_InternalLog::debug("Log5PHP_Appender_Base::doAppend()"); 

        if ($this->closed) {
            Log5PHP_InternalLog::debug("Log5PHP_Appender_Base::doAppend() Attempted to append to closed appender named [{$this->name}].");
            return;
        }
        if(!$this->isAsSevereAsThreshold($event->getLevel())) {
            Log5PHP_InternalLog::debug("Log5PHP_Appender_Base::doAppend() event level is less severe than threshold.");
            return;
        }

        $f = $this->getFirstFilter();
    
        while($f !== null) {
            switch ($f->decide($event)) {
                case LOG5PHP_LOGGER_FILTER_DENY: return;
                case LOG5PHP_LOGGER_FILTER_ACCEPT: return $this->append($event);
                case LOG5PHP_LOGGER_FILTER_NEUTRAL: $f = $f->getNext();
            }
        }
        $this->append($event);    
    }    
        
            
    /**
     * @see Log5PHP_Appender_Appendable::requiresLayout()
     * @return boolean
     */
    final function requiresLayout()
    {
        return $this->requiresLayout;
    }
           
    /**
     * @see Log5PHP_Appender_Appendable::setLayout()
     * @param Log5PHP_Layout $layout
     */
    final function setLayout(Log5PHP_Layout_Base $layout)
    {
        if ($this->requiresLayout())
        {
            $this->layout = $layout;
        }
    } 
 
    /**
     * @see Log5PHP_Appender_Appendable::setName()
     * @param string $name
     */
    final function setName($name) 
    {
        $this->name = $name;    
    }
    
    /**
     * Set the threshold level of this appender.
     *
     * @param mixed $threshold can be a {@link Log5PHP_Level} object or a string.
     * @see Log5PHP_Utility_OptionConverter::toLevel()
     */
    function setThreshold($threshold)
    {
        if (is_string($threshold)) {
           $this->threshold = Log5PHP_Utility_OptionConverter::toLevel($threshold, null);
        }elseif (is_a($threshold, 'Log5PHP_Level')) {
           $this->threshold = $threshold;
        }
    }
    
    /**
     * Perform actions before object serialization.
     *
     * Call {@link finalize()} to properly close the appender.
     */
    function __sleep()
    {
        $this->finalize();
        return array_keys(get_object_vars($this)); 
    }
    
    /**
     * Perform actions after object deserialization.
     *
     * Call {@link activateOptions()} to properly setup the appender.
     */
    function __wakeup()
    {
        $this->activateOptionsWrapper();
    }
    
}
