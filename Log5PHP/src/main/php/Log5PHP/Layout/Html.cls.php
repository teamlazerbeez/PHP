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
 * @subpackage src_main_php_Log5PHP_Layout
 */

/**
 * @ignore 
 */

/**
 * This layout outputs events in a HTML table.
 *
 * Parameters are: {@link $title}, {@link $locationInfo}.
 *
 * @version $Revision: 37220 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Layout
 */
class Log5PHP_Layout_Html extends Log5PHP_Layout_Base {

    /**
     * The <b>LocationInfo</b> option takes a boolean value. By
     * default, it is set to false which means there will be no location
     * information output by this layout. If the the option is set to
     * true, then the file name and line number of the statement
     * at the origin of the log statement will be output.
     *
     * <p>If you are embedding this layout within a {@link Log5PHP_Appender_Mail}
     * or a {@link Log5PHP_Appender_MailEvent} then make sure to set the
     * <b>LocationInfo</b> option of that appender as well.
     * @var boolean
     */
    private $locationInfo = false;
    
    /**
     * The <b>Title</b> option takes a String value. This option sets the
     * document title of the generated HTML document.
     * Defaults to 'Log5php Log Messages'.
     * @var string
     */
    private $title = "Log5php Log Messages";
    
    /**
     * The <b>LocationInfo</b> option takes a boolean value. By
     * default, it is set to false which means there will be no location
     * information output by this layout. If the the option is set to
     * true, then the file name and line number of the statement
     * at the origin of the log statement will be output.
     *
     * <p>If you are embedding this layout within a {@link Log5PHP_Appender_Mail}
     * or a {@link Log5PHP_Appender_MailEvent} then make sure to set the
     * <b>LocationInfo</b> option of that appender as well.
     * 
     * @param bool $flag
     */
    function setLocationInfo($flag)
    {
        if (is_bool($flag)) {
            $this->locationInfo = $flag;
        } else {
            $this->locationInfo = (bool)(strtolower($flag) == 'true');
        }
    }

    /**
     * Returns the current value of the <b>LocationInfo</b> option.
     */
    function getLocationInfo()
    {
        return $this->locationInfo;
    }
    
    /**
     * The <b>Title</b> option takes a String value. This option sets the
     * document title of the generated HTML document.
     */
    function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string Returns the current value of the <b>Title</b> option.
     */
    function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @return string Returns the content type output by this layout, i.e "text/html".
     */
    function getContentType()
    {
        return "text/html";
    }
    
    /**
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function format(Log5PHP_LogEvent $event)
    {
        $sbuf = LOG5PHP_LINE_SEP . "<tr>" . LOG5PHP_LINE_SEP;
    
        $sbuf .= "<td>";
        
        $eventTime = (float)$event->getTimeStampFloat();
        $eventStartTime = (float)Log5PHP_LogEvent::getStartTime();
        $sbuf .= number_format(($eventTime - $eventStartTime) * 1000, 0, '', '');
        $sbuf .= "</td>" . LOG5PHP_LINE_SEP;
    
        $sbuf .= "<td title=\"" . $event->getThreadName() . " thread\">";
        $sbuf .= $event->getThreadName();
        $sbuf .= "</td>" . LOG5PHP_LINE_SEP;
    
        $sbuf .= "<td title=\"Level\">";
        
        $level = $event->getLevel();
        
        if ($level->equals(Log5PHP_Level::getLevelDebug())) {
          $sbuf .= "<font color=\"#339933\">";
          $sbuf .= $level->toString();
          $sbuf .= "</font>";
        }elseif($level->equals(Log5PHP_Level::getLevelWarn())) {
          $sbuf .= "<font color=\"#993300\"><strong>";
          $sbuf .= $level->toString();
          $sbuf .= "</strong></font>";
        } else {
          $sbuf .= $level->toString();
        }
        $sbuf .= "</td>" . LOG5PHP_LINE_SEP;
    
        $sbuf .= "<td title=\"" . htmlentities($event->getLoggerName(), ENT_QUOTES) . " category\">";
        $sbuf .= htmlentities($event->getLoggerName(), ENT_QUOTES);
        $sbuf .= "</td>" . LOG5PHP_LINE_SEP;
    
        if ($this->locationInfo) {
            $locInfo = $event->getLocationInfo();
            $sbuf .= "<td>";
            $sbuf .= htmlentities($locInfo->getFileName(), ENT_QUOTES). ':' . $locInfo->getLineNumber();
            $sbuf .= "</td>" . LOG5PHP_LINE_SEP;
        }

        $sbuf .= "<td title=\"Message\">";
        $sbuf .= htmlentities($event->getRenderedMessage(), ENT_QUOTES);
        $sbuf .= "</td>" . LOG5PHP_LINE_SEP;

        $sbuf .= "</tr>" . LOG5PHP_LINE_SEP;
        
        if ($event->getNDC() != null) {
            $sbuf .= "<tr><td bgcolor=\"#EEEEEE\" style=\"font-size : xx-small;\" colspan=\"6\" title=\"Nested Diagnostic Context\">";
            $sbuf .= "NDC: " . htmlentities($event->getNDC(), ENT_QUOTES);
            $sbuf .= "</td></tr>" . LOG5PHP_LINE_SEP;
        }

        return $sbuf;
    }

    /**
     * @return string Returns appropriate HTML headers.
     */
    function getHeader()
    {
        $sbuf = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">" . LOG5PHP_LINE_SEP;
        $sbuf .= "<html>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<head>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<title>" . $this->title . "</title>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<style type=\"text/css\">" . LOG5PHP_LINE_SEP;
        $sbuf .= "<!--" . LOG5PHP_LINE_SEP;
        $sbuf .= "body, table {font-family: arial,sans-serif; font-size: x-small;}" . LOG5PHP_LINE_SEP;
        $sbuf .= "th {background: #336699; color: #FFFFFF; text-align: left;}" . LOG5PHP_LINE_SEP;
        $sbuf .= "-->" . LOG5PHP_LINE_SEP;
        $sbuf .= "</style>" . LOG5PHP_LINE_SEP;
        $sbuf .= "</head>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<body bgcolor=\"#FFFFFF\" topmargin=\"6\" leftmargin=\"6\">" . LOG5PHP_LINE_SEP;
        $sbuf .= "<hr size=\"1\" noshade>" . LOG5PHP_LINE_SEP;
        $sbuf .= "Log session start time " . strftime('%c', time()) . "<br>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<br>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" bordercolor=\"#224466\" width=\"100%\">" . LOG5PHP_LINE_SEP;
        $sbuf .= "<tr>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<th>Time</th>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<th>Thread</th>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<th>Level</th>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<th>Category</th>" . LOG5PHP_LINE_SEP;
        if ($this->locationInfo)
            $sbuf .= "<th>File:Line</th>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<th>Message</th>" . LOG5PHP_LINE_SEP;
        $sbuf .= "</tr>" . LOG5PHP_LINE_SEP;

        return $sbuf;
    }

    /**
     * @return string Returns the appropriate HTML footers.
     */
    function getFooter()
    {
        $sbuf = "</table>" . LOG5PHP_LINE_SEP;
        $sbuf .= "<br>" . LOG5PHP_LINE_SEP;
        $sbuf .= "</body></html>";

        return $sbuf;
    }
}
