<?php
/**
 * log5php is a PHP port of the log4j java logging package.
 * 
 * <p>This framework is based on log4j (see {@link http://jakarta.apache.org/log4j log4j} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by log4j team 
 * (Ceki G�lc� as log4j project founder and 
 * {@link http://jakarta.apache.org/log4j/docs/contributors.html contributors}).</p>
 *
 * <p>PHP port, extensions and modifications by VxR. All rights reserved.<br>
 * For more information, please see {@link http://www.vxr.it/log4php/}.</p>
 *
 * <p>This software is published under the terms of the LGPL License
 * a copy of which has been included with this distribution in the LICENSE file.</p>
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 */

/**
 * @ignore 
 */

/**
 * Appends log events to a db table using PEAR::DB class.
 *
 * <p>This appender uses a table in a database to log events.</p>
 * <p>Parameters are {@link $dsn}, {@link $createTable}, {@link table} and {@link $sql}.</p>
 * <p>See examples in test directory.</p>
 *
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Appender
 * @since 0.3
 */
class Log5PHP_Appender_Db extends Log5PHP_Appender_Base {

    /**
     * Create the log table if it does not exists (optional).
     * @var boolean
     */
    private $createTable = true;
    
    /**
     * PEAR::Db Data source name. Read PEAR::Db for dsn syntax (mandatory).
     * @var string
     */
    private $dsn;
    
    /**
     * A {@link Log5PHP_PatternLayout} string used to format a valid insert query (mandatory).
     * @var string
     */
    private $sql;
    
    /**
     * Table name to write events. Used only if {@link $createTable} is true.
     * @var string
     */    
    private $table;
    
    /**
     * @var object PEAR::Db instance
     */
    private $db = null;
    
    /**
     * @var boolean used to check if all conditions to append are true
     */
    private $canAppend = true;
    
    /**    
     */
    protected $requiresLayout = false;
    
    /**
     * Setup db connection.
     * Based on defined options, this method connects to db defined in {@link $dsn}
     * and creates a {@link $table} table if {@link $createTable} is true.
     * @return boolean true if all ok.
     */
    function activateOptions()
    {
        $this->db = DB::connect($this->dsn);

        if (DB::isError($this->db)) {
            Log5PHP_InternalLog::debug("Log5PHP_Appender_Db::activateOptions() DB Connect Error [".$this->db->getMessage()."]");            
            $this->db = null;
            $this->canAppend = false;

        } else {
        
            $this->layout = Log5PHP_Factory_Layout :: getNewLayout('Log5PHP_PatternLayout');
            $this->layout->setConversionPattern($this->getSql());
        
            // test if log table exists
            $tableInfo = $this->db->tableInfo($this->table, $mode = null);
            if (DB::isError($tableInfo) and $this->getCreateTable()) {
                $query = "CREATE TABLE {$this->table} (timestamp varchar(32),logger varchar(32),level varchar(32),message varchar(64),thread varchar(32),file varchar(64),line varchar(4) );";

                Log5PHP_InternalLog::debug("Log5PHP_Appender_Db::activateOptions() creating table '{$this->table}'... using sql='$query'");
                         
                $result = $this->db->query($query);
                if (DB::isError($result)) {
                    Log5PHP_InternalLog::debug("Log5PHP_Appender_Db::activateOptions() error while creating '{$this->table}'. Error is ".$result->getMessage());
                    $this->canAppend = false;
                    return;
                }
            }
            $this->canAppend = true;            
        }

    }
    
    protected function append(Log5PHP_LogEvent $event)
    {
        if ($this->canAppend) {

            $query = $this->layout->format($event);

            Log5PHP_InternalLog::debug("Log5PHP_Appender_Db::append() query='$query'");

            $this->db->query($query);
        }
    }
    
    function close()
    {
        if ($this->db !== null)
            $this->db->disconnect();
    }
    
    /**
     * @return boolean
     */
    function getCreateTable()
    {
        return $this->createTable;
    }
    
    /**
     * @return string the defined dsn
     */
    function getDsn()
    {
        return $this->dsn;
    }
    
    /**
     * @return string the sql pattern string
     */
    function getSql()
    {
        return $this->sql;
    }
    
    /**
     * @return string the table name to create
     */
    function getTable()
    {
        return $this->table;
    }
    
    function setCreateTable($flag)
    {
        $this->createTable = Log5PHP_Utility_OptionConverter::toBoolean($flag, true);
    }
    
    function setDsn($newDsn)
    {
        $this->dsn = $newDsn;
    }
    
    function setSql($sql)
    {
        $this->sql = $sql;    
    }
    
    function setTable($table)
    {
        $this->table = $table;
    }
    
}

