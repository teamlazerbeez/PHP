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
 * @subpackage src_main_php_Log5PHP_Appender
 */

/**
 * @ignore 
 */

/**
 * Log5PHP_Appender_DailyFile appends log events to a file ne.
 *
 * A formatted version of the date pattern is used as to create the file name
 * using the {@link PHP_MANUAL#sprintf} function.
 * <p>Parameters are {@link $datePattern}, {@link $file}. Note that file 
 * parameter should include a '%s' identifier and should always be set 
 * before {@link $file} param.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_DailyFile extends Log5PHP_Appender_File
{

    /**
     * Format date. 
     * It follows the {@link PHP_MANUAL#date()} formatting rules and <b>should always be set before {@link $file} param</b>.
     * @var string
     */
    private $datePattern = "Ymd";

    /**
    * Sets date format for the file name.
    * @param string $format a regular date() string format
    */
    function setDatePattern($format)
    {
        $this->datePattern = $format;
    }

    /**
    * @return string returns date format for the filename
    */
    function getDatePattern()
    {
        return $this->datePattern;
    }

    /**
    * The File property takes a string value which should be the name of the file to append to.
    * Sets and opens the file where the log output will go.
    *
    * @see Log5PHP_Appender_File::setFile()
    */
    function setFile($fileName, $append = true)
    {
            parent :: setFile(sprintf((string) $fileName, date($this->getDatePattern())), $append);
    }

}
