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
class gosSafe_Gen_DB extends gosSafe_Gen_Base
{
    /**
     * @param string $templateString
     * @return self
     */
    public static function getGenerator($templateString)
    {
        return new self($templateString);
    }

    /**
     * @param string $templateString
     * @param array/map $replacements
     * @return gosSafe_DB
     */
    public static function getSafe($templateString, array $replacements = array())
    {
        $template = new self($templateString);
        return $template->replace($replacements);
    }

    /**
     * Returns a comma-separated list of integers as a safe object.
     * @param array/list [<int>] $ints
     * @return gosSafe_DB
     */
    public static function getSafeFromIntList(array $ints)
    {
        foreach ($ints as &$int)
        {
            $int = gosSanitizer::ensureInt($int);
        }
        return new gosSafe_DB(implode(',', $ints));
    }

    /**
     * Stitch together a series of safe objects
     *
     * @param string $glue
     * @param Array <gosSafe_Base> $safeArray
     * @return gosSafe_DB
     */
    public static function safeImplode($glue, array $safeArray)
    {
        $safeStringArray = array();
        foreach($safeArray as $safe)
        {
            if ($safe instanceof gosSafe_DB)
            {
                $safeStringArray[] = (string)$safe;
            }
            else
            {
                throw new gosException_InvalidArgument('Array contains non gosSafe objects.', get_defined_vars());
            }
        }
        return self::getSafe(implode($glue, $safeStringArray));
    }

    /**
     * Create safe strings for each set of replacements and glue them all
     * together to create a safe object
     *
     * @param string $glue
     * @param array of array/map $replacements {<string>varToReplace: <mixed>value}
     * @return gosSafe_DB
     */
    public function safeReplaceAndImplode($glue, array $replacementsSet)
    {
        $safeStringArray = array();
        foreach($replacementsSet as $replacements)
        {
            $safeStringArray[] = $this->replace($replacements);
        }

        return self::getSafe(implode($glue, $safeStringArray));
    }

    /**
     * @see gosSafe_Gen_Base::getAllowedContexts()
     */
    protected function getAllowedContexts()
    {
        return array('db', 'dbSafeObj', 'dbSortColumns', 'dbColumn', 'f', 'i', 'pi', 'dbBool', 'dbTable', 'dbLikeClause');
    }

    /**
     * @see gosSafe_Gen_Base::getSafeObjType()
     */
    protected function getSafeObjType()
    {
        return 'gosSafe_DB';
    }

}
