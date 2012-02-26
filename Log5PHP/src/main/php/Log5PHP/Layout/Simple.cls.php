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
 * A simple layout.
 *
 * Returns the log statement in a format consisting of the
 * <b>level</b>, followed by " - " and then the <b>message</b>. 
 * For example, 
 * <samp> INFO - "A message" </samp>
 *
 * @version $Revision: 37220 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Layout
 */  
class Log5PHP_Layout_Simple extends Log5PHP_Layout_Base {
    
    /**
     * Returns the log statement in a format consisting of the
     * <b>level</b>, followed by " - " and then the
     * <b>message</b>. For example, 
     * <samp> INFO - "A message" </samp>
     *
     * @param Log5PHP_LogEvent $event
     * @return string
     */
    function format(Log5PHP_LogEvent $event)
    {
        $level = $event->getLevel();
        return $level->toString() . ' - ' . $event->getRenderedMessage(). LOG5PHP_LINE_SEP;
    }
}
