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
 * Interface for an appender
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 */
interface Log5PHP_Appender_Appendable 
{

    /**
     * Add a filter to the end of the filter list.
     *
     * @param Log5PHP_Filter $newFilter add a new Log5PHP_Filter
     */
    function addFilter(Log5PHP_Filter $newFilter);
    
    /**
     * Clear the list of filters by removing all the filters in it.
     */
    function clearFilters();


    /**
     * Return the first filter in the filter chain for this Appender. 
     * The return value may be <i>null</i> if no is filter is set.
     * @return Filter
     */
    function getFilter();
    
    /**
     * Release any resources allocated.
     * Subclasses of {@link Log5PHP_Appender_Appendable} should implement 
     * this method to perform proper closing procedures.
     */
    function close();

    /**
     * This method performs threshold checks and invokes filters before
     * delegating actual logging to the subclasses specific <i>append()</i> method.
     * @param Log5PHP_LogEvent $event
     */
    function doAppend(Log5PHP_LogEvent $event);

    /**
     * Get the name of this appender.
     * @return string
     */
    function getName();

    /**
     * Set the Layout for this appender.
     *
     * @param Log5PHP_Layout $layout
     */
    function setLayout(Log5PHP_Layout_Base $layout);
    
    /**
     * Returns this appender layout.
     * @return Log5PHP_Layout
     */
    function getLayout();

    /**
     * Set the name of this appender.
     *
     * The name is used by other components to identify this appender.
     *
     * @param string $name
     */
    function setName($name);

    /**
     * Configurators call this method to determine if the appender
     * requires a layout. 
     *
     * <p>If this method returns <i>true</i>, meaning that layout is required, 
     * then the configurator will configure a layout using the configuration 
     * information at its disposal.  If this method returns <i>false</i>, 
     * meaning that a layout is not required, then layout configuration will be
     * skipped even if there is available layout configuration
     * information at the disposal of the configurator.</p>
     *
     * <p>In the rather exceptional case, where the appender
     * implementation admits a layout but can also work without it, then
     * the appender should return <i>true</i>.</p>
     *
     * @return boolean
     */
    function requiresLayout();

}
