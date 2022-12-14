<?php
// Laminas Dbal for Smart.Framework
// Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\DbalLaminas;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================

/**
 * Class: \SmartModExtLib\DbalLaminas\DbalPdo - provides a general PDO Database Adapter using Laminas/Db as a module for Smart.Framework that supports the following PDO drivers such as: PDO SQLite, PDO MySQL, PDO PostgreSQL.
 *
 * If you wish to use the PDO drivers in your projects instead of the direct ones supported directly by Smart.Framework this is a good solution.
 *
 * <code>
 *
 * $conf_driver = [
 *		'driver'   => 'PDO_SQLITE',
 *		'database' => 'tmp/laminas-dbal-test.sqlite'
 * ];
 * //$conf_driver = [
 * //	'driver'   => 'PDO_MYSQL',
 * //	'host'     => '127.0.0.1',
 * //	'port'     => 3306,
 * //	'database' => 'smart_framework',
 * //	'username' => 'root',
 * //	'password' => 'root'
 * //];
 * //$conf_driver = [
 * //	'driver'   => 'PDO_PGSQL',
 * //	'host'     => '127.0.0.1',
 * //	'port'     => 5432,
 * //	'database' => 'smart_framework',
 * //	'username' => 'pgsql',
 * //	'password' => 'pgsql'
 * //];
 *
 * $db = new \SmartModExtLib\DbalLaminas\DbalPdo((array)$conf_driver);
 * //$adapter = $db->getConnection();
 *
 * // write test using the Laminas DB Objects
 * $db->queryExecute('DROP TABLE IF EXISTS sf_laminas_dbal_test');
 * $table = new \Laminas\Db\Sql\Ddl\CreateTable('sf_laminas_dbal_test');
 * $table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Integer('id'));
 * $table->addConstraint(new \Laminas\Db\Sql\Ddl\Constraint\PrimaryKey('id'));
 * $table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Varchar('name', 100));
 * $table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Text('descr'));
 * $table->addColumn(new \Laminas\Db\Sql\Ddl\Column\Integer('cnt'));
 * $sql = $db->getSqlBuilder();
 * $db->queryExecute((string)$sql->buildSqlString($table));
 *
 * // read test using the Laminas DB Objects
 * $sql = $db->getSqlBuilder();
 * $select = $sql->select();
 * $select->from('sf_laminas_dbal_test');
 * // $select->from([ 't' => 'sf_laminas_dbal_test' ]);
 * // $select->columns([ 'id', 'name' ]);
 * $select->where(array('id' => 1));
 * //$select->join(
 * //  [ 'u' => 'table2' ],
 * //  't.t_id = u.id',
 * //  [
 * //    'email',
 * //  ]
 * //);
 * $select->order([ 'id' => 'ASC' ]);
 * $select->limit(100);
 * $select->offset(0);
 * $sqlstr = (string) $sql->buildSqlString($select);
 * $results = (array) $db->queryReadMultiRecords((string)$sqlstr);
 *
 * // tests using Smart.Framework DB compatibility: count, read, write
 * $db->queryExecute('BEGIN'); // start transaction
 * $db->queryWrite('DELETE FROM sf_laminas_dbal_test');
 * $db->queryExecute('COMMIT'); // $db->queryExecute('ROLLBACK'); // commit or rollback transaction
 * $count = $db->queryCountRecords('SELECT COUNT(1) FROM sf_laminas_dbal_test');
 * $results = $db->queryReadMultiRecords( // get many rows 0..n [field1, field2, ..., fieldn]
 * 		'SELECT * FROM sf_laminas_dbal_test WHERE id > ?', // id > 0
 * 		[ 0 ]
 * );
 * $results = $db->queryReadSingleRecord( // get just one row [field1, field2, ..., fieldn]
 * 		'SELECT * FROM sf_laminas_dbal_test WHERE id > ? LIMIT 1 OFFSET 0', // id > 1
 * 		[ 1 ]
 * );
 *
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP PDO ; classes: \Laminas\Db
 * @version 	v.20221214.1044
 * @package 	modules:Database:PDO:Laminas-Dbal
 *
 */
final class DbalPdo {

	// ->

	private const LAMINAS_DB_VERSION = 'Laminas/Db 2.13.4 ; Laminas/Stdlib 3.6.4';

	private $cfg = [];
	private $connkey = '';
	private $connection = null;
	private $profiler = null;
	private $platform = null;

	private $timeout_conn = 0;
	private $slow_query_time = 0;
	private $fatal_err = true;


	/**
	 * Class Constructor for using Laminas/DB with a custom driver and options, using PDO, providing a compatible layer to use a project with any of PDO/PgSQL, PDO/SQLite and PDO/MySQL.
	 * The utility of this DB Driver is to serve the missing cross-db PDO Drivers support from Smart.Framework such as PDO/PgSQL, PDO/SQLite and PDO/MySQL to support a cross-DB project.
	 * The Laminas/DB provides a unified query parameters implementation using PDO and is not 100% compatible with the queries written for the direct drivers supplied by Smart.Framework when parameters are used (PostgreSQL, MySQLi, SQLite and other Non-PDO drivers) because the parameter mode can differ from driver to driver (Ex: ? :param $# ...)
	 *
	 * @hint: 	For the DB direct access Drivers (Adapters) such as: PgSQL, SQlite3 and MySQLi the Smart.Framework provides built-in / includded and more optimized libraries
	 *
	 * @param MIXED 	$cfg							:: the driver options as array[] or 'mysqli:config' | 'pgsql:config' | 'sqlite:tmp/sample-dbfile.sqlite'
	 * @param INTEGER+ 	$timeout 						:: the connection timeout (applies only to MySQL and PostgreSQL)
	 *
	 */
	public function __construct($cfg, $timeout=30, $fatal_err=null) {
		//--
		if(!\is_array($cfg)) {
			$this->cfg = array();
			switch((string)$cfg) {
				case 'mysqli:config':
					$arr_cfg = \Smart::get_from_config('mysqli');
					if(\Smart::array_size($arr_cfg) <= 0) {
						\Smart::raise_error(__METHOD__.' The CFG not found in Smart.Framework config (mysqli): '.$cfg);
						return false;
					} //end if
					$this->cfg = array();
					$this->cfg['driver'] 	= 'PDO_MYSQL';
					$this->cfg['database'] 	= (string) $arr_cfg['dbname'];
					$this->cfg['host'] 		= (string) $arr_cfg['server-host'];
					$this->cfg['port'] 		= (int)    $arr_cfg['server-port'];
					$this->cfg['username'] 	= (string) $arr_cfg['username'];
					$this->cfg['password'] 	= (string) base64_decode((string)$arr_cfg['password']);
					$this->cfg['timeout'] 	= (int)    $arr_cfg['timeout'];
					$this->cfg['slowtime'] 	= (float)  $arr_cfg['slowtime'];
					break;
				case 'pgsql:config':
					$arr_cfg = \Smart::get_from_config('pgsql');
					if(\Smart::array_size($arr_cfg) <= 0) {
						\Smart::raise_error(__METHOD__.' The CFG not found in Smart.Framework config (pgsql): '.$cfg);
						return false;
					} //end if
					$this->cfg = array();
					$this->cfg['driver'] 	= 'PDO_PGSQL';
					$this->cfg['database'] 	= (string) $arr_cfg['dbname'];
					$this->cfg['host'] 		= (string) $arr_cfg['server-host'];
					$this->cfg['port'] 		= (int)    $arr_cfg['server-port'];
					$this->cfg['username'] 	= (string) $arr_cfg['username'];
					$this->cfg['password'] 	= (string) base64_decode((string)$arr_cfg['password']);
					$this->cfg['timeout'] 	= (int)    $arr_cfg['timeout'];
					$this->cfg['slowtime'] 	= (float)  $arr_cfg['slowtime'];
					break;
				default: // sqlite:tmp/sample-dbfile.sqlite
					if((string)\trim((string)$cfg) == '') {
						\Smart::raise_error(__METHOD__.' Empty CFG not allowed !');
						return false;
					} //end if
					if(\strpos((string)$cfg, 'sqlite:') !== 0) {
						\Smart::raise_error(__METHOD__.' Invalid CFG: '.$cfg);
						return false;
					} //end if
					$the_db = (string) \trim((string)\substr((string)$cfg, 7));
					if((string)$the_db == '') {
						\Smart::raise_error(__METHOD__.' Empty Database Path (sqlite): '.$cfg);
						return false;
					} //end if
					if(!\SmartFileSysUtils::check_if_safe_path((string)$the_db, 'yes', 'yes')) { // deny absolute path access ; allow protected path access (starting with #)
						\Smart::raise_error(__METHOD__.' Unsafe Database Path (sqlite): '.$cfg);
						return false;
					} //end if
					$arr_cfg = \Smart::get_from_config('sqlite');
					if(\Smart::array_size($arr_cfg) <= 0) {
						\Smart::raise_error(__METHOD__.' The CFG not found in Smart.Framework config (sqlite): '.$cfg);
						return false;
					} //end if
					$this->cfg = array();
					$this->cfg['driver'] 	= 'PDO_SQLITE';
					$this->cfg['database'] 	= (string) $the_db;
					$this->cfg['timeout'] 	= (int)    $arr_cfg['timeout'];
					$this->cfg['slowtime'] 	= (float)  $arr_cfg['slowtime'];
			} //end switch
		} else {
			$this->cfg = (array) $cfg;
		} //end if else
		//--
		$this->cfg['driver'] = (string) \strtolower((string)\trim((string)$this->cfg['driver']));
		$this->cfg['host'] = (string) \trim((string)(isset($this->cfg['host']) ? $this->cfg['host'] : ''));
		$this->cfg['port'] = (int) (isset($this->cfg['port']) ? $this->cfg['port'] : null);
		//--
		switch((string)$this->cfg['driver']) {
		//	case 'mysqli': // do not use as the cross-db params compatibility in queries would be broken !
			case 'pdo_mysql':
				if((string)$this->cfg['host'] == '') {
					$this->cfg['host'] = '127.0.0.1';
				} //end if
				if($this->cfg['port'] <= 0) {
					$this->cfg['port'] = 3306;
				} //end if
				$this->slow_query_time = (float) (((float)$this->cfg['slowtime'] > 0) ? (float)$this->cfg['slowtime'] : 0.0050);
				break;
		//	case 'pgsql': // do not use as the cross-db params compatibility in queries would be broken !
			case 'pdo_pgsql':
				if((string)$this->cfg['host'] == '') {
					$this->cfg['host'] = '127.0.0.1';
				} //end if
				if($this->cfg['port'] <= 0) {
					$this->cfg['port'] = 5432;
				} //end if
				$this->slow_query_time = (float) (((float)$this->cfg['slowtime'] > 0) ? (float)$this->cfg['slowtime'] : 0.0050);
				break;
			case 'pdo_sqlite':
				$this->cfg['host'] = 'sqlite-file';
				$this->cfg['port'] = 0;
				$this->slow_query_time = (float) (((float)$this->cfg['slowtime'] > 0) ? (float)$this->cfg['slowtime'] : 0.0025);
				break;
			default:
				$this->error('INIT', 'Unsupported PDO Laminas/Db Driver: '.$this->cfg['driver'], '@Driver', '');
				return;
		} //end switch
		//--
		$this->cfg['charset'] = (string) \SMART_FRAMEWORK_SQL_CHARSET;
		$this->cfg['options'] = (array) ((isset($this->cfg['options']) && \is_array($this->cfg['options'])) ? $this->cfg['options'] : []);
		$this->cfg['options']['buffer_results'] = true;
		//--
		$this->connkey = (string) (isset($this->cfg['driver']) ? $this->cfg['driver'] : '').'*'.(isset($this->cfg['host']) ? $this->cfg['host'] : '').':'.(isset($this->cfg['port']) ? $this->cfg['port'] : '').'@'.(isset($this->cfg['database']) ? $this->cfg['database'] : '').'#'.(isset($this->cfg['username']) ? $this->cfg['username'] : '');
		//--
		if(($fatal_err === true) OR ($fatal_err === false)) {
			$this->fatal_err = (bool) $fatal_err;
		} else {
			$this->fatal_err = (bool) ! (\defined('\\SMART_SOFTWARE_SQLDB_FATAL_ERR') AND (\SMART_SOFTWARE_SQLDB_FATAL_ERR === false));
		} //end if else
		//--
		$this->connection = new \Laminas\Db\Adapter\Adapter((array)$this->cfg); // lazy connection, does not throw here (will connect on first query)
		//--
		if((int)$this->cfg['timeout'] > 0) {
			$timeout = (int) $this->cfg['timeout'];
		} //end if
		//--
		$timeout = (int) $timeout;
		if($timeout < 1) {
			$timeout = 1;
		} //end if
		if($timeout > 60) {
			$timeout = 60;
		} //end if
		//--
		$this->timeout_conn = (int) $timeout;
		//--
		$this->platform = $this->connection->getPlatform();
		//--
		if(\SmartFrameworkRegistry::ifDebug()) {
			//--
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|slow-time', \number_format((float)$this->slow_query_time, 7, '.', ''), '=');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'metainfo',
				'data' => 'Database Server: SQL ('.$this->cfg['driver'].') / App Connector Version: '.self::LAMINAS_DB_VERSION.' / Connection Charset: '.\SMART_FRAMEWORK_SQL_CHARSET
			]);
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'metainfo',
				'data' => 'Connection Timeout: default / Fast Query Reference Time < '.$this->slow_query_time.' seconds'
			]);
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'open-close',
				'data' => 'DB Connection: '.$this->connkey,
				'connection' => (string) \sha1((string)\print_r($this->cfg,1))
			]);
			//--
		} //end if
		//--
	} //END FUNCTION


	/**
	 * Laminas/DB: Set Fatal Error TRUE/FALSE
	 *
	 * @return VOID
	 */
	public function setFatalErr(bool $is_fatal) : void {
		//--
		$this->fatal_err = (bool) $is_fatal;
		//--
		return;
		//--
	} //END FUNCTION


	/**
	 * Laminas/DB: Get the Current Driver
	 *
	 * @return STRING
	 */
	public function getDriver() : string {
		//--
		return (string) $this->cfg['driver'];
		//--
	} //END FUNCTION


	/**
	 * Laminas/DB: Get the Platform
	 *
	 * @return OBJECT
	 */
	public function getPlatform() : object {
		//--
		return $this->platform;
		//--
	} //END FUNCTION


	/**
	 * Laminas/DB: Get the Connection
	 *
	 * @return OBJECT
	 */
	public function getConnection() : \Laminas\Db\Adapter\Adapter {
		//--
		return $this->connection;
		//--
	} //END FUNCTION


	/**
	 * Laminas/DB: Get the SQL Builder
	 *
	 * @return OBJECT
	 */
	public function getSqlBuilder() : \Laminas\Db\Sql\Sql {
		//--
		return new \Laminas\Db\Sql\Sql($this->connection);
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Get Count of Records by Query
	 * This function is intended to be used for count type queries: SELECT COUNT().
	 *
	 * @param STRING $query							:: the query
	 * @param ARRAY  $values 						:: *optional* array of parameters
	 * @return INTEGER								:: the result of COUNT()
	 */
	public function queryCountRecords(string $query, ?array $values=null) : int {
		//--
		$query = (string) \trim((string)$query);
		if((string)$query == '') {
			\Smart::log_warning(__METHOD__.' # Empty Query Detected');
			return 0;
		} //end if
		//--
		if(!\is_array($values)) {
			$values = [];
		} //end if
		//--
		$time_start = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_start = \microtime(true);
		} //end if
		//--
		$arr = [];
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('COUNT.Records', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
			return 0;
		} //end try catch
		//--
		$time_end = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_end = (float) (\microtime(true) - (float)$time_start);
		} //end if
		//--
		$count = 0;
		$arr[0] = $arr[0] ?? null;
		if(\is_array($arr[0])) {
			foreach($arr[0] as $key => $val) {
				$count = (int) $val; // find first row and first column value
				break;
			} //end if
		} //end if
		//--
		if(\SmartFrameworkRegistry::ifDebug()) {
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-queries', 1, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-time', $time_end, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'count',
				'data' => 'Laminas-Dbal/'.$this->cfg['driver'].' [Query::COUNT.Records]',
				'query' => (string) $query,
				'params' => (\Smart::array_size($values) > 0) ? (array) $values : '',
				'rows' => (int) $count,
				'time' => \Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $this->connkey
			]);
		} //end if
		//--
		return (int) $count;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Read (Non-Associative) one or multiple rows.
	 * PDO Query :: Get a List of Records as a List (a Non-Associative array). Supports one or more records.
	 * This function is intended to be used for read type queries: SELECT.
	 *
	 * @param STRING $query							:: the query
	 * @param ARRAY  $values 						:: *optional* array of parameters
	 * @return ARRAY (non-asociative) of results	:: [ 'column-0-0', 'column-0-1', ..., 'column-0-n', 'column-1-0', 'column-1-1', ... 'column-1-n', ..., 'column-m-0', 'column-m-1', ..., 'column-m-n' ]
	 */
	public function queryReadAsListMultiRecords(string $query, ?array $values=null) : array {
		//--
		$query = (string) \trim((string)$query);
		if((string)$query == '') {
			\Smart::log_warning(__METHOD__.' # Empty Query Detected');
			return [];
		} //end if
		//--
		if(!\is_array($values)) {
			$values = [];
		} //end if
		//--
		$time_start = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_start = \microtime(true);
		} //end if
		//--
		$arr = [];
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('READ.AsList.MultiRecords', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
			return [];
		} //end try catch
		//--
		$time_end = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_end = (float) (\microtime(true) - (float)$time_start);
		} //end if
		//--
		$data = [];
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			$arr[$i] = (array) $arr[$i];
			foreach($arr[$i] as $key => $val) {
				$data[] = (string) $val;
			} //end foreach
		} //end for
		$arr = null;
		//--
		if(\SmartFrameworkRegistry::ifDebug()) {
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-queries', 1, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-time', $time_end, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'read',
				'data' => 'Laminas-Dbal/'.$this->cfg['driver'].' [Query::READ.AsList.MultiRecords]',
				'query' => (string) $query,
				'params' => (\Smart::array_size($values) > 0) ? (array) $values : '',
				'rows' => (int) \Smart::array_size($data),
				'time' => \Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $this->connkey
			]);
		} //end if
		//--
		return (array) $data;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Get a List of Records (with row number iterator) each row being a separate Record as an Associative array. Supports one or more records.
	 * This function is intended to be used for read type queries: SELECT.
	 *
	 * @param STRING $query							:: the query
	 * @param ARRAY  $values 						:: *optional* array of parameters
	 * @return ARRAY (asociative) of results		:: [ 0 => ['column1', 'column2', ... 'column-n'], 1 => ['column1', 'column2', ... 'column-n'], ..., m => ['column1', 'column2', ... 'column-n'] ]
	 */
	public function queryReadMultiRecords(string $query, ?array $values=null) : array {
		//--
		$query = (string) \trim((string)$query);
		if((string)$query == '') {
			\Smart::log_warning(__METHOD__.' # Empty Query Detected');
			return [];
		} //end if
		//--
		if(!\is_array($values)) {
			$values = [];
		} //end if
		//--
		$time_start = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_start = \microtime(true);
		} //end if
		//--
		$arr = [];
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('READ.MultiRecords', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
			return [];
		} //end try catch
		//--
		$time_end = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_end = (float) (\microtime(true) - (float)$time_start);
		} //end if
		//--
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			$arr[$i] = (array) $arr[$i];
			foreach($arr[$i] as $key => $val) {
				$arr[$i][(string)$key] = (string) $val;
			} //end foreach
		} //end for
		//--
		if(\SmartFrameworkRegistry::ifDebug()) {
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-queries', 1, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-time', $time_end, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'read',
				'data' => 'Laminas-Dbal/'.$this->cfg['driver'].' [Query::READ.MultiRecords]',
				'query' => (string) $query,
				'params' => (\Smart::array_size($values) > 0) ? (array) $values : '',
				'rows' => (int) \Smart::array_size($arr),
				'time' => \Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $this->connkey
			]);
		} //end if
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Get a Single Record as an Associative array. Supports ONLY one records.
	 * !!! This will raise an error if more than one records are returned !!!
	 * This function does not support multiple rows because the associative data is structured without row iterator.
	 * For queries that return more than one row use: queryReadMultiRecords() or queryReadAsListMultiRecords().
	 * This function is intended to be used for read type queries: SELECT.
	 *
	 * @hints	ALWAYS use a LIMIT 1 OFFSET 0 with all queries using this function to avoid situations that will return more than 1 rows and will raise ERROR with this function.
	 *
	 * @param STRING $query							:: the query
	 * @param ARRAY  $values 						:: *optional* array of parameters
	 * @return ARRAY (asociative) of results		:: Returns just a SINGLE ROW as: [ 'column1', 'column2', ... 'column-n' ]
	 */
	public function queryReadSingleRecord(string $query, ?array $values=null) : array {
		//--
		$query = (string) \trim((string)$query);
		if((string)$query == '') {
			\Smart::log_warning(__METHOD__.' # Empty Query Detected');
			return [];
		} //end if
		//--
		if(!\is_array($values)) {
			$values = [];
		} //end if
		//--
		$time_start = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_start = \microtime(true);
		} //end if
		//--
		$arr = [];
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('READ.SingleRecord', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
			return [];
		} //end try catch
		//--
		$time_end = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_end = (float) (\microtime(true) - (float)$time_start);
		} //end if
		//--
		if(\SmartFrameworkRegistry::ifDebug()) { // use before testing row nums to register debug before exit if more than one rows
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-queries', 1, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-time', $time_end, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'read',
				'data' => 'Laminas-Dbal/'.$this->cfg['driver'].' [Query::READ.SingleRecord]',
				'query' => (string) $query,
				'params' => (\Smart::array_size($values) > 0) ? (array) $values : '',
				'rows' => (int) \Smart::array_size($arr),
				'time' => \Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $this->connkey
			]);
		} //end if
		//--
		if((int)\Smart::array_size($arr) > 1) {
			$this->error('READ.SingleRecord', 'The Result contains more than one row ...', (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
			return [];
		} //end if
		//--
		$arr[0] = $arr[0] ?? null;
		if(!\is_array($arr[0])) {
			$arr[0] = [];
		} //end if
		foreach($arr[0] as $key => $val) {
			$arr[0][(string)$key] = (string) $val;
		} //end foreach
		//--
		return (array) $arr[0];
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Write.
	 * This function is intended to be used for write type queries: INSERT ; INSERT IGNORE ; REPLACE ; UPDATE
	 *
	 * @param STRING $query							:: the query
	 * @param ARRAY  $values 						:: *optional* array of parameters
	 * @return INT 									:: number of affected-rows
	 */
	public function queryWrite(string $query, ?array $values=null) : int {
		//--
		$query = (string) \trim((string)$query);
		if((string)$query == '') {
			\Smart::log_warning(__METHOD__.' # Empty Query Detected');
			return 0;
		} //end if
		//--
		if(!\is_array($values)) {
			$values = [];
		} //end if
		//--
		$time_start = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_start = \microtime(true);
		} //end if
		//--
		$affected = 0;
		try {
			$affected = $this->connection->query(
				$query,
				$values
			)->getAffectedRows();
		} catch(\Exception $e) {
			$this->error('WRITE', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
			return 0;
		} //end try catch
		//--
		$time_end = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_end = (float) (\microtime(true) - (float)$time_start);
		} //end if
		//--
		if(\SmartFrameworkRegistry::ifDebug()) {
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-queries', 1, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-time', $time_end, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
				'type' => 'write',
				'data' => 'Laminas-Dbal/'.$this->cfg['driver'].' [Query::WRITE]',
				'query' => (string) $query,
				'params' => (\Smart::array_size($values) > 0) ? (array) $values : '',
				'rows' => (int) $affected,
				'time' => \Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $this->connkey
			]);
		} //end if
		//--
		return (int) $affected;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Execute.
	 * This function is intended to be used for write type queries: CREATE SCHEMAS / DDLs ; BEGIN (TRANSACTION) ; COMMIT ; ROLLBACK ; CALLING STORED PROCEDURES ...
	 *
	 * @param STRING 	$query						:: the query
	 * @return BOOLEAN 								:: TRUE if Success ; FALSE otherwise
	 */
	public function queryExecute(string $query) : bool {
		//--
		$query = (string) \trim((string)$query);
		if((string)$query == '') {
			\Smart::log_warning(__METHOD__.' # Empty Query Detected');
			return false;
		} //end if
		//--
		$time_start = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_start = \microtime(true);
		} //end if
		//--
		$affected = 0;
		try {
			$affected = $this->connection->query(
				$query,
				\Laminas\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
			)->getAffectedRows();
		} catch(\Exception $e) {
			$this->error('EXECUTE', (string)$e->getMessage(), (string)$query, '');
			return false;
		} //end try catch
		//--
		$time_end = 0;
		if(\SmartFrameworkRegistry::ifDebug()) {
			$time_end = (float) (\microtime(true) - (float)$time_start);
		} //end if
		//--
		if(\SmartFrameworkRegistry::ifDebug()) {
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-queries', 1, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|total-time', $time_end, '+');
			if(
				(stripos((string)trim((string)$query), 'BEGIN') === 0) OR
				(stripos((string)trim((string)$query), 'START TRANSACTION') === 0) OR
				(stripos((string)trim((string)$query), 'COMMIT') === 0) OR
				(stripos((string)trim((string)$query), 'ROLLBACK') === 0) OR
				(stripos((string)trim((string)$query), 'ABORT') === 0)
			) {
				\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
					'type' => 'transaction',
					'data' => 'Laminas-Dbal/'.$this->cfg['driver'].' [Query::EXECUTE.Transaction]',
					'query' => (string) $query,
					'params' => '',
					'time' => \Smart::format_number_dec($time_end, 9, '.', ''),
					'connection' => (string) $this->connkey
				]);
			} else {
				\SmartFrameworkRegistry::setDebugMsg('db', 'laminas-dbal/'.$this->cfg['driver'].'|log', [
					'type' => 'sql',
					'data' => 'Laminas-Dbal/'.$this->cfg['driver'].' [Query::EXECUTE]',
					'query' => (string) $query,
					'params' => '',
					'rows' => (int) $affected,
					'time' => \Smart::format_number_dec($time_end, 9, '.', ''),
					'connection' => (string) $this->connkey
				]);
			} //end if else
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	 public function enableProfiling() : bool {
		//--
		if(\SmartFrameworkRegistry::ifProdEnv()) {
			\Smart::log_warning(__METHOD__.' # The Profiling should be disabled in a Production Environment: Slow Performance');
			return false;
		} //end if
		//--
		if($this->profiler === null) {
			$this->profiler = new \Laminas\Db\Adapter\Profiler\Profiler();
			$this->connection->setProfiler($this->profiler);
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function getProfilingData() : array {
		//--
		if(\SmartFrameworkRegistry::ifProdEnv()) {
			\Smart::log_warning(__METHOD__.' # The Profiling should be disabled in a Production Environment: Slow Performance');
			return [];
		} //end if
		//--
		if($this->profiler === null) {
			return [];
		} //end if
		//--
		$arr = \Smart::json_decode((string)\Smart::json_encode((array)$this->profiler->getProfiles())); // convert all sub-objects to array
		if(!\is_array($arr)) {
			$arr = [];
		} //end if
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	/**
	 * Displays the Errors and HALT EXECUTION (This have to be a FATAL ERROR as it occur when a FATAL MySQLi ERROR happens or when a Query Syntax is malformed)
	 * PRIVATE
	 *
	 * @return :: HALT EXECUTION WITH ERROR MESSAGE
	 *
	 */
	private function error(?string $y_area, ?string $y_error_message, ?string $y_query, $y_params_or_title, ?string $y_warning='') : void {
		//--
		$driver = (string) $this->cfg['driver'];
		//--
		if($this->fatal_err === false) {
			throw new \Exception('#Laminas-Db@'.$this->connkey.'# :: Q# // '.$driver.' :: EXCEPTION :: '.$y_area."\n".$y_error_message);
			return;
		} //end if
		//--
		$err_log = $y_area."\n".'*** Error-Message: '.$y_error_message."\n".'*** Params:'."\n".\print_r($y_params_or_title,1)."\n".'*** Query:'."\n".$y_query;
		//--
		$def_warn = 'Execution Halted !';
		$y_warning = (string) \trim((string)$y_warning);
		if(\SmartFrameworkRegistry::ifDebug()) {
			$width = 750;
			$the_area = (string) $y_area;
			if((string)$y_warning == '') {
				$y_warning = (string) $def_warn;
			} //end if
			$the_error_message = 'Operation FAILED: '.$def_warn."\n".$y_error_message;
			if(\is_array($y_params_or_title)) {
				$the_params = '*** Params ***'."\n".\print_r($y_params_or_title, 1);
			} elseif((string)$y_params_or_title != '') {
				$the_params = '[ Reference Title ]: '.$y_params_or_title;
			} else {
				$the_params = '- No Params or Reference Title -';
			} //end if
			$the_query_info = (string) \trim((string)$y_query);
			if((string)$the_query_info == '') {
				$the_query_info = '-'; // query cannot e empty in this case (templating enforcement)
			} //end if
		} else {
			$width = 550;
			$the_area = '';
			$the_error_message = 'Operation FAILED: '.$def_warn;
			$the_params = '';
			$the_query_info = ''; // do not display query if not in debug mode ... this a security issue if displayed to public ;)
		} //end if else
		//--
		$out = (string) \SmartComponents::app_error_message(
			'Laminas-Db Client',
			(string) $driver,
			'SQL/DB',
			'Server',
			'modules/mod-dbal-laminas/libs/img/database.svg',
			(int)    $width, // width
			(string) $the_area, // area
			(string) $the_error_message, // err msg
			(string) $the_params, // title or params
			(string) $the_query_info // sql statement
		);
		//--
		\Smart::raise_error(
			'#Laminas-Db@'.$this->connkey.' :: Q# // '.$driver.' :: ERROR :: '.$err_log, // err to register
			(string) $out, // msg to display
			true // is html
		);
		die(''); // just in case
		return;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//--
/**
 *
 * @access 		private
 * @internal
 *
 */
function autoload__LaminasDbal_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if((\strpos($classname, '\\') === false) OR (!\preg_match('/^[a-zA-Z0-9_\\\]+$/', $classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(\strpos($classname, 'Laminas\\') === false) { // must start with this namespaces only
		return;
	} //end if
	//--
	$parts = (array) \explode('\\', $classname);
	//--
	$max = (int) \count($parts) - 1; // the last is the class
	if($max < 2) {
		return;
	} //end if
	//--
	$dir = 'modules/mod-dbal-laminas/libs/Laminas/';
	//--
	if(((string)$parts[1] == 'Db') OR ((string)$parts[1] == 'Stdlib')) {
		//--
		for($i=1; $i<$max; $i++) {
			if((string)$parts[$i] != '') {
				$dir .= (string) $parts[$i].'/';
			} //end if
		} //end for
		//--
	} else {
		//--
		return; // module not handled by this loader
		//--
	} //end if
	//--
	$dir  = (string) $dir;
	$file = (string) $parts[(int)$max];
	$path = (string) $dir.$file;
	$path = (string) \str_replace(array('\\', "\0"), array('', ''), $path); // filter out null byte and backslash
	//--
	if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in file
	} //end if
	//--
	if(!\is_file($path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once($path.'.php');
	//--
} //END FUNCTION
//--
\spl_autoload_register('\\SmartModExtLib\\DbalLaminas\\autoload__LaminasDbal_SFM', true, false); // throw / append
//--


// end of php code
