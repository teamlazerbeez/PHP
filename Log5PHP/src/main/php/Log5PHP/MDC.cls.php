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
 * The Log5PHP_MDC class is similar to the {@link Log5PHP_NDC} class except that it is
 * based on a map instead of a stack. It provides <i>mapped diagnostic contexts</i>.
 * 
 * A <i>Mapped Diagnostic Context</i>, or
 * MDC in short, is an instrument for distinguishing interleaved log
 * output from different sources. Log output is typically interleaved
 * when a server handles multiple clients near-simultaneously.
 *
 * <p><b><i>The MDC is managed on a per thread basis</i></b>.
 * 
 * @version $Revision: 26050 $
 * @since 0.3
 * @package external_Log5PHP
 */
class Log5PHP_MDC
{

	/**
	 * @var array $keyStore
	 */
	static private $keyStore = array ();

	/**
	 * Put a context value as identified with the key parameter into the current thread's
	 *  context map.
	 *
	 * <p>If the current thread does not have a context map it is
	 *  created as a side effect.</p>
	 *
	 * @param string $key the key
	 * @param string $value the value
	 */
	static function put($key, $value)
	{
		self :: $keyStore[$key] = $value;
	}

	/**
	 * @return int number of mappings
	 */
	static function size()
	{
		return sizeof(self :: $keyStore);
	}

	/**
	 * @return array keys
	 */
	static function keys()
	{
		return array_keys(self :: $keyStore);
	}

	/**
	 * Get the context identified by the key parameter.
	 *
	 * <p>You can use special key identifiers to map values in 
	 * PHP $_SERVER and $_ENV vars. Just put a 'server.' or 'env.'
	 * followed by the var name you want to refer.</p>
	 *
	 * <p>This method has no side effects.</p>
	 *
	 * @param string $key
	 * @return string
	 */
	static function get($key)
	{
		Log5PHP_InternalLog :: debug("Log5PHP_MDC::get() key='$key'");

		if (empty ($key))
		{
			return '';
		}

		if (strpos($key, 'server.') === 0)
		{
			$varName = substr($key, 7);

			Log5PHP_InternalLog :: debug("Log5PHP_MDC::get() a _SERVER[$varName] is requested.");

			return $_SERVER[$varName];
		}
		elseif (strpos($key, 'env.') === 0)
		{
			$varName = substr($key, 4);

			Log5PHP_InternalLog :: debug("Log5PHP_MDC::get() a _ENV[$varName] is requested.");

			return $_ENV[$varName];
		}
		elseif (array_key_exists($key, self :: $keyStore))
		{
			Log5PHP_InternalLog :: debug("Log5PHP_MDC::get() a normal key is requested.");

			return self :: $keyStore[$key];
		}
	}

	/**
	 * Remove the the context identified by the key parameter. 
	 *
	 * It only affects user mappings.
	 *
	 * @param string $key
	 */
	static function remove($key)
	{
		unset (self :: $keyStore[$key]);
	}

}
