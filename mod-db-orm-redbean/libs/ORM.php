<?php
// Redbean ORM for Smart.Framework
// Module Library
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\DbOrmRedbean;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//--
/**
 *
 * @access 		private
 * @internal
 *
 */
function autoload__RedbeanOrm_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)\substr((string)$classname, 0, 11) !== 'RedBeanPHP\\') { // if class name is not starting with RedBeanPHP
		return;
	} //end if
	//--
	$path = 'modules/mod-db-orm-redbean/libs/'.\str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
	//--
	if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in path
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
\spl_autoload_register('\\SmartModExtLib\\DbOrmRedbean\\autoload__RedbeanOrm_SFM', true, false); // throw / append
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
 * //R::setup('mysqli:config'); // use the values from Smart.Framework config (mysqli)
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
 * @depends 	extensions: PHP Ctype, PHP PDO ; classes: \RedbeanOrm\Db
 * @version 	v.20191029
 * @package 	modules:Database:PDO:Redbean-ORM
 *
 */
final class ORM extends \RedBeanPHP\Facade {

	// ::

	private static $conexion = null;

	public static function setup($dsn=null, $username=null, $password=null, $frozen=true, $partialBeans=false) {
		//--
		if(!\function_exists('\\ctype_alnum')) {
			\Smart::raise_error(__METHOD__.'() # PHP Ctype extension is required but not found ...');
			return;
		} //end if
		//--
		if(self::$conexion) {
			\Smart::raise_error(__METHOD__.' Already have setup a connection ...');
			return false;
		} //end if
		//--
		switch((string)$dsn) {
			case 'mysqli:config':
				$arr_cfg = \Smart::get_from_config('mysqli');
				if(\Smart::array_size($arr_cfg) <= 0) {
					\Smart::raise_error(__METHOD__.' The DSN not found in Smart.Framework config (mysqli): '.$dsn);
					return false;
				} //end if
				$conex_str = (string) 'mysql:host='.$arr_cfg['server-host'].';port='.$arr_cfg['server-port'].';dbname='.$arr_cfg['dbname'];
				$conex_usr = (string) (string)$arr_cfg['username'];
				$conex_pwd = (string) ($arr_cfg['password'] ? \base64_decode((string)$arr_cfg['password']) : '');
				self::$conexion = parent::setup(
					(string) $conex_str,
					(string) $conex_usr,
					(string) $conex_pwd,
					(bool)   $frozen,
					(bool)   $partialBeans
				);
				break;
			case 'pgsql:config':
				$arr_cfg = \Smart::get_from_config('pgsql');
				if(\Smart::array_size($arr_cfg) <= 0) {
					\Smart::raise_error(__METHOD__.' The DSN not found in Smart.Framework config (pgsql): '.$dsn);
					return false;
				} //end if
				$conex_str = (string) 'pgsql:host='.$arr_cfg['server-host'].';port='.$arr_cfg['server-port'].';dbname='.$arr_cfg['dbname'].';connect_timeout='.(int)$arr_cfg['timeout'];
				$conex_usr = (string) $arr_cfg['username'];
				$conex_pwd = (string) ($arr_cfg['password'] ? \base64_decode((string)$arr_cfg['password']) : '');
				self::$conexion = parent::setup(
					(string) $conex_str,
					(string) $conex_usr,
					(string) $conex_pwd,
					(bool)   $frozen,
					(bool)   $partialBeans
				);
				break;
			default: // sqlite:tmp/sample-dbfile.sqlite OR custom DSN
				if((string)\trim((string)$dsn) == '') {
					\Smart::raise_error(__METHOD__.' Empty DSN not allowed !');
					return false;
				} //end if
				self::$conexion = parent::setup(
					(string) $dsn,
					$username ? (string)$username : null,
					$password ? (string)$password : null,
					(bool) $frozen,
					(bool) $partialBeans
				);
		} //end switch
		//--
		if(self::$conexion) {
			if(\SmartFrameworkRuntime::ifDebug()) {
				parent::debug(true, 1); //select mode 1 to suppress screen output
				\SmartDebugProfiler::register_extra_debug_log((string)__CLASS__, 'registerToDebugLog');
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
	public static function registerToDebugLog() {
		//--
		// this must be called at the end
		//--
		if(!\SmartFrameworkRuntime::ifDebug()) {
			return;
		} //end if
		//--
		$logger = parent::getDatabaseAdapter()->getDatabase()->getLogger();
		$logs = (array) $logger->getLogs();
		$logger->clear();
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
			if(\strpos($logs[$i+2], 'resultset: ') === 0) { // the 3rd column is optional
				$affected = (string) $logs[$i+2];
				if($affected) {
					$matches = array();
					\preg_match_all('/\d+/', (string)$affected, $matches);
					if(\is_array($matches[0])) {
						$affected = (string) \trim((string)$matches[0][0]);
					} else {
						$affected = '';
					} //end if else
				} //end if
				$i++;
			} //end if
			$i++;
			//--
			if((string)$query != '') {
				\SmartFrameworkRegistry::setDebugMsg('db', 'redbean-orm|log', [
					'type' => 'sql',
					'data' => 'Redbean-ORM [Query]',
					'query' => (string) $query,
					'params' => (array) $params,
					'rows' => (string) $affected,
					'time' => -1,
					'connection' => '@'
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


//end of php code
?>