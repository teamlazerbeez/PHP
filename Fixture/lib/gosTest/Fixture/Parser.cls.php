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
class gosTest_Fixture_Parser
{
    /**
     * @var string SPECIAL_VALUE_REGEX the RegEx used to find special values.
     */
    const SPECIAL_VALUE_REGEX = '/.*?<<(.*?)>>.*/';

    /**
     * @var string SPECIAL_VALUE_REGEX the RegEx used to find special values.
     */
    const SPECIAL_VALUE_REPLACE_REGEX = '/<<.*?>>/';

    /**
     * @var string DB_SEPARATOR The separator used to separate the name of the
     *      DB from the rest of the identifier when accessing data from a
     *      different DB
     */
    const DB_SEPARATOR = '/';

    /**
     * @var string  SF_STRING   The string that values which should be replaced
     *                          with SF values start with.
     */
    const SF_REGEX = '/^randStatic.(sfLogin|sfUserID|sfPassword|sfOrganizationID)$/';

    /**
     * @param gosTest_Fixture_Controller $controller The fixture controller that this row is going to be a part of to use for special value references
     * @param string $fixtureIdentifier Name of the current fixture to use to replace "<<this." with "<<{$fixtureIdentifier}."
     * @param array $tableRow Column => Value pairs that are going to be inserted into the db and might contain special values to parse
     * @return array Passed column => value pairs with special values replaced by referenced values
     */
    public static function parse(gosTest_Fixture_Wrapper $wrapper, $fixtureIdentifier, array $tableRow)
    {
        $rawColumnMap = array();
        // Iterate over every column and process special values (like <<auto>>)
        // ...Store sql escaped values into a different array than the raw values
        foreach ($tableRow as $colName => $colValue)
        {
            // If the value is <<.*>> then it's a special value that needs to be parsed
            while (self::hasSpecialValue($colValue) && $colValue != '<<auto>>')
            {
                // Remove the << >> that surrounds the value
                $specialValue = self::getFixtureReference($colValue);

                // A Fixture reference that starts with "this." is shorthand for referring to the current fixture,
                // ...replace the shorthand with the current fixture name to properly expand and follow
                if (strpos($specialValue, 'this.') === 0)
                {
                    // Strip 'this' off and replace with the name of the current fixture
                    $specialValue = $fixtureIdentifier . substr($specialValue, 4);
                }

                if (strpos($specialValue, self::DB_SEPARATOR) !== false)
                {
                    // we're trying to get value from a different DB
                    list($dbName, $specialValue) = explode(self::DB_SEPARATOR, $specialValue);
                    $fixture = gosTest_Fixture_Controller::getByDBName($dbName);
                    $specialValue = $fixture->get($specialValue);
                }
                elseif (gosRegex::match(self::SF_REGEX, $specialValue) == 1)
                {
                    $parts = explode('.', $specialValue);
                    $specialValue = gosTest_Fixture_Salesforce::getSFValue($parts[1]);
                }
                else
                {
                    // otherwise, get from this wrapper
                    $specialValue = $wrapper->get($specialValue);
                }

                $colValue = self::replaceFixtureReference($colValue, $specialValue);
            }
            $rawColumnMap[$colName] = $colValue;
        }

        return $rawColumnMap;
    }

    /**
     * Values surrounded by << and >> are special values that need to be handled differently
     * than literal values
     *
     * @param string $rowValue Value to check for special meaning
     * @return bool True if it needs to handled specially, false if it's a literal
     */
    protected static function isSpecialValue($rowValue)
    {
        return substr($rowValue, 0, 2) == '<<' && substr($rowValue, -2) == '>>';
    }

    /**
     * Same as above, but checks for special values within the rowValue
     *
     * @param string $rowValue Value to check for special meaning
     * @return bool True if it needs to handled specially, false if it's a literal
     */
    protected static function hasSpecialValue($rowValue)
    {
        return (gosRegex::match(self::SPECIAL_VALUE_REGEX, $rowValue) == 1);
        //return substr($rowValue, 0, 2) == '<<' && substr($rowValue, -2) == '>>';
    }

    /**
     * Returns the fixture referenced in a rowValue.  Assumes that there is a
     * valid fixture reference within $rowValue (i.e. MUST be called after
     * hasSpecialValue()).
     * @param string $rowValue
     * @return string
     */
    protected static function getFixtureReference($rowValue)
    {
        gosRegex::match(self::SPECIAL_VALUE_REGEX, $rowValue, $matches);
        return $matches[1];
    }

    /**
     * @param string $rowValue
     * @param string $replacement
     * @return string
     */
    protected static function replaceFixtureReference($rowValue, $replacement)
    {
        /*
         * if the whole rowValue is the special value, then we should just
         * return the value instead of gosRegex::replace()ing it.  This check
         * ensures that we keep numerics as numeric.  If we're replacing only
         * part of the rowValue, then gosRegex::replace() is fine because that will
         * always be a string
         */
        if (self::isSpecialValue($rowValue))
        {
            return $replacement;
        }
        return gosRegex::replace(self::SPECIAL_VALUE_REPLACE_REGEX, $replacement, $rowValue, 1);
    }

    /**
     * Ensures that a certain kind of identifier is acceptable to be used in
     * fixtures.  This is currently used because we reserve '/' to be used
     * to access a fixture from a different database.
     *
     * @param string $type e.g. 'Fixture', 'Table', 'Row', 'Column name'
     * @param string $identifier
     * @throws gosException_InvalidArgument
     */
    public static function assertAcceptableIdentifier($type, $identifier)
    {
        // make sure the entryIdentifier doesn't contain the DB separator
        if (strpos($identifier, self::DB_SEPARATOR) !== false)
        {
            throw new gosException_InvalidArgument($type .' identifier "'. $identifier .'" contains the DB separator character "'. self::DB_SEPARATOR .'".', get_defined_vars());
        }
    }

}
