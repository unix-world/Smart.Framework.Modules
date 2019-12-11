<?php
// Class: \SmartModDataModel\Cloud\SqWebmail
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

namespace SmartModDataModel\Cloud;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


final class SqWebmail {

	// ->
	// v.20191210

	private $db;


	public function __construct($y_path) {
		//--
		if((string)$y_path == '') {
			throw new \Exception(__CLASS__.': DB PATH is NOT Defined !');
			return;
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($y_path)) {
			throw new \Exception(__CLASS__.': DB PATH is UNSAFE (1) !');
			return;
		} //end if
		//--
		$y_path = (string) \SmartFileSysUtils::add_dir_last_slash($y_path);
		if(!\SmartFileSysUtils::check_if_safe_path($y_path)) {
			throw new \Exception(__CLASS__.': DB PATH is UNSAFE (2) !');
			return;
		} //end if
		//--
		$this->db = new \SmartSQliteDb((string)$y_path.'mailbox.sqlite');
		$this->db->open();
		//--
		if(!\SmartFileSystem::is_type_file((string)$y_path.'mailbox.sqlite')) {
			if($this->db instanceof \SmartSQliteDb) {
				$this->db->close();
			} //end if
			throw new \Exception(__CLASS__.': DB File does NOT Exists !');
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


	public function getOneMessageByUid($uid) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		return (array) $this->db->read_asdata('SELECT `id`, `stat_uid`, `stat_del`, `date_time` FROM `messages` WHERE ((`stat_uid` IS NOT NULL) AND (`stat_uid` = \''.$this->db->escape_str((string)$uid).'\')) LIMIT 1 OFFSET 0');
		//--
	} //END FUNCTION


	public function markOneMessageAsReadById($id) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data(
			'UPDATE `messages` SET `stat_read` = 1 WHERE ((`id` = ?) AND (`stat_read` != 1))',
			[
				(string) $id
			]
		);
		//--
	} //END FUNCTION


	public function updOneMessageAttsById($id, $atts_num, $atts_lst) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$atts_num = (int) $atts_num;
		if($atts_num < 0) {
			$atts_num = 0;
		} //end if
		//--
		return (array) $this->db->write_data(
			'UPDATE `messages` SET `have_atts` = ?, `atts` = ? WHERE (`id` = ?)',
			[
				(int)    $atts_num,
				(string) trim((string)$atts_lst),
				(string) $id
			]
		);
		//--
	} //END FUNCTION


	public function updOneMessageKeywordsById($id, $keywords) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data(
			'UPDATE `messages` SET `keywds` = ? WHERE (`id` = ?)',
			[
				(string) trim((string)$keywords),
				(string) $id
			]
		);
		//--
	} //END FUNCTION


	public function insertOneMessage($arr_data) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		if(\Smart::array_size($arr_data) <= 0) {
			return array();
		} //end if
		//--
		return (array) $this->db->write_data('INSERT INTO `messages` '.$this->db->prepare_statement((array)$arr_data, 'insert'));
		//--
	} //END FUNCTION


	public function listCountRecords($box, $srcby, $src) {
		//--
		return (int) $this->db->count_data('SELECT COUNT(1) FROM `messages`'.$this->buildListWhereCondition($box, $srcby, $src));
		//--
	} //END FUNCTION


	public function listGetRecords($box, $srcby, $src, $limit, $ofs, $sortdir, $sortby) {
		//--
		$limit  = (int) \Smart::format_number_int($limit, '+');
		if($limit < 1) {
			$limit = 1;
		} //end if
		//--
		$ofs = (int) \Smart::format_number_int($ofs, '+');
		//--
		$sortdir = (string) strtoupper((string)$sortdir);
		if((string)$sortdir != 'ASC') {
			$sortdir = 'DESC';
		} //end if
		//--
		switch((string)$sortby) {
			case 'have_atts':
				$sortby = 'have_atts';
				break;
			case 'from_all':
				$sortby = 'from_addr';
				break;
			case 'to_all':
				$sortby = 'to_addr';
				break;
			case 'msg_subj':
				$sortby = 'msg_subj';
				break;
			case 'date_time':
				$sortby = 'date_time';
				break;
			case 'id':
			default:
				$sortby = 'id';
		} //end switch
		//--
		return (array) $this->db->read_adata('SELECT *, `from_addr` || \' \' || `from_name` AS `from_all`, `to_addr` || \' \' || `to_name` AS `to_all` FROM `messages`'.$this->buildListWhereCondition($box, $srcby, $src).' ORDER BY `'.$sortby.'` '.$sortdir.' LIMIT '.(int)$limit.' OFFSET '.(int)$ofs);
		//--
	} //END FUNCTION


	//===== PRIVATES


	private function buildListWhereCondition($box, $srcby, $src) {
		//--
		$src = (string) trim((string)$src);
		//--
		$where = ' WHERE (`folder` = \''.$this->db->escape_str((string)$box).'\')';
		if((string)$src != '') {
			switch((string)$srcby) {
				case 'from_addr':
				case 'from_name':
				case 'to_addr':
				case 'to_name':
				case 'msg_subj':
				case 'date_time':
					$where = ' AND (`'.$srcby.'` LIKE \'%'.$this->db->escape_str((string)$src).'%\')';
					break;
				case 'keywds':
				case 'atts':
					$where = ' AND (`'.$srcby.'` LIKE \'%'.$this->db->escape_str((string)$src, 'likes').'%\')';
					break;
				default:
					// nothing, leave as is set above
			} // end switch
		} //end if
		//--
		return (string) $where;
		//--
	} //END FUNCTION


	private function initDBSchema() {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__CLASS__.': Invalid DB Connection !');
			return 0;
		} //end if
		//--
		if($this->db->check_if_table_exists('messages') != 1) { // create table if not exists
			//--
			$this->db->write_data('BEGIN');
			$this->db->create_table(
				'messages',
				(string) $this->messagesTableSchema(),
				(array)  $this->messagesTableIndexes()
			);
			$this->db->write_data('COMMIT');
			//--
			if($this->db->check_if_table_exists('messages') != 1) {
				throw new \Exception(__CLASS__.': Table: `messages` NOT FOUND !');
				return 0;
			} //end if
			//--
		} //end if
		//--
		if(\Smart::random_number(0, 1000) == 500) {
			$this->db->write_data('VACUUM');
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION


	private function messagesTableSchema() {
		//--
		return '
			id 				VARCHAR(255) 		PRIMARY KEY NOT NULL,
			stat_uid 		VARCHAR(255) 		UNIQUE NULL DEFAULT NULL,
			stat_read 		INTEGER      		NOT NULL DEFAULT 0,
			stat_del 		INTEGER      		NOT NULL DEFAULT 0,
			starred 		INTEGER      		NOT NULL DEFAULT 0,
			date_time 		VARCHAR(23)  		NOT NULL DEFAULT \'0000-00-00 00:00:00\',
			folder 			VARCHAR(7)   		NOT NULL DEFAULT \'none\',
			size_kb 		DECIMAL(16,2)		NOT NULL DEFAULT 0,
			m_priority 		INTEGER    	 		NOT NULL DEFAULT 3,
			have_atts 		INTEGER      		NOT NULL DEFAULT 0,
			msg_id 			VARCHAR(255) 		NOT NULL DEFAULT \'\',
			msg_inreply		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			msg_subj 		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			from_addr 		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			from_name 		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			to_addr 		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			to_name 		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			cc_addr 		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			cc_name 		VARCHAR(255) 		NOT NULL DEFAULT \'\',
			addrss 			TEXT  		 		NOT NULL DEFAULT \'\',
			atts 			TEXT  		 		NOT NULL DEFAULT \'\',
			keywds 			TEXT  		 		NOT NULL DEFAULT \'\'
		';
		//--
	} //END FUNCTION


	private function messagesTableIndexes() {
		//--
		return [
			'idx_mail__id' 			=> 'id DESC',
			'idx_mail__stat_uid' 	=> 'stat_uid',
			'idx_mail__starred' 	=> 'starred',
			'idx_mail__date_time' 	=> 'date_time DESC',
			'idx_mail__folder' 		=> 'folder ASC',
			'idx_mail__msg_id' 		=> 'msg_id ASC',
			'idx_mail__msg_inreply' => 'msg_inreply ASC',
			'idx_mail__msg_subj' 	=> 'msg_subj',
			'idx_mail__from_addr' 	=> 'from_addr',
			'idx_mail__from_name' 	=> 'from_name',
			'idx_mail__to_addr' 	=> 'to_addr',
			'idx_mail__to_name' 	=> 'to_name',
			'idx_mail__cc_addr' 	=> 'cc_addr',
			'idx_mail__cc_name' 	=> 'cc_name'
		];
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>