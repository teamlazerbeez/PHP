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
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Factory
 */
class Log5PHP_Factory_Appender extends Log5PHP_Factory_Cached
{
    protected static $namespace = 'appender';
    
    /**
     * @param string $class
     * @param string $name
     * @see Log5PHP_CachingFactory::createInstanceForKey
     */
    static function createAppenderWithName($class, $name)
    {
        return self :: createInstanceForKey($class, self :: $namespace, $name, $name);
    }
    
    /**
     * @param string $name
     * @return object
     * @see Log5PHP_CachingFactory::getInstanceForKey
     */
    static function getAppender($name)
    {
        return self :: getInstanceForKey(self :: $namespace, $name);
    }
    
    /**
     * @param string $name
     * @return bool
     */
    static function appenderExists($name)
    {
        return self :: keyExists(self :: $namespace, $name);
    }
    
}

