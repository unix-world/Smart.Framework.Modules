<?php
// Controller: Cloud/Files
// Route: admin.php/page/cloud.files/~
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // admin only
define('SMART_APP_MODULE_AUTH', true); // requires auth always
define('SMART_APP_MODULE_DIRECT_OUTPUT', true); // do direct output

//--
if(!SmartAppInfo::TestIfModuleExists('mod-webdav')) {
	http_response_code(500);
	echo SmartComponents::http_message_500_internalerror('ERROR: Cloud Files requires Mod WebDAV ...');
	die('');
} //end if
//--

/**
 * Admin Controller (direct output)
 */
class SmartAppAdminController extends \SmartModExtLib\Webdav\ControllerAdmDavFs {


	public function Run() {

		//--
		if(SmartAuth::check_login() !== true) {
			http_response_code(403);
			echo SmartComponents::http_message_403_forbidden('ERROR: WebDAV Invalid Auth ...');
			return;
		} //end if
		//--
		if(
			(SmartAuth::test_login_privilege('cloud') !== true)
			AND
			(SmartAuth::test_login_privilege('cloud-files') !== true)
		) {
			http_response_code(403);
			echo SmartComponents::http_message_403_forbidden('This Area is Restricted by your Account Privileges !');
			return;
		} //end if
		//--

		//--
		if(SmartFrameworkRuntime::PathInfo_Enabled() !== true) {
			http_response_code(500);
			echo SmartComponents::http_message_500_internalerror('ERROR: WebDAV requires PathInfo to be enabled into init.php for Admin Area ...');
			return;
		} //end if
		//--
		if(strpos((string)SmartUtils::get_server_current_request_uri(), '/~') === false) {
			http_response_code(400);
			echo SmartComponents::http_message_400_badrequest('ERROR: WebDAV requires to be accessed in a special mode: `/~`');
			return;
		} //end if
		//--

		//--
		\SmartModExtLib\Cloud\cloudUtils::ensureCloudHtAccess();
		//--
		$safe_user_dir = (string) Smart::safe_username(SmartAuth::get_auth_username());
		if(((string)$safe_user_dir == '') OR (SmartFileSysUtils::checkIfSafeFileOrDirName((string)$safe_user_dir) != '1')) {
			http_response_code(500);
			echo SmartComponents::http_message_500_internalerror('ERROR: WebDAV Unsafe User Dir ...');
			return;
		} //end if
		//--
		$safe_user_path = (string) 'wpub/cloud/'.$safe_user_dir.'/webdav';
		if(SmartFileSysUtils::checkIfSafePath((string)$safe_user_path) != '1') {
			http_response_code(500);
			echo SmartComponents::http_message_500_internalerror('ERROR: WebDAV Unsafe User Path ...');
			return;
		} //end if
		//--
		if(SmartFileSystem::is_type_dir((string)$safe_user_path) !== true) {
			http_response_code(500);
			echo SmartComponents::http_message_500_internalerror('ERROR: WebDAV User Path does not exists ...');
			return;
		} //end if
		//--

		//--
		if((string)ltrim((string)$this->RequestPathGet(), '/') != '') {
			$txt_lnk = 'Cloud.Files :: Home';
			$url_lnk = (string) SmartUtils::get_server_current_url().SmartUtils::get_server_current_script().'/page/cloud.files/~';
		} else {
			$txt_lnk = 'Cloud :: Home';
			$url_lnk = (string) SmartUtils::get_server_current_url().SmartUtils::get_server_current_script().'?/page/cloud.welcome';
		} //end if else
		//--
		if(defined('NCLOUD_WEBDAV_PROPFIND_ETAG_MAX_FSIZE')) {
			define('SMART_WEBDAV_PROPFIND_ETAG_MAX_FSIZE', (int)NCLOUD_WEBDAV_PROPFIND_ETAG_MAX_FSIZE); // etags on PROPFIND :: set = -2 to disable etags ; set to -1 to show etags for all files ; if >= 0, if the file size is >= with this limit will only calculate the etag (etag on PROPFIND is not mandatory for WebDAV and may impact performance if there are a large number of files in a directory or big size files ...) ; etags will always show on HEAD method
		} //end if
		$this->DavFsRunServer(
			(string) $safe_user_path,
			(bool) (defined('NCLOUD_WEBDAV_SHOW_QUOTA') AND (NCLOUD_WEBDAV_SHOW_QUOTA === true)), // you may wish to disable this on large webdav file systems to avoid huge calculations
			'Files',
			'Smart.Cloud/WebDAV (c) 2012-'.date('Y').' unix-world.org',
			'#',
			(string) $url_lnk,
			(string) $txt_lnk
		);
		//--

	} //END FUNCTION

} //END CLASS

// end of php code
