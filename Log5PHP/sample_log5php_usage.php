<?php

define('LOG5PHP_CONFIGURATION', 'sample_log5php_config.xml');
define('LOG5PHP_LINE_SEP', "\n");
define('LOG5PHP_DIR', dirname(__FILE__) . '/src/main/php');

require_once LOG5PHP_DIR . '/autoload.inc.php';

spl_autoload_register('Log5PHP_autoload');

$logger = Log5PHP_Manager::getRootLogger();

Log5PHP_MDC::put('texture', 'fuzzy');
Log5PHP_MDC::put('weather', 'sunny');
Log5PHP_NDC::push('First ndc frame');
Log5PHP_NDC::push('A second ndc frame');

$logger->debug('Doh!');
$logger->fatal('Oh noes!');

Log5PHP_Manager::shutdown();

?>
