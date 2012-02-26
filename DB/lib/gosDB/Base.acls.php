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

abstract class gosDB_Base
{
    /**
     * @param gosDB_ConnParams parameter object
     */
    abstract public function __construct(gosDB_ConnParams $params);

    /**
     * Execute passed sql query. Should be used for insert/update.
     * @param string $sql Sql query to execute
     * @throws gosException_DB if query execution fails.
     */
    abstract public function execute($sql);

    /**
     * Execute passed sql query and return a 2D array containing all rows returned from
     * execution.
     * @param string $sql Sql query to execute
     * @return array 2D array containing one row per result row. Empty array if query returns no results.
     * @throws gosException_DB if query execution fails.
     */
    abstract public function getAll($sql);

    /**
     * Execute passed sql query and return a 1D array containing column => value mapping.
     * @param string $sql Sql query to execute
     * @return array 1D array containing column => value mapping for the first
     * row returned from query execution. empty array if query returns no
     * results.
     * @throws gosException_DB if query execution fails.
     */
    abstract public function getRow($sql);

    /**
     * Execute passed sql query and return a single value matching the first column of the first row.
     * @param string $sql Sql query to execute
     * @return string|false String representation of the first column in the first row matched. false if query returns no results.
     * @todo Should this thrown an exception if more than one row or more than
     * one column within a row are found? RAC 12/03/07 [mbp: I think it should
     * -- have a getFirst that tolerates multiple results.]
     * @throws gosException_DB if query execution fails.
     */
    abstract public function getOne($sql);

    /**
     * Execute passed query.
     * @param string $sql Sql query to execute
     * @return resource Database resource that can be stepped through
     * @todo Should this be private? RAC 12/03/07
     * @throws gosException_DB if query execution fails.
     */
    abstract protected function queryError($sql);

    /**
     * Begin a new db transaction.  Since mysql doesn't allow nested transactions,
     * throw an exception if an attempt to start a transaction is made while a previously
     * started transaction is still open.
     */
    abstract public function startTransaction();

    abstract public function commitTransaction();

    abstract public function rollbackTransaction();

    /**
     * @return bool true if the db currently has an open transaction
     */
    abstract public function isInTransaction();

    /**
     * Prepare a value for being included in a db query by escaping characters that
     * could be used for sql injection or cause other weird behavior
     * @param mixed $unescapedValue Value that needs to be ecaped before being included in query.
     * @return string Properly escaped version of the value passed.
     * @todo Does this always return a string? RAC 12/03/07
     */
    abstract public function escape($unescapedValue);

    /**
     * Return the auto-increment ID of the most recently inserted row by the db connection
     * included in this object.
     * @return int Auto-increment row ID of the most recently inserted row by the
     * private db connection.
     * @todo What happens if no insert has been made? What does insert_id() return? RAC 12/03/07
     */
    abstract public function insertID();

    abstract public function affectedRows();

    /**
     * @return bool
     */
    abstract public function isConnected();

}
