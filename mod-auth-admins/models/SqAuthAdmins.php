<?php
// Class: \SmartModDataModel\AuthAdmins\SqAuthAdmins
// (c) 2006-2018 unix-world.org - all rights reserved

namespace SmartModDataModel\AuthAdmins;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


final class SqAuthAdmins {

	// ->
	// v.181109

	private $db;


	public function __construct() {
		//--
		if(!defined('APP_AUTH_DB_SQLITE')) {
			throw new \Exception('AUTH DB SQLITE is NOT Defined !');
			return;
		} //end if
		//--
		$this->db = new \SmartSQliteDb((string)APP_AUTH_DB_SQLITE);
		$this->db->open();
		//--
		if(!\SmartFileSystem::is_type_file((string)APP_AUTH_DB_SQLITE)) {
			if($this->db instanceof \SmartSQliteDb) {
				$this->db->close();
			} //end if
			throw new \Exception('AUTH DB SQLITE File does NOT Exists !');
			return;
		} //end if
		//--
		$this->initDBSchema(); // create default schema if not exists (and a default account)
		//--
	} //END FUNCTION


	public function __destruct() {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			return;
		} //end if
		//--
		$this->db->close();
		//--
	} //END FUNCTION


	public function getLoginData($auth_user_name, $auth_user_hash_pass) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return array();
		} //end if
		//--
		return (array) $this->db->read_asdata(
			'SELECT * FROM "admins" WHERE (("id" = ?) AND ("pass" = ?) AND ("active" = 1)) LIMIT 1 OFFSET 0',
			array($auth_user_name, $auth_user_hash_pass)
		);
		//--
	} //END FUNCTION


	public function getById($id) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return array();
		} //end if
		//--
		return (array) $this->db->read_asdata(
			'SELECT * FROM "admins" WHERE ("id" = ?) LIMIT 1 OFFSET 0',
			array((string)$id)
		);
		//--
	} //END FUNCTION


	public function countByFilter($id='') {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return 0;
		} //end if
		//--
		$where = '';
		$params = '';
		//--
		if((string)$id != '') {
			$where = ' WHERE ("id" = ?)';
			$params = array($id);
		} //end if else
		//--
		return (int) $this->db->count_data(
			'SELECT COUNT(1) FROM "admins"'.$where,
			$params
		);
		//--
	} //END FUNCTION


	public function getListByFilter($fields=array(), $limit=10, $ofs=0, $sortby='id', $sortdir='ASC', $id='') {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return array();
		} //end if
		//--
		if(\Smart::array_size($fields) > 0) {
			$tmp_arr = (array) $fields;
			$fields = array();
			for($i=0; $i<\Smart::array_size($tmp_arr); $i++) {
				$fields[] = '"'.$tmp_arr[$i].'"';
			} //end for
			unset($tmp_arr);
			$fields = (string) implode(', ', (array) $fields);
		} else {
			$fields = '*';
		} //end if else
		//--
		$limit = ' LIMIT '.\Smart::format_number_int($limit,'+').' OFFSET '.\Smart::format_number_int($ofs,'+');
		$where = '';
		$params = '';
		//--
		if((string)$id != '') {
			$limit = ' LIMIT 1 OFFSET 0';
			$where = ' WHERE ("id" = ?)';
			$params = array($id);
		} //end if else
		//--
		$sortby = strtolower(trim((string)$sortby));
		switch((string)$sortby) {
			case 'active':
			case 'email':
			case 'name_f':
			case 'name_l':
			case 'modif':
				// OK
				break;
			case 'id':
			default:
				$sortby = 'id';
		} //end switch
		//--
		$sortdir = strtoupper((string)$sortdir);
		if((string)$sortdir != 'DESC') {
			$sortdir = 'ASC';
		} //end if
		//--
		return (array) $this->db->read_adata(
			'SELECT '.$fields.' FROM "admins"'.$where.' ORDER BY "'.$sortby.'" '.$sortdir.$limit,
			$params
		);
		//--
	} //END FUNCTION


	public function insertAccount($data) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return -100;
		} //end if
		//--
		$data = (array) $data;
		$data['id'] = (string) \Smart::safe_username((string)trim((string)$data['id']));
		$data['pass'] = (string) trim((string)$data['pass']);
		$data['email'] = (string) trim((string)$data['email']);
		//--
		if((strlen((string)$data['id']) < 3) OR (strlen((string)$data['id']) > 25)) {
			return -10; // invalid username length
		} //end if
		if(!preg_match('/^[a-z0-9\.]+$/', (string)$data['id'])) {
			return -11; // invalid characters in username
		} //end if
		if(!preg_match('/^[a-f0-9]+$/', (string)$data['pass'])) {
			return -12; // invalid password, must be hex hash
		} //end if
		if(strlen((string)$data['pass']) != 128) {
			return -13; // invalid password, must be sha512 (128 chars)
		} //end if
		//-- {{{SYNC-MOD-AUTH-EMAIL-VALIDATION}}}
		if((strlen((string)$data['email']) < 6) OR (strlen((string)$data['email']) > 96) OR (!preg_match((string)\SmartValidator::regex_stringvalidation_expression('email'), (string)$data['email']))) {
			$data['email'] = null; // NULL, as the email is invalid
		} //end if
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		$check_id = (array) $this->db->read_asdata(
			'SELECT "id" FROM "admins" WHERE ("id" = ?) LIMIT 1 OFFSET 0',
			array((string)$data['id'])
		);
		if($data['email'] === null) {
			$check_eml = array();
		} else {
			$check_eml = (array) $this->db->read_asdata(
				'SELECT "id" FROM "admins" WHERE ("email" = ?) LIMIT 1 OFFSET 0',
				array((string)$data['email'])
			);
		} //end if else
		if(\Smart::array_size($check_id) > 0) {
			$out = -2; // duplicate ID
		} elseif(\Smart::array_size($check_eml) > 0) {
			$out = -3; // duplicate email
		} else {
			$wr = (array) $this->db->write_data(
				'INSERT INTO "admins" '.$this->db->prepare_statement(
					[
						'id' 		=> (string) $data['id'],
						'pass' 		=> (string) $data['pass'], // pass should be already a hash to avoid send it unsecure !!
						'email' 	=>          $data['email'], // mixed: false (NULL) or string
						'name_f' 	=> (string) $data['name_f'],
						'name_l' 	=> (string) $data['name_l'],
						'created' 	=> time(),
						'active' 	=> '0'
					],
					'insert'
				)
			);
			$out = $wr[1];
		} //end if else
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	public function updateStatus($id, $status) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return -100;
		} //end if
		//--
		if((string)$id == '') {
			return -10; // invalid username length
		} //end if
		if((string)$id == 'admin') {
			return -11; // admin account cannot be deactivated
		} //end if
		//--
		$status = (int) $status;
		if($status != 1) {
			$status = 0;
		} //end if
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		$wr = $this->db->write_data(
			'UPDATE "admins" '.$this->db->prepare_statement(
				(array) [
					'modif' 	=> time(),
					'active' 	=> (int) $status
				],
				'update'
			).' '.$this->db->prepare_param_query('WHERE ("id" = ?)', [(string)$id])
		);
		$out = $wr[1];
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	public function updatePassword($id, $hash) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return -100;
		} //end if
		//--
		if((string)$id == '') {
			return -10; // invalid username length
		} //end if
		//--
		$hash = (string) $hash;
		if(strlen((string)$hash) != 128) {
			return -13; // invalid password, must be sha512 (128 chars)
		} //end if
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		$wr = $this->db->write_data(
			'UPDATE "admins" '.$this->db->prepare_statement(
				(array) [
					'modif' 	=> time(),
					'pass' 		=> (string) $hash
				],
				'update'
			).' '.$this->db->prepare_param_query('WHERE ("id" = ?)', [(string)$id])
		);
		$out = $wr[1];
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	public function updateAccount($id, $data) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return -100;
		} //end if
		//--
		$data = (array) $data;
		//--
		if((string)$id == '') {
			return -10; // invalid username length
		} //end if
		//-- {{{SYNC-MOD-AUTH-EMAIL-VALIDATION}}}
		$data['email'] = (string) $data['email'];
		if((strlen((string)$data['email']) < 6) OR (strlen((string)$data['email']) > 96) OR (!preg_match((string)\SmartValidator::regex_stringvalidation_expression('email'), (string)$data['email']))) {
			$data['email'] = null; // NULL, as the email is invalid
		} //end if
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		if($data['email'] === null) {
			$check_eml = array();
		} else {
			$check_eml = (array) $this->db->read_asdata(
				'SELECT "id" FROM "admins" WHERE (("email" = ?) AND ("id" != ?)) LIMIT 1 OFFSET 0',
				array((string)$data['email'], (string)$id)
			);
		} //end if else
		if(\Smart::array_size($check_eml) > 0) {
			$out = -2; // duplicate email
		} else {
			$arr = [
				'modif' 	=> time(),
				'name_f' 	=> (string) $data['name_f'],
				'name_l' 	=> (string) $data['name_l'],
				'email' 	=>          $data['email'] // mixed: false (NULL) or string
			];
			if(((string)$id != 'admin') AND ((string)$id != (string)\SmartAuth::get_login_id())) {
				$arr['priv'] = (array) $data['priv'];
			} //end if
			$wr = $this->db->write_data(
				'UPDATE "admins" '.$this->db->prepare_statement(
					(array) $arr,
					'update'
				).' '.$this->db->prepare_param_query('WHERE ("id" = ?)', [(string)$id])
			);
			$out = $wr[1];
		} //end if else
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


//--


	private function initDBSchema() {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception('Invalid AUTH DB Connection !');
			return 0;
		} //end if
		//--
		if($this->db->check_if_table_exists('admins') != 1) { // create auth DB if not exists
			//--
			if(defined('APP_AUTH_ADMIN_PASSWORD')) {
				//--
				$init_password = (string)APP_AUTH_ADMIN_PASSWORD;
				if(\SmartUnicode::str_len($init_password) < 10) {
					http_response_code(203);
					die(\SmartComponents::http_error_message('INVALID PASSWORD set as APP_AUTH_ADMIN_PASSWORD', 'The password is too short, must be minimum 10 characters. Manually REFRESH this page after by pressing F5 ...'));
					return 0;
				} //end if
				$init_hash_pass = \SmartHashCrypto::password($init_password, 'admin');
				//--
				$this->db->write_data('BEGIN');
				$this->db->write_data((string)$this->dbDefaultSchema());
				$this->db->write_data("INSERT INTO admins VALUES ('admin', '".$this->db->escape_str($init_hash_pass)."', 1, 0, 'admin@localhost', 'Mr.', 'Super', 'Admin', '', '', '', '', '', '', '', 0, 0, 0, '<superadmin>,<admin>', '', '', '', 0, ".(int)time().")");
				$this->db->write_data('COMMIT');
				//--
				http_response_code(202);
				die(\SmartComponents::http_status_message('OK :: AUTH DB Initialized', \SmartComponents::operation_ok('Login Info: username=admin ; password={what is set into APP_AUTH_ADMIN_PASSWORD}. Manually REFRESH this page after by pressing F5 ...', '98%')));
				return 0;
				//--
			} else {
				//--
				http_response_code(208);
				die(\SmartComponents::http_error_message('Cannot Initialize the AUTH DB !', 'Please Set the APP_AUTH_ADMIN_PASSWORD constant in config and Manually REFRESH this page after by pressing F5 ...'));
				return 0;
				//--
			} //end if
			//--
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION


	private function dbDefaultSchema() { // {{{SYNC-TABLE-AUTH_TEMPLATE}}}
//-- default schema ; default user: admin ; default pass: APP_AUTH_ADMIN_PASSWORD
$schema = <<<'SQL'
CREATE TABLE 'admins' (
id character varying(25) PRIMARY KEY NOT NULL,
pass character varying(128) NOT NULL,
active smallint DEFAULT 0 NOT NULL,
quota bigint DEFAULT 0 NOT NULL,
email character varying(96) DEFAULT NULL NULL,
title character varying(16) DEFAULT '' NOT NULL,
name_f character varying(64) DEFAULT '' NOT NULL,
name_l character varying(64) DEFAULT '' NOT NULL,
address character varying(64) DEFAULT '' NOT NULL,
city character varying(64) DEFAULT '' NOT NULL,
region character varying(64) DEFAULT '' NOT NULL,
country character varying(2) DEFAULT '' NOT NULL,
zip character varying(64) DEFAULT '' NOT NULL,
phone character varying(32) DEFAULT '' NOT NULL,
ip_addr character varying(39) DEFAULT '' NOT NULL,
logintime bigint DEFAULT 0 NOT NULL,
tries smallint DEFAULT 0 NOT NULL,
trytime bigint DEFAULT 0 NOT NULL,
priv text DEFAULT '' NOT NULL,
restrict text DEFAULT '' NOT NULL,
settings text DEFAULT '' NOT NULL,
keys text DEFAULT '' NOT NULL,
modif INTEGER DEFAULT 0 NOT NULL,
created INTEGER DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX 'id' ON "admins" ("id" ASC);
CREATE UNIQUE INDEX 'email' ON "admins" ("email");
CREATE INDEX 'active' ON "admins" ("active");
CREATE INDEX 'modif' ON "admins" ("modif");
CREATE INDEX 'created' ON "admins" ("created");
SQL;
//--
	//--
	return (string) $schema;
	//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>