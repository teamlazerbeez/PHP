<?php

require_once dirname(__FILE__).'/../testconfig.inc.php';

class LoggerRendererMapTest extends PHPUnit_Framework_TestCase {
        
    protected function setUp() {
    }
    
    protected function tearDown() {
    }
    
    public function testAddRenderer() {
        
        $hierarchy = Log5PHP_LoggerRepository::getInstance();
        
        //print_r($hierarchy);
        
        Log5PHP_ObjectRenderer_Map::addRenderer($hierarchy, 'string', 'LoggerDefaultRenderer');
        
        //print_r($hierarchy);
        
        throw new PHPUnit_Framework_IncompleteTestError();
    }
    
    public function testFindAndRender() {
        throw new PHPUnit_Framework_IncompleteTestError();
    }
    
    public function testGetByObject() {
        throw new PHPUnit_Framework_IncompleteTestError();
    }
    
    public function testGetByClassName() {
        throw new PHPUnit_Framework_IncompleteTestError();
    }
    
    public function testGetDefaultRenderer() {
        throw new PHPUnit_Framework_IncompleteTestError();
    }
    
    public function testClear() {
        throw new PHPUnit_Framework_IncompleteTestError();
    }
    
    public function testPut() {
        throw new PHPUnit_Framework_IncompleteTestError();
    }
    
    public function testRendererExists() {
        throw new PHPUnit_Framework_IncompleteTestError();
    }

}
?>
