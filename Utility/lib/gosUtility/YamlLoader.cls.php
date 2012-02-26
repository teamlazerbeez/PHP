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
class gosUtility_YamlLoader
{
    /**
     * Load yaml file as associative array
     * @param string $fileName
     * @param boolean $faultOnEmpty Throws error on empty yaml file
     * @return array
     */
    public static function loadFile($fileName, $faultOnEmpty)
    {
        // ensure file exists
        self::checkFileNameExists($fileName);

        $contents = file_get_contents($fileName);

        if ($contents === FALSE)
        {
            $functionContext = get_defined_vars();
            throw new gosException_RuntimeError('Could not read ' . $fileName, $functionContext);
        }

        $parsedYaml = syck_load($contents);

        // Handle situation of invalid yaml
        if (!is_array($parsedYaml))
        {
            if($faultOnEmpty)
            {
                $functionContext = get_defined_vars();
                throw new gosException_RuntimeError($fileName . ' was empty!', $functionContext);
            }

            return null;
        }

        return $parsedYaml;
    }

    /**
     * Throws an exception if the file doesn't exist
     * @param string $fileName the filename to check
     */
    private static function checkFileNameExists($fileName)
    {
        if (!file_exists($fileName))
        {
            $functionContext = get_defined_vars();
            throw new gosException_InvalidArgument('File ' . $fileName . ' does not exist', $functionContext);
        }
    }
}
