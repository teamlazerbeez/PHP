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

class gosUtility_YamlLoaderTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function tearDownExtension()
    {
    }

    public function testLoadFile_FileExists()
    {
        $parsedYaml = gosUtility_YamlLoader::loadFile(GOS_ROOT . 'Utility/test/gosUtility/_helpers/nameMapping.yaml', false);

        $this->assertNotNull($parsedYaml);
        $this->assertTrue(is_array($parsedYaml['dataType']));
    }

    public function testLoadFile_FileNotExists()
    {
        $fileName = GOS_ROOT . 'Utility/test/gosUtility/_helpers/nameMapping.yaml.doesNotExist';

        try
        {
            gosUtility_YamlLoader::loadFile($fileName, false);
            $this->fail("Yaml loader did not throw an error for missing file.");
        }
        catch(gosException_InvalidArgument $e)
        {
            $this->assertEquals('File ' . $fileName . ' does not exist', $e->getMessage());
        }
    }

    public function testLoadFile_NullOnEmptyFile()
    {
        $fileName = GOS_ROOT . 'Utility/test/gosUtility/_helpers/Empty.yaml';

        // Specify that an error should NOT be thrown for empty yaml content
        $parsedYaml = gosUtility_YamlLoader::loadFile($fileName, false);

        $this->assertNull($parsedYaml);
    }

    public function testLoadFile_FaultOnEmptyFile()
    {
        $fileName = GOS_ROOT . 'Utility/test/gosUtility/_helpers/Empty.yaml';

        try
        {
            // Specify that an error SHOULD be thrown for empty yaml content
            gosUtility_YamlLoader::loadFile($fileName, true);
            $this->fail("Yaml loader did not throw an error for empty file.");
        }
        catch(gosException_RuntimeError $e)
        {
            $this->assertEquals($fileName . ' was empty!', $e->getMessage());
        }
    }
}
