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

class gosUtility_URLStitcherTest extends gosTest_Framework_TestCase
{
    public function setUpExtension()
    {
    }

    public function tearDownExtension()
    {
    }

    public function testAbsolute_BuildsFullURL()
    {
        $actual = gosUtility_URLStitcher::absolute('http', 'example.com', '80', 'user', 'pass', '/path/to/something', 'foo=bar&baz=qux', 'anchor');
        $expected = 'http://user:pass@example.com:80/path/to/something?foo=bar&baz=qux#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testAbsolute_NoPort()
    {
        $actual = gosUtility_URLStitcher::absolute('http', 'example.com', '', 'user', 'pass', '/path/to/something', 'foo=bar&baz=qux', 'anchor');
        $expected = 'http://user:pass@example.com/path/to/something?foo=bar&baz=qux#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testAbsolute_NoAuth()
    {
        $actual = gosUtility_URLStitcher::absolute('http', 'example.com', '80', '', '', '/path/to/something', 'foo=bar&baz=qux', 'anchor');
        $expected = 'http://example.com:80/path/to/something?foo=bar&baz=qux#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testAbsolute_NoPass()
    {
        $actual = gosUtility_URLStitcher::absolute('http', 'example.com', '80', 'user', '', '/path/to/something', 'foo=bar&baz=qux', 'anchor');
        $expected = 'http://user@example.com:80/path/to/something?foo=bar&baz=qux#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testAbsolute_NoPath()
    {
        $actual = gosUtility_URLStitcher::absolute('http', 'example.com', '80', 'user', 'pass', '', 'foo=bar&baz=qux', 'anchor');
        $expected = 'http://user:pass@example.com:80?foo=bar&baz=qux#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testAbsolute_NoQuery()
    {
        $actual = gosUtility_URLStitcher::absolute('http', 'example.com', '80', 'user', 'pass', '/path/to/something', '', 'anchor');
        $expected = 'http://user:pass@example.com:80/path/to/something#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testAbsolute_NoFragment()
    {
        $actual = gosUtility_URLStitcher::absolute('http', 'example.com', '80', 'user', 'pass', '/path/to/something', 'foo=bar&baz=qux', '');
        $expected = 'http://user:pass@example.com:80/path/to/something?foo=bar&baz=qux';
        $this->assertSame($expected, $actual);
    }

    public function testRelative_KeepsLeadingSlash()
    {
        $actual = gosUtility_URLStitcher::relative('/path/to/something', 'foo=bar&baz=qux', 'anchor');
        $expected = '/path/to/something?foo=bar&baz=qux#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testRelative_NoLeadingSlash()
    {
        $actual = gosUtility_URLStitcher::relative('path/to/something', 'foo=bar&baz=qux', 'anchor');
        $expected = 'path/to/something?foo=bar&baz=qux#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testRelative_NoQuery()
    {
        $actual = gosUtility_URLStitcher::relative('/path/to/something', '', 'anchor');
        $expected = '/path/to/something#anchor';
        $this->assertSame($expected, $actual);
    }

    public function testRelative_NoAnchor()
    {
        $actual = gosUtility_URLStitcher::relative('/path/to/something', 'foo=bar&baz=qux', '');
        $expected = '/path/to/something?foo=bar&baz=qux';
        $this->assertSame($expected, $actual);
    }
}
