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

class gosDB_Mysqli extends gosDB_Base
{
    /**
     * @var int how long to wait between seeing if the connection is up, in
     * seconds
     */
    private static $secsBetweenConnPings = 300;

    /**
     * @var mysqli mysql connection
     */
    protected $conn;

    /**
     * @var bool
     */
    private $inTransaction = false;

    /**
     * @var string database name
     */
    private $dbName;

    /**
     * @var int the number of seconds since the epoch when the connection was
     * last pinged
     */
    private $timestampAtLastConnActivity;

    /**
     * @var array
     * update/insert/delete statements with an explicity declared transctions
     */
    private $sqlStatements = array();

    /**
     * Logging object
     */
    protected $logger;

    /**
     * Connection parameters
     */
    private $connParams;

    /**
     * @param gosDB_ConnParams parameter object
     * @security BH/AK 2009-10-19
     */
    public function __construct(gosDB_ConnParams $params)
    {
        $this->connParams = $params;

        $this->conn = new mysqli($this->connParams->getHost(), $params->getUser(), $params->getPassword(), $params->getDBName(), $params->getPort());
        $this->dbName = $this->connParams->getDBName();

        $this->timestampAtLastConnActivity = time();
    }

    protected function reconnect()
    {
        $this->conn = new mysqli($this->connParams->getHost(), $params->getUser(), $params->getPassword(), $params->getDBName(), $params->getPort());
        $this->timestampAtLastConnActivity = time();
    }

    public function setCharset($charset)
    {
        $this->conn->set_charset($charset);
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Close the database connection
     * @security BH/AK 2009-10-19
     */
    public function close()
    {
        if ($this->conn instanceof mysqli)
        {
            $this->conn->close();
        }
    }

    /**
     * @todo What is this used for?
     * @security BH/AK 2009-10-19
     */
    public function __get($attributeName)
    {
        return $this->conn->$attributeName;
    }

    /**
     * Execute passed sql query. Should be used for insert/update.  Throws gosException_DB
     * if query execution fails.
     * @param string $sql Properly-escaped SQL query to execute
     * @security BH/AK 2009-10-19
     */
    public function execute($sql)
    {
        return $this->queryError($sql);
    }

    /**
     * Execute passed sql query and return a 2D array containing all rows returned from
     * execution.  Throws gosException_DB if query execution fails.
     * @param string $sql Properly-escaped SQL query to execute
     * @return array 2D array containing one row per result row. Empty array if query returns no results.
     * @security BH/AK 2009-10-19
     */
    public function getAll($sql)
    {
        $resultSet = $this->queryError($sql);

        $this->assertNotBooleanResult($resultSet);

        // Iterate over all rows in the result set, creating a php array to return
        // @todo factor out into its own function/class? RAC 12/03/07
        $return = array();
        while ($row = $resultSet->fetch_assoc())
        {
            $return[] = $row;
            // @todo Can the row be unset from resultSet resource after being copied into return array? RAC 12/03/07
        }
        $resultSet->close();
        return $return;
    }

    /**
     * Execute passed sql query and return a 1D array containing column => value mapping.
     * Throws gosException_DB if query execution fails.
     * @param string $sql Sql query to execute
     * @return array 1D array containing column => value mapping for the first row returned
     * from query execution. empty array if query returns no results.
     * @security BH/AK 2009-10-19
     */
    public function getRow($sql)
    {
        $resultSet = $this->queryError($sql);

        $this->assertNotBooleanResult($resultSet);

        $return = $resultSet->fetch_assoc();
        // No data found, return array for backwards compatibility
        if ($return === null)
        {
            $return = array();
        }
        $resultSet->close();
        return $return;
    }

    /**
     * Execute passed sql query and return a single value matching the first column of the first row.
     * Throws gosException_DB if query execution fails.
     * @param string $sql Sql query to execute
     * @return string|false String representation of the first column in the first row matched. false if query returns no results.
     * @todo Should this thrown an exception if more than one row or more than one column within a row are found? RAC 12/03/07
     * @security BH/AK 2009-10-19
     */
    public function getOne($sql)
    {
        $resultSet = $this->queryError($sql);

        $this->assertNotBooleanResult($resultSet);

        // More than one row returned, throw exception
        if ($resultSet->num_rows > 1)
        {
            throw new gosException_InvalidArgument('query returned more than one row ('. $resultSet->num_rows .'): '. $sql, get_defined_vars());
        }

        $firstRow = $resultSet->fetch_array(MYSQLI_NUM);
        $resultSet->close();

        // More than one columns returned
        if (count($firstRow) > 1)
        {
            throw new gosException_InvalidArgument('query requested more than one column: '. $sql, get_defined_vars());
        }

        // No results found for query, return false
        if (!is_array($firstRow))
        {
            $return = false;
        }
        // Get the value out of the array and return it
        elseif (is_array($firstRow))
        {
            $return = $firstRow[0];
        }
        return $return;
    }

    /**
     * Returns all of the values from one column as a list.
     * @param string/SQL $sql
     * @param string $columnName
     * @return array/list
     */
    public function getColumnAsList($sql, $columnName)
    {
        $resultRows = $this->getAll($sql);
        $results = array();
        foreach ($resultRows as $row)
        {
            if (!isset($row[$columnName]))
            {
                throw new gosException_InvalidArgument('Column name "' . $columnName . '" does not exist in the result.', get_defined_vars());
            }
            $results[] = $row[$columnName];
        }
        return $results;
    }

    /**
     * Execute passed query.  Throws gosException_DB if query execution fails.
     * @param string $sql Sql query to execute
     * @return resource Database resource that can be stepped through
     * @todo Should this be private? RAC 12/03/07
     * @security BH/AK 2009-10-19
     */
    public function queryError($sql)
    {
        // Increment the number of queries made (across all DB connections)
        global $dbQueryNr;
        ++$dbQueryNr;
        $queryStartTime = microtime(true);

        try
        {
            $this->checkConnStatus();
        }
        catch (gosException_DB $e)
        {
            // Attempt to reconnect if server disappears
            // (2006 is CR_SERVER_GONE_ERROR)
            if ($e->getDBObject()->ErrorNo() == 2006)
            {
                $this->reconnect();
                $this->checkConnStatus();
            }
            else
            {
                throw $e;
            }
        }

        if ($this->isInTransaction())
        {
            $this->sqlStatements[] = $sql;
        }
        $resultSet = $this->queryImpl($sql);

        /**
         * Retry for deadlock and lock wait timeout:
         *  1213 => deadlock
         *  1205 => lock wait timeout
         */
        $mysqlerrno = $this->ErrorNo();
        if ($mysqlerrno == 1205 || $mysqlerrno == 1213 && !($this->isInTransaction()))
        {
            if ($this->logger) $this->logger->info('A database error <' . $mysqlerrno . '> that appears to be a deadlock has occurred, retrying query<' . $sql . '>.');
            sleep(3);
            $resultSet = $this->conn->queryImpl($sql);
        }

        if ($mysqlerrno == 1213 && $this->isInTransaction())
        {
            if ($this->logger) $logger->info('A database error <' . $mysqlerrno . '> that appears to be a deadlock has occurred in a transaction, retrying all queries.');
            sleep(3);
            foreach ($this->sqlStatements as $sql)
            {
                $resultSet = $this->conn->queryImpl($sql);
                if (!$resultSet)
                {
                    break;
                }
            }
        }

        // Throw exception if the query failed
        if (!$resultSet)
        {
            throw new gosException_DB('SQL Query failed: ' . $sql, get_defined_vars(), $sql, $this);
        }

        $this->timestampAtLastConnActivity = time();
        return $resultSet;
    }

    /**
     * Actually perform the query against our connection object.
     *
     * This method suitable to be overridden.
     */
    protected function queryImpl($sql)
    {
        return $this->conn->query($sql);
    }

    /**
     * Perform a query using the real_query() method
     */
    public function realQuery($sql)
    {
        return $this->conn->real_query($sql);
    }

    /**
     * Alias of the queryError function
     * @param string $sql Sql query to execute
     * @return resource Database resource that can be stepped through
     * @security BH/AK 2009-10-19
     */
     public function getResultSet($sql)
     {
        return $this->queryError($sql);
     }

    /**
     * Passthrough for mysqli prepare() method
     * @param  The query, as a string. This parameter can include one or more parameter markers
     * in the SQL statement by embedding question mark (?) characters at the appropriate positions.
     * @return mysqli_stmt Database statement to use for binding params, executing, and binding result
     * @security BH/AK 2009-10-19
     */
    public function prepare($query)
    {
        // Error occurred
        if (!($stmt = $this->conn->prepare($query)))
        {
            throw new gosException_DB('SQL Query failed', get_defined_vars(), $query, $this);
        }
        return $stmt;
    }

    /**
     * Begin a new db transaction.  Since mysql doesn't allow nested transactions,
     * throw an exception if an attempt to start a transaction is made while a previously
     * started transaction is still open.
     * @security BH/AK 2009-10-19
     */
    public function startTransaction()
    {
        if ($this->inTransaction === true)
        {
            throw new gosException_StateError('Cannot nest transactions in DB "' . $this->getDBName() . '".', get_defined_vars());
        }

        $this->conn->autocommit(false);
        $this->inTransaction = true;
        $this->sqlStatements = array();
    }

    /**
     * Commit and end the current transaction.
     * @todo Do we need a function that will commit the outstanding queries but not end the transaction?
     * @security BH/AK 2009-10-19
     */
    public function commitTransaction()
    {
        // Ensure that we are actually in a transaction
        if ($this->inTransaction === false)
        {
            throw new gosException_StateError('No transactions to commit in DB "' . $this->getDBName() . '".', get_defined_vars());
        }

        // Commits any outstanding queries and ends transaction
        $this->conn->commit();
        $this->conn->autocommit(true);
        $this->inTransaction = false;
        $this->sqlStatements = array();
    }

    /**
     * @security BH/AK 2009-10-19
     */
    public function rollbackTransaction()
    {
        //
        if ($this->inTransaction === false)
        {
            throw new gosException_StateError('No transactions to rollback in DB "' . $this->getDBName() . '".', get_defined_vars());
        }

        $this->conn->rollback();
        $this->conn->autocommit(true);
        $this->inTransaction = false;
        $this->sqlStatements = array();
    }

    /**
     * @security BH/AK 2009-10-19
     */
    public function isInTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * Prepare a value for being included in a db query by escaping characters that
     * could be used for sql injection or cause other weird behavior
     * @param mixed $unescapedValue Value that needs to be ecaped before being included in query.
     * @param string Properly escaped version of the value passed.
     * @security BH/AK 2009-10-19
     */
    public function escape($unescapedValue)
    {
        return $this->conn->escape_string($unescapedValue);
    }

    /**
     * Escape the string, respecting the connection's character encoding.
     * @param mixed $unescapedValue Value that needs to be ecaped before being included in query.
     * @param string Properly escaped version of the value passed.
     * @security BH/AK 2009-10-19
     */
    public function real_escape($unescapedValue)
    {
        return $this->conn->real_escape_string($unescapedValue);
    }

    /**
     * Return the auto-increment ID of the most recently inserted row by the db connection
     * included in this object.
     * @return int Auto-increment row ID of the most recently inserted row by the
     * private db connection.
     * @todo What happens if no insert has been made? What does insert_id() return? RAC 12/03/07
     * @security BH/AK 2009-10-19
     */
    public function insertID()
    {
        $insertID = mysqli_insert_id($this->conn);
        if ($insertID == 0)
        {
            throw new gosException_StateError('Last query did not insert/update a table with an auto-increment column', get_defined_vars());
        }
        return $insertID;
    }

    /**
     * Alias for insertID().
     * @security BH/AK 2009-10-19
     */
    public function insert_id()
    {
        return $this->insertID();
    }

    /**
     * @security BH/AK 2009-10-19
     */
    public function affectedRows()
    {
        return $this->conn->affected_rows;
    }

    /**
     * @security BH/AK 2009-10-19
     */
    public function matchedRows()
    {
        $info_str = $this->conn->info;
        gosRegex::match('/Rows matched: ([0-9]*)/', $info_str, $r_matched);
        return (int)$r_matched[1];
    }

    /**
     * @security BH/AK 2009-10-19
     */
    public function ErrorNo()
    {
        return $this->conn->errno;
    }

    /**
     * @return string (not escaped)
     * @security BH/AK 2009-10-19
     */
    public function ErrorMsg()
    {
        return $this->conn->error;
    }

    /**
     * @security BH/AK 2009-10-19
     */
    public function isConnected()
    {
        return $this->conn->ping();
    }

    /**
     * @return string (not escaped)
     * @security BH/AK 2009-10-19
     */
    public function getDBName()
    {
        return $this->dbName;
    }

    /**
     * If it's been long enough since the last check, see if the connection is
     * still alive. If it's not, try once more. If that fails, throw
     * gosException_DB.
     *
     * @throws gosException_DB
     * @security BH/AK 2009-10-19
     */
    private function checkConnStatus()
    {
        // see if enough time has elapsed that we should check again
        $current = time();

        if ($current - $this->timestampAtLastConnActivity < self::$secsBetweenConnPings)
        {
            return;
        }

        // try twice to get a good ping()
        for ($i = 0; $i < 2; $i++)
        {
            // It's been too long since our last ping, so test the connection
            $result = $this->conn->ping();

            // if no error, return
            if ($result && $this->conn->errno == 0)
            {
                $this->timestampAtLastConnActivity = $current;
                return;
            }
        }

        throw new gosException_DB('Could not ping the db', get_defined_vars(), '(ping)', $this);
    }

    /**
     * @param object|bool if the result is a bool, then the query was UPDATE,
     * INSERT, etc, and not a SELECT, so throw an exception
     * @throws gosException_InvalidArgument if it's a bool
     * @security BH/AK 2009-10-19
     */
    private function assertNotBooleanResult($resultSet)
    {
        if (is_bool($resultSet))
        {
            throw new gosException_InvalidArgument('You need to use a SELECT, etc, not UPDATE, INSERT, etc', get_defined_vars());
        }
    }

    /**
     * write the header for debug SQL output/display (says 'private')
     * @param string $routine - where the call was made
     * @param string $SQLQuery - the SQL call
     * @param string $parameterArray - SQL parameters, if any
     * @param string $comment - commentary if any
     * @param string $result - the result array returned from a SQL call
     * @param integer $seconds - number of seconds the call took
     * @return nothing - echos output
     * @security CL/MP 2009-10-23
     **/
    private function formatDebugMsg($SQLQuery, $result, $seconds)
    {
        global $dbQueryNr;
        $rows = $result instanceof mysqli_result ? $result->num_rows : '-';
        // if we have a slow query, call it out !
        if (((float)$seconds) > .5)
        {
            $seconds = '<span style="color: red; font-size: 120%;"><b>'. gosEncHTMLContent($seconds). '</b></span>';
        }
        ob_start();
        debug_print_backtrace();
        $backtraceOutput = ob_get_clean();

        $debugHTML = '';
        $debugHTML .= '
    <DIV style="background-color:#FFE1E1; margin: 7px 0px 7px 0px; padding: 7px 4px 7px 4px;">
        <div class="body">queryNr['. gosEncHTMLContent(1+$dbQueryNr) .', '. gosEncHTMLContent($seconds) .' s] <br>SQL: '. gosEncHTMLContent($SQLQuery) .'<br>
                    Result: ('. $rows .' rows) <span id="qDebug-'. $dbQueryNr .'-ShowHideToggle" onclick="showHideQDebug('. $dbQueryNr .');" style="font-face:bold;color:black;text-decoration:underline;cursor:hand;">Show/Hide</span>
            <div id="qDebug-'. $dbQueryNr .'-ResultDiv" style="display:none;">';
        if ($result === false)
        {
            $debugHTML .= '<span style="color: red;">'. gosEncHTMLContent($this->conn->ErrorMsg()) .'</span>';
        }
        $debugHTML .= '
                <TABLE BORDER="1" CELLPADDING="2" CELLSPACING="0" CLASS="body">';
        $printedHeaders = false;
        // Format all of the returned rows
        if ($result instanceof mysqli_result)
        {
            while ($row = $result->fetch_assoc())
            {
                // Add column headers if it's the first row
                if (!$printedHeaders)
                {
                    $debugHTML .= '<tr class="bodyBold">';
                    foreach ($row as $key => $value)
                    {
                        $debugHTML .= '<th>'. gosEncHTMLContent($key). '</th>';
                    }
                    $debugHTML .= '</tr>';
                    $printedHeaders = true;
                }

                // Format all of the columns in the current row
                $debugHTML .= '<tr>';
                foreach ($row as $key => $value)
                {
                    $d = gosEncHTMLContent($row[$key]);
                    $debugHTML .= '<td>'. ($d != '' ? $d : '&nbsp;'). '</td>';
                }
                $debugHTML .= '</tr>';
            }
            $result->data_seek(0);
        }
        $debugHTML .= '</TABLE></div>';
        $debugHTML .= '<span '. ($result === false ? 'style="color: red;"' : '') .'><pre>Call trace:
'.gosEncHTMLContent($backtraceOutput) .'
        </pre></span></div></div>';
        $debugHTML .= <<<ENT
<script>
showHideQDebug = function (queryNum) {
    var resultDiv = document.getElementById('qDebug-'+ queryNum +'-ResultDiv');
    resultDiv.style.display = resultDiv.style.display != 'none' ? 'none' : '' ;
}
</script>
ENT;
        return $debugHTML;
    }
}
