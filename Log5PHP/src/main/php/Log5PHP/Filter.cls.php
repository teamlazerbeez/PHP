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
 * @subpackage src_main_php_Log5PHP
 */

/**
 * @ignore 
 */
 
/**
 * The log event must be logged immediately without consulting with
 * the remaining filters, if any, in the chain.  
 */
define('LOG5PHP_LOGGER_FILTER_ACCEPT',  1);

/**
 * This filter is neutral with respect to the log event. The
 * remaining filters, if any, should be consulted for a final decision.
 */
define('LOG5PHP_LOGGER_FILTER_NEUTRAL', 0);

/**
 * The log event must be dropped immediately without consulting
 *  with the remaining filters, if any, in the chain.  
 */
define('LOG5PHP_LOGGER_FILTER_DENY',    -1);

/**
 * Users should extend this class to implement customized logging
 * event filtering. Note that {@link Log5PHP_Category} and {@link Log5PHP_Appender_Base}, 
 * the parent class of all standard
 * appenders, have built-in filtering rules. It is suggested that you
 * first use and understand the built-in rules before rushing to write
 * your own custom filters.
 * 
 * <p>This abstract class assumes and also imposes that filters be
 * organized in a linear chain. The {@link #decide
 * decide(Log5PHP_LogEvent)} method of each filter is called sequentially,
 * in the order of their addition to the chain.
 * 
 * <p>The {@link decide()} method must return one
 * of the integer constants {@link LOG5PHP_LOG5PHP_LOGGER_FILTER_DENY}, 
 * {@link LOG5PHP_LOGGER_FILTER_NEUTRAL} or {@link LOG5PHP_LOGGER_FILTER_ACCEPT}.
 * 
 * <p>If the value {@link LOG5PHP_LOGGER_FILTER_DENY} is returned, then the log event is
 * dropped immediately without consulting with the remaining
 * filters. 
 * 
 * <p>If the value {@link LOG5PHP_LOGGER_FILTER_NEUTRAL} is returned, then the next filter
 * in the chain is consulted. If there are no more filters in the
 * chain, then the log event is logged. Thus, in the presence of no
 * filters, the default behaviour is to log all logging events.
 * 
 * <p>If the value {@link LOG5PHP_LOGGER_FILTER_ACCEPT} is returned, then the log
 * event is logged without consulting the remaining filters. 
 * 
 * <p>The philosophy of log5php filters is largely inspired from the
 * Linux ipchains. 
 * 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP
 */
class Log5PHP_Filter {

    /**
     * @var Log5PHP_Filter Points to the next {@link Log5PHP_Filter} in the filter chain.
     */
    private $next;

    /**
     * Usually filters options become active when set. We provide a
     * default do-nothing implementation for convenience.
    */
    function activateOptions()
    {
        return;
    }

    /**   
     * Decide what to do.  
     * <p>If the decision is {@link LOG5PHP_LOGGER_FILTER_DENY}, then the event will be
     * dropped. If the decision is {@link LOG5PHP_LOGGER_FILTER_NEUTRAL}, then the next
     * filter, if any, will be invoked. If the decision is {@link LOG5PHP_LOGGER_FILTER_ACCEPT} then
     * the event will be logged without consulting with other filters in
     * the chain.
     *
     * @param Log5PHP_LogEvent $event The {@link Log5PHP_LogEvent} to decide upon.
     * @return integer {@link LOG5PHP_LOGGER_FILTER_NEUTRAL} or {@link LOG5PHP_LOGGER_FILTER_DENY}|{@link LOG5PHP_LOGGER_FILTER_ACCEPT}
     */
    function decide($event)
    {
        return LOG5PHP_LOGGER_FILTER_NEUTRAL;
    }

    /**
     * @var Log5PHP_Filter $filter the filter to set as the next in the chain
     */
    public function setNext(Log5PHP_Filter $filter)
    {
        $this->next = $filter;        
    }
    
    /**
     * @return Log5PHP_Filter the next loggerFilter in the chain;;
     */
    public function getNext()
    {
        return $this->next;
    }

}
