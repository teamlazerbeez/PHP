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
class gosUtility_URLBuilder
{
    /**
     * @var applicationRoot The root URL of the application.
     */
    public static $applicationRoot = 'http://CONFIGURE_ME';

    private $scheme;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $path;
    private $query;
    private $fragment;

    /**
     * Params should come in without any escaping.
     *
     * @param string $scheme
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $path
     * @param string $fragment
     */
    protected function __construct($scheme, $host, $port, $user, $pass, $path, array $queryParams, $fragment)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->path = $path;
        $this->fragment = $fragment;

        $this->queryParams = $queryParams;
    }

    /**
     * Set a query param (overwrites any previous param of the same name)
     *
     * @param $name param name
     * @param $value param value
     */
    public function setQueryParam($name, $value)
    {
        $this->queryParams[$name] = $value;
    }

    /**
     * Set many query params.
     *
     * @param array $paramData Map of param names to param values
     */
    public function setQueryParamsFromArray(array $queryArr)
    {
        foreach ($queryArr as $pName => $pValue)
        {
            $this->setQueryParam($pName, $pValue);
        }
    }

    /**
     * @param string $queryStr Properly url-encoded query string
     */
    public function setQueryParamsFromUrlEncodedQueryString($queryStr)
    {
        $this->setQueryParamsFromArray(self::extractQueryParamsFromString($queryStr));
    }

    /**
     * @return string/URL The host of the URL
     */
    public function getHost()
    {
        return gosSanitizer::sanitizeForURLComponent($this->host);
    }

    /**
     * @return string The url (url encoded)
     */
    public function toString()
    {
        $cleanScheme = gosSanitizer::sanitizeForURLComponent($this->scheme);
        $cleanHost = $this->getHost();
        $cleanPort = gosSanitizer::sanitizeForURLComponent($this->port);
        $cleanUser = gosSanitizer::sanitizeForURLComponent($this->user);
        $cleanPass = gosSanitizer::sanitizeForURLComponent($this->pass);
        $cleanPath = gosSanitizer::sanitizeURLPathString($this->path);
        $cleanQuery = http_build_query($this->queryParams);
        $cleanFragment = gosSanitizer::sanitizeForURLComponent($this->fragment);

        return gosUtility_URLStitcher::absolute($cleanScheme, $cleanHost, $cleanPort, $cleanUser,
            $cleanPass, $cleanPath, $cleanQuery, $cleanFragment);
    }

    /**
     * Because this is an unescaped url, query parameters are not allowed since they cannot be
     * conclusively split.
     *
     * @param string $url unescaped absolute url.
     * @return gosUtility_URLBuilder
     */
    public static function createForAbsoluteUrl($url)
    {
        $urlParts = parse_url($url);

        $urlParts['query'] = array_key_exists('query', $urlParts) ? $urlParts['query'] : '';

        if (strlen($urlParts['query']) > 0)
        {
            throw new gosException_InvalidArgument('Cannot provide a query string; query params must be added afterwards to ensure correctness',
                 get_defined_vars());
        }

        return self::createForAbsoluteUrlWithEncodedQueryParams($url);
    }

    /**
     * This must be used ONLY when you know that you are using query parameters that have already been url encoded.
     *
     * @param string $urlWithQueryParams an absolute url includeing properly escaped query parameters.
     * @return gosUtility_URLBuilder
     */
    public static function createForAbsoluteUrlWithEncodedQueryParams($urlWithQueryParams)
    {
        $urlParts = parse_url($urlWithQueryParams);

        $urlParts['scheme'] = array_key_exists('scheme', $urlParts) ? $urlParts['scheme'] : '';
        $urlParts['host'] = array_key_exists('host', $urlParts) ? $urlParts['host'] : '';
        $urlParts['port'] = array_key_exists('port', $urlParts) ? $urlParts['port'] : '';
        $urlParts['user'] = array_key_exists('user', $urlParts) ? $urlParts['user'] : '';
        $urlParts['pass'] = array_key_exists('pass', $urlParts) ? $urlParts['pass'] : '';
        $urlParts['path'] = array_key_exists('path', $urlParts) ? $urlParts['path'] : '';
        $urlParts['query'] = array_key_exists('query', $urlParts) ? $urlParts['query'] : '';
        $urlParts['fragment'] = array_key_exists('fragment', $urlParts) ? $urlParts['fragment'] : '';

        return new self($urlParts['scheme'], $urlParts['host'], $urlParts['port'], $urlParts['user'],
            $urlParts['pass'], $urlParts['path'], self::extractQueryParamsFromString($urlParts['query']),
             $urlParts['fragment']);
    }

    /**
     * @param string $appRootUrl unescaped path relative to self::$applicationRoot (no leading /)
     * @return gosUtility_URLBuilder
     * @see createForAbsoluteUrl
     */
    public static function createForAppRootUrl($appRootUrl)
    {
        $url = self::$applicationRoot . $appRootUrl;
        return self::createForAbsoluteUrl($url);
    }

    /**
     * @param string $appRootUrl unescaped path relative to self::$applicationRoot (no leading /)
     * @return gosUtility_URLBuilder
     * @see createForAbsoluteUrl
     */
    public static function createForAppRootUrlWithEncodedQueryParams($url)
    {
        $appRootUrl = self::$applicationRoot . $url;
        return self::createForAbsoluteUrlWithEncodedQueryParams($appRootUrl);
    }

    /**
     * @param string $queryString full query string of url-encoded params
     * @return array map of query names to query values (url decoded)
     */
    private static function extractQueryParamsFromString($queryString)
    {
        parse_str($queryString, $queryArr);

        // case 7036: If get_magic_quotes_gpc is enabled, the strings we get from parse_str will be
        //   escaped with slashes. Check to see if this setting is enabled and, if so, unescape everything.
        if (get_magic_quotes_gpc())
        {
            $queryArr = gosUtility_String::stripslashesDeep($queryArr);
        }

        return $queryArr;
    }
}
