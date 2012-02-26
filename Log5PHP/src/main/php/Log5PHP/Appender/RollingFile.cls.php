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
 * Log5PHP_Appender_RollingFile extends Log5PHP_Appender_File to backup the log files 
 * when they reach a certain size.
 *
 * <p>Parameters are {@link $maxFileSize}, {@link $maxBackupIndex}.</p> 
 *
 * <p>Contributors: Sergio Strampelli.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_RollingFile extends Log5PHP_Appender_File {

    /**
     * Set the maximum size that the output file is allowed to reach
     * before being rolled over to backup files.
     *
     * <p>In configuration files, the <var>MaxFileSize</var> option takes a
     * long integer in the range 0 - 2^63. You can specify the value
     * with the suffixes "KB", "MB" or "GB" so that the integer is
     * interpreted being expressed respectively in kilobytes, megabytes
     * or gigabytes. For example, the value "10KB" will be interpreted
     * as 10240.</p>
     * <p>The default maximum file size is 10MB.</p>
     *
     * <p>Note that MaxFileSize cannot exceed <b>2 GB</b>.</p>
     *
     * @var integer
     */
    private $maxFileSize = 10485760;
    
    /**
     * Set the maximum number of backup files to keep around.
     * 
     * <p>The <var>MaxBackupIndex</var> option determines how many backup
     * files are kept before the oldest is erased. This option takes
     * a positive integer value. If set to zero, then there will be no
     * backup files and the log file will be truncated when it reaches
     * MaxFileSize.</p>
     * <p>There is one backup file by default.</p>
     *
     * @var integer 
     */
    private $maxBackupIndex  = 1;
    
    /**
     * @var string the filename expanded
     * @access private
     */
    private $expandedFileName = null;

    /**
     * Returns the value of the MaxBackupIndex option.
     * @return integer 
     */
    function getExpandedFileName() {
        return $this->expandedFileName;
    }

    /**
     * Returns the value of the MaxBackupIndex option.
     * @return integer 
     */
    function getMaxBackupIndex() {
        return $this->maxBackupIndex;
    }

    /**
     * Get the maximum size that the output file is allowed to reach
     * before being rolled over to backup files.
     * @return integer
     */
    function getMaximumFileSize() {
        return $this->maxFileSize;
    }

    /**
     * Implements the usual roll over behaviour.
     *
     * <p>If MaxBackupIndex is positive, then files File.1, ..., File.MaxBackupIndex -1 are renamed to File.2, ..., File.MaxBackupIndex. 
     * Moreover, File is renamed File.1 and closed. A new File is created to receive further log output.
     * 
     * <p>If MaxBackupIndex is equal to zero, then the File is truncated with no backup files created.
     */
    function rollOver()
    {
        // If maxBackups <= 0, then there is no file renaming to be done.
        if($this->maxBackupIndex > 0) {
            $fileName = $this->getExpandedFileName();
            // Delete the oldest file, to keep Windows happy.
            $file = $fileName . '.' . $this->maxBackupIndex;
            if (is_writable($file))
                unlink($file);
            // Map {(maxBackupIndex - 1), ..., 2, 1} to {maxBackupIndex, ..., 3, 2}
            for ($i = $this->maxBackupIndex - 1; $i >= 1; $i--) {
                $file = $fileName . "." . $i;
                if (file_exists($file)) {
                    $target = $fileName . '.' . ($i + 1);
                    rename($file, $target);
                }
            }
    
            // Rename fileName to fileName.1
            $target = $fileName . ".1";
    
            $this->closeFile(); // keep windows happy.
    
            $file = $fileName;
            
            # Only rename if the file is there
            if (file_exists($file))
            {
                rename($file, $target);
            }
        }
        
        $this->setFile($fileName, false);
        unset($this->fp);
        $this->activateOptions();
    }
    
    function setFileName($fileName)
    {
        $this->fileName = $fileName;
        $this->expandedFileName = realpath($fileName);
        Log5PHP_InternalLog::debug("Log5PHP_Appender_RollingFile::setFileName():filename=[{$fileName}]:expandedFileName=[{$this->expandedFileName}]");  
    }


    /**
     * Set the maximum number of backup files to keep around.
     * 
     * <p>The <b>MaxBackupIndex</b> option determines how many backup
     * files are kept before the oldest is erased. This option takes
     * a positive integer value. If set to zero, then there will be no
     * backup files and the log file will be truncated when it reaches
     * MaxFileSize.
     *
     * @param mixed $maxBackups
     */
    function setMaxBackupIndex($maxBackups)
    {
        if (is_numeric($maxBackups))
            $this->maxBackupIndex = abs((int)$maxBackups);
    }

    /**
     * Set the maximum size that the output file is allowed to reach
     * before being rolled over to backup files.
     *
     * @param mixed $maxFileSize
     * @see setMaxFileSize()
     */
    function setMaximumFileSize($maxFileSize)
    {
        $this->setMaxFileSize($maxFileSize);
    }

    /**
     * Set the maximum size that the output file is allowed to reach
     * before being rolled over to backup files.
     * <p>In configuration files, the <b>MaxFileSize</b> option takes an
     * long integer in the range 0 - 2^63. You can specify the value
     * with the suffixes "KB", "MB" or "GB" so that the integer is
     * interpreted being expressed respectively in kilobytes, megabytes
     * or gigabytes. For example, the value "10KB" will be interpreted
     * as 10240.
     *
     * @param mixed $value
     */
    function setMaxFileSize($value)
    {
        $maxFileSize = null;
        $numpart = substr($value,0, strlen($value) -2);
        $suffix  = strtoupper(substr($value, -2));

        switch ($suffix) {
            case 'KB': $maxFileSize = (int)((int)$numpart * 1024); break;
            case 'MB': $maxFileSize = (int)((int)$numpart * 1024 * 1024); break;
            case 'GB': $maxFileSize = (int)((int)$numpart * 1024 * 1024 * 1024); break;
            default:
                if (is_numeric($value)) {
                    $maxFileSize = (int)$value;
                }
        }
        
        if ($maxFileSize === null) {
            Log5PHP_InternalLog::debug("Log5PHP_Appender_RollingFile::setMaxFileSize():value=[$value] wrong declaration");
        } else {
            $this->maxFileSize = abs($maxFileSize);
        }
    }

    /**
     * @param Log5PHP_LogEvent $event
     */
    protected function append(Log5PHP_LogEvent $event)
    {
        if ($this->fp) {
            parent::append($event);
            if (ftell($this->fp) > $this->getMaximumFileSize())    
                $this->rollOver();
        }
    }
}
