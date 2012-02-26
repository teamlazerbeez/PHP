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
class Log5PHP_PatternConverter_Category extends Log5PHP_PatternConverter_Named {

    /**
     * Constructor
     *
     * @param Log5PHP_Utility_FormattingInfo $formattingInfo
     * @param integer $precision
     */
    function __construct(Log5PHP_Utility_FormattingInfo $formattingInfo, $precision)
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternConverter_Category::LoggerCategoryPatternConverter() precision='$precision'");    
    
        parent :: __construct($formattingInfo, $precision);
    }

    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function getFullyQualifiedName($event)
    {
      return $event->getLoggerName();
    }
}


