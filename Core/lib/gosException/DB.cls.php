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
class gosException_DB extends gosException
{
    private $dbObject;
    private $query;

    /**
     * @param string $message Exception message explaining why the exception was thrown
     * @param array $errorContext Array containing all variables in the local scope context
     * @param string $query Query that caused the error
     * @param object $dbObject Database object that returned the error
     */
    public function __construct($message, $errorContext, $query, $dbObject)
    {
        $this->dbObject = $dbObject;
        $this->query = $query;

        $message .= "\n query: ". $query;
        if ($dbObject->ErrorNo() != 0)
        {
            $message .= "\n mysql error num: ". $dbObject->ErrorNo();
            $message .= "\n mysql error: ". $dbObject->ErrorMsg();
        }

        parent::__construct($message, $errorContext);
    }

    public function getDBObject()
    {
        return $this->DBObject;
    }

    public function getQuery()
    {
        return $this->query;
    }
}
