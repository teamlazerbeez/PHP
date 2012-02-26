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
class Log5PHP_PatternConverter_Location extends Log5PHP_PatternConverter_Base {

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
      Log5PHP_InternalLog::debug("Log5PHP_PatternConverter_Location::LoggerLocationPatternConverter() type='$type'");

      parent :: __construct($formattingInfo);
      $this->type = $type;
    }

    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function convert(Log5PHP_LogEvent $event)
    {
        $locationInfo = $event->getLocationInfo();
        switch($this->type) {
            case LOG5PHP_LOGGER_PATTERN_PARSER_FULL_LOCATION_CONVERTER:
                return $locationInfo->getFullInfo();
            case LOG5PHP_LOGGER_PATTERN_PARSER_METHOD_LOCATION_CONVERTER:
                return $locationInfo->getMethodName();
            case LOG5PHP_LOGGER_PATTERN_PARSER_LINE_LOCATION_CONVERTER:
                return $locationInfo->getLineNumber();
            case LOG5PHP_LOGGER_PATTERN_PARSER_FILE_LOCATION_CONVERTER:
                return $locationInfo->getFileName();
            default:
                return '';
        }
    }
}


