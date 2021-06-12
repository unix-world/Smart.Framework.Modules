<?php
// Controller: Cloud/WebmailGet
// Route: admin.php?/page/cloud.webmailget
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // admin only
define('SMART_APP_MODULE_AUTH', true); // requires auth always
define('SMART_APP_MODULE_DIRECT_OUTPUT', true);


/**
 * Admin Controller r.20210612
 */
class SmartAppAdminController extends SmartAbstractAppController {

	private $username;
	private $userpath;


	public function Run() {

		//--
		if(SmartAuth::check_login() !== true) {
			if(!headers_sent()) {
				http_response_code(403);
			} //end if
			die(SmartComponents::http_message_403_forbidden('ERROR: WebMail Invalid Auth ...'));
			return;
		} //end if
		//--
		\SmartModExtLib\Cloud\cloudUtils::ensureCloudHtAccess();
		//--
		$this->username = (string) SmartAuth::get_login_id();
		//--
		$safe_user_dir = (string) Smart::safe_username((string)$this->username);
		if(((string)$safe_user_dir == '') OR (SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$safe_user_dir) != '1')) {
			if(!headers_sent()) {
				http_response_code(500);
			} //end if
			die(SmartComponents::http_message_500_internalerror('ERROR: WebMail Unsafe User Dir ...'));
			return;
		} //end if
		//--
		$safe_user_path = (string) 'wpub/cloud/'.$safe_user_dir.'/mail';
		if(SmartFileSysUtils::check_if_safe_path((string)$safe_user_path) != '1') {
			if(!headers_sent()) {
				http_response_code(500);
			} //end if
			die(SmartComponents::http_message_500_internalerror('ERROR: WebMail Unsafe User Path ...'));
			return;
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$safe_user_path) !== true) {
			if(!headers_sent()) {
				http_response_code(500);
			} //end if
			die(SmartComponents::http_message_500_internalerror('ERROR: WebMail User Path does not exists ...'));
			return;
		} //end if
		//--
		$this->userpath = (string) SmartFileSysUtils::add_dir_last_slash((string)$safe_user_path);
		//--

		//-- {{{SYNC-CLOUD-MAIL-CHK-MBOX}}}
		$mbox = (string) trim((string)$this->RequestVarGet('mbox', '', 'string'));
		if((string)$mbox == '') {
			die(SmartComponents::http_message_500_internalerror('ERROR: No WebMail selected for User: '.$this->username));
			return;
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$this->userpath.$mbox) !== true) {
			die(SmartComponents::http_message_500_internalerror('ERROR: Invalid WebMail MailBox ('.$mbox.') selected for User: '.$this->username));
			return;
		} //end if
		//--

		//--
		echo SmartMarkersTemplating::render_file_template(
			$this->ControllerGetParam('module-view-path').'webmailget-start.mtpl.htm',
			(array) SmartComponents::set_app_template_conform_metavars()
		);
		$this->InstantFlush();
		//--

		//--
		$arr_boxes = (array) \SmartModExtLib\Cloud\webmailUtils::getAllowedBoxes(true, true); // allow all, and restrict later {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
		$box = $this->RequestVarGet('box', 'inbox', 'string');
		if(!in_array((string)$box, (array)$arr_boxes)) {
			die(SmartComponents::http_message_400_badrequest('ERROR: Invalid WebMail Box ('.$box.')'));
			return;
		} //end if
		//--
		$log = (string) $this->get_from_server((string)$mbox, (string)$box); // echoes
		//--
		if($this->IfDebug()) {
			echo '<br><hr><br>'.Smart::nl_2_br(Smart::escape_html($log));
			$this->InstantFlush();
		} //end if
		//--
		echo SmartMarkersTemplating::render_file_template(
			$this->ControllerGetParam('module-view-path').'webmailget-end.mtpl.htm',
			(array) SmartComponents::set_app_template_conform_metavars()
		);
		$this->InstantFlush();
		//--

	} //END FUNCTION


	private function print_msg_ok($msg) {
		//--
		if((string)trim((string)$msg == '')) {
			$msg = 'Empty Message OK !...';
		} //end if
		//--
		echo SmartComponents::operation_ok($msg, '96%');
		//--
	} //END FUNCTION


	private function print_msg_notice($msg) {
		//--
		if((string)trim((string)$msg == '')) {
			$msg = 'Empty Message Notice !...';
		} //end if
		//--
		echo SmartComponents::operation_notice($msg, '96%');
		//--
	} //END FUNCTION


	private function print_msg_warn($msg) {
		//--
		if((string)trim((string)$msg == '')) {
			$msg = 'Empty Message Warn !...';
		} //end if
		//--
		echo SmartComponents::operation_warn($msg, '96%');
		//--
	} //END FUNCTION


	private function print_fatal_err($err) {
		//--
		if((string)trim((string)$err == '')) {
			$err = 'Empty Fatal Error !...';
		} //end if
		//--
		echo SmartComponents::operation_error($err, '96%');
		//--
	} //END FUNCTION


	private function print_err($err) {
		//--
		if((string)trim((string)$err == '')) {
			$err = 'Empty Error !...';
		} //end if
		//--
		echo '<br><font color="#FF0000">'.Smart::escape_html((string)$err).'</font>';
		//--
	} //END FUNCTION


	private function get_from_server($mbox, $box) {

		//--
		if(!SmartFileSystem::is_type_dir((string)$this->userpath)) {
			$this->print_fatal_err('#User Mail Dir is Missing !');
			return '';
		} //end if
		//--
		if((string)trim((string)$mbox) == '') {
			$this->print_fatal_err('#Empty MailBox Name !');
			return '';
		} //end if
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name($mbox)) {
			$this->print_fatal_err('#Invalid MailBox Name !');
			return '';
		} //end if
		//--
		$the_mbox_path = (string) SmartFileSysUtils::add_dir_last_slash($this->userpath.$mbox);
		//--
		if(!SmartFileSysUtils::check_if_safe_path($the_mbox_path)) {
			$this->print_fatal_err('#Unsafe MailBox Path !');
			return '';
		} //end if
		if(!SmartFileSystem::is_type_dir($the_mbox_path)) {
			$this->print_fatal_err('Selected MailBox Dir is Missing !');
			return '';
		} //end if
		//--

		//--
		$tmp_cfg_arr = \SmartModExtLib\Cloud\webmailUtils::parseMboxConfig($the_mbox_path, $mbox); // return mixed: err string or array config
		if(!is_array($tmp_cfg_arr)) {
			$this->print_fatal_err('MailBox Config: '.$tmp_cfg_arr);
			return '';
		} //end if
		//--
		$tmp_cfg_send_arr = (array) $tmp_cfg_arr['send'];
		$tmp_cfg_get_arr = (array) $tmp_cfg_arr['get'];
		//--
		if(Smart::array_size($tmp_cfg_get_arr) <= 0) {
			$this->print_fatal_err('ERROR: Invalid WebMail MailBox Configuration [GET] for ('.$mbox.') selected for User: '.$this->username);
			return '';
		} //end if
		//--
		$tmp_cfg_arr = array();
		//--

		//--
		$mailbox_enable_notes = false;
		if($tmp_cfg_get_arr['settings_use_notes'] === true) {
			$mailbox_enable_notes = true;
		} //end if
		//--
		$mailbox_enable_send = false;
		if(Smart::array_size($tmp_cfg_send_arr) > 0) {
			$mailbox_enable_send = true;
		} //end if
		//--

		//--
		$arr_boxes = (array) \SmartModExtLib\Cloud\webmailUtils::getAllowedBoxes($mailbox_enable_send, $mailbox_enable_notes); // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
		//--
		if(!in_array((string)strtolower((string)$box), (array)$arr_boxes)) {
			$this->print_fatal_err('Invalid Box: '.$box);
			return '';
		} //end if
		//--
		$icon_download_item = 'sfi sfi-mail2';
		$store_sync_mode = true;
		$cleanup_all_mark_deleted = false; // if this is set to TRUE will force all get at once (ex: Notes)
		switch((string)strtolower((string)$box)) { // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
			case 'inbox':
				$msg_type = 'message';
				$srv_folder_name = 'INBOX'; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}} :: using it all upercase is safer as a convention to select the inbox if named differently
				$srv_allow_create = false; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}} :: this should exist on server, do not allow create
				$use_the_dir = 'inbox';
				if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
					if($mailbox_enable_send === true) {
						$use_next_dir = 'sent';
					} else {
						$use_next_dir = 'trash';
					} //end if else
				} else {
					$use_next_dir = ''; // after this, stop (for POP3)
				} //end if else
				$use_mark_read = '0'; // after read will be 1
				break;
			case 'sent': // sync just on IMAP4, but NOT on POP3
				$msg_type = 'message';
				if(((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') AND ($mailbox_enable_send === true)) {
					$srv_folder_name = 'Sent'; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					$srv_allow_create = true; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					$use_the_dir = 'sent';
					$use_next_dir = 'trash';
					$use_mark_read = '0'; // after read will be 1
				} else {
					$this->print_fatal_err('Invalid Folder: '.$box);
					return '';
				} //end if else
				break;
			case 'trash':
				$msg_type = 'message';
				if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
					$srv_folder_name = 'Trash'; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					$srv_allow_create = true; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					$use_the_dir = 'trash';
					if($mailbox_enable_notes === true) {
						$use_next_dir = 'notes';
					} else {
						$use_next_dir = ''; // after this, stop
					} //end if else
					$use_mark_read = '0'; // after read will be 1
				} else {
					$this->print_fatal_err('Invalid Folder: '.$box);
					return '';
				} //end if else
				break;
			case 'notes': // sync just on IMAP4, but NOT on POP3
				$icon_download_item = 'sfi sfi-file-text';
				$store_sync_mode = false; // IMPORTANT: never sync Notes as they cannot be moved in other folder than Notes on Server ; if deleted, iOS will delete the note not move to Trash !!
				$msg_type = 'apple-note';
				if(((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') AND ($mailbox_enable_notes === true)) {
					if(((string)trim((string)SmartAuth::get_login_privkey()) == '') OR ((string)trim((string)SmartAuth::get_login_password()) == '')) {
						$this->print_fatal_err('Empty or Invalid User Account Privacy-Key. The Privacy-Key is REQUIRED for Folder: '.$box);
						return '';
					} //end if
					$cleanup_all_mark_deleted = true; // supported just on IMAP4, after iterating all notes on server and delete all mark as delete, the remaining are no more existing on server thus cleanup also locally
					$srv_folder_name = 'Notes'; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					$srv_allow_create = true; // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
					$use_the_dir = 'notes';
					$use_next_dir = '';
					$use_mark_read = '1'; // use marked read 1 by default to avoid update keywords and other info in DB since Notes are encrypted except subject so avoid leak sensitive information in DB which is not encrypted ; if duplicate by Note Universal-UID will increment this by one
				} else {
					$this->print_fatal_err('Invalid Folder: '.$box);
					return '';
				} //end if else
				break;
			default:
				$this->print_fatal_err('Invalid Folder: '.$box);
				return '';
		} //end switch
		//--
		if((string)trim((string)$srv_folder_name) == '') {
			$this->print_fatal_err('Invalid Server Folder: '.$box);
			return '';
		} //end if
		//--

		//--
		if(!SmartFileSystem::is_type_dir($the_mbox_path.$use_the_dir)) {
			SmartFileSystem::dir_create($the_mbox_path.$use_the_dir, false); // do not allow recursive
			if(!SmartFileSystem::is_type_dir($the_mbox_path.$use_the_dir)) {
				$this->print_fatal_err('Inbox MailBox Sub-Dir is Missing !');
				return '';
			} //end if
		} //end if
		//--

		//--
		echo '<br>';
		echo '<table title="'.Smart::escape_html($the_mbox_path.$use_the_dir).'"><tr><td><span style="font-size:1.5rem;"><b>'.Smart::escape_html($mbox).'&nbsp;&nbsp;/&nbsp;&nbsp;'.Smart::escape_html($srv_folder_name).'</b></span></td><td>&nbsp;</td><td align="center" id="img-loader" width="64"><img width="32" height="32" src="lib/framework/img/loading-spin.svg"></td><td align="right" width="64"><img width="64" height="64" src="modules/mod-cloud/views/img/email/folder-'.Smart::escape_html($use_the_dir).'.svg"></td></tr></table>';
		$this->InstantFlush();
		sleep(1);
		//--

		//--
		$storage = new SmartGetFileSystem();
		$arr_storage = $storage->get_storage($this->userpath);
		//--
		$quota_used = Smart::format_number_int($arr_storage['size'], '+');
		$quota_max = Smart::format_number_int(SmartAuth::get_login_quota(), '+');
		//--
		echo '<hr><span style="color:#778899;">Current UserSpace Size is: #Quota='.$quota_max.' / @Used='.$quota_used.'</span><br>';
		echo '<br>';
		$this->InstantFlush();
		//--

		//-- pre-check quota
		if(($quota_max <= 0) OR (($quota_max > 0) AND ($quota_max >= $quota_used))) {
			// OK
		} else {
			$this->print_msg_warn('Your Quota has been reached: '.Smart::escape_html($quota_used).' of '.Smart::escape_html($quota_max));
			return '';
		} //end if else
		//--

		//--
		$tmp_cfg_get_arr['settings_type'] = (string) $tmp_cfg_get_arr['settings_type'];
		//--
		$tmp_cfg_get_arr['settings_host'] = (string) $tmp_cfg_get_arr['settings_host'];
		if((string)trim((string)$tmp_cfg_get_arr['settings_host']) == '') {
			$this->print_fatal_err('Invalid Settings: Empty Server Host');
			return '';
		} //end if
		$tmp_cfg_get_arr['settings_port'] = Smart::format_number_int($tmp_cfg_get_arr['settings_port'], '+');
		if(($tmp_cfg_get_arr['settings_port'] <= 0) OR ($tmp_cfg_get_arr['settings_port'] > 65535)) {
			$this->print_fatal_err('Invalid Settings: Server Port: '.(int)$tmp_cfg_get_arr['settings_port']);
			return '';
		} //end if
		$tmp_cfg_get_arr['settings_tls'] = (string) $tmp_cfg_get_arr['settings_tls'];
		//--
		$tmp_cfg_get_arr['settings_auth_username'] = (string) $tmp_cfg_get_arr['settings_auth_username'];
		$tmp_cfg_get_arr['settings_auth_password'] = (string) SmartUtils::crypto_blowfish_decrypt((string)$tmp_cfg_get_arr['settings_auth_password']);
		if((string)trim((string)$tmp_cfg_get_arr['settings_auth_password']) == '') {
			$this->print_fatal_err('Invalid Settings: Empty Password');
			return '';
		} //end if
		//--
		$tmp_cfg_get_arr['settings_auth_mode'] = (string) $tmp_cfg_get_arr['settings_auth_mode'];
		//--
		$tmp_cfg_get_arr['settings_limit_per_session'] = Smart::format_number_int($tmp_cfg_get_arr['settings_limit_per_session'], '+');
		if($tmp_cfg_get_arr['settings_limit_per_session'] < 0) {
			$tmp_cfg_get_arr['settings_limit_per_session'] = 0;
		} //end if
		if($tmp_cfg_get_arr['settings_limit_per_session'] > 1000) {
			$tmp_cfg_get_arr['settings_limit_per_session'] = 1000; // if have to use a limit then the hard limit is 1000
		} //end if
		//--

		//### check if POP3 or IMAP4

		// TODO: delete old (stat_cloud > 0) if more than 1 year old

		//-- open pop3 connection
		if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
			$mailget = new SmartMailerImap4Client();
		} elseif((string)$tmp_cfg_get_arr['settings_type'] == 'pop3') {
			$use_next_dir = '';
			$mailget = new SmartMailerPop3Client();
		} else {
			$this->print_fatal_err('Invalid Settings: Server Type: '.Smart::escape_html($tmp_cfg_get_arr['settings_type']));
			return '';
		} //end if
		//--
		if($this->IfDebug()) {
			$mailget->debug = true;
		} else {
			$mailget->debug = false;
		} //end if else
		//--
		if((string)$tmp_cfg_get_arr['settings_tls'] == 'unsecure') {
			$tmp_cfg_get_arr['settings_tls'] = '';
		} //end if
		$connect = $mailget->connect($tmp_cfg_get_arr['settings_host'], $tmp_cfg_get_arr['settings_port'], $tmp_cfg_get_arr['settings_tls']);
		//--
		if($connect) {
			//--
			$login = $mailget->login($tmp_cfg_get_arr['settings_auth_username'], $tmp_cfg_get_arr['settings_auth_password'], $tmp_cfg_get_arr['settings_auth_mode']);
			//--
			if($login AND $mailget->is_connected_and_logged_in()) {
				//--
				$db = \SmartModExtLib\Cloud\webmailUtils::getDBStorageObject($the_mbox_path);
				//--
				if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
					//--
					$mailget->select_mailbox((string)$srv_folder_name, (bool)$srv_allow_create);
					//--
					if(((string)$mailget->error != '') OR ((string)$mailget->get_selected_mailbox() != (string)$srv_folder_name)) {
						$mailget->quit();
						$this->print_fatal_err('MAILGET ERROR [SELECTED = '.$mailget->get_selected_mailbox().']: '.$mailget->error);
						return (string) $mailget->log;
					} //end if
					//--
				} //end if
				//--
				$mailget->noop();
				if((string)$mailget->error != '') {
					$mailget->quit();
					$this->print_fatal_err('MAILGET ERROR [NOOP]: '.$mailget->error);
					return (string) $mailget->log;
				} //end if
				//--
				$arr_count = array();
				$arr_count = $mailget->count();
				if((string)$mailget->error != '') {
					$mailget->quit();
					$this->print_fatal_err('MAILGET ERROR [COUNT]: '.$mailget->error);
					return (string) $mailget->log;
				} //end if
				//--
				$count = Smart::format_number_int($arr_count['count'], '+');
				$size = Smart::format_number_int($arr_count['size'], '+');
				//--
				echo 'There are <b>#'.Smart::escape_html(SmartTextTranslations::formatAsLocalNumber($count, 0)).' messages</b> in the MailBox';
				if($size > 0) {
					echo ' with a <b>total size of '.Smart::escape_html(SmartUtils::pretty_print_bytes($size, 2)).'</b>';
				} //end if
				echo '.<br>';
				$this->InstantFlush();
				//--
				$errors = 0;
				//--
				if($count > 0) {
					//--
					$uidls = array(); // no more used
					$cnt_max_get = (int) $count;
					//-- limit get if set
					$cnt_max_limit = $cnt_max_get;
					if((int) $tmp_cfg_get_arr['settings_limit_per_session'] > 0) {
						if((int) $tmp_cfg_get_arr['settings_limit_per_session'] < $cnt_max_get) {
							$cnt_max_limit = (int) $tmp_cfg_get_arr['settings_limit_per_session'];
						} //end if
					} else {
						if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
							$cnt_max_limit = 0;
						} else {
							$cnt_max_limit = 1000; // for POP3 we use a hard limit
						} //end if else
					} //end if
					//--
					if(((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') AND ($cleanup_all_mark_deleted === true)) {
						$cnt_max_limit = 0; // notes have to be handled all at once !!!
					} //end if
					//--
					$tmp_downloaded = 0;
					//--
					if($cnt_max_limit <= 0) {
						$cnt_max_limit = 0; // can't be lower than 0
					} //end if
					//--
					if(((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') AND ($cnt_max_limit == 0)) {
						//--
						$tmp_retr_text = 'Retrieving All-at-Once ...';
						$tmp_retr_mode = 'all-imap4';
						//--
						$tmp_txt_uids = (string) $mailget->uid(); // TODO: parse this with foreach as is not safe to use array values of parse_uidls()
						$tmp_all_uids = (array) $mailget->parse_uidls($tmp_txt_uids); // convert to non-associative array
						//--
						if((int)$count !== (int)Smart::array_size($tmp_all_uids)) {
							$mailget->quit();
							$this->print_fatal_err('MAILGET ERROR [INVALID MAILBOX UIDL COUNT = '.(int)Smart::array_size($tmp_all_uids).'] / TotalCount = '.(int)$count);
							return (string) $mailget->log;
						} //end if
						//--
						$cnt_max_get = Smart::array_size($tmp_all_uids);
						//--
					} else {
						//--
						$tmp_retr_text = 'Retrieving One-by-One / Max-Per-Session: '.(int)$cnt_max_limit.' ...';
						$tmp_retr_mode = 'each';
						//--
						$tmp_txt_uids = '';
						$tmp_all_uids = array();
						//--
						for($i=1; $i<=$cnt_max_get; $i++) { // start at 1 as this is the first Message ID on POP3 or IMAP4 {{{SYNC-IMAP4-POP3-FIRST-MSG-NUM}}}
							$tmp_all_uids[$i] = $i;
						} //end for
						//--
					} //end if
					//--
					echo '<div><h4 style="display:inline-block;">'.Smart::escape_html($tmp_retr_text).'</h4></div>';
					$this->InstantFlush();
					//--
					$cnt_crr = 0;
					foreach($tmp_all_uids as $key => $val) {
						//--
						// $key is the message num ; $val is the message UID (or message num, depends on how it manages)
						//--
						$cnt_crr++; // be sure to increment here (at the begining of loop) to real start at 1 (was init with zero) as this should be the first Message ID on POP3 or IMAP4 {{{SYNC-IMAP4-POP3-FIRST-MSG-NUM}}}
						//--
						if(!$mailget->is_connected_and_logged_in()) {
							$errors += 1;
							$rd_err = 'Server Connection Dropped ...';
							$this->print_err($rd_err);
							break;
						} //end if
						//--
						if((string)$mailget->get_selected_mailbox() != (string)$srv_folder_name) {
							$errors += 1;
							$rd_err = 'Invalid Server MailBox Selected for ['.$srv_folder_name.']: '.$mailget->get_selected_mailbox();
							$this->print_err($rd_err);
							break;
						} //end if
						//--
						if((string)$tmp_retr_mode == 'all-imap4') {
							//--
							$crr_uid = (string) $val; // uid from list
							//--
						} else {
							//--
							$crr_uid = (string) $mailget->uid($key); // get uid for message num
							if((string)$mailget->error != '') {
								$errors += 1;
								$rd_err = 'Server Error: '.$mailget->error;
								$this->print_err($rd_err);
								break; // perthaps UID out of range
							} //end if
							//--
						} //end if else
						//--
						$real_uid = (string) \SmartModExtLib\Cloud\webmailUtils::getMessageRealUid($crr_uid, $tmp_cfg_get_arr['settings_type']);
						//--
						if(((string)$crr_uid != '') AND ((string)trim((string)$real_uid) != '')) { // we can't support messages without UIDs
							//--
							$tmp_rd_arr = (array) $db->getOneMessageByUid($crr_uid, $use_the_dir);
							//--
							if(Smart::array_size($tmp_rd_arr) > 0) {
								//--
								if($tmp_rd_arr['stat_cloud'] == 1) { // marked as deleted on WebMail and the UID is in sync, delete also on Server
									//-- {{{SYNC-WEBMAIL-DELETE-MARK-DELETED}}} ; apply to any: inbox, sent, trash, notes (as marked as deleted is safely managed elsewhere, so is safe here to delete in DB)
									$db->deleteOneMessageById((string)$tmp_rd_arr['id']); // {{{SYNC-WEBMAIL-DELETE-DO-DELETE}}} :: here we know the ID, so delete it here ...
									echo ' <i class="sfi sfi-bin2" style="color:#FF3300;"></i> ';
									if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
										$mailget->delete((string)$real_uid, true); // delete by UID on IMAP4, because after deletion numbering sequence changes instant !!
									} else {
										$mailget->delete($key); // delete this message from server by number
									} //end if else
									$mailget->clear_last_error();
								} //end if
								//--
								// CHECK IF MESSAGES DELETION IS SET TO IMMEDIATELY DELETE FROM SERVER ! (Notes must NOT be deleted from Server !!!)
								// #OR#
								// CHECK IF MESSAGE NEED TO BE DELETED, DELETE IT.
								// check by delete status as of $tmp_rd_arr['stat_cloud']
								/*
								if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
									$mailget->delete($real_uid, true); // delete this message from server by UID
								} else {
									$mailget->delete($key); // delete this message from server by number
								} //end if else
								*/
								//-- WARNING: check if need to delete on IMAP4 !!!
								//echo '.';
								//echo ' <font color="#778899"><b>[EXISTS]</b></font>';
								//--
							} else { // if message is not yet downloaded, download it
								//--
								$tmp_downloaded += 1;
								//--
								if(($cnt_crr > 0) AND (($cnt_crr % 10) == 0)) {
									echo ' ';
								} else {
									echo '<i class="'.Smart::escape_html($icon_download_item).'" style="color:#555555; margin-right:3px;"></i>';
								} //end if else
								if(($cnt_crr % 50) == 0) {
									echo '<br>';
								} //end if
								//--
								$tmp_message_file = '';
								$tmp_stor_result = 0;
								$tmp_wr_result = 0;
								$rd_err = '';
								$tmp_message_content = '';
								$tmp_message_error = '';
								$tmp_message_size = 0;
								//--
								if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
									$tmp_message_content = $mailget->read($real_uid, true); // on imap4 we can get a message by UID
								} else {
									$tmp_message_content = $mailget->read($key); // default retrieve by number
								} //end if else
								//--
								$tmp_message_error = (string) $mailget->error;
								//--
								if((string)$tmp_message_error != '') {
									$errors += 1;
									$rd_err = 'Retrieve Failed for Message ('.$key.'): '.$tmp_message_error;
									$this->print_err($rd_err);
									$tmp_message_content = ''; // be sure to clear for safety, it may be a message part only ... if error
								} else {
									$tmp_message_size = (int) strlen((string)$tmp_message_content); // $mailget->size($key); since this cannot be done by UUID, we do it differently
								} //end if
								//--
								if(((string)$tmp_message_error == '') AND ((string)trim((string)$tmp_message_content) != '') AND (($quota_max <= 0) OR (($quota_max > 0) AND ($quota_max >= ($quota_used + $tmp_message_size))))) {
									//-- check for store duplicates as if another email client will move a message from a inbox to trash and back the UID will change ; in this case avoid duplicates (apply also for other unattended messages move on server from a other boxes to another and back)
									$arr_msg_store = (array) \SmartModExtLib\Cloud\webmailUtils::storeMessage(
										(string) $msg_type,
										(string) $this->username,
										(string) $mbox,
										(bool)   $store_sync_mode, // depends, on Notes do not sync
										$db,     // db model
										(int)    $use_mark_read,
										(string) $crr_uid,
										(string) $tmp_message_content,
										(string) $use_the_dir,
										(string) $the_mbox_path,
										(array)  $tmp_cfg_get_arr
									); // the full message string must be passed here as it must be stored on disk
									//--
									$tmp_is_sync_mode = (bool) $arr_msg_store['sync_mode'];
									$tmp_action_on_server = (string) $arr_msg_store['action_on_server'];
									$tmp_message_id = (string) $arr_msg_store['message_id'];
									$tmp_message_file = (string) $arr_msg_store['message_file'];
									$tmp_stor_result = (int) $arr_msg_store['stor_result'];
									$tmp_err_result = (string) $arr_msg_store['error'];
									$tmp_wr_result = (int) $arr_msg_store['wr_result'];
									//--
									$arr_msg_store = array();
									//--
									if(((bool)$tmp_is_sync_mode === true) AND ((string)$tmp_action_on_server != '')) {
										//--
										$tmp_arr_srv_act = (array) explode(':', (string)trim((string)$tmp_action_on_server));
										if((string)$tmp_arr_srv_act[0] == 'sync') {
											switch((string)$tmp_arr_srv_act[1]) {
												case 'server':
													if((string)$tmp_arr_srv_act[2] == 'delete') { // [sync:server:delete:do] ; {{{SYNC-WEBMAIL-DELETE-MARK-DELETED}}}
														// $db->deleteOneMessageById() {{{SYNC-WEBMAIL-DELETE-DO-DELETE}}} :: here the message ID is unknown so the deletion will be operated in Utils / store() function that will detect duplicate by checksum
														echo ' <i class="sfi sfi-cross" style="color:#FF3300;"></i> '; // marked as deleted on WebMail and the UID is NOT in sync, delete also on Server
														if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
															$mailget->delete((string)$real_uid, true); // delete by UID on IMAP4, because after deletion numbering sequence changes instant !!
														} else {
															$mailget->delete($key); // delete this message from server by number
														} //end if else
														$mailget->clear_last_error();
													} elseif((string)$tmp_arr_srv_act[2] == 'move') { // ['sync:server:move:%folder%']
														if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
															echo ' <i class="sfi sfi-loop" style="color:#FF9900;"></i> ';
															if(in_array((string)$tmp_arr_srv_act[3], (array)$arr_boxes)) { // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
																if($mailget->copy((string)$real_uid, (string)\SmartModExtLib\Cloud\webmailUtils::getServerBoxByFolder($tmp_arr_srv_act[3]), true)) { // copy UID !!! (required on IMAP4)
																	$mailget->clear_last_error();
																	$mailget->delete((string)$real_uid, true); // delete by UID on IMAP4, because after deletion numbering sequence changes instant !!
																} //end if
																$mailget->clear_last_error();
															} else {
																$errors += 1;
																$rd_err = 'Invalid Server Sync Folder: ['.$tmp_arr_srv_act[3].'] for Action: ['.$tmp_arr_srv_act[2].'] for Realm: ['.$tmp_arr_srv_act[1].'] for: '.$key;
																$this->print_err($rd_err);
															} //end if else
														} else {
															$errors += 1;
															$rd_err = 'Unsupported ['.strtoupper((string)$tmp_cfg_get_arr['settings_type']).'] Server Sync Action: ['.$tmp_arr_srv_act[2].'] for Realm: ['.$tmp_arr_srv_act[1].'] for: '.$key;
															$this->print_err($rd_err);
														} //end if else
													} else {
														$errors += 1;
														$rd_err = 'Invalid Server Sync Action: ['.$tmp_arr_srv_act[2].'] for Realm: ['.$tmp_arr_srv_act[1].'] for: '.$key;
														$this->print_err($rd_err);
													} //end if else
													break;
												case 'local':
													if((string)$tmp_arr_srv_act[2] == 'update') {
														if((string)$tmp_arr_srv_act[3] == 'uid') {
															echo ' <i class="sfi sfi-loop2" style="color:#337AB7;"></i> ';
															// OK: nothing to do, was synced [sync:local:update:uid]
														} else {
															$errors += 1;
															$rd_err = 'Invalid Local Sync Result: ['.$tmp_arr_srv_act[3].'] for Action: ['.$tmp_arr_srv_act[2].'] for Realm: ['.$tmp_arr_srv_act[1].'] for: '.$key;
															$this->print_err($rd_err);
														} //end if else
													} else {
														$errors += 1;
														$rd_err = 'Invalid Local Sync Action: ['.$tmp_arr_srv_act[2].'] for Realm: ['.$tmp_arr_srv_act[1].'] for: '.$key;
														$this->print_err($rd_err);
													} //end if else
													break;
												default:
													$errors += 1;
													$rd_err = 'Invalid Sync Realm: ['.$tmp_arr_srv_act[1].'] for: '.$key;
													$this->print_err($rd_err);
											} //end switch
										} else {
											$errors += 1;
											$rd_err = 'Invalid Server Action: ['.$tmp_arr_srv_act[0].'] for: '.$key;
											$this->print_err($rd_err);
										} //end if else
										//--
										$tmp_arr_srv_act = array();
										//--
									} elseif(((string)$tmp_message_id != '') AND ((string)$tmp_message_file != '') AND ((int)$tmp_stor_result == 1) AND ((string)$tmp_err_result == '')) {
										//--
										// OK
										//--
									} else {
										$errors += 1;
										$rd_err = 'Failed to store message on disk: '.$key.' / (ERR='.$tmp_stor_result.' @ '.$tmp_err_result.') '.$tmp_message_file;
										$this->print_err($rd_err);
									} //end if
									//--
								} else { // TODO: Check also message $mailget->size()
									//--
									$errors += 1;
									if((string)$tmp_message_error == '') { // avoid rewrite error message if any
										$rd_err = 'Message too Big ('.$key.') ! Your Quota has been Reached: '.Smart::format_number_dec(($quota_used / 1000 / 1000), 2, '.', '').'MB'.' of '.Smart::format_number_dec(($quota_max / 1000 / 1000), 2, '.', '').'MB';
										$this->print_err($rd_err);
									} //end if else
									//--
								} //end if else
								//--
								$tmp_message_content = ''; // cleanup
								$tmp_message_size = 0;
								//--
								if(($tmp_wr_result == 1) AND ($tmp_stor_result == 1)) { // OK
									//-- CHECK IF MESSAGES DELETION IS SET TO IMMEDIATELY DELETE FROM SERVER ! (Notes must NOT be deleted from Server !!!)
									/*
									if((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') {
										$mailget->delete($real_uid, true); // delete this message from server by UID
									} else {
										$mailget->delete($key); // delete this message from server by number
									} //end if else
									*/
									//-- WARNING: check if need to delete on IMAP4 !!!
								} //end if
								//--
							} //end if else
							//--
						} else {
							//--
							$errors += 1;
							echo '<br><font color="#FF0000">'.'Message #'.(int)$key.' have NO / VALID UID !'.'</font><br>';
							//-- CHECK IF MESSAGES DELETION IS SET TO IMMEDIATELY DELETE FROM SERVER ! (Notes must NOT be deleted from Server !!!)
							/*
							$mailget->delete($key); // delete this message from server by number
							*/
							//-- WARNING: check if need to delete on IMAP4 !!!
							//--
						} //end if
						//--
						if($cnt_max_limit > 0) { // if a limit is used
							if($tmp_downloaded >= $cnt_max_limit) {
								break;
							} //end if
						} //end if
						//--
						$this->InstantFlush();
						//--
					} //end foreach
					//--
					if(((string)$tmp_cfg_get_arr['settings_type'] == 'imap4') AND ($cleanup_all_mark_deleted === true)) {
						echo '<br><b>... CLEANUP ALL DELETED: `'.Smart::escape_html($use_the_dir).'` ...';
						$cleanup_arr = (array) $db->cleanupMarkAsDeletedMessages($use_the_dir); // cleanup mark as deleted notes (they are only stored in Notes and if they marked as deleted in WebMail and not deleted above it means were not existting on server)
						echo ' [#'.(int)$cleanup_arr[1].']</b><br>';
						$cleanup_arr = null;
						$this->InstantFlush();
					} //end if
					//--
					if($cnt_crr > 0) {
						echo '<br><b>['.(int)$cnt_crr.']</b><br>';
						$this->InstantFlush();
					} //end if
					//--
				} //end if
				//--
				$db = null;
				//--
			} else {
				//--
				$mailget->quit();
				$this->print_fatal_err('ERROR: Could NOT Login !');
				return (string) $mailget->log;
				//--
			} //end if
			//--
		} else {
			//--
			$mailget->quit();
			$this->print_fatal_err('ERROR: Could NOT Connect !'.'<hr><small>'.Smart::escape_html($mailget->error).'</small>');
			return (string) $mailget->log;
			//--
		} //end if
		//--
		$mailget->quit();
		//--

		//--
		echo '<br><hr><br>'."\n";
		$this->InstantFlush();
		//--
		if($errors <= 0) {
			if(!$this->IfDebug()) {
				echo '<script>'."\n";
				if((string)$use_next_dir == '') {
					echo SmartViewHtmlHelpers::js_code_wnd_close_modal_popup(3000)."\n";
					echo SmartViewHtmlHelpers::js_code_wnd_refresh_parent($this->ControllerGetParam('url-script').'?page='.Smart::escape_url(substr($this->ControllerGetParam('url-page'), 0, -3)).'&mbox='.Smart::escape_url($mbox).'&box=inbox')."\n";
				} else {
					echo SmartViewHtmlHelpers::js_code_wnd_redirect($this->ControllerGetParam('url-script').'?page='.Smart::escape_url($this->ControllerGetParam('url-page')).'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($use_next_dir), 3000)."\n";
				} //end if else
				echo '</script>'."\n";
			} //end if
			$this->print_msg_ok('OK: Done');
		} else {
			$this->print_msg_notice('There are some Errors / Warnings (see the log for details) ...');
		} //end if else
		//--
		echo '<br>';
		$this->InstantFlush();
		//--

		//--
		return (string) $mailget->log; // prevent other output
		//--

	} //END FUNCTION


} //END CLASS


// end of php code
