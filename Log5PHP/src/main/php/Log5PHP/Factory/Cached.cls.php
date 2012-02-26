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
 * Extends simple factory to add support for caching the object associated with
 * a key. The object caches are namespaced by the parent directory that contains
 * the source for their class defs so that this factory can be used by multiple
 * subclasses, all of which need their own discrete caches.
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Factory
 */
class Log5PHP_Factory_Cached extends Log5PHP_Factory_Simple
{
    /**
     * @var array of arrays [namespace] => ([name] => [object] cache)
     */
    protected static $instanceCache = array();
    
    /**
     * Nuke the object cache. Useful when resetting the configuration, for
     * instance.
     */
    public static function resetInstanceCache()
    {
        self :: $instanceCache = array();
    }
    
    /**
     * @param string $namespace
     * @param string $key
     * @return bool
     */
    protected static function keyExists($namespace, $key)
    {
        if (!array_key_exists($namespace, self :: $instanceCache))
        {
            return false;
        }
        
        $namespaceCache = self :: $instanceCache[$namespace];
        
        if (!array_key_exists($key, $namespaceCache))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the object associated with the key
     * 
     * @param string $namespace
     * @param string $key
     * @return object
     * @throws Log5PHP_InvalidArgumentException if nothing is set for the key
     */
    protected static function getInstanceForKey($namespace, $key)
    {
        #echo '=> get ' . $namespace . '[' . $key . "]\n";
        if (!array_key_exists($namespace, self :: $instanceCache))
        {
            throw new Log5PHP_Exception_InvalidArgument('Object for key \'' . $key . '\' has not yet been created');
        }
        
        $namespaceCache = self :: $instanceCache[$namespace];
        
        if (!array_key_exists($key, $namespaceCache))
        {
            throw new Log5PHP_Exception_InvalidArgument('Object for key \'' . $key . '\' has not yet been created');
        }
        
        return $namespaceCache[$key];
    }
    
    /**
     * Create the object for a given key
     * @param string $classname class to create
     * @param string $namespace namespace to create the object in
     * @param string $key key to associate the new object with
     * @param mixed $param optional parameter to the class's constructor
     * @throws Log5PHP_InvalidArgumentException if an object is already set for
     * the key
     * @return object the created object
     */    
    protected static function createInstanceForKey($classname, $namespace, $key, $param = null)
    {
        if (!array_key_exists($namespace, self :: $instanceCache))
        {
            #echo "creating ns $namespace\n";
            self :: $instanceCache[$namespace] = array();
        }
        
        /*
         * Work around php retardation: have to get this array by reference so
         * that writes will stick. No doubt Zend will eventually suck the entire
         * universe into their gaping lack of clue.
         */
        $namespaceCache =& self :: $instanceCache[$namespace];
        
        if (array_key_exists($key, $namespaceCache))
        {
            #echo "tried to create obj for key $key that already exists in ns $namespace\n";
            throw new Log5PHP_Exception_InvalidArgument('Object for key \'' . $key . '\' has already been created');
        }
        
        #echo '<= add ' . $namespace . '[' . $key . "]\n";
        $namespaceCache[$key] = self :: getNewInstance($classname, $param);
        #echo "namespace cache:\n";
        #var_dump($namespaceCache);
        #echo "top level:\n";
        #var_dump(self :: $instanceCache);
        return self :: getInstanceForKey($namespace, $key);
    }
}

