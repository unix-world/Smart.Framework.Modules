<?php
// Redbean ORM for Smart.Framework
// Module Library
// (c) 2006-present unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\DbOrmRedbean;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//--
\spl_autoload_register(function(string $classname) : void {
	//--
	if((\strpos((string)$classname, '\\') === false) OR (!\preg_match('/^[a-zA-Z0-9_\\\]+$/', (string)$classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(\str_starts_with((string)$classname, 'RedBeanPHP\\') === false) { // if class name is starting with RedBeanPHP\
		return;
	} //end if
	//--
	$path = (string) \SmartFileSysUtils::getSmartFsRootPath().'modules/mod-db-orm-redbean/libs/'.\str_replace([ '\\', "\0" ], [ '/', '' ], (string)$classname);
	//--
	if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', (string)$path)) {
		return; // invalid path characters in path
	} //end if
	//--
	if(!\is_file((string)$path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once((string)$path.'.php');
	//--
}, true, false); // throw / append
//--


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================

/**
 * Class: \SmartModExtLib\DbOrmRedbean\ORM - provides a general ORM Database Adapter using ReadBean ORM as a module for Smart.Framework that supports the following drivers such as: SQlite, MySQLi, PostgreSQL and PDO(SQLite, MySQL, PostgreSQL).
 *
 * If you wish to use an ORM in your projects instead of the direct ones supported directly by Smart.Framework this is a good solution.
 *
 * <code>
 *
 * use \SmartModExtLib\DbOrmRedbean\ORM as R;
 *
 * R::setup('sqlite:tmp/redbean-dbfile.sqlite'); // use a custom sqlite database with relative path to the project folder (will be stored in {project-folder}/tmp/redbean-dbfile.sqlite)
 * //R::setup('mysql:config'); // use the values from Smart.Framework config (mysqli)
 * //R::setup('pgsql:config'); // use the values from Smart.Framework config (pgsql)
 *
 * R::freeze(false); // allow schema creation if not exists
 * $book = R::dispense('book');
 * $book->title = 'Mr.';
 * $book->author = 'Test RedBean';
 * $id = R::store($book); // write to db
 * R::freeze(true); // freeze schema creations
 *
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP Ctype, PHP PDO ; vendor-classes: \RedbeanOrm\Db ; classes: Smart, SmartFileSysUtils, SmartEnvironment
 * @version 	v.20260130
 * @package 	modules:Database:PDO:Redbean-ORM
 *
 */
final class ORM extends \RedBeanPHP\Facade {

	// ::

	private static $conexion = null;


	public static function setup(?string $dsn=null, ?string $username=null, ?string $password=null, bool $frozen=true, bool $partialBeans=false, array $pdo_options=[]) : ?\RedBeanPHP\ToolBox {
		//--
		if(!\function_exists('\\ctype_alnum')) {
			\Smart::raise_error(__METHOD__.'() # PHP Ctype extension is required but not found ...');
			return null;
		} //end if
		//--
		if(self::$conexion) {
			\Smart::raise_error(__METHOD__.' Already have setup a connection ...');
			return null;
		} //end if
		//--
		switch((string)$dsn) {
			case 'mysql:config':
				$arr_cfg = \Smart::get_from_config('mysqli');
				if(\Smart::array_size($arr_cfg) <= 0) {
					\Smart::raise_error(__METHOD__.' The DSN not found in Smart.Framework config (mysqli): '.$dsn);
					return null;
				} //end if
				$conex_str = (string) 'mysql:host='.($arr_cfg['server-host'] ?? null).';port='.((int)($arr_cfg['server-port'] ?? null)).';dbname='.($arr_cfg['dbname'] ?? null);
				$conex_usr = (string) ($arr_cfg['username'] ?? null);
				$conex_pwd = (string) (isset($arr_cfg['password']) ? \base64_decode((string)$arr_cfg['password']) : '');
				self::$conexion = parent::setup(
					(string) $conex_str, 	// DSN
					(string) $conex_usr, 	// username
					(string) $conex_pwd, 	// password
					(bool)   $frozen, 		// frozen
					(bool)   $partialBeans, // partial beans
					(array)  $pdo_options 	// additional (PDO) options
				);
				break;
			case 'pgsql:config':
				$arr_cfg = \Smart::get_from_config('pgsql');
				if(\Smart::array_size($arr_cfg) <= 0) {
					\Smart::raise_error(__METHOD__.' The DSN not found in Smart.Framework config (pgsql): '.$dsn);
					return null;
				} //end if
				$conex_str = (string) 'pgsql:host='.($arr_cfg['server-host'] ?? null).';port='.((int)($arr_cfg['server-port'] ?? null)).';dbname='.($arr_cfg['dbname'] ?? null).';connect_timeout='.((int)($arr_cfg['timeout'] ?? null).';sslmode=disable;gssencmode=disable');
				$conex_usr = (string) ($arr_cfg['username'] ?? null);
				$conex_pwd = (string) (isset($arr_cfg['password']) ? \base64_decode((string)$arr_cfg['password']) : '');
				self::$conexion = parent::setup(
					(string) $conex_str, 	// DSN
					(string) $conex_usr, 	// username
					(string) $conex_pwd, 	// password
					(bool)   $frozen, 		// frozen
					(bool)   $partialBeans, // partial beans
					(array)  $pdo_options 	// additional (PDO) options
				);
				break;
			default: // sqlite:tmp/sample-dbfile.sqlite OR custom DSN
				if((string)\trim((string)$dsn) == '') {
					\Smart::raise_error(__METHOD__.' Empty DSN not allowed !');
					return null;
				} //end if
				if(\strpos((string)$dsn, 'sqlite:') !== 0) {
					\Smart::raise_error(__METHOD__.' Invalid DSN: `'.$dsn.'`');
					return null;
				} //end if
				$the_db = (string) \trim((string)\substr((string)$dsn, 7));
				if((string)$the_db == '') {
					\Smart::raise_error(__METHOD__.' Empty Database Path (sqlite): '.$dsn);
					return null;
				} //end if
				if(!\SmartFileSysUtils::checkIfSafePath((string)$the_db, true, true)) { // deny absolute path access ; allow protected path access (starting with #)
					\Smart::raise_error(__METHOD__.' Unsafe Database Path (sqlite): '.$dsn);
					return null;
				} //end if
				if(\Smart::array_size(\Smart::get_from_config('sqlite')) <= 0) {
					\Smart::raise_error(__METHOD__.' The CFG not found in Smart.Framework config (sqlite): '.$dsn);
					return null;
				} //end if
				if((!defined('\\SMART_FRAMEWORK_FILESYSUTILS_ROOTPATH')) OR (!is_string(\SMART_FRAMEWORK_FILESYSUTILS_ROOTPATH))) {
					\Smart::raise_error(__METHOD__.' The SMART_FRAMEWORK_FILESYSUTILS_ROOTPATH was not defined or is not valid string in Smart.Framework (sqlite)');
					return null;
				} //end if
				self::$conexion = parent::setup(
					(string) 'sqlite:'.\SMART_FRAMEWORK_FILESYSUTILS_ROOTPATH.$the_db, 	// DSN
					null, 																// username
					null, 																// password
					(bool) $frozen, 													// frozen
					(bool) $partialBeans, 												// partial beans
					(array) $pdo_options 												// additional (PDO) options
				);
		} //end switch
		//--
		if(self::$conexion) {
			if(\SmartEnvironment::ifDebug()) {
				parent::debug(true, 1); //select mode 1 to suppress screen output
				if(\class_exists('\\SmartDebugProfiler')) {
					\SmartDebugProfiler::register_extra_debug_log((string)__CLASS__, 'registerToDebugLog');
				} //end if
			} //end if
		} //end if
		//--
		return self::$conexion;
		//--
	} //END FUNCTION


	/**
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function registerToDebugLog() : void {
		//--
		// this must be called at the end
		//--
		if(!\SmartEnvironment::ifDebug()) {
			return;
		} //end if
		//--
		$logger = parent::getDatabaseAdapter()->getDatabase()->getLogger();
		$logs = (array) $logger->getLogs();
		$logger->clear();
		//--
		$db_type = (string) parent::getDatabaseAdapter()->getDatabase()->getDatabaseType();
		//--
		for($i=0; $i<\Smart::array_size($logs); $i++) {
			$query = (string) \trim((string)$logs[$i]);
			$params = (string) \trim((string)$logs[$i+1]);
			if((string)$params != '') {
				//-- fix: the original class RedBeanPHP/Logger/RDefault.php uses var_export() ; fixed by unixman to do json_encode()
				/*
				if((\stripos((string)$params, 'array(') === 0) OR (\stripos((string)$params, 'array (') === 0)) {
					eval('$params = '.(string)$params.';');
				} //end if
				*/
				$params = \json_decode($params, true);
				//-- #end fix
				if(!\is_array($params)) {
					$params = array();
				} //end if
			} //end if
			$affected = '';
			if(\strpos((string)($logs[$i+2] ?? null), 'resultset: ') === 0) { // the 3rd column is optional
				$affected = (string) ($logs[$i+2] ?? null);
				if($affected) {
					$matches = array();
					\preg_match_all('/\d+/', (string)$affected, $matches);
					if(\is_array(($matches[0] ?? null))) {
						$affected = (string) \trim((string)($matches[0][0] ?? null));
					} else {
						$affected = '';
					} //end if else
				} //end if
				$i++;
			} //end if
			$i++;
			//--
			if((string)$query != '') {
				\SmartEnvironment::setDebugMsg('db', 'redbean-orm|log', [
					'type' => 'sql',
					'data' => 'Redbean-ORM [Query]',
					'query' => (string) $query,
					'params' => (array) $params,
					'rows' => (string) $affected,
					'time' => -1,
					'connection' => 'pdo:'.$db_type
				]);
			} //end if
			//--
		} //end for
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
