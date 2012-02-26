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
abstract class gosSafe_Base
{
    /**
     * @var string $value The string, safe for this object's context
     */
    protected $value;

    /**
     * These objects should ONLY be created by gosSafe_Gen_*.  There is no way,
     * however, to enforce this in PHP.  So please, be mindful.
     *
     * @param $value @see self::$value
     * @return self
     */
    public final function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string @see self::$value
     */
    public final function __tostring()
    {
        return $this->value;
    }
}
