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
 */

class gosDB_ConnParams
{
    /**
     * @var string $host
     */
    protected $host;

    /**
     * @var string $user
     */
    protected $user;

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var string $dbName
     */
    protected $dbName;

    /**
     * @var int $port
     */
    protected $port;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $dbName
     * @param int $port
     * @security BH/AK 2009-10-19
     */
    public function __construct($host, $user, $password, $dbName, $port)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbName = $dbName;
        $this->port = $port;
    }

    /**
     * @return string
     * @security BH/AK 2009-10-19
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     * @security BH/AK 2009-10-19
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     * @security BH/AK 2009-10-19
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     * @security BH/AK 2009-10-19
     */
    public function getDBName()
    {
        return $this->dbName;
    }

    /**
     * @return int
     * @security BH/AK 2009-10-19
     */
    public function getPort()
    {
        return $this->port;
    }

}
?>
