<?php
/**
 * @copyright Copyright © 2008, Genius.com 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 *
 * $Revision:: 26050                                      $
 * $Date:: 2008-12-18 17:10:10 -0800 (Thu, 18 Dec 2008)   $
 * $Author:: bhewitt                                      $
 */

/**
 * If the classname starts with the Log5PHP prefix, it will try to load it (and
 * throw an exception if it cannot). If the classname does not start with the
 * proper prefix, this function will simply return.
 * 
 * Put this somewhere in your init code:
 * define('LOG5PHP_DIR', 'path/to/log5php_code/src/main/php')
 * spl_autoload_register ('Log5PHP_autoload');
 * 
 * @param string $className class to load
 */
function Log5PHP_autoload($className)
{
    $classPrefix = 'Log5PHP';
    
    $fileExt = '.php';
    
    # not a class we know about, so just return
    if (substr($className, 0, strlen($classPrefix)) !== $classPrefix)
    {
        return;
    }
    
    # Split the classname on _
    $classChunks = explode('_', $className);
    
    $path = LOG5PHP_DIR . '/' . implode('/', $classChunks);
    
    $classPath          = $path . '.cls'    . $fileExt;
    $abstractClassPath  = $path . '.acls'   . $fileExt;
    $interfacePath      = $path . '.itf'    . $fileExt;
    
    # set to true if any of the source paths exist
    $foundSource = false;
    
    foreach(array($classPath, $abstractClassPath, $interfacePath) as $sourcePath)
    {
        if (file_exists($sourcePath))
        {
            $foundSource = true;
            require_once($sourcePath);
            break;
        }
    }
    
    if (!$foundSource)
    {
        throw new Exception('Could not load class ' . $className);
    }
}

/**
 * php's fopen throws an E_WARNING in addition to returning false on errors, so
 * to avoid pointlessly killing a script because a file couldn't be opened, we
 * use this no-op error handler around fopen, then restore the previous error
 * handler
 */
function Log5PHP_null_error_handler($errno, $errmsg)
{
    // do nothing
}
