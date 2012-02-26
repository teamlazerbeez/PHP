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

class lib_gosDB_UtilTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function tearDownExtension()
    {
    }

    public function testValidateDBIdentifier()
    {
        gosDB_Util::validateDBIdentifier('main');
        // we just don't want an exception thrown
    }

    public function testValidateDBIdentifier_Invalid()
    {
        $this->setExpectedException('gosException_InvalidArgument');
        gosDB_Util::validateDBIdentifier('invalid');
    }

    public function testGetValidDBIdentifiers()
    {
        $result = gosDB_Util::getValidDBIdentifiers();
        $this->assertTrue(is_array($result));
    }

    public function testGetTmpDir()
    {
        $db = gosDB_Helper::getDBByName('main');
        $tmpdir = gosDB_Util::getTmpDir($db);
        $this->assertNotNull($tmpdir);
    }
}
