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
class Log5PHP_PatternConverter_Basic extends Log5PHP_PatternConverter_Base {

    /**
     * @var integer
     */
    private $type;

    /**
     * Constructor
     *
     * @param Log5PHP_Utility_FormattingInfo $formattingInfo
     * @param integer $type
     */
    function __construct(Log5PHP_Utility_FormattingInfo $formattingInfo, $type)
    {
      Log5PHP_InternalLog::debug("Log5PHP_PatternConverter_Basic::LoggerBasicPatternConverter() type='$type'");    
    
      parent :: __construct($formattingInfo);
      $this->type = $type;
    }

    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function convert(Log5PHP_LogEvent $event)
    {
        switch($this->type) {
            case LOG5PHP_LOGGER_PATTERN_PARSER_RELATIVE_TIME_CONVERTER:
                $timeStamp = $event->getTimeStampFloat();
                $startTime = Log5PHP_LogEvent::getStartTime();
                return (string)(int)($timeStamp * 1000 - $startTime * 1000);
                
            case LOG5PHP_LOGGER_PATTERN_PARSER_THREAD_CONVERTER:
                return $event->getThreadName();

            case LOG5PHP_LOGGER_PATTERN_PARSER_LEVEL_CONVERTER:
                $level = $event->getLevel();
                return $level->toString();

            case LOG5PHP_LOGGER_PATTERN_PARSER_NDC_CONVERTER:
                return $event->getNDC();

            case LOG5PHP_LOGGER_PATTERN_PARSER_MESSAGE_CONVERTER:
                return $event->getRenderedMessage();
                
            default: 
                return '';
        }
    }
}


