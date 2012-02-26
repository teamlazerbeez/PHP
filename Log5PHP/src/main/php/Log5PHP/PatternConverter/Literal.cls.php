<?php
/**
 * @copyright Copyright © 2007, Genius.com 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_PatternConverter
 *
 * $Revision:: 26050                                      $
 * $Date:: 2008-12-18 17:10:10 -0800 (Thu, 18 Dec 2008)   $
 * $Author:: bhewitt                                      $
 */

/**
 * @ignore
 */

/**
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_PatternConverter
 */
class Log5PHP_PatternConverter_Literal extends Log5PHP_PatternConverter_Base {
    
    /**
     * @var string
     */
    private $literal;

    /**
     * Constructor
     *
     * @param string $value
     */
    function __construct($value)
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternConverter_Literal::LoggerLiteralPatternConverter() value='$value'");    
    
        $this->literal = $value;
    }

    /**
     * @param string $sbuf
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function format($sbuf, $event)
    {
        return $sbuf . $this->convert($event);
    }

    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function convert(Log5PHP_LogEvent $event)
    {
      return $this->literal;
    }
}


