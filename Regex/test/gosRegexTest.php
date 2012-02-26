<?php
require_once dirname(dirname(dirname(__FILE__))) .'/Core/testConfig.inc.php';

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
class gosRegexTest extends gosTest_Framework_TestCase
{
    protected $backtrack_limit;
    protected $recursion_limit;

    function setUpExtension()
    {
        $this->backtrack_limit = ini_get('pcre.backtrack_limit');
        $this->recursion_limit = ini_get('pcre.recursion_limit');
    }

    function tearDownExtension()
    {
        // Reset things that might ahve been fiddled with
        ini_set('pcre.backtrack_limit', $this->backtrack_limit);
        ini_set('pcre.recursion_limit', $this->backtrack_limit);
    }

    public function testFilter_Filters()
    {
        $subject = array('1', 'a', '2', 'b', '3', 'A', 'B', '4');
        $pattern = array('/\d/', '/[a-z]/', '/[1a]/');
        $replace = array('A:$0', 'B:$0', 'C:$0');
        $results = gosRegex::filter($pattern, $replace, $subject);

        $expected = array(
            0 => 'A:C:1',
            1 => 'B:C:a',
            2 => 'A:2',
            3 => 'B:b',
            4 => 'A:3',
            7 => 'A:4',
        );

        foreach ($expected as $key => $value)
        {
            $this->assertEquals($value, $results[$key]);
        }
    }

    public function testGrep_Greps()
    {
        $input = array('foo', 'bar', 'foobar', 'barbaz', 'quxbar', 'fooqux');
        $results = gosRegex::grep('/foo/', $input);

        $this->assertEquals('foo', $results[0]);
        $this->assertEquals('foobar', $results[2]);
        $this->assertEquals('fooqux', $results[5]);
    }

    public function testGrep_GrepsInverted()
    {
        $input = array('foo', 'bar', 'foobar', 'barbaz', 'quxbar', 'fooqux');
        $results = gosRegex::grep('/foo/', $input, PREG_GREP_INVERT);

        $this->assertEquals('bar', $results[1]);
        $this->assertEquals('barbaz', $results[3]);
        $this->assertEquals('quxbar', $results[4]);
    }

    public function testMatch_ReturnsMatchCount()
    {
        $numMatches = gosRegex::match('/foo/', 'foo foo bar foo baz foo');
        $this->assertEquals(1, $numMatches);
    }

    public function testMatch_ReturnsMatchesArray()
    {
        gosRegex::match('/foo (bar)/', 'foo foo bar foo baz foo', $matches);
        $this->assertEquals('foo bar', $matches[0]);
        $this->assertEquals('bar', $matches[1]);
    }

    public function testMatch_StartsAtOffset()
    {
        gosRegex::match('/foo\w*/', 'foo foobar bar foo baz foo', $matches, null, 1);
        $this->assertEquals('foobar', $matches[0]);
    }

    public function testMatch_HitBacktrackLimit()
    {
        // Ensure we hit the backtrack limit
        ini_set('pcre.backtrack_limit', 1);

        $this->setExpectedException('gosException_RegularExpression', 'PREG_BACKTRACK_LIMIT_ERROR');

        // See http://us.php.net/preg_last_error
        gosRegex::match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
    }

    public function testMatch_HitRecursionLimit()
    {
        // Ensure we hit the recursion limit
        ini_set('pcre.recursion_limit', 1);

        $this->setExpectedException('gosException_RegularExpression', 'PREG_RECURSION_LIMIT_ERROR');

        // See http://us.php.net/preg_last_error
        gosRegex::match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
    }

    public function testMatch_all_ReturnsMatchesArray()
    {
        // See http://us.php.net/preg_match_all
        gosRegex::match_all("/\(?  (\d{3})?  \)?  (?(1)  [\-\s] ) \d{3}-\d{4}/x", "Call 555-1212 or 1-800-555-1212", $matches);

        $this->assertEquals('555-1212', $matches[0][0]);
        $this->assertEquals('800-555-1212', $matches[0][1]);
        $this->assertEquals('', $matches[1][0]);
        $this->assertEquals('800', $matches[1][1]);
    }

    public function testQuote_Quotes()
    {
        $actual = gosRegex::quote('$40 for a g3/400');

        $this->assertEquals('\$40 for a g3/400', $actual);
    }

    public function testQuote_QuotesWithDelimiter()
    {
        $actual = gosRegex::quote('$40 for a g3/400', '/');

        $this->assertEquals('\$40 for a g3\/400', $actual);
    }

    public function testReplace_Replaces()
    {
        $actual = gosRegex::replace('/foo/', 'bar', 'foobar');

        $this->assertEquals('barbar', $actual);
    }

    public function testReplace_callback_Replaces()
    {
        $callback = create_function('$matches', "return 'bar';");
        $actual = gosRegex::replace_callback('/foo/', $callback, 'foobar');

        $this->assertEquals('barbar', $actual);
    }

    public function testReplace_callback_ReplacesWhenGivenSelfStringRelativeFunction()
    {
        $callback = create_function('$matches', "return 'bar';");
        $actual = gosRegex::replace_callback('/foo/', 'self::replaceCallback', 'foobar');

        $this->assertEquals('barbar', $actual);
    }

    public function testReplace_callback_ReplacesWhenGivenSelfArrayRelativeFunction()
    {
        $callback = create_function('$matches', "return 'bar';");
        $actual = gosRegex::replace_callback('/foo/', array('self', 'replaceCallback'), 'foobar');

        $this->assertEquals('barbar', $actual);
    }

    public function testReplace_callback_ReplacesWhenGivenThisAndFunction()
    {
        $callback = create_function('$matches', "return 'bar';");
        $actual = gosRegex::replace_callback('/foo/', array($this, 'replaceCallback'), 'foobar');

        $this->assertEquals('barbar', $actual);
    }

    public static function replaceCallback($matches)
    {
        return 'bar';
    }

    public function testSplit_Splits()
    {
        $results = gosRegex::split('/\s/', 'foofoobar barfoobazfoo');

        $this->assertEquals('foofoobar', $results[0]);
        $this->assertEquals('barfoobazfoo', $results[1]);
    }
}
