<?php
require_once dirname(__FILE__) . '/../testconfig.inc.php';

class DefaultRendererMockObject
{
    private $a;
    protected $b;
    public $c;
}

class LoggerDefaultRendererTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testDoRender()
    {
        $class = new DefaultRendererMockObject();
        $renderer = new Log5PHP_ObjectRenderer_Default();
        self :: assertEquals(var_export($class, true), $renderer->doRender($class));
    }

}
?>
