<?php
// Class: \SmartModDataModel\DbAdmin\MongoDbAdmin
// Type: Module Data Model: DbAdmin / MongoDB Admin
// Info: this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

namespace SmartModDataModel\DbAdmin;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

final class MongoDbAdmin {

	// ::

	private static $mongo = null;
	private static $config = [];


	public static function getServerBuildInfo() {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return array();
		} //end if
		//--
		$result = (array) $mongo->command(
			[
				'buildInfo' => 1
			]
		);
		//--
		if(!$mongo->is_command_ok($result)) {
			return array();
		} //end if
		//--
		$result = (array) $result[0];
		unset($result['ok']);
		//--
		return (array) $result;
		//--
	} //END FUNCTION


	public static function getDbName() {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return '';
		} //end if
		//--
		return (string) self::$config['dbname'];
		//--
	} //END FUNCTION


	public static function getDbHost() {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return '';
		} //end if
		//--
		return (string) self::$config['server-host'];
		//--
	} //END FUNCTION


	public static function getDbPort() {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return '';
		} //end if
		//--
		return (string) self::$config['server-port'];
		//--
	} //END FUNCTION


	public static function getDbCollections() {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return array();
		} //end if
		//--
		$result = (array) $mongo->command(
			[
				'listCollections' => 1 // 'listDatabases' => 1 (to get databases list ; req to be connected to `admin` db)
			]
		);
		//--
		return (array) $result;
		//--
	} //END FUNCTION


	public static function getDbCollectionIndexes($collection) {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return array();
		} //end if
		//--
		$result = (array) $mongo->command(
			[
				'listIndexes' => (string) $collection
			]
		);
		//--
		return (array) $result;
		//--
	} //END FUNCTION


	public static function getRecordsCount($collection, $query) {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return 0;
		} //end if
		//--
		return (int) $mongo->count(
			(string) $collection,
			(array)  $query // filter
		);
		//--
	} //END FUNCTION


	public static function getRecordsData($collection, $query, $offset=0, $limit=10, $sorting=[]) {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return array();
		} //end if
		//--
		$arrOptions = [
			'limit' => (int) $limit, // limit
			'skip' 	=> (int) $offset // offset
		];
		//--
		if(\Smart::array_type_test($sorting) == 2) {
			foreach($sorting as $key => $val) {
				$key = (string) trim((string)$key);
				$val = (string) strtoupper((string)trim((string)$val));
				if((string)$key != '') {
					if($val === 'DESC') {
						$val = -1;
					} else {
						$val = 1;
					} //end if else
					$arrOptions['sort'][(string)$key] = (int) $val;
				} //end if
			} //end foreach
		} //end if
		//--
		return (array) $mongo->find(
			(string) $collection,
			(array)  $query, // filter
			(array)  [],     // no projection
			(array)  $arrOptions
		);
		//--
	} //END FUNCTION


	final public static function insertRecord($collection, $doc) {
		//--
		$doc = \Smart::json_decode((string)$doc);
		if(\Smart::array_size($doc) <= 0) {
			return array(
				'smart@error' => 'Empty Document'
			);
		} //end if
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return array();
		} //end if
		//--
		$result = array();
		try {
			$result = (array) $mongo->insert(
				(string) $collection,
				(array)  $doc
			);
		} catch(\Exception $err) {
			$result = array(
				'smart@error' => 'Exception: '.$err->getMessage()
			);
		} //end try catch
		//--
		return (array) $result;
		//--
	} //END FUNCTION


	final public static function deleteRecord($collection, $id) {
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return array();
		} //end if
		//--
		$result = (array) $mongo->delete(
			(string) $collection,
			[
				'_id' => (string) $id
			] // filter
		);
		//--
		return (array) $result;
		//--
	} //END FUNCTION


	//===== PRIVATES


	private static function getInstance() {
		//--
		$cfg = \Smart::get_from_config('mongodb');
		if(\Smart::array_size($cfg) <= 0) {
			\Smart::raise_error(__METHOD__.'() MongoDB Config is not available ...');
			return null;
		} //end if
		self::$config = (array) $cfg;
		//--
		if(self::$mongo === null) {
			try {
				self::$mongo = new \SmartMongoDb((array)self::$config, false); // non-fatal errors !!
			} catch(\Exception $e) {
				return null;
			}
		} //end if
		//--
		return self::$mongo; // mixed
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
