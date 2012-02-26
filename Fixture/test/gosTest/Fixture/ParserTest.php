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

class test_unit_helpers_gosTest_Fixture_ParserTest extends gosTest_Framework_TestCase
{
    protected $db;

    public function setUpExtension()
    {
        $this->db = gosDB_Helper::getDBByName('main');
    }

    public function teardownExtension()
    {
    }

    public function testParseWithSelfReferentialFixtureUsingThisProperlyReturnsFixtureValue()
    {
        $fixtureController = gosTest_Fixture_Controller::getByDBName('main');
        $fixtureController->parseFixtureFile(GOS_ROOT . 'Fixture/test/gosTest/Fixture/_fixtures/parserTest.yaml');
        $parsedResult = gosTest_Fixture_Parser::parse($fixtureController, 'foobar', array('i1' => '<<this.fixture_test.i1>>'));

        $this->assertEquals($fixtureController->get('foobar.fixture_test.i1'), $parsedResult['i1']);
    }

    public function testIsSpecialValue()
    {
        $fixtureController = self::getProxy('gosTest_Fixture_Parser', array($this->db));
        $this->assertTrue($fixtureController->PROTECTED_isSpecialValue('<<auto>>'));
        $this->assertTrue($fixtureController->PROTECTED_isSpecialValue('<<foo.customer.customerID>>'));

        $this->assertFalse($fixtureController->PROTECTED_isSpecialValue('foo.customer.customerID'));
        $this->assertFalse($fixtureController->PROTECTED_isSpecialValue('<<foo.customer.customerID'));
        $this->assertFalse($fixtureController->PROTECTED_isSpecialValue('foo.customer.customerID>>'));
        $this->assertFalse($fixtureController->PROTECTED_isSpecialValue('>>foo.customer.customerID<<'));
    }

    public function testHasSpecialValue()
    {
        $fixtureController = self::getProxy('gosTest_Fixture_Parser', array($this->db));
        $this->assertTrue($fixtureController->PROTECTED_hasSpecialValue('<<auto>>'));
        $this->assertTrue($fixtureController->PROTECTED_hasSpecialValue('{activityID:<<this.activity.activityID>>}'));

        $this->assertFalse($fixtureController->PROTECTED_hasSpecialValue('{activityID:this.activity.activityID}'));
        $this->assertFalse($fixtureController->PROTECTED_hasSpecialValue('this.activity.activityID'));
    }

    public function testGetFixtureReference()
    {
        $fixtureController = self::getProxy('gosTest_Fixture_Parser', array($this->db));
        $this->assertSame('auto', $fixtureController->PROTECTED_getFixtureReference('<<auto>>'));
        $this->assertSame('this.activity.activityID', $fixtureController->PROTECTED_getFixtureReference('{activityID:<<this.activity.activityID>>}'));
    }

    public function testReplaceFixtureReference()
    {
        $fixtureController = self::getProxy('gosTest_Fixture_Parser', array($this->db));
        // note assertSame() here: we want the returned type to be the same
        $this->assertSame(5, $fixtureController->PROTECTED_replaceFixtureReference('<<auto>>', 5));
        $this->assertSame('{activityID:5}', $fixtureController->PROTECTED_replaceFixtureReference('{activityID:<<this.activity.activityID>>}', 5));
    }

}
