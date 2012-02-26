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
class gosTest_Fixture
{
    /**
     * One entry for every table that has rows included in this fixture
     */
    private $tables = array();

    /**
     * Return whether the passed table has already been included in this fixture
     *
     * @param string $tableName The name of the table to check for
     * @return bool True if the table is already part of the fixture, false otherwise
     */
    public function hasTable($tableName)
    {
        return isset($this->tables[$tableName]);
    }

    /**
     * Add the passed table representation to the list of tables covered by this fixutre
     *
     * @param gosTest_Fixture_Table $fixtureTable Representation of all rows in the given table covered by this fixture
     * @throws gosException_StateError If the table has already been added to the fixture
     */
    public function addTable(gosTest_Fixture_Table $fixtureTable)
    {
        // If The table is already in the fixture, something is fishy
        if (isset($this->tables[$fixtureTable->getTableName()]))
        {
            throw new gosException_StateError('Table ('. $fixtureTable->getTableName() .') is already in the fixture', get_defined_vars());
        }
        $this->tables[$fixtureTable->getTableName()] = $fixtureTable;
    }

    /**
     * Retrieve the gosTest_Fixture_Table object that contains references to all rows in the passed table name
     * that exist for this fixture
     *
     * @param string $tableName Name of db table that has rows as part of this fixture
     * @return gosTest_Fixture_Table Representation of all rows in the passed table include in this fixture
     * @throws gosException_InvalidArgument If the passed table name is not part of this fixture
     */
    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName]))
        {
            throw new gosException_InvalidArgument('Table does not exist in fixture: '. $tableName, get_defined_vars());
        }
        return $this->tables[$tableName];
    }
}
