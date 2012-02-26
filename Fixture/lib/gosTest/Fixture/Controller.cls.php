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
class gosTest_Fixture_Controller
{
    /**
     * @var array Cache of fixture controllers for different datbases
     */
    private static $cachedWrapper = array();

    /**
     * Static factory for creating, caching, and returning database fixture controllers.
     *
     * @param string $dbName Name of the database object to use for loading fixtures
     * @return gosTest_Fixture_Controller Fixture controller for connecting to the requested DB
     */
    public static function getByDBName($dbName)
    {
        // If this fixture controller has not already been constructed, create it and
        // ...store it in a object cache
        if (!isset(self::$cachedWrapper[$dbName]))
        {
            $dbObj = gosDB_Helper::getDBByName($dbName);
            self::$cachedWrapper[$dbName] = new gosTest_Fixture_Wrapper($dbObj);
        }
        return self::$cachedWrapper[$dbName];
    }

    /**
     * Delete all rows created in all databases as part of fixture setup and unset the cached
     * fixture controllers
     */
    public static function reset()
    {
        // Delete all rows from all db controllers and unset them from the cache
        foreach (self::$cachedWrapper as $dbName => $wrapper)
        {
            $wrapper->deleteAll();
        }
    }
}
