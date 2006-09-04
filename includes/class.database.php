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

require_once dirname(dirname(__FILE__)) . '/adodb/adodb.inc.php';

class Database
{
    /**
     * Table prefix, usually flyspray_
     * @var string
     * @access private
     */
    var $dbprefix;
    
    /**
     * Cache for queries done by cached_query()
     * @var array
     * @access private
     * @see cached_query();
     */
    var $cache = array();

    /**
     * Open a connection to the database quickly
     * @param array $conf connection data
     * @return void
     */
    function dbOpenFast($conf)
    {
        extract($conf, EXTR_REFS|EXTR_SKIP);
        $this->dbOpen($dbhost, $dbuser, $dbpass, $dbname, $dbtype, $dbprefix);
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
    function dbOpen($dbhost = '', $dbuser = '', $dbpass = '', $dbname = '', $dbtype = '', $dbprefix = '')
    {
        
        $this->dbtype   = $dbtype;
        $this->dbprefix = $dbprefix;
        $ADODB_COUNTRECS = false;
        $dsn = "$dbtype://$dbuser:$dbpass@$dbhost/$dbname";
        $this->dblink = NewADOConnection($dsn);

        if ($this->dblink === false ) {

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
    }

    /**
     * Closes the database connection
     * @return void
     */
    function dbClose()
    {
        $this->dblink->Close();
    }

    /**
     * Replace undef values (treated as NULL in SQL database) with empty
     * strings.
     * @param array input array or false
     * @return array SQL safe array (without undefined values)
     */
    function dbUndefToEmpty($arr)
    {
        if (is_array($arr)) {
            $c = count($arr);

            for($i=0; $i<$c; $i++) {
                if (!isset($arr[$i])) {
                    $arr[$i] = '';
                }
                // This line safely escapes sql before it goes to the db
                $this->dblink->qstr($arr[$i]);
            }
        }
        return $arr;
    }

    function CountRows(&$result)
    {
        return (int) $result->RecordCount();
    }

    function AffectedRows()
    {
        return (int) $this->dblink->Affected_Rows();
    }

    function FetchRow(&$result)
    {
        return $result->FetchRow();
    }

    function fetchCol(&$result, $col=0)
    {
        $tab = array();
        while ($tmp = $result->fetchRow()) {
            $tab[] = $tmp[$col];
        }
        return $tab;
    }

    function Query($sql, $inputarr = false, $numrows = -1, $offset = -1)
    {
        // auto add $dbprefix where we have {table}
        $sql = $this->_add_prefix($sql);
        // replace undef values (treated as NULL in SQL database) with empty
        // strings
        $inputarr = $this->dbUndefToEmpty($inputarr);

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

        if($this->dblink->hasTransactions === true) {
            $this->dblink->StartTrans();
        }

        if (($numrows >= 0 ) or ($offset >= 0 )) {
            $result =  $this->dblink->SelectLimit($sql, $numrows, $offset, $inputarr);
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
                
                $query_params =  implode(',', array_map('htmlspecialchars', $inputarr));
            
            } 

            die (sprintf("Query {%s} with params {%s} Failed! (%s)",
                    htmlspecialchars($sql, ENT_QUOTES, 'utf-8'), 
                    $query_params, $this->dblink->ErrorMsg()));
        }

        if($this->dblink->hasTransactions === true) {
           $this->dblink->CompleteTrans();
        }

        return $result;
    }

    function cached_query($idx, $sql, $sqlargs = array())
    {
        if (isset($this->cache[$idx])) {
            return $this->cache[$idx];
        }

        $sql = $this->Query($sql, $sqlargs);
        return ($this->cache[$idx] = $this->fetchAllArray($sql));
    }

    function FetchOne(&$result)
    {
        $row = $this->FetchRow($result);
        return (count($row) ? $row[0] : '');
    }

    function FetchAllArray(&$result)
    {
        return $result->GetArray();
    }

    function GetColumnNames($table, $alt, $prefix)
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
    function Replace($table, $field, $keys, $autoquote = true)
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
    function _add_prefix($sql_data)
    {
        return (string) preg_replace('/{([\w\-]*?)}/', $this->QuoteIdentifier($this->dbprefix . '\1'), $sql_data);
    }
    
    /**
     * Helper method to quote an indentifier 
     * (table or field name) with the database specific quote
     * @param string $ident table or field name to be quoted
     * @return string
     * @access public
     * @since 0.9.9
     */
    function QuoteIdentifier($ident)
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
    function qstr($string)
    {
        return $this->dblink->qstr($string);
    }
    // End of Database Class
}

?>
