<?php
/**
 * @copyright Copyright © 2008, Genius.com 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_test_php_stubs
 *
 * $Revision:: 26050                                      $
 * $Date:: 2008-12-18 17:10:10 -0800 (Thu, 18 Dec 2008)   $
 * $Author:: bhewitt                                      $
 */

/**
 * @ignore
 */

/**
 * useful for testing or if you want to get the string contents of an appender
 * for whatever reason
 * 
 * @package external_Log5PHP
 * @subpackage src_test_php_stubs
 */
class Log5PHP_Appender_Buffer extends Log5PHP_Appender_Base
{
    /**
     * @var array list of event outputs
     */
    private $buffer = array();
    
    /**
     * @var array list of events
     */
    private $events = array();
    
    /**
     */
    protected $requiresLayout = true;
    
    /**
     * Append the formatted event to the internal buffer
     */
    protected function append(Log5PHP_LogEvent $event)
    {
        $this->buffer[] = $this->layout->format($event);
        $this->events[] = $event;
    }
    
    /**
     * @param int which event buffer to get
     * @return string contents of buffer
     */
    public function getBuffer($index)
    {
        return $this->buffer[$index];
    }
    
    /**
     * @param int which event to get
     * @return Log5PHP_LogEvent
     */
    public function getEvent($index)
    {
        return $this->events[$index];
    }
    
    /**
     * Reset the buffer to ''
     */
    public function clearBuffer()
    {
        $this->buffer = '';
    }
}
?>
