<?php
// Module Lib: \SmartModExtLib\Cloud\webmailUtils
// (c) 2006-2020 unix-world.org - all rights reserved
// r.5.7.2 / smart.framework.v.5.7

namespace SmartModExtLib\Cloud;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


final class webmailUtils {

	// r.20200420
	// ::


	public static function getAllowedBoxes($allow_sent, $allow_notes, $allow_trash=true) {
		//--
		$boxes = [ 'inbox' ];
		//--
		if($allow_sent === true) {
			$boxes[] = 'sent';
		} //end if
		//--
		if($allow_notes === true) {
			$boxes[] = 'notes';
		} //end if
		//--
		if($allow_trash === true) {
			$boxes[] = 'trash';
		} //end if
		//--
		return (array) $boxes;
		//--
	} //END FUNCTION


	public static function getServerBoxByFolder($folder) {
		//--
		$srvbox = '';
		switch((string)$folder) {
			case 'inbox':
				$srvbox = 'INBOX';
				break;
			case 'sent':
				$srvbox = 'Sent';
				break;
			case 'notes':
				$srvbox = 'Notes';
				break;
			case 'trash':
				$srvbox = 'Trash';
				break;
			default:
				// leave empty
		} //end switch
		//--
		if((string)$srvbox == '') {
			Smart::raise_error(
				__METHOD__.' :: Invalid Folder: '.$folder
			);
			die(''); // just in case
		} //end if
		//--
		return (string) $srvbox;
		//--
	} //END FUNCTION


	public static function getDBStorageObject($the_mbox_path) {
		//--
		$db = new \SmartModDataModel\Cloud\SqWebmail($the_mbox_path);
		//--
		return (object) $db;
		//--
	} //END FUNCTION


	public static function checkMboxPathNotOk($the_mbox_path) {
		//--
		if((string)\trim((string)$the_mbox_path) == '') {
			return 'MailBox Path is Empty';
		} //end if
		//--
		if(!\SmartFileSysUtils::check_if_safe_path((string)$the_mbox_path)) {
			return 'MailBox Path is Not Safe';
		} //end if
		//--
		if(\SmartFileSystem::is_type_dir((string)$the_mbox_path) !== true) {
			return 'MailBox Path does not exists';
		} //end if
		//--
		return ''; // OK
		//--
	} //END FUNCTION


	// return STRING as ERROR / ARRAY with Config if OK
	public static function parseMboxConfig($the_mbox_path, $mbox) {
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($the_mbox_path)) {
			return 'The MailBox Path is Unsafe';
		} //end if
		//--
		$the_mbox_path = (string) \SmartFileSysUtils::add_dir_last_slash($the_mbox_path);
		if(!\SmartFileSystem::is_type_dir($the_mbox_path)) {
			return 'The MailBox Path does not exists';
		} //end if
		//--
		$file_mbx_cfg = (string) $the_mbox_path.'mailbox.json';
		if(!\SmartFileSystem::is_type_file($file_mbx_cfg)) {
			return 'The MailBox Config is Missing';
		} //end if
		$tmp_cfg_arr = \Smart::json_decode(\SmartFileSystem::read($file_mbx_cfg));
		//--
		if(\Smart::array_size($tmp_cfg_arr) <= 0) {
			return 'Invalid MailBox Config';
		} //end if
		if(\Smart::array_size($tmp_cfg_arr['get']) <= 0) {
			return 'Invalid MailBox Config Get';
		} //end if
		if(\Smart::array_size($tmp_cfg_arr['send']) <= 0) {
			return 'Invalid MailBox Config Send';
		} //end if
		if(((string)\trim((string)$tmp_cfg_arr['webmail-account']) == '') OR ((string)$tmp_cfg_arr['webmail-account'] !== (string)$mbox)) {
			return 'Invalid MailBox Config Account';
		} //end if
		//--
		return (array) $tmp_cfg_arr; // OK
		//--
	} //END FUNCTION


	// get the message real UID (as on server) by UID
	// on IMAP4 will have 'IMAP4-UIV-@num@-UID-@uid@'
	// on POP3 will have '@num@' (num is the same as UID
	public static function getMessageRealUid($uid, $type) {
		//--
		if((string)$type != 'imap4') {
			return (string) \trim((string)$uid); // return it back, trimmed, don't know how to parse
		} //end if
		//--
		$uid = (string) \trim((string)$uid);
		if((string)$uid == '') {
			return '';
		} //end if
		//--
		if(\strpos((string)$uid, 'IMAP4-UIV-') !== 0) {
			return '';
		} //end if
		//--
		$uid = (string) \trim((string)\ltrim((string)$uid, 'IMAP4-UIV-'));
		if((string)$uid == '') {
			return '';
		} //end if
		//--
		if(\strpos((string)$uid, '-UID-') === false) {
			return '';
		} //end if
		//--
		$uid = (array) \explode('-UID-', (string)$uid);
		$uid = (string) \trim((string)$uid[1]);
		if((string)$uid == '') {
			return '';
		} //end if
		//--
		return (string) $uid;
		//--
	} //END FUNCTION


	public static function handleSelectedMessages($sel, $username, $safe_user_path, $mbox, $box, $action) {

		//--
		if(\Smart::array_size($sel) <= 0) {
			return 'No Messages Selected';
		} //end if
		//--

		//--
		$chk = (string) self::checkMboxPathNotOk($safe_user_path);
		if($chk) {
			return (string) 'handleSelectedMessages (1): '.$chk;
		} //end if
		//--

		//--
		if((string)\trim((string)$mbox) == '') {
			return 'MailBox is Undefined (Empty)';
		} //end if
		//--
		$the_mbox_path = (string) \SmartFileSysUtils::add_dir_last_slash($safe_user_path.$mbox);
		//--
		$chk = (string) self::checkMboxPathNotOk($the_mbox_path);
		if($chk) {
			return (string) 'handleSelectedMessages (2): '.$chk;
		} //end if
		//--

		//--
		$tmp_cfg_arr = self::parseMboxConfig($the_mbox_path, $mbox); // return mixed: err string or array config
		if(!\is_array($tmp_cfg_arr)) {
			return (string) 'handleSelectedMessages (3): '.$tmp_cfg_arr;
		} //end if
		//--
		$tmp_cfg_get_arr = (array) $tmp_cfg_arr['get'];
		//--
		$tmp_cfg_arr = array();
		//--

		//--
		if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
			$is_imap4 = true;
		} else {
			$is_imap4 = false;
		} //end if else
		//--

		//--
		if((string)\trim((string)$box) == '') {
			return 'handleSelectedMessages (4): MailBox Box is Undefined (Empty)';
		} //end if
		//--
		switch((string)$box) { // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
			case 'inbox':
			case 'sent':
			case 'trash':
			case 'notes':
				break;
			default:
				return 'handleSelectedMessages (5): Invalid Box Selected: '.$box;
		} //end switch
		//--
		$the_box_path = (string) \SmartFileSysUtils::add_dir_last_slash($the_mbox_path.$box);
		//--
		$chk = (string) self::checkMboxPathNotOk($the_box_path);
		if($chk) {
			return (string) 'handleSelectedMessages (6): '.$chk;
		} //end if
		//--

		//--
		if((string)\trim((string)$action) == '') {
			return 'handleSelectedMessages (7): MailBox Action Box is Undefined (Empty)';
		} //end if
		//--
		switch((string)$action) { // {{{SYNC-WEBMAIL-ACTION}}}
			case 'delete': // from INBOX, Sent, Trash or Notes (for Trash or Notes remove the message from Disk and mark deleted in DB ; on 1st sync will be deleted also on server)
				break;
			case 'restore': // restore to INBOX or Sent
				if((string)$box != 'trash') {
					return (string) 'handleSelectedMessages (8): Restore Action is Available just for Trash';
				} //end if
				break;
			default:
				return (string) 'handleSelectedMessages (9) Invalid Action Box Selected: '.$action;
		} //end switch
		//--

		//--
		if($is_imap4 === true) {
			$reset_uid = true; // on IMAP4 reset the UID after move because UID is unique just for a box
		} else {
			$reset_uid = false; // on POP3 keep UID
		} //end if else
		//--

		//--
		$db = self::getDBStorageObject($the_mbox_path);
		//--

		//--
		$errors = [];
		//--
		if((string)$action == 'delete') { // {{{SYNC-WEBMAIL-ACTION}}}
			//--
			$errors = (array) self::processSelectedMessages($the_mbox_path, $db, $sel, $box, 'delete', $reset_uid);
			//--
		} elseif((string)$action == 'restore') { // {{{SYNC-WEBMAIL-ACTION}}}
			//--
			$errors = (array) self::processSelectedMessages($the_mbox_path, $db, $sel, $box, 'restore', $reset_uid);
			//--
		} else {
			//--
			$errors[] = 'Invalid Action: '.$action;
			//--
		} //end if
		//--

		//--
		if(\Smart::array_size($errors) > 0) {
			return 'One or more messages faild to be processed: '.\implode(', ', $errors);
		} //end if
		//--

		//--
		return '';
		//--

	} //END FUNCTION


	private static function processSelectedMessages($the_mbox_path, $db, $sel, $box, $action, $reset_uid) {
		//--
		$errors = [];
		//--
		if(self::checkMboxPathNotOk($the_mbox_path)) {
			$errors[] = (string) __METHOD__.' :: The MailBox Path is Invalid !';
			return (array) $errors;
		} //end if
		//--
		if(!\is_a($db, '\\SmartModDataModel\\Cloud\\SqWebmail')) {
			$errors[] = (string) __METHOD__.' :: The DB Instance is Invalid !';
			return (array) $errors;
		} //end if
		//--
		if(\Smart::array_size($sel) <= 0) {
			$errors[] = (string) __METHOD__.' :: No Messages to Process ...';
			return (array) $errors;
		} //end if
		//--
		switch((string)$action) { // {{{SYNC-WEBMAIL-ACTION}}}
			case 'delete': // from INBOX, Sent or Trash (for Trash remove the message from Disk and from IMAP4 Server)
				break;
			case 'restore': // restore to INBOX or Sent
				if((string)$box != 'trash') {
					$errors[] = (string) __METHOD__.' :: Restore Action is Available just for Trash';
					return (array) $errors;
				} //end if
				break;
			default:
				$errors[] = (string) __METHOD__.' :: Invalid Action Box Selected: '.$action;
				return (array) $errors;
		} //end switch
		//--
		foreach($sel as $key => $val) {
			//--
			if((string)\trim((string)$val) != '') {
				//--
				if(\SmartFileSysUtils::check_if_safe_file_or_dir_name($val)) {
					//--
					$the_msg_arr = (array) $db->getOneMessageById((string)$val);
					//--
					if((\Smart::array_size($the_msg_arr) > 0) AND (\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$the_msg_arr['id'])) AND ((int)$the_msg_arr['stat_cloud'] <= 0) AND ((string)$the_msg_arr['folder'] === (string)$box)) { // if not found in DB or folder is different, just skip with no error, this is req. when running restore with multiple destinations
						//-- DELETE
						if(((string)$action == 'delete') AND ((string)$box == 'trash')) { // {{{SYNC-WEBMAIL-ACTION}}}
							//-- PERMANENT DELETE: Trash
							$tmp_file = (string) \SmartFileSysUtils::add_dir_last_slash($the_mbox_path).'trash/'.$the_msg_arr['id'];
							if(\SmartFileSystem::is_type_file($tmp_file)) { // if not file (exists), don't register any error ... it's just ok :-)
								if(\SmartFileSystem::delete($tmp_file)) {
									//--
									$test = array();
									//--
									$test = (array) $db->markDeletedOneMessageById($the_msg_arr['id']);
									//--
									if($test[1] != 1) {
										$errors[] = (string) 'Cannot Delete from DB the Message: '.$the_msg_arr['id'];
									} else {
										// OK: DELETE SUCCESSFUL
									} //end if else
									//--
									$test = null;
									//--
								} else {
									$errors[] = (string) 'Cannot Delete the Message: '.$the_msg_arr['id'];
								} //end if
								//--
							} //end if
							$tmp_file = '';
							//--
						} elseif(((string)$action == 'delete') AND ((string)$box == 'notes')) { // {{{SYNC-WEBMAIL-ACTION}}}
							//-- PERMANENT DELETE: Notes
							$tmp_file = (string) \SmartFileSysUtils::add_dir_last_slash($the_mbox_path).'notes/'.$the_msg_arr['id'];
							if(\SmartFileSystem::is_type_file($tmp_file)) { // if not file (exists), don't register any error ... it's just ok :-)
								if(\SmartFileSystem::delete($tmp_file)) {
									//--
									$test = array();
									//--
									$test = (array) $db->markDeletedOneMessageById($the_msg_arr['id']);
									//--
									if($test[1] != 1) {
										$errors[] = (string) 'Cannot Delete from DB the Note: '.$the_msg_arr['id'];
									} else {
										// OK: DELETE SUCCESSFUL
									} //end if else
									//--
									$test = null;
									//--
								} else {
									$errors[] = (string) 'Cannot Delete the Note: '.$the_msg_arr['id'];
								} //end if
								//--
							} //end if
							$tmp_file = '';
							//--
						} elseif( // {{{SYNC-WEBMAIL-ACTION}}}
							((string)$action == 'delete') // box != 'trash' for delete ... :: comes from condition above
							OR
							(((string)$action == 'restore') AND ((string)$box == 'trash'))  // for restore the box must be always 'trash'
						) {
							//-- MOVE TO DESTINATION
							$destfolder = '';
							if((string)$action == 'delete') { // action: delete to Trash
								$destfolder = 'trash';
							} elseif( // action: restore (from Trash) to INBOX or Sent
								(((string)$action == 'restore') AND ((string)$box == 'trash')) AND
								(((string)\trim((string)$the_msg_arr['ifolder']) != '') AND (\in_array((string)$the_msg_arr['ifolder'], (array)self::getAllowedBoxes(true, true, false)))) // dissalow trash in arr here :: {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
							) {
								$destfolder = (string) $the_msg_arr['ifolder'];
							} //end if else
							//--
							if(((string)\trim((string)$destfolder) != '') AND (\in_array((string)$destfolder, (array)self::getAllowedBoxes(true, true)))) { // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
								//--
								if($reset_uid === true) {
									$tmp_new_uuid = (string) self::assignWebmailUid(); // for IMAP4 reset the UID, as it will be moved to another folder also on server and this UID becomes deprecated / may not be used anymore on IMAP4
								} else {
									$tmp_new_uuid = (string) $the_msg_arr['stat_uid']; // for POP3 keep the UID
								} //end if else
								//--
								$err_move = (string) self::moveOneMessage(
									$db,
									(int)    $the_msg_arr['stat_cloud'], 	// stat cloud
									(string) $the_msg_arr['id'], 			// message ID
									(string) $tmp_new_uuid, 				// UID
									(string) $box, 							// old folder
									(string) $destfolder,					// new folder ; on DELETE this will be trash ; on RESTORE will be inbox or sent but must be equal with ifolder so must be run twice
									(string) $the_mbox_path
								);
								//--
								if((string)$err_move != '') {
									$errors[] = (string) 'Failed to Restore (Move to ['.$destfolder.']) the Message: '.$the_msg_arr['id'].' :: '.$err_move;
								} else {
									// OK: RESTORED SUCCESSFUL
								} //end if else
								//--
								$err_move = '';
								//--
							} else {
								//--
								$errors[] = (string) 'Invalid Destination Folder: ['.$destfolder.'] for Action: ['.$action.'] for File Name: '.$the_msg_arr['id'];
								//--
							} //end if
							//--
							$destfolder = '';
							//--
						} else {
							//--
							$errors[] = (string) 'Invalid Action: ['.$action.'] for File Name: '.$the_msg_arr['id'];
							//--
						} //end if else
						//--
					} //end if
					//--
				} else {
					//--
					$errors[] = (string) 'Invalid File Name: '.$val;
					//--
				} //end if else
				//--
			} //end if
			//--
		} //end foreach
		//--
		return (array) $errors;
		//--
	} //END FUNCTION


	public static function getMessageChecksum($tmp_message_content) {
		//--
		return (string) \SmartHashCrypto::sha256((string)$tmp_message_content);
		//--
	} //END FUNCTION


	public static function storeMessage($msg_type, $username, $mbox, $sync_mode, $db, $use_mark_read, $crr_uid, $tmp_message_content, $use_the_dir, $the_mbox_path, $tmp_cfg_get_arr) {

		//--
		switch((string)$msg_type) {
			case 'message':
			case 'apple-note':
				break;
			default:
				$out_arr['error'] = __METHOD__.' :: Invalid Message Type: '.$msg_type;
				return (array) $out_arr;
		} //end switch
		//--

		//--
		if(((string)\trim((string)$mbox) == '') OR (strpos((string)\SmartFileSysUtils::add_dir_last_slash($the_mbox_path), (string)'/'.$mbox.'/') === false)) {
			$out_arr['error'] = __METHOD__.' :: Invalid or Empty MailBox: '.$mbox;
			return (array) $out_arr;
		} //end if
		//--

		//--
		if($sync_mode !== true) {
			$sync_mode = false;
		} //end if
		//--

		//--
		$out_arr = [
			'sync_mode' 		=> (bool)   $sync_mode,
			'action_on_server' 	=> (string) '', // available just with SYNC MODE :: '' | 'sync:server:delete:do' (on POP3 and IMAP4) | 'sync:local:update:uid' (on POP3 and IMAP4) | 'sync:server:move:%folder%' (only on IMAP4)
			'message_id' 		=> (string) '', // message filename
			'message_file' 		=> (string) '', // message relative path (incl. filename)
			'stor_result' 		=> (int)    -1, // OK must be 1
			'wr_result' 		=> (int)    -1, // OK must be 1
			'error' 			=> (string)  '' // OK must be empty string ''
		];
		//--

		//--
		if(!\is_a($db, '\\SmartModDataModel\\Cloud\\SqWebmail')) {
			$out_arr['error'] = __METHOD__.' :: The DB Instance is Invalid !';
			return (array) $out_arr;
		} //end if
		//--
		if((string)\trim((string)$crr_uid) == '') {
			$out_arr['error'] = __METHOD__.' :: The Message UID is Empty !';
			return (array) $out_arr;
		} //end if
		//--
		$tmp_message_content = (string) \trim((string)$tmp_message_content);
		if((string)$tmp_message_content == '') {
			$out_arr['error'] = __METHOD__.' :: The Message Content is Empty !';
			return (array) $out_arr;
		} //end if
		if(((string)\trim((string)$use_the_dir) == '') OR (!\in_array((string)$use_the_dir, (array)self::getAllowedBoxes(true, true)))) { // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
			$out_arr['error'] = __METHOD__.' :: The Folder is Empty or Invalid: '.$use_the_dir;
			return (array) $out_arr;
		} //end if
		if(self::checkMboxPathNotOk($the_mbox_path)) {
			$out_arr['error'] = __METHOD__.' :: The MailBox Path is Empty / Invalid or does not exists !';
			return (array) $out_arr;
		} //end if
		//--
		$tmp_rd_arr = (array) $db->getOneMessageByUid($crr_uid, $use_the_dir);
		if(\Smart::array_size($tmp_rd_arr) > 0) { // this should be checked before calling this function
			$out_arr['error'] = __METHOD__.' :: The Message is Duplicate in Folder ['.$use_the_dir.']: exists already a message with UID: '.$crr_uid;
			return (array) $out_arr;
		} //end if
		//--

		//--
		$tmp_msg_cksum = (string) self::getMessageChecksum((string)$tmp_message_content);
		//--

		//--
		$is_duplicate = false; // initialize ...
		//--
		if(($sync_mode === true) AND ((string)$use_the_dir != 'notes')) { // NEVER SYNC NOTES, THEY CAN'T LOOSE THE UID AS THEY CAN'T BE MOVED IN OTHER IMAP FOLDERS THAN Notes ; Also If a Note is deleted on server by iOS, keep it in webmail except if explicit deleted (this is a safety measure if deletet by mistake on iOS)
			//-- find duplicates by checksum (from any folder) ; will return none, one or many
			$tmp_chk_arr = (array) $db->getMessagesByCksum($tmp_msg_cksum, $crr_uid); // {{{SYNC-MANAGE-DUPLICATES-BY-CHECKSUM}}}
			//-- if found many, then try to resolve it ...
			if((((int)\Smart::array_size($tmp_chk_arr) > 0)) AND ((string)$tmp_chk_1st_arr['folder'] != 'notes') AND ((string)$tmp_chk_1st_arr['ifolder'] != 'notes')) { // if found at least one, get the first and process it
				//--
				$tmp_chk_1st_arr = (array) $tmp_chk_arr[0]; // get the first, which is the oldest but with stat_cloud = 0 (if no one found with stat_cloud = 0, then stat_cloud = 1 (if no one found with stat_cloud = 1, then stat_cloud = 2))
				//--
				switch((int)$tmp_chk_1st_arr['stat_cloud']) {
					case 0: // default
						//--
						if((string)$tmp_chk_1st_arr['folder'] == (string)$use_the_dir) { // just the UID is not synced with IMAP4 or POP3
							//--
							$db->updateOneMessageUidById((string)$tmp_chk_1st_arr['id'], (string)$crr_uid);
							$out_arr['action_on_server'] = 'sync:local:update:uid';
							return (array) $out_arr;
							//--
						} else { // folders are not identical (handle it different on IMAP4 vs POP3)
							//--
							if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') { // on IMAP4 move the message, UID will be updated on next sync since there is no direct method to find it after move
								$out_arr['action_on_server'] = 'sync:server:move:'.$tmp_chk_1st_arr['folder'];
								return (array) $out_arr;
							} else { // on POP3 register as duplicate as on POP3 moving between folders is not supported, and we have it stored as default
								$is_duplicate = true; // register as duplicate
							} //end if else
							//--
						} //end if
						//--
						break;
					case 1: // marked as deleted on WebMail, delete also on Server ... here stat_cloud = 1, is very clear and this is the 1st message in DB as ordered by stat_cloud ASC
						//--
						$out_arr['action_on_server'] = 'sync:server:delete:do'; // {{{SYNC-WEBMAIL-DELETE-MARK-DELETED}}}
						$db->deleteOneMessageById((string)$tmp_chk_1st_arr['id']); // {{{SYNC-WEBMAIL-DELETE-DO-DELETE}}}
						return (array) $out_arr;
						//--
						break;
					case 2: // marked as duplicate
					default:
						//--
						$is_duplicate = false; // this message is only as duplicate on WebMail, so store it as default
						//--
				} //end switch
				//--
			} //end if else
			//--
			$tmp_chk_1st_arr = array();
			$tmp_chk_arr = array();
			//--
		} //end if
		//--

		//--
		$eml = new \SmartMailerMimeDecode();
		$tmp_msg_head = (array) $eml->get_header(\SmartUnicode::sub_str($tmp_message_content, 0, 16384)); // we only do a fast decode ... later they can be updated
		//--
		$fixed_subject = (string) \Smart::text_cut_by_limit((string)$tmp_msg_head['subject'], 255, true, '...'); // cut long subjects for store in DB
		//--
		$fldr_y = (string) \date('Y', @\strtotime((string)$tmp_msg_head['date']));
		$fldr_m = (string) \date('Y-m', @\strtotime((string)$tmp_msg_head['date']));
		$fldr_d = (string) \date('Y-m-d', @\strtotime((string)$tmp_msg_head['date']));
		//--
		$tmp_message_fname = (string) \Smart::safe_filename(\substr((string)$use_the_dir, 0, 1).'__'.\date('Y-m-d_H-i-s', @\strtotime((string)$tmp_msg_head['date'])).'__'.\substr((string)\SmartHashCrypto::crc32b((string)$tmp_msg_cksum), 0, 3).'-'.\strtolower((string)\Smart::uuid_10_seq().'-'.\Smart::uuid_10_num().'-'.\Smart::uuid_10_str()).'.eml'); // make sure there are no message duplicates by ID !
		$tmp_message_folder = (string) \SmartFileSysUtils::add_dir_last_slash($the_mbox_path.$use_the_dir);
		//$tmp_message_folder .= $fldr_y.'/'.$fldr_m.'/'.$fldr_d.'/';
		\SmartFileSystem::dir_create($tmp_message_folder, true);
		//-- STORE MESSAGE TO FILE (IF REQ. SO)
		$tmp_stor_ok = false;
		$tmp_stor_size = 0;
		//--

		//-- if apple note, encrypt before store
		if((string)$msg_type == 'apple-note') {
			$tmp_message_content = (string) \SmartMailerNotes::encrypt_eml_message_as_apple_notes($tmp_msg_head['message-uid'], $tmp_msg_head['date'], $tmp_msg_head['from_addr'], $fixed_subject, $tmp_message_content); // store just 255 bytes of subject to avoid leak too many unencrypted words
			if((string)trim((string)$tmp_message_content) == '') {
				$out_arr['error'] = __METHOD__.' :: Failed to Encrypt the Apple-Note Message';
				return (array) $out_arr;
			} //end if
		} //end if
		//--

		//--
		if($is_duplicate === true) { // handle fake duplicate registration
			//--
			$tmp_message_file_id = (string) '#duplicate:'.$use_the_dir.':'.$crr_uid.':'.\Smart::uuid_10_seq().'-'.\Smart::uuid_10_str().'-'.\Smart::uuid_10_num();
			$tmp_message_file = (string) 'nonexisting/'.$tmp_message_file_id;
			$tmp_stat_cloud = 2; // duplicate
			$tmp_stor_result = 1; // fake store as OK
			$tmp_stor_size = (int) \strlen((string)$tmp_message_content);
			$tmp_stor_ok = true;
			//--
		} else { // handle real registration
			//--
			$tmp_message_file_id = (string) $tmp_message_fname; // if stored on disk also store the file name as ID to be able to read it
			$tmp_message_file = (string) \Smart::safe_pathname($tmp_message_folder.$tmp_message_fname);
			$tmp_stat_cloud = 0; // default
			$tmp_stor_result = (int) \SmartFileSystem::write($tmp_message_file, 'X-WebMail-MetaData: Smart.Cloud.Signature.[START]'."\r\n".'X-WebMail-Message-Server: '.\Smart::normalize_spaces($tmp_cfg_get_arr['settings_host'].':'.$tmp_cfg_get_arr['settings_port'])."\r\n".'X-WebMail-Message-Checksum: '.\Smart::normalize_spaces($tmp_msg_cksum)."\r\n".'X-WebMail-Message-Size: '.\Smart::normalize_spaces((int)\strlen((string)$tmp_message_content))."\r\n".'X-WebMail-Account: '.\Smart::normalize_spaces($username)."\r\n".'X-WebMail-MetaData: Smart.Cloud.Signature.[END]'."\r\n".$tmp_message_content);
			if(\SmartFileSystem::is_type_file($tmp_message_file)) {
				$tmp_stor_size = (int) \SmartFileSystem::get_file_size($tmp_message_file);
				$tmp_stor_ok = true;
			} else {
				$out_arr['error'] = __METHOD__.' :: Stored Message File could not be found upon check !';
				return (array) $out_arr;
			} //end if
			if((int)$tmp_stor_size < (int)\strlen((string)$tmp_message_content)) {
				\SmartFileSystem::delete($tmp_message_file);
				$out_arr['error'] = __METHOD__.' :: Stored Message File invalid size !';
				return (array) $out_arr;
			} //end if
			//--
		} //end if
		//--
		$out_arr['stor_result'] = (int) $tmp_stor_result;
		//--

		//-- RECORD UID TO DB
		if(($tmp_stor_result == 1) AND ($tmp_stor_ok === true)) {
			//--
			$arr_write 					= array();
			$arr_write['id'] 			= (string) $tmp_message_file_id;
			$arr_write['stat_uid'] 		= (string) $crr_uid;
			$arr_write['stat_cksum'] 	= (string) $tmp_msg_cksum;
			$arr_write['stat_read'] 	= (int)    $use_mark_read; // if 0 will be updated on first read
			$arr_write['stat_cloud'] 	= (int)    $tmp_stat_cloud; // 0 = default ; 1 = deleted ; 2 = duplicate by checksum
			$arr_write['stat_created'] 	= (int)    \time();
			$arr_write['stat_updated'] 	= (int)    \time();
			$arr_write['date_time'] 	= (string) \date('Y-m-d H:i:s', @\strtotime((string)$tmp_msg_head['date']));
			$arr_write['folder'] 		= (string) $use_the_dir;
			if((string)$use_the_dir == 'trash') { // try to detect if the origin is INBOX or Sent :: {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
				$origin_trash_detected = null;
				if($origin_trash_detected === null) {
					if((string)$tmp_msg_head['from_addr'] == (string)$mbox) {
						if((string)$tmp_msg_head['to_addr'] == (string)$mbox) {
							// as the To == From and both are the same as MBox, it can't be detected ..., so use trash
						} else {
							$origin_trash_detected = 'sent'; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
						} //end if else
					} else {
						$origin_trash_detected = 'inbox'; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					} //end if
				} //end if
				if(($origin_trash_detected !== null) AND (\in_array((string)$origin_trash_detected, (array)self::getAllowedBoxes(true, false, false)))) { // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					$arr_write['ifolder'] 	= (string) $origin_trash_detected;
				} else {
					$arr_write['ifolder'] 	= (string) $use_the_dir;
				} //end if else
			} else {
				$arr_write['ifolder'] 	= (string) $use_the_dir; // this will never change after insert to preserve the original folder on message moves
			} //end if else
			$arr_write['size_kb'] 		= (string) \Smart::format_number_dec(((int)$tmp_stor_size / 1000), 2, '.', '');
			$arr_write['msg_subj'] 		= (string) $fixed_subject;
			$arr_write['from_addr'] 	= (string) $tmp_msg_head['from_addr'];
			if((string)$msg_type == 'apple-note') { // TODO: change UID of old notes !?
				$db->incrementMessageNotesReadStatusByMsgId((string)$tmp_msg_head['message-uid'], (string)$fixed_subject); // {{{SYNC-NOTES-MSG-UNIVERSAL-UID}}}
				$arr_write['from_name'] 	= '';
				$arr_write['to_addr'] 		= '';
				$arr_write['to_name'] 		= '';
				$arr_write['cc_addr'] 		= '';
				$arr_write['cc_name'] 		= '';
				$arr_write['m_priority'] 	= 0;
				$arr_write['have_atts'] 	= 0;
				$arr_write['msg_id'] 		= (string) $tmp_msg_head['message-uid']; // {{{SYNC-NOTES-MSG-UNIVERSAL-UID}}} for notes this is the real ID that will not change if note edited
				$arr_write['msg_inreply'] 	= (string) $tmp_msg_head['message-id']; // store this just for records
			} else { // message
				$arr_write['from_name'] 	= (string) $tmp_msg_head['from_name'];
				$arr_write['to_addr'] 		= (string) $tmp_msg_head['to_addr'];
				$arr_write['to_name'] 		= (string) $tmp_msg_head['to_name'];
				$arr_write['cc_addr'] 		= (string) $tmp_msg_head['cc_addr'];
				$arr_write['cc_name'] 		= (string) $tmp_msg_head['cc_name'];
				$arr_write['m_priority'] 	= (int)    \Smart::format_number_int($tmp_msg_head['priority'], '+');
				$arr_write['have_atts'] 	= (int)    \Smart::format_number_int($tmp_msg_head['attachments']);
				$arr_write['msg_id'] 		= (string) $tmp_msg_head['message-id'];
				$arr_write['msg_inreply'] 	= (string) $tmp_msg_head['in-reply-to'];
			} //end if else
			$arr_write['addrss'] 		= ''; // to be updated on first read
			$arr_write['atts'] 			= ''; // to be updated on first read
			$arr_write['keywds'] 		= ''; // to be updated on first read
			//-- write to DB
			$tmp_wr_result 				= (array) $db->insertOneMessage((array)$arr_write);
			//-- register out results
			$out_arr['message_id']  	= (string) $tmp_message_file_id;
			$out_arr['message_file'] 	= (string) $tmp_message_file;
			$out_arr['wr_result'] 		= (int)    $tmp_wr_result[1];
			if($tmp_wr_result[1] != 1) {
				$out_arr['error'] = __METHOD__.' :: Message Save to DB Failed: '.$crr_uid.' / '.$use_the_dir;
			} //end if
			//--
			$arr_write 					= array(); // free mem
			//--
		} //end if
		//--

		//--
		return (array) $out_arr;
		//--

	} //END FUNCTION


	public static function parseEmlAddressesAsArr($field) {
		//--
		$arr = [];
		//--
		if(\strpos((string)$field, ',') === false) {
			$field = (string) \trim((string)\str_replace(['<', '>'], '', (string)$field)); // make compliant with list array
			if((string)$field != '') {
				$arr = (array) [ $field ]; // ensure array
			} //end if else
		} else {
			$arr = (array) \Smart::list_to_array($field, true); // ensure array
		} //end if else
		//--
		$regex = (string) \SmartValidator::regex_stringvalidation_expression('email', 'full');
		//--
		$safe_arr = [];
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			$arr[$i] = (string) \strtolower((string)$arr[$i]);
			if(\preg_match((string)$regex, (string)$arr[$i])) {
				$safe_arr[] = (string) $arr[$i];
			} //end if
		} //end for
		//--
		return (array) $safe_arr;
		//--
	} //END FUNCTION


	public static function checksumAttachmentComposerData($relative_file_path) {
		//--
		return (string) \SmartHashCrypto::sha384('SmartFrameworkCloudWebMail//'.\SmartUtils::get_visitor_tracking_uid().'@'.\SmartUtils::unique_auth_client_private_key().'#'.$relative_file_path.'#'.\SmartHashCrypto::sha256($relative_file_path));
		//--
	} //END FUNCTION


	public static function createAttachmentComposerData($display_name, $relative_file_path) {
		//--
		$display_name = (string) \trim((string)$display_name);
		if((string)$display_name == '') {
			return array();
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$display_name)) {
			return array();
		} //end if
		//--
		$relative_file_path = (string) \trim((string)$relative_file_path);
		if((string)$relative_file_path == '') {
			return array();
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path((string)$relative_file_path)) {
			return array();
		} //end if
		if(!\SmartFileSystem::is_type_file((string)$relative_file_path)) {
			return array();
		} //end if
		if(!\SmartFileSystem::have_access_read((string)$relative_file_path)) {
			return array();
		} //end if
		//--
		return array(
			'name' 	=> (string) $display_name,
			'file' 	=> (string) \SmartUtils::crypto_blowfish_encrypt((string)$relative_file_path),
			'chk' 	=> (string) self::checksumAttachmentComposerData($relative_file_path)
		);
		//--
	} //END FUNCTION


	public static function assignWebmailUid() {
		//--
		return (string) 'WebMail-UUID-'.\Smart::uuid_10_seq().'-'.\Smart::uuid_10_num().'-'.\Smart::uuid_10_str();
		//--
	} //END FUNCTION


	public static function sendEmail($username, $safe_user_path, $mbox, $form) {

		//--
		$chk = (string) self::checkMboxPathNotOk($safe_user_path);
		if($chk) {
			return (string) 'sendEmail (1): '.$chk;
		} //end if
		//--

		//--
		if((string)\trim((string)$mbox) == '') {
			return 'MailBox is Undefined (Empty)';
		} //end if
		//--
		$the_mbox_path = (string) \SmartFileSysUtils::add_dir_last_slash($safe_user_path.$mbox);
		//--
		$chk = (string) self::checkMboxPathNotOk($the_mbox_path);
		if($chk) {
			return (string) 'sendEmail (2): '.$chk;
		} //end if
		//--

		//--
		$tmp_cfg_arr = self::parseMboxConfig($the_mbox_path, $mbox); // return mixed: err string or array config
		if(!\is_array($tmp_cfg_arr)) {
			return (string) 'sendEmail (3): '.$tmp_cfg_arr;
		} //end if
		//--
		$tmp_cfg_send_from_addr = (string) $tmp_cfg_arr['webmail-account'];
		$tmp_cfg_send_from_name = (string) $tmp_cfg_arr['webmail-accname'];
		$tmp_cfg_send_arr = (array) $tmp_cfg_arr['send'];
		$tmp_cfg_get_arr = (array) $tmp_cfg_arr['get'];
		//--
		if(\Smart::array_size($tmp_cfg_send_arr) <= 0) {
			return 'Email Send is not configured on this account';
		} //end if
		//--
		$tmp_cfg_arr = array();
		//--

		//--
		$max_body_size = (int) (8 * 1000 * 1000); // max body size is 8M
		//--
		$max_atts_size = (int) (50 * 1000 * 1000); // 50 MB (IMPORTANT: This must no more than 1/4 of PHP INI memory_limit
		//--

		//--
		if(\Smart::array_size($form) <= 0) {
			return 'Empty Form Data';
		} //end if
		//--

		//--
		$form['replytoaddr'] 	= (string) \trim((string)$form['replytoaddr']);
		$form['inreplyto'] 		= (string) \trim((string)$form['inreplyto']);
		$form['to'] 			= (string) \trim((string)$form['to']);
		$form['cc'] 			= (string) \trim((string)$form['cc']);
		$form['bcc'] 			= (string) \trim((string)$form['bcc']);
		$form['subject'] 		= (string) \trim((string)$form['subject']);
		$form['htmlbody'] 		= (string) \trim((string)$form['htmlbody']);
		//--

		//--
		if(\strlen((string)$form['replytoaddr']) > 128) {
			$form['replytoaddr'] = '';
		} //end if
		//--
		if(\strlen((string)$form['inreplyto']) > 255) {
			$form['inreplyto'] = '';
		} //end if
		//--
		if((string)$form['to'] == '') {
			return 'Empty To Address';
		} elseif(\strlen((string)$form['to']) > 65535) {
			return 'Oversized To Address(es)';
		} //end if
		//--
		if(\strlen((string)$form['cc']) > 65535) {
			return 'Oversized Cc Address(es)';
		} //end if
		//--
		if(\strlen((string)$form['bcc']) > 128) {
			return 'Oversized Bcc Address';
		} //end if
		//--
		if((string)$form['subject'] == '') {
			return 'Empty Subject';
		} elseif(\SmartUnicode::str_len((string)$form['subject']) > 127) {
			return 'Oversized Subject';
		} //end if
		//--
		if((string)$form['htmlbody'] == '') {
			return 'Empty Message Body';
		} elseif(\SmartUnicode::str_len((string)$form['htmlbody']) > (int)$max_body_size) {
			return 'Oversized Message Body';
		} //end if
		//--

		//-- To (array)
		$form['to'] = (array) self::parseEmlAddressesAsArr($form['to']);
		if(\Smart::array_size($form['to']) <= 0) {
			return 'Invalid To Address';
		} //end if
		//--

		//-- Cc (array)
		$test = (bool) \strlen((string)$form['cc']);
		if($test) {
			$form['cc'] = (array) self::parseEmlAddressesAsArr($form['cc']);
			if(\Smart::array_size($form['cc']) <= 0) {
				return 'Invalid Cc Address';
			} //end if
		} else {
			$form['cc'] = array();
		} //end if
		$test = null;
		//--

		//-- Bcc (string)
		$test = (bool) \strlen((string)$form['bcc']);
		if($test) {
			$form['bcc'] = (array) self::parseEmlAddressesAsArr($form['bcc']);
			if(\Smart::array_size($form['bcc']) <= 0) {
				return 'Invalid Bcc Address';
			} //end if
			$form['bcc'] = (string) $form['bcc'][0];
		} else {
			$form['bcc'] = '';
		} //end if
		$test = null;
		//--

		//--
		$form['htmlbody'] = (string) \trim((string)(new \SmartHtmlParser((string)$form['htmlbody']))->get_clean_html(false)); // clean, without html comments
		if((string)$form['htmlbody'] == '') {
			return 'Invalid Message';
		} //end if
		//--

		//--
		$atts = array();
		$size_atts = 0;
		$num_atts = 0;
		if((string)$form['mode'] == 'forward') { // forward must have a single eml attached
			$min_atts_num = 0;
			$max_atts_num = 1;
		} else {
			$min_atts_num = 0;
			$max_atts_num = 9;
		} //end if
		//--
		if(\Smart::array_size($form['attachments']) > 0) {
			//--
			for($i=0; $i<$max_atts_num; $i++) {
				//--
				$tmp_att = (string) \trim((string)$form['attachments'][$i]);
				if(((string)$tmp_att != '') AND (\strlen((string)$tmp_att) <= 65535)) {
					$tmp_att = (array) \explode('|', (string)$tmp_att);
					$tmp_att[0] = (string) \trim((string)$tmp_att[0]); // checksum
					$tmp_att[1] = (string) \trim((string)\SmartUtils::crypto_blowfish_decrypt((string)$tmp_att[1])); // file (encrypted)
					$tmp_att[2] = (string) \trim((string)$tmp_att[2]); // *name (optional)
					if(((string)$tmp_att[0] != '') AND ((string)$tmp_att[1] != '') AND ((string)$tmp_att[2] != '')) {
						if((string)self::checksumAttachmentComposerData((string)$tmp_att[1]) === (string)$tmp_att[0]) {
							if(\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$tmp_att[2])) {
								if(\SmartFileSysUtils::check_if_safe_path((string)$tmp_att[1])) {
									if(\SmartFileSystem::is_type_file((string)$tmp_att[1])) {
										if(\SmartFileSystem::have_access_read((string)$tmp_att[1])) {
											$tmp_ok_att = false;
											if((string)$form['mode'] == 'forward') { // forward must have a single eml attached
												if((string)\substr((string)$tmp_att[1], -4, 4) == '.eml') { // {{{SYNC-WEBMAIL-FWD-ALLOWED-FILE-EXTENSION}}}
													$tmp_ok_att = true;
												} //end if
											} else {
												$tmp_ok_att = true;
											} //end if else
											if($tmp_ok_att === true) {
												$tmp_ok_att = (string) \SmartFileSystem::read((string)$tmp_att[1]);
												if((string)$tmp_ok_att != '') {
													$atts[(string)$tmp_att[2]] = (string) $tmp_ok_att;
													$size_atts = (int) ((int)$size_atts + (int)\strlen((string)$tmp_ok_att));
													$num_atts++;
												} //end if
											} //end if
											$tmp_ok_att = null;
										} //end if
									} //end if
								} //end if
							} //end if
						} //end if
					} //end if
				} //end if
				//--
			} //end for
			//--
		} else {
			//--
			for($i=0; $i<$max_atts_num; $i++) {
				//--
				$tmp_att = (array) \SmartUtils::read_uploaded_file(
					'webmail_attachments',
					(int) $i,
					(int) $max_atts_size,
					'' // allowed_extensions (all)
				);
				//--
				if(((string)$tmp_att['status'] == 'WARN') AND ((string)$tmp_att['msg-code'] == '3')) {
					// no file uploaded :: OK
				} elseif(((string)$tmp_att['status'] == 'OK') AND ((string)$tmp_att['msg-code'] == '0')) {
					// OK
					$atts[(string)$tmp_att['filename']] = (string) $tmp_att['filecontent'];
					$size_atts = (int) ((int)$size_atts + (int)$tmp_att['filesize']);
					$num_atts++;
				} else { // ERR
					return 'Attachment ['.($i+1).'] ERROR: ('.$tmp_att['msg-code'].') '.$tmp_att['message'];
				} //end if else
				//--
			} //end for
			//--
		} //end if else
		//--
		if((int)\Smart::array_size($atts) < (int)$min_atts_num) {
			return 'Attachments Minimum required Number is: '.(int)$min_atts_num.' but current there are '.(int)\Smart::array_size($atts).' attachments ...';
		} //end if
		//--
		if((int)\Smart::array_size($atts) > (int)$max_atts_num) {
			return 'Attachments Maximum allowed Number is: '.(int)$max_atts_num.' but current there are '.(int)\Smart::array_size($atts).' attachments ...';
		} //end if
		//--
		if((int)$size_atts > (int)$max_atts_size) {
			return 'Attachments Oversized: '.\SmartUtils::pretty_print_bytes((int)$size_atts, 2, ' ', 1000).' (Max Allowed is: '.\SmartUtils::pretty_print_bytes((int)$max_atts_size, 2, ' ', 1000);
		} //end if
		//--

		//--
		// @return [ 'result' => 'Operation RESULT', 'error' => 'ERROR Message if any', 'log' => 'Send LOG', 'message' => 'The Mime MESSAGE' ]
		$arr_send = (array) \SmartMailerUtils::send_extended_email(
			[ // server settings
				'smtp_mxdomain' 		=> '', // leave empty
				'server_name' 			=> (string) $tmp_cfg_send_arr['settings_host'],
				'server_port' 			=> (int)    $tmp_cfg_send_arr['settings_port'],
				'server_sslmode' 		=> (string) $tmp_cfg_send_arr['settings_tls'],
				'server_cafile' 		=> '',
				'server_auth_user' 		=> (string) $tmp_cfg_send_arr['settings_auth_username'],
				'server_auth_pass' 		=> (string) \SmartUtils::crypto_blowfish_decrypt((string)$tmp_cfg_send_arr['settings_auth_password']),
				'send_from_addr' 		=> (string) $tmp_cfg_send_from_addr,
				'send_from_name' 		=> (string) $tmp_cfg_send_from_name,
				'use_qp_encoding' 		=> (bool)   ($tmp_cfg_send_arr['settings_use_qp_encoding'] === true) ? true : false,
				'use_min_enc_subj' 		=> (bool)   ($tmp_cfg_send_arr['settings_use_min_enc_subj'] === true) ? true : false,
				'use_antispam_rules' 	=> (bool)   ($tmp_cfg_send_arr['settings_use_antispam_rules'] === false ? false : true)
			],
			'send-return', 					// do send + return
			(array)  $form['to'], 			// to
			(array)  $form['cc'], 			// cc
			(string) $form['bcc'], 			// bcc
			(string) $form['subject'], 		// subj
			(string) $form['htmlbody'], 	// body
			true, 							// is_html
			(array)  $atts, 				// attachments
			(string) $form['replytoaddr'], 	// replytoaddr
			(string) $form['inreplyto'], 	// inreplyto
			3 								// priority
		);
		//--
		if((int)$arr_send['result'] <= 0) { // $arr_send['result'] return INTEGER 0 or # of sent messages
			return 'FAILED to send Message: '.$arr_send['error'];
		} //end if
		//--

		//--
		$store_err = [];
		//--

		//--
		$db = self::getDBStorageObject($the_mbox_path);
		//-- generate a new WebMail UID for POP3 or for IMAP4 (for IMAP4 will try to update later if can append message and could get the new UID)
		$new_gen_uuid = (string) self::assignWebmailUid();
		//-- do not check for duplicates on message send, store them all !!
		$arr_msg_store = (array) self::storeMessage(
			'message',
			(string) $username,
			(string) $mbox,
			false,   // no sync mode, this is only for get mode
			$db,     // db model
			1,       // mark as read
			(string) $new_gen_uuid,
			(string) \trim((string)\str_replace([ "\r", "\n" ], [ '', "\r\n" ], (string)$arr_send['message'])), // {{{SYNC-MAIL-MSG-IMAP4-STORE}}} fix message with str replace to be exact as in imap ->(append) otherwise the checksum will not match after append on server and retrieve back
			'sent',  // store in sent folder
			(string) $the_mbox_path,
			(array)  $tmp_cfg_get_arr
		);
		//--
		if(((string)$arr_msg_store['message_id'] == '') OR ((string)$arr_msg_store['message_file'] == '') OR ($arr_msg_store['stor_result'] != 1) OR ($arr_msg_store['wr_result'] != 1) OR ((string)$arr_msg_store['error'] != '')) {
			//--
			$store_err[] = 'There was an error storing the message: '.$arr_msg_store['error'];
			//--
		} else {
			//--
			if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
				//--
				$new_uuid = '';
				//--
				$mailget = new \SmartMailerImap4Client();
				//--
				if(\SmartFrameworkRuntime::ifDebug()) {
					$mailget->debug = true;
				} //end if
				//--
				if((string)$tmp_cfg_get_arr['settings_tls'] == 'unsecure') {
					$tmp_cfg_get_arr['settings_tls'] = '';
				} //end if
				//--
				$connect = $mailget->connect($tmp_cfg_get_arr['settings_host'], $tmp_cfg_get_arr['settings_port'], $tmp_cfg_get_arr['settings_tls']);
				//--
				if($connect) {
					//--
					$login = $mailget->login($tmp_cfg_get_arr['settings_auth_username'], \SmartUtils::crypto_blowfish_decrypt((string)$tmp_cfg_get_arr['settings_auth_password']), $tmp_cfg_get_arr['settings_auth_mode']);
					//--
					if($login AND $mailget->is_connected_and_logged_in()) {
						//--
						$mailget->select_mailbox('Sent', true); // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}} :: will create it if does not exists (2nd param is TRUE)
						if(((string)$mailget->error == '') AND ((string)$mailget->get_selected_mailbox() == 'Sent')) {
							// IT IS IMPORTANT TO TRY TO GET THE REAL IMAP4 UID because the message uploaded on server will differ a little and will not match by checksum
							$new_uuid = (string) $mailget->append($arr_send['message']);
							if((string)self::getMessageRealUid($new_uuid, (string)$tmp_cfg_get_arr['settings_type']) == '') {
								$new_uuid = ''; // UID is not a real and valid UID from the IMAP server, it was a fallback UID by IMAP4 client library,  so clear it (does not make sense to update it, is not a real one)
								$store_err[] = 'NOTICE: could not get the stored Sent UID from the IMAP4 Server, will be updated on next sync ...'; // this is only a WARNING, the UID may get later on re-sync by checkum ...
							} //end if
						} else {
							$store_err[] = 'FAILED to Store the Sent Message on the IMAP4 Server, Error Selecting MailBox: Sent = '.$mailget->get_selected_mailbox();
						} //end if
						//--
					} else {
						//--
						$store_err[] = 'FAILED to Login to the IMAP4 Server to Store the Sent message';
						//--
					} //end if
					//--
				} else {
					//--
					$store_err[] = 'FAILED to Connect to the IMAP4 Server to Store the Sent message';
					//--
				} //end if
				//--
				$mailget->noop();
				$mailget->quit();
				//--
				if(((string)\trim((string)$new_uuid) != '') AND ((string)\trim((string)$arr_msg_store['message_id']) != '')) {
					$tmp_arr_upd_uid = (array) $db->updateOneMessageUidById((string)$arr_msg_store['message_id'], (string)$new_uuid);
					if($tmp_arr_upd_uid[1] != 1) {
						$store_err[] = 'WARNING: could not update the message UID in sync with Sent folder on IMAP4';
					} //end if
				} else {
					$store_err[] = 'WARNING: could not initiate update of the message UID in sync with Sent folder on IMAP4';
				} //end if
				//--
			} //end if
			//--
		} //end if
		//--

		//--
		if(\Smart::array_size($store_err) > 0) {
			return 'Message was SENT, but the following Error(s) / Warnings(s) occured while storing the message: '.\implode(' / ', (array)$store_err);
		} //end if
		//--

		//--
		return ''; // OK
		//--

	} //END FUNCTION


	//##### PRIVATES


	// will move message on FileSystem ***only*** if stat_cloud <= 0, otherwise message file will not exists and will only operate the change in DB
	private static function moveOneMessage($db, $stat_cloud, $id, $uid, $old_folder, $new_folder, $the_mbox_path) {
		//--
		if(!\is_a($db, '\\SmartModDataModel\\Cloud\\SqWebmail')) {
			return (string) __METHOD__.' :: The DB Instance is Invalid !';
		} //end if
		if((string)\trim((string)$id) == '') {
			return (string) __METHOD__.' :: Cannot Operate Move for Empty Message ID';
		} //end if
		if((string)\trim((string)$uid) == '') {
			return (string) __METHOD__.'Cannot Operate Move as missing the new UID the Message ID: '.$id;
		} //end if
		//--
		$arr_allowed_folders = (array) self::getAllowedBoxes(true, true); // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
		//--
		if(!\in_array((string)$old_folder, (array)$arr_allowed_folders)) {
			return (string) __METHOD__.'Cannot Operate Move as the New Folder ['.$old_folder.'] is Invalid for the Message ID: '.$id;
		} //end if
		if(!\in_array((string)$new_folder, (array)$arr_allowed_folders)) {
			return (string) __METHOD__.'Cannot Operate Move as the New Folder ['.$new_folder.'] is Invalid for the Message ID: '.$id;
		} //end if
		if(self::checkMboxPathNotOk($the_mbox_path)) {
			return (string) __METHOD__.' Cannot Operate Move as as The MailBox Path is Empty / Invalid or does not exists for the Message ID: '.$id;
		} //end if
		//--
		if((string)$old_folder == (string)$new_folder) {
			return 'Cannot Operate Move in DB for the Message as The Old Folder ['.$old_folder.'] is the same as the New Folder ['.$new_folder.'] for Message ID: '.$id;
		} //end if
		//--
		$path_old = (string) \SmartFileSysUtils::add_dir_last_slash((string)$the_mbox_path).$old_folder;
		if(self::checkMboxPathNotOk($path_old)) {
			return (string) __METHOD__.' Cannot Operate Move as as The Path for Old Folder ['.$old_folder.'] is Unsafe or does not exists for the Message ID: '.$id;
		} //end if
		//--
		$path_new = (string) \SmartFileSysUtils::add_dir_last_slash((string)$the_mbox_path).$new_folder;
		if(self::checkMboxPathNotOk($path_new)) {
			return (string) __METHOD__.' Cannot Operate Move as as The Path for New Folder ['.$path_new.'] is Unsafe or does not exists for the Message ID: '.$id;
		} //end if
		//--
		$the_file = (string) \SmartFileSysUtils::add_dir_last_slash($path_old).$id;
		//--
		if(\SmartFileSystem::is_type_file($the_file)) { // if not file exists, do nothing on File System ..., and should NOT return error
			//--
			if($stat_cloud <= 0) { // if not duplicate or not deleted, move, otherwise the file should not exists and will be removed below anyway ...
				if(!\SmartFileSystem::copy(
					(string) $the_file,
					(string) \SmartFileSysUtils::add_dir_last_slash($path_new).$id,
					true, // overwrite destination
					true  // check copy contents
				)) {
					return 'Cannot Operate Move (Copy) for the Message from ['.$path_old.'] to ['.$path_new.'] for: '.$id;
				} //end if
			} //end if
			//-- do not check $stat_cloud here, just DELETE it, DOESN'T MATTER WHAT THE STATUS IS ; if stat cloud is zero, was copied above so delete it ; otherwise (stat cloud = 1 as duplicate OR = 2 as MARK DELETED) it should not exist ... so delete it now !
			if(!\SmartFileSystem::delete($the_file)) {
				return 'Cannot Operate Move (Delete Old) for the Message: '.$the_file;
			} //end if
			//--
		} //end if
		//--
		$test = $db->moveOneMessageById($id, $new_folder, $uid);
		if($test[1] != 1) {
			return 'Cannot Operate Move (in DB) for the Message: '.$id;
		} //end if
		//--
		return '';
		//--
	} //END FUNCTION



} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
