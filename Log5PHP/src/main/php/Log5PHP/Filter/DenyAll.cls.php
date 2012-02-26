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
 * @subpackage src_main_php_Log5PHP_Filter
 */

/**
 * @ignore 
 */

/**
 * This filter drops all logging events. 
 * 
 * <p>You can add this filter to the end of a filter chain to
 * switch from the default "accept all unless instructed otherwise"
 * filtering behaviour to a "deny all unless instructed otherwise"
 * behaviour.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Filter
 * @since 0.3
 */
class Log5PHP_Filter_DenyAll extends Log5PHP_Filter {

  /**
   * Always returns the integer constant {@link LOG5PHP_LOGGER_FILTER_DENY}
   * regardless of the {@link Log5PHP_LogEvent} parameter.
   * 
   * @param Log5PHP_LogEvent $event The {@link Log5PHP_LogEvent} to filter.
   * @return LOG5PHP_LOGGER_FILTER_DENY Always returns {@link LOG5PHP_LOGGER_FILTER_DENY}
   */
  function decide($event)
  {
    return LOG5PHP_LOGGER_FILTER_DENY;
  }
}
