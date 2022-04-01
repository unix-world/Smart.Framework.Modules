<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Docs/ConvertJson (to Markdown)
// Route: ?page=docs.task-convert-json-docs
// (c) 2013-2021 unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// SMART_APP_MODULE_DIRECT_OUTPUT :: TRUE :: # by parent class

define('SMART_APP_MODULE_AREA', 'TASK');
define('SMART_APP_MODULE_AUTH', true);


/**
 * Task Controller: Custom Task
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20210614
 *
 */
final class SmartAppTaskController extends \SmartModExtLib\Docs\AbstractTaskController {

	private const CONVERSIONS_MAX_RUN_TIMEOUT = 2105; // {{{SYNC-DOCS-CONVERSIONS-TIMEOUT}}}

	protected $title = 'Task: JSON Docs :: Convert HTML to Markdown';

	protected $sficon = '';
	protected $msg = '';
	protected $err = '';

	protected $working = true;
	protected $workstop = true;
	protected $endscroll = true;

	public function Run() {

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->err = 'Mod AuthAdmins is missing !';
			return;
		} //end if
		if(!SmartAppInfo::TestIfModuleExists('vendor')) {
			$this->err = 'Vendor module is missing !';
			return;
		} //end if
		//--

		//--
		$this->sficon = 'stack';
		//--

		//--
		ini_set('max_execution_time', (int)self::CONVERSIONS_MAX_RUN_TIMEOUT);
		if((int)ini_get('max_execution_time') !== (int)self::CONVERSIONS_MAX_RUN_TIMEOUT) {
			$this->err = 'Failed to set PHP.INI max_execution_time as: '.(int)self::CONVERSIONS_MAX_RUN_TIMEOUT;
			return;
		} //end if
		//--

		//--
		$this->EchoTextMessage('Starting ...', true);
		$this->EchoTextMessage('JSON Docs Dir: `'.\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH.'`');
		$this->EchoTextMessage('JSON Optimized Docs File: `'.\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_OPT_FILE.'`');
		$this->EchoTextMessage('JSON Markdown Docs File: `'.\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_MD_FILE.'`');
		//--

		//--
		$realm = (string) trim((string)$this->RequestVarGet('realm', '', 'string'));
		if((string)$realm == '') {
			$this->err = 'Empty Realm ...';
			return;
		} //end if
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$realm)) {
			$this->err = 'Invalid Realm, Unsafe Name: `'.$realm.'`';
			return;
		} //end if
		if(!SmartFileSysUtils::check_if_safe_path((string)\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH.$realm)) {
			$this->err = 'Invalid Realm, Unsafe Path: `'.$realm.'`';
			return;
		} //end if
		if(!SmartFileSystem::is_type_dir((string)\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH.$realm)) {
			$this->err = 'Invalid Realm, N/A: `'.\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH.$realm.'`';
			return;
		} //end if
		//--
		$this->EchoTextMessage('REALM: `'.$realm.'`', true);
		//--

		//--
		$jsondb = (string) SmartFileSysUtils::add_dir_last_slash(\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH.$realm).Smart::safe_filename((string)\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_OPT_FILE);
		if(!SmartFileSysUtils::check_if_safe_path((string)$jsondb)) {
			$this->err = 'Invalid Realm JSON OPT DB, Unsafe Path: `'.$jsondb.'`';
			return;
		} //end if
		if(!SmartFileSystem::is_type_file((string)$jsondb)) {
			$this->err = 'Invalid Realm JSON OPT DB, N/A: `'.$jsondb.'`';
			return;
		} //end if
		//--

		//--
		$jsonconvdb = (string) SmartFileSysUtils::add_dir_last_slash(\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_PATH.$realm).Smart::safe_filename((string)\SmartModExtLib\Docs\OptimizationUtils::THE_DOCS_MD_FILE);
		if(!SmartFileSysUtils::check_if_safe_path((string)$jsonconvdb)) {
			$this->err = 'Invalid Realm JSON Markdown DB, Unsafe Path: `'.$jsonconvdb.'`';
			return;
		} //end if
		if(SmartFileSystem::is_type_dir((string)$jsonconvdb)) {
			$this->err = 'Invalid Realm JSON Markdown DB, N/A: `'.$jsonconvdb.'`';
			return;
		} //end if
		//--

		//--
		$this->EchoTextMessage('Decoding JSON ...', true);
		$arr = Smart::json_decode((string)SmartFileSystem::read((string)$jsondb)); // mixed
		if(Smart::array_size($arr) <= 0) {
			$this->err = 'Malformed Realm JSON OPT DB: `'.$jsondb.'`';
			return;
		} //end if
		$this->EchoTextMessage('Processing JSON ...', true);
		//--
		$converted_arr = [];
		$processed_num = 0;
		$total_docs = (int) Smart::array_size($arr);
		$arr_disabled = [];
		foreach($arr as $key => $val) {
			//--
			$markdown = (string) \SmartModExtLib\Docs\SmartHTML2Markdown::convert((string)$val);
			//--
			if((string)trim((string)$markdown) == '') {
				$this->err = 'KEY: `'.$key.'` :: Markdown is EMPTY !';
				return;
				break;
			} //end if
			//--
			$converted_arr[(string)$key] = (string) $markdown;
			//--
			$processed_num++;
			$this->EchoTextMessage('#'.(int)$processed_num.' of [#'.(int)$total_docs.'] :: Key `'.$key.'` was converted ...');
			if(($processed_num % 50) === 25) {
				$this->PageScrollDown();
			} //end if
			//--
			$markdown = null; // free mem
			//--
		} //end foreach
		//--

		//--
		if(SmartFileSystem::is_type_file((string)$jsonconvdb)) {
			SmartFileSystem::delete((string)$jsonconvdb);
		} //end if
		if(SmartFileSystem::path_exists((string)$jsonconvdb)) {
			$this->err = 'Failed to delete the Markdown JSON DB: `'.$jsonconvdb.'`';
			return;
		} //end if
		//--
		SmartFileSystem::write((string)$jsonconvdb, (string)Smart::json_encode(
			(array) $converted_arr,
			false,
			true,
			false
		));
		//--
		if(!SmartFileSystem::is_type_file((string)$jsonconvdb)) {
			$this->err = 'Failed to save the Markdown JSON DB: `'.$jsonconvdb.'`';
			return;
		} //end if
		if(SmartFileSystem::get_file_size((string)$jsonconvdb) <= 0) {
			$this->err = 'The saved Markdown JSON DB: `'.$jsonconvdb.'` have size zero !';
			return;
		} //end if
		//--

		//--
		$this->msg = 'DONE ...';
		//--

	} //END FUNCTION


} //END CLASS

// end of php code
