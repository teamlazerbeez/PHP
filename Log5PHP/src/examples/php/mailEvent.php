<?php
/**
 * @author Marshall Pierce <marshall@genius.com>
 * @copyright Copyright © 2008, Genius.com
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_examples_php
 *
 * $Revision:: 26050                                      $
 * $Date:: 2008-12-18 17:10:10 -0800 (Thu, 18 Dec 2008)   $
 * $Author:: bhewitt                                      $
 */

define('LOG5PHP_DIR', dirname(__FILE__).'/../../main/php');
define('LOG5PHP_LINE_SEP', "\n");
define('LOG5PHP_CONFIGURATION', dirname(__FILE__).'/mailEvent.xml');


require_once LOG5PHP_DIR . '/autoload.inc.php';
spl_autoload_register('Log5PHP_autoload');


$logger = Log5PHP_Manager::getRootLogger();
$logger->fatal("Some critical message!");

?>
