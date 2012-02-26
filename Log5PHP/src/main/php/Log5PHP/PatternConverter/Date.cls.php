<?php
/**
 * @copyright Copyright © 2007, Genius.com 
 * @version $Revision: 43879 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_PatternConverter
 *
 * $Revision:: 43879                                      $
 * $Date:: 2010-07-19 17:54:18 -0700 (Mon, 19 Jul 2010)   $
 * $Author:: mwudka                                       $
 */

/**
 * @ignore
 */

/**
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_PatternConverter
 */
class Log5PHP_PatternConverter_Date extends Log5PHP_PatternConverter_Base {

    /**
     * @var string
     */
    private $df;
    
    /**
     * Constructor
     *
     * @param Log5PHP_Utility_FormattingInfo $formattingInfo
     * @param string $df date format
     */
    function __construct(Log5PHP_Utility_FormattingInfo $formattingInfo, $df)
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternConverter_Date::LoggerDatePatternConverter() dateFormat='$df'");    
    
        parent :: __construct($formattingInfo);
        $this->df = $df;
    }

    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function convert(Log5PHP_LogEvent $event)
    {
        $paddedUsecs = str_pad($event->getTimeUsecs(), 6, '0', STR_PAD_LEFT);

        $dfWithMicrosecs = str_replace('u', $paddedUsecs, $this->df);
        
        return date($dfWithMicrosecs, $event->getTimeStampFloat());   
    }
}


