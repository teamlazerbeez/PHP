<?php
/**
 * log5php is a PHP port of the log4j java logging package.
 * 
 * <p>This framework is based on log4j (see {@link http://jakarta.apache.org/log4j log4j} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by log4j team 
 * (Ceki G�lc� as log4j project founder and 
 * {@link http://jakarta.apache.org/log4j/docs/contributors.html contributors}).</p>
 *
 * <p>PHP port, extensions and modifications by VxR. All rights reserved.<br>
 * For more information, please see {@link http://www.vxr.it/log4php/}.</p>
 *
 * <p>This software is published under the terms of the LGPL License
 * a copy of which has been included with this distribution in the LICENSE file.</p>
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_ObjectRenderer
 */

/**
 * @ignore 
 */

/**
 * Map class objects to an {@link Log5PHP_ObjectRenderer}.
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_ObjectRenderer
 * @since 0.3
 */
class Log5PHP_ObjectRenderer_Map {

    /**
     * @var array
     */
    private $map;

    /**
     * @var Log5PHP_ObjectRenderer_Default
     */
    private $defaultRenderer;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->map = array();
        $this->defaultRenderer = new Log5PHP_ObjectRenderer_Default();
    }

    /**
     * Add a renderer to a hierarchy passed as parameter.
     * Note that hierarchy must implement getObjectRendererMap() and setRenderer() methods.
     *
     * @param Log5PHP_LoggerRepository $repository a logger repository.
     * @param string $renderedClassName
     * @param string $renderingClassName
     */
    static function addRenderer($repository, $renderedClassName, $renderingClassName)
    {
        Log5PHP_InternalLog::debug("Log5PHP_ObjectRenderer_Map::addRenderer() Rendering class: [{$renderingClassName}], Rendered class: [{$renderedClassName}].");
        $renderer = Log5PHP_Factory_ObjectRenderer::getObjectRenderer($renderingClassName);
        if($renderer == null) {
            Log5PHP_InternalLog::warn("LoggerObjectRenderer_Map::addRenderer() Could not instantiate renderer [{$renderingClassName}].");
            return;
        } else {
            $repository->setRenderer($renderedClassName, $renderer);
        }
    }


    /**
     * Find the appropriate renderer for the class type of the
     * <var>o</var> parameter. 
     *
     * This is accomplished by calling the {@link getByObject()} 
     * method if <var>o</var> is object or using {@link Log5PHP_ObjectRenderer_Default}. 
     * Once a renderer is found, it is applied on the object <var>o</var> and 
     * the result is returned as a string.
     *
     * @param mixed $o
     * @return string 
     */
    function findAndRender($o)
    {
        if($o == null)
        {
            return null;
        } 
        else 
        {
            if (is_object($o))
            {
                $renderer = $this->getByObject($o);
                if ($renderer !== null) 
                {
                    return $renderer->doRender($o);
                } 
                else 
                {
                    return null;
                }
            } 
            else 
            {
                $renderer = $this->defaultRenderer;
                return $renderer->doRender($o);
            }
        }
    }

    /**
     * Syntactic sugar method that calls {@link PHP_MANUAL#get_class} with the
     * class of the object parameter.
     * 
     * @param mixed $o
     * @return string
     */
    function getByObject($o)
    {
        return ($o == null) ? null : $this->getByClassName(get_class($o));
    }


    /**
     * Search the parents of <var>clazz</var> for a renderer. 
     *
     * The renderer closest in the hierarchy will be returned. If no
     * renderers could be found, then the default renderer is returned.
     *
     * @param string $class
     * @return Log5PHP_ObjectRenderer
     */
    function getByClassName($class)
    {
        $r = null;
        for($c = strtolower($class); !empty($c); $c = get_parent_class($c)) {
            if (isset($this->map[$c])) {
                return  $this->map[$c];
            }
        }
        return $this->defaultRenderer;
    }

    /**
     * @return Log5PHP_ObjectRenderer_Default
     */
    function getDefaultRenderer()
    {
        return $this->defaultRenderer;
    }


    function clear()
    {
        $this->map = array();
    }

    /**
     * Register a {@link Log5PHP_ObjectRenderer} for <var>clazz</var>.
     * @param string $class
     * @param Log5PHP_ObjectRenderer $or
     */
    function put($class, $or)
    {
        $this->map[strtolower($class)] = $or;
    }
    
    /**
     * @param string $class
     * @return boolean
     */
    function rendererExists($class)
    {
        return class_exists($class);
    }
}
