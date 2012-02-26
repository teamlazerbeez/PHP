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
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/Fixture/fixtureTestConfig.inc.php';

class test_unit_helpers_gosTest_Fixture_WrapperTest extends gosTest_Framework_TestCase
{
    protected $db;

    public function setUpExtension()
    {
        $this->db = gosDB_Helper::getDBByName('main');
    }

    public function teardownExtension()
    {
        //$this->emptyTable('main', 'team');
        //$this->emptyTable('main', 'teamEmailBudget');
    }

    public function testParseFixtureFile_BadFileNameThrowException()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

         // don't include file path in exception, because it changes
        $this->setExpectedException('gosException_InvalidArgument',
            'File does not exist');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/BADFILE.yaml');
    }

    public function testParseFixtureFile_NonYamlFileThrowException()
    {
        // With syck v0.55 (what MacPorts has), syck_load() segfaults with bad YAML
        $this->markTestSkipped();

        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        // don't include file path in exception, because it changes
        $this->setExpectedException('gosException_InvalidArgument',
            'File did not contain any fixtures or is not formatted properly');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/badFormat.yaml');
    }

    public function testParseFixtureFile_BadFixtureNameThrowsExc()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $this->setExpectedException('gosException_InvalidArgument',
            'Fixture identifier "dummy/Team" contains the DB separator character "/".');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/wrapperBadFixtureName.yaml');
    }

    public function testParseFixtureFile_BadTableNameThrowsExc()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $this->setExpectedException('gosException_InvalidArgument',
            'Table identifier "bad/table" contains the DB separator character "/".');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/wrapperBadTableName.yaml');
    }

    public function testParseFixtureFile_BadRowIdentifierThrowsExc()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $this->setExpectedException('gosException_InvalidArgument',
            'Row identifier "row/Identifier" contains the DB separator character "/".');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/wrapperBadRowIdentifier.yaml');
    }

    /*
     * This function probably should be in TableRowTest...
     */
    public function testParseFixtureFile_BadColNameThrowsExc()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $this->setExpectedException('gosException_InvalidArgument',
            'Column identifier "col/Name" contains the DB separator character "/".');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/wrapperBadColName.yaml');
    }

    public function testValidFileLoadsDb()
    {
        $this->assertSame($this->rowCount('fixture_test'), '0');
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/controllerTest.yaml');
        $this->assertSame($this->rowCount('fixture_test'), '1');
        $fixtureController->deleteAll();
        $this->assertSame($this->rowCount('fixture_test'), '0');
    }

    public function testInsertFixtureInsertsIntoDb()
    {
        $contents = file_get_contents(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        // Parse the file
        $entries = syck_load($contents);

        $fixtureController = self::getProxy('gosTest_Fixture_Wrapper', array($this->db));
        $fixtureName = key($entries);
        $fixture = current($entries);
        $fixtureController->PROTECTED_insertFixture($fixtureName, $fixture, $fixtureController->getNonPublicVariable('fixtureCollection'));
        $this->assertEquals(1, $this->rowCount('fixture_test'));
        $this->assertEquals(1, $this->rowCount('ut_active_records'));
    }

    public function testInsertFixtureSetsFixtureToDbValues()
    {
        $contents = file_get_contents(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        // Parse the file
        $entries = syck_load($contents);

        $fixtureController = self::getProxy('gosTest_Fixture_Wrapper', array($this->db));
        $fixtureName = key($entries);
        $fixture = current($entries);
        $fixtureController->PROTECTED_insertFixture($fixtureName, $fixture, $fixtureController->getNonPublicVariable('fixtureCollection'));

        $this->assertEquals($fixtureController->get('simpleFixture.fixture_test.s1'), $this->db->getOne('SELECT s1 FROM fixture_test'));
    }

    public function testInsertFixtureHandlesAutoIncrement()
    {
        $contents = file_get_contents(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        // Parse the file
        $entries = syck_load($contents);

        $fixtureController = self::getProxy('gosTest_Fixture_Wrapper', array($this->db));
        $fixtureName = key($entries);
        $fixture = current($entries);
        $fixtureController->PROTECTED_insertFixture($fixtureName, $fixture, $fixtureController->getNonPublicVariable('fixtureCollection'));

        $this->assertEquals($fixtureController->get('simpleFixture.ut_active_records.id'), $this->db->getOne('SELECT id FROM ut_active_records'));
    }

    public function testInsertFixtureHandlesForeignKeyFromOtherFixture()
    {
        $contents = file_get_contents(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        // Parse the file
        $entries = syck_load($contents);

        $fixtureController = self::getProxy('gosTest_Fixture_Wrapper', array($this->db));
        $fixtureName = key($entries);
        $fixture = current($entries);
        $fixtureController->PROTECTED_insertFixture($fixtureName, $fixture, $fixtureController->getNonPublicVariable('fixtureCollection'));

        $this->assertEquals($fixtureController->get('simpleFixture.ut_active_records.string_not_allow_null'), $this->db->getOne('SELECT string_not_allow_null FROM ut_active_records'));
        $this->assertEquals($fixtureController->get('simpleFixture.fixture_test.s1'), $fixtureController->get('simpleFixture.ut_active_records.string_not_allow_null'));
        $this->assertEquals($this->db->getOne('SELECT s1 FROM fixture_test'), $this->db->getOne('SELECT string_not_allow_null FROM ut_active_records'));
    }

    public function testInsertDuplicateFixtureThrowsException()
    {
        $contents = file_get_contents(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        // Parse the file
        $entries = syck_load($contents);

        $fixtureController = self::getProxy('gosTest_Fixture_Wrapper', array($this->db));
        $fixtureName = key($entries);
        $fixture = current($entries);
        $fixtureController->PROTECTED_insertFixture($fixtureName, $fixture, $fixtureController->getNonPublicVariable('fixtureCollection'));

        $this->setExpectedException('gosException_StateError');
        // Try inserting the same fixture a second time
        $fixtureController->PROTECTED_insertFixture($fixtureName, $fixture, $fixtureController->getNonPublicVariable('fixtureCollection'));
    }

    public function testGetWithBadString_NoPeriodsThrowsException()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');

        $this->setExpectedException('gosException_InvalidArgument', 'Row value identifier must be [^.]+\.[^.]+\.[^.]+: "foobar"');
        $withGet = $fixtureController->get('foobar');
    }

    public function testGetWithBadString_OnlyOnePeriodThrowsException()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');

        $this->setExpectedException('gosException_InvalidArgument', 'Row value identifier must be [^.]+\.[^.]+\.[^.]+: "foo.bar"');
        $withGet = $fixtureController->get('foo.bar');
    }

    public function testGetWithBadString_TooManyPeriodsImpliesExcessiveDepth()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');

        $this->setExpectedException('gosException_InvalidArgument', 'The key passed <i1> does not map to a valid row in table <fixture_test>');
        $withGet = $fixtureController->get('simpleFixture.fixture_test.i1.foo');
    }

    public function testGetReturnsValue()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        $withGet = $fixtureController->get('simpleFixture.fixture_test.i1');
        $withGetFixture = $fixtureController->getFixture('simpleFixture')->getTable('fixture_test')->getTableRow()->getColumnValue('i1');
        $this->assertSame($withGet, $withGetFixture);
    }

    public function testGetArrayReturnsAllColumnsInsertedIntoRow()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        $withGetArray = $fixtureController->getArray('simpleFixture.fixture_test');
        $this->assertTrue(is_array($withGetArray));

        $withGetFixture = $fixtureController->getFixture('simpleFixture')->getTable('fixture_test')->getTableRow()->getArrayMap();
        $this->assertSame($withGetArray, $withGetFixture);

        $this->assertTrue(isset($withGetArray['i1']));

        $withGetArray = $fixtureController->getArray('simpleFixture.ut_active_records');
        $this->assertSame(count($withGetArray), 4);
        $this->assertTrue(isset($withGetArray['id']));
        $this->assertEquals($withGetArray['int_not_allow_null'], 7);
        $this->assertEquals($withGetArray['enum_not_null'], 'foo');
    }

    public function testDeleteAllDeletesFromAllTables()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        $this->assertEquals(1, $this->rowCount('fixture_test'));
        $this->assertEquals(1, $this->rowCount('ut_active_records'));
        $fixtureController->deleteAll();
        $this->assertEquals(0, $this->rowCount('fixture_test'));
        $this->assertEquals(0, $this->rowCount('ut_active_records'));
    }

    public function testGetFixtureWithBadFixtureNameThrowsException()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');

        $this->setExpectedException('gosException_InvalidArgument');
        $fixtureController->getFixture('This fixture does not exist');
    }

    public function testGetFixtureWithGoodFixtureReturnsObject()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');

        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/simpleFixture.yaml');
        $result = $fixtureController->getFixture('simpleFixture');
        $this->assertType('gosTest_Fixture', $result);
    }

    public function testInsertingIntoNewTableFirstWipesPreviousDataInTable()
    {
        // First load the row with a hardcoded primary key into the db
        $this->db->execute('INSERT INTO ut_active_records SET id=700');

        // Now try loading the yaml file again into the same DB.  This should not throw a db
        // ...exception for duplicate row since the data inserted above should be deleted first
        $initialController = gosTest_Fixture_Controller::getByDBName('main');
        $initialController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/wipeDataTest.yaml');
    }

    public function testGetArray()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/controllerTest.yaml');
        $expected = array('i1' => $fixtureController->get('f.fixture_test.i1'),
                          's1' => $fixtureController->get('f.fixture_test.s1'));
        $result = $fixtureController->getArray('f.fixture_test');
        $this->assertEquals($expected, $result);
    }

    private function rowCount($tableName)
    {
        return $this->db->getOne('SELECT COUNT(*) FROM '. $tableName);
    }
}
