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
require_once dirname(dirname(dirname(dirname(__FILE__)))) .'/Core/testConfig.inc.php';

class gosUtility_StringTest extends gosTest_Framework_TestCase
{

    function setupExtension()
    {
    }

    function tearDownExtension()
    {
    }

    function testStripslashesDeep_String()
    {
        $string = "O\'Reilly";
        $actual = gosUtility_String::stripslashesDeep($string);

        $expected = "O'Reilly";
        $this->assertEquals($expected, $actual);
    }

    function testStripslashesDeep_Array()
    {
        $string = array("O\'Reilly", "O\'Really", "Foo Bar\'ly", "mul\'ti\'ple");
        $actual = gosUtility_String::stripslashesDeep($string);

        $expected = array("O'Reilly", "O'Really", "Foo Bar'ly", "mul'ti'ple");
        $this->assertEquals($expected, $actual);
    }

    function testStripslashesDeep_2DArray()
    {
        $string = array(array("O\'Reilly", "O\'Really"), array("Foo Bar\'ly", "mul\'ti\'ple"));
        $actual = gosUtility_String::stripslashesDeep($string);

        $expected = array(array("O'Reilly", "O'Really"), array("Foo Bar'ly", "mul'ti'ple"));
        $this->assertEquals($expected, $actual);
    }
}
