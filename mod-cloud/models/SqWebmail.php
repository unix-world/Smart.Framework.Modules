<?php
// Class: \SmartModDataModel\Cloud\SqWebmail
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

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
	// r.20200420

	private $db;
	private $version = 'Smart.Cloud.WebMail#r.2020-04-20';


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


	public function getOneMessageByUid($uid, $folder) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$uid = (string) \trim((string)$uid);
		if((string)$uid == '') {
			throw new \Exception(__METHOD__.': Invalid Param: UID !');
			return array();
		} //end if
		//--
		$folder = (string) \trim((string)$folder);
		if((string)$folder == '') {
			throw new \Exception(__METHOD__.': Invalid Param: Folder !');
			return array();
		} //end if
		//--
		return (array) $this->db->read_asdata('SELECT * FROM `messages` WHERE (((`stat_uid` != \'\') AND (`stat_uid` = \''.$this->db->escape_str((string)$uid).'\')) AND (`folder` = \''.$this->db->escape_str((string)$folder).'\')) LIMIT 1 OFFSET 0');
		//--
	} //END FUNCTION


	// get messages by checksum ordered ASC by stat_cloud, DESC by stat_created, WHERE uid != currentUID
	public function getMessagesByCksum($checksum, $uid) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$checksum = (string) \trim((string)$checksum);
		if((string)$checksum == '') {
			throw new \Exception(__METHOD__.': Invalid Param: Checksum !');
			return array();
		} //end if
		//--
		$uid = (string) \trim((string)$uid);
		if((string)$uid == '') {
			throw new \Exception(__METHOD__.': Invalid Param: UID !');
			return array();
		} //end if
		//--
		return (array) $this->db->read_adata('SELECT * FROM `messages` WHERE ((`stat_cksum` != \'\') AND (`stat_cksum` = \''.$this->db->escape_str((string)$checksum).'\') AND (`stat_uid` != \'\') AND (`stat_uid` != \''.$this->db->escape_str((string)$uid).'\')) ORDER BY `stat_cloud` ASC, `stat_created` DESC LIMIT 5 OFFSET 0'); // LIMIT 5 should be enough (for 3 values of stat_cloud) ; must order by stat_cloud ASCENDING to get 1st the default ones, 2nd the deleted ones and only 3rd the duplicates (rest) ; also order DESCENDING by stat_created to get 1st the older one
		//--
	} //END FUNCTION


	public function updateOneMessageUidById($id, $uid) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
			return array();
		} //end if
		//--
		$uid = (string) \trim((string)$uid);
		if((string)$uid == '') {
			throw new \Exception(__METHOD__.': Invalid Param: UID !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data(
			'UPDATE `messages` SET `stat_uid` = ?, `stat_updated` = ? WHERE (`id` = ?)',
			[
				(string) $uid, 		// upd UID
				(int)    \time(), 	// upd updated time
				(string) $id 		// where
			]
		);
		//--
	} //END FUNCTION


	public function getOneMessageById($id) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
			return array();
		} //end if
		//--
		return (array) $this->db->read_asdata('SELECT * FROM `messages` WHERE (`id` = \''.$this->db->escape_str((string)$id).'\') LIMIT 1 OFFSET 0');
		//--
	} //END FUNCTION


	public function deleteOneMessageById($id) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data('DELETE FROM `messages` WHERE (`id` = \''.$this->db->escape_str((string)$id).'\')');
		//--
	} //END FUNCTION


	public function cleanupMarkAsDeletedMessages($folder) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$folder = (string) \trim((string)$folder);
		if((string)$folder == '') {
			throw new \Exception(__METHOD__.': Invalid Param: Folder !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data('DELETE FROM `messages` WHERE ((`stat_cloud` = 1) AND (`folder` = \''.$this->db->escape_str((string)$folder).'\'))');
		//--
	} //END FUNCTION


	public function markDeletedOneMessageById($id) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data('UPDATE `messages` SET `stat_cloud` = 1 WHERE (`id` = \''.$this->db->escape_str((string)$id).'\')');
		//--
	} //END FUNCTION


	public function moveOneMessageById($id, $folder, $uid) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
			return array();
		} //end if
		//--
		$folder = (string) \trim((string)$folder);
		if((string)$folder == '') {
			throw new \Exception(__METHOD__.': Invalid Param: Folder !');
			return array();
		} //end if
		//--
		$uid = (string) \trim((string)$uid); // on message move a new (local, WebMail UID has to be generated since the old UID is no more the same after moving a message out of an IMAP Folder)
		if((string)$uid == '') {
			throw new \Exception(__METHOD__.': Invalid Param: UID !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data(
			'UPDATE `messages` SET `stat_uid` = ?, `folder` = ? WHERE (`id` = ?)',
			[
				(string) $uid,
				(string) $folder,
				(string) $id
			]
		);
		//--
	} //END FUNCTION


	public function incrementMessageNotesReadStatusByMsgId($msgid, $msgsubj) { // {{{SYNC-NOTES-MSG-UNIVERSAL-UID}}} ; update many ; this is just for notes, to mark duplicates
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$msgid = (string) \trim((string)$msgid);
		if((string)$msgid == '') {
			return array(); // don't throw, this can be empty sometimes if message is corrupted ; if empty, stop here !
		} //end if
		//--
		$msgsubj = (string) $msgsubj; // don't check, it can be empty ; sync all related notes to have the same subject in DB (in message will be kept the original one)
		//--
		return (array) $this->db->write_data(
			'UPDATE `messages` SET `msg_subj` = ?, `stat_read` = `stat_read` + 1 WHERE ((`msg_id` = ?) AND (`ifolder` = ?))', // increment by one all notes that have the same message ID
			[
				(string) $msgsubj,
				(string) $msgid,
				(string) 'notes'
			]
		);
		//--
	} //END FUNCTION


	public function markOneMessageAsReadById($id) {
		//--
		if(!$this->db instanceof \SmartSQliteDb) {
			throw new \Exception(__METHOD__.': Invalid DB Connection !');
			return array();
		} //end if
		//--
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data(
			'UPDATE `messages` SET `stat_read` = 1 WHERE ((`id` = ?) AND (`stat_read` <= 0))', // stat_read can be 0 or 1 (but for notes can be 2, 3, ...)
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
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
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
		$id = (string) \trim((string)$id);
		if((string)$id == '') {
			throw new \Exception(__METHOD__.': Invalid Param: ID !');
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
		$arr_data['id'] = (string) \trim((string)$arr_data['id']);
		if((string)$arr_data['id'] == '') {
			throw new \Exception(__METHOD__.': Invalid Param: DATA[ID] !');
			return array();
		} //end if
		$arr_data['stat_uid'] = (string) \trim((string)$arr_data['stat_uid']);
		if((string)$arr_data['stat_uid'] == '') {
			throw new \Exception(__METHOD__.': Invalid Param: DATA[STAT-UID] !');
			return array();
		} //end if
		$arr_data['stat_cksum'] = (string) \trim((string)$arr_data['stat_cksum']);
		if((string)$arr_data['stat_cksum'] == '') {
			throw new \Exception(__METHOD__.': Invalid Param: DATA[STAT-CKSUM] !');
			return array();
		} //end if
		$arr_data['folder'] = (string) \trim((string)$arr_data['folder']);
		if((string)$arr_data['folder'] == '') {
			throw new \Exception(__METHOD__.': Invalid Param: DATA[FOLDER] !');
			return array();
		} //end if
		$arr_data['ifolder'] = (string) \trim((string)$arr_data['ifolder']);
		if((string)$arr_data['ifolder'] == '') {
			throw new \Exception(__METHOD__.': Invalid Param: DATA[IFOLDER] !');
			return array();
		} //end if
		//--
		return (array) $this->db->write_data('INSERT INTO `messages` '.$this->db->prepare_statement((array)$arr_data, 'insert'));
		//--
	} //END FUNCTION


	public function listSizeAllRecords($box) {
		//--
		$arr = (array) $this->db->read_asdata('SELECT SUM(`size_kb`) as `sum_size_kb` FROM `messages`'.$this->buildListWhereCondition($box, '', ''));
		//--
		return (int) \ceil(((float)$arr['sum_size_kb']) * 1000); // bytes
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
			case 'stat_read':
				$sortby = 'stat_read';
				break;
			case 'msg_id':
				$sortby = 'msg_id';
				break;
			case 'msg_inreply':
				$sortby = 'msg_inreply';
				break;
			case 'ifolder':
				$sortby = 'ifolder';
				break;
			case 'have_atts':
				$sortby = 'have_atts';
				break;
			case 'from_addr':
				$sortby = 'from_addr';
				break;
			case 'to_addr':
				$sortby = 'to_addr';
				break;
			case 'msg_subj':
				$sortby = 'msg_subj';
				break;
			case 'date_time':
				$sortby = 'date_time';
				break;
			case 'size_kb':
				$sortby = 'size_kb';
				break;
			case 'id':
			default:
				$sortby = 'id';
		} //end switch
		//--
		return (array) $this->db->read_adata('SELECT * FROM `messages`'.$this->buildListWhereCondition($box, $srcby, $src).' ORDER BY `'.$sortby.'` '.$sortdir.' LIMIT '.(int)$limit.' OFFSET '.(int)$ofs);
		//--
	} //END FUNCTION


	//===== PRIVATES


	private function buildListWhereCondition($box, $srcby, $src) {
		//--
		$src = (string) trim((string)$src);
		//--
		$where = ' WHERE ((`folder` = \''.$this->db->escape_str((string)$box).'\') AND (`stat_cloud` <= 0))';
		if((string)$src != '') {
			switch((string)$srcby) {
				case 'from_addr':
				case 'from_name':
				case 'to_addr':
				case 'to_name':
				case 'msg_subj':
				case 'date_time':
					$where .= ' AND (`'.$srcby.'` LIKE \'%'.$this->db->escape_str((string)$src).'%\')';
					break;
				case 'keywds':
				case 'atts':
					$where .= ' AND (`'.$srcby.'` LIKE \'%'.$this->db->escape_str((string)$src, 'likes').'%\')';
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
			$this->db->write_data('INSERT OR REPLACE INTO `_smartframework_metadata` (`id`, `description`) VALUES (\'smart-cloud-webmail-version\', \''.$this->db->escape_str((string)$this->version).'\')');
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
			stat_uid 		VARCHAR(255) 		NOT NULL DEFAULT \'\', -- must not be unique, UID is unique just for a specific folder on IMAP4 / POP3, but sometimes it can get duplicates
			stat_cksum 		VARCHAR(129) 		NOT NULL DEFAULT \'\',
			stat_cloud 		INTEGER      		NOT NULL DEFAULT 0, -- 0 = default ; 1 = deleted ; 2 = duplicate
			stat_created 	INTEGER      		NOT NULL DEFAULT 0, -- time when 1st downloaded from server (to be used by delete old messages)
			stat_updated 	INTEGER      		NOT NULL DEFAULT 0, -- time updated time, 1st downloaded from server, updated each time the UID changed from the server (to be used for cleanup)
			stat_read 		INTEGER      		NOT NULL DEFAULT 0,
			starred 		INTEGER      		NOT NULL DEFAULT 0,
			date_time 		VARCHAR(23)  		NOT NULL DEFAULT \'0000-00-00 00:00:00\',
			folder 			VARCHAR(7)   		NOT NULL DEFAULT \'none\',
			ifolder 		VARCHAR(7)   		NOT NULL DEFAULT \'none\', -- this should never change
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
			'idx_mail__id' 				=> 'id DESC',
			'idx_mail__stat_uid' 		=> 'stat_uid',
			'idx_mail__stat_cksum' 		=> 'stat_cksum',
			'idx_mail__stat_cloud' 		=> 'stat_cloud',
			'idx_mail__stat_created' 	=> 'stat_created',
			'idx_mail__stat_updated' 	=> 'stat_updated',
			'idx_mail__stat_read' 		=> 'stat_read',
			'idx_mail__starred' 		=> 'starred',
			'idx_mail__date_time' 		=> 'date_time DESC',
			'idx_mail__folder' 			=> 'folder ASC',
			'idx_mail__msg_id' 			=> 'msg_id ASC',
			'idx_mail__msg_inreply' 	=> 'msg_inreply ASC',
			'idx_mail__msg_subj' 		=> 'msg_subj',
			'idx_mail__from_addr' 		=> 'from_addr',
			'idx_mail__from_name' 		=> 'from_name',
			'idx_mail__to_addr' 		=> 'to_addr',
			'idx_mail__to_name' 		=> 'to_name',
			'idx_mail__cc_addr' 		=> 'cc_addr',
			'idx_mail__cc_name' 		=> 'cc_name'
		];
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
