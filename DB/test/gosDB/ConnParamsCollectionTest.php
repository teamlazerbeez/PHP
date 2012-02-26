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

class lib_gosDB_ConnParamsCollectionTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function tearDownExtension()
    {
    }

    public function testSetParams_and_GetParams()
    {
        $collection = new gosDB_ConnParamsCollection();
        $connParamsMain = new gosDB_ConnParams('host', 'user', 'password', 'main', 'port');

        // make sure you can set params for multiple database connections
        $collection->setParams('main', $connParamsMain);

        $this->assertSame($connParamsMain, $collection->getParams('main'));
    }

    public function testSetParams_ErrorsWhenParamsAlreadySet()
    {
        $collection = new gosDB_ConnParamsCollection();

        $collection->setParams('main', new gosDB_ConnParams('host', 'user', 'password', 'dbname', 'port'));
        $this->setExpectedException('gosException_StateError');
        $collection->setParams('main', new gosDB_ConnParams('host', 'user', 'password', 'dbname', 'port'));
    }

    public function testGetParams_ErrorsWhenParamsNotYetSet()
    {
        $collection = new gosDB_ConnParamsCollection();

        $this->setExpectedException('gosException_StateError');
        $collection->getParams('main');
    }

}
