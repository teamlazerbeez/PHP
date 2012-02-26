<?php
// No fixtures in this test, but we need a DB defined
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/Fixture/fixtureTestConfig.inc.php';

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
 * @author Alex Korn <alex.e.korn@gmail.com>
 */
class lib_gosSafe_Gen_DBTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function tearDownExtension()
    {
    } 

    public function testGetGenerator_ReturnsDBGeneratorObj()
    {
        $result = gosSafe_Gen_DB::getGenerator("select * from table where id = {i:id}");
        $this->assertTrue($result instanceof gosSafe_Gen_DB);
    }

    public function testGetSafe_ReturnsDBSafeObj()
    {
        // the rest of the functionality of this is tested in the base
        $result = gosSafe_Gen_DB::getSafe("select * from table where id = {i:id}", 
            array('id' => 3));
        $this->assertTrue($result instanceof gosSafe_DB);
    }

    public function testGetSafeFromIntList_ReturnsDBSafeObjWithCommaSeparatedList()
    {
        $intList = array(1, 2, 3, 4);
        $result = gosSafe_Gen_DB::getSafeFromIntList($intList);
        $this->assertTrue($result instanceof gosSafe_DB);
        $this->assertSame("1,2,3,4", (string)$result);
    }

    public function testGetSafeFromIntList_ThrowsExcWhenBadValuePassed()
    {
        $intList = array(1, 2, "Robert');DROP TABLE Students;--", 4);

        // actual message is tested elsewhere
        $this->setExpectedException('gosException_InvalidArgument');
        gosSafe_Gen_DB::getSafeFromIntList($intList);
    }

    public function testSafeImplode_returnsSuccessfullyImplodedString()
    {
        $expected = gosSafe_Gen_DB::getSafe("id = {i:id}) AND (anotherID = {i:anotherID}",
            array(
                'id' => 3,
                'anotherID' => 12
                ));
        $safeArray[] = gosSafe_Gen_DB::getSafe("id = {i:id}",
            array('id' => 3));
        $safeArray[] = gosSafe_Gen_DB::getSafe("anotherID = {i:anotherID}",
            array('anotherID' => 12));

        $result = gosSafe_Gen_DB::safeImplode(') AND (', $safeArray);
        $this->assertEquals($expected, $result);
    }

    public function testSafeImplode_throwsExceptionWithNonSafeObjectInArray()
    {
        $safeArray[] = gosSafe_Gen_DB::getSafe("id = {i:id}",
            array('id' => 3));
        $safeArray[] = "anotherID = {i:anotherID}";

        $this->setExpectedException('gosException_InvalidArgument');
        $result = gosSafe_Gen_DB::safeImplode(') AND (', $safeArray);
    }

    public function testSafeReplaceAndImplode_returnSuccessful()
    {
        // Test against ints and general db strings
        $gen = gosSafe_Gen_DB::getGenerator("({i:id}, '{db:str}')");
        $replacementsSet = array();
        // Set of strings expected to be generated for each of the replacements
        $expectedStrings = array();

        for($n = 0; $n < 10; $n++)
        {
            $replacementsSet[] = array(
                'id' => $n,
                'str' => 'I ate ' . $n . ' peanuts'
            );

            // The safe value expected for this array entry
            $expectedStrings[] = '(' . $n . ', \'I ate ' . $n . ' peanuts\')';
        }

        $safe = $gen->safeReplaceAndImplode(',', $replacementsSet);

        $this->assertEquals(implode(',', $expectedStrings), (string)$safe);
    }
}
