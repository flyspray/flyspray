<?php
/**
 * Flyspray
 *
 * Database class class
 *
 * This class is a wrapper for ADOdb functions.
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray
 * @author Tony Collins
 * @author Cristian Rodriguez
 */

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

class Database
{
    /**
     * Table prefix, usually flyspray_
     * @var string
     * @access private
     */
    public $dbprefix;

    /**
     * Cache for queries done by cached_query()
     * @var array
     * @access private
     * @see cached_query();
     */
    private $cache = array();

    /**
     * dblink
     * adodb handler object
     * @var object
     * @access public
     */
    public $dblink = null;

    /**
     * Open a connection to the database quickly
     * @param array $conf connection data
     * @return void
     */
    public function dbOpenFast($conf)
    {
        if(!is_array($conf) || extract($conf, EXTR_REFS|EXTR_SKIP) < 5) {

            die( 'Flyspray was unable to connect to the database. '
                 .'Check your settings in flyspray.conf.php');
        }

       $this->dbOpen($dbhost, $dbuser, $dbpass, $dbname, $dbtype, isset($dbprefix) ? $dbprefix : '');
    }

    /**
     * Open a connection to the database and set connection parameters
     * @param string $dbhost hostname where the database server uses
     * @param string $dbuser username to connect to the database
     * @param string $dbpass password to connect to the database
     * @param string $dbname
     * @param string $dbtype database driver to use, currently :
     *  "mysql", "mysqli","pdo_mysql" "pgsql", "pdo_pgsql" should work correctly.
     * @param string $dbprefix database prefix.
     */
    public function dbOpen($dbhost = '', $dbuser = '', $dbpass = '', $dbname = '', $dbtype = '', $dbprefix = '')
    {

        $this->dbtype   = $dbtype;
        $this->dbprefix = $dbprefix;
        $ADODB_COUNTRECS = false;
        
        $this->dblink = NewADOConnection($this->dbtype);
        $this->dblink->Connect($dbhost, $dbuser, $dbpass, $dbname);

        if ($this->dblink === false || (!empty($this->dbprefix) && !preg_match('/^[a-z][a-z0-9_]+$/i', $this->dbprefix))) {

            die('Flyspray was unable to connect to the database. '
               .'Check your settings in flyspray.conf.php');
        }
            $this->dblink->SetFetchMode(ADODB_FETCH_BOTH);

            /*
             * this will work only in the following systems/PHP versions
             *
             * PHP4 and 5 with postgresql
             * PHP5 with "mysqli" or "pdo_mysql" driver (not "mysql" driver)
             * using mysql 4.1.11 or later and mysql 5.0.6 or later.
             *
             * in the rest of the world, it will silently return FALSE.
             */

            $this->dblink->SetCharSet('utf8');

            //enable debug if constact DEBUG_SQL is defined.
            !defined('DEBUG_SQL') || $this->dblink->debug = true;
            
            if($dbtype === 'mysql' || $dbtype === 'mysqli') {
                $dbinfo = $this->dblink->ServerInfo();
                if(isset($dbinfo['version']) && version_compare($dbinfo['version'], '5.0.2', '>=')) {
                    $this->dblink->Execute("SET SESSION SQL_MODE='TRADITIONAL'");
                }
            }
    }

    /**
     * Closes the database connection
     * @return void
     */
    public function dbClose()
    {
        $this->dblink->Close();
    }

    /**
     * CountRows
     * Returns the number of rows in a result
     * @param object $result
     * @access public
     * @return int
     */
    public function CountRows(&$result)
    {
        return (int) $result->RecordCount();
    }

    /**
     * AffectedRows
     *
     * @access public
     * @return int
     */
    public function AffectedRows()
    {
        return (int) $this->dblink->Affected_Rows();
    }

    /**
     * FetchRow
     *
     * @param & $result
     * @access public
     * @return void
     */

    public function FetchRow(&$result)
    {
        return $result->FetchRow();
    }

    /**
     * fetchCol
     *
     * @param & $result
     * @param int $col
     * @access public
     * @return void
     */

    public function fetchCol(&$result, $col=0)
    {
        $tab = array();
        while ($tmp = $result->fetchRow()) {
            $tab[] = $tmp[$col];
        }
        return $tab;
    }

    /**
     * Query
     *
     * @param mixed $sql
     * @param mixed $inputarr
     * @param mixed $numrows
     * @param mixed $offset
     * @access public
     * @return void
     */

    public function Query($sql, $inputarr = false, $numrows = -1, $offset = -1)
    {
        // auto add $dbprefix where we have {table}
        $sql = $this->_add_prefix($sql);
        // remove conversions for MySQL
        if (strcasecmp($this->dbtype, 'pgsql') != 0) {
            $sql = str_replace('::int', '', $sql);
            $sql = str_replace('::text', '', $sql);
        }

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

        if (($numrows >= 0 ) or ($offset >= 0 )) {
            /* adodb drivers are inconsisent with the casting of $numrows and $offset so WE
             * cast to integer here anyway */
            $result =  $this->dblink->SelectLimit($sql, (int) $numrows, (int) $offset, $inputarr);
        } else {
           $result =  $this->dblink->Execute($sql, $inputarr);
        }

        if (!$result) {

            if (function_exists("debug_backtrace") && defined('DEBUG_SQL')) {
                echo "<pre style='text-align: left;'>";
                var_dump(debug_backtrace());
                echo "</pre>";
            }

            $query_params = '';

            if(is_array($inputarr) && count($inputarr)) {

                $query_params =  implode(',', array_map(array('Filters','noXSS'), $inputarr));

            }

            die (sprintf("Query {%s} with params {%s} Failed! (%s)",
                    Filters::noXSS($sql), $query_params, Filters::noXSS($this->dblink->ErrorMsg())));
        }


        return $result;
    }

    /**
     * cached_query
     *
     * @param mixed $idx
     * @param mixed $sql
     * @param array $sqlargs
     * @access public
     * @return array
     */
    public function cached_query($idx, $sql, $sqlargs = array())
    {
        if (isset($this->cache[$idx])) {
            return $this->cache[$idx];
        }

        $sql = $this->Query($sql, $sqlargs);
        return ($this->cache[$idx] = $this->fetchAllArray($sql));
    }

    /**
     * FetchOne
     *
     * @param & $result
     * @access public
     * @return array
     */
    public function FetchOne(&$result)
    {
        $row = $this->FetchRow($result);
        return (count($row) ? $row[0] : '');
    }

    /**
     * FetchAllArray
     *
     * @param & $result
     * @access public
     * @return array
     */
    public function FetchAllArray(&$result)
    {
        return $result->GetArray();
    }

    /**
     * GroupBy
     *
     * This groups a result by a single column the way
     * MySQL would do it. Postgre doesn't like the queries MySQL needs.
     *
     * @param object $result
     * @param string $column
     * @access public
     * @return array process the returned array with foreach ($return as $row) {}
     */
    public function GroupBy(&$result, $column)
    {
        $rows = array();
        while ($row = $this->FetchRow($result)) {
            $rows[$row[$column]] = $row;
        }
        return array_values($rows);
    }

    /**
     * GetColumnNames
     *
     * @param mixed $table
     * @param mixed $alt
     * @param mixed $prefix
     * @access public
     * @return void
     */

    public function GetColumnNames($table, $alt, $prefix)
    {
        global $conf;

        if (strcasecmp($conf['database']['dbtype'], 'pgsql')) {
            return $alt;
        }

        $table = $this->_add_prefix($table);
        $fetched_columns = $this->Query('SELECT column_name FROM information_schema.columns WHERE table_name = ?',
                                         array(str_replace('"', '', $table)));
        $fetched_columns = $this->FetchAllArray($fetched_columns);

        foreach ($fetched_columns as $key => $value)
        {
            $col_names[$key] = $prefix . $value[0];
        }

        $groupby = implode(', ', $col_names);

        return $groupby;
    }

    /**
     * Replace
     *
     * Try to update a record,
     * and if the record is not found,
     * an insert statement is generated and executed.
     *
     * @param string $table
     * @param array $field
     * @param array $keys
     * @param bool $autoquote
     * @access public
     * @return integer 0 on error, 1 on update. 2 on insert
     */
    public function Replace($table, $field, $keys, $autoquote = true)
    {
        $table = $this->_add_prefix($table);
        return $this->dblink->Replace($table, $field, $keys, $autoquote);
    }

    /**
     * Adds the table prefix
     * @param string $sql_data table name or sql query
     * @return string sql with correct,quoted table prefix
     * @access private
     * @since 0.9.9
     */
    private function _add_prefix($sql_data)
    {
        return preg_replace('/{([\w\-]*?)}/', $this->QuoteIdentifier($this->dbprefix . '\1'), $sql_data);
    }

    /**
     * Helper method to quote an indentifier
     * (table or field name) with the database specific quote
     * @param string $ident table or field name to be quoted
     * @return string
     * @access public
     * @since 0.9.9
     */
    public function QuoteIdentifier($ident)
    {
        return (string) $this->dblink->nameQuote . $ident . $this->dblink->nameQuote ;
    }

    /**
     * Quote a string in a safe way to be entered to the database
     * (for the very few cases we don't use prepared statements)
     *
     * @param string $string  string to be quoted
     * @return string  quoted string
     * @access public
     * @since 0.9.9
     * @notes please use this little as possible, always prefer prepared statements
     */
    public function qstr($string)
    {
        return $this->dblink->qstr($string, false);
    }

    /**
     * fill_placeholders
     *  a convenience function to fill sql query placeholders
     *  according to the number of columns to be used.
     * @param array $cols
     * @param integer $additional generate N additional placeholders
     * @access public
     * @return string comma separated "?" placeholders
     * @static
     */
    public function fill_placeholders($cols, $additional=0)
    {
        if(is_array($cols) && count($cols) && is_int($additional)) {

            return join(',', array_fill(0, (count($cols) + $additional), '?'));

        } else {
            //this is not an user error, is a programmer error.
            trigger_error("incorrect data passed to fill_placeholders", E_USER_ERROR);
        }
    }
    // End of Database Class
}
