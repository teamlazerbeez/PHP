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
class gosUtility_String {
    /**
     * Recursively strip slashes in strings and arrays
     * @param string|array $value
     * @return string|array values after stripslashes
     **/
    public static function stripslashesDeep($value)
    {
        if (is_array($value))
        {
            $value = array_map('gosUtility_String::stripslashesDeep', $value);
        }
        else
        {
            $value = stripslashes($value);
        }
        return $value;
    }
}
