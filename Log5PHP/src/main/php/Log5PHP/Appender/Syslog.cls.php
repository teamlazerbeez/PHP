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
 * Log events using php {@link PHP_MANUAL#syslog} function.
 *
 * Levels are mapped as follows:
 * - <b>level &gt;= FATAL</b> to LOG_ALERT
 * - <b>FATAL &gt; level &gt;= ERROR</b> to LOG_ERR 
 * - <b>ERROR &gt; level &gt;= WARN</b> to LOG_WARNING
 * - <b>WARN  &gt; level &gt;= INFO</b> to LOG_INFO
 * - <b>INFO  &gt; level &gt;= DEBUG</b> to LOG_DEBUG
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_Syslog extends Log5PHP_Appender_Base
{

	/**
	 * The ident string is added to each message. Typically the name of your application.
	 *
	 * @var string Ident for your application
	 */
	private $ident = "Log5PHP Syslog-Event";

	/**
	 * The priority parameter value indicates the level of importance of the message.
	 * It is passed on to the Syslog daemon.
	 * 
	 * @var int     Syslog priority
	 */
	private $priority;

	/**
	 * The option used when generating a log message.
	 * It is passed on to the Syslog daemon. 
     * @see openlog
	 * 
	 * @var int     Syslog priority
	 */
	private $option;

	/**
	 * The facility value indicates the source of the message.
	 * It is passed on to the Syslog daemon.
	 *
	 * @var const int     Syslog facility
	 */
	private $facility;

	/**
	 * If it is necessary to define logging priority in the .properties-file,
	 * set this variable to "true".
	 *
	 * @var const int  value indicating whether the priority of the message is defined in the .properties-file
	 *                 (or properties-array)
	 */
	private $overridePriority;

	/**
	 * Set the ident of the syslog message.
	 *
	 * @param string Ident
	 */
	public function setIdent($ident)
	{
		$this->ident = $ident;
	}

	/**
	 * Set the priority value for the syslog message.
	 *
	 * @param const int Priority
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;
	}

	/**
	 * Set the facility value for the syslog message. Uses constant() to get the
	 * constant named after the provided string.
	 *
	 * @param string Facility
	 */
	public function setFacility($facility)
	{
		$this->facility = constant($facility);
	}

	/**
	 * If the priority of the message to be sent can be defined by a value in the properties-file, 
	 * set parameter value to "true".
	 *
	 * @param bool Override priority
	 */
	public function setOverridePriority($overridePriority)
	{
		$this->overridePriority = $overridePriority;
	}

	/**
	 * Set the option value for the syslog message.
	 * This value is used as a parameter for php openlog()  
	 * and passed on to the syslog daemon.
	 *
	 * @param string    $option
	 */
	public function setOption($option)
	{
		$this->option = $option;
	}

	function close()
	{
		closelog();
	}

	protected function append(Log5PHP_LogEvent $event)
	{

		if ($this->option == NULL)
		{
			$this->option = LOG_PID | LOG_CONS;
		}

		// Attach the process ID to the message, use the facility defined in the config file
		openlog($this->ident, $this->option, $this->facility);

		$level = $event->getLevel();
		$message = $event->getRenderedMessage();

		// If the priority of a syslog message can be overridden by a value defined in the properties-file,
		// use that value, else use the one that is defined in the code.
		if ($this->overridePriority)
		{
			syslog($this->priority, $message);
		}
		else
		{
			if ($level->isGreaterOrEqual(Log5PHP_Level :: getLevelFatal()))
			{
                syslog(LOG_ALERT, $message);
			}
			elseif ($level->isGreaterOrEqual(Log5PHP_Level :: getLevelError()))
			{
				syslog(LOG_ERR, $message);
			}
			elseif ($level->isGreaterOrEqual(Log5PHP_Level :: getLevelWarn()))
			{
				syslog(LOG_WARNING, $message);
			}
			elseif ($level->isGreaterOrEqual(Log5PHP_Level :: getLevelInfo()))
			{
				syslog(LOG_INFO, $message);
			}
			elseif ($level->isGreaterOrEqual(Log5PHP_Level :: getLevelDebug()))
			{
				syslog(LOG_DEBUG, $message);
			}
		}
		closelog();
	}
}
