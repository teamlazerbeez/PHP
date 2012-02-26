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

class lib_gosDB_MysqliTest extends gosTest_Framework_TestCase
{
    /**
     * @var gosDB_Mysqli $gosDatabase
     */
    protected $gosDatabase;

    public function setUpExtension()
    {
        $this->gosDatabase = gosDB_Helper::getDBByName('main');
    }

    public function tearDownExtension()
    {
        $this->emptyTable('main', 'ut_active_records');
        $this->emptyTable('main', 'ut_active_records_2');
        $this->emptyTable('main', 'ut_active_records_4');
    }

    public function testExecute_WithUpdate()
    {
        $expected = time();
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='". ($expected - 1) ."'");
        $newRowID = $this->gosDatabase->insertID();
        $this->gosDatabase->execute("UPDATE ut_active_records SET string_default_ls='". $expected ."'");
        $result = $this->gosDatabase->getRow("select string_default_ls from ut_active_records where id=". $newRowID);
        $this->assertEquals($expected, $result['string_default_ls']);
    }

    public function testGetAll_ReturnsEmptyArrayOnNoResults()
    {
        $expected = array();
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $result = $this->gosDatabase->getAll('SELECT * FROM ut_active_records');
        $this->assertEquals($expected, $result);
    }

    public function testGetAll_Returns2dArrayOnOneMatch()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $result = $this->gosDatabase->getAll('SELECT * FROM ut_active_records');
        $this->assertTrue(is_array($result[0]));

        $this->assertEquals(1, count($result));
    }

    public function testGetAll_Returns2dArrayOnMultipleMatches()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $result = $this->gosDatabase->getAll('SELECT * FROM ut_active_records');
        $this->assertTrue(is_array($result[0]));
        $this->assertTrue(is_array($result[1]));

        $this->assertEquals(2, count($result));
    }

    public function testGetRow_ReturnsEmptyArrayOnNoResults()
    {
        $expected = array();
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $result = $this->gosDatabase->getRow('SELECT * FROM ut_active_records');
        $this->assertEquals($expected, $result);
    }

    public function testGetRow_Returns1dArrayOnSingleMatch()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $result = $this->gosDatabase->getRow('SELECT * FROM ut_active_records');
        $this->assertTrue(is_array($result));
        $this->assertTrue(!is_array(array_pop($result)));
    }

    public function testGetRow_Returns1dArrayOnMultipleMatches()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $result = $this->gosDatabase->getRow('SELECT * FROM ut_active_records');
        $this->assertTrue(is_array($result));
        $this->assertTrue(!is_array(array_pop($result)));
    }

    public function testGetOne_ReturnsFalseNoResults()
    {
        $expected = false;
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $result = $this->gosDatabase->getOne('SELECT * FROM ut_active_records');
        $this->assertEquals($expected, $result);
    }

    public function testGetOne_ReturnsNonArrayOnSingleMatch()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $result = $this->gosDatabase->getOne('SELECT id FROM ut_active_records');
        $this->assertTrue(!is_array($result));
        $this->assertTrue(is_numeric($result));
    }

    public function testGetOne_ThrowsExceptionOnMultipleMatches()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');

        $result = false;
        $query = 'SELECT id FROM ut_active_records';

        $this->setExpectedException('gosException_InvalidArgument', 'query returned more than one row (2): '. $query);
        $this->gosDatabase->getOne($query);
    }

    public function testGetOne_ThrowsExceptionWhenResultHasMultipleColumns()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute('INSERT INTO ut_active_records SET int_allow_null=1');

        $result = false;
        $query = 'SELECT * FROM ut_active_records';

        $this->setExpectedException('gosException_InvalidArgument', 'query requested more than one column: '. $query);
        $this->gosDatabase->getOne($query);
    }

    public function testGetColumnAsList_ReturnsList()
    {
        $this->gosDatabase->execute("INSERT INTO ut_active_records_4 SET customerID = 2");
        $this->gosDatabase->execute("INSERT INTO ut_active_records_4 SET customerID = 3");
        $this->gosDatabase->execute("INSERT INTO ut_active_records_4 SET customerID = 4");
        $result = $this->gosDatabase->getColumnAsList("SELECT customerID FROM ut_active_records_4", 'customerID');
        $this->assertEquals(array(2, 3, 4), $result);
    }

    public function testGetColumnAsList_ThrowsExcWhenColumnDoesNotExistInResult()
    {
        $this->gosDatabase->execute("INSERT INTO ut_active_records_4 SET customerID = 2");

        $this->setExpectedException('gosException_InvalidArgument', 'Column name "id" does not exist in the result.');
        $this->gosDatabase->getColumnAsList("SELECT customerID FROM ut_active_records_4", 'id');
    }

    public function testRollbackTransaction()
    {
        $expected = time();
        $this->gosDatabase->execute('DELETE FROM ut_active_records_4');
        $this->gosDatabase->execute("insert into ut_active_records_4 set customerID='". ($expected - 1) ."'");
        $newRowID = $this->gosDatabase->insertID();

        $this->gosDatabase->startTransaction();
        $this->gosDatabase->execute("UPDATE ut_active_records_4 SET customerID='". $expected ."' where id=". $newRowID);
        $this->gosDatabase->rollbackTransaction();

        $result = $this->gosDatabase->getOne("select customerID from ut_active_records_4 where id=". $newRowID);
        $this->assertNotEquals($expected, $result);
    }

    public function testCommitTransaction()
    {
        $expected = time();
        $this->gosDatabase->execute('DELETE FROM ut_active_records_4');
        $this->gosDatabase->execute("insert into ut_active_records_4 set customerID='". ($expected - 1) ."'");
        $newRowID = $this->gosDatabase->insertID();

        $this->gosDatabase->startTransaction();
        $this->gosDatabase->execute("UPDATE ut_active_records_4 SET customerID='". $expected ."' where id=". $newRowID);
        $this->gosDatabase->commitTransaction();

        $result = $this->gosDatabase->getOne("select customerID from ut_active_records_4 where id=". $newRowID);
        $this->assertEquals($expected, $result);
    }

    public function testInsertIdOnAutoincrementReturnsId()
    {
        $expected1 = 'foo';
        $expected2 = 'bar';
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='". $expected1 ."'");
        $newRowID = $this->gosDatabase->insertID();
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='". $expected2 ."'");
        $newRow2ID = $this->gosDatabase->insertID();

        $this->assertEquals($newRowID + 1, $newRow2ID);

        $result1 = $this->gosDatabase->getOne("SELECT string_default_ls FROM ut_active_records WHERE id=". $newRowID);
        $this->assertEquals($expected1, $result1);

        $result2 = $this->gosDatabase->getOne("SELECT string_default_ls FROM ut_active_records WHERE id=". $newRow2ID);
        $this->assertEquals($expected2, $result2);
    }

    public function testInsertID_OnNonAutoincrementTableThrowsException()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records_2');
        $this->gosDatabase->execute("insert into ut_active_records_2 set value='foo'");

        $this->setExpectedException('gosException_StateError');
        $newRowID = $this->gosDatabase->insertID();
    }

    public function testExecute_SqlErrorThrowsException()
    {
        $this->setExpectedException('gosException_DB');
        $this->gosDatabase->execute('DELETE FROM foobartable');
    }

    public function testMatchedRows_ReturnsNumberRowsMatchedIfNoRowsChanged()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("update ut_active_records set string_default_ls='foo' where string_default_ls='foo'");
        $expected = 3;
        $result = $this->gosDatabase->matchedRows();
        $this->assertEquals($expected, $result);

        $expected = 0;
        $this->gosDatabase->execute("update ut_active_records set string_default_ls='foo' where string_default_ls='bar'");
        $result = $this->gosDatabase->matchedRows();
        $this->assertEquals($expected, $result);
    }

    public function testMatchedRows_ReturnsNumberRowsMatchedIfRowsChanged()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("update ut_active_records set string_default_ls='bar' where string_default_ls='foo'");
        $expected = 3;
        $result = $this->gosDatabase->matchedRows();
        $this->assertEquals($expected, $result);
    }

    public function testMatchedRows_ReturnsInt()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");

        $this->gosDatabase->execute("update ut_active_records set string_default_ls='bar' where string_default_ls='foo'");
        $result = $this->gosDatabase->matchedRows();
        $this->assertTrue(is_int($result));

        $this->gosDatabase->execute("update ut_active_records set string_default_ls='foo' where string_default_ls='bar'");
        $result = $this->gosDatabase->matchedRows();
        $this->assertTrue(is_int($result));
    }

    public function testAffectedRows_ReturnsNumberRowsAffected()
    {
        $this->gosDatabase->execute('DELETE FROM ut_active_records');
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("insert into ut_active_records set string_default_ls='foo'");
        $this->gosDatabase->execute("update ut_active_records set string_default_ls='foo' where string_default_ls='foo'");
        $expected = 0;
        $result = $this->gosDatabase->affectedRows();
        $this->assertEquals($expected, $result);

        $expected = 3;
        $this->gosDatabase->execute("update ut_active_records set string_default_ls='bar' where string_default_ls='foo'");
        $result = $this->gosDatabase->matchedRows();
        $this->assertEquals($expected, $result);
    }
}
