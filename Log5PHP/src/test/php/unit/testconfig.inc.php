<?php
if (!defined('LOG5PHP_DIR'))
{
    define('LOG5PHP_DIR', dirname(__FILE__) . '/../../../main/php');
}

if (!defined('LOG5PHP_DEFAULT_INIT_OVERRIDE'))
{
    define('LOG5PHP_DEFAULT_INIT_OVERRIDE', true);
}

require_once LOG5PHP_DIR . '/autoload.inc.php';
spl_autoload_register('Log5PHP_autoload');

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';
?>
