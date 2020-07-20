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


	public static function getDbCollectionIndexes(string $collection) {
		//--
		$collection = (string) \trim((string)$collection);
		if((string)$collection == '') {
			\Smart::log_warning(__METHOD__.'() MongoDB Collection Name is Empty ...');
			return array();
		} //end if
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


	public static function getRecordsCount(string $collection, array $query=[]) {
		//--
		$collection = (string) \trim((string)$collection);
		if((string)$collection == '') {
			\Smart::log_warning(__METHOD__.'() MongoDB Collection Name is Empty ...');
			return 0;
		} //end if
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


	public static function getRecordsData(string $collection, array $query=[], $offset=0, $limit=10, $sorting=[]) {
		//--
		$collection = (string) \trim((string)$collection);
		if((string)$collection == '') {
			\Smart::log_warning(__METHOD__.'() MongoDB Collection Name is Empty ...');
			return array();
		} //end if
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


	public static function getRealMongoId(string $id) {
		//--
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			return '';
		} //end if
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return (string) $id;
		} //end if
		//--
		if((\strpos((string)$id, 'ObjectId(') === 0) AND ((string)\substr((string)$id, -1, 1) == ')')) { // try to convert to MongoDB ObjectId
			$objMongoId = $mongo->getObjectId((string)\substr((string)$id, 9, -1)); // return mixed
		} else { // preserve as string
			$objMongoId = (string) $id;
		} //end if
		//--
		return $objMongoId; // MIXED: string or object
		//--
	} //END FUNCTION


	public static function getRecord(string $collection, string $id) {
		//--
		$collection = (string) \trim((string)$collection);
		if((string)$collection == '') {
			\Smart::log_warning(__METHOD__.'() MongoDB Collection Name is Empty ...');
			return array();
		} //end if
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return array();
		} //end if
		//--
		if((string)\trim((string)$id) == '') {
			return array();
		} //end if
		$id = self::getRealMongoId($id); // mixed
		if(!$id) {
			return array();
		} //end if
		//--
		$result = (array) $mongo->findone(
			(string) $collection,
			[
				'_id' => $id // mixed
			] // filter
		);
		//--
		return (array) $result;
		//--
	} //END FUNCTION


	public static function insertRecord(string $collection, array $doc) {
		//--
		$collection = (string) \trim((string)$collection);
		if((string)$collection == '') {
			\Smart::log_warning(__METHOD__.'() MongoDB Collection Name is Empty ...');
			return 'No Collection Selected';
		} //end if
		//--
		if(!\is_array($doc)) {
			return 'Document Data is NOT Array';
		} //end if
		if(\array_key_exists('_id', (array)$doc)) {
			unset($doc['_id']);
		} //end if
		//--
		if(\Smart::array_size($doc) <= 0) {
			return 'Empty Document';
		} //end if
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return 'MongoDB Instance is N/A';
		} //end if
		//--
		$doc['_id'] = (string) $mongo->assign_uuid();
		//--
		$result = array();
		try {
			$result = (array) $mongo->insert(
				(string) $collection,
				(array)  $doc
			);
		} catch(\Exception $err) {
			return 'Insert EXCEPTION: '.$err->getMessage();
		} //end try catch
		//--
		if($result[1] != 1) {
			return 'Insert FAILED: ['.$result[1].']';
		} //end if
		//--
		return 'OK';
		//--
	} //END FUNCTION


	public static function modifyRecord(string $collection, string $id, array $doc) {
		//--
		$collection = (string) \trim((string)$collection);
		if((string)$collection == '') {
			\Smart::log_warning(__METHOD__.'() MongoDB Collection Name is Empty ...');
			return 'No Collection Selected';
		} //end if
		//--
		if((string)\trim((string)$id) == '') {
			return 'Empty Record UID';
		} //end if
		//--
		if(!\is_array($doc)) {
			return 'Document Data is NOT Array';
		} //end if
		if(\array_key_exists('_id', (array)$doc)) {
			unset($doc['_id']); // this must not be updated
		} //end if
		//--
		if(\Smart::array_size($doc) <= 0) {
			return 'Empty Document';
		} //end if
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return 'MongoDB Instance is N/A';
		} //end if
		//--
		$id = self::getRealMongoId($id); // mixed
		if(!$id) {
			return 'Invalid Record UID';
		} //end if
		//--
		$result = array();
		try {
			$result = (array) $mongo->update(
				(string) $collection,
				(array) [ '_id' => $id ], // filter
				(array) [ 0 => (array) $doc ] // replace
			);
		} catch(\Exception $err) {
			return 'Update EXCEPTION: '.$err->getMessage();
		} //end try catch
		//--
		if($result[1] != 1) {
			return 'Update FAILED: ['.$result[1].']';
		} //end if
		//--
		return 'OK';
		//--
	} //END FUNCTION


	public static function deleteRecord(string $collection, string $id) {
		//--
		$collection = (string) \trim((string)$collection);
		if((string)$collection == '') {
			\Smart::log_warning(__METHOD__.'() MongoDB Collection Name is Empty ...');
			return 'No Collection Selected';
		} //end if
		//--
		$mongo = self::getInstance();
		if(!$mongo) {
			\Smart::log_warning(__METHOD__.'() MongoDB Instance is not available ...');
			return 'MongoDB Instance is N/A';
		} //end if
		//--
		if((string)\trim((string)$id) == '') {
			return 'Empty Record UID';
		} //end if
		$id = self::getRealMongoId($id); // mixed
		if(!$id) {
			return 'Invalid Record UID';
		} //end if
		//--
		$result = (array) $mongo->delete(
			(string) $collection,
			[
				'_id' => $id // mixed
			] // filter
		);
		//--
		if($result[1] != 1) {
			return 'Delete FAILED: ['.$result[1].']';
		} //end if
		//--
		return 'OK';
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
