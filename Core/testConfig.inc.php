<?php
/**
 * Configuration file for test classes
 */
require_once dirname(dirname(__FILE__)) .'/Core/gosConfig.inc.php';

// No memory limit
ini_set('memory_limit', -1);
// No time limit
set_time_limit (0);

define('LOG5PHP_CONFIGURATION', GOS_ROOT . 'Core/test/log5php.xml');
