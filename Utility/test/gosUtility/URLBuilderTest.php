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

class gosUtility_URLBuilderTest extends gosTest_Framework_TestCase
{

    function setupExtension()
    {
    }

    function tearDownExtension()
    {
    }

    function testCreateForAbsoluteUrl_NoQueryParamsAllowed()
    {
        $url = 'http://foo.com/path1/path2?foo=bar';
        $this->setExpectedException('gosException_InvalidArgument', 'Cannot provide a query string; query params must be added afterwards to ensure correctness');
        gosUtility_URLBuilder::createForAbsoluteUrl($url);
    }

    function testToString_AbsoluteUrl()
    {
        $url = 'http://foo.com/path1/path2#asdf';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $this->assertEquals($url, $builder->toString());
    }

    public function testToString_AppRootUrlWithEncodedQueryParamsPrependsAppRoot()
    {
        $url = 'activities/emailReview.php?activityID=35606';
        $builder = gosUtility_URLBuilder::createForAppRootUrlWithEncodedQueryParams($url);

        $this->assertSame(gosUtility_URLBuilder::$applicationRoot . $url, $builder->toString());
    }

    function testToString_AddedQueryParams()
    {
        $url = 'http://foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $builder->setQueryParam('p2', 'v2&?');
        $builder->setQueryParam('p3', 'v3');

        $this->assertEquals($url . '?p2=v2%26%3F&p3=v3', $builder->toString());
    }

    function testToString_AddedQueryParamsViaArray()
    {
        $url = 'http://foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $builder->setQueryParamsFromArray(array('p2' => 'v2', 'p3' => 'v3'));

        $this->assertEquals($url . '?p2=v2&p3=v3', $builder->toString());
    }

    function testToString_AddedQueryParamsViaEncodedQueryString()
    {
        $url = 'http://foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $builder->setQueryParamsFromUrlEncodedQueryString('foo=%40bar&%26=%25');

        $this->assertEquals($url . '?foo=%40bar&%26=%25', $builder->toString());
    }

    function testToString_AddingQueryParamsMaintainsOrder()
    {
        $url = 'http://foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $builder->setQueryParam('p2', 'v2');
        $builder->setQueryParam('p3', 'v3');
        $builder->setQueryParam('p4', 'v4');

        $this->assertEquals($url . '?p2=v2&p3=v3&p4=v4', $builder->toString());
    }

    function testToString_OverrideQueryParams()
    {
        $url = 'http://foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $builder->setQueryParam('p1', 'v2');

        $this->assertEquals('http://foo.com/path1/path2?p1=v2', $builder->toString());
    }

    function testToString_EscapesParamValue()
    {
        $url = 'http://foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $builder->setQueryParam('p1', 'v@');

        $this->assertEquals('http://foo.com/path1/path2?p1=v%40', $builder->toString());
    }

    function testToString_EscapesPath()
    {
        $url = 'http://foo.com/pa@th1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $this->assertEquals('http://foo.com/pa%40th1/path2', $builder->toString());
    }

    function testToString_EscapesParamName()
    {
        $url = 'http://foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $builder->setQueryParam('@', 'v');

        $this->assertEquals('http://foo.com/path1/path2?%40=v', $builder->toString());
    }

    function testToString_AbsoluteUrlWithUserPass()
    {
        $url = 'http://user:pass@foo.com/path1/path2';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $this->assertEquals($url, $builder->toString());
    }

    function testToString_AbsoluteUrlWithFragment()
    {
        $url = 'http://foo.com/path1/path2#frag';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);

        $this->assertEquals($url, $builder->toString());
    }

    function testToString_AbsoluteUrlWithFragmentAndQuery()
    {
        $url = 'http://foo.com/path1/path2?p1=v1&p2=v2#frag';
        $builder = gosUtility_URLBuilder::createForAbsoluteUrlWithEncodedQueryParams($url);

        $this->assertEquals($url, $builder->toString());
    }

    function testToString_RelativeUrl()
    {
        $url = 'path1/path2';
        $builder = gosUtility_URLBuilder::createForAppRootUrl($url);

        $this->assertEquals(gosUtility_URLBuilder::$applicationRoot . $url, $builder->toString());
    }

    function testToString_ScriptInPath_SanitizesURL()
    {
        $unsafeString = 'http://user:password@host.com/path/<script>alert("TOYO!")</script>/foo.html#fragment';
        $actual = gosUtility_URLBuilder::createForAbsoluteUrl($unsafeString)->toString();
        $expected = 'http://user:password@host.com/path/%3Cscript%3Ealert%28%22TOYO%21%22%29%3C/script%3E/foo.html#fragment';
        $this->assertSame($expected, $actual);
    }
}
