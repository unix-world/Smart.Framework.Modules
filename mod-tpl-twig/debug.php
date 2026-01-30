<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Twig Templating Debug r.20260128
// Route: ?/page/tpl-twig.debug (?page=tpl-twig.debug)
// (c) 2006-present unix-world.org - all rights reserved

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
			$this->PageViewSetErrorStatus(404, 'NO Twig-TPL-DEBUG Service has been activated on this server ...');
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
		$tpl = (string) $this->RequestVarGet('tpl', '', 'string');
		//--

		//--
		$this->PageViewSetCfg('rawpage', true);
		//--
		$this->PageViewSetVar(
			'main',
			(string) SmartDebugProfiler::display_debug_page(
				'{{ Twig-TPL }} Template Debug Profiling',
				(string) \SmartModExtLib\TplTwig\SmartTwigTemplating::debug((string)$tpl)
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


/**
 * Task Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppTaskController extends SmartAppAdminController {

	// this will clone the SmartAppIndexController to run exactly the same action in task.php

} //END CLASS


// end of php code
