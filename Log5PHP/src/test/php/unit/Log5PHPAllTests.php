<?php

/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 * 
 *      http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @category   tests   
 * @package external_Log5PHP
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version    SVN: $Id$
 */

require_once dirname(__FILE__) . '/testconfig.inc.php';

require_once 'appenders/AllTests.php';
require_once 'layouts/AllTests.php';
require_once 'spi/AllTests.php';
require_once 'or/AllTests.php';

require_once 'LoggerLogTest.php';
require_once 'LoggerLevelTest.php';
require_once 'LoggerRootTest.php';
require_once 'LoggerHierarchyTest.php';
require_once 'LoggerBasicConfiguratorTest.php';

class Log5PHPAllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner :: run(self :: suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('log5php');

        $suite->addTestSuite('LoggerLogTest');
        $suite->addTestSuite('LoggerLevelTest');
        $suite->addTestSuite('LoggerRootTest');
        $suite->addTestSuite('LoggerHierarchyTest');
        $suite->addTestSuite('LoggerBasicConfiguratorTest');
        
        $suite->addTest(appenders_AllTests :: suite());
        $suite->addTest(spi_AllTests :: suite());
        $suite->addTest(Layouts_AllTests :: suite());

        return $suite;
    }
}

?>
