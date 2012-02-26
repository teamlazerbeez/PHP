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

/**
 * Tests the Log5PHP_Level
 */
class LoggerLevelTest extends PHPUnit_Framework_TestCase
{

    protected function doTestLevel($o, $code, $str, $syslog)
    {
        $this->assertTrue($o instanceof Log5PHP_Level);
        $this->assertEquals($o->toInt(), $code);
        $this->assertEquals($o->toString(), $str);
        $this->assertEquals($o->getSyslogEquivalent(), $syslog);
    }

    public function testLevelOff()
    {
        $this->doTestLevel(Log5PHP_Level :: getLevelOff(), LOG5PHP_LEVEL_OFF_INT, 'OFF', 0);
        $this->doTestLevel(Log5PHP_Level :: toLevel(LOG5PHP_LEVEL_OFF_INT), LOG5PHP_LEVEL_OFF_INT, 'OFF', 0);
        $this->doTestLevel(Log5PHP_Level :: toLevel('OFF'), LOG5PHP_LEVEL_OFF_INT, 'OFF', 0);
    }

    public function testLevelFatal()
    {
        $this->doTestLevel(Log5PHP_Level :: getLevelFatal(), LOG5PHP_LEVEL_FATAL_INT, 'FATAL', 0);
        $this->doTestLevel(Log5PHP_Level :: toLevel(LOG5PHP_LEVEL_FATAL_INT), LOG5PHP_LEVEL_FATAL_INT, 'FATAL', 0);
        $this->doTestLevel(Log5PHP_Level :: toLevel('FATAL'), LOG5PHP_LEVEL_FATAL_INT, 'FATAL', 0);
    }

    public function testLevelError()
    {
        $this->doTestLevel(Log5PHP_Level :: getLevelError(), LOG5PHP_LEVEL_ERROR_INT, 'ERROR', 3);
        $this->doTestLevel(Log5PHP_Level :: toLevel(LOG5PHP_LEVEL_ERROR_INT), LOG5PHP_LEVEL_ERROR_INT, 'ERROR', 3);
        $this->doTestLevel(Log5PHP_Level :: toLevel('ERROR'), LOG5PHP_LEVEL_ERROR_INT, 'ERROR', 3);
    }

    public function testLevelWarn()
    {
        $this->doTestLevel(Log5PHP_Level :: getLevelWarn(), LOG5PHP_LEVEL_WARN_INT, 'WARN', 4);
        $this->doTestLevel(Log5PHP_Level :: toLevel(LOG5PHP_LEVEL_WARN_INT), LOG5PHP_LEVEL_WARN_INT, 'WARN', 4);
        $this->doTestLevel(Log5PHP_Level :: toLevel('WARN'), LOG5PHP_LEVEL_WARN_INT, 'WARN', 4);
    }

    public function testLevelInfo()
    {
        $this->doTestLevel(Log5PHP_Level :: getLevelInfo(), LOG5PHP_LEVEL_INFO_INT, 'INFO', 6);
        $this->doTestLevel(Log5PHP_Level :: toLevel(LOG5PHP_LEVEL_INFO_INT), LOG5PHP_LEVEL_INFO_INT, 'INFO', 6);
        $this->doTestLevel(Log5PHP_Level :: toLevel('INFO'), LOG5PHP_LEVEL_INFO_INT, 'INFO', 6);
    }

    public function testLevelDebug()
    {
        $this->doTestLevel(Log5PHP_Level :: getLevelDebug(), LOG5PHP_LEVEL_DEBUG_INT, 'DEBUG', 7);
        $this->doTestLevel(Log5PHP_Level :: toLevel(LOG5PHP_LEVEL_DEBUG_INT), LOG5PHP_LEVEL_DEBUG_INT, 'DEBUG', 7);
        $this->doTestLevel(Log5PHP_Level :: toLevel('DEBUG'), LOG5PHP_LEVEL_DEBUG_INT, 'DEBUG', 7);
    }

    public function testLevelAll()
    {
        $this->doTestLevel(Log5PHP_Level :: getLevelAll(), LOG5PHP_LEVEL_ALL_INT, 'ALL', 7);
        $this->doTestLevel(Log5PHP_Level :: toLevel(LOG5PHP_LEVEL_ALL_INT), LOG5PHP_LEVEL_ALL_INT, 'ALL', 7);
        $this->doTestLevel(Log5PHP_Level :: toLevel('ALL'), LOG5PHP_LEVEL_ALL_INT, 'ALL', 7);
    }
}
?>
