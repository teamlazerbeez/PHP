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

class test_unit_helpers_gosTest_Fixture_FixtureCollectionTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function teardownExtension()
    {
    }

    public function testSetAndGetFixture()
    {
        $fixture = new gosTest_Fixture();
        $coll = new gosTest_Fixture_FixtureCollection();
        $coll->setFixture('test', $fixture);

        $result = $coll->getFixture('test');
        $this->assertSame($fixture, $result);

        $this->setExpectedException('gosException_InvalidArgument');
        $result2 = $coll->getFixture('invalid');
    }

    public function testIsFixtureLoaded()
    {
        $coll = new gosTest_Fixture_FixtureCollection();
        $this->assertFalse($coll->isFixtureLoaded('test'));

        $coll->setFixture('test', new gosTest_Fixture());
        $this->assertTrue($coll->isFixtureLoaded('test'));
    }

    public function testTableAffected()
    {
        $coll = new gosTest_Fixture_FixtureCollection();
        $this->assertFalse($coll->isTableAffected('test'));
        $coll->setTableAffected('test');
        $this->assertTrue($coll->isTableAffected('test'));
    }

    public function testReset()
    {
        $db = gosDB_Helper::getDBByName('main');
        $coll = new gosTest_Fixture_FixtureCollection();
        $coll->setFixture('test', new gosTest_Fixture());
        $db->execute("INSERT INTO fixture_test SET i1=7");
        $coll->setTableAffected('fixture_test');
        $coll->reset($db);
        $this->assertFalse($coll->isFixtureLoaded('test'));
        $this->assertFalse($coll->isTableAffected('fixture_test'));
        $this->assertEquals(0, $db->getOne("SELECT COUNT(*) FROM fixture_test"));
    }

    public function testPrepareTable()
    {
        $db = gosDB_Helper::getDBByName('main');
        $coll = new gosTest_Fixture_FixtureCollection();
        $db->execute("INSERT INTO fixture_test SET i1=7");

        $coll->prepareTable('fixture_test', $db);
        $this->assertEquals(0, $db->getOne("SELECT COUNT(*) FROM fixture_test"));
    }

}
