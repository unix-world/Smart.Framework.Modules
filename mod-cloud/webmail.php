<?php
// Controller: Cloud/Webmail
// Route: admin.php?/page/cloud.webmail
// Author: unix-world.org
// v.180206

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
		$safe_user_path = (string) 'wpub/dav/'.$safe_user_dir.'/mail';
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
$html_content = '<a id="url_recognition" href="'.$this->pagelink.'&msg='.Smart::escape_url(SmartMailerMimeParser::encode_mime_fileurl(
	(string) $this->userpath.'inbox/test_uxm_multi_mimes.eml',
	(string) $this->secretKey()
)).'" target="cloud_webmail_eml_display" data-smart="open.modal">test_uxm_multi_mimes.eml</a>';

	$msg = $this->RequestVarGet('msg', '', 'string');
	$reply = $this->RequestVarGet('reply', '', 'string');

	if((string)$msg != '') {

		if($reply) {
			$arr_repl = SmartMailerMimeParser::get_message_data_structure(
				(string) $msg,
				(string) $this->secretKey(),
				'data-reply', // 'data-full' | 'data-reply'
				$this->pagelink.'&msg={{{MESSAGE}}}&rawmode={{{RAWMODE}}}&mime={{{MIME}}}&disp={{{DISP}}}',
				'_self'
			);
			print_r($arr_repl);
			die();
		} else {
			$this->displayMimeMessage($msg);
		}

	} else {

		//--
		$this->PageViewSetVars([
			'title' => 'WebMail',
			'main' => (string) SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'webmail-main.mtpl.inc.htm',
				[
					'MODULE-PATH' 		=> (string) $this->ControllerGetParam('module-path'),
					'AREA-HTML-TOP' 	=> '<h1>WebMail</h1>',
					'AREA-HTML-VBAR' 	=> '',
					'AREA-HTML-HBAR' 	=> '',
					'AREA-HTML-CONTENT' => (string) $html_content
				]
			)
		]);
		//--

	}

	} //END FUNCTION


	private function secretKey() {
		//--
		return (string) $this->ControllerGetParam('controller').':'.sha1(SMART_FRAMEWORK_SECURITY_KEY.'|'.$this->username.'|'.$this->userpath);
		//--
	} //END FUNCTION


	private function displayMimeMessage($msg) {
		//--
		$print = $this->RequestVarGet('print', '', 'string');
		$pdf = $this->RequestVarGet('pdf', '', 'string');
		//--
		if(((string)$print == '') AND ((string)$pdf == '')) {
			$mime_mode = '';
			$bttns_area = '<div style="text-align:right; padding-right:10px;"><a href="'.$this->pagelink.'&reply=yes&msg='.Smart::escape_url((string)$msg).'"><img src="lib/core/plugins/img/email/send-reply.svg" alt="Reply" title="Reply" style="cursor:pointer;"></a> &nbsp; <img src="lib/core/plugins/img/email/bttn-pdf.svg" alt="PDF" title="PDF" style="cursor:pointer;" onClick="self.location = self.location + \'&print=yes&pdf=yes\';"></div>';
		} elseif((string)$pdf == '') {
			$mime_mode = 'print';
			$bttns_area = '<script>SmartJS_BrowserUtils.PrintPage();</script>';
		} else { // PDF
			$mime_mode = 'print';
			$bttns_area = '';
		} //end if else
		//--
		// TODO: test $this->IfRequestPrintable() and set a modal/popup template in this case
		$main = (string) SmartMailerMimeParser::display_message(
			(string) $msg,
			(string) $this->secretKey(),
			$this->pagelink.'&msg={{{MESSAGE}}}&rawmode={{{RAWMODE}}}&mime={{{MIME}}}&disp={{{DISP}}}',
			'_self',
			'<div align="center"><h1 style="display:inline;">'.'MIME Message'.'</h1></div>'.$bttns_area,
			(string) $mime_mode // 'default' | 'print'
		);
		//-- forwarder for misc email parts
		$test_rawpage = $this->RequestVarGet('rawmode', '', 'string');
		if((string)$test_rawpage == 'raw') {
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
				$main = SmartPdfExport::generate((string)$main, 'normal', SmartUtils::get_server_current_script(), SmartUtils::get_server_current_url(), SMART_FRAMEWORK_ADMIN_AREA);
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


} //END CLASS

//end of php code
?>