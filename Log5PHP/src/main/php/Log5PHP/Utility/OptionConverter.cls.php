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
 * @subpackage src_main_php_Log5PHP_Utility
 */

/**
 * @ignore 
 */
define('LOG5PHP_OPTION_CONVERTER_DELIM_START', '${');
define('LOG5PHP_OPTION_CONVERTER_DELIM_STOP', '}');
define('LOG5PHP_OPTION_CONVERTER_DELIM_START_LEN', 2);
define('LOG5PHP_OPTION_CONVERTER_DELIM_STOP_LEN', 1);

/**
 * A convenience class to convert property values to specific types.
 *
 * @version $Revision: 26050 $ 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Utility
 * @since 0.5
 */
class Log5PHP_Utility_OptionConverter
{

    /**
     * @param array $l
     * @param array $r
     * @return array
     *
     */
    static function concatanateArrays($l, $r)
    {
        return array_merge($l, $r);
    }

    /**
    * Read a predefined var.
    *
    * It returns a value referenced by <var>$key</var> using this search criteria:
    * - if <var>$key</var> is a constant then return it. Else
    * - if <var>$key</var> is set in <var>$_ENV</var> then return it. Else
    * - return <var>$def</var>. 
    *
    * @param string $key The key to search for.
    * @param string $def The default value to return.
    * @return string    the string value of the system property, or the default
    *                   value if there is no property with that key.
    *
    */
    static function getSystemProperty($key, $def)
    {
        Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::getSystemProperty():key=[{$key}]:def=[{$def}].");

        if (defined($key))
        {
            return (string) constant($key);
        }
        elseif (isset ($_ENV[$key]))
        {
            return (string) $_ENV[$key];
        }
        else
        {
            return $def;
        }
    }

    /**
     * If <var>$value</var> is <i>true</i>, then <i>true</i> is
     * returned. If <var>$value</var> is <i>false</i>, then
     * <i>true</i> is returned. Otherwise, <var>$default</var> is
     * returned.
     *
     * <p>Case of value is unimportant.</p>
     *
     * @param string $value
     * @param boolean $default
     * @return boolean
     *
     */
    static function toBoolean($value, $default)
    {
        if ($value === null)
        {
            return $default;
        }
        if ($value == 1)
        {
            return true;
        }
        $trimmedVal = strtolower(trim($value));
        if ("true" == $trimmedVal or "yes" == $trimmedVal)
        {
            return true;
        }
        if ("false" == $trimmedVal)
        {
            return false;
        }
        return $default;
    }

    /**
     * @param string $value
     * @param integer $default
     * @return integer
     */
    static function toInt($value, $default)
    {
        $value = trim($value);
        if (is_numeric($value))
        {
            return (int) $value;
        }
        else
        {
            return $default;
        }
    }

    /**
     * Converts a standard or custom priority level to a Level
     * object.
     *
     * <p> If <var>$value</var> is of form "<b>level#full_file_classname</b>",
     * where <i>full_file_classname</i> means the class filename with path
     * but without php extension, then the specified class' <i>toLevel()</i> method
     * is called to process the specified level string; if no '#'
     * character is present, then the default {@link Log5PHP_Level}
     * class is used to process the level value.</p>
     *
     * <p>As a special case, if the <var>$value</var> parameter is
     * equal to the string "NULL", then the value <i>null</i> will
     * be returned.</p>
     *
     * <p>If any error occurs while converting the value to a level,
     * the <var>$defaultValue</var> parameter, which may be
     * <i>null</i>, is returned.</p>
     *
     * <p>Case of <var>$value</var> is insignificant for the level level, but is
     * significant for the class name part, if present.</p>
     *
     * @param string $value
     * @param Log5PHP_Level $defaultValue
     * @return Log5PHP_Level a {@link Log5PHP_Level} or null
     */
    static function toLevel($value, $defaultValue)
    {
        if ($value === null)
            return $defaultValue;

        $hashIndex = strpos($value, '#');
        if ($hashIndex === false)
        {
            if ("NULL" == strtoupper($value))
            {
                return null;
            }
            else
            {
                // no class name specified : use standard Level class
                return Log5PHP_Level :: toLevel($value, $defaultValue);
            }
        }

        $result = $defaultValue;

        $clazz = substr($value, ($hashIndex +1));
        $levelName = substr($value, 0, $hashIndex);

        // This is degenerate case but you never know.
        if ("NULL" == strtoupper($levelName))
        {
            return null;
        }

        Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::toLevel():class=[{$clazz}]:pri=[{$levelName}]");

        if (!class_exists($clazz))
        {
            # removed @
            include_once ("{$clazz}.php");
        }

        $clazz = basename($clazz);

        if (class_exists($clazz))
        {
            # removed @
            $result = call_user_func(array (
                $clazz,
                'toLevel'
            ), $value, $defaultValue);
            if (!is_a($result, 'loggerlevel'))
            {
                Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::toLevel():class=[{$clazz}] cannot call toLevel(). Returning default.");
                $result = $defaultValue;
            }
        }
        else
        {
            Log5PHP_InternalLog :: warn("LoggerOptionConverter::toLevel() class '{$clazz}' doesnt exists.");
        }
        return $result;
    }

    /**
     * @param string $value
     * @param float $default
     * @return float
     *
     */
    static function toFileSize($value, $default)
    {
        if ($value === null)
            return $default;

        $s = strtoupper(trim($value));
        $multiplier = (float) 1;
        if (($index = strpos($s, 'KB')) !== false)
        {
            $multiplier = 1024;
            $s = substr($s, 0, $index);
        }
        elseif (($index = strpos($s, 'MB')) !== false)
        {
            $multiplier = 1024 * 1024;
            $s = substr($s, 0, $index);
        }
        elseif (($index = strpos($s, 'GB')) !== false)
        {
            $multiplier = 1024 * 1024 * 1024;
            $s = substr($s, 0, $index);
        }
        if (is_numeric($s))
        {
            return (float) $s * $multiplier;
        }
        else
        {
            Log5PHP_InternalLog :: warn("LoggerOptionConverter::toFileSize() [{$s}] is not in proper form.");
        }
        return $default;
    }

    /**
     * Find the value corresponding to <var>$key</var> in
     * <var>$props</var>. Then perform variable substitution on the
     * found value.
     *
     * @param string $key
     * @param array $props
     * @return string
     *
     */
    static function findAndSubst($key, $props)
    {
        # removed @
        $value = $props[$key];
        if (empty ($value))
        {
            return null;
        }
        return Log5PHP_Utility_OptionConverter :: substVars($value, $props);
    }

    /**
     * Perform variable substitution in string <var>$val</var> from the
     * values of keys found with the {@link getSystemProperty()} method.
     * 
     * <p>The variable substitution delimeters are <b>${</b> and <b>}</b>.
     * 
     * <p>For example, if the "MY_CONSTANT" contains "value", then
     * the call
     * <code>
     * $s = Log5PHP_Utility_OptionConverter::substituteVars("Value of key is ${MY_CONSTANT}.");
     * </code>
     * will set the variable <i>$s</i> to "Value of key is value.".</p>
     * 
     * <p>If no value could be found for the specified key, then the
     * <var>$props</var> parameter is searched, if the value could not
     * be found there, then substitution defaults to the empty string.</p>
     * 
     * <p>For example, if {@link getSystemProperty()} cannot find any value for the key
     * "inexistentKey", then the call
     * <code>
     * $s = Log5PHP_Utility_OptionConverter::substVars("Value of inexistentKey is [${inexistentKey}]");
     * </code>
     * will set <var>$s</var> to "Value of inexistentKey is []".</p>
     * 
     * <p>A warn is thrown if <var>$val</var> contains a start delimeter "${" 
     * which is not balanced by a stop delimeter "}" and an empty string is returned.</p>
     * 
     * log4j-author Avy Sharell
     * 
     * @param string $val The string on which variable substitution is performed.
     * @param array $props
     * @return string
     *
     */
    static function substVars($val, $props = null)
    {
        Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::substVars():val=[{$val}]");

        $sbuf = '';
        $i = 0;
        while (true)
        {
            $j = strpos($val, LOG5PHP_OPTION_CONVERTER_DELIM_START, $i);
            if ($j === false)
            {
                Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::substVars() no more variables");
                // no more variables
                if ($i == 0)
                { // this is a simple string
                    Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::substVars() simple string");
                    return $val;
                }
                else
                { // add the tail string which contails no variables and return the result.
                    $sbuf .= substr($val, $i);
                    Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::substVars():sbuf=[{$sbuf}]. Returning sbuf");
                    return $sbuf;
                }
            }
            else
            {

                $sbuf .= substr($val, $i, $j - $i);
                Log5PHP_InternalLog :: debug("Log5PHP_Utility_OptionConverter::substVars():sbuf=[{$sbuf}]:i={$i}:j={$j}.");
                $k = strpos($val, LOG5PHP_OPTION_CONVERTER_DELIM_STOP, $j);
                if ($k === false)
                {
                    Log5PHP_InternalLog :: warn("LoggerOptionConverter::substVars() " .
                    "'{$val}' has no closing brace. Opening brace at position {$j}.");
                    return '';
                }
                else
                {
                    $j += LOG5PHP_OPTION_CONVERTER_DELIM_START_LEN;
                    $key = substr($val, $j, $k - $j);
                    // first try in System properties
                    $replacement = Log5PHP_Utility_OptionConverter :: getSystemProperty($key, null);
                    // then try props parameter
                    if ($replacement == null and $props !== null)
                    {
                        # removed @
                        $replacement = $props[$key];
                    }

                    if (!empty ($replacement))
                    {
                        // Do variable substitution on the replacement string
                        // such that we can solve "Hello ${x2}" as "Hello p1" 
                        // the where the properties are
                        // x1=p1
                        // x2=${x1}
                        $recursiveReplacement = Log5PHP_Utility_OptionConverter :: substVars($replacement, $props);
                        $sbuf .= $recursiveReplacement;
                    }
                    $i = $k +LOG5PHP_OPTION_CONVERTER_DELIM_STOP_LEN;
                }
            }
        }
    }

}
