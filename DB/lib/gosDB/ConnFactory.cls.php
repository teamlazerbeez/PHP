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

class gosDB_ConnFactory
{
    /**
     * @var gosDB_ConnParamsCollection conn params used to generate configuration objects which are
     * then used to create db connections
     */
    protected $connParamsCollection;

    /**
     * @var array/map {db identifier: gosDB_Mysqli}
     */
    protected $connCache = array();

    /**
     * @param gosDB_ConnParamsCollection $connParamsCollection
     * @security BH/AK 2009-10-19
     */
    public function __construct(gosDB_ConnParamsCollection $connParamsCollection)
    {
        $this->connParamsCollection = $connParamsCollection;
    }

    /**
     * Gets a connection to the specified database.
     * @param string $dbIdentifier set the name of the default db.
     * @return gosDB_Base Database connection object to use to query requested DB
     * @security BH/AK 2009-10-19
     */
    public function get($dbIdentifier)
    {
        // Invalid database identifier passed in
        gosDB_Util::validateDBIdentifier($dbIdentifier);

        // avoid re-creating the db connection if it's already been made
        if (!array_key_exists($dbIdentifier, $this->connCache))
        {
            $this->connCache[$dbIdentifier] = $this->createConnectionForDB($dbIdentifier);
        }

        return $this->connCache[$dbIdentifier];
    }

    /**
     * Create and cache the connection for the specified name
     * @param string $dbIdentifier
     * @return gosDB_Base A connection to a database
     * @security BH/AK 2009-10-19
     */
    protected function createConnectionForDB($dbIdentifier)
    {
        $config = $this->connParamsCollection->getParams($dbIdentifier);

        return new gosDB_Mysqli($config);
    }

}

?>
