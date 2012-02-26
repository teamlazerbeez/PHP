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
 * Appends log events to mail using php function {@link PHP_MANUAL#mail}.
 *
 * <p>Parameters are {@link $from}, {@link $to}, {@link $subject}.</p>
 * <p>This appender requires a layout.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_Mail extends Log5PHP_Appender_Base {

    /**
     * @var string 'from' field
     */
    private $from = null;

    /**
     * @var string 'subject' field
     */
    private $subject = 'Log5php Report';
    
    /**
     * @var string 'to' field
     */
    private $to = null;

    /**
     * @var string used to create mail body
     * @access private
     */
    private $body = '';
    
    /**
     */
    protected $requiresLayout = true;
    
    function activateOptions()
    {
        return;
    }
    
    function close()
    {
        $from       = $this->getFrom();
        $to         = $this->getTo();

        if (!empty($this->body) and $from !== null and $to !== null and $this->layout !== null) {

            $subject    = $this->getSubject();            

            Log5PHP_InternalLog::debug("Log5PHP_Appender_Mail::close() sending mail from=[{$from}] to=[{$to}] subject=[{$subject}]");
            
            mail(
                $to, $subject, 
                $this->layout->getHeader() . $this->body . $this->layout->getFooter(),
                "From: {$from}\r\n"
            );
        }
    }
    
    /**
     * @return string
     */
    function getFrom()
    {
        return $this->from;
    }
    
    /**
     * @return string
     */
    function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    function getTo()
    {
        return $this->to;
    }
    
    function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    function setTo($to)
    {
        $this->to = $to;
    }

    function setFrom($from)
    {
        $this->from = $from;
    }  

    protected function append(Log5PHP_LogEvent $event)
    {
        if ($this->layout !== null)
            $this->body .= $this->layout->format($event);
    }
}
