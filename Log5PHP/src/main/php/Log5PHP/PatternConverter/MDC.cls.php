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
class Log5PHP_PatternConverter_MDC extends Log5PHP_PatternConverter_Base
{

	/**
	 * @var string
	 */
	private $key;

	/**
	 * Constructor
	 *
	 * @param Log5PHP_Utility_FormattingInfo $formattingInfo
	 * @param string $key
	 */
	function __construct(Log5PHP_Utility_FormattingInfo $formattingInfo, $key)
	{
		Log5PHP_InternalLog :: debug("Log5PHP_PatternConverter_MDC::LoggerMDCPatternConverter() key='$key'");
        
		parent :: __construct($formattingInfo);
		$this->key = $key;
	}

	/**
	 * @param Log5PHP_LogEvent $event
	 * @return string
	 */
	function convert(Log5PHP_LogEvent $event)
	{
		return $event->getMDC($this->key);
	}
}

