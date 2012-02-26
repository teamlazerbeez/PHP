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
 */

class gosDB_ConnParamsCollection
{
    /**
     * @var array List<gosDB_ConnParams> $params
     */
    protected $params = array();

    /**
     * @param string $dbIdentifier
     * @param gosDB_ConnParams $params
     * @throws gosException_StateError if params already set
     * @security BH/AK 2009-10-19
     */
    public function setParams($dbIdentifier, gosDB_ConnParams $params)
    {
        gosDB_Util::validateDBIdentifier($dbIdentifier);

        if (isset($this->params[$dbIdentifier]))
        {
            throw new gosException_StateError('Params already set for dbIdentifier "'. $dbIdentifier .'"', get_defined_vars());
        }

        $this->params[$dbIdentifier] = $params;
    }

    /**
     * @param string $dbIdentifier
     * @return gosDB_ConnParams
     * @throws gosException_StateError if params not yet set
     * @security BH/AK 2009-10-19
     */
    public function getParams($dbIdentifier)
    {
        gosDB_Util::validateDBIdentifier($dbIdentifier);

        if (!isset($this->params[$dbIdentifier]))
        {
            throw new gosException_StateError('Params not yet set for dbIdentifier "'. $dbIdentifier .'"', get_defined_vars());
        }

        return $this->params[$dbIdentifier];
    }

    /**
     * @return array
     */
    public function getDBs()
    {
        $dbs = array_keys($this->params);
        return $dbs;
    }
}
?>
