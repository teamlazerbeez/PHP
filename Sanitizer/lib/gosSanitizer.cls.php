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
 */
class gosSanitizer
{
    protected static $HTMLAttributeTranslationTable;
    protected static $inverseHTMLAttributeTranslationTable;
    protected static $JSTranslationTable;
    protected static $CSSTranslationTable;
    protected static $XMLTranslationTable;

    /**
     * Get the hex value of an integer.
     *
     * @param int $int The number to convert (< 255)
     * @return string of length 2 representing that number in hexadecimal
     */
    protected static function getHexForInt($int)
    {
        if ($int > 255)
        {
            throw new gosException_InvalidArgument('This function does not currently support integers of more than 8 bits', get_defined_vars());
        }

        return str_pad(dechex($int), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get the array used for translating HTML special characters into
     * escape sequences.
     *
     * @return Array a map from ASCII characters to &#xHH; encodings
     */
    protected static function getHTMLAttributeTranslationTable()
    {
        if (!self::$HTMLAttributeTranslationTable)
        {
            $table = array();
            $valuesToEncode = array_merge(range(0, 47), range(58, 64), range(91, 96), range(123, 127));
            foreach ($valuesToEncode as $i)
            {
                $table[chr($i)] = '&#x' . self::getHexForInt($i) . ';';
            }
            self::$HTMLAttributeTranslationTable = $table;
        }

        return self::$HTMLAttributeTranslationTable;
    }

    /**
     * Get the array used for translating numeric values of HTML escape
     * sequences into characters.
     *
     * @return array map of &#xHH; encodings to ascii characters
     */
    protected static function getInverseHTMLAttributeTranslationTable()
    {
        if (!self::$inverseHTMLAttributeTranslationTable)
        {
            $forwardTable = self::getHTMLAttributeTranslationTable();

            $inverseTable = array();

            foreach ($forwardTable as $key => $val)
            {
                $inverseTable[$val] = $key;
            }

            self::$inverseHTMLAttributeTranslationTable = $inverseTable;
        }

        return self::$inverseHTMLAttributeTranslationTable;
    }

    /**
     * Get the array used for translating JavaScript special characters into
     * escape sequences.
     *
     * @return Array a map from ASCII characters to \xHH encodings
     */
    protected static function getJSTranslationTable()
    {
        if (!self::$JSTranslationTable)
        {
            $table = array();
            $valuesToEncode = array_merge(range(0, 47), range(58, 64), range(91, 96), range(123, 127));
            foreach ($valuesToEncode as $i)
            {
                $table[chr($i)] = '\x' . self::getHexForInt($i);
            }
            self::$JSTranslationTable = $table;
        }

        return self::$JSTranslationTable;
    }

    /**
     * Get the array used for translating numeric JavaScript escape sequences
     * into characters.
     *
     * @return Array a map from ASCII characters to \HH encodings
     */
    protected static function getCSSTranslationTable()
    {
        if (!self::$CSSTranslationTable)
        {
            $table = array();
            $valuesToEncode = array_merge(range(0, 47), range(58, 64), range(91, 96), range(123, 127));
            foreach ($valuesToEncode as $i)
            {
                $table[chr($i)] = '\\' . self::getHexForInt($i);
            }
            self::$CSSTranslationTable = $table;
        }

        return self::$CSSTranslationTable;
    }

    /**
     * Get the array used for translating XML special characters into
     * escape sequences.
     *
     * @return Array a map from ASCII characters to &#xHH; encodings
     */
    protected static function getXMLTranslationTable()
    {
        if (!self::$XMLTranslationTable)
        {
            // Decimal values for: " & ' < >
            $valuesToEncode = array(34, 38, 39, 60, 62);

            $table = array();
            foreach ($valuesToEncode as $i)
            {
                $table[chr($i)] = '&#x' . self::getHexForInt($i) . ';';
            }
            self::$XMLTranslationTable = $table;
        }

        return self::$XMLTranslationTable;
    }

    /**
     * Throw an exception if the string contains an invalid UTF-8 character.
     * See: http://devlog.info/2008/08/24/php-and-unicode-utf-8/
     *
     * @param string $string The string to sanitize
     *
     * @return The given string.
     */
    public static function ensureValidUTF8($string)
    {
        if (strlen($string) AND !preg_match('/^.{1}/us', $string)) {
            throw new gosException_Security('Invalid UTF-8 character', get_defined_vars());
        }

        return $string;
    }

    /**
     * Ensure a string contains only valid characters for a MySQL column name.
     *
     * @param string $name the name of the db column to ensure
     * @return string/SQL The string passed in, if it didn't throw an exception
     */
    public static function ensureDBColumn($name)
    {
        if (!preg_match("/^[\w.]+$/D", $name))
        {
            throw new gosException_Security('Invalid DB column', get_defined_vars());
        }

        return $name;
    }

    /**
     * Ensure a string contains only valid characters for a MySQL table name.
     *
     * @param string $name the name of the db table to ensure
     * @return string/SQL snippet The string passed in, if it didn't throw an exception
     */
    public static function ensureDBTable($name)
    {
        if (!preg_match('/^[\w]+$/D', $name))
        {
            throw new gosException_Security('Invalid DB table: "' . $name . '".', get_defined_vars());
        }

        return $name;
    }

    /**
     * Ensure a string is a valid MySQL sort.
     *
     * @param string $name a string of column[s] to ensure are sane, including asc/desc
     * @return string the string passed in, if it didn't throw an exception
     */
    public static function ensureDBSortColumns($order)
    {
        $cols = explode(",", $order);
        foreach ($cols as $col)
        {
            $col = trim($col);
            if (!preg_match("/^[\w.]+ (asc|desc)$/iD", $col))
            {
                throw new gosException_Security('Invalid DB sort columns: "' . $order . '".', get_defined_vars());
            }
        }

        return $order;
    }

    /**
     * Ensure a string is valid for a JavaScript variable name.
     *
     * @param string $name the name of a JS variable to ensure is allowed variable characters
     * @return string the string passed in if it didn't throw an exception
     */
    public static function ensureJSVariableName($name)
    {
        if (!preg_match('/^[\w$]+$/D', $name))
        {
            throw new gosException_Security('Invalid JS Variable name: "' . $name . '".', get_defined_vars());
        }

        return $name;
    }

    /**
     * Escape any special characters in the given string so that it can be
     * safely used in HTML content.
     *
     * @param string The data
     * @return string The data, sanitized to be placed in the contents of an HTML element (e.g. <div>DATA</div>)
     */
    public static function sanitizeForHTMLContent($data)
    {
        self::ensureValidUTF8($data);
        return str_replace('/', '&#x2F;', htmlentities($data, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Escape any special characters in the given string so that it can be
     * safely used in HTML attributes.
     *
     * @param string The data
     * @return string The data, sanitized to be placed in the attribute of an HTML element (e.g. <div width="DATA"></div>)
     * Note: You MUST still put the attribute in quotes.  This is not safe unless in quotes.
     * Note: This is NOT sufficient for sanitizing URLs.  Use getForHTMLURL() instead.
     * Note: This is NOT sufficient for sanitizing JavaScript for attributes.  Use getForJS() instead.
     * Note: This is NOT sufficient for sanitizing CSS in attributes.  Use getForCSS() instead.
     */
    public static function sanitizeForHTMLAttribute($data)
    {
        self::ensureValidUTF8($data);
        return strtr($data, self::getHTMLAttributeTranslationTable());
    }

    /**
     * Replace HTML Attribute escape sequences with their character equivalents.
     *
     * @param string html attribute escaped data
     * @return string the string with html attribute escaped chars replaced with their normal ASCII equivs
     */
    public static function desanitizeForHTMLAttribute($data)
    {
        self::ensureValidUTF8($data);
        return strtr($data, self::getInverseHTMLAttributeTranslationTable());
    }

    /**
     * Properly encode a value representing a complete, absolute URL. No query
     * params allowed, though, as they cannot be parsed unambiguously when
     * unencoded, and will be double-encoded if already encoded.
     *
     * Use gosUtility_URLBuilder if you need query params.
     *
     * @param string The data
     * @return string The data, sanitized to be placed in a URL attribute of an HTML element (e.g. <img src="DATA" />)
     * Note: You MUST still put the URL in quotes.  This is not safe unless in quotes.
     */
    public static function sanitizeForAbsoluteURL($data)
    {
        self::ensureValidUTF8($data);
        return gosUtility_URLBuilder::createForAbsoluteUrl($data)->toString();
    }

    /**
     * Properly encode a value representing a relative URL. No query params
     * allowed, though, as they cannot be parsed unambiguously when unencoded,
     * and will be double-encoded if already encoded.
     *
     * Use gosUtility_URLBuilder if you need query params.
     *
     * @param string The data
     * @return string The data, sanitized to be placed in a URL attribute of an HTML element (e.g. <img src="DATA" />)
     * Note: You MUST still put the URL in quotes.  This is not safe unless in quotes.
     */
    public static function sanitizeForRelativeURL($data)
    {
        self::ensureValidUTF8($data);

        // pull out data in the form: path?query#fragment
        $regex = '!([^?\#]*)    # Grab the path (anything but a question mark or octothorpe)
                    \??         # Stop getting path at a question mark (if it exists)
                    ([^\#]*)    # Grab the query string (anything but an octothorpe)
                    \#?         # Stop getting query at an octothorpe
                    (.*)        # Everything else is the fragment
                !x';            // Ignore whitespace & allow comments in this regex
        preg_match($regex, $data, $urlMatches);

        $path = $urlMatches[1];
        $query = $urlMatches[2];
        $fragment = $urlMatches[3];

        if (strlen($query) > 0)
        {
            throw new gosException_InvalidArgument('Cannot provide a query string; query params must be added afterwards to ensure correctness',
                get_defined_vars());
        }


        $cleanPath = self::sanitizeURLPathString($path);
        $cleanFragment = self::sanitizeForURLComponent($fragment);

        // no query
        $cleanURL = gosUtility_URLStitcher::relative($cleanPath, '', $cleanFragment);
        return $cleanURL;
    }

    /**
     * Encode the path portion of a URL.
     *
     * @param string $pathString
     * @return string the given path with each component sanitized
     */
    public static function sanitizeURLPathString($pathString)
    {
        self::ensureValidUTF8($pathString);

        // We don't want to encode the slashes in the path, so we encode each path element individually
        if ($pathString)
        {
            $pathSeparator = '/';

            $pathParts = explode($pathSeparator, $pathString);
            $cleanParts = array();
            foreach ($pathParts as $part)
            {
                $cleanParts[] = self::sanitizeForURLComponent($part);
            }
            $cleanPath = implode($pathSeparator, $cleanParts);
        }
        else
        {
            $cleanPath = '';
        }

        return $cleanPath;
    }

    /**
     * Break apart and unescape a URL query string.
     *
     * @param string $queryString
     * @return array map of query param names to query values, unescaped
     */
    public static function explodeURLQueryString($queryString)
    {
        self::ensureValidUTF8($queryString);

        $queryArray = array();

         // We don't want to encode the '&' and '=', so we split up the query and encode the individual elements
        if ($queryString)
        {
            // NOTE: We dont' support the ';' separator, although it is actually valid
            $querySeparator = '&';
            $queryParts = explode($querySeparator, $queryString);

            foreach ($queryParts as $pair)
            {
                $pairPieces = explode('=', $pair);
                $key = $pairPieces[0];
                $queryArray[$pairPieces[0]] = array_key_exists(1, $pairPieces) ? $pairPieces[1] : '';
            }
        }

        return $queryArray;
    }

    /**
     * @param string $queryString
     * @return string the given query with all key's and value's sanitized
     */
    protected static function sanitizeURLQueryString($queryString)
    {
        return http_build_query(self::explodeURLQueryString($queryString));
    }

    /**
     * @param string The data
     * @return string The data, sanitized to be placed in a URL attribute of an HTML element (e.g. <img src="DATA" />)
     *
     * Note: You MUST still put the URL in quotes.  This is not safe unless in quotes.
     * This is for individual components, not groups of components separated by
     * special characters. Examples of valid input are: a query parameter key,
     * a query parameter value, one directory or file name in a path.
     */
    public static function sanitizeForURLComponent($data)
    {
        self::ensureValidUTF8($data);

        return urlencode($data);
    }

    /**
     * @param string The data
     * @return string The data, sanitized to be placed as a value (NOT a property) in CSS (e.g. <div style="background-color: DATA;"></div>)
     *
     * Note: You MUST still put the URL in quotes.  This is not safe unless in quotes.
     */
    public static function sanitizeForCSS($data)
    {
        self::ensureValidUTF8($data);

        return strtr($data, self::getCSSTranslationTable());
    }

    /**
     * @param string The data
     * @return string The data, sanitized to be placed in JavaScript (e.g. <a onclick="DATA"></a>)
     */
    public static function sanitizeForJS($data)
    {
        self::ensureValidUTF8($data);

        return strtr($data, self::getJSTranslationTable());
    }

    /**
     * @param string The data
     * @return string The data, sanitized to be placed in XML (e.g. <foo attribute="DATA"></foo>)
     */
    public static function sanitizeForXML($data)
    {
        self::ensureValidUTF8($data);

        return strtr($data, self::getXMLTranslationTable());
    }

    /**
     * @param string The data
     * @param gosDB_Base $db The database against which to run the escape call (defaults to 'main')
     * @return string The data, sanitized to be placed in an SQL query
     */
    public static function sanitizeForDB($data, gosDB_Base $db = null)
    {
        if (!$db)
        {
            $db = gosDB_Helper::getDBByName('main');
        }

        self::ensureValidUTF8($data);
        return $db->escape($data);
    }

    /**
     * @param value Value to ensure is an integer
     * @return The given value, if it was an integer
     * @throws gosException_InvalidArgument if given something other than an integer
     */
    public static function ensureInt($value)
    {
        // Be sure we have gotten bool(false), not just 0
        if (filter_var($value, FILTER_VALIDATE_INT) !== false)
        {
            return (int)$value;
        }

        throw new gosException_InvalidArgument('Integer expected, was <' . $value . '>', get_defined_vars());
    }

    /**
     * @param value Value to ensure is a positive integer
     * @return The given value, if it was a positive integer
     * @throws gosException_InvalidArgument if given something other than a positive integer
     */
    public static function ensurePosInt($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) !== false)
        {
            return (int)$value;
        }

        throw new gosException_InvalidArgument('Positive integer expected, was <' . $value . '>', get_defined_vars());
    }

    /**
     * @param value Value to ensure is a float
     * @return The given value, if it was a float
     * @throws gosException_InvalidArgument if given something other than a float
     */
    public static function ensureFloat($value)
    {
        if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false)
        {
            return (float)$value;
        }

        throw new gosException_InvalidArgument('Float expected, was <' . $value . '>', get_defined_vars());
    }


    /**
     * @param value Value to ensure is a DB compatible boolean
     * @return The given value, as int(0) or int(1)
     * @throws gosException_InvalidArgument if given something uncoercable
     */
    public static function ensureDBBool($value)
    {
        // Because of PHP's loose casting, we need to be very explicit here
        if ($value === 0 || $value === '0' || $value === false ||
            $value === 1 || $value === '1' || $value === true)
        {
            return (int)$value;
        }

        throw new gosException_InvalidArgument('DB bool expected, was <' . $value . '>.', get_defined_vars());
    }
}
