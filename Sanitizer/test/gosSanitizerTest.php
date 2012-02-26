<?php
// No fixtures in this test, but we need a DB defined
require_once dirname(dirname(dirname(__FILE__))) .'/Fixture/fixtureTestConfig.inc.php';

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
class gosSanitizerTest extends gosTest_Framework_TestCase
{
    function setUpExtension()
    {
    }

    function tearDownExtension()
    {
    }

    protected function getIllegalUTF8Char()
    {
        return pack('H*', 'C379');
    }

    public function testGetHexForInt_WorksWithSingleDigitIntegers()
    {
        $proxy = self::getProxy('gosSanitizer');
        $result = $proxy->PROTECTED_GetHexForInt(0);
        $this->assertEquals('00', $result);

        $result = $proxy->PROTECTED_GetHexForInt(5);
        $this->assertEquals('05', $result);

        $result = $proxy->PROTECTED_GetHexForInt(10);
        $this->assertEquals('0a', $result);

        $result = $proxy->PROTECTED_GetHexForInt(15);
        $this->assertEquals('0f', $result);
    }

    public function testGetHexForInt_WorksWithDigitsBetween16And255()
    {
        $proxy = self::getProxy('gosSanitizer');
        foreach (range(16, 255) as $value)
        {
            $result = $proxy->PROTECTED_GetHexForInt($value);
            $this->assertEquals(dechex($value), $result);
        }
    }

    public function testGetHexForInt_ThrowsExceptionWithDigitsGreaterThan255()
    {
        $proxy = self::getProxy('gosSanitizer');

        $this->setExpectedException('gosException_InvalidArgument', 'This function does not currently support integers of more than 8 bits');
        $result = $proxy->PROTECTED_GetHexForInt(500);
    }

    public function testGetHTMLAttributeTranslationTable_ReturnsTableOfCorrectSize()
    {
        $proxy = self::getProxy('gosSanitizer');
        $actual = $proxy->PROTECTED_getHTMLAttributeTranslationTable();
        $this->assertEquals(66, count($actual));
    }

    public function testGetJSTranslationTable_ReturnsTableOfCorrectSize()
    {
        $proxy = self::getProxy('gosSanitizer');
        $actual = $proxy->PROTECTED_getJSTranslationTable();
        $this->assertEquals(66, count($actual));
    }

    public function testGetCSSTranslationTable_ReturnsTableOfCorrectSize()
    {
        $proxy = self::getProxy('gosSanitizer');
        $actual = $proxy->PROTECTED_getCSSTranslationTable();
        $this->assertEquals(66, count($actual));
    }

    public function testGetXMLTranslationTable_ReturnsTableOfCorrectSize()
    {
        $proxy = self::getProxy('gosSanitizer');
        $actual = $proxy->PROTECTED_getXMLTranslationTable();
        $this->assertEquals(5, count($actual));
    }

    public function testEnsureValidUTF8_Yep()
    {
        $actual = gosSanitizer::ensureValidUTF8("foobar");
        $this->assertEquals("foobar", $actual);
    }

    public function testEnsureValidUTF8_SingleIllegalChar_ThrowsException()
    {
        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::ensureValidUTF8($this->getIllegalUTF8Char());
    }

    public function testEnsureValidUTF8_FirstCharIllegal_ThrowsException()
    {
        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::ensureValidUTF8($this->getIllegalUTF8Char() . "foobar");
    }

    public function testEnsureValidUTF8_LastCharIllegal_ThrowsException()
    {
        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::ensureValidUTF8('foobar' . $this->getIllegalUTF8Char());
    }

    public function testEnsureValidUTF8_MiddleCharIllegal_ThrowsException()
    {
        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::ensureValidUTF8('foobar' . $this->getIllegalUTF8Char() . "foobar");
    }

    public function testSanitizeForHTMLContent_SanitizesData()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $actual = gosSanitizer::sanitizeForHTMLContent($unsafeString);
        $expected = '&lt;&gt;&#x2F;&#039;&quot;&amp;!@#$%^*()-=asdf1234`~_+';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForHTMLContent_InvalidUTF8_ThrowsException()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+' . $this->getIllegalUTF8Char();

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForHTMLContent($unsafeString);
    }

    public function testSanitizeForHTMLAttribute_SanitizesData()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $actual = gosSanitizer::sanitizeForHTMLAttribute($unsafeString);
        $expected = '&#x3c;&#x3e;&#x2f;&#x27;&#x22;&#x26;&#x21;&#x40;&#x23;&#x24;&#x25;&#x5e;&#x2a;&#x28;&#x29;&#x2d;&#x3d;asdf1234&#x60;&#x7e;&#x5f;&#x2b;';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForHTMLAttribute_IllegalUTF8_ThrowsException()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+' . $this->getIllegalUTF8Char();

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForHTMLAttribute($unsafeString);
    }

    public function testSanitizeForHTMLAttribute_ThenDesanitize()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $escaped = gosSanitizer::sanitizeForHTMLAttribute($unsafeString);
        $unescaped = gosSanitizer::desanitizeForHTMLAttribute($escaped);
        $this->assertSame($unsafeString, $unescaped);
    }

    public function testSanitizeForHTMLAttribute_ThenDesanitize_IllegalUTF8_ThrowsException()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $escaped = gosSanitizer::sanitizeForHTMLAttribute($unsafeString);

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $unescaped = gosSanitizer::desanitizeForHTMLAttribute($escaped . $this->getIllegalUTF8Char());
    }

    public function testSanitizeForInverseHTMLAttribute_UnSanitizesData()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $escaped = '&#x3c;&#x3e;&#x2f;&#x27;&#x22;&!@#$&#x25;&#x5e;&#x2a;()&#x2d;&#x3d;asdf1234`~_&#x2b;';
        $actual = gosSanitizer::desanitizeForHTMLAttribute($escaped);
        $this->assertSame($unsafeString, $actual);
    }

    public function testSanitizeForJS_SanitizesData()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $actual = gosSanitizer::sanitizeForJS($unsafeString);
        $expected = '\x3c\x3e\x2f\x27\x22\x26\x21\x40\x23\x24\x25\x5e\x2a\x28\x29\x2d\x3dasdf1234\x60\x7e\x5f\x2b';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForJS_IllegalUTF8_ThrowsException()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+' . $this->getIllegalUTF8Char();
        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForJS($unsafeString);
    }

    public function testSanitizeForCSS_SanitizesData()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $actual = gosSanitizer::sanitizeForCSS($unsafeString);
        $expected = '\3c\3e\2f\27\22\26\21\40\23\24\25\5e\2a\28\29\2d\3dasdf1234\60\7e\5f\2b';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForCSS_IllegalUTF8_ThrowsException()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+' . $this->getIllegalUTF8Char();

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForCSS($unsafeString);
    }

    public function testSanitizeForXML_SanitizesData()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $actual = gosSanitizer::sanitizeForXML($unsafeString);
        $expected = '&#x3c;&#x3e;/&#x27;&#x22;&#x26;!@#$%^*()-=asdf1234`~_+';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForXML_IllegalUTF8_ThrowsException()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+' . $this->getIllegalUTF8Char();

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForXML($unsafeString);
    }

    public function testSanitizeForURLComponent_SanitizesData()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $actual = gosSanitizer::sanitizeForURLComponent($unsafeString);
        $expected = '%3C%3E%2F%27%22%26%21%40%23%24%25%5E%2A%28%29-%3Dasdf1234%60%7E_%2B';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForURLComponent_IllegalUTF8_ThrowsException()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+' . $this->getIllegalUTF8Char();

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForURLComponent($unsafeString);
    }

    public function testSanitizeForAbsoluteURL_SafeURL_NoChange()
    {
        $safeString = 'http://user:password@host.com/path/more/last.html#fragment';
        $actual = gosSanitizer::sanitizeForAbsoluteURL($safeString);
        $this->assertSame($safeString, $actual);
    }

    public function testSanitizeForAbsoluteURL_IllegalUTF8_ThrowsException()
    {
        $safeString = 'http://user:password@host.com/path/more/last.html#fragment' . $this->getIllegalUTF8Char();

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForAbsoluteURL($safeString);
    }

    public function testSanitizeForAbsoluteURL_NoQueryParamsAllowed()
    {
        $safeString = 'http://user:password@host.com/path/more/last.html?query=value&second=foo&third=bar#fragment';
        $this->setExpectedException('gosException_InvalidArgument', 'Cannot provide a query string; query params must be added afterwards to ensure correctness');
        gosSanitizer::sanitizeForAbsoluteURL($safeString);
    }

    public function testSanitizeForRelativeURL_SafeURL_NoChange()
    {
        $safeString = 'path/more/last.html#fragment';
        $actual = gosSanitizer::sanitizeForRelativeURL($safeString);
        $this->assertSame($safeString, $actual);
    }

    public function testSanitizeForRelativeURL_IllegalUTF8_ThrowsException()
    {
        $safeString = 'path/more/last.html#fragment' . $this->getIllegalUTF8Char();

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $actual = gosSanitizer::sanitizeForRelativeURL($safeString);
    }

    public function testSanitizeForRelativeURL_NoQueryParamsAllowed()
    {
        $safeString = 'path/more/last.html?query=value&second=foo&third=bar#fragment';
        $this->setExpectedException('gosException_InvalidArgument', 'Cannot provide a query string; query params must be added afterwards to ensure correctness');
        gosSanitizer::sanitizeForAbsoluteURL($safeString);
    }

    public function testSanitizeForRelativeURL_ScriptInPath_SanitizesURL()
    {
        $unsafeString = 'path/<script>alert("TOYO!")</script>/foo.html#fragment';
        $actual = gosSanitizer::sanitizeForRelativeURL($unsafeString);
        $expected = 'path/%3Cscript%3Ealert%28%22TOYO%21%22%29%3C/script%3E/foo.html#fragment';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForRelativeURL_PathAloneIsEncoded()
    {
        $unsafeString = 'path</foo<>.php';
        $actual = gosSanitizer::sanitizeForRelativeURL($unsafeString);
        $expected = 'path%3C/foo%3C%3E.php';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForRelativeURL_QueryAloneThrowsException()
    {
        $unsafeString = '?key<=value>';
        $this->setExpectedException('gosException_InvalidArgument', 'Cannot provide a query string; query params must be added afterwards to ensure correctness');

        gosSanitizer::sanitizeForRelativeURL($unsafeString);
    }

    public function testSanitizeForRelativeURL_PathQueryAndFragmentThrowsException()
    {
        $unsafeString = 'path/foo<>.php?key<=value>#<scripty';
        $this->setExpectedException('gosException_InvalidArgument', 'Cannot provide a query string; query params must be added afterwards to ensure correctness');
        gosSanitizer::sanitizeForRelativeURL($unsafeString);
    }

    public function testSanitizeForRelativeURL_PathAndFragmentAreEncoded()
    {
        $unsafeString = 'path/foo<>.php#<scripty';
        $actual = gosSanitizer::sanitizeForRelativeURL($unsafeString);
        $expected = 'path/foo%3C%3E.php#%3Cscripty';
        $this->assertSame($expected, $actual);
    }

    public function testSanitizeForRelativeURL_QueryAndFragmentThrowsException()
    {
        $unsafeString = '?key<=value>#<scripty';
        $this->setExpectedException('gosException_InvalidArgument', 'Cannot provide a query string; query params must be added afterwards to ensure correctness');
        gosSanitizer::sanitizeForRelativeURL($unsafeString);
    }

    public function testSanitizeForRelativeURL_FragmentAloneIsEncoded()
    {
        $unsafeString = '#<scripty';
        $actual = gosSanitizer::sanitizeForRelativeURL($unsafeString);
        $expected = '#%3Cscripty';
        $this->assertSame($expected, $actual);
    }


    public function testSanitizeURLPathString_IllegalUTF8_ThrowsException()
    {
        $path = 'value1/=<script;>/value2' . $this->getIllegalUTF8Char();
        $proxy = self::getProxy('gosSanitizer', array());

        $this->setExpectedException('gosException_Security', 'Invalid UTF-8 character');
        $result = $proxy->PROTECTED_sanitizeURLPathString($path);
    }

    public function testSanitizeURLPathString_PathThatDoesNotStartWithSlashIsReturnedWithNoSlash()
    {
        $path = 'value1/value2';
        $proxy = self::getProxy('gosSanitizer', array());

        $result = $proxy->PROTECTED_sanitizeURLPathString($path);

        $this->assertEquals($path, $result);
    }

    public function testSanitizeURLQueryString_SanitizesKeysAndValues()
    {
        $path = 'key<=>val';
        $proxy = self::getProxy('gosSanitizer', array());

        $expected = 'key%3C=%3Eval';
        $result = $proxy->PROTECTED_sanitizeURLQueryString($path);

        $this->assertEquals($expected, $result);
    }

    public function testSanitizeURLQueryString_SanitizesKeyWithNoValue()
    {
        $path = 'ke>y';
        $proxy = self::getProxy('gosSanitizer', array());

        $expected = 'ke%3Ey=';
        $result = $proxy->PROTECTED_sanitizeURLQueryString($path);

        $this->assertEquals($expected, $result);

    }

    public function testSanitizeForDB_WithDBReturnsEncodedValue()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $db = gosDB_Helper::getDBByName('main');
        $this->assertSame(gosSanitizer::sanitizeForDB($unsafeString, $db), gosSanitizer::sanitizeForDB($unsafeString, $db));
    }

    public function testSanitizeForDB_WithoutDBReturnsEncodedValue()
    {
        $unsafeString = '<>/\'"&!@#$%^*()-=asdf1234`~_+';
        $this->assertSame(gosSanitizer::sanitizeForDB($unsafeString, gosDB_Helper::getDBByName('main')), gosSanitizer::sanitizeForDB($unsafeString));
    }

    public function testEnsureDBColumn_WithValidColumn()
    {
        $column = "everything.AllThe_Time";
        $result = gosSanitizer::ensureDBColumn($column);
        $this->assertEquals($column, $result);
    }

    public function testEnsureDBColumn_WithInvalidColumn()
    {
        $column = "I'm $0 1NV@LiD!";
        $this->setExpectedException("gosException_Security");
        gosSanitizer::ensureDBColumn($column);
    }

    public function testEnsureDBColumn_WithTrickyColumn()
    {
        // If the regular expression isn't using /D, /^\w$/ would match this!
        $column = "iamValid
            haha just kidding!!!";
        $this->setExpectedException("gosException_Security");
        gosSanitizer::ensureDBColumn($column);
    }

    public function testEnsureDBTable_WithValidTableNameReturnsTableName()
    {
        $table = "every_thing";
        $result = gosSanitizer::ensureDBTable($table);
        $this->assertSame($table, $result);
    }

    public function testEnsureDBTable_ThrowsExcWithInvalidTableName()
    {
        $table = "bad table name OR 1 = 1";

        $this->setExpectedException('gosException_Security');
        gosSanitizer::ensureDBTable($table);
    }

    public function testEnsureDBTable_ThrowsExcWithTableNameWithNewLine()
    {
        // If the regular expression isn't using /D, /^\w$/ would match this!
        $table = "iamValid
            haha just kidding!!!";
        $this->setExpectedException('gosException_Security');
        gosSanitizer::ensureDBTable($table);
    }

    public function testEnsureDBSortColumns_ReturnsValueWithCapitalizedDirection()
    {
        $column = "a._valid ASC";
        $result = gosSanitizer::ensureDBSortColumns($column);
        $this->assertEquals($column, $result);
    }

    public function testEnsureDBSortColumns_ReturnsValueWithLowerCaseDirection()
    {
        $column = "a._valid desc";
        $result = gosSanitizer::ensureDBSortColumns($column);
        $this->assertEquals($column, $result);
    }

    public function testEnsureDBSortColumns_ThrowsExcWithEvilThings()
    {
        $column = "a._valid desc; evil things";
        $this->setExpectedException('gosException_Security', 'Invalid DB sort columns: "' . $column . '".');
        gosSanitizer::ensureDBSortColumns($column);
    }

    public function testEnsureDBSortColumns_ThrowsExcWhenDirectionOmitted()
    {
        $column = "column";
        $this->setExpectedException('gosException_Security', 'Invalid DB sort columns: "' . $column . '".');
        gosSanitizer::ensureDBSortColumns($column);
    }

    public function testEnsureDBSortColumns_ReturnsValueWithMultipleColumns()
    {
        $column = "a DESC, b asc, something._else ASC";
        $result = gosSanitizer::ensureDBSortColumns($column);
        $this->assertEquals($column, $result);
    }

    public function testEnsureInt_ReturnsPositiveIntegerGivenPositiveInteger()
    {
        $value = 2;
        $this->assertSame($value, gosSanitizer::ensureInt($value));
    }

    public function testEnsureInt_ReturnsNegativeIntegerGivenNegativeInteger()
    {
        $value = -2;
        $this->assertSame($value, gosSanitizer::ensureInt($value));
    }

    public function testEnsureInt_ReturnsZeroGivenZero()
    {
        $value = 0;
        $this->assertSame($value, gosSanitizer::ensureInt($value));
    }

    public function testEnsureInt_CastsToInteger()
    {
        $value = '2';
        $this->assertSame((int)$value, gosSanitizer::ensureInt($value));
    }

    public function testEnsureInt_ThrowsExceptionGivenFloat()
    {
        $value = -2.1;
        $this->setExpectedException('gosException_InvalidArgument', 'Integer expected, was <-2.1>');
        gosSanitizer::ensureInt($value);
    }

    public function testEnsureInt_ThrowsInvalidArgumentExceptionNonNumber()
    {
        $value = "two";
        $this->setExpectedException('gosException_InvalidArgument', 'Integer expected, was <two>');
        gosSanitizer::ensureInt($value);
    }

    public function testEnsurePosInt_ReturnsPositiveIntegerGivenPositiveInteger()
    {
        $value = 2;
        $this->assertSame($value, gosSanitizer::ensurePosInt($value));
    }

    public function testEnsurePosInt_CastsToInteger()
    {
        $value = '2';
        $this->assertSame((int)$value, gosSanitizer::ensurePosInt($value));
    }

    public function testEnsurePosInt_ThrowsInvalidArgumentExceptionGivenZero()
    {
        $value = 0;
        $this->setExpectedException('gosException_InvalidArgument', 'Positive integer expected, was <0>');
        $this->assertSame($value, gosSanitizer::ensurePosInt($value));
    }

    public function testEnsurePosInt_ThrowsInvalidArgumentExceptionGivenNegativeInt()
    {
        $value = -2;
        $this->setExpectedException('gosException_InvalidArgument', 'Positive integer expected, was <-2>');
        $this->assertSame($value, gosSanitizer::ensurePosInt($value));
    }

    public function testEnsurePosInt_ThrowsInvalidArgumentExceptionGivenNonPositiveNumber()
    {
        $value = -2.1;
        $this->setExpectedException('gosException_InvalidArgument', 'Positive integer expected, was <-2.1>');
        gosSanitizer::ensurePosInt($value);
    }

    public function testEnsurePosInt_ThrowsInvalidArgumentExceptionNonNumber()
    {
        $value = "two";
        $this->setExpectedException('gosException_InvalidArgument', 'Positive integer expected, was <two>');
        gosSanitizer::ensurePosInt($value);
    }

    public function testEnsureFloat_ReturnsPositiveFloatGivenPositiveFloat()
    {
        $value = 2.2;
        $this->assertSame($value, gosSanitizer::ensureFloat($value));
    }

    public function testEnsureFloat_CastsToFloat()
    {
        $value = '2.2';
        $this->assertSame((float)$value, gosSanitizer::ensureFloat($value));
    }

    public function testEnsureFloat_ReturnsZeroGivenZero()
    {
        $value = 0;
        $this->assertSame((float)$value, gosSanitizer::ensureFloat($value));
    }

    public function testEnsureFloat_ThrowsInvalidArgumentExceptionGivenNegativeInt()
    {
        $value = -2;
        $this->assertSame((float)$value, gosSanitizer::ensureFloat($value));
    }

    public function testEnsureFloat_ThrowsInvalidArgumentExceptionGivenNonPositiveNumber()
    {
        $value = -2.1;
        $this->assertSame((float)$value, gosSanitizer::ensureFloat($value));
    }

    public function testEnsureFloat_ThrowsInvalidArgumentExceptionNonNumber()
    {
        $value = "two";
        $this->setExpectedException('gosException_InvalidArgument', 'Float expected, was <two>');
        gosSanitizer::ensureFloat($value);
    }

    public function testEnsureDBBool_Returns0ForValuesLike0()
    {
        $value = 0;
        $result = gosSanitizer::ensureDBBool($value);
        $this->assertSame(0, $result);

        $value = false;
        $result = gosSanitizer::ensureDBBool($value);
        $this->assertSame(0, $result);

        $value = '0';
        $result = gosSanitizer::ensureDBBool($value);
        $this->assertSame(0, $result);
    }

    public function testEnsureDBBool_Returns1ForValuesLike1()
    {
        $value = 1;
        $result = gosSanitizer::ensureDBBool($value);
        $this->assertSame(1, $result);

        $value = true;
        $result = gosSanitizer::ensureDBBool($value);
        $this->assertSame(1, $result);

        $value = '1';
        $result = gosSanitizer::ensureDBBool($value);
        $this->assertSame(1, $result);
    }

    public function testEnsureDBBool_ThrowsExcGivenNonBool()
    {
        $value = '';
        $this->setExpectedException('gosException_InvalidArgument', 'DB bool expected, was <' . $value . '>.', get_defined_vars());
        gosSanitizer::ensureDBBool($value);
    }
}
