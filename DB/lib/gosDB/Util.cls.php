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

class gosDB_Util
{
    /**
     * @var array $validDBIdentifiers
     */
    protected static $validDBIdentifiers;

    /**
     * @var string
     */
    protected static $tmpdir = null;

    /**
     * Don't allow anyone to make an instance of this class
     * @security BH/AK 2009-10-19
     */
    private function __construct()
    {
    }

    /**
     * @param string $dbIdentifier
     * @return none
     * @throws gosException_InvalidArgument
     * @security BH/AK 2009-10-19
     */
    public static function validateDBIdentifier($dbIdentifier)
    {
        if (empty(self::$validDBIdentifiers))
        {
            throw new gosException('You must call setValidDBIdentifiers() before validating DB identifiers', get_defined_vars());
        }

        if (!in_array($dbIdentifier, self::$validDBIdentifiers))
        {
            throw new gosException_InvalidArgument('The dbIdentifier "'. $dbIdentifier .'" is not valid.', get_defined_vars());
        }
    }

    /**
     * @return array
     * @security BH/AK 2009-10-19
     */
    public static function getValidDBIdentifiers()
    {
        if (empty(self::$validDBIdentifiers))
        {
            throw new gosException('You must call setValidDBIdentifiers() before validating DB identifiers', get_defined_vars());
        }

        return self::$validDBIdentifiers;
    }

    public static function setValidDBIdentifiers(array $validDBIdentifiers)
    {
        self::$validDBIdentifiers = $validDBIdentifiers;
    }

    /**
     * Can be cached since it can't be changed during runtime.
     *
     * It is used in creating tmp files for 'insert into .. select ..' statement
     * to eliminate lock, thus improving performance and avoiding deadlock.
     *
     * @param db_handler    $db
     * @return string
     */
    public static function getTmpDir($db)
    {
        if (self::$tmpdir === null)
        {
            $row = $db->getRow("show variables like 'tmpdir'");
            self::$tmpdir = rtrim($row['Value'], '/');
        }

        return self::$tmpdir;
    }

    /**
     * Get a unique string for temp table/file name.
     * It consists of 4 parts:
     * 1). gosTemp:      To be ignored by replicants;
     * 2). UUID:        Get a hashed value based on time plus spatial data.
     *                  Remove the spatial part since they would be the same if
     *                  they are generated from the same DB server.
     * 3). MySQL thread ID:     To unique-ize the names for possible situations
     *                          that two or more mysql threads may call UUID()
     *                          at the same time.
     * 4). Random:      To unique-ize the names for possible situations that one
     *                  thread may be so fast that it calls the function more than
     *                  once within a microsecond. High impossible. Just in case.
     * @param   bool    $toBeReplicated
     * @param   string/DB  $prefix : prefix for the tmp table name, this must not
     *                              contain untrusted data
     * @return  string  name of the tmp file
     *
     * @security ALM/DS 2009-10-23
     */
    public static function createTempTableName($toBeReplicated, $prefix = '')
    {
        $rowConnectionID = mgDBGetRow("select connection_id() as connectionID");
        $connectionID = $rowConnectionID['connectionID'];

        $rowUUID = mgDBGetRow("select UUID() as UUID");
        $UUID = str_replace('-', '_', substr($rowUUID['UUID'], 0, 24));

        if ($toBeReplicated === true)
        {
            if (!is_string($prefix))
            {
                throw new gosException_InvalidArgument('$prefix is not string', get_defined_vars());
            }
            /* gosRegex::match() can be executed here to make sure $prefix contains
             * .. alphanumeric and underscore chars only. But it is expensive.
             * So we trust correct $prefix will be passed.
             * .. $prefix is not user-generated. ==> risk is very low.
             */
            if (trim($prefix) === '' || trim($prefix) == 'Temp')
            {
                throw new gosException_InvalidArgument('$prefix cannot be empty string for to-be-replicated tables', get_defined_vars());
            }
        }

        return 'gos' . trim($prefix) . 'Temp_' . $connectionID . '_' . $UUID . rand(1000000, 9999999);
    }

    /**
     * Creates a new temporary table with the given specification. So, if you
     * ... call this passing '(cola int, colb int) engine = heap' it will
     * ... run create temporary table <some random name> (cola int, colb int) engine = heap
     * @param string $createSpec The thing to put after create temporary table <some random name>
     * @param bool $toBeReplicated
     * @param string $prefix
     * @return string The name of the created table
     */
    public static function createUniqueTempTable($createSpec, $toBeReplicated, $prefix = '')
    {
        $mysqlErrno = 0;
        $dbTableName = '';
        do
        {
            // Put the temp table inside mgTmpDB so that we have delete powers
            $gosTableName = self::createTempTableName($toBeReplicated, $prefix);
            mgDBExecute('CREATE TEMPORARY TABLE mgTmpDB.' . $gosTableName . ' ' . $createSpec);
            $mysqlErrno = gosDB_Main::errorNo();
        }
        while ($mysqlErrno === 1050);

        return $gosTableName;
    }

    public static function dropMainTempTable($tempTableName)
    {
        $db = gosDB_Helper::getDBByName('main');
        $dropStmt = gosSafe_Gen_DB::getSafe(
            "DROP TEMPORARY TABLE mgTmpDB.{dbTable:tempTable}",
            array(
                'tempTable' => $tempTableName,
                )
            );
        $db->execute($dropStmt);
    }

}
