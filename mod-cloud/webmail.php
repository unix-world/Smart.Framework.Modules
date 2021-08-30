<?php
// Controller: Cloud/Webmail
// Route: admin.php?/page/cloud.webmail
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // admin only
define('SMART_APP_MODULE_AUTH', true); // requires auth always


/**
 * Admin Controller r.20210830
 */
class SmartAppAdminController extends SmartAbstractAppController {

	private $username;
	private $userpath;
	private $pagelink;
	private $getlink;


	public function Initialize() {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->PageViewSetErrorStatus(500, ' # Mod AuthAdmins is missing !');
			return false;
		} //end if
		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-auth-admins/templates/');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
		return true;
		//--
	} //END FUNCTION


	public function Run() {

		//--
		if(SmartAuth::check_login() !== true) {
			$this->PageViewSetErrorStatus(403, 'ERROR: WebMail Invalid Auth ...');
			return;
		} //end if
		//--
		\SmartModExtLib\Cloud\cloudUtils::ensureCloudHtAccess();
		//--
		$this->username = (string) SmartAuth::get_login_id();
		//--
		$safe_user_dir = (string) Smart::safe_username((string)$this->username);
		if(((string)$safe_user_dir == '') OR (SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$safe_user_dir) != '1')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebMail Unsafe User Dir ...');
			return;
		} //end if
		//--
		$safe_user_path = (string) 'wpub/cloud/'.$safe_user_dir.'/mail';
		if(SmartFileSysUtils::check_if_safe_path((string)$safe_user_path) != '1') {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebMail Unsafe User Path ...');
			return;
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$safe_user_path) !== true) {
			$this->PageViewSetErrorStatus(500, 'ERROR: WebMail User Path does not exists ...');
			return;
		} //end if
		//--
		$this->userpath = (string) SmartFileSysUtils::add_dir_last_slash((string)$safe_user_path);
		if(!SmartFileSystem::is_type_file($this->userpath.'.htaccess')) {
			SmartFileSystem::write($this->userpath.'.htaccess', '### Smart.Framework // Cloud.WebMail @ HtAccess Data Protection ###'."\n\n".trim((string)SMART_FRAMEWORK_HTACCESS_NOINDEXING)."\n".trim((string)SMART_FRAMEWORK_HTACCESS_FORBIDDEN)."\n");
			if(!SmartFileSystem::is_type_file($this->userpath.'.htaccess')) {
				$this->PageViewSetErrorStatus(500, 'ERROR: WebMail HTAccess does not exists and could not be created ...');
				return;
			} //end if
		} //end if
		//--

		//--
		$this->pagelink = (string) $this->ControllerGetParam('url-script').'?page='.$this->ControllerGetParam('url-page');
		$this->getlink = (string) $this->ControllerGetParam('url-script').'?page='.$this->ControllerGetParam('url-page').'get';
		//--

		//-- Main Screen (select a mailbox) {{{SYNC-CLOUD-MAIL-CHK-MBOX}}}
		$mbox = (string) trim((string)$this->RequestVarGet('mbox', '', 'string'));
		if((string)$mbox == '') {
			//--
			$storage = new SmartGetFileSystem(true);
			$arr_storage = $storage->get_storage($this->userpath, false);
			$arr_mboxes = [];
			for($i=0; $i<Smart::array_size($arr_storage['list-dirs']); $i++) {
				if(strpos((string)$arr_storage['list-dirs'][$i], '@') !== false) {
					$arr_mboxes[] = (string) $arr_storage['list-dirs'][$i];
				} //end if
			} //end for
			$arr_storage = [];
			$storage = null;
			//--
			$this->PageViewSetVars([
				'title' => 'WebMail',
				'main' => (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'webmail.mtpl.inc.htm',
					[
						'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
						'CLOUD-USERNAME' 	=> (string) $this->username,
						'CLOUD-MAILBOX' 	=> (string) '',
						'AREA-HTML-HBAR' 	=> '',
						'AREA-HTML-VBAR' 	=> (string) SmartMarkersTemplating::render_file_template(
							$this->ControllerGetParam('module-view-path').'partials/webmail-part-select-mbox-vbar.mtpl.inc.htm',
							[
								'MODULE-PATH' => (string) $this->ControllerGetParam('module-path')
							]
						),
						'AREA-HTML-CONTENT' => (string) SmartMarkersTemplating::render_file_template(
							$this->ControllerGetParam('module-view-path').'partials/webmail-part-select-mbox.mtpl.inc.htm',
							[
								'MODULE-PATH' => (string) $this->ControllerGetParam('module-path'),
								'URL-PAGE' => (string) $this->pagelink,
								'ARR-MBOXES' => (array) $arr_mboxes
							]
						)
					]
				)
			]);
			//--
			return;
			//--
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$this->userpath.$mbox) !== true) {
			die(SmartComponents::http_message_500_internalerror('ERROR: Invalid WebMail MailBox ('.$mbox.') selected for User: '.$this->username));
			return;
		} //end if
		//--

		//--
		$tmp_cfg_arr = \SmartModExtLib\Cloud\webmailUtils::parseMboxConfig($this->userpath.$mbox, $mbox); // return mixed: err string or array config
		if(!is_array($tmp_cfg_arr)) {
			die(SmartComponents::http_message_500_internalerror('ERROR: Invalid WebMail MailBox Configuration for ('.$mbox.') selected for User: '.$this->username.' # '.$tmp_cfg_arr));
			return;
		} //end if
		//--
		$tmp_cfg_get_arr = (array) $tmp_cfg_arr['get'];
		if(Smart::array_size($tmp_cfg_get_arr) <= 0) {
			die(SmartComponents::http_message_500_internalerror('ERROR: Invalid WebMail MailBox Configuration [GET] for ('.$mbox.') selected for User: '.$this->username));
			return;
		} //end if
		//--
		$mailbox_enable_notes = false;
		if($tmp_cfg_get_arr['settings_use_notes'] === true) {
			$mailbox_enable_notes = true;
		} //end if
		//--
		$tmp_cfg_send_arr = (array) $tmp_cfg_arr['send'];
		$mailbox_enable_send = false;
		if(Smart::array_size($tmp_cfg_send_arr) > 0) {
			$mailbox_enable_send = true;
		} //end if
		//--
		// TODO: deep check ths configuration for get / *send (if defined)
		//--
		$tmp_cfg_arr = array();
		//--

		//--
		$arr_boxes = (array) \SmartModExtLib\Cloud\webmailUtils::getAllowedBoxes($mailbox_enable_send, $mailbox_enable_notes); // {{{SYNC-WEBMAIL-IMAP4-FOLDERS}}}
		//--
		$box = $this->RequestVarGet('box', '', 'string');
		if(!in_array((string)$box, (array)$arr_boxes)) {
			$this->PageViewSetErrorStatus(404, 'ERROR: Invalid WebMail Box: '.$box);
			return 404;
		} //end if
		//--
		$op = $this->RequestVarGet('op', '', 'string');
		//--
		switch((string)$op) {
			case 'msgs-sel-action':
				//--
				$action = $this->RequestVarGet('action', '', 'string');
				$sel = $this->RequestVarGet('sel', [], 'array');
				if(!is_array($sel)) {
					$sel = [];
				} //end if
				//--
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'application/json');
				//--
				$ajx_title = (string) strtoupper((string)$action).' Selected Messages: #'.Smart::array_size($sel);
				//--
				$err_sel = '';
				//--
				if(!$err_sel) {
					if(Smart::array_size($sel) <= 0) {
						$err_sel = 'No Messages Selected';
					} //end if
				} //end if
				//--
				if(!$err_sel) {
					switch((string)$action) { // {{{SYNC-WEBMAIL-ACTION}}}
						case 'delete':
							break;
						case 'restore':
							if((string)$box != 'trash') {
								$err_sel = 'Cannot Restore from: '.$box;
							} //end if
							break;
						default:
							$err_sel = 'Invalid Action Selected: '.$action;
					} //end switch
				} //end if
				//--
				if(!$err_sel) {
					$err_sel = (string) \SmartModExtLib\Cloud\webmailUtils::handleSelectedMessages($sel, $this->username, $this->userpath, $mbox, $box, $action);
				} //end if
				//--
				if(!$err_sel) {
					$ajx_status = 'OK';
					$ajx_message = 'Operation Completed';
				} else {
					$ajx_status = 'ERROR';
					$ajx_message = 'Warning: '.$err_sel;
				} //end if else
				//--
				$this->PageViewSetVar(
					'main',
					(string) SmartViewHtmlHelpers::js_ajax_replyto_html_form(
						(string) $ajx_status,
						(string) $ajx_title,
						(string) $ajx_message
					)
				);
				return;
				//--
				break;
			case 'send-json-msg':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'application/json');
				$form = $this->RequestVarGet('webmail', [], 'array');
				//--
				$err_send = (string) \SmartModExtLib\Cloud\webmailUtils::sendEmail($this->username, $this->userpath, $mbox, $form);
				//--
				$ajx_title = 'Send Message: <'.$mbox.'>';
				if((string)$err_send == '') {
					$ajx_status = 'OK';
					$ajx_message = 'Message Sent';
					$ajx_jseval = (string) SmartViewHtmlHelpers::js_code_disable_away_page().' '.SmartViewHtmlHelpers::js_code_wnd_refresh_parent($this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box=sent').' '.SmartViewHtmlHelpers::js_code_wnd_close_modal_popup(5000);
				} else {
					$ajx_status = 'ERROR';
					$ajx_message = 'Message Send ERROR: '.$err_send;
					$ajx_jseval = '';
				} //end if else
				//--
				$this->PageViewSetVar(
					'main',
					(string) SmartViewHtmlHelpers::js_ajax_replyto_html_form(
						(string) $ajx_status,
						(string) $ajx_title,
						(string) $ajx_message,
						'', // redirect
						'', '',
						(string) $ajx_jseval
					)
				);
				return;
				//--
				break;
			case 'list-json-mbox':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'application/json');
				//--
				$ofs = $this->RequestVarGet('ofs', '', 'integer+');
				$sortby = $this->RequestVarGet('sortby', '', 'string');
				$sortdir = $this->RequestVarGet('sortdir', 'ASC', ['ASC','DESC']);
				$srcby = $this->RequestVarGet('srcby', '', 'string');
				$src = $this->RequestVarGet('src', '', 'string');
				//--
				$this->PageViewSetVar(
					'main',
					(string) $this->listJsonMailbox($mbox, $box, $ofs, $sortby, $sortdir, $srcby, $src)
				);
				//--
				return;
				//--
				break;
			default:
				//--
				// nothing to do, go below
				//--
		} //end switch
		//--

		//--
		$msg = $this->RequestVarGet('msg', '', 'string');
		//--
		if((string)$msg != '') {
			//--
			$id = $this->RequestVarGet('id', '', 'string');
			$reply = $this->RequestVarGet('reply', '', 'string');
			//--
			if($reply) { // Reply to Message
				//--
				$arr_repl = (array) SmartMailerMimeParser::get_message_data_structure(
					((string)$box == 'notes') ? 'apple-note' : 'message',
					(string) $msg,
					(string) $this->secretKey(),
					'data-reply', // 'data-full' | 'data-reply'
					$this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($box).'&op=view-message&msg={{{MESSAGE}}}&rawmode={{{RAWMODE}}}&mime={{{MIME}}}&disp={{{DISP}}}&mode={{{MODE}}}',
					'_self',
					'print' // need to be print to avoid re-linking with real-links
				);
				//--
				$this->PageViewSetVar(
					'main',
					(string) $this->displayComposer((string)$mbox, (string)$box, 'reply', (array)$arr_repl, (string)$id, (string)$msg)
				);
				//--
			} else { // Display or Forward Message
				//--
				$forward = $this->RequestVarGet('forward', '', 'string');
				//--
				if($forward) { // Forward Message
					//--
					$arr_repl = (array) SmartMailerMimeParser::get_message_data_structure(
						((string)$box == 'notes') ? 'apple-note' : 'message',
						(string) $msg,
						(string) $this->secretKey(),
						'data-full', // 'data-full' | 'data-reply'
						$this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($box).'&op=view-message&msg={{{MESSAGE}}}&rawmode={{{RAWMODE}}}&mime={{{MIME}}}&disp={{{DISP}}}&mode={{{MODE}}}',
						'_self',
						'print' // need to be print to avoid re-linking with real-links
					);
					//--
					$this->PageViewSetVar(
						'main',
						(string) $this->displayComposer((string)$mbox, (string)$box, 'forward', (array)$arr_repl, (string)$id, (string)$msg)
					);
					//--
				} else { // Display Message
					//--
					$this->displayMimeMessage($mbox, $box, $msg, $id);
					//--
				} //end if else
				//--
			} //end if else
			//--
		} else {
			//--
			$compose = $this->RequestVarGet('compose', '', 'string');
			//--
			if($compose) { // compose new message
				//--
				$this->PageViewSetVar(
					'main',
					(string) $this->displayComposer((string)$mbox, (string)$box, 'compose')
				);
				//--
			} else { // list the folder
				//--
				$the_mbox_path = $this->mboxPath($mbox);
				$db = new \SmartModDataModel\Cloud\SqWebmail($the_mbox_path); // open connection / initialize
				//--
				$this->PageViewSetVars([
					'title' => 'WebMail',
					'main' => (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'webmail.mtpl.inc.htm',
						[
							'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
							'CLOUD-USERNAME' 	=> (string) $this->username,
							'CLOUD-MAILBOX' 	=> (string) $mbox,
							'ENABLE-SEND' 		=> (string) (($mailbox_enable_send === true) ? 'yes' : 'no'),
							'ENABLE-NOTES' 		=> (string) (($mailbox_enable_notes === true) ? 'yes' : 'no'),
							'AREA-HTML-HBAR' 	=> '',
							'AREA-HTML-VBAR' 	=> (string) SmartMarkersTemplating::render_file_template(
								$this->ControllerGetParam('module-view-path').'partials/webmail-part-list-mbox-vbar.mtpl.inc.htm',
								[
									'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
									'URL-PAGE' 			=> (string) $this->pagelink,
									'URL-GET' 			=> (string) $this->getlink,
									'ENABLE-SEND' 		=> (string) (($mailbox_enable_send === true) ? 'yes' : 'no'),
									'ENABLE-NOTES' 		=> (string) (($mailbox_enable_notes === true) ? 'yes' : 'no'),
									'CURRENT-MBOX' 		=> (string) $mbox,
									'CURRENT-BOX' 		=> (string) $box,
									'BOXES' 			=> (array)  $arr_boxes,
									'SIZE-KB-HTML' 		=> (string) SmartUtils::pretty_print_bytes((int)$db->listSizeAllRecords($box), 2, '&nbsp;')
								]
							),
							'AREA-HTML-CONTENT' => (string) SmartMarkersTemplating::render_file_template(
								$this->ControllerGetParam('module-view-path').'partials/webmail-part-list-mbox.mtpl.inc.htm',
								[
									'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
									'URL-PAGE' 			=> (string) $this->pagelink,
									'ENABLE-SEND' 		=> (string) (($mailbox_enable_send === true) ? 'yes' : 'no'),
									'ENABLE-NOTES' 		=> (string) (($mailbox_enable_notes === true) ? 'yes' : 'no'),
									'CURRENT-MBOX' 		=> (string) $mbox,
									'CURRENT-BOX' 		=> (string) $box
								]
							)
						]
					)
				]);
				//--
			} //end if else
			//--
		} //end if else
		//--

	} //END FUNCTION


	private function mboxPath($mbox) {
		//--
		if((!$mbox) OR (!SmartFileSysUtils::check_if_safe_file_or_dir_name($mbox))) {
			Smart::raise_error(__METHOD__.'() :: MailBox parameter is Empty or Invalid: '.$mbox);
			return 'tmp/#invalid@mail.box#';
		} //end if
		//--
		return (string) SmartFileSysUtils::add_dir_last_slash((string)$this->userpath.$mbox);
		//--
	} //END FUNCTION


	private function secretKey() {
		//--
		return (string) $this->ControllerGetParam('controller').':'.sha1($this->userpath.'|'.$this->username.'|'.SMART_FRAMEWORK_SECURITY_KEY);
		//--
	} //END FUNCTION


	private function markMessageAsRead($model, $mbox, $box, $msg, $id) {
		//--
		if(!is_a($model, '\\SmartModDataModel\\Cloud\\SqWebmail')) {
			Smart::log_warning(__METHOD__.' :: Invalid DB Model');
			return;
		} //end if
		//--
		if((string)$mbox == '') {
			Smart::log_warning(__METHOD__.' :: Empty MailBox');
			return;
		} //end if
		if((string)$box == '') {
			Smart::log_warning(__METHOD__.' :: Empty MailBox Box');
			return;
		} //end if
		if((string)$msg == '') {
			Smart::log_warning(__METHOD__.' :: Empty Message Link');
			return;
		} //end if
		if((string)$id == '') {
			Smart::log_warning(__METHOD__.' :: Empty MailBox ID');
			return;
		} //end if
		//--
		$wr = (array) $model->markOneMessageAsReadById($id);
		//--
		if($wr[1] == 1) { // update the rest just on first read
				//--
				$arr_msg = (array) SmartMailerMimeParser::get_message_data_structure(
					((string)$box == 'notes') ? 'apple-note' : 'message',
					(string) $msg,
					(string) $this->secretKey(),
					'data-full'
				);
				//print_r($arr_msg); die();
				if((int)$arr_msg['atts_num'] > 0) {
					$model->updOneMessageAttsById($id, (int)$arr_msg['atts_num'], (string)$arr_msg['atts_lst']);
				} //end if
				//--
				$arr_msg = (array) SmartMailerMimeParser::get_message_data_structure(
					((string)$box == 'notes') ? 'apple-note' : 'message',
					(string) $msg,
					(string) $this->secretKey(),
					'data-reply'
				);
				//print_r($arr_msg); die();
				$model->updOneMessageKeywordsById($id, (string)SmartUtils::extract_keywords((string)$arr_msg['message'], 255));
				//--
				$arr_msg = null; // free mem
				//--
		} //end if
		//--
	} //END FUNCTION


	private function displayMimeMessage($mbox, $box, $msg, $id='') {
		//--
		if((string)$mbox == '') {
			return;
		} //end if
		if((string)$box == '') {
			return;
		} //end if
		if((string)$msg == '') {
			return;
		} //end if
		//--
		$the_mbox_path = $this->mboxPath($mbox);
		if(!SmartFileSystem::is_type_dir($the_mbox_path)) {
			return;
		} //end if
		//--
		$mode = $this->RequestVarGet('mode', '', 'string');
		$pdf = $this->RequestVarGet('pdf', '', 'string');
		$rawmode = $this->RequestVarGet('rawmode', '', 'string');
		if((string)$rawmode != 'raw') {
			$rawmode = '';
		} //end if
		//--
		$use_sandbox = false;
		$mime_mode = '';
		$bttns_area = '';
		//--
		if(((string)$mode == '') AND ((string)$pdf == '')) { // it uses auto sandbox
			//--
			$display_repl_fwd_bttns = true;
			if((string)$rawmode == '') {
				//--
				if((string)$id == '') {
					return;
				} //end if
				//--
				$model = new \SmartModDataModel\Cloud\SqWebmail($the_mbox_path); // open connection / initialize
				//--
				$rd = (array) $model->getOneMessageById($id);
				if(((int)$rd['stat_read'] <= 0) AND ((string)$rd['ifolder'] != 'notes')) { // do not update messages in notes folder or messages that are already marked as read
					$this->markMessageAsRead($model, $mbox, $box, $msg, $id);
				} //end if
				if((string)$rd['ifolder'] == 'notes') {
					$display_repl_fwd_bttns = false;
				} //end if
				//--
				$rd = null;
				$model = null; // close DB connection
				//--
			} //end if
			//--
			if($display_repl_fwd_bttns !== false) { // hide Reply and Forward buttons if Note
				$bttns_area = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'partials/webmail-display-actions.mtpl.inc.htm',
					[
						'ACTION-REPLY' 		=> (string) $this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($box).'&reply=yes&id='.Smart::escape_url((string)$id).'&msg='.Smart::escape_url((string)$msg),
						'ACTION-FORWARD' 	=> (string) $this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($box).'&forward=yes&id='.Smart::escape_url((string)$id).'&msg='.Smart::escape_url((string)$msg),
						'PDF-ACTIVE' 		=> (string) ($this->isPdfActive() ? 'yes' : 'no')
					]
				);
			} //end if
			//--
		} elseif(((string)$mode == 'print') AND ((string)$pdf == '')) {
			$use_sandbox = true;
			$mime_mode = 'print';
			$bttns_area = '';
		} else { // PDF
			$mime_mode = 'print';
			$bttns_area = '';
		} //end if else
		//--
		if((string)$mode != 'partial') {
			$mime_ttl = ((string)$box == 'notes') ? 'Note' : 'eMail Message';
		} else { // partial
			$use_sandbox = true;
			$mime_ttl = ((string)$box == 'notes') ? 'Note Part' : 'Message Part';
		} //end if else
		//--
		$main = (string) SmartMailerMimeParser::display_message(
			((string)$box == 'notes') ? 'apple-note' : 'message',
			(string) $msg,
			(string) $this->secretKey(),
			$this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($box).'&msg={{{MESSAGE}}}&rawmode={{{RAWMODE}}}&mime={{{MIME}}}&disp={{{DISP}}}&mode={{{MODE}}}',
			'_self',
			'<div align="left"><h1 style="display:inline; color:#333333;">'.Smart::escape_html($mime_ttl).'</h1></div>'.$bttns_area,
			(string) $mime_mode // 'default' | 'print'
		);
		//-- sandbox if required (print mode) :: if non-print mode will use automatically ; for print mode must be custom implemented !
		if($use_sandbox === true) {
			$main = '<div title="WebMail HTML Safe SandBox / iFrame" style="position:relative;"><img height="16" src="lib/core/plugins/img/email/safe.svg" style="cursor:help; position:absolute; top:3px; left:7px; opacity:0.25;"><iframe name="WebMailMessageSandBox" id="WebMailMessageSandBox" scrolling="auto" marginwidth="5" marginheight="5" hspace="0" vspace="0" frameborder="0" style="width:97vw; min-height:97vh; height:max-content; border:1px solid #EFEFEF;" sandbox="allow-same-origin" srcdoc="'.Smart::escape_html('<!DOCTYPE html><html><head><title>'.Smart::escape_html($mime_ttl).'</title><meta charset="'.Smart::escape_html(SMART_FRAMEWORK_CHARSET).'">'.'<style>'."\n".trim((string)SmartFileSystem::read('lib/core/css/base.css'))."\n".'</style>'.'</head><body>'.$main.'<script>alert(\'If you can see this alert the WebMail iFrame Sandbox is unsafe ...\');</script></body></html>').'"></iframe></div>';
		} //end if
		//-- forwarder for misc email parts
		if(((string)$rawmode == 'raw') AND ((string)$mode != 'partial')) { // msg raw parts such as images (cids)
			//--
			$this->PageViewSetCfg('rawpage', true);
			//--
			$test_rawmime = $this->RequestVarGet('mime', '', 'string');
			$enforce_better_detect_mime_and_disp = null;
			if((string)$test_rawmime != '') {
				$test_rawmime = (string) SmartUtils::url_obfs_decode((string)$test_rawmime);
				if((string)$test_rawmime == 'image') { // {{{SYNC-BETTER-CID-IMGS-DETECTION-OF-MIMETYPE}}} FIX: SVGs don't function with mime type 'image', they need 'image/svg+xml'
					$test_img_type = (string) SmartDetectImages::guess_image_extension_by_img_content((string)$main, false); // don't use GD, too expensive
					if((string)$test_img_type != '') {
						$test_rawmime = (array) SmartFileSysUtils::mime_eval('mime-image-'.sha1($main).$test_img_type, 'inline');
						$enforce_better_detect_mime_and_disp = (string) $test_rawmime[1];
						$test_rawmime = (string) $test_rawmime[0];
					} //end if
				} //end if
				$this->PageViewSetCfg('rawmime', (string)$test_rawmime);
			} //end if
			//--
			if($enforce_better_detect_mime_and_disp !== null) {
				$this->PageViewSetCfg('rawdisp', (string)$enforce_better_detect_mime_and_disp);
			} else {
				$test_rawdisp = $this->RequestVarGet('disp', '', 'string');
				if((string)$test_rawdisp != '') {
					$test_rawdisp = (string) SmartUtils::url_obfs_decode((string)$test_rawdisp);
					$this->PageViewSetCfg('rawdisp', (string)$test_rawdisp);
				} //end if
			} //end if else
			//--
		} else { // default, partial (but not raw), pdf
			//--
			if((string)$pdf != '') {
				if($this->isPdfActive()) {
					$this->PageViewSetCfg('rawpage', true);
					$this->PageViewSetCfg('rawmime', (string)\SmartModExtLib\PdfGenerate\PdfUtils::pdf_mime_header());
					$this->PageViewSetCfg('rawdisp', (string)\SmartModExtLib\PdfGenerate\PdfUtils::pdf_disposition_header('message-'.time().'.pdf', 'inline')); // TODO: since the msg file name is encrypted we need a way to get it
					$main = (string) \SmartModExtLib\PdfGenerate\HtmlToPdfExport::generate((string)$main, 'normal', 'auto'); // auto allow credentials
				} else {
					$this->PageViewSetErrorStatus(500, 'ERROR: PDF Generator is missing or not active ...');
					return;
				} //end if else
			} else {
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
			} //end if else
			//--
		} //end if
		//--
		$this->PageViewSetVar(
			'main',
			(string) $main
		);
		//--
	} //END FUNCTION


	private function isPdfActive() {
		//--
		$is_active_pdf = false;
		if(SmartAppInfo::TestIfModuleExists('mod-pdf-generate')) {
			if(\SmartModExtLib\PdfGenerate\HtmlToPdfExport::is_active()) {
				$is_active_pdf = true;
			} //end if
		} //end if else
		//--
		return (bool) $is_active_pdf;
		//--
	} //END FUNCTION


	private function listJsonMailbox($mbox, $box, $ofs, $sortby, $sortdir, $srcby, $src) {
		//--
		$the_mbox_path = $this->mboxPath($mbox);
		//--
		$model = new \SmartModDataModel\Cloud\SqWebmail($the_mbox_path); // open connection / initialize
		//--
		$sort_type = '';
		switch((string)$sortby) {
			case 'size_kb':
				$sort_type = 'numeric';
				break;
			default:
				$sort_type = 'text';
		} //end switch
		//--
		$limit = 25;
		//--
		$data = [
			'status'  			=> 'OK',
			'crrOffset' 		=> (int)    $ofs,
			'itemsPerPage' 		=> (int)    $limit,
			'sortBy' 			=> (string) $sortby,
			'sortDir' 			=> (string) $sortdir,
			'sortType' 			=> (string) $sort_type, // applies only with clientSort (not used for Server-Side sort)
			'filter' 			=> [
				'srcby' => (string) $srcby,
				'src' 	=> (string) $src
			],
			'totalRows' 		=> (int)    $model->listCountRecords((string)$box, (string)$srcby, (string)$src),
			'rowsList' 			=> (array)  $model->listGetRecords((string)$box, (string)$srcby, (string)$src, (int)$limit, (int)$ofs, (string)$sortdir, (string)$sortby)
		];
		//--
		unset($model); // close connection
		//--
		for($i=0; $i<count($data['rowsList']); $i++) {
			//--
			$val = (array) $data['rowsList'][$i];
			$data['rowsList'][$i]['@link'] = (string) $this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($box).'&id='.Smart::escape_url($val['id']).'&msg='.Smart::escape_url(SmartMailerMimeParser::encode_mime_fileurl(
				(string) rtrim($the_mbox_path,'/').'/'.rtrim($val['folder'],'/').'/'.ltrim($val['id'],'/'),
				(string) $this->secretKey()
			));
			//--
		} //end for
		//--
		return (string) Smart::json_encode((array)$data);
		//--
	} //END FUNCTION


	private function displayComposer($mbox, $box, $mode, $msg_arr=[], $msg_id='', $msg_url='') {
		//--
		$composer_height_htmledit = '70vh';
		$composer_height_msgedit = '60vh';
		//--
		if((string)$mode == 'reply') {
			$composer_title = 'Reply to Message';
			$composer_replytoaddr = (string) trim((string)$msg_arr['from']);
			$composer_inreplyto = (string) trim((string)$msg_arr['message-id']);
			$composer_to = (string) trim((string)$msg_arr['from']);
			$composer_subject = (string) trim((string)$msg_arr['subject']);
			if(stripos((string)$composer_subject, 'Re:') !== 0) {
				$composer_subject = 'Re: '.$composer_subject;
			} //end if
			$composer_arr_atts = [];
			$composer_msg = '<div style="background:#FFFFFF; color:#111111; padding:5px;"><br><br><br><hr><div style="color:#777777; font-style:italic; margin-left:10px;">in reply for message `<b>'.Smart::escape_html($msg_arr['subject']).'</b>`'.'<br>received by &lt;<b>'.Smart::escape_html($msg_arr['to']).'</b>&gt;'.' on '.Smart::escape_html($msg_arr['date']).'<br>from &lt;<b>'.Smart::escape_html($msg_arr['from']).'</b>&gt;'.', wrote:'.'</div><hr><br><div style="margin-left:10px; padding-left:10px; border-left:3px solid #CCCCCC;">'.$msg_arr['message'].'</div></div><br><br>';
			$composer_extra = '';
		} elseif((string)$mode == 'forward') {
			$composer_title = 'Forward Message';
			$composer_replytoaddr = '';
			$composer_inreplyto = '';
			$composer_to = '';
			$composer_subject = (string) trim((string)$msg_arr['subject']);
			if(stripos((string)$composer_subject, 'Fwd:') !== 0) {
				$composer_subject = 'Fwd: '.$composer_subject;
			} //end if
			if(!$msg_url) {
				return (string) SmartComponents::operation_error($composer_title.' // ERROR: File Path is Empty ...', '100%');
			} //end if
			$msg_fpath = (array) SmartMailerMimeParser::decode_mime_fileurl(
				(string) $msg_url,
				(string) $this->secretKey()
			);
			if(Smart::array_size($msg_fpath) <= 0) {
				return (string) SmartComponents::operation_error($composer_title.' // ERROR: File Path is Invalid (1) ...', '100%');
			} //end if
			$msg_fpath = (string) $msg_fpath['message-file'];
			if(!$msg_fpath) {
				return (string) SmartComponents::operation_error($composer_title.' // ERROR: File Path is Invalid (2) ...', '100%');
			} //end if
			if((string)substr((string)$msg_fpath, -4, 4) != '.eml') { // {{{SYNC-WEBMAIL-FWD-ALLOWED-FILE-EXTENSION}}}
				return (string) SmartComponents::operation_error($composer_title.' // ERROR: File Path is Invalid (3) ...', '100%');
			} //end if
			$fake_att_name = 'forwarded-message.eml';
			$arr_att_fwd = (array) \SmartModExtLib\Cloud\webmailUtils::createAttachmentComposerData(
				(string) $fake_att_name,
				(string) $msg_fpath
			);
			if(Smart::array_size($arr_att_fwd) <= 0) { // checks if file path is safe, exists, is a file, is readable
				return (string) SmartComponents::operation_error($composer_title.' // ERROR: File Path is Invalid (4) ...', '100%');
			} //end if
			$composer_arr_atts = [
				(array) $arr_att_fwd
			];
			$composer_msg = '<div style="background:#FFFFFF; color:#111111; padding:5px;"><br><br><br><hr><div style="color:#777777; font-style:italic; margin-left:10px;">forwarded message `<b>'.Smart::escape_html($msg_arr['subject']).'</b>`'.'<br>received by &lt;<b>'.Smart::escape_html($msg_arr['to']).'</b>&gt;'.' on '.Smart::escape_html($msg_arr['date']).'<br>from &lt;<b>'.Smart::escape_html($msg_arr['from']).'</b>&gt;'.', attached below'.'</div><hr><br>';
			$composer_extra = (string) '<div id="fwd-msg-preview"><h3>Preview of `'.Smart::escape_html($fake_att_name).'`:</h3></div>'.$msg_arr['message'];
			$composer_height_htmledit = '35vh';
			$composer_height_msgedit = '30vh';
		} else {
			$msg_arr = [];
			$composer_title = 'New Message';
			$composer_replytoaddr = '';
			$composer_inreplyto = '';
			$composer_to = '';
			$composer_subject = '';
			$composer_arr_atts = [];
			$composer_msg = '';
			$composer_extra = '';
		} //end if else
		//--
		$composer_cc = '';
		$composer_bcc = '';
		//--
		$html_editor = (bool) !SmartAppInfo::TestIfModuleExists('mod-wflow-components');
		//--
		if($html_editor) {
			$composer_init = (string) SmartViewHtmlHelpers::html_jsload_htmlarea(
				'',
				'lib/js/jsedithtml/cleditor/jquery.cleditor.smartframeworkcomponents-simple.css'
			);
			$composer_draw = (string) SmartViewHtmlHelpers::html_js_htmlarea(
				'webmail-html-composer',
				'webmail[htmlbody]',
				(string) $composer_msg,
				'97vw',
				(string) $composer_height_htmledit
			);
		} else {
			$composer_init = (string) SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'webmail-composer-msgedit-init.mtpl.inc.htm',
				[
				]
			);
			$composer_draw = (string) SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'webmail-composer-msgedit-draw.mtpl.inc.htm',
				[
					'HTML-ID' => 'webmail-html-composer',
					'HTML-VAR' => 'webmail[htmlbody]',
					'THE-MSG' => (string) $composer_msg,
					'WIDTH' => '96vw',
					'HEIGHT' => (string) $composer_height_msgedit
				]
			);
		} //end if else
		//--
		return (string) SmartMarkersTemplating::render_file_template(
			$this->ControllerGetParam('module-view-path').'webmail-composer.mtpl.inc.htm',
			[
				'MODULE-PATH' 			=> (string) $this->ControllerGetParam('module-path'),
				'URL-PAGE' 				=> (string) $this->pagelink,
				'JS-PAGEAWAY' 			=> (string) SmartViewHtmlHelpers::js_code_init_away_page(),
				'JS-DPAGEAWAY' 			=> (string) SmartViewHtmlHelpers::js_code_disable_away_page(),
				'CURRENT-MBOX' 			=> (string) $mbox,
				'CURRENT-BOX' 			=> (string) $box,
				'CURRENT-MSG' 			=> (string) $msg_id,
				'BACK-URL' 				=> (string) (($msg_id && $msg_url) ? $this->pagelink.'&mbox='.Smart::escape_url($mbox).'&box='.Smart::escape_url($box).'&id='.Smart::escape_url($msg_id).'&msg='.Smart::escape_url($msg_url) : ''),
				'COMPOSER-MODE' 		=> (string) $mode,
				'COMPOSER-TITLE' 		=> (string) $composer_title,
				'COMPOSER-REPLYTOADDR' 	=> (string) $composer_replytoaddr,
				'COMPOSER-INREPLYTO' 	=> (string) $composer_inreplyto,
				'COMPOSER-TO' 			=> (string) $composer_to,
				'COMPOSER-CC' 			=> (string) $composer_cc,
				'COMPOSER-BCC' 			=> (string) $composer_bcc,
				'COMPOSER-SUBJECT' 		=> (string) $composer_subject,
				'COMPOSER-ATTS' 		=> (array)  $composer_arr_atts,
				'HTMLAREA-INIT' 		=> (string) $composer_init,
				'HTMLAREA-DISPLAY' 		=> (string) $composer_draw,
				'EXTRA-HTML' 			=> (string) $composer_extra
			]
		);
		//--
	} //END FUNCTION



} //END CLASS


// end of php code
