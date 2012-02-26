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
class gosTest_Fixture_FixtureCollection
{
    /**
     * @var array $loadedFixtures The fixtures that have been loaded in
     */
    protected $loadedFixtures = array();

    /**
     * @var array $affectedTables The tables we have touched so far this run
     */
    protected $affectedTables = array();

    /**
     * @param string $fixtureIdentifier
     * @param gosTest_Fixture $fixture
     * @return none
     */
    public function setFixture($fixtureIdentifier, gosTest_Fixture $fixture)
    {
        $this->loadedFixtures[$fixtureIdentifier] = $fixture;
    }

    /**
     * @param string $fixtureIdentifier
     * @return gosTest_Fixture
     */
    public function getFixture($fixtureIdentifier)
    {
        if (!$this->isFixtureLoaded($fixtureIdentifier))
        {
            throw new gosException_InvalidArgument('Invalid fixture name: "'. $fixtureIdentifier .'".', get_defined_vars());
        }
        return $this->loadedFixtures[$fixtureIdentifier];
    }

    /**
     * @param string $fixtureIdentifier
     * @return bool
     */
    public function isFixtureLoaded($fixtureIdentifier)
    {
        return isset($this->loadedFixtures[$fixtureIdentifier]);
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function isTableAffected($tableName)
    {
        return isset($this->affectedTables[$tableName]);
    }

    /**
     * @param string $tableName
     * @return none
     */
    public function setTableAffected($tableName)
    {
        $this->affectedTables[$tableName] = $tableName;
    }

    /**
     * Deletes everything from affected tables and internal storage of loaded
     * fixtures and affected tables
     * @param gosDB_Base $db
     * @return none
     */
    public function reset(gosDB_Base $db)
    {
        foreach ($this->affectedTables as $tableName)
        {
            $stmt = "TRUNCATE TABLE " . $tableName;
            $db->execute($stmt);
        }
        $this->loadedFixtures = array();
        $this->affectedTables = array();
    }

    /**
     * @param string $tableName
     * @param gosDB_Base $db
     * @return none
     */
    public function prepareTable($tableName, gosDB_Base $db)
    {
        // If this is the first time we're loading this table as part of this run,
        // ...nuke anything that is in the table so that we don't run into problems
        if (!$this->isTableAffected($tableName))
        {
            $stmt = "TRUNCATE TABLE " . $tableName;
            $db->execute($stmt);
        }
    }

}

?>
