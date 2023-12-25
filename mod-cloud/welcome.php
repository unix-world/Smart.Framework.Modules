<?php
// Controller: Cloud/AddressBook
// Route: admin.php?/page/cloud.welcome/
// (c) 2006-2021 unix-world.org - all rights reserved

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
		$version = 'r.20231027';
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-webdav')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Cloud requires Mod WebDAV ...');
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
		\SmartModExtLib\Cloud\cloudUtils::ensureCloudHtAccess();
		//--

		//--
		$urlCardDav = 'admin.php#!carddav';
		$urlCalDav  = 'admin.php#!caldav';
		$urlWebDav  = 'admin.php#!webdav';
		//--
		if(SmartEnvironment::isAdminArea()) {
			$urlCardDav = 'admin.php/page/cloud.abook/~';
			$urlCalDav  = 'admin.php/page/cloud.ical/~';
			$urlWebDav  = 'admin.php/page/cloud.files/~';
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'VERSION' 		=> (string) $version,
			'LOGO-TXT' 		=> (string) 'Smart.Cloud :: '.$version.' @ Powered by Smart.Framework Web Service',
			'LOGO-IMG' 		=> (string) 'lib/core/img/app/globe.svg',
			'IMGS-PATH' 	=> (string) 'modules/mod-webdav/libs/img/',
			'DATE-YEAR' 	=> (string) date('Y'),
			'URL-CARDDAV' 	=> (string) $urlCardDav,
			'URL-CALDAV' 	=> (string) $urlCalDav,
			'URL-WEBDAV' 	=> (string) $urlWebDav,
		]);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
 */
class SmartAppAdminController extends SmartAppIndexController {

} //END CLASS


// end of php code
