<?php

class Db {

	const EXCEPTIONS 	= true;
	const NO_EXCEPTIONS = false;
	const FLATTEN 		= true;
	const NO_FLATTEN 	= false;
	const IS_NULL 		= 'IS NULL';
	const IS_NOT_NULL 	= 'IS NOT NULL';
	const IN			= 'IN';

	static $inst = false;

	private $config;
	private $pdo;
	private $connected = false;
	private $query_count = 0;
   
	public	$prefix = null;
	public	$debug = false;
	

	public static function _get(){
		if(self::$inst == false) self::$inst = new Db();
		return self::$inst;
	}

	public function setConfig($config){
		$this->config = $config;
		return $this;
	}

	public function connect(){
		try{
			$this->pdo = new PDO(
				sprintf(
					'%s:dbname=%s;host=%s;port=%i',
					$this->config['driver'],
					$this->config['database'],
					$this->config['host'],
					$this->config['port']
				)
				,$this->config['user']
				,$this->config['password']
				,array(
					 PDO::ATTR_ERRMODE				=>	PDO::ERRMODE_EXCEPTION
					,PDO::ATTR_DEFAULT_FETCH_MODE	=>	PDO::FETCH_ASSOC
				)
			);
			$this->connected = true;
		} catch(PDOException $error){
			$this->connected = false;
			throw new Exception("Database Connection Failed: ".$error->getMessage());
		}
		return $this;
	}

	public function exec($statement){
		$this->query_count++;
		return $this->pdo->exec($statement);
	}

	public function prepare($statement,$driver_options=array()){
		$this->query_count++;
		return $this->pdo->prepare($statement,$driver_options);
	}

	public function query($statement){
		$this->query_count++;
		return $this->pdo->query($statement);
	}

	public function getQueryCount(){
		return $this->query_count;
	}

	public function close(){
		static $inst = false;
	}

	//Db::prepwhere(); Prepares WHERE strings to be used in queries
	// $pairs	array of clauses which can be in 4 formats
	//				1)	'field-name'	=>	array($bool='AND',$operator='=',$value)
	//				2) 	'field-name'	=>	array($operator='=',$value) //bool defaults to AND
	//				3)	'field-name'	=>	array($operator) //bool defaults to AND, value defaults to NULL
	//				4)	'field-name'	=>	$value //bool defaults to AND, operator defaults to =
	//				NOTE: use Db::IS_NULL and Db::IS_NOT_NULL for null value operators
	// $type	specify the start of the string defaults to 'WHERE'
	// returns an array, with members:
	//     [0] <string> the resulting WHERE clause; compiled for use with PDO::prepare including leading space (ready-to-use)
	//     [n] <array>  the values array; ready for use with PDO::execute
	public static function prepwhere($pairs=array(),$type='WHERE'){
		if(!count($pairs)) return array('',null);
		
		$values = array();
		$str = ' '.strtoupper($type).' ';
		$fieldcnt = 0;
		foreach($pairs as $field => $value){
			//set defaults
			$op = '=';
			$bool = 'AND';
			//handle advanced settings
			switch((is_array($value) ? count($value) : false)){
				case 3:
					$bool 	= array_shift($value);
					$op 	= array_shift($value);
					$value 	= array_shift($value);
					break;
				case 2:
					$op 	= array_shift($value);
					$value 	= array_shift($value);
					break;
				case 1:
					$op 	= array_shift($value);
					$value 	= null;
					break;
				default:
					//nothing
					break;
			}
			//format properly
			$bool = ($fieldcnt++==0)?'':' '.strtoupper($bool).' ';
			$op = strtoupper($op);
			//handle op and add to values if needed
			switch($op){
				case Db::IS_NULL:
				case Db::IS_NOT_NULL:
					//no action needed
					break;
				case Db::IN:
					if(is_array($value)){
						$op .= '('.rtrim(str_repeat('?,',count($value)),',').') ';
						foreach($value as $v) $values[] = $v;
					} else {
						$op .= '('.$value.')';
					}
					break;
				default:
					$op .= '?';
					$values[] = (string)$value;
					break;
			}
			//concat
			$str .= sprintf('%s%s%s',$bool,self::escape($field),$op);
		}
		return array_merge(array($str),$values);
	}
	
	public function run($stmt,$params=array()){
		if(!is_array($params)) $params = array($params);
		if($this->debug) debug_dump($stmt,$params);
		$query = $this->prepare($stmt);
		$query->execute($params);
		return $query;
	}

	public function insert($table,$params=array(),$update_if_exists=false){
		if($update_if_exists) return $this->insertOrUpdate($table,$params);
		$stmt = sprintf(
			'INSERT INTO `%s` (%s) VALUES (%s)'
			,$table
			,implodei(',',self::escape(array_keys($params)))
			,rtrim(str_repeat('?,',count($params)),',')
		);
		$this->run($stmt,array_values($params));
		return $this->lastInsertId();
	}
	
	protected function insertOrUpdate($table,$params=array()){
		$stmt = sprintf(
			'INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s'
			,$table
			,implodei(',',self::escape(array_keys($params)))
			,rtrim(str_repeat('?,',count($params)),',')
			,implodei('=?,',self::escape(array_keys($params))).'=?'
		);
		$this->run($stmt,array_merge(array_values($params),array_values($params)));
		return $this->lastInsertId();
	}

	public function update($table,$primary_key,$primary_key_value=null,$params=array()){
		if(is_array($primary_key)){
			$key_stmt = implodei('=? AND ',self::escape(array_keys($primary_key)));
			$params = $primary_key_value;
		} else {
			$key_stmt = self::escape($primary_key).' =?';
		}
		if(!count($params)) throw new Exception('No data provided for update to: '.$table);
		$stmt = sprintf(
			'UPDATE `%s` SET %s WHERE %s'
			,$table
			,implodei('=?, ',self::escape(array_keys($params))).'=?'
			,$key_stmt
		);
		if(!is_array($primary_key)) $params[] = $primary_key_value;
		else $params = array_merge($params,array_values($primary_key));
		return $this->run($stmt,array_values($params));
	}

	public function fetch($stmt,$params=array(),$throw_exception=Db::NO_EXCEPTIONS,$except_code=null,$flatten=Db::NO_FLATTEN){
		if(is_array($stmt)) list($stmt,$params) = $stmt;
		$query = $this->run($stmt,$params);
		$result = $query->fetch();
		$query->closeCursor();
		if(((!is_array($result)) || (count($result)==0)) && $throw_exception !== Db::NO_EXCEPTIONS)
			throw new Exception($throw_exception,$except_code);
		if($flatten && is_array($result) && (count($result)>0) && (count(array_keys($result)) == 1)){
			$col = array_shift(array_keys($result));
			$result = $result[$col];
		}
		return $result;
	}

	public function fetchAll($stmt,$params=array(),$throw_exception=Db::NO_EXCEPTIONS,$except_code=null,$flatten=Db::NO_FLATTEN){
		if(is_array($stmt)) list($stmt,$params) = $stmt;
		$query = $this->run($stmt,$params);
		$result = $query->fetchAll();
		if(!$result && $throw_exception !== Db::NO_EXCEPTIONS) throw new Exception($throw_exception,$except_code);
		if($flatten && is_array($result) && (count($result)>0) && is_array($result[0]) && (count(array_keys($result[0])) == 1)){
			$col = array_shift(array_keys($result[0]));
			$arr = array();
			foreach($result as $row) $arr[] = $row[$col];
			$result = $arr;
		}
		return $result;
	}

	public function __call($function_name,$parameters) {
		if(!is_array($parameters)) $parameters = array();
		return call_user_func_array(array($this->pdo, $function_name), $parameters);
	}
	
	public static function escape($arr=array()){
		if(!is_array($arr)) return '`'.implode('`.`',explode('.',$arr)).'`';
		foreach($arr as &$f){
			//join parts of an array into escaped fields
			if(is_array($f)) $f = '`'.implode('`.`',$f).'`';
			//escape fields and blow up periods
			else $f = '`'.implode('`.`',explode('.',$f)).'`';
		}
		return $arr;
	}

	public function setPrefix($prefix){
		$this->prefix = $prefix;
		return $this;
	}

}
