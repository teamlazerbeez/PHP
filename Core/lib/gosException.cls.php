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

class gosException extends Exception
{
    /**
     * @var array $errorContext
     */
    protected $errorContext;

    /**
     * Overwrite the constructor for the php exception class to take an exception
     * message and an array of variables in context instead of a message and an
     * error code
     * @param string $message Exception message explaining why the exception was thrown
     * @param array $errorContext Array containing all variables in the local scope context
     * when the exception was thrown, such as that returned by
     * get_defined_vars()
     */
    public function __construct($message, $errorContext)
    {
        $this->errorContext = $errorContext;
        parent::__construct($message);
    }

    /**
     * Get the array of the variables that were in context when the exception was thrown
     * @return array Array containing all variables in the local scope context
     * when the exception was thrown
     */
    public final function getContextVariables()
    {
        return $this->errorContext;
    }
}
?>
