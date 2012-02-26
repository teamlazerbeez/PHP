<?php

require_once dirname(__FILE__).'/../testconfig.inc.php';

class LoggerObjectRendererTest extends PHPUnit_Framework_TestCase {
        
    protected function setUp() {
    }
    
    protected function tearDown() {
    }
    
    public function testFactory() {
            $renderer = Log5PHP_Factory_ObjectRenderer::getObjectRenderer('LoggerDefaultRenderer');
            self::assertType('LoggerDefaultRenderer', $renderer);
    }

}
?>
