<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Dust Templating Debug r.20200121
// Route: ?/page/tpl-dust.debug (?page=tpl-dust.debug)
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow if debug is not enabled
		if(!$this->IfDebug()) {
			$this->PageViewSetErrorStatus(404, 'NO Dust-TPL-DEBUG Service has been activated on this server ...');
			return;
		} //end if
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-tpl')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: TPL module (mod-tpl) is missing ...');
			return;
		} //end if
		//--

		//--
		$tpl = $this->RequestVarGet('tpl', '', 'string');
		//--

		//--
		$this->PageViewSetCfg('rawpage', true);
		//--
		$this->PageViewSetVar(
			'main',
			(string) SmartDebugProfiler::display_debug_page(
				'{ Dust-TPL } Template Debug Profiling',
				(string) \SmartModExtLib\TplDust\SmartDustTemplating::debug($tpl)
			)
		);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php

} //END CLASS


// end of php code
