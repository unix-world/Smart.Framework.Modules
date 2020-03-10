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

	// r.20200223
	// ::


	public static function storeMessage($username, $db, $use_mark_read, $crr_uid, $tmp_message_content, $use_the_dir, $the_mbox_path, $tmp_cfg_arr) {
		//--
		$out_arr = [
			'message_file' 	=> '',
			'stor_result' 	=> -1,
			'wr_result' 	=> -1,
			'error' 		=> ''
		];
		//--
		if(!\is_a($db, '\\SmartModDataModel\\Cloud\\SqWebmail')) {
			$out_arr['error'] = __METHOD__.' :: The DB Instance is Invalid !';
			return (array) $out_arr;
		} //end if
		//--
		$tmp_message_content = (string) \trim((string)$tmp_message_content);
		if((string)$tmp_message_content == '') {
			$out_arr['error'] = __METHOD__.' :: The Message Content is Empty !';
			return (array) $out_arr;
		} //end if
		//--
		$eml = new \SmartMailerMimeDecode();
		$tmp_msg_head = (array) $eml->get_header(\SmartUnicode::sub_str($tmp_message_content, 0, 16384)); // we only do a fast decode ... later they can be updated
		//--
		$fldr_y = date('Y', @\strtotime((string)$tmp_msg_head['date']));
		$fldr_m = date('Y-m', @\strtotime((string)$tmp_msg_head['date']));
		$fldr_d = date('Y-m-d', @\strtotime((string)$tmp_msg_head['date']));
		//--
		$tmp_message_sh_folder = (string) \Smart::safe_filename($use_the_dir); // this may vary as INBOX or SPAM
		$tmp_message_fname = (string) \Smart::safe_filename(\substr((string)$use_the_dir, 0, 2).'__'.\date('Y_m_d__H_i_s', @\strtotime((string)$tmp_msg_head['date'])).'__'.\sha1((string)$tmp_cfg_arr['settings_host'].$crr_uid).'.eml');
		$tmp_message_folder = (string) \SmartFileSysUtils::add_dir_last_slash($the_mbox_path.$tmp_message_sh_folder);
		//$tmp_message_folder .= $fldr_y.'/'.$fldr_m.'/'.$fldr_d.'/';
		\SmartFileSystem::dir_create($tmp_message_folder, true);
		$tmp_message_file = (string) \Smart::safe_pathname($tmp_message_folder.$tmp_message_fname);
		//-- STORE MESSAGE TO FILE
		$tmp_stor_result = (int) \SmartFileSystem::write($tmp_message_file, 'Message-Server: '.\Smart::normalize_spaces($tmp_cfg_arr['settings_host'].':'.$tmp_cfg_arr['settings_port'])."\r\n".'Message-UID: '.\Smart::normalize_spaces($crr_uid)."\r\n".'Message-Size: '.\Smart::normalize_spaces((int)strlen((string)$tmp_message_content))."\r\n".'WebMail-Account: '.\Smart::normalize_spaces($username)."\r\n".'WebMail-MetaData: #END'."\r\n".$tmp_message_content);
		$out_arr['stor_result'] = (int) $tmp_stor_result;
		//-- RECORD UID TO DB
		if(($tmp_stor_result == 1) AND (\SmartFileSystem::is_type_file($tmp_message_file))) {
			//--
			$out_arr['message_file'] = (string) $tmp_message_file;
			//--
			$arr_write 					= array();
			$arr_write['id'] 			= (string) $tmp_message_fname;
			$arr_write['stat_uid'] 		= (string) $crr_uid;
			$arr_write['stat_read'] 	= (int)    $use_mark_read;
			$arr_write['stat_del'] 		= (int)    0;
			$arr_write['date_time'] 	= (string) \date('Y-m-d H:i:s', @\strtotime((string)$tmp_msg_head['date']));
			$arr_write['folder'] 		= (string) $tmp_message_sh_folder;
			$arr_write['size_kb'] 		= (string) \Smart::format_number_dec((\SmartFileSystem::get_file_size($tmp_message_file) / 1000), 2, '.', '');
			$arr_write['m_priority'] 	= (int)    \Smart::format_number_int($tmp_msg_head['priority'], '+');
			$arr_write['have_atts'] 	= (int)    \Smart::format_number_int($tmp_msg_head['attachments']);
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
			$out_arr['wr_result'] = (int) $tmp_wr_result[1];
			//--
			$arr_write = array();
			//--
		} //end if
		//--
		return (array) $out_arr;
		//--
	} //END FUNCTION



} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
