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
class gosUtility_URLStitcher
{
    /**
     * Build an absolue URL from pieces
     *
     * @param   string  $scheme
     * @param   string  $host
     * @param   string  $port
     * @param   string  $user
     * @param   string  $pass
     * @param   string  $path
     * @param   string  $query
     * @param   string  $fragment
     *
     * @return  string  The URL pieces assembled
     */
    public static function absolute($scheme, $host, $port, $user, $pass, $path, $query, $fragment)
    {
        // Reassemble the URL
        $newURL = $scheme . '://';

        if (!empty($user))
        {
            $host = "@" . $host;
            $newURL .= $user;
        }
        if (!empty($pass))
        {
            $newURL .= ":" . $pass;
        }

        $newURL .= $host;

        if (!empty($port))
        {
            $newURL .= ":" . $port;
        }

        if (!empty($path))
        {
            $newURL .= $path;
        }

        if (!empty($query))
        {
            $newURL .= "?" . $query;
        }

        if (!empty($fragment))
        {
            $newURL .= "#" . $fragment;
        }

        return $newURL;
    }

    /**
     * Build a relative URL from pieces
     *
     * @param   string  $path
     * @param   string  $query
     * @param   string  $fragment
     *
     * @return  string  The URL pieces assembled
     */
    public static function relative($path, $query, $fragment)
    {
        $newURL = '';

        if (!empty($path))
        {
            $newURL .= $path;
        }

        if (!empty($query))
        {
            $newURL .= "?" . $query;
        }

        if (!empty($fragment))
        {
            $newURL .= "#" . $fragment;
        }

        return $newURL;
    }
}
