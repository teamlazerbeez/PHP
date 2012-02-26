<?php
class gosUtility_Config_ReaderProxy extends gosUtility_Config_Reader
{
    /**
     * THIS IS FOR DEBUGGING AND TESTING ONLY.
     * @return array
     * @ignore
     */
    public static function getConfigFileStack()
    {
        return self::$configFileStack;
    }
    
    public static function reset()
    {
        self::$configFileStack = array();
        self::$configCache = array();  
    }

    /**
     *
     * @param string $key the property key to replace
     * @param string $value the new value to set
     */
    public static function overrideConfigValue($key, $value)
    {
        self::$configCache[$key] = $value;
    }
}
