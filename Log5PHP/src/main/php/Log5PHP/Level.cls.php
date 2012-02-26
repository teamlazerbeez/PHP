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
define('LOG5PHP_LEVEL_OFF_INT',     2147483647); 
define('LOG5PHP_LEVEL_FATAL_INT',        50000);
define('LOG5PHP_LEVEL_ERROR_INT',        40000);
define('LOG5PHP_LEVEL_WARN_INT',         30000);
define('LOG5PHP_LEVEL_INFO_INT',         20000);
define('LOG5PHP_LEVEL_DEBUG_INT',        10000);
/*
 * somehow  php managed to NOT allow -PHP_INT_MAX - 1 as the smallest possible
 * int... not sure how they could screw that up.
 */
define('LOG5PHP_LEVEL_ALL_INT',    -2147483647);

/**
 * Defines the minimum set of levels recognized by the system, that is
 * <i>OFF</i>, <i>FATAL</i>, <i>ERROR</i>,
 * <i>WARN</i>, <i>INFO</i, <i>DEBUG</i> and
 * <i>ALL</i>.
 *
 * <p>The <i>Log5PHP_Level</i> class may be subclassed to define a larger
 * level set.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @since 0.5
 */
class Log5PHP_Level {

    /**
     * @var integer
     */
    private $levelInt;
  
    /**
     * @var string
     */
    private $levelStr;
  
    /**
     * @var integer
     */
    private $syslogEquivalent;

    /**
     * The following are used to memoize the getLevelFoo() static methods
     */

    /**
     * @var Log5PHP_Level
     */
    private static $levelOff;
    
    /**
     * @var Log5PHP_Level
     */
    private static $levelFatal;
    
    /**
     * @var Log5PHP_Level
     */
    private static $levelError;

    /**
     * @var Log5PHP_Level
     */
    private static $levelWarn;
    
    /**
     * @var Log5PHP_Level
     */
    private static $levelInfo;
    
    /**
     * @var Log5PHP_Level
     */
    private static $levelDebug;
    
    /**
     * @var Log5PHP_Level
     */
    private static $levelAll;
    
    /**
     * Constructor
     *
     * @param integer $levelInt
     * @param string $levelStr
     * @param integer $syslogEquivalent
     */
    function __construct($levelInt, $levelStr, $syslogEquivalent)
    {
        $this->levelInt = $levelInt;
        $this->levelStr = $levelStr;
        $this->syslogEquivalent = $syslogEquivalent;
    }

    /**
     * Two priorities are equal if their level fields are equal.
     *
     * @param object $o
     * @return boolean 
     */
    function equals(Log5PHP_Level $o)
    {
        return ($this->levelInt == $o->levelInt);
    }
    
        /**
     * Return the syslog equivalent of this priority as an integer.
     * @return integer
     */
    final function getSyslogEquivalent()
    {
        return $this->syslogEquivalent;
    }

    /**
     * Returns true if this level has a higher level than the argument
     * 
     * @param Log5PHP_Level $l
     * @return bool
     */
    function isGreater(Log5PHP_Level $l)
    {
        return $this->toInt() > $l->toInt();
    }

    /**
     * Returns <i>true</i> if this level has a higher or equal
     * level than the level passed as argument, <i>false</i>
     * otherwise.  
     * 
     * <p>You should think twice before overriding the default
     * implementation of <i>isGreaterOrEqual</i> method.
     *
     * @param Log5PHP_Level $r
     * @return boolean
     */
    final function isGreaterOrEqual(Log5PHP_Level $r)
    {
        return $this->toInt() >= $r->toInt();
    }

    /**
     * Returns the string representation of this priority.
     * @return string
     */
    final function toString()
    {
        return $this->levelStr;
    }

    /**
     * Returns the integer representation of this level.
     * @return integer
     */
    final function toInt()
    {
        return $this->levelInt;
    }
    
    /**
     * Returns an Off Level
     * @return Log5PHP_Level
     */
    static function getLevelOff()
    {
        if (!isset(self :: $levelOff))
        {
            self :: $levelOff = new Log5PHP_Level(LOG5PHP_LEVEL_OFF_INT, 'OFF', 0);
        }
        
        return self :: $levelOff;
    }

    /**
     * Returns a Fatal Level
     * @return Log5PHP_Level
     */
    static function getLevelFatal()
    {
        if (!isset(self :: $levelFatal))
        { 
            self :: $levelFatal = new Log5PHP_Level(LOG5PHP_LEVEL_FATAL_INT, 'FATAL', 0);
        }        
        return self :: $levelFatal;
    }
    
    /**
     * Returns an Error Level
     * @return Log5PHP_Level
     */
    static function getLevelError()
    {
        if (!isset(self :: $levelError))
        {
             self :: $levelError = new Log5PHP_Level(LOG5PHP_LEVEL_ERROR_INT, 'ERROR', 3);
        }
        return self :: $levelError;
    }
    
    /**
     * Returns a Warn Level
     * @return Log5PHP_Level
     */
    static function getLevelWarn()
    {
        if (!isset(self :: $levelWarn))
        {
            self :: $levelWarn = new Log5PHP_Level(LOG5PHP_LEVEL_WARN_INT, 'WARN', 4);
        }
        return self :: $levelWarn;
    }

    /**
     * Returns an Info Level
     * @return Log5PHP_Level
     */
    static function getLevelInfo()
    {
        if (!isset(self :: $levelInfo))
        {
            self :: $levelInfo = new Log5PHP_Level(LOG5PHP_LEVEL_INFO_INT, 'INFO', 6);
        }
        return self :: $levelInfo;
    }

    /**
     * Returns a Debug Level
     * @return Log5PHP_Level
     */
    static function getLevelDebug()
    {
        if (!isset(self :: $levelDebug))
        {
            self :: $levelDebug = new Log5PHP_Level(LOG5PHP_LEVEL_DEBUG_INT, 'DEBUG', 7);
        }
        return self :: $levelDebug;
    }

    /**
     * Returns an All Level
     * @return Log5PHP_Level
     */
    static function getLevelAll()
    {
        if (!isset(self :: $levelAll))
        {
            self :: $levelAll = new Log5PHP_Level(LOG5PHP_LEVEL_ALL_INT, 'ALL', 7);
        }
        return self :: $levelAll;
    }
    
    /**
     * Convert the string passed as argument to a level. If the
     * conversion fails, then this method returns a DEBUG Level.
     *
     * @param mixed $arg
     * @param Log5PHP_Level $defaultLevel
     */
    static function toLevel($arg, Log5PHP_Level $defaultLevel = null)
    {
        if ($defaultLevel === null) {
            return Log5PHP_Level::toLevel($arg, Log5PHP_Level::getLevelDebug());
        }
        
        if (is_int($arg)) 
        {
            switch($arg) 
            {
                case LOG5PHP_LEVEL_ALL_INT:     return Log5PHP_Level::getLevelAll();
                case LOG5PHP_LEVEL_DEBUG_INT:   return Log5PHP_Level::getLevelDebug();
                case LOG5PHP_LEVEL_INFO_INT:    return Log5PHP_Level::getLevelInfo();
                case LOG5PHP_LEVEL_WARN_INT:    return Log5PHP_Level::getLevelWarn();
                case LOG5PHP_LEVEL_ERROR_INT:   return Log5PHP_Level::getLevelError();
                case LOG5PHP_LEVEL_FATAL_INT:   return Log5PHP_Level::getLevelFatal();
                case LOG5PHP_LEVEL_OFF_INT:     return Log5PHP_Level::getLevelOff();
                default:                        return $defaultLevel;
            }
        } 
        elseif (is_string($arg))
        {
            switch(strtoupper($arg))
            {
                case 'ALL':     return Log5PHP_Level::getLevelAll();
                case 'DEBUG':   return Log5PHP_Level::getLevelDebug();
                case 'INFO':    return Log5PHP_Level::getLevelInfo();
                case 'WARN':    return Log5PHP_Level::getLevelWarn();
                case 'ERROR':   return Log5PHP_Level::getLevelError();
                case 'FATAL':   return Log5PHP_Level::getLevelFatal();
                case 'OFF':     return Log5PHP_Level::getLevelOff();
                default:        return $defaultLevel;
            }
        }
        else
        {
            throw new Log5PHP_Exception_InvalidArgument('Level passed was not a string or int: ' . var_export($arg, true));
        }
                
    }
}
