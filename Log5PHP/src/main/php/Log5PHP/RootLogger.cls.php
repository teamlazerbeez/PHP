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
 */

/**
 * @ignore 
 */

/**
 * The root logger. Setting a null value to the level of the root category may
 * have catastrophic results.
 *
 * @version $Revision: 37220 $
 * @package external_Log5PHP
 * @see Log5PHP_Logger
 */
class Log5PHP_RootLogger extends Log5PHP_Logger {

    /**
     * @var string name of logger 
     */
    protected $name   = 'root';

    /**
     * @var object must be null for Log5PHP_Root
     */
    protected $parent = null;
    
    /**
     * Constructor
     *
     * @param integer $level initial log level
     */
    function __construct($level = null)
    {
        parent :: __construct($this->name);
        if ($level == null)
            $level = Log5PHP_Level::getLevelAll();
        $this->setLevel($level);
    } 

    /**
     * Always returns false.
     * Because Log5PHP_Root has no parents, it returns false.
     * @param Logger $parent
     * @return boolean
     */
    function setParent(Log5PHP_Logger $parent)
    {
        return false;
    }  
}
