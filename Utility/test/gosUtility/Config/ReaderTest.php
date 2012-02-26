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
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/Core/testConfig.inc.php';
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/Utility/test/gosUtility/Config/_helpers/ReaderProxy.cls.php';

class gosUtility_Config_ReaderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        gosUtility_Config_ReaderProxy::reset();
        gosUtility_Config_ReaderProxy::addConfigFile(GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/shared.yaml');
        gosUtility_Config_ReaderProxy::addConfigFile(GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/env.yaml');
        gosUtility_Config_ReaderProxy::addConfigFile(GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/machine.yaml');
    }

    public function tearDown()
    {
        gosUtility_Config_ReaderProxy::reset();
    }

    function testYamlParse()
    {
        $this->assertSame(gosUtility_Config_Reader::getConfigEntry('a_large_number') , 1000000);
        $this->assertEquals(gosUtility_Config_Reader::getConfigEntry('nested'),
        array(  'thing',
                'thing2',
                'other thing',
                 array('last thing' => array('subentry', 'another one'))
             )
        );
        $this->assertSame(gosUtility_Config_Reader::getConfigEntry('environment') , 'test');
        $this->assertSame(gosUtility_Config_Reader::getConfigEntry('blame') , 'someone-else@genius.com');
    }

    function testSetBadFile()
    {
        $this->setExpectedException('gosException_InvalidArgument', 'File /no/such/file/asdf does not exist');

        gosUtility_Config_Reader::addConfigFile('/no/such/file/asdf');
    }

    function testPush()
    {
        $this->assertSame(1000000, gosUtility_Config_Reader::getConfigEntry('a_large_number')); # set in machine.yaml

        gosUtility_Config_Reader::addConfigFile(GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/machine_alt.yaml');
        $this->assertSame(1000000, gosUtility_Config_Reader::getConfigEntry('a_large_number'));

        $this->assertSame(1000001, gosUtility_Config_Reader::getConfigEntry('a_larger_number')); # set in machine_alt
        gosUtility_Config_Reader::addConfigFile(GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/shared.yaml');
        $this->assertSame(1000, gosUtility_Config_Reader::getConfigEntry('a_large_number'));
    }

    function testBadEntry()
    {
        $this->setExpectedException('gosException_InvalidArgument', 'Config entry \'asdf\' does not exist');
        gosUtility_Config_Reader::getConfigEntry('asdf');
    }

    function testGetConfigFile()
    {
        $this->assertSame(array(
            GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/shared.yaml',
            GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/env.yaml',
            GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/machine.yaml'),
            gosUtility_Config_ReaderProxy::getConfigFileStack());
    }
}
