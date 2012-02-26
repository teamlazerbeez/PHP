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
 * FileAppender appends log events to a file.
 *
 * Parameters are ({@link $fileName} but option name is <b>file</b>), 
 * {@link $append}.
 *
 * @version $Revision: 32923 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_File extends Log5PHP_Appender_Base {

    /**
     * @var boolean if {@link $file} exists, appends events.
     */
    private $append = true;  

    /**
     * @var string the file name used to append events
     */
    protected $fileName;

    /**
     * @var mixed file resource
     */
    protected $fp = false;
    
    /**
     */
    protected $requiresLayout = true;
    
    function activateOptions()
    {
        $fileName = $this->getFile();
        Log5PHP_InternalLog::debug("Log5PHP_Appender_File::activateOptions() opening file '{$fileName}'");
        
        // this generates an E_WARNING in addition to returning false, because php is fucking stupid
        set_error_handler('Log5PHP_null_error_handler');
        $this->fp = fopen($fileName, ($this->getAppend()? 'a':'w'));
        restore_error_handler();
        
        if ($this->fp) {
            if ($this->getAppend())
                fseek($this->fp, 0, SEEK_END);
            fwrite($this->fp, $this->layout->getHeader());
        } else {
        }
    }
    
    function close()
    {
        if ($this->fp and $this->layout !== null)
        {
            fwrite($this->fp, $this->layout->getFooter());
        }
                    
        $this->closeFile();
        $this->closed = true;
    }

    /**
     * Closes the previously opened file.
     */
    function closeFile() 
    {
        if ($this->fp)
        {
            fclose($this->fp);
        }
    }
    
    /**
     * @return boolean
     */
    function getAppend()
    {
        return $this->append;
    }

    /**
     * @return string
     */
    function getFile()
    {
        return $this->getFileName();
    }
    
    /**
     * @return string
     */
    function getFileName()
    {
        return $this->fileName;
    } 
 
    /**
     * Close any previously opened file and call the parent's reset.
     */
    function reset()
    {
        $this->closeFile();
        $this->fileName = null;
        parent::reset();
    }

    function setAppend($flag)
    {
        $this->append = Log5PHP_Utility_OptionConverter::toBoolean($flag, true);        
    } 
  
    /**
     * Sets and opens the file where the log output will go. Convenience wrapper
     * around setFile and setAppend.
     *
     * This is an overloaded method. It can be called with:
     * - setFile(string $fileName) to set filename.
     * - setFile(string $fileName, boolean $append) to set filename and append.
     */
    function setFile($fileName, $append = true)
    {
        $this->setFileName($fileName);
        $this->setAppend($append);
    }
    
    function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    protected function append(Log5PHP_LogEvent $event)
    {
        if ($this->fp and $this->layout !== null) {

            Log5PHP_InternalLog::debug("Log5PHP_Appender_File::append()");
        
            set_error_handler('Log5PHP_null_error_handler');
            fwrite($this->fp, $this->layout->format($event));
            restore_error_handler();
            
        } 
    }
}
