<?php
/**
 * Flyspray
 *
 * Database class
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
	 * Table prefix, usually 'flyspray_'
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
	 * ADOdb database connection handler object
	 * @var object
	 * @access public
	*/
	public $dblink = null;

	/**
	 * Open a connection to the database quickly
	 * (note by maintainer: not 'faster' because it calls dbOpen(), just easier to call with an array of parameters)
	 *
	 * @param array $conf connection data
	 *
	 * @return void
	 */
	public function dbOpenFast($conf)
	{
		if(!is_array($conf) || extract($conf, EXTR_REFS|EXTR_SKIP) < 5) {
			die(
				'Flyspray was unable to connect to the database. '
				.'Check your settings in flyspray.conf.php'
			);
		}

		$this->dbOpen($dbhost, $dbuser, $dbpass, $dbname, $dbtype, isset($dbprefix) ? $dbprefix : '');
	}

	/**
	 * Opens a connection to the database and set connection parameters.
	 *
	 * @param string $dbhost hostname which the database server uses
	 * @param string $dbuser username to connect to the database
	 * @param string $dbpass password to connect to the database
	 * @param string $dbname
	 * @param string $dbtype database driver to use
	 *                       supported: "mysqli", "pgsql"
	 *                       experimental: "pdo_mysql", "pdo_pgsql", do not use.
	 * @param string $dbprefix database prefix
	 *
	 * @return void
	 */
	public function dbOpen($dbhost = '', $dbuser = '', $dbpass = '', $dbname = '', $dbtype = '', $dbprefix = '')
	{
		if ($dbtype==='mysql' && version_compare(PHP_VERSION,'7.0.0')>=0) {
			// silently switch to mysqli as mysql extension is obsolete and removed by PHP7
			$dbtype='mysqli';
		}
		$this->dbtype = $dbtype;
		$this->dbprefix = $dbprefix;
		if (!empty($this->dbprefix) && !preg_match('/^[a-z][a-z0-9_]+$/i', $this->dbprefix)) {
			die('Check your dbprefix setting in flyspray.conf.php');
		}
		$ADODB_COUNTRECS = false;

		/** 20160408 peterdd: hack to enable database socket usage with adodb-5.20.3
		 *  For instance on german 1und1 managed linux servers, e.g. $dbhost='localhost:/tmp/mysql5.sock'
		 */
		if (($dbtype=='mysqli' || $dbtype='pdo_mysql') && 'localhost:/'==substr($dbhost,0,11)) {
			$dbsocket=substr($dbhost,10);
			$dbhost='localhost';
			if ($dbtype=='mysqli') {
				ini_set('mysqli.default_socket', $dbsocket );
			} else {
				ini_set('pdo_mysql.default_socket',$dbsocket);
			}
		}

		# adodb for pdo is a bit different then the others at the moment (adodb 5.20.4)
		# see https://adodb.org/dokuwiki/doku.php?id=v5:database:pdo
		if ($this->dbtype=='pdo_mysql') {
			$this->dblink = ADOnewConnection('pdo');
			$dsnString= 'host='.$dbhost.';dbname='.$dbname.';charset=utf8mb4';
			$this->dblink->connect('mysql:' . $dsnString, $dbuser, $dbpass);
		} else {
			$this->dblink = ADOnewConnection($this->dbtype);
			$this->dblink->connect($dbhost, $dbuser, $dbpass, $dbname);
		}

		if (!$this->dblink->isConnected()) {
			die('Flyspray was unable to connect to the database. Check your settings in flyspray.conf.php and check if the database server is running and reachable for Flyspray.');
		}
		$this->dblink->setFetchMode(ADODB_FETCH_BOTH);

		/** ADOdb 5.21 now supports setting connection parameters before the connect().
		 * So setting the charset could be done before connect in future avoiding extra request.
		 */
		if ($dbtype=='mysqli') {
			$sinfo=$this->dblink->serverInfo();
			if (version_compare($sinfo['version'], '5.5.3')>=0) {
				$this->dblink->setCharSet('utf8mb4');
			} else {
				$this->dblink->setCharSet('utf8');
			}
		} else {
			$this->dblink->setCharSet('utf8');
		}

		// enable debug if constant DEBUG_SQL is defined.
		!defined('DEBUG_SQL') || $this->dblink->debug = true;
            
		if ($dbtype === 'mysql' || $dbtype === 'mysqli') {
			$dbinfo = $this->dblink->serverInfo();
			if (isset($dbinfo['version']) && version_compare($dbinfo['version'], '5.0.2', '>=')) {
				$this->dblink->execute("SET SESSION SQL_MODE='TRADITIONAL'");
			}
		}
	}

	/**
	 * Closes the database connection
	 * @return void
	 */
	public function dbClose()
	{
		$this->dblink->close();
	}

	/**
	 * insert_ID
	 * 
	 * @access public
	 */
	public function insert_ID()
	{
		return $this->dblink->insert_ID();
	}

    /**
     * countRows
     * Returns the number of rows in a result
     * @param object $result
     * @access public
     * @return int
     */
    public function countRows($result)
    {
        return (int) $result->recordCount();
    }

    /**
     * affectedRows
     *
     * @access public
     * @return int
     */
    public function affectedRows()
    {
        return (int) $this->dblink->affected_Rows();
    }

    /**
     * fetchRow
     *
     * @param $result
     * @access public
     * @return array
     */
    public function fetchRow($result)
    {
        return $result->fetchRow();
    }

    /**
     * fetchCol
     *
     * @param $result
     * @param int $col
     * @access public
     * @return array
     */
    public function fetchCol($result, $col=0)
    {
        $tab = array();
        while ($tmp = $result->fetchRow()) {
            $tab[] = $tmp[$col];
        }
        return $tab;
    }

    /**
     * query
     *
     * @param mixed $sql
     * @param mixed $inputarr
     * @param mixed $numrows
     * @param mixed $offset
     * @access public
     * @return void
     */
    public function query($sql, $inputarr = false, $numrows = -1, $offset = -1)
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
            $result =  $this->dblink->selectLimit($sql, (int) $numrows, (int) $offset, $inputarr);
        } else {
            $result =  $this->dblink->execute($sql, $inputarr);
        }

        if (!$result) {
            if(function_exists('debug_backtrace') && defined('DEBUG_SQL')) {
                echo "<pre style='text-align: left;'>";
                var_dump(debug_backtrace());
                echo "</pre>";
            }

            $query_params = '';

            if (is_array($inputarr) && count($inputarr)) {
                $query_params =  implode(',', array_map(array('Filters','noXSS'), $inputarr));
            }

            die(sprintf("Query {%s} with params {%s} failed! (%s)",
                Filters::noXSS($sql), $query_params, Filters::noXSS($this->dblink->errorMsg())));

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

        $sql = $this->query($sql, $sqlargs);
        return ($this->cache[$idx] = $this->fetchAllArray($sql));
    }

    /**
     * fetchOne
     *
     * @param $result
     * @access public
     * @return mixed
     */
    public function fetchOne($result)
    {
        $row = $this->fetchRow($result);
        return (isset($row[0]) ? $row[0] : '');
    }

    /**
     * fetchAllArray
     *
     * @param $result
     * @access public
     * @return array
     */
    public function fetchAllArray($result)
    {
        return $result->getArray();
    }

    /**
     * groupBy
     *
     * This groups a result by a single column the way
     * MySQL would do it. Postgre doesn't like the queries MySQL needs.
     *
     * @param object $result
     * @param string $column
     * @access public
     * @return array process the returned array with foreach ($return as $row) {}
     */
    public function groupBy($result, $column)
    {
        $rows = array();
        while ($row = $this->fetchRow($result)) {
            $rows[$row[$column]] = $row;
        }
        return array_values($rows);
    }

    /**
     * getColumnNames
     *
     * @param mixed $table
     * @param mixed $alt
     * @param mixed $prefix
     * @access public
     * @return string
     */
    public function getColumnNames($table, $alt, $prefix)
    {
        global $conf;

        if (strcasecmp($conf['database']['dbtype'], 'pgsql')) {
            return $alt;
        }

        $table = $this->_add_prefix($table);
        $fetched_columns = $this->query('SELECT column_name FROM information_schema.columns WHERE table_name = ?',
                                         array(str_replace('"', '', $table)));
        $fetched_columns = $this->fetchAllArray($fetched_columns);

        foreach ($fetched_columns as $key => $value) {
            $col_names[$key] = $prefix . $value[0];
        }

        $groupby = implode(', ', $col_names);

        return $groupby;
    }

    /**
     * replace
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
    public function replace($table, $field, $keys, $autoquote = true)
    {
        $table = $this->_add_prefix($table, false); // ADOdb 5.21 quotes $table parameter too, so avoid double quoting.
        return $this->dblink->replace($table, $field, $keys, $autoquote);
    }

	/**
	 * Adds the table prefix
	 * @param string $sql_data table name or sql query
	 * @param bool $quote (optional) This option was added to avoid double quoting with ADOdb 5.21 replace()
	 * @return string sql with correct,quoted table prefix
	 * @access private
	 * @since 0.9.9
	 */
	private function _add_prefix($sql_data, $quote=true)
	{
		if ($quote) {
			return preg_replace('/{([\w\-]*?)}/', $this->quoteIdentifier($this->dbprefix . '\1'), $sql_data);
		} else {
			return preg_replace('/{([\w\-]*?)}/', $this->dbprefix . '\1', $sql_data);
		}
	}

    /**
     * Helper method to quote an indentifier
     * (table or field name) with the database specific quote
     * @param string $ident table or field name to be quoted
     * @return string
     * @access public
     * @since 0.9.9
     */
    public function quoteIdentifier($ident)
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

} // end class Database
