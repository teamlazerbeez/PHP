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
 * The internal representation of logging event.
 *
 * @version $Revision: 43701 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP
 */
class Log5PHP_LogEvent {

    /** 
    * @var string Fully Qualified Class Name of the calling category class.
    */
    private $fqcn;
    
    /**
    * @var Logger reference
    */
    private $logger = null;
    
    /** 
    * The category (logger) name.
    * This field will be marked as private in future
    * releases. Please do not access it directly. 
    * Use the {@link getLoggerName()} method instead.
    * @deprecated 
    */
    private $categoryName;
    
    /** 
    * Level of logging event.
    * <p> This field should not be accessed directly. You shoud use the
    * {@link getLevel()} method instead.
    *
    * @deprecated
    * @var Log5PHP_Level
    */
    private $level;
    
    /** 
     * @var string The nested diagnostic context (NDC) of logging event. 
     */
    private $ndc;
    
    /** 
     * Have we tried to do an NDC lookup? If we did, there is no need
     * to do it again.  Note that its value is always false when
     * serialized. Thus, a receiving SocketNode will never use it's own
     * (incorrect) NDC. See also writeObject method.
     * @var boolean
     */
    private $ndcLookupRequired = true;
    
    /** 
     * Have we tried to do an MDC lookup? If we did, there is no need
     * to do it again.  Note that its value is always false when
     * serialized. See also the getMDC and getMDCCopy methods.
     * @var boolean  
     */
    private $mdcCopyLookupRequired = true;
    
    /** 
     * @var mixed The application supplied message of logging event. 
     */
    private $message;
    
    /** 
     * The application supplied message rendered through the log5php
     * objet rendering mechanism. At present renderedMessage == message.
     * @var string
     */
    private $renderedMessage;
    
    /** 
     * The name of thread in which this logging event was generated.
     * log5php saves here the process id via {@link PHP_MANUAL#getmypid getmypid()} 
     * @var mixed
     */
    private $threadName = null;
    
    /** 
    * The number of seconds elapsed from 1/1/1970 until logging event
    * was created plus microseconds if available.
    * @var float
    */
    private $timeStampFloat;
    
    /**
     * @var DateTime in UTC
     */
    private $timeStamp;
    
    /**
     * @var int millis since last second
     */
    private $timeMillis;

    /**
     *
     * @var int usecs since last second
     */
    private $timeUsecs;
    
    /** 
    * @var Log5PHP_LocationInfo Location information for the caller. 
    */
    private $locationInfo = null;
    
    /**
     * @var string
     */
    public static $startTime = null;
    
    // Serialization
    /*
    var $serialVersionUID = -868428216207166145L;
    var $PARAM_ARRAY = array();
    var $TO_LEVEL = "toLevel";
    var $TO_LEVEL_PARAMS = null;
    var $methodCache = array(); // use a tiny table
    */

    /**
    * Instantiate a LogEvent from the supplied parameters.
    *
    * <p>Except {@link $timeStamp} all the other fields of
    * Log5PHP_LogEvent are filled when actually needed.
    *
    * @param string $fqcn name of the caller class.
    * @param mixed $logger The {@link Logger} category of this event or the
    * logger name.
    * @param Log5PHP_Level $priority The level of this event.
    * @param mixed $message The message of this event.
    */
    function __construct($fqcn, Log5PHP_Logger $logger, Log5PHP_Level $priority, $message)
    {
        $this->fqcn = $fqcn;
        $this->logger = $logger;
        $this->categoryName = $logger->getName();
        $this->level = $priority;
        $this->message = $message;
    
        list($usecs, $secs) = explode(' ', microtime());
        $this->timeStampFloat = ((float)$usecs + (float)$secs);

        // until php 5.2.2 we cannot use 'u' escape code
        $this->timeStamp = new DateTime();
        $this->timeStamp->setTimeZone(new DateTimeZone('UTC')); // ALWAYS use UTC

        $this->timeMillis = (int) round($usecs * 1000);
        $this->timeUsecs = (int) round($usecs * 1000000);
    }

    /**
     * Set the location information for this logging event. The collected
     * information is cached for future use.
     * 
     * Finds the class, function, line and file of the call to log5php.
     *
     * <p>This method uses {@link PHP_MANUAL#debug_backtrace debug_backtrace()}
     * function to collect informations about caller.</p>
     * <p>It only recognize informations generated by {@link Logger} and its subclasses.</p>
     * @return Log5PHP_LocationInfo
     */
    function getLocationInfo()
    {
        if($this->locationInfo === null) 
        {
            $this->locationInfo = new Log5PHP_LocationInfo(debug_backtrace(), $this->fqcn);
        }
        
        return $this->locationInfo;
    }

    /**
     * Return the level of this event. Use this form instead of directly
     * accessing the {@link $level} field.
     * @return Log5PHP_Level  
     */
    function getLevel()
    {
        return $this->level;
    }

    /**
     * Return the name of the logger. Use this form instead of directly
     * accessing the {@link $categoryName} field.
     * @return string  
     */
    function getLoggerName()
    {
        return $this->categoryName;
    }

    /**
     * Return the message for this logging event.
     *
     * <p>Before serialization, the returned object is the message
     * passed by the user to generate the logging event. After
     * serialization, the returned value equals the String form of the
     * message possibly after object rendering.
     * @return mixed
     */
    function getMessage()
    {
        if($this->message !== null) {
            return $this->message;
        } else {
            return $this->getRenderedMessage();
        }
    }

    /**
     * This method returns the NDC for this event. It will return the
     * correct content even if the event was generated in a different
     * thread or even on a different machine. The {@link Log5PHP_NDC::get()} method
     * should <b>never</b> be called directly.
     * @return string  
     */
    function getNDC()
    {
        if ($this->ndcLookupRequired) {
            $this->ndcLookupRequired = false;
            $this->ndc = implode(' ', Log5PHP_NDC::get());
        }
        return $this->ndc;
    }


    /**
     * Returns the the context corresponding to the <code>key</code>
     * parameter.
     * @return string
     */
    function getMDC($key)
    {
        return Log5PHP_MDC::get($key);
    }

    /**
     * Render message.
     * @return string
     */
    function getRenderedMessage()
    {
        if($this->renderedMessage === null and $this->message !== null) {
            if (is_string($this->message)) {
                $this->renderedMessage = $this->message;
            } else {
                if ($this->logger !== null) {
                    $repository = $this->logger->getLoggerRepository();
                } else {
                    $repository = Log5PHP_Manager::getLoggerRepository();
                }
                
                $rendererMap = $repository->getObjectRendererMap();
                $this->renderedMessage= $rendererMap->findAndRender($this->message);
            }
        }
        return $this->renderedMessage;
    }

    /**
     * Returns the time when the application started, in seconds
     * elapsed since 01.01.1970 plus microseconds if available.
     *
     * @return float
     */
    static function getStartTime()
    {
        if (is_null(self :: $startTime)) {
            if (function_exists('microtime')) {
                list($usec, $sec) = explode(' ', microtime()); 
                self :: $startTime = ((float)$usec + (float)$sec);
            } else {
                self :: $startTime = time();
            }
        }
        return self :: $startTime; 
    }
    
    /**
     * @return float seconds since epoch
     */
    function getTimeStampFloat()
    {
        return $this->timeStampFloat;
    }
    
    /**
     * @return DateTime
     */
    function getTimeStamp()
    {
        return $this->timeStamp;   
    }
    
    /**
     * This will become unnecessary once we have php 5.2.2.
     * 
     * @return int millis since last second
     */
    function getTimeMillis()
    {
        return $this->timeMillis;
    }

    /**
     * 
     * @return int millis since last second
     */
    function getTimeUsecs()
    {
        return $this->timeUsecs;
    }

    /**
     * @return string ISO8601 format
     */
    function getTimeISO8601()
    {
        return $this->timeStamp->format('Y-m-d\TH:i:s.') . $this->timeMillis . $this->timeStamp->format('P');
    }
    
    /**
     * @return mixed
     */
    function getThreadName()
    {
        if ($this->threadName === null)
            $this->threadName = (string)getmypid();
        return $this->threadName;
    }

    /**
     * @return mixed null
     */
    function getThrowableInformation()
    {
        return null;
    }
    
    /**
     * Serialize this object
     * @return string
     */
    function toString()
    {
        serialize($this);
    }
    
    /**
     * Avoid serialization of the {@link $logger} object
     */
    function __sleep()
    {
        return array(
            'fqcn','categoryName',
            'level',
            'ndc','ndcLookupRequired',
            'message','renderedMessage',
            'threadName',
            'timeStamp',
            'locationInfo'
        );
    }

}

Log5PHP_LogEvent::getStartTime();

