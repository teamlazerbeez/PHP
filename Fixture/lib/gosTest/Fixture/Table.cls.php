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
class gosTest_Fixture_Table
{
    private $rowMapping = array();
    private $tableName;

    /**
     * Create the db table representation.  We need to pass in the table name that it is representing
     * so that it can construct and execute DB queries to insert rows
     *
     * @param string $tableName Name of the table this object will be representing
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Get an array of all table rows
     * @return array array of all rows inserted into the table with this fixture
     */
    public function getTableRows()
    {
        return $this->rowMapping;
    }

    /**
     * Get the table row object indexed by the key passed.
     *
     * @param string $key The associative index of the gosTest_Fixture_TableRow object to return
     * @return gosTest_Fixture_TableRow The object representation of the table row requested
     */
    public function getTableRow($key=0)
    {
        if (!isset($this->rowMapping[$key]))
        {
            throw new gosException_InvalidArgument('The key passed <'. $key .'> does not map to a valid row in table <'. $this->tableName .'>', get_defined_vars());
        }

        return $this->rowMapping[$key];
    }

    /**
     * Add the passed table row to the set of rows stored for this table.  Append it to the
     * end of the array which will cause an auto-generated key
     *
     * @param gosTest_Fixture_TableRow $tableRowObj Representation of a single row in the current table
     */
    public function addTableRow(gosTest_Fixture_TableRow $tableRowObj)
    {
        $this->rowMapping[] = $tableRowObj;
    }

    /**
     * Add the passed table row to the set of rows stored for this table.  Use the key passed
     * to index the row so that it is easy to pull this individual row from the table.
     *
     * @param gosTest_Fixture_TableRow $tableRowObj Representation of a single row in the current table
     * @param string $key Label to use to index the passed row if multiple rows are being stored for this table
     * @throws gosException_InvalidArgument If the key already is in use
     */
    public function addKeyedTableRow(gosTest_Fixture_TableRow $tableRowObj, $key)
    {
        // If the key passed is already in use, throw an exception
        if (isset($this->rowMapping[$key]))
        {
            throw new gosException_InvalidArgument('Row key ('. $key .') already exists for table ('. $this->tableName .')', get_defined_vars());
        }
        $this->rowMapping[$key] = $tableRowObj;
    }

    /**
     * Retrieve the name of the table that this object represents
     * @return string Name of the table
     */
    public function getTableName()
    {
        return $this->tableName;
    }
}
