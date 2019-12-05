<?php
// Controller: Cloud/WebmailGet
// Route: admin.php?/page/cloud.webmailget
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

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
 * Admin Controller
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

		//--
		echo SmartMarkersTemplating::render_file_template(
			$this->ControllerGetParam('module-view-path').'webmailget-start.mtpl.htm',
			(array) SmartComponents::set_app_template_conform_metavars()
		);
		$this->InstantFlush();
		//--
		$log = (string) $this->get_from_server('iradu@unix-world.org', 'inbox'); // echoes
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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


	private function get_from_server($y_mbx_name, $y_mbx_dir='inbox') {

		//--
		if(!SmartFileSystem::is_type_dir((string)$this->userpath)) {
			echo SmartComponents::operation_error('#User Mail Dir is Missing !');
			return '';
		} //end if
		//--
		if((string)trim((string)$y_mbx_name) == '') {
			echo SmartComponents::operation_error('#Empty MailBox Name !');
			return '';
		} //end if
		$y_mbx_name = Smart::safe_validname($y_mbx_name, '_'); // OK
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name($y_mbx_name)) {
			echo SmartComponents::operation_error('#Invalid MailBox Name !');
			return '';
		} //end if
		//--
		$the_mbox_path = (string) SmartFileSysUtils::add_dir_last_slash($this->userpath.$y_mbx_name);
		//--
		if(!SmartFileSysUtils::check_if_safe_path($the_mbox_path)) {
			echo SmartComponents::operation_error('#Unsafe MailBox Path !');
			return '';
		} //end if
		if(!SmartFileSystem::is_type_dir($the_mbox_path)) {
			echo SmartComponents::operation_error('Selected MailBox Dir is Missing !');
			return '';
		} //end if
		//--

		//--
		switch(strtolower((string)$y_mbx_dir)) {
			/* do not sync trash !!!
			case 'trash':
				$use_the_dir = 'trash';
				$use_next_dir = '';
				$img_get = 'folder-trash.svg';
				break;
			*/
			case 'sent':
				$use_the_dir = 'sent';
				$use_next_dir = ''; // 'trash';
				$use_mark_read = '1';
				$img_get = 'folder-sent.svg';
				break;
			case 'inbox':
			default:
				$use_the_dir = 'inbox';
				$use_next_dir = 'sent';
				$use_mark_read = '0';
				$img_get = 'folder-inbox.svg';
		} //end switch
		//--
		$spam_dir = 'junk';
		//--

		//--
		if(!is_dir($the_mbox_path.$use_the_dir)) {
			echo SmartComponents::operation_error('Inbox MailBox Sub-Dir is Missing !');
			return '';
		} //end if
		//--
		if(!is_dir($the_mbox_path.$spam_dir)) {
			echo SmartComponents::operation_error('Spam MailBox Sub-Dir is Missing !');
			return '';
		} //end if
		//--

		//--
		echo '<br>';
		echo '<table title="'.Smart::escape_html($the_mbox_path.$use_the_dir).'"><tr><td><span style="font-size:1.5rem;"><b>'.Smart::escape_html($y_mbx_name).'&nbsp;&nbsp;/&nbsp;&nbsp;'.Smart::escape_html(ucfirst($use_the_dir)).'</b></span></td><td>&nbsp;</td><td align="center" id="img-loader" width="64"><img width="32" height="32" src="lib/framework/img/loading-spin.svg"></td><td align="right" width="64"><img width="64" height="64" src="lib/core/plugins/img/email/'.$img_get.'"></td></tr></table>';
		$this->InstantFlush();
		sleep(2);
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
			echo SmartComponents::operation_warn('Your Quota has been reached: '.Smart::escape_html($quota_used).' of '.Smart::escape_html($quota_max));
			return '';
		} //end if else
		//--

		//--
		$file_mbx_cfg = (string) $the_mbox_path.'mailbox.json';
		if(!SmartFileSystem::is_type_file($file_mbx_cfg)) {
			echo SmartComponents::operation_error('The MailBox Config is Missing !');
			return '';
		} //end if
		$tmp_cfg_arr = Smart::json_decode(SmartFileSystem::read($file_mbx_cfg));
		//--
		if(Smart::array_size($tmp_cfg_arr) <= 0) {
			echo SmartComponents::operation_error('Invalid MailBox Config !');
			return '';
		} //end if
		//--

		//--
		$tmp_cfg_arr['settings_type'] = (string) $tmp_cfg_arr['settings_type'];
		//--
		$tmp_cfg_arr['settings_host'] = (string) $tmp_cfg_arr['settings_host'];
		if((string)trim((string)$tmp_cfg_arr['settings_host']) == '') {
			echo SmartComponents::operation_error('Invalid Settings: Empty Server Host');
			return '';
		} //end if
		$tmp_cfg_arr['settings_port'] = Smart::format_number_int($tmp_cfg_arr['settings_port'], '+');
		if(($tmp_cfg_arr['settings_port'] <= 0) OR ($tmp_cfg_arr['settings_port'] > 65535)) {
			echo SmartComponents::operation_error('Invalid Settings: Server Port: '.(int)$tmp_cfg_arr['settings_port']);
			return '';
		} //end if
		$tmp_cfg_arr['settings_tls'] = (string) $tmp_cfg_arr['settings_tls'];
		//--
		$tmp_cfg_arr['settings_auth_username'] = (string) $tmp_cfg_arr['settings_auth_username'];
		$tmp_cfg_arr['settings_auth_password'] = (string) SmartUtils::crypto_blowfish_decrypt((string)$tmp_cfg_arr['settings_auth_password']);
		if((string)trim((string)$tmp_cfg_arr['settings_auth_password']) == '') {
			echo SmartComponents::operation_error('Invalid Settings: Empty Password');
			return '';
		} //end if
		//--
		$tmp_cfg_arr['settings_auth_mode'] = (string) $tmp_cfg_arr['settings_auth_mode'];
		//--
		$tmp_cfg_arr['settings_limit_per_session'] = Smart::format_number_int($tmp_cfg_arr['settings_limit_per_session'], '+');
		if($tmp_cfg_arr['settings_limit_per_session'] < 0) {
			$tmp_cfg_arr['settings_limit_per_session'] = 0;
		} //end if
		if($tmp_cfg_arr['settings_limit_per_session'] > 1000) {
			$tmp_cfg_arr['settings_limit_per_session'] = 1000; // hard limit
		} //end if
		//--

		//### check if POP3 or IMAP4

		//-- open pop3 connection
		if((string)$tmp_cfg_arr['settings_type'] == 'imap4') {
			$mailget = new SmartMailerImap4Client();
		} elseif((string)$tmp_cfg_arr['settings_type'] == 'pop3') {
			$use_next_dir = '';
			$mailget = new SmartMailerPop3Client();
		} else {
			echo SmartComponents::operation_error('Invalid Settings: Server Type: '.Smart::escape_html($tmp_cfg_arr['settings_type']));
			return '';
		} //end if
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			$mailget->debug = true;
		} else {
			$mailget->debug = false;
		} //end if else
		//--
		if((string)$tmp_cfg_arr['settings_tls'] == 'unsecure') {
			$tmp_cfg_arr['settings_tls'] = '';
		} //end if
		$connect = $mailget->connect($tmp_cfg_arr['settings_host'], $tmp_cfg_arr['settings_port'], $tmp_cfg_arr['settings_tls']);
		//--
		if($connect) {
			//--
			$login = $mailget->login($tmp_cfg_arr['settings_auth_username'], $tmp_cfg_arr['settings_auth_password'], $tmp_cfg_arr['settings_auth_mode']);
			//--
			if($login) {
				//--
				$db = new \SmartModDataModel\Cloud\SqWebmail($the_mbox_path);
				//--
				if((string)$tmp_cfg_arr['settings_type'] == 'imap4') {
					//--
					$mailget->select_mailbox(ucfirst($use_the_dir), false); // do not create if does not exists
					//--
					if(strlen($mailget->error) > 0) {
						$mailget->select_mailbox(ucfirst($use_the_dir), true); // create if does not exists
						if(strlen($mailget->error) > 0) {
							echo SmartComponents::operation_error('MAILGET ERROR [NOOP]: '.$mailget->error);
							return (string) $mailget->log;
						} //end if
					} //end if
					//--
				} //end if
				//--
				$mailget->noop();
				if(strlen($mailget->error) > 0) {
					echo SmartComponents::operation_error('MAILGET ERROR [NOOP]: '.$mailget->error);
					return (string) $mailget->log;
				} //end if
				//--
				$arr_count = array();
				$arr_count = $mailget->count();
				if(strlen($mailget->error) > 0) {
					echo SmartComponents::operation_error('MAILGET ERROR [COUNT]: '.$mailget->error);
					return (string) $mailget->log;
				} //end if
				//--
				$count = Smart::format_number_int($arr_count['count'], '+');
				$size = Smart::format_number_int($arr_count['size'], '+');
				//--
				echo 'There are #'.SmartTextTranslations::formatAsLocalNumber($count, 0).' messages in the MailBox';
				if($size > 0) {
					echo ' with size of '.SmartUtils::pretty_print_bytes($size);
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
					if((int) $tmp_cfg_arr['settings_limit_per_session'] > 0) {
						if((int) $tmp_cfg_arr['settings_limit_per_session'] < $cnt_max_get) {
							$cnt_max_limit = (int) $tmp_cfg_arr['settings_limit_per_session'];
						} //end if
					} else {
						if((string)$tmp_cfg_arr['settings_type'] == 'imap4') {
							$cnt_max_limit = 0;
						} else {
							$cnt_max_limit = 1000; // for POP3 we use a hard limit
						} //end if else
					} //end if
					//--
					$tmp_downloaded = 0;
					//--
					if($cnt_max_limit <= 0) {
						$cnt_max_limit = 0; // can't be lower than 0
					} //end if
					//--
					if(((string)$tmp_cfg_arr['settings_type'] == 'imap4') AND ($cnt_max_limit == 0)) {
						//--
						$tmp_retr_text = 'Retrieving All-at-Once ...';
						$tmp_retr_mode = 'all-imap4';
						//--
						$tmp_txt_uids = (string) $mailget->uid();
						$tmp_all_uids = (array) $mailget->parse_uidls($tmp_txt_uids);
						//--
						$cnt_max_get = Smart::array_size($tmp_all_uids);
						//--
					} else {
						//--
						$tmp_retr_text = 'Retrieving One-by-One  / Max-Per-Session: '.(int)$cnt_max_limit.' ...';
						$tmp_retr_mode = 'each';
						//--
						$tmp_txt_uids = '';
						$tmp_all_uids = array();
						//--
					} //end if
					//--
					echo '<span style="font-size:15px;"><b>'.Smart::escape_html($tmp_retr_text).'</b></span><br>';
					$this->InstantFlush();
					//--
					for($i=1; $i<=$cnt_max_get; $i++) { // we start at 1 as this is the first MessageID
						//--
						if((string)$tmp_retr_mode == 'all-imap4') {
							//--
							$crr_uid = (string) $tmp_all_uids[$i-1];
							$tmp_xuid = (array) explode('-UID-', $crr_uid);
							$num_uid = (string) trim((string)$tmp_xuid[1]);
							$tmp_xuid = array();
							//--
						} else {
							//--
							$crr_uid = (string) $mailget->uid($i); // uid for message
							$tmp_xuid = array();
							$num_uid = '[none]';
							//--
						} //end if else
						//--
						if(((string)$crr_uid != '') AND ((string)$num_uid != '')) { // we can't support messages without UIDs
							//--
							$tmp_rd_arr = (array) $db->getOneMessageByUid($crr_uid);
							//--
							if(((string)$tmp_rd_arr['id'] == '') AND ((string)$tmp_rd_arr['stat_uid'] == '')) { // if message is not yet downloaded, download it
								//--
								$tmp_downloaded += 1;
								//--
								if(($i > 0) AND (($i % 10) == 0)) {
									echo ' ';
								} else {
									echo '.';
								} //end if else
								if(($i % 100) == 0) {
									echo '<br>';
								} //end if
								$this->InstantFlush();
								//--
								$tmp_stor_result = 0;
								$tmp_wr_result = 0;
								$rd_err = '';
								$tmp_message_content = '';
								$tmp_message_error = '';
								$tmp_message_size = 0;
								//--
								if((string)$tmp_retr_mode == 'all-imap4') {
									$tmp_message_content = $mailget->read($num_uid, true); // on imap4 we can get a message by UID
								} else {
									$tmp_message_content = $mailget->read($i); // default retrieve by number
								} //end if else
								//--
								$tmp_message_error = (string) $mailget->error;
								//--
								if(strlen($tmp_message_error) > 0) {
									$errors += 1;
									$rd_err = 'Retrieve Failed for Message ('.$i.'): '.$tmp_message_error;
								} else {
									$tmp_message_size = strlen($tmp_message_content); // $mailget->size($i); since this cannot be done by UUID, we do it differently
								} //end if
								//--
								if((strlen($tmp_message_error) <= 0) AND (($quota_max <= 0) OR (($quota_max > 0) AND ($quota_max >= ($quota_used + $tmp_message_size))))) {
									//--
									$eml = new SmartMailerMimeDecode();
									$tmp_msg_head = $eml->get_header(SmartUnicode::sub_str($tmp_message_content, 0, 16384)); // we only do a fast decode ... later they can be updated
									//--
									$fldr_y = date('Y', @strtotime($tmp_msg_head['date']));
									$fldr_m = date('Y-m', @strtotime($tmp_msg_head['date']));
									$fldr_d = date('Y-m-d', @strtotime($tmp_msg_head['date']));
									//--
									$tmp_message_sh_folder = (string) Smart::safe_filename($use_the_dir); // this may vary as INBOX or SPAM
									$tmp_message_fname = (string) Smart::safe_filename(substr((string)$use_the_dir, 0, 2).'__'.date('Y_m_d__H_i_s', @strtotime($tmp_msg_head['date'])).'__'.sha1($tmp_cfg_arr['settings_host'].$crr_uid).'.eml');
									$tmp_message_folder = SmartFileSysUtils::add_dir_last_slash($the_mbox_path.$tmp_message_sh_folder);
									//$tmp_message_folder .= $fldr_y.'/'.$fldr_m.'/'.$fldr_d.'/';
									SmartFileSystem::dir_create($tmp_message_folder, true);
									$tmp_message_file = Smart::safe_pathname($tmp_message_folder.$tmp_message_fname);
									//--
									if(strlen($tmp_message_content) > 0) {
										//-- STORE MESSAGE TO FILE
										$tmp_stor_result = SmartFileSystem::write($tmp_message_file, 'Message-Server: '.Smart::normalize_spaces($tmp_cfg_arr['settings_host'].':'.$tmp_cfg_arr['settings_port'])."\r\n".'Message-UID: '.Smart::normalize_spaces($crr_uid)."\r\n".'Message-Size: '.Smart::normalize_spaces($tmp_message_size)."\r\n".'NetOffice-Account: '.Smart::normalize_spaces($this->username)."\r\n".'NetVision-MetaData: #END'."\r\n".$tmp_message_content);
										//-- RECORD UID TO DB
										if(($tmp_stor_result == 1) AND (is_file($tmp_message_file))) {
											//--
											$arr_write 					= array();
											$arr_write['id'] 			= (string) $tmp_message_fname;
											$arr_write['stat_uid'] 		= (string) $crr_uid;
											$arr_write['stat_read'] 	= (int)    $use_mark_read;
											$arr_write['stat_del'] 		= 0;
											$arr_write['date_time'] 	= (string) date('Y-m-d H:i:s', @strtotime($tmp_msg_head['date']));
											$arr_write['folder'] 		= (string) $tmp_message_sh_folder;
											$arr_write['size_kb'] 		= (string) Smart::format_number_dec((@filesize($tmp_message_file) / 1000), 2, '.', '');
											$arr_write['m_priority'] 	= (int) Smart::format_number_int($tmp_msg_head['priority'], '+');
											$arr_write['have_atts'] 	= (int) Smart::format_number_int($tmp_msg_head['attachments']);
											$arr_write['msg_id'] 		= (string) $tmp_msg_head['message-id'];
											$arr_write['msg_inreply'] 	= (string) $tmp_msg_head['in-reply-to'];
											$arr_write['msg_subj'] 		= (string) $tmp_msg_head['subject'];
											$arr_write['from_addr'] 	= (string) $tmp_msg_head['from_addr'];
											$arr_write['from_name'] 	= (string) $tmp_msg_head['from_name'];
											$arr_write['to_addr'] 		= (string) $tmp_msg_head['to_addr'];
											$arr_write['to_name'] 		= (string) $tmp_msg_head['to_name'];
											$arr_write['cc_addr'] 		= (string) $tmp_msg_head['cc_addr'];
											$arr_write['cc_name'] 		= (string) $tmp_msg_head['cc_name'];
											$arr_write['addrss'] 		= ''; // to be updated on first read
											$arr_write['atts'] 			= ''; // to be updated on first read
											$arr_write['keywds'] 		= ''; // to be updated on first read
											//-- OK
											$tmp_wr_result = (array) $db->insertOneMessage((array)$arr_write);
											//--
											$arr_write = array();
											//--
										} else {
											$errors += 1;
											$rd_err = 'Failed to store message on disk: '.$i.' / '.$tmp_message_file;
										} //end if
										//--
									} //end if
									//--
								} else {
									//--
									$errors += 1;
									if(strlen($tmp_message_error) <= 0) { // avoid rewrite error message if any
										$rd_err = 'Message too Big ('.$i.') ! Your Quota has been Reached: '.Smart::format_number_dec(($quota_used / 1000 / 1000), 2, '.', '').'MB'.' of '.Smart::format_number_dec(($quota_max / 1000 / 1000), 2, '.', '').'MB';
									} //end if else
									//--
								} //end if else
								//--
								$tmp_message_content = ''; // cleanup
								$tmp_message_size = 0;
								//--
								if(($tmp_wr_result[1] == 1) AND ($tmp_stor_result == 1)) { // OK
									//-- CHECK IF MESSAGES DELETION IS SET TO IMMEDIATELY DELETE FROM SERVER !
									if((string)$tmp_retr_mode == 'all-imap4') {
										//$mailget->delete($num_uid, true); // delete this message from server by UID
									} else {
										//$mailget->delete($i); // delete this message from server by number
									} //end if else
									//-- WARNING: check if need to delete on IMAP4 !!!
								} //end if
								//--
								if(strlen($rd_err) > 0) {
									echo '<br><font color="#FF0000">'.Smart::escape_html($rd_err).'</font>';
									$this->InstantFlush();
								} //end if
								//--
							} else {
								//--
								// CHECK IF MESSAGE NEED TO BE DELETED, DELETE IT.
								// check by delete status as of $tmp_rd_arr['stat_del']
								if((string)$tmp_retr_mode == 'all-imap4') {
									//$mailget->delete($num_uid, true); // delete this message from server by UID
								} else {
									//$mailget->delete($i); // delete this message from server by number
								} //end if else
								//-- WARNING: check if need to delete on IMAP4 !!!
								//echo '.';
								//echo ' <font color="#778899"><b>[EXISTS]</b></font>';
								$this->InstantFlush();
								//--
							} //end if
							//--
						} else {
							//--
							$errors += 1;
							echo '<br><font color="#FF0000">'.'Message #'.(int)$i.' have NO UID !'.'</font>';
							//--
						} //end if
						//--
						if($cnt_max_limit > 0) { // if a limit is used
							if($tmp_downloaded >= $cnt_max_limit) {
								$i++;
								break;
							} //end if
						} //end if
						//--
					} //end for
					//--
					if($i > 0) {
						echo '<br><b>['.(int)($i - 1).']</b><br>';
						$this->InstantFlush();
					} //end if
					//--
				} //end if
				//--
				unset($db);
				//--
			} else {
				//--
				echo SmartComponents::operation_error('ERROR: Could NOT Login !');
				return (string) $mailget->log;
				//--
			} //end if
			//--
		} else {
			//--
			echo SmartComponents::operation_error('ERROR: Could NOT Connect !'.'<hr><small>'.Smart::escape_html($mailget->error).'</small>', '650');
			return (string) $mailget->log;
			//--
		} //end if
		//--
		$mailget->quit();
		//--

		//--
		echo '<br><hr><br>'."\n";
	//	echo SmartComponents::refresh_parent('admin.php?op=netofx_mbx_lst&mboxname='.rawurlencode($y_mbx_name).'&mboxsub=inbox'); // aaa
		$this->InstantFlush();
		//--
		if($errors <= 0) {
			echo SmartComponents::operation_ok('OK: Done').'<br>';
		//	if((string)$use_next_dir == '') {
		//		echo SmartComponents::close_window(3000);
		//	} else {
		//		echo SmartComponents::redirect_page('admin.php?op=netofx_mbx_get&mboxname='.rawurlencode($y_mbx_name).'&mboxsub='.rawurlencode($use_next_dir), '3000');
		//	} //end if else
		//	$this->InstantFlush();
		} else {
			echo SmartComponents::operation_notice('There are some Warnings (see the log for details) ...').'<br>';
		} //end if
		//--
		$this->InstantFlush();
		//--

		//--
		return (string) $mailget->log; // prevent other output
		//--

	} //END FUNCTION


} //END CLASS


//end of php code
?>