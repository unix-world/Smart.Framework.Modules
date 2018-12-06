<?php
// Controller: Cloud/Addressbook
// Route: admin.php/page/cloud.abook/~
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
define('SMART_APP_MODULE_DIRECT_OUTPUT', true); // do direct output

/**
 * Admin Controller (direct output)
 */
class SmartAppAdminController extends \SmartModExtLib\Webdav\ControllerAdmCardDavFs {


	public function Run() {

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-webdav')) {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: Cloud Addressbook requires Mod WebDAV ...');
			return;
		} //end if
		//--

		//--
		if(!defined('SMART_SOFTWARE_URL_ALLOW_PATHINFO') OR ((int)SMART_SOFTWARE_URL_ALLOW_PATHINFO < 1)) {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: CardDAV requires PathInfo to be enabled into init.php for Admin Area ...');
			return;
		} //end if
		//--

		//--
		if(SmartAuth::check_login() !== true) {
			http_response_code(503);
			echo \SmartComponents::http_message_500_internalerror('ERROR: CardDAV Invalid Auth ...');
			return;
		} //end if
		//--
		$safe_user_dir = (string) Smart::safe_username(SmartAuth::get_login_id());
		if(((string)$safe_user_dir == '') OR (SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$safe_user_dir) != '1')) {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: CardDAV Unsafe User Dir ...');
			return;
		} //end if
		//--
		$safe_user_path = (string) 'wpub/dav/'.$safe_user_dir.'/carddav';
		if(SmartFileSysUtils::check_if_safe_path((string)$safe_user_path) != '1') {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: CardDAV Unsafe User Path ...');
			return;
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$safe_user_path) !== true) {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: CardDAV User Path does not exists ...');
			return;
		} //end if
		//--

		//--
		if((string)ltrim((string)$this->RequestPathGet(), '/') != '') {
			$txt_lnk = 'Cloud.Addressbook :: Home';
			$url_lnk = (string) \SmartUtils::get_server_current_url().\SmartUtils::get_server_current_script().'/page/cloud.abook/~';
		} else {
			$txt_lnk = 'Cloud :: Home';
			$url_lnk = (string) \SmartUtils::get_server_current_url().\SmartUtils::get_server_current_script().'?/page/cloud.welcome';
		} //end if else
		//--
		$this->DavFsRunServer(
			(string) $safe_user_path,
			(bool) (defined('NCLOUD_CARDDAV_SHOW_QUOTA') AND (NCLOUD_CARDDAV_SHOW_QUOTA === true)), // you may wish to disable this on large webdav file systems to avoid huge calculations
			'Addressbook',
			'Smart.Cloud/CardDAV (c) 2012-'.date('Y').' unix-world.org',
			'#',
			(string) $url_lnk,
			(string) $txt_lnk
		);
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>