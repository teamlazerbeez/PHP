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
 * @author Alex Korn <alex.e.korn@gmail.com>
 */
abstract class gosSafe_Gen_Base
{
    /**
     * The source template string that this generator will use.
     * Replacements are in the following format:
     * {contextIdentifier(s):variableName}
     * where multiple contextIdentifier's are separated by ':'.
     * e.g. {hc:name} means to insert variable 'name' escaped as HTML content
     * e.g. {ha:hc:qtip} means to insert variable 'qtip' escaped first as
     *      HTML content and then as an HTML attribute (think of it as
     *      functions that are executed from right to left).
     *
     * When creating strings to be passed as a templateString, never insert
     * variables directly; use replacements instead.
     * e.g. BAD '<a href="#">' . $name . '</a>'
     * e.g. GOOD '<a href="#">{hc:name}</a>', array('name' => $name)
     *
     * @var string/unescaped $templateString
     */
    protected $templateString;

    /**
     * The array of matches, as returned from gosRegex::match_all()
     * @var array $matches
     */
    protected $matches;

    /**
     * All available escaping contexts and what function to run.
     * Do not change this in the code (it should be a constant, but should
     * also be protected).
     *
     * @var array/map $escapingContexts {<string>context signifier:
     *                                  <string> name of fn to run}
     */
    protected $escapingContexts = array(
        'dbColumn' => 'gosSanitizer::ensureDBColumn',
        'dbSortColumns' => 'gosSanitizer::ensureDBSortColumn',
        'dbTable' => 'gosSanitizer::ensureDBTable',
        'absurl' => 'gosSanitizer::sanitizeForAbsoluteURL',
        'ha' => 'gosSanitizer::sanitizeForHTMLAttribute',
        'hc' => 'gosSanitizer::sanitizeForHTMLContent',
        'js' => 'gosSanitizer::sanitizeForJS',
        'url' => 'gosSanitizer::sanitizeForURLComponent',
        'db' => 'gosSanitizer::sanitizeForDB',

        'hcSafeObj' => 'ensureHCSafeObj',
        'urlBuilderObj' => 'ensureURLBuilderObj',
        'dbSafeObj' => 'ensureDBSafeObj',

        'dbBool' => 'gosSanitizer::ensureDBBool',
        'i' => 'gosSanitizer::ensureInt',
        'pi' => 'gosSanitizer::ensurePosInt',
        'f' => 'gosSanitizer::ensureFloat',

        //'dbLikeClause' => 'gosEncDBLikeClause',
    );

    /**
     * Force static creation methods
     *
     * @param $templateString @see self::$templateString
     * @return self
     */
    protected final function __construct($templateString)
    {
        $this->templateString = $templateString;
        // optimization: make the list of matches right now so that we don't
        // need to reconstruct it every time we run replace()
        gosRegex::match_all('/\{([\w|:]+)\}/', $this->templateString, $this->matches, PREG_SET_ORDER);
    }

    /**
     * @param array/map $replacements {<string>varToReplace: <mixed>value}
     * @return gosSafe_Base
     */
    public final function replace(array $replacements)
    {
        $source = $this->templateString;
        /*
         * optimization: store which pairs of contexts/variables we
         * have already replaced and ignore them when that pair has already
         * been replaced
         */
        $replacedPairs = array();
        foreach ($this->matches as $match)
        {
            $pair = $match[0];
            if (isset($replacedPairs[$pair]))
            {
                continue;
            }
            // get the variable and signifiers data out of the string
            $splitItems = explode(':', $match[1]);
            $variableToReplace = array_pop($splitItems);
            $signifiers = array_reverse($splitItems);

            // make sure the variable is valid
            if (!array_key_exists($variableToReplace, $replacements))
            {
                throw new gosException_InvalidArgument('Trying to replace key "' . $variableToReplace . '", which is not present in the list of replacements.', get_defined_vars());
            }

            if (count($signifiers) < 1)
            {
                throw new gosException_InvalidArgument('No signifier given', get_defined_vars());
            }

            // escape the variable
            $variableToEscape = $replacements[$variableToReplace];
            foreach ($signifiers as $signifier)
            {
                $escapingFunction = $this->getEscapingFunctionForContextSignifier($signifier);
                try
                {
                    $variableToEscape = call_user_func($escapingFunction, $variableToEscape);
                }
                catch (gosException_InvalidArgument $e)
                {
                    throw new gosException_InvalidArgument('Exception with item "' . $variableToReplace . '": ' . $e->getMessage(), $e->getContextVariables(), $e);
                }

            }
            // and replace
            $source = str_replace($pair, $variableToEscape, $source);

            $replacedPairs[$pair] = true;
        }
        $safeObjType = $this->getSafeObjType();
        return new $safeObjType($source);
    }

    /**
     * Returns the escaping function for a context signifier, checking that it
     * is a valid signifier for a context that's valid for this type of string
     * generator.
     *
     * Technically, this should be private, but I'm making it protected so that
     * we can test it directly.
     *
     * @param string $signifier
     * @return string The name of a function
     */
    protected final function getEscapingFunctionForContextSignifier($signifier)
    {
        if (!isset($this->escapingContexts[$signifier]))
        {
            throw new gosException_InvalidArgument('Context "' . $signifier . '" is not one of the known contexts.', get_defined_vars());
        }
        if (!in_array($signifier, $this->getAllowedContexts()))
        {
            throw new gosException_InvalidArgument('Context "' . $signifier . '" is not among the allowed escaping contexts for this safe string generator.', get_defined_vars());
        }
        return $this->escapingContexts[$signifier];
    }

    /**
     * Returns the list of all available escaping contexts available for this
     * type of safe string.
     *
     * @return array/list [<string>context signifier]
     */
    abstract protected function getAllowedContexts();

    /**
     * @return string The name of the gosSafe_* object that this generator will create.
     */
    abstract protected function getSafeObjType();

    /**
     * @param mixed $val
     * @return gosSafe_DB
     */
    protected static function ensureDBSafeObj($val)
    {
        if (!($val instanceof gosSafe_DB))
        {
            throw new gosException_InvalidArgument('Value is not a DB safe obj.', get_defined_vars());
        }
        return $val;
    }

    /**
     * @param mixed $val
     * @return gosSafe_HC
     */
    protected static function ensureHCSafeObj($val)
    {
        if (!($val instanceof gosSafe_HC))
        {
            throw new gosException_InvalidArgument('Value is not a HC safe obj.', get_defined_vars());
        }
        return $val;
    }

    /**
     *
     * @param <type> $urlBuilder
     * @return gosUtility_URLBuilder
     */
    protected static function ensureURLBuilderObj($urlBuilder)
    {
        if(!($urlBuilder instanceof gosUtility_URLBuilder))
        {
            throw new gosException_InvalidArgument('Value is not a URL Builder obj.', get_defined_vars());
        }
        return $urlBuilder;
    }
}
