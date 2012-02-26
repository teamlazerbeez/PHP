<?php
/**
 * Global configuration for Genius Open Source.
 */

// Make notices error
error_reporting(E_ALL);

// Set so that all mb_ string functions use UTF-8 encoding.
mb_internal_encoding('UTF-8');
date_default_timezone_set('GMT');

// Root is the top of the repository
define('GOS_ROOT', dirname(dirname(__FILE__)) . '/');

// Setup logging framework
define('LOG5PHP_LINE_SEP', "\n");
define('LOG5PHP_DIR', GOS_ROOT . 'Log5PHP/src/main/php');
require_once(LOG5PHP_DIR . '/autoload.inc.php');
spl_autoload_register('Log5PHP_autoload');

require_once(GOS_ROOT . 'Core/autoloader.inc.php');
