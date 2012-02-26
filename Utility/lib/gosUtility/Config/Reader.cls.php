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
class gosUtility_Config_Reader
{
    /**
     * @var array config file locations
     */
    protected static $configFileStack = array ();

    /**
     * @var array cached config information
     */
    protected static $configCache = array();

    /**
     * @param string $entryName config entry to get
     * @return mixed the value at the entry name
     */
    public static function getConfigEntry($entryName)
    {
        self :: checkEntryExists($entryName);

        return self :: $configCache[$entryName];
    }

    /**
     * @param string $fileName
     */
    public static function addConfigFile($fileName)
    {
        self::$configFileStack[] = $fileName;

        $parsedYaml = gosUtility_YamlLoader::loadFile($fileName, false);

        if ($parsedYaml == null)
        {
            return;
        }

        # merge the new stuff in
        foreach ($parsedYaml as $name => $value)
        {
            self::$configCache[$name] = $value;
        }
    }

    /**
     * Throws an exception if the config entry doesn't exist
     * @param string $entryName the entry name to check
     */
    protected static function checkEntryExists($entryName)
    {
        if (!array_key_exists($entryName, self :: $configCache))
        {
            $functionContext = get_defined_vars();
            throw new gosException_InvalidArgument('Config entry \'' . $entryName . '\' does not exist', $functionContext);
        }
    }
}
