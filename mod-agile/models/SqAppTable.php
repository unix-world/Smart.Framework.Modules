<?php
// Class: \SmartModDataModel\Agile\SqAppTable
// Author: unix-world.org

namespace SmartModDataModel\Agile;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


abstract class SqAppTable {

	// ->

private $ver = 'r.181115';
private $db = null;
private $sqdb = '#db/';
private $tblname = 'data_objects';
private $clsname = '';


final public function __construct() {
	//--
	$classname = (array) explode('\\', (string)get_class($this));
	$this->clsname = (string) strtolower((string)end($classname));
	$classname = null;
	if(((string)trim((string)$this->clsname) == '') OR (!preg_match('/^[a-z0-9]+$/', (string)$this->clsname))) {
		throw new \Exception(__METHOD__.': DB SQLITE Invalid characters in Class Name: '.$this->clsname);
		return;
	} //end if
	//--
	$this->sqdb .= 'agile-'.strtolower((string)$this->clsname).'.sqlite';
	if(\SmartFileSysUtils::check_if_safe_path((string)$this->sqdb, 'yes', 'yes') != 1) { // deny absolute path access ; allow protected path access (starting with #)
		throw new \Exception(__METHOD__.': DB SQLITE Invalid Path: '.$this->sqdb);
		return;
	} //end if
	//--
	$this->db = new \SmartSQliteDb((string)$this->sqdb);
	$this->db->open();
	//--
	if(!\SmartFileSystem::is_type_file((string)$this->sqdb)) {
		if($this->db instanceof \SmartSQliteDb) {
			$this->db->close();
		} //end if
		throw new \Exception(__METHOD__.': DB SQLITE File does NOT Exists !');
		return;
	} //end if
	//--
	if(!$this->initDBSchema()) {
		throw new \Exception(__METHOD__.': Failed to Initialize the Data Table !');
		return;
	} //end if
	//--
} //END FUNCTION


final public function __destruct() {
	//--
	if(!$this->db instanceof \SmartSQliteDb) {
		return;
	} //end if
	//--
	$this->db->close();
	//--
} //END FUNCTION


final public function getOneByUuid($uuid) {
	//--
	if(!$this->checkInstance()) {
		return array();
	} //end if
	//--
	$uuid = (string) trim((string)$uuid);
	if((string)$uuid == '') {
		return array();
	} //end if
	//--
	return (array) $this->db->read_asdata('SELECT * FROM `'.$this->tblname.'` WHERE (`uuid` = ?) ORDER BY `id` DESC LIMIT 1 OFFSET 0',
		[
			(string) $uuid
		]
	);
	//--
} //END FUNCTION


final public function getAllByUuid($limit=100) {
	//--
	if(!$this->checkInstance()) {
		return array();
	} //end if
	//--
	return (array) $this->db->read_adata(
		'SELECT * FROM `'.$this->tblname.'` GROUP BY `uuid` ORDER BY `id` DESC LIMIT '.(int)$limit.' OFFSET 0'
	);
	//--
} //END FUNCTION


final public function getNewUuid() {
	//--
	return (string) \Smart::uuid_10_seq().'-'.\Smart::uuid_10_num().'-'.\Smart::uuid_10_str(); // str 32 chars, very unique
	//--
} //END FUNCTION


final public function saveData($data, $user) {
	//--
	$data = (array) $data;
	//--
	$newdata = array();
	//--
	$newdata['uuid'] = (string) trim((string)$data['uuid']);
	if((string)$newdata['uuid'] == '') {
		return -1; // empty uuid
	} //end if
	//--
	$newdata['dtime'] = (string) date('Y-m-d H:i:s');
	$newdata['user'] = (string) trim((string)$user);
	$newdata['project'] = (string) trim((string)$newdata['project']);
	if(!$newdata['project']) {
		$newdata['project'] = 'Default Project';
	} //end if
	//--
	$newdata['title'] = (string) trim((string)$data['title']);
	if((string)$newdata['title'] == '') {
		return -2; // invalid title
	} //end if
	//--
	$newdata['saved_data'] = (string) trim((string)$data['saved_data']);
	if((string)$newdata['saved_data'] == '') {
		return -3; // invalid json
	} //end if
	//--
	$compare = (array) $this->getOneByUuid((string)$newdata['uuid']);
	if(((string)$compare['uuid'] === (string)$newdata['uuid']) AND ((string)$compare['title'] === (string)$newdata['title']) AND ((string)$compare['saved_data'] === (string)$newdata['saved_data'])) {
		return 1; // data not changed ...
	} //end if
	//--
	$wr = $this->db->write_data(
		'INSERT INTO `'.$this->tblname.'` '.$this->db->prepare_statement(
			(array) $newdata,
			'insert'
		)
	);
	//--
	return (int) $wr[1];
	//--
} //END FUNCTION


//##### PROTECTED


final protected function getDatabaseName() {
	//--
	return (string) $this->sqdb;
	//--
} //END FUNCTION


final protected function getTableName() {
	//--
	return (string) $this->tblname;
	//--
} //END FUNCTION


final protected function getConnection() {
	//--
	return $this->db; // mixed
	//--
} //END FUNCTION


final protected function checkInstance() {
	//--
	if(!$this->db instanceof \SmartSQliteDb) {
		throw new \Exception(__METHOD__.': Invalid DB Connection !');
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION


//##### PRIVATES


final private function initDBSchema() {
	//--
	if(!$this->checkInstance()) {
		return 0;
	} //end if
	//--
	if($this->db->check_if_table_exists((string)$this->tblname) != 1) {
		$this->db->write_data('BEGIN');
		$this->db->write_data((string)$this->getSqlSchema((string)$this->tblname)); // create table if not exists
		$this->db->write_data('INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (?, ?)', [ 'app-name', 'Mod.Agile' ]);
		$this->db->write_data('INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (?, ?)', [ 'app-version', (string)$this->ver ]);
		$this->db->write_data('INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (?, ?)', [ 'app-namespace', (string)$this->clsname ]);
		$this->db->write_data('COMMIT');
	} //end if
	if($this->db->check_if_table_exists((string)$this->tblname) != 1) {
		return 0; // if fail to create table, stop
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION


final private function getSqlSchema($table) {
//--
$table = (string) $table;
//--
$sql = <<<SQL
CREATE TABLE '{$table}' (
	'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	'dtime' character varying(23) NOT NULL,
	'uuid' character varying(32) NOT NULL,
	'user' character varying(96) NOT NULL,
	'project' character varying(64) NOT NULL,
	'title' character varying(255) NOT NULL,
	'saved_data' TEXT NOT NULL
);
CREATE INDEX 'idx_dtime' ON '{$table}' ('dtime');
CREATE INDEX 'idx_uuid' ON '{$table}' ('uuid');
CREATE INDEX 'idx_user' ON '{$table}' ('user');
CREATE INDEX 'idx_project' ON '{$table}' ('project');
SQL;
//--
return (string) $sql;
//--
} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>