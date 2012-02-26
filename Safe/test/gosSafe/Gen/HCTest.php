<?php
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/Core/testConfig.inc.php';

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
class lib_gosSafe_Gen_HCTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function tearDownExtension()
    {
    } 

    public function testGetGenerator_ReturnsHCGeneratorObj()
    {
        $result = gosSafe_Gen_HC::getGenerator('<span>{hc:name}</span>');
        $this->assertTrue($result instanceof gosSafe_Gen_HC);
    }

    public function testGetSafe_ReturnsHCSafeObj()
    {
        // the rest of the functionality of this is tested in the base
        $result = gosSafe_Gen_HC::getSafe('<span>{hc:name}</span>', 
            array('name' => '<script>alert(1)</script>'));
        $this->assertTrue($result instanceof gosSafe_HC);
    }

}
