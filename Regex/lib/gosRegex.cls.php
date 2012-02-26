<?php

/**
 * Genius Open Source Libraries Collection
 * Copyright 2010 Team Lazer Beez (http://teamlazerbeez.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Drew Stephens <drew@dinomite.net> (http://teamlazerbeez.com)
 */
class gosRegex
{
    /**
     * Wrapper for preg_grep that turns (silent) errors into exceptions.
     *
     * @see http://us.php.net/preg_grep
     * @throws  gosException_RegularExpression   On preg_last_error() != 0
     */
    public static function grep($pattern, $input, $flags = 0)
    {
        $ret = preg_grep($pattern, $input, $flags);

        gosRegex::checkError();

        return $ret;
    }

    /**
     * Wrapper for preg_filter that turns (silent) errors into exceptions.
     *
     * @see http://us.php.net/preg_filter
     * @throws  gosException_RegularExpression   On preg_last_error() != 0
     */
    public static function filter($pattern, $replacement, $subject, $limit = -1, &$count = null)
    {
        $ret = preg_filter($pattern, $replacement, $subject, $limit, $count);

        gosRegex::checkError();

        return $ret;
    }

    /**
     * Wrapper for preg_match that turns (silent) errors into exceptions.
     *
     * @see http://us.php.net/preg_match
     * @throws  gosException_RegularExpression   On preg_last_error() != 0
     */
    public static function match($pattern, $subject, &$matches = null, $flags = null, $offset= null)
    {
        $ret = preg_match($pattern, $subject, $matches, $flags, $offset);

        gosRegex::checkError();

        return $ret;
    }

    /**
     * Wrapper for preg_match_all that turns (silent) errors into exceptions.
     *
     * @see http://us.php.net/preg_match_all
     * @throws  gosException_RegularExpression   On preg_last_error() != 0
     */
    public static function match_all($pattern, $subject, &$matches, $flags = null, $offset = null)
    {
        $ret = preg_match_all($pattern, $subject, $matches, $flags, $offset);

        gosRegex::checkError();

        return $ret;
    }

    /**
     * Wrapper for preg_quote, which can't error, but is represented for completeness.
     *
     * @see http://us.php.net/preg_quote
     */
    public static function quote($str, $delimiter = null)
    {
        return preg_quote($str, $delimiter);
    }

    /**
     * Wrapper for preg_replace that turns (silent) errors into exceptions.
     *
     * @see http://us.php.net/preg_replace
     * @throws  gosException_RegularExpression   On preg_last_error() != 0
     */
    public static function replace($pattern, $replacement, $subject, $limit = -1, &$count = null)
    {
        $ret = preg_replace($pattern, $replacement, $subject, $limit, $count);

        gosRegex::checkError();

        return $ret;
    }

    /**
     * Wrapper for preg_replace_callback that turns (silent) errors into exceptions.
     *
     * @see http://us.php.net/preg_replace_callback
     * @throws  gosException_RegularExpression   On preg_last_error() != 0
     */
    public static function replace_callback($pattern, $callback, $subject, $limit = -1, &$count = null)
    {
        // If the callback given is of class "self", then replace it with the calling class' name
        if (is_string($callback) && strpos($callback, 'self') === 0)
        {
            $backtrace = debug_backtrace(false);
            $callingClass = $backtrace[1]['class'];
            $callback = str_replace('self', $callingClass, $callback);
        }
        elseif (is_array($callback) && is_string($callback[0]) && strpos($callback[0], 'self') === 0)
        {
            $backtrace = debug_backtrace(false);
            $callingClass = $backtrace[1]['class'];
            $callback[0] = str_replace('self', $callingClass, $callback[0]);
        }

        $ret = preg_replace_callback($pattern, $callback, $subject, $limit, $count);

        gosRegex::checkError();

        return $ret;
    }

    /**
     * Wrapper for preg_split that turns (silent) errors into exceptions.
     *
     * @see http://us.php.net/preg_split
     * @throws  gosException_RegularExpression   On preg_last_error() != 0
     */
    public static function split($pattern, $subject, $limit = -1, $flags = 0)
    {
        $ret = preg_split($pattern, $subject, $limit, $flags);

        gosRegex::checkError();

        return $ret;
    }

    /**
     * Check preg_last_error() and throw an exception if it is non-zero.
     */
    protected static function checkError()
    {
        if (preg_last_error() != 0)
        {
            $message;
            switch (preg_last_error()) {
                case PREG_INTERNAL_ERROR:
                    $message = 'PREG_INTERNAL_ERRORR';
                    break;
                case PREG_BACKTRACK_LIMIT_ERROR:
                    $message = 'PREG_BACKTRACK_LIMIT_ERROR';
                    break;
                case PREG_RECURSION_LIMIT_ERROR:
                    $message = 'PREG_RECURSION_LIMIT_ERROR';
                    break;
                case PREG_BAD_UTF8_ERROR:
                    $message = 'PREG_BAD_UTF8_ERROR';
                    break;
                case PREG_BAD_UTF8_OFFSET_ERROR:
                    $message = 'PREG_BAD_UTF8_OFFSET_ERROR';
                    break;
            }

            throw new gosException_RegularExpression($message, get_defined_vars());
        }
    }
}
