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
require_once dirname(dirname(dirname(dirname(__FILE__)))) .'/Fixture/fixtureTestConfig.inc.php';

class test_unit_helpers_gosTest_FixtureTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function teardownExtension()
    {
    }

    public function testAddTableAddsToClass()
    {
        $tableName = 'foo';
        $fixture = new gosTest_Fixture();
        $table = new gosTest_Fixture_Table($tableName);
        $fixture->addTable($table);

        $this->assertTrue($fixture->hasTable($tableName));
    }

    public function testGetTableRetrievesTableClass()
    {
        $tableName = 'foo';
        $fixture = new gosTest_Fixture();
        $table = new gosTest_Fixture_Table($tableName);
        $fixture->addTable($table);

        $resultFixture = $fixture->getTable($tableName);
        $this->assertTrue($resultFixture instanceof gosTest_Fixture_Table);
        $this->assertSame($tableName, $resultFixture->getTableName());
    }

    public function testHasTableReturnsTrueWithInsertedTable()
    {
        $tableName = 'foo';
        $fixture = new gosTest_Fixture();
        $table = new gosTest_Fixture_Table($tableName);
        $fixture->addTable($table);

        $this->assertTrue($fixture->hasTable($tableName));
    }

    public function testHasTableReturnsFalseWithUninsertedTable()
    {
        $tableName = 'foo';
        $fixture = new gosTest_Fixture();
        $table = new gosTest_Fixture_Table($tableName);
        $fixture->addTable($table);

        $this->assertFalse($fixture->hasTable('bar'));
    }

    public function testBadTableNameThrowException()
    {
        $fixture = new gosTest_Fixture();
        $this->setExpectedException('gosException_InvalidArgument');
        $fixture->getTable('no tables exist');
    }

    public function testAddDuplicateTableThrowsException()
    {
        $fixture = new gosTest_Fixture();
        $table = new gosTest_Fixture_Table('foo');
        $fixture->addTable($table);

        $this->setExpectedException('gosException_StateError');
        $fixture->addTable($table);
    }
}
?>
