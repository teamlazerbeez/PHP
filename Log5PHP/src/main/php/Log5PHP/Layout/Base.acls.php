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
 * Extend this abstract class to create your own log layout format.
 *  
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 */
abstract class Log5PHP_Layout_Base 
{

    /**
     * Override this method
     */
    function activateOptions() 
    {
        // override as necessary
    }

    /**
     * Override this method to create your own layout format.
     *
     * @param Log5PHP_LogEvent
     * @return string
     */
    function format(Log5PHP_LogEvent $event)
    {
        return $event->getRenderedMessage();
    } 
    
    /**
     * Returns the content type output by this layout.
     * @return string
     */
    function getContentType()
    {
        return "text/plain";
    } 
            
    /**
     * Returns the footer for the layout format.
     * @return string
     */
    function getFooter()
    {
        return null;
    } 

    /**
     * Returns the header for the layout format.
     * @return string
     */
    function getHeader()
    {
        return null;
    }
}
