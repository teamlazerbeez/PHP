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
 * @subpackage src_main_php_Log5PHP_Utility
 */

/**
 * @ignore 
 */

/**
 * This class encapsulates the information obtained when parsing
 * formatting modifiers in conversion modifiers.
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Utility
 * @since 0.3
 */
class Log5PHP_Utility_FormattingInfo
{

    /**
     * int $min
     */
    private $min = -1;

    /**
     * int $max
     */
    private $max = 0x7FFFFFFF;

    /**
     * bool $leftAlign
     */
    private $leftAlign = false;

    function reset()
    {
        $this->min = -1;
        $this->max = 0x7FFFFFFF;
        $this->leftAlign = false;
    }

    /**
     * @return int
     */
    function getMin()
    {
        return $this->min;
    }

    /**
     * @return int
     */
    function getMax()
    {
        return $this->max;
    }

    /**
     * @return bool
     */
    function getLeftAlign()
    {
        return $this->leftAlign;
    }

    /**
     * @param bool $leftAlign
     */
    function setLeftAlign($leftAlign)
    {
        $this->leftAlign = $leftAlign;
    }

    /**
     * @param bool $min
     */
    function setMin($min)
    {
        $this->min = $min;
    }
    
    /**
     * @param bool $max
     */
    function setMax($max)
    {
        $this->max = $max;
    }
    
    function dump()
    {
        Log5PHP_InternalLog :: debug("Log5PHP_Utility_FormattingInfo::dump() min={$this->min}, max={$this->max}, leftAlign={$this->leftAlign}");
    }
}
