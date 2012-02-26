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
abstract class Log5PHP_PatternConverter_Named extends Log5PHP_PatternConverter_Base {

    /**
     * @var integer
     */
    private $precision;

    /**
     * Constructor
     *
     * @param Log5PHP_Utility_FormattingInfo $formattingInfo
     * @param integer $precision
     */
    function __construct(Log5PHP_Utility_FormattingInfo $formattingInfo, $precision)
    {
      Log5PHP_InternalLog::debug("Log5PHP_PatternConverter_Named::LoggerNamedPatternConverter() precision='$precision'");    
    
      parent :: __construct($formattingInfo);
      $this->precision =  $precision;
    }

    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    abstract function getFullyQualifiedName($event);

    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    final function convert(Log5PHP_LogEvent $event)
    {
        $n = $this->getFullyQualifiedName($event);
        if ($this->precision <= 0) {
            return $n;
        } else {
            $len = strlen($n);
            
            // We substract 1 from 'len' when assigning to 'end' to avoid out of
            // bounds exception in return r.substring(end+1, len). This can happen if
            // precision is 1 and the category name ends with a dot.
            $end = $len -1 ;
            for($i = $this->precision; $i > 0; $i--) {
                $end = strrpos(substr($n, 0, ($end - 1)), '.');
                if ($end == false)
                    return $n;
            }
            return substr($n, ($end + 1), $len);
        }
    }
}


