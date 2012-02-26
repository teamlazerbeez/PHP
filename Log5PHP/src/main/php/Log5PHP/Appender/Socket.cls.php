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
define('LOG5PHP_LOGGER_APPENDER_SOCKET_DEFAULT_PORT', 4446);
define('LOG5PHP_LOGGER_APPENDER_SOCKET_DEFAULT_TIMEOUT', 30);

/**
 * Serialize events and send them to a network socket.
 *
 * Parameters are {@link $remoteHost}, {@link $port}, {@link $timeout}, 
 * {@link $locationInfo}, {@link $useXml} and {@link $log4jNamespace}.
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */
class Log5PHP_Appender_Socket extends Log5PHP_Appender_Base
{

	/**
	 * @var mixed socket connection resource
	 */
	private $socket = null;

	/**
	 * Target host. On how to define remote hostaname see 
	 * {@link PHP_MANUAL#fsockopen}
	 * @var string 
	 */
	private $remoteHost = '';

	/**
	 * @var integer the network port.
	 */
	private $port = LOG5PHP_LOGGER_APPENDER_SOCKET_DEFAULT_PORT;

	/**
	 * @var integer connection timeout
	 */
	private $timeout = LOG5PHP_LOGGER_APPENDER_SOCKET_DEFAULT_TIMEOUT;

	/**
	 * @var Log5PHP_Layout
	 */
	protected $layout = null;

	/**
	 * @var boolean
	 */
	protected $requiresLayout = true;

	/**
	 * Create a socket connection using defined parameters
	 */
	function activateOptions()
	{
		Log5PHP_InternalLog :: debug("Log5PHP_Appender_Socket::activateOptions() creating a socket...");
		$errno = 0;
		$errstr = '';
		$this->socket = fsockopen($this->getRemoteHost(), $this->getPort(), $errno, $errstr, $this->getTimeout());
		if ($errno)
		{
			Log5PHP_InternalLog :: debug("Log5PHP_Appender_Socket::activateOptions() socket error [$errno] $errstr");
		}
		else
		{
			Log5PHP_InternalLog :: debug("Log5PHP_Appender_Socket::activateOptions() socket created [" . $this->socket . "]");
            fwrite($this->socket, $this->layout->getHeader());
		}
	}

	function close()
	{
		# removed @
        fwrite($this->socket, $this->layout->getFooter());
		fclose($this->socket);
	}

	/**
	 * @return string
	 */
	function getHostname()
	{
		return $this->getRemoteHost();
	}

	/**
	 * @return integer
	 */
	function getPort()
	{
		return $this->port;
	}

	function getRemoteHost()
	{
		return $this->remoteHost;
	}

	/**
	 * @return integer
	 */
	function getTimeout()
	{
		return $this->timeout;
	}

	function reset()
	{
		$this->close();
		parent :: reset();
	}

	/**
	 * @param string
	 * @deprecated Please, use {@link setRemoteHost}
	 */
	function setHostname($hostname)
	{
		$this->setRemoteHost($hostname);
	}

	/**
	 * @param integer
	 */
	function setPort($port)
	{
		$port = Log5PHP_Utility_OptionConverter :: toInt($port, 0);
		if ($port > 0 and $port < 65535)
			$this->port = $port;
	}

	/**
	 * @param string
	 */
	function setRemoteHost($hostname)
	{
		$this->remoteHost = $hostname;
	}

	/**
	 * @param integer
	 */
	function setTimeout($timeout)
	{
		$this->timeout = Log5PHP_Utility_OptionConverter :: toInt($timeout, $this->getTimeout());
	}

	/**
	 * @param Log5PHP_LogEvent
	 */
	protected function append(Log5PHP_LogEvent $event)
	{
		if ($this->socket)
		{

			Log5PHP_InternalLog :: debug("Log5PHP_Appender_Socket::append()");

			fwrite($this->socket, $this->layout->format($event));

			// not sure about it...
			fflush($this->socket);
		}
	}
}
