<?php
/**
 * @copyright Copyright © 2008, Genius.com 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_test_php_unit_layouts
 *
 * $Revision:: 26050                                      $
 * $Date:: 2008-12-18 17:10:10 -0800 (Thu, 18 Dec 2008)   $
 * $Author:: bhewitt                                      $
 */

/**
 * @ignore
 */
require_once dirname(__FILE__) . '/../testconfig.inc.php';
require_once dirname(dirname(dirname(__FILE__))) . '/stubs/Buffer.cls.php';

/**
 * @package external_Log5PHP
 * @subpackage src_test_php_unit_layouts
 */
class BuzzsawXMLTest extends PHPUnit_Framework_TestCase
{
    
    function setUp()
    {
        $this->layout = new Log5PHP_Layout_BuzzsawXML();
        
        $this->appender = new Log5PHP_Appender_Buffer('buf');
        $this->appender->setLayout($this->layout);
        
        // logger setup
        $this->logger = new Log5PHP_Logger('testLogger');
        $this->logger->addAppender($this->appender);
        $this->logger->setLevel(Log5PHP_Level::getLevelDebug());
        $this->logger->setRepository(Log5PHP_LoggerRepository::getInstance());
    }
    
    function testOutput() 
    {
        $this->logger->debug('msg');
        $fileContents = file_get_contents(dirname(__FILE__) . '/expectedBuzzsawXMLOutput.txt');
        
        $event = $this->appender->getEvent(0);                                                                                                                                
        
        // have to replace process id and timestamp info
        $fileContents = preg_replace('/<process>.*<\/process>/', '<process>' . getmypid() . '</process>', $fileContents);
        $fileContents = preg_replace('/<timestamp>.*<\/timestamp>/', '<timestamp>' . $event->getTimeISO8601() . '</timestamp>', $fileContents);
        $fileContents = preg_replace('/<fileName>.*<\/fileName>/', '<fileName>' . __FILE__ . '</fileName>', $fileContents);
        $fileContents = preg_replace('/<hostname>.*<\/hostname>/', '<hostname>' . exec('hostname') . '</hostname>', $fileContents);
        
        
        
        $this->assertEquals($fileContents, $this->appender->getBuffer(0)); 
    }
    
}
?>
