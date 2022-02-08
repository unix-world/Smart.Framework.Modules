<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Class: \SmartModExtLib\Docs\AbstractTaskController
// (c) 2006-2021 unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

namespace SmartModExtLib\Docs;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

if(!\SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
	\SmartFrameworkRuntime::Raise500Error('Mod AuthAdmins is missing !');
	die('Mod AuthAdmins is missing !');
} //end if

// SMART_APP_MODULE_DIRECT_OUTPUT :: TRUE :: # by parent class

//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================

/**
 * Task Controller: Abstract Custom Task
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20210612
 *
 */
abstract class AbstractTaskController extends \SmartModExtLib\AuthAdmins\AbstractTaskController {

	protected const REQ_MAX_MEMORY_SIZE = '1024M';

	protected $title = 'Docs Task (Abstract)';

	protected $name_prefix = 'Docs';
	protected $name_suffix = 'Task';
	protected $app_tpl = ''; // path/to/some.mtpl.htm
	protected $app_main_url = '';

	private $appid = '';
	protected $details = false;


	protected function InitTask() {
		//--
		if(!$this->TestDirectOutput()) {
			return 'ERROR: Direct Output is not enabled ...';
		} //end if
		//--
		if(\defined('\\SMART_HTML_CLEANER_USE_VALIDATOR')) {
			return 'ERROR: a constant has been already defined and should not: `SMART_HTML_CLEANER_USE_VALIDATOR` ...';
		} //end if
		define('SMART_HTML_CLEANER_USE_VALIDATOR', 'tidy:required');
		//--
		ini_set('memory_limit', (string)self::REQ_MAX_MEMORY_SIZE);
		if((string)ini_get('memory_limit') !== (string)self::REQ_MAX_MEMORY_SIZE) {
			return 'Failed to set PHP.INI memory_limit as: '.(string)self::REQ_MAX_MEMORY_SIZE;
		} //end if
		//--
		$this->name_prefix = 'Docs';
		$this->name_suffix = 'Task';
		//--
		$this->app_tpl = '';
		$this->app_main_url = (string) $this->ControllerGetParam('url-script').'?page=auth-admins.tasks';
		//--
		return null;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
