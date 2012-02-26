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
 * When location information is not available the constant
 * <i>NA</i> is returned. Current value of this string
 * constant is <b>?</b>.
 */
define('LOG5PHP_LOGGER_LOCATION_INFO_NA', 'NA');

/**
 * The internal representation of caller location information.
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP
 * @since 0.3
 */
class Log5PHP_LocationInfo
{

	/**
	 * @var string Caller's line number.
	 */
	private $lineNumber = null;

	/**
	 * @var string Caller's file name.
	 */
	private $fileName = null;

	/**
	 * @var string Caller's fully qualified class name.
	 */
	private $className = null;

	/**
	 * @var string Caller's method name.
	 */
	private $methodName = null;

	/**
	 * Instantiate location information based on a {@link PHP_MANUAL#debug_backtrace}.
	 *
	 * @param array $trace output of debug_backtrace (presumably, d_b was
	 * actually called within LogEvent)
	 * @param mixed $caller
	 */
	function __construct($trace, $fqcn = null)
	{
        $this->initForDebugBacktrace($trace);
	}

	function getClassName()
	{
		return ($this->className === null) ? LOG5PHP_LOGGER_LOCATION_INFO_NA : $this->className;
	}

	/**
	 *  Return the file name of the caller.
	 *  <p>This information is not always available.
	 */
	function getFileName()
	{
		return ($this->fileName === null) ? LOG5PHP_LOGGER_LOCATION_INFO_NA : $this->fileName;
	}

	/**
	 *  Returns the line number of the caller.
	 *  <p>This information is not always available.
	 */
	function getLineNumber()
	{
		return ($this->lineNumber === null) ? LOG5PHP_LOGGER_LOCATION_INFO_NA : $this->lineNumber;
	}

	/**
	 *  Returns the method name of the caller.
	 */
	function getMethodName()
	{
		return ($this->methodName === null) ? LOG5PHP_LOGGER_LOCATION_INFO_NA : $this->methodName;
	}

	/**
	 * Full information. This is never set anywhere, but for compatibility I'll
     * maintain it.
     * @return string
     */
    function getFullInfo()
    {
        return ($this->fullInfo === null) ? LOG5PHP_LOGGER_LOCATION_INFO_NA : $this->fullInfo;
    }

	/**
	 * Initialize ``shortcut'' fields (line number that called log5php, etc)
	 * based on the backtrace. Note that this method is DESTRUCTIVE for the
	 * backtrace itself so it should be called LAST.
	 */
	private function initForDebugBacktrace(array $trace)
	{
		$prevHop = null;
		// search from the bottom up to identify the caller
		$hop = array_pop($trace);
		while ($hop !== null)
		{
			if (isset ($hop['class']))
			{
				$className = $hop['class'];

				// skip the log5php infrastructure
				if ($className == 'Log5PHP_Logger' or get_parent_class($className) == 'Log5PHP_Logger')
				{
					$this->lineNumber = $hop['line'];
					$this->fileName = $hop['file'];
					break;
				}
			}
			$prevHop = $hop;
			$hop = array_pop($trace);
		}

		// prevHop is now pointing at the frame that called log5php
		$this->className = isset ($prevHop['class']) ? $prevHop['class'] : 'main';

		// do not use the file-loading functions as the function that called log5php
		if (isset ($prevHop['function']) and $prevHop['function'] !== 'include' and $prevHop['function'] !== 'include_once' and $prevHop['function'] !== 'require' and $prevHop['function'] !== 'require_once')
		{
			$this->methodName = $prevHop['function'];
		}
		else
		{
			$this->methodName = 'main';
		}
	}

	/**
	 * Convert a backtrace returned from debug_backtrace() from an array into a string
	 * that can easily be read.  Similar to debug_print_backtrace() with it being returned
	 * as a string instead of printed.
	 * #0 [fileFoo.php:322] fooObj->barMethod(arg1, arg2)
	 * @param array $backtrace Backtrace returned from a call to debug_backtrace()
	 * @return string String representation of the passed backtrace
	 */
     /*
	private static function convertBacktraceToString(array $backtrace)
	{
		$buf = '';
		$offset = 0;

		// Add an entry for each stack level in the trace
		foreach ($backtrace as $stackFrame)
		{
            echo "==== stack frame " . $stackFrame['file'] . ":" . $stackFrame['line'] . "====\n";
			$buf .= '#' . $offset;
			if (isset ($stackFrame['file']))
			{
				$buf .= ' [' . $stackFrame['file'] . ':' . $stackFrame['line'] . '] ';
			}

			// Show method calls in <class>-><method> or <class>::<method> format
			if (isset ($stackFrame['class']))
			{
				$buf .= $stackFrame['class'] . $stackFrame['type'];
			}

			// Treat object methods and non-object functions the same since methods will have been prefixed by the above if()
			if (isset ($stackFrame['function']))
			{
				$argumentString = '';
				$buf .= $stackFrame['function'] . '(';
				// Add all arguments to the debug output
				foreach ($stackFrame['args'] as $argument)
				{
                    echo "==== arg ====\n";
                    // can't use var_export -- it will die on objects that are linked to each other bidirectionally
					$argumentString .= print_r($argument, true);
					// Don't let really big arguments destroy readability, condense strings
					// ...longer than 5000 characters (arbitrary) to be only 500 (also arbitrary)
					if (strlen($argumentString) > 5000)
					{
						$argumentString = substr($argumentString, 0, 500) . '...<condensed>';
					}
					$buf .= $argumentString . ', ';
				}

				// Remove trailing ', ' if the method/function had arguments
				if (count($stackFrame['args']) != 0)
				{
					$buf = substr($buf, 0, -2);
				}
				$buf .= ')' . chr(10);
			}
			++ $offset;
		}
		return $buf;
	}
    */
}
