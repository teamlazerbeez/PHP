<?php
/**
 * @copyright Copyright © 2007, Genius.com 
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
 * Wrap the basic functionality of loading classes and creating objects for a
 * given family of objects
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Factory
 */
class Log5PHP_Factory_Simple
{
    
    /**
     * If the source for the class has not been loaded yet, this will attempt to
     * load it
     * 
     * @param string $classname class name to instantiate
     * @param mixed $param optional parameter
     */    
    static protected function getNewInstance($classname, $param = null)
    {
        if (!class_exists($classname))
        {
            throw new Log5PHP_Error_ClassNotFound('Couldn\'t find classname ' . $classname);
        }
        
        if ($param === null)
        {
            return new $classname();
        }
        else
        {
            return new $classname($param);
        }
    }
    
}

