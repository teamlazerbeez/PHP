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
 * @subpackage src_main_php_Log5PHP_Filter
 */

/**
 * @ignore 
 */

/**
 * This is a very simple filter based on string matching.
 * 
 * <p>The filter admits two options {@link $stringToMatch} and
 * {@link $acceptOnMatch}. If there is a match (using {@link PHP_MANUAL#strpos}
 * between the value of the {@link $stringToMatch} option and the message 
 * of the {@link Log5PHP_LogEvent},
 * then the {@link decide()} method returns {@link LOG5PHP_LOGGER_FILTER_ACCEPT} if
 * the <b>AcceptOnMatch</b> option value is true, if it is false then
 * {@link LOG5PHP_LOGGER_FILTER_DENY} is returned. If there is no match, {@link LOG5PHP_LOGGER_FILTER_NEUTRAL}
 * is returned.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Filter
 * @since 0.3
 */
class Log5PHP_Filter_StringMatch extends Log5PHP_Filter {
  
    /**
     * @var boolean
     */
    private $acceptOnMatch = true;

    /**
     * @var string
     */
    private $stringToMatch = null;
  
    /**
     * @return boolean
     */
    function getAcceptOnMatch()
    {
        return $this->acceptOnMatch;
    }
    
    /**
     * @param mixed $acceptOnMatch a boolean or a string ('true' or 'false')
     */
    function setAcceptOnMatch($acceptOnMatch)
    {
        $this->acceptOnMatch = is_bool($acceptOnMatch) ? 
            $acceptOnMatch : 
            (bool)(strtolower($acceptOnMatch) == 'true');
    }
    
    /**
     * @return string
     */
    function getStringToMatch()
    {
        return $this->stringToMatch;
    }
    
    /**
     * @param string $s the string to match
     */
    function setStringToMatch($s)
    {
        $this->stringToMatch = $s;
    }

    /**
     * @return integer a {@link LOGGER_FILTER_NEUTRAL} is there is no string match.
     */
    function decide($event)
    {
        $msg = $event->getRenderedMessage();
        
        if($msg === null or  $this->stringToMatch === null)
            return LOG5PHP_LOGGER_FILTER_NEUTRAL;
        if( strpos($msg, $this->stringToMatch) !== false ) {
            return ($this->acceptOnMatch) ? LOG5PHP_LOGGER_FILTER_ACCEPT : LOG5PHP_LOGGER_FILTER_DENY ; 
        }
        return LOG5PHP_LOGGER_FILTER_NEUTRAL;        
    }
}
