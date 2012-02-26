<?php
/**
 * @copyright Copyright © 2008, Genius.com 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Factory
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
 * @subpackage src_main_php_Log5PHP_Factory
 */
class Log5PHP_Factory_Layout extends Log5PHP_Factory_Simple
{
    public static function getNewLayout($className)
    {
        return self :: getNewInstance($className);
    }
}

