<?php
// Class: \SmartModDataModel\Oauth2\SqOauth2
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModDataModel\Oauth2;

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
 * SQLite Model for ModOauth2
 * @ignore
 */
final class SqOauth2 {

	// ->
	// v.20231001

	private $db;


	public function __construct() {
		//--
		$db_path = '#db/oauth2-'.\sha1((string)\SMART_FRAMEWORK_SECURITY_KEY).'.sqlite';
		//--
		$this->db = new \SmartSQliteDb((string)$db_path);
		$this->db->open();
		//--
		if(!\SmartFileSystem::is_type_file((string)$db_path)) {
			if($this->db instanceof \SmartSQliteDb) {
				$this->db->close();
			} //end if
			\Smart::raise_error('OAUTH2 DB SQLITE File does NOT Exists !');
			return;
		} //end if
		//--
		$schema_ok = (bool) $this->initDBSchema(); // create default schema if not exists
		if($schema_ok !== true) {
			if($this->db instanceof \SmartSQliteDb) {
				$this->db->close();
			} //end if
			\Smart::raise_error('OAUTH2 DB SQLITE :: INVALID DB Schema !');
			return;
		} //end if
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


	//===== Management


	public function countByFilter(string $id='') {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return 0;
		} //end if
		//--
		$where = '';
		$params = '';
		//--
		if((string)$id != '') {
			$where = ' WHERE (`id` LIKE ?)';
			$params = array($id);
		} //end if else
		//--
		return (int) $this->db->count_data(
			'SELECT COUNT(1) FROM `oauth2_acc`'.$where,
			$params
		);
		//--
	} //END FUNCTION


	public function getListByFilter(array $fields=[], int $limit=10, int $ofs=0, string $sortby='id', string $sortdir='ASC', string $id='') {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return array();
		} //end if
		//--
		if(\Smart::array_size($fields) > 0) {
			$tmp_arr = (array) $fields;
			$fields = array();
			for($i=0; $i<\Smart::array_size($tmp_arr); $i++) {
				if(\is_array($tmp_arr[$i])) {
					foreach($tmp_arr[$i] as $kk => $vv) {
						$fields[] = $vv.'('.'`'.$kk.'`'.') AS `'.$kk.'-'.$vv.'`';
						break;
					} //end foreach
				} else {
					$fields[] = '`'.$tmp_arr[$i].'`';
				} //end if else
			} //end for
			unset($tmp_arr);
			$fields = (string) \implode(', ', (array) $fields);
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
			$where = ' WHERE (`id` LIKE ?)';
			$params = array($id);
		} //end if else
		//--
		$sortby = (string) \strtolower((string)\trim((string)$sortby));
		switch((string)$sortby) {
			case 'access_expire_time':
				// OK, CUSTOM
				break;
			case 'active':
			case 'admin':
			case 'created':
			case 'modified':
				// OK, STD
				break;
			case 'id':
			default:
				$sortby = 'id'; // DEFAULT
		} //end switch
		//--
		$sortdir = (string) \strtoupper((string)$sortdir);
		if((string)$sortdir != 'DESC') {
			$sortdir = 'ASC';
		} //end if
		//--
		return (array) $this->db->read_adata(
			'SELECT '.$fields.' FROM `oauth2_acc`'.$where.' ORDER BY `'.$sortby.'` '.$sortdir.$limit,
			$params
		);
		//--
	} //END FUNCTION


	public function getById(string $id, bool $decrypt=false) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return array();
		} //end if
		//--
		if((string)\trim((string)$id) == '') {
			return array();
		} //end if
		//--
		$arr = (array) $this->db->read_asdata(
			'SELECT * FROM `oauth2_acc` WHERE (`id` = ?) LIMIT 1 OFFSET 0',
			array((string)$id)
		);
		//--
		if($decrypt === true) {
			//--
			if(\Smart::array_size($arr) > 0) {
				//--
				if(\array_key_exists('client_secret', $arr)) {
					if((string)\trim((string)$arr['client_secret']) != '') {
						$arr['client_secret'] = (string) \SmartUtils::crypto_blowfish_decrypt((string)$arr['client_secret'], (string)$arr['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
					} //end if
				} //end if
				//--
				if(\array_key_exists('refresh_token', $arr)) {
					if((string)\trim((string)$arr['refresh_token']) != '') {
						$arr['refresh_token'] = (string) \SmartUtils::crypto_blowfish_decrypt((string)$arr['refresh_token'], (string)$arr['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
					} //end if
				} //end if
				//--
				if(\array_key_exists('access_token', $arr)) {
					if((string)\trim((string)$arr['access_token']) != '') {
						$arr['access_token'] = (string) \SmartUtils::crypto_blowfish_decrypt((string)$arr['access_token'], (string)$arr['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
					} //end if
				} //end if
				//--
				if(\array_key_exists('code', $arr)) {
					if((string)\trim((string)$arr['code']) != '') {
						$arr['code'] = (string) \SmartUtils::crypto_blowfish_decrypt((string)$arr['code'], (string)$arr['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
					} //end if
				} //end if
				//--
				if(\array_key_exists('logs', $arr)) {
					if((string)\trim((string)$arr['logs']) != '') {
						$arr['logs'] = (string) \SmartUtils::crypto_blowfish_decrypt((string)$arr['logs'], (string)$arr['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
					} //end if
				} //end if
				//--
			} //end if
			//--
		} //end if else
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	public function insertRecord(array $arr_data, string $redirect_url='') { // {{{SYNC-OAUTH2-DEFAULT-REDIRECT-URL}}}
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return -100;
		} //end if
		//--
		$this->db->write_data('VACUUM');
		//--
		if(!\is_array($arr_data)) {
			$arr_data = array();
		} //end if
		//--
		$data = [];
		//--
		$data['modified'] = (int) \time();
		$data['created'] = (int) $data['modified'];
		//--
		$data['errs'] = 0;
		$data['active'] = 1;
		$data['admin'] = (string) \SmartAuth::get_auth_username();
		//--
		$data['id'] = (string) \trim((string)$arr_data['id']);
		if((\strlen((string)$data['id']) < 5) OR (\strlen((string)$data['id']) > 127)) {
			return -10; // invalid id length
		} //end if
		if(!\preg_match((string)\SmartModExtLib\Oauth2\Oauth2Api::OAUTH2_REGEX_VALID_ID, (string)$data['id'])) { // {{{SYNC-OAUTH2-REGEX-ID}}}
			return -11; // invalid characters in id
		} //end if
		//--
		$data['scope'] = (string) \trim((string)$arr_data['scope']);
		if((string)$data['scope'] == '') {
			return -12; // empty scope
		} //end if
		if(strlen((string)$data['scope']) > 255) {
			return -13; // scope too long
		} //end if
		//--
		$data['url_auth'] = (string) \trim((string)$arr_data['url_auth']);
		if($this->isOauth2UrlValid((string)$data['url_auth']) !== true) {
			return -14; // empty or invalid auth URL ({...}/auth)
		} //end if
		//--
		$data['url_token'] = (string) \trim((string)$arr_data['url_token']);
		if($this->isOauth2UrlValid((string)$data['url_token']) !== true) {
			return -15; // empty or invalid token URL ({...}/token)
		} //end if
		//--
		$data['url_redirect'] = (string) \trim((string)$redirect_url);
		if((string)$data['url_redirect'] == '') {
			return -16; // empty url redirect
		} //end if
		//--
		$data['client_id'] = (string) \trim((string)$arr_data['client_id']);
		if((string)$data['client_id'] == '') {
			return -17; // empty client ID
		} //end if
		//--
		$data['client_secret'] = (string) \trim((string)$arr_data['client_secret']);
		if((string)$data['client_secret'] == '') {
			return -18; // empty client Secret
		} //end if
		$data['client_secret'] = (string) \SmartUtils::crypto_blowfish_encrypt((string)$data['client_secret'], (string)$data['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
		//--
		$data['code'] = (string) \trim((string)$arr_data['code']);
		if((string)$data['code'] == '') {
			return -19; // empty code
		} //end if
		$data['code'] = (string) \SmartUtils::crypto_blowfish_encrypt((string)$data['code'], (string)$data['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
		//--
		$data['access_token'] = (string) \trim((string)$arr_data['access_token']);
		if((string)$data['access_token'] == '') { // {{{SYNC-OAUTH2-CONDITION-ACCESS-TOKEN}}}
			return -20; // empty access token
		} //end if
		$data['access_token'] = (string) \SmartUtils::crypto_blowfish_encrypt((string)$data['access_token'], (string)$data['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
		//--
		$data['refresh_token'] = (string) \trim((string)$arr_data['refresh_token']);
		if((string)$data['refresh_token'] == '') { // OK: some providers do not use this (ex: github)
			//--
			$data['access_expire_seconds'] = 0;
			$data['access_expire_time'] = 0;
			//--
		}  else {
			//--
			$data['refresh_token'] = (string) \SmartUtils::crypto_blowfish_encrypt((string)$data['refresh_token'], (string)$data['id'].':'.\SMART_FRAMEWORK_SECURITY_KEY);
			//--
			$data['access_expire_seconds'] = (int) \trim((string)$arr_data['access_expire_seconds']);
			if((int)$data['access_expire_seconds'] < 1) { // {{{SYNC-OAUTH2-CONDITION-ACCESS-EXPIRE-SECONDS-TOKEN}}}
				return -22; // invalid access token expire seconds
			} //end if
			//--
			$data['access_expire_time'] = (int) ((int)$data['access_expire_seconds'] + (int)$data['modified']);
			if((int)$data['access_expire_time'] <= (int)\time()) {
				return -23; // invalid access token expire time
			} //end if
			//--
		} //end if else
		//--
		$data['description'] = (string) \trim((string)$arr_data['description']);
		if((string)$data['description'] == '') {
			return -24; // empty description
		} //end if
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		$check_id = (array) $this->db->read_asdata(
			'SELECT `id` FROM `oauth2_acc` WHERE (`id` = ?) LIMIT 1 OFFSET 0',
			[
				(string) $data['id']
			]
		);
		if(\Smart::array_size($check_id) > 0) {
			return -2; // duplicate ID
		} //end if
		//--
		$wr = (array) $this->db->write_data(
			'INSERT INTO `oauth2_acc` '.$this->db->prepare_statement(
				(array) $data,
				'insert'
			)
		);
		$out = $wr[1];
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	public function updateRecordLogs(string $id, string $logs, bool $errs=false) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return -100;
		} //end if
		//--
		$logs = (string) \trim((string)$logs);
		if((string)$logs == '') {
			return -10;
		} //end if
		//--
		$data = [];
		$data['logs'] = (string) \SmartUtils::crypto_blowfish_encrypt((string)$logs, (string)$id.':'.\SMART_FRAMEWORK_SECURITY_KEY);
		if($errs === true) {
			$data['errs'] = 1;
		} //end if
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		$wr = $this->db->write_data(
			'UPDATE `oauth2_acc` '.$this->db->prepare_statement(
				(array) $data,
				'update'
			).' '.$this->db->prepare_param_query(
				'WHERE (`id` = ?)',
				[
					(string) $id
				]
			)
		);
		$out = $wr[1];
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	public function updateRecordAccessToken(string $id, string $access_token, int $access_expire_seconds) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return -100;
		} //end if
		//--
		if(\Smart::random_number(0, 1000) == 500) {
			$this->db->write_data('VACUUM');
		} //end if
		//--
		if((string)$id == '') {
			return -10; // invalid ID
		} //end if
		//--
		$rdarr = (array) $this->getById((string)$id, false);
		if(\Smart::array_size($rdarr) <= 0) {
			return -11; // record not found, Invalid ID
		} //end if
		if((string)$rdarr['refresh_token'] == '') {
			return -12; // records without a refresh token must not update the access token
		} //end if
		$rdarr = null;
		//--
		$data = [];
		$data['modified'] = (int) \time();
		$data['logs'] = (string) \SmartUtils::crypto_blowfish_encrypt((string)'# '.\date('Y-m-d H:i:s O')."\n".'# '.'Access Token Updated using the stored Refresh Token', (string)$id.':'.\SMART_FRAMEWORK_SECURITY_KEY);
		//--
		$data['access_token'] = (string) \trim((string)$access_token);
		if((string)$data['access_token'] == '') { // {{{SYNC-OAUTH2-CONDITION-ACCESS-TOKEN}}}
			return -13; // empty access token
		} //end if
		$data['access_token'] = (string) \SmartUtils::crypto_blowfish_encrypt((string)$data['access_token'], (string)$id.':'.\SMART_FRAMEWORK_SECURITY_KEY);
		//--
		$data['access_expire_seconds'] = (int) $access_expire_seconds;
		if((int)$data['access_expire_seconds'] < 1) { // {{{SYNC-OAUTH2-CONDITION-ACCESS-EXPIRE-SECONDS-TOKEN}}}
			return -14; // invalid access token expire seconds
		} //end if
		//--
		$data['access_expire_time'] = (int) ((int)$data['access_expire_seconds'] + (int)$data['modified']);
		if((int)$data['access_expire_time'] <= (int)\time()) {
			return -15; // invalid access token expire time
		} //end if
		//--
		$data['errs'] = 0;
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		$wr = $this->db->write_data(
			'UPDATE `oauth2_acc` '.$this->db->prepare_statement(
				(array) $data,
				'update'
			).' '.$this->db->prepare_param_query(
				'WHERE (`id` = ?)',
				[
					(string) $id
				]
			)
		);
		$out = $wr[1];
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	public function updateStatus($id, $status) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return -100;
		} //end if
		//--
		if(\Smart::random_number(0, 1000) == 500) {
			$this->db->write_data('VACUUM');
		} //end if
		//--
		if((string)$id == '') {
			return -10; // invalid ID
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
			'UPDATE `oauth2_acc` '.$this->db->prepare_statement(
				(array) [
					'active' 	=> (int) $status
				],
				'update'
			).' '.$this->db->prepare_param_query(
				'WHERE (`id` = ?)',
				[
					(string) $id
				]
			)
		);
		$out = $wr[1];
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	public function deleteRecord(string $id) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return -100;
		} //end if
		//--
		$this->db->write_data('VACUUM');
		//--
		$out = -1;
		//--
		$this->db->write_data('BEGIN');
		//--
		$wr = $this->db->write_data(
			'DELETE FROM `oauth2_acc` '.$this->db->prepare_param_query(
				'WHERE (`id` == ?)',
				[
					(string) $id
				]
			)
		);
		$out = $wr[1];
		//--
		$this->db->write_data('COMMIT');
		//--
		return (int) $out;
		//--
	} //END FUNCTION


	//#####


	private function isOauth2UrlValid(string $url) { // {{{SYNC-OAUTH2-VALIDATE-URL}}}
		//--
		if(
			((string)\trim((string)$url) == '') OR
			(strpos((string)$url, 'https://') !== 0) OR
		//	(strpos((string)$url, 'http://') !== 0) OR // make nonsense to use http scheme for OAUTH2 because it is unsecure
			(strlen((string)\trim((string)$url)) < 15) OR
			(strlen((string)\trim((string)$url)) > 255)
		) {
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	//@@@@@


	private function initDBSchema() {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			\Smart::raise_error('Invalid OAUTH2 DB Connection !');
			return false;
		} //end if
		//--
		if($this->db->check_if_table_exists('oauth2_acc') != 1) { // check and create DB table if not exists
			$this->db->write_data('BEGIN');
			$this->db->write_data((string)$this->dbDefaultSchema());
			$this->db->write_data('COMMIT');
			if($this->db->check_if_table_exists('oauth2_acc') != 1) { // create DB table, it should exist
				return false;
			} //end if
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	private function dbDefaultSchema() { // {{{SYNC-TABLE-OAUTH2_TEMPLATE}}}
//-- default schema ; default user: APP_OAUTH2_ADMIN_USERNAME ; default pass: APP_OAUTH2_ADMIN_PASSWORD
$version = (string) $this->db->escape_str(\SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.\SMART_FRAMEWORK_RELEASE_VERSION);
$schema = <<<SQL
INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES ('version@oauth2', '{$version}');
CREATE TABLE 'oauth2_acc' (
	`id` character varying(127) PRIMARY KEY NOT NULL,
	`active` smallint DEFAULT 0 NOT NULL,
	`scope` character varying(255) NOT NULL,
	`url_auth` character varying(255) NOT NULL,
	`url_token` character varying(255) NOT NULL,
	`url_redirect` character varying(255) NOT NULL,
	`client_id` character varying(255) NOT NULL,
	`client_secret` character varying(255) NOT NULL,
	`code` character varying(255) NOT NULL,
	`access_token` character varying(255) NOT NULL,
	`refresh_token` character varying(255) NOT NULL,
	`access_expire_seconds` integer DEFAULT 0 NOT NULL,
	`access_expire_time` bigint DEFAULT 0 NOT NULL,
	`description` text DEFAULT '' NOT NULL,
	`logs` text DEFAULT '' NOT NULL,
	`errs` smallint DEFAULT 0 NOT NULL,
	`admin` character varying(25) DEFAULT '' NOT NULL,
	`modified` integer DEFAULT 0 NOT NULL,
	`created` integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX 'oauth2_acc_id' ON `oauth2_acc` (`id` ASC);
CREATE INDEX 'oauth2_acc_active' ON `oauth2_acc` (`active`);
CREATE INDEX 'oauth2_acc_admin' ON `oauth2_acc` (`admin`);
CREATE INDEX 'oauth2_acc_modif' ON `oauth2_acc` (`modified`);
CREATE INDEX 'oauth2_acc_created' ON `oauth2_acc` (`created`);
CREATE INDEX 'oauth2_acc_access_expire_time' ON `oauth2_acc` (`access_expire_time` ASC);
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
