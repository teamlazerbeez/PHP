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

class LoggerBasicConfiguratorTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        Log5PHP_Configurator_Basic :: configure();
    }

    protected function tearDown()
    {
        Log5PHP_Configurator_Basic :: resetConfiguration();
    }

    public function testConfigure()
    {
        $root = Log5PHP_Manager :: getRootLogger();
        $appender = $root->getAppender('A1');
        self :: assertType('Log5PHP_Appender_Console', $appender);
        $layout = $appender->getLayout();
        self :: assertType('Log5PHP_Layout_TTCC', $layout);
    }

    public function testResetConfiguration()
    {
        throw new PHPUnit_Framework_IncompleteTestError();

        $this->testConfigure();

        //$root = Log5PHP_Manager::getRootLogger();

        $hierarchy = Log5PHP_LoggerRepository::getInstance();

        var_dump(count($hierarchy->getCurrentLoggers()));

        Log5PHP_Configurator_Basic :: resetConfiguration();

        var_dump(count($hierarchy->getCurrentLoggers()));

        /*
        $logger = Log5PHP_Manager::getLogger('A1');
        
        $layout = $logger->getLayout();
        var_dump($layout);
        
        var_dump($logger->getName());
        */
        //$appender = Log5PHP_Manager::getRootLogger()->getAppender('A1');
        //var_dump($appender);

    }

    /*public function testRootLogger() {
            $root = Log5PHP_Manager::getRootLogger();
            $a = $root->getAppender('A1');
            self::assertType('LoggerAppenderConsole', $a);
            $l = $a->getLayout();
            self::assertType('LoggerLayoutTTCC', $l);
    }*/

}
?>
