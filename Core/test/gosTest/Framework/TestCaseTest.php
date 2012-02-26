<?php
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/Core/testConfig.inc.php';
require_once dirname(__FILE__) .'/_helpers/TestCaseStub.php';

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
class gosTest_Framework_TestCaseTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function teardownExtension()
    {
    }

    public function testAssertBetweenInclusive_FailsWhenTooLarge()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        // 'or is less than' is part of the less than or equal to assertion
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', 'or is less than');
        $testCase->assertBetweenInclusive(3, 5, 6);
    }

    public function testAssertBetweenInclusive_FailsWhenTooSmall()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        // 'or is less than' is part of the greater than or equal to assertion
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', 'or is greater than');
        $testCase->assertBetweenInclusive(3, 5, 2);
    }

    public function testAssertBetweenInclusive_SucceedsWhenJustRight()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertBetweenInclusive(3, 5, 4);
        // silently succeeds
    }

    public function testAssertBetweenInclusive_IsInclusive()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertBetweenInclusive(3, 5, 3);
        $testCase->assertBetweenInclusive(3, 5, 5);
        // both silently succeed
    }

    public function testAssertTimeFuzzy_MySQLFormat_IdenticalTimes_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $time = '2009-07-01 10:11:12';
        $testCase->assertTimeFuzzy($time, $time);
        // silently succeeds
    }

    public function testAssertTimeFuzzy_SFFormat_IdenticalTimes_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $time = '2009-07-01Z10:11:12';
        $testCase->assertTimeFuzzy($time, $time);
        // silently succeeds
    }

    public function testAssertTimeFuzzy_MySQLFormat_TwoSecondsLater_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01 10:11:11', '2009-07-01 10:11:13');
        // silently succeeds
    }

    public function testAssertTimeFuzzy_SFFormat_TwoSecondsLater_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01Z10:11:11', '2009-07-01Z10:11:13');
        // silently succeeds
    }

    public function testAssertTimeFuzzy_MySQLFormat_TwoSecondsEarlier_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01 10:11:12', '2009-07-01 10:11:11');
        // silently succeeds
    }

    public function testAssertTimeFuzzy_SFFormat_TwoSecondsEarlier_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01Z10:11:12', '2009-07-01Z10:11:11');
        // silently succeeds
    }

    public function testAssertTimeFuzzy_MySQLFormat_ThreeSecondsEarlier_Fails()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', 'Expected time');
        $testCase->assertTimeFuzzy('2009-07-01 10:11:13', '2009-07-01 10:11:10');
        // silently succeeds
    }

    public function testAssertTimeFuzzy_SFFormat_ThreeSecondsEarlier_Fails()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', 'Expected time');
        $testCase->assertTimeFuzzy('2009-07-01Z10:11:13', '2009-07-01Z10:11:10');
        // silently succeeds
    }

    public function testAssertTimeFuzzy_MySQLFormat_ThreeSecondsEarlierFuzzinessSpecified_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01 10:11:13', '2009-07-01 10:11:10', 3);
        // silently succeeds
    }

    public function testAssertTimeFuzzy_SFFormat_ThreeSecondsEarlierFuzzinessSpecified_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01Z10:11:13', '2009-07-01Z10:11:10', 3);
        // silently succeeds
    }

    public function testAssertTimeFuzzy_MySQLFormat_CrossingMinutesBarrier_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01 10:11:58', '2009-07-01 10:12:05', 10);
        // silently succeeds
    }

    public function testAssertTimeFuzzy_SFFormat_CrossingMinutesBarrier_Succeeds()
    {
        $testCase = new gosTest_Framework_TestCaseStub();
        $testCase->assertTimeFuzzy('2009-07-01Z10:11:58', '2009-07-01Z10:12:05', 10);
        // silently succeeds
    }
}
