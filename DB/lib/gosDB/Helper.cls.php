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

class gosDB_Helper
{
    /**
     * The factory we'll use to create the database connection
     * @var gosDB_ConnFactory $dbConnFactory
     */
    private static $dbConnFactory = null;

    /**
     * Resets the db factory to be a new factory that uses the specified conn
     * params factory.
     * @param gosDB_ConnParamsCollection $params
     * @security BH/AK 2009-10-19
     */
    public static function setDBConnParamsCollection(gosDB_ConnParamsCollection $params)
    {
        self::$dbConnFactory = new gosDB_ConnFactory($params);
        gosDB_Util::SetValidDBIdentifiers($params->getDBs());
    }

    /**
     * @param string $dbIdentifier set the name of the default db.
     * @return gosDB_Base Database object to use to query requested DB
     * @security BH/AK 2009-10-19
     * @security DL/SL 2009-10-27
     */
    public static function getDBByName($dbIdentifier)
    {
        gosDB_Util::validateDBIdentifier($dbIdentifier);
        $db = self::$dbConnFactory->get($dbIdentifier);
        $db->setCharset('utf8');
        $db->setLogger(Log5PHP_Manager::getLogger('gosdb.mysqli'));
        return $db;
    }
}
