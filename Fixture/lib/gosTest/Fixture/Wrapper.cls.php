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
class gosTest_Fixture_Wrapper
{
    /**
     * @var array: Array of tables (string tableName => boolean true) that cannot be included in a normal fixture
     */
    private $initialTables = array();

    /**
     * @var gosDB_Base db to connect to
     */
    private $db;

    /**
     * @var gosTest_Fixture_FixtureCollection Objects that hold fixtures that
     *      have been loaded into this DB and tables that have been inserted
     *      into
     */
    protected $fixtureCollection;
    protected $initialFixtureCollection;

    /**
     * @var array $fixtureEntriesCache Array of filePath => fixtureEntriesCache
     */
    private static $fixtureEntriesCache = array();

    /**
     * The constructor to create controller that operates on the passed db connection object. This is protected
     * so that people will be forced to use the static creation method and thus using the controlled cache.
     * @todo: this is not actually protected
     * @param gosDB_Base $db Database connection object the controllers should use to connect to the database to load fixtures
     */
    public function __construct(gosDB_Base $db)
    {
        $this->db = $db;
        $this->fixtureCollection = new gosTest_Fixture_FixtureCollection();
        $this->initialFixtureCollection = new gosTest_Fixture_FixtureCollection();
    }

    /**
     * @see self::parseFixtureFileHelper()
     */
    public function parseFixtureFile($filePath)
    {
        $this->parseFixtureFileHelper($filePath, $this->fixtureCollection);
    }

    /**
     * @see self::parseFixtureFileHelper()
     */
    public function parseInitialFixtureFile($filePath)
    {
        $this->parseFixtureFileHelper($filePath, $this->initialFixtureCollection);
    }

    /**
     * Read in the yaml file and create the gosTest_Fixture objects for each fixture in the file
     * @param string $filePath The full path to the fixture yaml file to load
     * @param gosTest_Fixture_FixtureCollection The fixture collection to parse
     *          this file into
     * @throws gosException_InvalidArgument If the file doesn't exist or file parsing fails
     */
    protected function parseFixtureFileHelper($filePath, gosTest_Fixture_FixtureCollection $fixtureCollection)
    {
        /*
         * If it's not in our cache, we should load it in.
         * Note: This makes unit tests on dev1 about 5% faster, and on dev2
         *   maybe a bit faster (but it's hard to tell because it's less consistent).
         *   I don't know if it's really worth it, but hopefully it will help as
         *   we write more unit tests (and possibly our new dev box) -AK 2008/10/29
         */
        if (!isset(self::$fixtureEntriesCache[$filePath]))
        {
            // File doesn't exist
            if (!file_exists($filePath))
            {
                throw new gosException_InvalidArgument('File does not exist: '. $filePath, get_defined_vars());
            }

            // turn on buffering, then eval the file so that you can include PHP in the fixture file
            ob_start();
            include($filePath);
            $contents = ob_get_clean();

            // Parse the file
            self::$fixtureEntriesCache[$filePath] = syck_load($contents);
            if (!is_array(self::$fixtureEntriesCache[$filePath]))
            {
                throw new gosException_InvalidArgument('File did not contain any fixtures or is not formatted properly: '. $filePath, get_defined_vars());
            }
        }

        // Load each fixture into the DB
        foreach (self::$fixtureEntriesCache[$filePath] as $entryIdentifier => $identifierTables)
        {
            $this->insertFixture($entryIdentifier, $identifierTables, $fixtureCollection);
        }
    }

    /**
     * Retrieve an individual value from a fixture table row
     *
     * @param string $elementReference
     * @return string Value retrieved by following the reference passed in
     */
    public function get($elementReference)
    {
        // Convert the element reference (fixture.table.column or fixture.table.row.colum)
        // ...into an array containing each dotted element
        $valueParts = explode('.', $elementReference);
        // Got to have 3 or 4 parts
        $numParts = count($valueParts);
        // One row in the table
        if ($numParts == 3)
        {
            // Note the if, else if, else here allows us to get the correct exceptions thrown if
            // there are any typos in our fixtures or in our unit tests.
            if ($this->fixtureExists($valueParts[0]))
            {
                return $this->getFixture($valueParts[0])->getTable($valueParts[1])->getTableRow()->getColumnValue($valueParts[2]);
            }
            else if ($this->initialFixtureExists($valueParts[0]))
            {
                return $this->getInitialFixture($valueParts[0])->getTable($valueParts[1])->getTableRow()->getColumnValue($valueParts[2]);
            }
            else
            {
                throw new gosException_InvalidArgument('Invalid fixture name: "'. $valueParts[0] .'"', get_defined_vars());
            }
        }
        // Multiple rows in the table
        elseif ($numParts == 4)
        {
            if ($this->fixtureExists($valueParts[0]))
            {
                return $this->getFixture($valueParts[0])->getTable($valueParts[1])->getTableRow($valueParts[2])->getColumnValue($valueParts[3]);
            }
            else if ($this->initialFixtureExists($valueParts[0]))
            {
                return $this->getInitialFixture($valueParts[0])->getTable($valueParts[1])->getTableRow($valueParts[2])->getColumnValue($valueParts[3]);
            }
            else
            {
                throw new gosException_InvalidArgument('Invalid fixture name: "'. $valueParts[0] .'"', get_defined_vars());
            }
        }
        // Bad identifier
        else
        {
            throw new gosException_InvalidArgument('Row value identifier must be [^.]+\.[^.]+\.[^.]+: "'. $elementReference .'"', get_defined_vars());
        }
    }

    /**
     * Retrieve an entire row as an array from a fixture table row
     *
     * @param string $elementReference
     * @return array Column => Value associative array retrieved by following the reference passed in
     */
    public function getArray($elementReference)
    {
        // Convert the element reference (fixture.table.column or fixture.table.row.colum)
        // ...into an array containing each dotted element
        $valueParts = explode('.', $elementReference);
        // Got to have 3 or 4 parts
        $numParts = count($valueParts);
        // One row in the table
        if ($numParts == 2)
        {
            if ($this->fixtureExists($valueParts[0]))
            {
                return $this->getFixture($valueParts[0])->getTable($valueParts[1])->getTableRow()->getArrayMap();
            }
            else if ($this->initialFixtureExists($valueParts[0]))
            {
                return $this->getInitialFixture($valueParts[0])->getTable($valueParts[1])->getTableRow()->getArrayMap();
            }
            else
            {
                throw new gosException_InvalidArgument('Invalid fixture name: '. $valueParts[0], get_defined_vars());
            }
        }
        // Multiple rows in the table
        elseif ($numParts == 3)
        {
            if ($this->fixtureExists($valueParts[0]))
            {
                return $this->getFixture($valueParts[0])->getTable($valueParts[1])->getTableRow($valueParts[2])->getArrayMap();
            }
            else if ($this->initialFixtureExists($valueParts[0]))
            {
                return $this->getInitialFixture($valueParts[0])->getTable($valueParts[1])->getTableRow($valueParts[2])->getArrayMap();
            }
            else
            {
                throw new gosException_InvalidArgument('Invalid fixture name: '. $valueParts[0], get_defined_vars());
            }
        }
        // Bad identifier
        else
        {
            throw new gosException_InvalidArgument('Row array identifier must be [^.]+\.[^.]+\.[^.]+: '. $elementReference, get_defined_vars());
        }
    }

    /**
     * delete all rows from all database tables inserted into by all fixtures
     */
    public function deleteAll()
    {
        $this->fixtureCollection->reset($this->db);
    }

    /**
     * Get the fixture for the specified name
     * @param string $name: fixture name
     * @return fixture
     * @throws gosException_InvalidArgument if the fixture name doesn't exist
     */
    public function getFixture($name)
    {
        return $this->fixtureCollection->getFixture($name);
    }

    /**
     * Get the fixture for the specified name
     * @param string $name: fixture name
     * @return fixture
     * @throws gosException_InvalidArgument if the fixture name doesn't exist
     */
    public function getInitialFixture($name)
    {
        return $this->initialFixtureCollection->getFixture($name);
    }

    /**
     * Simple wrapper function to see if a fixture exists
     * @param string $name
     */
    protected function fixtureExists($name)
    {
        return $this->fixtureCollection->isFixtureLoaded($name);
    }

    /**
     * Simple wrapper function to see if an initial fixture exists
     * @param string $name
     */
    protected function initialFixtureExists($name)
    {
        return $this->initialFixtureCollection->isFixtureLoaded($name);
    }

    /**
     * Creates and caches new gosTest_Fixture object then inserts db rows into tables included in the fixture
     *
     * @param string $fixtureIdentifier The indentifier used to index and retrieve the fixture
     * @param array $identifierTables Array of all tables that have entries as part of this fixture
     * @param gosTest_Fixture_FixtureCollection $fixtureCollection The fixture collection we're inserting into
     * @throws gosException_StateError If an identifier that already exists is passed in
     */
    protected function insertFixture($fixtureIdentifier, array $identifierTables, gosTest_Fixture_FixtureCollection $fixtureCollection)
    {
        gosTest_Fixture_Parser::assertAcceptableIdentifier('Fixture', $fixtureIdentifier);

        // Don't overwrite fixtures that have the same identifier
        if ($fixtureCollection->isFixtureLoaded($fixtureIdentifier))
        {
            throw new gosException_StateError('Fixture identifier already exists: '. $fixtureIdentifier, get_defined_vars());
        }

        // Create the fixture and add it the the controller cache
        $fixture = new gosTest_Fixture();
        $fixtureCollection->setFixture($fixtureIdentifier, $fixture);

        // Loop over tables to insert into for this fixture
        foreach ($identifierTables as $tableName => $tableData)
        {
            gosTest_Fixture_Parser::assertAcceptableIdentifier('Table', $tableName);

            if (!is_array($tableData))
            {
                throw new gosException_InvalidArgument('No table data defined for table "'. $tableName. '" in fixture "'. $fixtureIdentifier .'".', get_defined_vars());
            }

            // Table already exists in the fixture
            if ($fixture->hasTable($tableName))
            {
                throw new gosException_StateError('Table already exists in the fixture: '. $tableName, get_defined_vars());
            }

            // If this is the first time we're loading this table as part of this run,
            // ...nuke anything that is in the table so that we don't run into problems
            $fixtureCollection->prepareTable($tableName, $this->db);

            // Keep track of the tables we are inserting into so that we can clean up after ourselves
            $fixtureCollection->setTableAffected($tableName);
            $tableObj = new gosTest_Fixture_Table($tableName);

            // If the first element of tableData is an array, then tableData is a 2D array instead
            // ...of a 1D array which means that there are multiple rows to insert into the db table
            if (is_array(current($tableData)))
            {
                // Add each row into the db table
                foreach ($tableData as $rowIdentifier => $tableRow)
                {
                    gosTest_Fixture_Parser::assertAcceptableIdentifier('Row', $rowIdentifier);
                    $tableRowObj = new gosTest_Fixture_TableRow($this->db, $tableName, $rowIdentifier, gosTest_Fixture_Parser::parse($this, $fixtureIdentifier, $tableRow));
                    $tableObj->addKeyedTableRow($tableRowObj, $rowIdentifier);
                }
            }
            // Single row to insert
            else
            {
                $tableRowObj = new gosTest_Fixture_TableRow($this->db, $tableName, null, gosTest_Fixture_Parser::parse($this, $fixtureIdentifier, $tableData));
                $tableObj->addTableRow($tableRowObj);
            }
            $fixture->addTable($tableObj);
        }
    }

}
