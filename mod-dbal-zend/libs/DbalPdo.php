<?php
// Zend Dbal for Smart.Framework
// Module Library
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\DbalZend;

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
 * Class: \SmartModExtLib\DbalZend\DbalPdo - provides a general PDO Database Adapter using Zend/Db as a module for Smart.Framework that supports the following PDO drivers such as: PDO SQLite, PDO MySQL, PDO PostgreSQL.
 *
 * If you wish to use the PDO drivers in your projects instead of the direct ones supported directly by Smart.Framework this is a good solution.
 *
 * <code>
 *
 * $conf_driver = [
 *		'driver'   => 'PDO_SQLITE',
 *		'database' => 'tmp/zend-test.sqlite'
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
 * $db = new \SmartModExtLib\DbalZend\DbalPdo((array)$conf_driver);
 * $adapter = $db->getConnection();
 *
 * // write test using the Zend DB Objects
 * $db->write_data('DROP TABLE IF EXISTS sf_zend_dbal_test', 'QUERY_MODE_EXECUTE');
 * $table = new \Zend\Db\Sql\Ddl\CreateTable('sf_zend_dbal_test');
 * $table->addColumn(new \Zend\Db\Sql\Ddl\Column\Integer('id'));
 * $table->addConstraint(new \Zend\Db\Sql\Ddl\Constraint\PrimaryKey('id'));
 * $table->addColumn(new \Zend\Db\Sql\Ddl\Column\Varchar('name', 100));
 * $table->addColumn(new \Zend\Db\Sql\Ddl\Column\Text('descr'));
 * $table->addColumn(new \Zend\Db\Sql\Ddl\Column\Integer('cnt'));
 * $sql = new \Zend\Db\Sql\Sql($adapter);
 * $adapter->query(
 * 		$sql->getSqlStringForSqlObject($table),
 * 		$adapter::QUERY_MODE_EXECUTE
 * );
 *
 * // read test using the Zend DB Objects
 * $sql = new \Zend\Db\Sql\Sql($adapter);
 * $select = $sql->select();
 * $select->from('sf_zend_dbal_test');
 * $select->where(array('id' => 1));
 * $sqlstr = (string) $sql->getSqlStringForSqlObject($select);
 * $results = (array) $adapter->query($sqlstr, [])->toArray();
 *
 * // tests using Smart.Framework DB compatibility: count, read, write
 * $db->write_data('DELETE FROM sf_zend_dbal_test');
 * $count = $db->count_data('SELECT COUNT(1) FROM sf_zend_dbal_test');
 * $results = $db->read_adata( // get many rows 0..n [field1, field2, ..., fieldn]
 * 		'SELECT * FROM sf_zend_dbal_test WHERE id > ?', // id > 0
 * 		[ 0 ]
 * );
 * $results = $db->read_asdata( // get just one row [field1, field2, ..., fieldn]
 * 		'SELECT * FROM sf_zend_dbal_test WHERE id > ? LIMIT 1 OFFSET 0', // id > 1
 * 		[ 1 ]
 * );
 *
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP PDO ; classes: \Zend\Db
 * @version 	v.20210429
 * @package 	modules:Database:PDO:Zend-Dbal
 *
 */
final class DbalPdo {

	// ->

	private $zend_db_version = 'Zend/Db 2.11.0.uxm.1 ; Zend/Stdlib 3.2.1';

	private $cfg = array();
	private $connkey = '';
	private $connection = null;
	private $profiler = null;
	private $platform = null;

	private $timeout_conn = 0;
	private $slow_query_time = 0;


	/**
	 * Class Constructor for using Zend/DB with a custom driver and options, using PDO, providing a compatible layer to use a project with any of PDO/PgSQL, PDO/SQLite and PDO/MySQL.
	 * The utility of this DB Driver is to serve the missing cross-db PDO Drivers support from Smart.Framework such as PDO/PgSQL, PDO/SQLite and PDO/MySQL to support a cross-DB project.
	 * The Zend/DB provides a unified query parameters implementation using PDO and is not 100% compatible with the queries written for the direct drivers supplied by Smart.Framework when parameters are used (PostgreSQL, MySQLi, SQLite and other Non-PDO drivers) because the parameter mode can differ from driver to driver (Ex: ? :param $# ...)
	 *
	 * @hint: 	For the DB direct access Drivers (Adapters) such as: PgSQL, SQlite3 and MySQLi the Smart.Framework provides built-in / includded and more optimized libraries
	 *
	 * @param MIXED 	$cfg							:: the driver options as array[] or 'mysqli:config' | 'pgsql:config' | 'sqlite:tmp/sample-dbfile.sqlite'
	 * @param INTEGER+ 	$timeout 						:: the connection timeout (applies only to MySQL and PostgreSQL)
	 *
	 */
	public function __construct($cfg, $timeout=30) {
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
					$this->cfg['driver'] 		= 'PDO_MYSQL';
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
					$this->cfg['driver'] 		= 'PDO_PGSQL';
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
					$this->cfg['driver'] 		= 'PDO_SQLITE';
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
				$this->error('INIT', 'Unsupported PDO Zend/Db Driver: '.$this->cfg['driver'], '@Driver', '');
				return;
		} //end switch
		//--
		$this->cfg['charset'] = (string) \SMART_FRAMEWORK_SQL_CHARSET;
		$this->cfg['options'] = (array) ((isset($this->cfg['options']) && \is_array($this->cfg['options'])) ? $this->cfg['options'] : []);
		$this->cfg['options']['buffer_results'] = true;
		//--
		$this->connkey = (string) (isset($this->cfg['driver']) ? $this->cfg['driver'] : '').'*'.(isset($this->cfg['host']) ? $this->cfg['host'] : '').':'.(isset($this->cfg['port']) ? $this->cfg['port'] : '').'@'.(isset($this->cfg['database']) ? $this->cfg['database'] : '').'#'.(isset($this->cfg['username']) ? $this->cfg['username'] : '');
		//--
		$this->connection = new \Zend\Db\Adapter\Adapter((array)$this->cfg); // lazy connection, does not throw here (will connect on first query)
		//--
		$this->platform = $this->connection->getPlatform();
		//--
		if(\SmartFrameworkRegistry::ifDebug()) {
			$this->profiler = new \Zend\Db\Adapter\Profiler\Profiler();
			$this->connection->setProfiler($this->profiler);
		} //end if
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
	} //END FUNCTION


	/**
	 * Zend/DB: Get the Current Driver
	 *
	 * @return STRING
	 */
	public function getDriver() {
		//--
		return (string) $this->cfg['driver'];
		//--
	} //END FUNCTION


	/**
	 * Zend/DB: Get the Connection
	 *
	 * @return OBJECT
	 */
	public function getConnection() {
		//--
		return $this->connection;
		//--
	} //END FUNCTION


	/**
	 * Zend/DB: Get the Platform
	 *
	 * @return OBJECT
	 */
	public function getPlatform() {
		//--
		return $this->platform;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Count
	 * This function is intended to be used for count type queries: SELECT COUNT().
	 *
	 * @param STRING $queryval						:: the query
	 * @param STRING $values 						:: *optional* array of parameters
	 * @return INTEGER								:: the result of COUNT()
	 */
	public function count_data($query, $values='') {
		//--
		if(!\is_array($values)) {
			$values = array();
		} //end if
		//--
		$arr = array();
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('COUNT-DATA', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
		} //end try catch
		//--
		$count = 0;
		if(\is_array($arr[0])) {
			foreach($arr[0] as $key => $val) {
				$count = (int) $val; // find first row and first column value
				break;
			} //end if
		} //end if
		//--
		return (int) $count;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Read (Non-Associative) one or multiple rows.
	 * This function is intended to be used for read type queries: SELECT.
	 *
	 * @param STRING $queryval						:: the query
	 * @param STRING $values 						:: *optional* array of parameters
	 * @return ARRAY (non-asociative) of results	:: array('column-0-0', 'column-0-1', ..., 'column-0-n', 'column-1-0', 'column-1-1', ... 'column-1-n', ..., 'column-m-0', 'column-m-1', ..., 'column-m-n')
	 */
	public function read_data($query, $values='') {
		//--
		if(!\is_array($values)) {
			$values = array();
		} //end if
		//--
		$arr = array();
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('READ-DATA', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
		} //end try catch
		//--
		$data = array();
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			$arr[$i] = (array) $arr[$i];
			foreach($arr[$i] as $key => $val) {
				$data[] = (string) $val;
			} //end foreach
		} //end for
		//--
		return (array) $data;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Read (Associative) one or multiple rows.
	 * This function is intended to be used for read type queries: SELECT.
	 *
	 * @param STRING $queryval						:: the query
	 * @param STRING $values 						:: *optional* array of parameters
	 * @return ARRAY (asociative) of results		:: array(0 => array('column1', 'column2', ... 'column-n'), 1 => array('column1', 'column2', ... 'column-n'), ..., m => array('column1', 'column2', ... 'column-n'))
	 */
	public function read_adata($query, $values='') {
		//--
		if(!\is_array($values)) {
			$values = array();
		} //end if
		//--
		$arr = array();
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('READ-aDATA', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
		} //end try catch
		//--
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			$arr[$i] = (array) $arr[$i];
			foreach($arr[$i] as $key => $val) {
				$arr[$i][(string)$key] = (string) $val;
			} //end foreach
		} //end for
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Read (Associative) - Single Row (just for 1 row, to easy the use of data from queries).
	 * !!! This will raise an error if more than one row(s) are returned !!!
	 * This function does not support multiple rows because the associative data is structured without row iterator.
	 * For queries that return more than one row use: read_adata() or read_data().
	 * This function is intended to be used for read type queries: SELECT.
	 *
	 * @hints	ALWAYS use a LIMIT 1 OFFSET 0 with all queries using this function to avoid situations that will return more than 1 rows and will raise ERROR with this function.
	 *
	 * @param STRING $queryval						:: the query
	 * @param STRING $values 						:: *optional* array of parameters
	 * @return ARRAY (asociative) of results		:: Returns just a SINGLE ROW as: array('column1', 'column2', ... 'column-n')
	 */
	public function read_asdata($query, $values='') {
		//--
		if(!\is_array($values)) {
			$values = array();
		} //end if
		//--
		$arr = array();
		try {
			$arr = (array) $this->connection->query(
				$query,
				$values
			)->toArray();
		} catch(\Exception $e) {
			$this->error('READ-asDATA', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values) > 0) ? (array)$values : '');
		} //end try catch
		//--
		if(\Smart::array_size($arr) > 1) {
			throw new \Exception('The Result contains more than one row ...');
		} //end if
		//--
		if(!\is_array($arr[0])) {
			$arr[0] = array();
		} //end if
		//--
		foreach($arr[0] as $key => $val) {
			$arr[0][(string)$key] = (string) $val;
		} //end foreach
		//--
		return (array) $arr[0];
		//--
	} //END FUNCTION


	/**
	 * PDO Query :: Write.
	 * This function is intended to be used for write type queries: BEGIN (TRANSACTION) ; COMMIT ; ROLLBACK ; INSERT ; INSERT IGNORE ; REPLACE ; UPDATE ; CREATE SCHEMAS ; CALLING STORED PROCEDURES ...
	 *
	 * @param STRING $queryval						:: the query
	 * @param STRING $values_or_mode 				:: *optional* ARRAY of parameters OR the PDO query execution mode as STRING (QUERY_MODE_EXECUTE, implementing \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)
	 * @return ARRAY 								:: [0 => 'control-message', 1 => #affected-rows]
	 */
	public function write_data($query, $values_or_mode='') {
		//--
		if((!\is_array($values_or_mode)) AND ((string)\strtoupper((string)$values_or_mode) == 'QUERY_MODE_EXECUTE')) {
			$values_or_mode = \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE;
		} elseif(!\is_array($values_or_mode)) {
			$values_or_mode = array();
		} //end if
		//--
		$affected = 0;
		try {
			$affected = $this->connection->query(
				$query,
				$values_or_mode
			)->getAffectedRows();
		} catch(\Exception $e) {
			$this->error('WRITE-DATA', (string)$e->getMessage(), (string)$query, (\Smart::array_size($values_or_mode) > 0) ? (array)$values_or_mode : (array)['@flag' => $values_or_mode]);
		} //end try catch
		//--
		return (int) $affected;
		//--
	} //END FUNCTION


	/**
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function __destruct() {
		//--
		if(!\SmartFrameworkRegistry::ifDebug()) {
			return;
		} //end if
		if(!$this->profiler) {
			return;
		} //end if
		//--
		$arr = (array) $this->profiler->getProfiles();
		if(\Smart::array_size($arr) <= 0) {
			return;
		} //end if
		//--
		$driver = (string) $this->cfg['driver'];
		//--
		\SmartFrameworkRegistry::setDebugMsg('db', 'zend-db/'.$driver.'|log', [
			'type' => 'metainfo',
			'data' => 'Database Server: SQL ('.$driver.') / App Connector Version: '.$this->zend_db_version.' / Connection Charset: '.\SMART_FRAMEWORK_SQL_CHARSET
		]);
		\SmartFrameworkRegistry::setDebugMsg('db', 'zend-db/'.$driver.'|log', [
			'type' => 'metainfo',
			'data' => 'Connection Timeout: default / Fast Query Reference Time < '.$this->slow_query_time.' seconds'
		]);
		\SmartFrameworkRegistry::setDebugMsg('db', 'zend-db/'.$driver.'|log', [
			'type' => 'open-close',
			'data' => 'DB Connection: '.$this->connkey,
			'connection' => (string) \sha1((string)\print_r($this->cfg,1))
		]);
		//--
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			//--
			$arr[$i] = (array) $arr[$i];
			foreach($arr[$i] as $key => $val) {
				if((string)$key == 'parameters') {
					if((\is_object($val)) AND ($val instanceof \Zend\Db\Adapter\ParameterContainer)) {
						$arr[$i][(string)$key] = (array) $val->getNamedArray();
					} else {
						$arr[$i][(string)$key] = array();
					} //end if else
				} //end if
			} //end foreach
			//--
			\SmartFrameworkRegistry::setDebugMsg('db', 'zend-db/'.$driver.'|slow-time', \number_format((float)$this->slow_query_time, 7, '.', ''), '=');
			\SmartFrameworkRegistry::setDebugMsg('db', 'zend-db/'.$driver.'|total-queries', 1, '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'zend-db/'.$driver.'|total-time', (float)$arr[$i]['elapse'], '+');
			\SmartFrameworkRegistry::setDebugMsg('db', 'zend-db/'.$driver.'|log', [
				'type' => 'sql',
				'data' => 'Zend-Db/'.$driver.' [Query]',
				'query' => (string) $arr[$i]['sql'],
				'params' => (\Smart::array_size($arr[$i]['parameters']) > 0) ? (array) $arr[$i]['parameters'] : '',
				'time' => \Smart::format_number_dec((float)$arr[$i]['elapse'], 9, '.', ''),
				'connection' => (string) $this->connkey
			]);
			//--
		} //end for
		//--
	} //END FUNCTION


	/**
	 * Displays the Errors and HALT EXECUTION (This have to be a FATAL ERROR as it occur when a FATAL MySQLi ERROR happens or when a Query Syntax is malformed)
	 * PRIVATE
	 *
	 * @return :: HALT EXECUTION WITH ERROR MESSAGE
	 *
	 */
	private function error($y_area, $y_error_message, $y_query, $y_params_or_title, $y_warning='') {
		//--
		$driver = (string) $this->cfg['driver'];
		//--
		if(\defined('\\SMART_SOFTWARE_SQLDB_FATAL_ERR') AND (\SMART_SOFTWARE_SQLDB_FATAL_ERR === false)) {
			throw new \Exception('#Zend-Db@'.$this->connkey.'# :: Q# // '.$driver.' :: EXCEPTION :: '.$y_area."\n".$y_error_message);
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
			'Zend-Db Client',
			(string) $driver,
			'SQL/DB',
			'Server',
			'modules/mod-dbal-zend/libs/img/database.svg',
			(int)    $width, // width
			(string) $the_area, // area
			(string) $the_error_message, // err msg
			(string) $the_params, // title or params
			(string) $the_query_info // sql statement
		);
		//--
		\Smart::raise_error(
			'#Zend-Db@'.$this->connkey.' :: Q# // '.$driver.' :: ERROR :: '.$err_log, // err to register
			$out, // msg to display
			true // is html
		);
		die(''); // just in case
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
function autoload__ZendDbal_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if((\strpos($classname, '\\') === false) OR (!\preg_match('/^[a-zA-Z0-9_\\\]+$/', $classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(\strpos($classname, 'Zend\\') === false) { // must start with this namespaces only
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
	$dir = 'modules/mod-dbal-zend/libs/Zend/';
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
\spl_autoload_register('\\SmartModExtLib\\DbalZend\\autoload__ZendDbal_SFM', true, false); // throw / append
//--


// end of php code
