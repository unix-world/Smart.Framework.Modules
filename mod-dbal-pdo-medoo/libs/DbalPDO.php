<?php
// Medoo DBAL PDO adapter for Smart.Framework
// Module Library
// (c) 2006-2024 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\DbalPdoMedoo;

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
function autoload__DbalPDOMedoo_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)\substr((string)$classname, 0, 6) !== 'Medoo\\') { // if class name is not starting with Medoo
		return;
	} //end if
	//--
	$path = 'modules/mod-dbal-pdo-medoo/libs/'.\str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
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
\spl_autoload_register('\\SmartModExtLib\\DbalPdoMedoo\\autoload__DbalPDOMedoo_SFM', true, false); // throw / append
//--


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================

/**
 * Class: \SmartModExtLib\DbalPdoMedoo\DbalPDO - provides a general DBAL Database Adapter using Medoo PDO as a module for Smart.Framework that supports the following drivers such as: PDO(SQLite, MySQL, PostgreSQL).
 *
 * If you wish to use an PDO extended DBAL in your projects instead of the direct PDO supported directly by Smart.Framework this is a good solution.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP PDO ; vendor-classes: \Medoo\Medoo ; classes: Smart, SmartFileSysUtils, SmartEnvironment
 * @version 	v.20241216
 * @package 	modules:Database:PDO:Medoo-DBAL
 *
 */
final class DbalPDO {

	// ::

	private $conexion = null;

	public function __construct() {
		//--
		$this->conexion = null;
		//--
	} //END FUNCTION

	public function setup(string $type, array $options=[]) : ?\Medoo\Medoo {
		//--
		if($this->conexion) {
			\Smart::raise_error(__METHOD__.' Already have setup a connection ...');
			return null;
		} //end if
		//--
		switch((string)$type) {
			case 'sqlite':
				if(\Smart::array_size(\Smart::get_from_config('sqlite', 'array')) <= 0) {
					\Smart::raise_error(__METHOD__.' The CFG not found in Smart.Framework config (sqlite)');
					return null;
				} //end if
				$db_file = (string) ($options['db-file'] ?? null);
				if((string)$db_file != ':memory:') {
					if(!\SmartFileSysUtils::checkIfSafePath((string)$db_file, true, true)) { // deny absolute path access ; allow protected path access (starting with #)
						\Smart::raise_error(__METHOD__.' Unsafe or Empty Database Path (sqlite)');
						return null;
					} //end if
				} //end if
				$this->conexion = new \Medoo\Medoo([
					'database_type' => 'sqlite',
					'database_file' => (string) $db_file,
				]);
				break;
			case 'pgsql':
				if(\Smart::array_size($options) <= 0) {
					$arr_cfg = (array) \Smart::get_from_config('pgsql', 'array');
				} else {
					$arr_cfg = (array) $options;
				} //end if
				if(\Smart::array_size($arr_cfg) <= 0) {
					\Smart::raise_error(__METHOD__.' The CFG is Empty or not found in Smart.Framework config (pgsql)');
					return null;
				} //end if
				$options = [
					'database_type' => 'pgsql',
					'host' 			=> (string) ($arr_cfg['server-host'] ?? null),
					'port' 			=> (string) ((int)($arr_cfg['server-port'] ?? null)),
					'username' 		=> (string) ($arr_cfg['username'] ?? null),
					'password' 		=> (string) (isset($arr_cfg['password']) ? \base64_decode((string)$arr_cfg['password']) : ''),
					'database_name' => (string) ($arr_cfg['dbname'] ?? null),
					'charset' 		=> (string) \SMART_FRAMEWORK_SQL_CHARSET,
				];
				$this->conexion = new \Medoo\Medoo((array)$options);
				break;
			case 'mysql':
				if(\Smart::array_size($options) <= 0) {
					$arr_cfg = (array) \Smart::get_from_config('mysqli', 'array');
				} else {
					$arr_cfg = (array) $options;
				} //end if
				if(\Smart::array_size($arr_cfg) <= 0) {
					\Smart::raise_error(__METHOD__.' The CFG is Empty or not found in Smart.Framework config (mysqli)');
					return null;
				} //end if
				$options = [
					'database_type' => 'mysql',
					'host' 			=> (string) ($arr_cfg['server-host'] ?? null),
					'port' 			=> (string) ((int)($arr_cfg['server-port'] ?? null)),
					'username' 		=> (string) ($arr_cfg['username'] ?? null),
					'password' 		=> (string) (isset($arr_cfg['password']) ? \base64_decode((string)$arr_cfg['password']) : ''),
					'database_name' => (string) ($arr_cfg['dbname'] ?? null),
					'charset' 		=> (string) ($arr_cfg['charset'] ?? \SMART_FRAMEWORK_SQL_CHARSET),
				];
				$this->conexion = new \Medoo\Medoo((array)$options);
				break;
			case 'pdo':
				$options['pdo'] = $options['pdo'] ?? null;
				if(\is_null($options['pdo']) OR (!\is_object($options['pdo'])) OR (!($options['pdo'] instanceof \PDO))) {
					\Smart::raise_error(__METHOD__.' The PDO option must be an instance of the PDO object');
					return null;
				} //end if
				$this->conexion = new \Medoo\Medoo([
					'pdo' => (object) $options['pdo'],
				]);
				break;
			default:
				\Smart::raise_error(__METHOD__.' Already have setup a connection ...');
				return null;
		} //END FUNCTION
		//--
		return $this->conexion;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
