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
 * @subpackage src_main_php_Log5PHP_PatternConverter
 */

/**
 * @ignore 
 */

/**
 * Log5PHP_PatternConverter is an abstract class that provides the formatting 
 * functionality that derived classes need.
 * 
 * <p>Conversion specifiers in a conversion patterns are parsed to
 * individual PatternConverters. Each of which is responsible for
 * converting a logging event in a converter specific manner.</p>
 * 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_PatternConverter
 * @since 0.3
 */
abstract class Log5PHP_PatternConverter_Base {

    /**
     * @var Log5PHP_PatternConverter next converter in converter chain
     */
    private $next = null;
    
    private $min = -1;
    private $max = 0x7FFFFFFF;
    
    /**
     * @var boolean
     */
    private $leftAlign = false;

    /**
     * Constructor 
     *
     * @param Log5PHP_Utility_FormattingInfo $fi
     */
    function __construct(Log5PHP_Utility_FormattingInfo $fi = null) 
    {  
        if ($fi !== null) {
            $this->min = $fi->getMin();
            $this->max = $fi->getMax();
            $this->leftAlign = $fi->getLeftAlign();
        }
    }
    
    /**
     * @return next PatternConverter in the chain
     */
    final function getNext()
    {
        return $this->next;
    }
    
    /**
     * @param Log5PHP_PatternConverter_Base $next next element in the chain
     */
    final function setNext(Log5PHP_PatternConverter_Base $next)
    {
        $this->next = $next;
    }
  
    /**
     * Derived pattern converters must override this method in order to
     * convert conversion specifiers in the correct way.
     *
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    abstract function convert(Log5PHP_LogEvent $event);

    /**
     * A template method for formatting in a converter specific way. Handles the
     * min/max/left align aspects after the element has been converted to the
     * pattern's representation.
     *
     * @param string $sbuf string buffer of previously parsed pattern elements
     * @param Log5PHP_LogEvent $e event to interpret via the pattern and append
     * to the sbuf
     * @return the modified sbuf
     */
    function format($sbuf, $e)
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternConverter::format() sbuf='$sbuf'");    
    
        $s = $this->convert($e);
        Log5PHP_InternalLog::debug("Log5PHP_PatternConverter::format() converted event is '$s'");    
        
    
        if($s == null or empty($s)) {
            if($this->min > 0)
                $sbuf .= self :: spacePad($sbuf, $this->min);
            return $sbuf;
        }
        
        $len = strlen($s);
    
        if($len > $this->max) 
        {
            $sbuf .= substr($s , 0, ($len - $this->max));
        }
        elseif($len < $this->min)
        {
            if($this->leftAlign) {    
                $sbuf .= $s;
                $sbuf .= self :: spacePad($sbuf, ($this->min - $len));
            } else {
                $sbuf .= self :: spacePad($sbuf, ($this->min - $len));
                $sbuf .= $s;
            }
        }
        else
        {
            $sbuf .= $s;
        }
        
        return $sbuf;
    }    


    /**
     * @param string    $sbuf     string buffer
     * @param integer   $length    pad length
     * @return string the spaces necessary to pad $sbuf out to $length
     * @todo reimplement using PHP string functions
     */
    private static function spacePad($sbuf, $length)
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternConverter::spacePad() sbuf='$sbuf' len='$length'");        
        return str_repeat(' ', strlen($sbuf) - $length);
    }
}

