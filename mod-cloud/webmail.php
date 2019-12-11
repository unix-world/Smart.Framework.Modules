<?php
// Controller: Cloud/Webmail
// Route: admin.php?/page/cloud.webmail
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


/**
 * Admin Controller
 */
class SmartAppAdminController extends SmartAbstractAppController {

	private $username;
	private $userpath;
	private $pagelink;
	private $getlink;


	public function Run() {

		//--
		if(SmartAuth::check_login() !== true) {
			$this->PageViewSetErrorStatus(403, 'ERROR: WebMail Invalid Auth ...');
			return;
		} //end if
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
		//--
		$this->pagelink = (string) $this->ControllerGetParam('url-script').'?page='.$this->ControllerGetParam('url-page');
		$this->getlink = (string) $this->ControllerGetParam('url-script').'?page='.$this->ControllerGetParam('url-page').'get';
		//--


/*
$html_content = '<a id="url_recognition" href="'.$this->pagelink.'&msg='.Smart::escape_url(SmartMailerMimeParser::encode_mime_fileurl(
	(string) $this->userpath.'inbox/test_uxm_multi_mimes.eml',
	(string) $this->secretKey()
)).'" target="cloud_webmail_eml_display" data-smart="open.modal">test_uxm_multi_mimes.eml</a>';
*/

$html_content = '';
$html_vbar = '';
$mbox = 'iradu@unix-world.org';

		$arr_boxes = [ 'inbox', 'junk', 'sent', 'trash' ];
		$box = $this->RequestVarGet('box', 'inbox', 'string');
		if(!in_array((string)$box, (array)$arr_boxes)) {
			$this->PageViewSetErrorStatus(404, 'ERROR: Invalid WebMail Box: '.$box);
			return 404;
		} //end if

		//--
		$op = $this->RequestVarGet('op', '', 'string');
		//--
		switch((string)$op) {
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
				// nothing special
		} //end switch
		//--
// $this->pagelink.'&mbox='.Smart::escape_url($mbox)
$html_content = (string) SmartMarkersTemplating::render_file_template(
	$this->ControllerGetParam('module-view-path').'partials/webmail-part-list-mbox.mtpl.inc.htm',
	[
		'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
		'URL-PAGE' 			=> (string) $this->pagelink,
		'CURRENT-MBOX' 		=> (string) $mbox,
		'CURRENT-BOX' 		=> (string) $box
	]
);
$html_vbar = (string) SmartMarkersTemplating::render_file_template(
	$this->ControllerGetParam('module-view-path').'partials/webmail-part-list-mbox-vbar.mtpl.inc.htm',
	[
		'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
		'URL-PAGE' 			=> (string) $this->pagelink,
		'URL-GET' 			=> (string) $this->getlink,
		'CURRENT-MBOX' 		=> (string) $mbox,
		'CURRENT-BOX' 		=> (string) $box,
		'BOXES' 			=> (array)  $arr_boxes
	]
);

		//--
		$msg = $this->RequestVarGet('msg', '', 'string');
		$reply = $this->RequestVarGet('reply', '', 'string');
		//--
		if((string)$msg != '') {
			//--
			if($reply) {

				// TODO:
				$arr_repl = SmartMailerMimeParser::get_message_data_structure(
					(string) $msg,
					(string) $this->secretKey(),
					'data-reply', // 'data-full' | 'data-reply'
					$this->pagelink.'&op=view-message&msg={{{MESSAGE}}}&rawmode={{{RAWMODE}}}&mime={{{MIME}}}&disp={{{DISP}}}',
					'_self'
				);

				echo '<h1>Reply ...</h1><pre>';
				echo Smart::escape_html(SmartUtils::pretty_print_var($arr_repl));
				echo '</pre>';
				die();

			} else {
				//--
				$id = $this->RequestVarGet('id', '', 'string');
				//--
				$this->markMessageAsRead($mbox, $id, $msg);
				$this->displayMimeMessage($msg);
				//--
			} //end if else
			//--
		} else {
			//--
			$this->PageViewSetVars([
				'title' => 'WebMail',
				'main' => (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'webmail.mtpl.inc.htm',
					[
						'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
						'AREA-HTML-TOP' 	=> '<h1>WebMail</h1>',
						'AREA-HTML-VBAR' 	=> (string) $html_vbar,
						'AREA-HTML-HBAR' 	=> '',
						'AREA-HTML-CONTENT' => (string) $html_content
					]
				)
			]);
			//--
		} //end if else

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
		return (string) $this->ControllerGetParam('controller').':'.sha1(SMART_FRAMEWORK_SECURITY_KEY.'|'.$this->username.'|'.$this->userpath);
		//--
	} //END FUNCTION


	private function markMessageAsRead($mbox, $id, $msg) {
		//--
		$the_mbox_path = $this->mboxPath($mbox);
		//--
		$model = new \SmartModDataModel\Cloud\SqWebmail($the_mbox_path); // open connection / initialize
		//--
		$wr = $model->markOneMessageAsReadById($id);
		//--
		if($wr[1] == 1) { // update just on first read
				//--
				$arr_msg = SmartMailerMimeParser::get_message_data_structure(
					(string) $msg,
					(string) $this->secretKey(),
					'data-full'
				);
				//print_r($arr_msg); die();
				if((int)$arr_msg['atts_num'] > 0) {
					$model->updOneMessageAttsById($id, (int)$arr_msg['atts_num'], (string)$arr_msg['atts_lst']);
				} //end if
				//--
				$arr_msg = SmartMailerMimeParser::get_message_data_structure(
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
		unset($model); // close connection
		//--
	} //END FUNCTION


	private function displayMimeMessage($msg) {

		//--
		$mode = $this->RequestVarGet('mode', '', 'string');
		$pdf = $this->RequestVarGet('pdf', '', 'string');
		//--
		$use_sandbox = false;
		if(((string)$mode == '') AND ((string)$pdf == '')) {
			// it uses auto sandbox
			$mime_mode = '';
			$bttns_area = '<div style="text-align:right; padding-right:10px;"><a href="'.$this->pagelink.'&reply=yes&msg='.Smart::escape_url((string)$msg).'"><img src="lib/core/plugins/img/email/send-reply.svg" alt="Reply" title="Reply" style="cursor:pointer;"></a> &nbsp; <img src="lib/core/plugins/img/email/bttn-pdf.svg" alt="PDF" title="PDF" style="cursor:pointer;" onClick="SmartJS_BrowserUtils.PopUpLink(self.location + \'&print=yes&pdf=yes\', \'webmail-pdf\', null, null, 1);"></div>';
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
			$mime_ttl = 'MIME Message';
		} else {
			$use_sandbox = true;
			$mime_ttl = 'MIME Message Part';
		} //end if else
		//--
		$main = (string) SmartMailerMimeParser::display_message(
			(string) $msg,
			(string) $this->secretKey(),
			$this->pagelink.'&msg={{{MESSAGE}}}&rawmode={{{RAWMODE}}}&mime={{{MIME}}}&disp={{{DISP}}}&mode={{{MODE}}}',
			'_self',
			'<div align="center"><h1 style="display:inline;">'.Smart::escape_html($mime_ttl).'</h1></div>'.$bttns_area,
			(string) $mime_mode // 'default' | 'print'
		);
		//-- sandbox if required (print mode) :: use sandbox iframe if non-print mode ; for print mode use iframe sandbox to ensure safety !
		if($use_sandbox === true) {
			$main = '<div title="WebMail HTML Safe SandBox / iFrame" style="position:relative;"><img height="16" src="lib/core/plugins/img/email/safe.svg" style="cursor:help; position:absolute; top:3px; left:7px; opacity:0.25;"><iframe name="WebMailMessageSandBox" id="WebMailMessageSandBox" scrolling="auto" marginwidth="5" marginheight="5" hspace="0" vspace="0" frameborder="0" style="width:97vw; min-height:97vh; height:max-content; border:1px solid #EFEFEF;" srcdoc="'.Smart::escape_html('<!DOCTYPE html><html><head><title>'.Smart::escape_html($mime_ttl).'</title><meta charset="'.Smart::escape_html(SMART_FRAMEWORK_CHARSET).'">'.SmartFileSystem::read('lib/core/templates/base-html-styles.inc.htm').'</head><body>'.$main.'<script>alert(\'If you can see this alert the WebMail iFrame Sandbox is unsafe ...\');</script></body></html>').'" sandbox></iframe></div>';
		} //end if
		//-- forwarder for misc email parts
		$test_rawpage = $this->RequestVarGet('rawmode', '', 'string');
		if(((string)$test_rawpage == 'raw') AND ((string)$mode != 'partial')) {
			//--
			$this->PageViewSetCfg('rawpage', true);
			//--
			$test_rawmime = $this->RequestVarGet('mime', '', 'string');
			if((string)$test_rawmime != '') {
				$test_rawmime = (string) SmartUtils::url_hex_decode((string)$test_rawmime);
				$this->PageViewSetCfg('rawmime', (string)$test_rawmime);
			} //end if
			//--
			$test_rawdisp = $this->RequestVarGet('disp', '', 'string');
			if((string)$test_rawdisp != '') {
				$test_rawdisp = (string) SmartUtils::url_hex_decode((string)$test_rawdisp);
				$this->PageViewSetCfg('rawdisp', (string)$test_rawdisp);
			} //end if
			//--
		} else {
			//--
			if((string)$pdf != '') {
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', SmartPdfExport::pdf_mime_header());
				$this->PageViewSetCfg('rawdisp', SmartPdfExport::pdf_disposition_header('message-'.time().'.pdf', 'inline')); // TODO: since the msg file name is encrypted we need a way to get it
				$main = SmartPdfExport::generate((string)$main, 'normal', 'auto');
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


	private function listJsonMailbox($mbox, $box, $ofs, $sortby, $sortdir, $srcby, $src) {
		//--
		$the_mbox_path = $this->mboxPath($mbox);
		//--
		$model = new \SmartModDataModel\Cloud\SqWebmail($the_mbox_path); // open connection / initialize
		//--
		$limit = 25;
		//--
		$data = [
			'status'  			=> 'OK',
			'crrOffset' 		=> (int)    $ofs,
			'itemsPerPage' 		=> (int)    $limit,
			'sortBy' 			=> (string) $sortby,
			'sortDir' 			=> (string) $sortdir,
			'sortType' 			=> (string) '', // applies only with clientSort (not used for Server-Side sort)
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
			$data['rowsList'][$i]['@link'] = (string) $this->pagelink.'&mbox='.Smart::escape_url($mbox).'&id='.Smart::escape_url($val['id']).'&msg='.Smart::escape_url(SmartMailerMimeParser::encode_mime_fileurl(
				(string) rtrim($the_mbox_path,'/').'/'.rtrim($val['folder'],'/').'/'.ltrim($val['id'],'/'),
				(string) $this->secretKey()
			));
			//--
		} //end for
		//--
		return (string) Smart::json_encode((array)$data);
		//--
	} //END FUNCTION


} //END CLASS


//end of php code
?>