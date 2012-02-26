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

class test_unit_helpers_gosTest_Fixture_TableTest extends gosTest_Framework_TestCase
{
    protected $db;

    public function setUpExtension()
    {
        $this->db = gosDB_Helper::getDBByName('main');
    }

    public function tearDownExtension()
    {
        $this->db->execute('DELETE FROM fixture_test');
    }

    public function testCreateTable()
    {
        $fixtureTable = new gosTest_Fixture_Table('fixture_test');
    }

    public function testAddTableRow()
    {
        $columnMap = array();
        $columnMap['i1'] = 7;
        $columnMap['s1'] = 'barValue';
        $fixtureTableRow = new gosTest_Fixture_TableRow($this->db, 'fixture_test', null, $columnMap);
        $fixtureTable = new gosTest_Fixture_Table('fixture_test');
        $fixtureTable->addTableRow($fixtureTableRow);
        $resultName = $this->db->getOne("SELECT s1 FROM fixture_test WHERE i1=7");
        $this->assertEquals($columnMap['s1'], $resultName);
    }

    public function testAddKeyedTableRow()
    {
        $columnMap = array();
        $columnMap['i1'] = 2;
        $columnMap['s1'] = 'barValue';
        $fixtureTableRow = new gosTest_Fixture_TableRow($this->db, 'fixture_test', null, $columnMap);
        $fixtureTable = new gosTest_Fixture_Table('fixture_test');
        $fixtureTable->addKeyedTableRow($fixtureTableRow, 'foo bar');

        $resultTableRow = $fixtureTable->getTableRow('foo bar');
        $this->assertTrue($resultTableRow instanceof gosTest_Fixture_TableRow);
        $this->assertEquals($columnMap['s1'], $resultTableRow->getColumnValue('s1'));
    }

    public function testAddKeyedTableRow_WithDuplicateKeyThrowsException()
    {
        $columnMap = array();
        $columnMap['i1'] = 2;
        $columnMap['s1'] = 'barValue';
        $fixtureTableRow = new gosTest_Fixture_TableRow($this->db, 'fixture_test', null, $columnMap);
        $fixtureTable = new gosTest_Fixture_Table('fixture_test');
        $fixtureTable->addKeyedTableRow($fixtureTableRow, 'foo bar');

        $this->setExpectedException('gosException_InvalidArgument');
        $fixtureTable->addKeyedTableRow($fixtureTableRow, 'foo bar');
    }
}
