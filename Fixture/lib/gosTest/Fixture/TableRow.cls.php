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
class gosTest_Fixture_TableRow
{
    /**
     * @param array $rawColumnMap
     */
    private $rawColumnMap = array();

    /**
     * @var string $tableName Keeps track of which table this row belongs to
     * for error reporting purposes (makes it easier to find errors in fixture
     * files)
     */
    private $tableName;

    /**
     * @var string|null $rowLabel The label given to this row if there is one,
     *                            null otherwise.  Used for error reporting.
     */
    private $rowLabel;

    /**
     * Create an object to represent an individual row in a database table.
     *
     * @param gosDB_Base $db Database connection object to use for inserting the table row
     * @param string $tableName Name of the table this object will be representing
     * @param string|null $rowLabel @see self::$rowLabel
     * @param array $columns
     */
    public function __construct(gosDB_Base $db, $tableName, $rowLabel, array $rawColumnMap)
    {
        $escapedColumnMap = array();
        $autoIncrementColumn = null;

        // Iterate over every column and process special values (like <<auto>>)
        // ...Store sql escaped values into a different array than the raw values
        foreach ($rawColumnMap as $colName => $colValue)
        {
            gosTest_Fixture_Parser::assertAcceptableIdentifier('Column', $colName);

            // If it's an auto-increment column, don't add to insert array
            if ($colValue === '<<auto>>')
            {
                $autoIncrementColumn = $colName;
                continue;
            }
            $escapedColumnMap[$colName] = "'". $db->escape($colValue) ."'";
        }
        $this->insert($db, $tableName, $escapedColumnMap);
        $this->tableName = $tableName;
        $this->rowLabel = $rowLabel;

        // If an auto-increment column was found, add the value to the column map
        if ($autoIncrementColumn !== null)
        {
            // we get the gosException_StateError when calling this on a table
            // with no auto-increment column.  This will just give us the name
            // of the table on which we're getting the error.
            try
            {
                $rawColumnMap[$autoIncrementColumn] = $db->insertID();
            }
            catch (gosException_StateError $e)
            {
                throw new gosException_StateError($e->getMessage().' (in "'. $this->tableName . $this->getRowLabel() .'.'. $autoIncrementColumn .'")', $e->getContextVariables());
            }
        }

        // Set class variable to the raw (non-SQL escaped) values map
        $this->rawColumnMap = $rawColumnMap;
    }

    /**
     * Get the value of the passed column in the current row.
     *
     * @param string $columnName The name of the column to map to a value in the row
     * @return mixed The value in the current row for the column name passed
     */
    public function getColumnValue($columnName)
    {
        if (!isset($this->rawColumnMap[$columnName]))
        {
            throw new gosException_InvalidArgument('Column is not set in column/value mapping: "'. $this->tableName . $this->getRowLabel() .'.'. $columnName .'".', get_defined_vars());
        }
        return $this->rawColumnMap[$columnName];
    }

    /**
     * @return array Key => value pairs
     */
    public function getArrayMap()
    {
        return $this->rawColumnMap;
    }

    /**
     * Insert the passed table row into the db table passed.
     *
     * @param gosDB_Base $db The database connection object to use for insertion
     * @param string $tableName Name of the table to insert this row into
     * @param array $escapedColumnMap ColumnName => Value associative array to use for inserting into passed table
     * @return gosTest_Fixture_Table Object representing the db table that we are inserting into and encapsulating all inserted rows
     */
    protected function insert(gosDB_Base $db, $tableName, $escapedColumnMap)
    {
        $nameStr = implode(',', array_keys($escapedColumnMap));
        $valueStr = implode(',', array_values($escapedColumnMap));

        $stmt = "INSERT INTO " . $tableName . " (" . $nameStr .
        ") VALUES (" . $valueStr . ")";
        $db->execute($stmt);
    }

    /**
     * Gets the row label for outputting error messages (prepended with a '.'
     * if it is set)
     *
     * @return string
     */
    protected function getRowLabel()
    {
        if ($this->rowLabel === null)
        {
            return '';
        }
        else
        {
            return '.'. $this->rowLabel;
        }
    }

}
