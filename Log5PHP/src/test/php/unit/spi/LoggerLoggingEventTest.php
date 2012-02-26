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
 * @subpackage src_test_php_unit_spi
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version    SVN: $Id$
 */

/**  */
require_once 'PHPUnit/Framework/TestCase.php';

class Log5PHP_Appender_LogEventStub extends Log5PHP_Appender_Null
{

    protected $requiresLayout = true;

    protected function append(Log5PHP_LogEvent $event)
    {
        $this->layout->format($event);
    }

}

class Log5PHP_Layout_LogEventStub extends Log5PHP_Layout_Base
{

    public function activateOptions()
    {
        return;
    }

    public function format($event)
    {
        LoggerLoggingEventTest :: $locationInfo = $event->getLocationInfo();
    }
}

class LoggerLoggingEventTest extends PHPUnit_Framework_TestCase
{

    public static $locationInfo;

    public function testConstructWithLogger()
    {
        $l = Log5PHP_Level :: getLevelDebug();
        $e = new Log5PHP_LogEvent('fqcn', Log5PHP_Factory_LoggerDefault::makeNewLoggerInstance('TestLogger'), $l, 'test');
        $this->assertEquals($e->getLoggerName(), 'TestLogger');
    }

    public function testConstructWithTimestamp()
    {
        $l = Log5PHP_Level :: getLevelDebug();
        $timestamp = microtime(true);
        $e = new Log5PHP_LogEvent('fqcn', Log5PHP_Factory_LoggerDefault::makeNewLoggerInstance('TestLogger'), $l, 'test');
        $this->assertTrue(($e->getTimeStampFloat() - $timestamp) < 0.1);
    }

    public function testGetStartTime()
    {
        $time = Log5PHP_LogEvent :: getStartTime();
        $this->assertType('float', $time);
        $time2 = Log5PHP_LogEvent :: getStartTime();
        $this->assertEquals($time, $time2);
    }

    public function testGetLocationInformation()
    {
        $hierarchy = Log5PHP_LoggerRepository::getInstance();
        $root = $hierarchy->getRootLogger();

        $a = new Log5PHP_Appender_LogEventStub('A1');
        $a->setLayout(new Log5PHP_Layout_LogEventStub());
        $root->addAppender($a);

        $logger = $hierarchy->getLogger('test');

        $line = __LINE__;
        $logger->debug('test');
        $hierarchy->shutdown();

        $li = self :: $locationInfo;

        $this->assertEquals($li->getClassName(), get_class($this));
        $this->assertEquals($li->getFileName(), __FILE__);
        $this->assertEquals($li->getLineNumber(), $line + 1);
        $this->assertEquals($li->getMethodName(), __FUNCTION__);
    }

}
?>

