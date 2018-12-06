<?php
// Controller: Cloud/AddressBook
// Route: admin.php?/page/cloud.welcome/
// Author: unix-world.org
// v.181109

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // shared


/**
 * Index Controller
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-webdav')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Cloud requires Mod WebDAV ...');
			return;
		} //end if
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Cloud requires Mod Auth Admins ...');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetCfg('template-path', '@'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-welcome.htm'); // the default template
		//--

		//-- check DOM
		if(!class_exists('DOMDocument')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: PHP DOM Class is missing ...');
			return;
		} //end if
		//--

		//--
		$version = 'r.181109';
		//--
		$this->PageViewSetVars([
			'VERSION' 		=> (string) $version,
			'LOGO-TXT' 		=> (string) 'Smart.Cloud :: '.$version.' @ Powered by Smart.Framework / Server',
			'LOGO-IMG' 		=> (string) 'modules/mod-cloud/views/img/globe.svg',
			'IMGS-PATH' 	=> (string) 'modules/mod-webdav/libs/img/',
			'DATE-YEAR' 	=> (string) date('Y')
		]);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
 */
class SmartAppAdminController extends SmartAppIndexController {

} //END CLASS


//end of php code
?>