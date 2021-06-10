<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: AppRelease/CodeCleanup
// Route: ?/page/app-release.code-cleanup (?page=app-release.code-cleanup)
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
 * @version 	v.20210605
 *
 */
final class SmartAppTaskController extends \SmartModExtLib\Docs\AbstractTaskController {

	private const OPTIMIZATIONS_MAX_RUN_TIMEOUT = 2105;

	protected $title = 'Task: JSON Docs :: Optimize and Validate Images and SVGs, Validate HTML';

	protected $sficon = '';
	protected $msg = '';
	protected $err = '';

	protected $working = true;
	protected $workstop = true;
	protected $endscroll = true;

	public function Run() {

		//--
		$this->sficon = 'stack';
		//--

		//--
		ini_set('max_execution_time', (int)self::OPTIMIZATIONS_MAX_RUN_TIMEOUT);
		if((int)ini_get('max_execution_time') !== (int)self::OPTIMIZATIONS_MAX_RUN_TIMEOUT) {
			$this->err = 'Failed to set PHP.INI max_execution_time as: '.(int)self::OPTIMIZATIONS_MAX_RUN_TIMEOUT;
			return;
		} //end if
		//--

		//--
		$this->EchoTextMessage('Starting ...', true);
		$this->EchoTextMessage('JSON Docs Dir: `'.self::DOCS_PATH.'`');
		$this->EchoTextMessage('JSON Docs File: `'.self::DOCS_FILE.'`');
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
		if(!SmartFileSysUtils::check_if_safe_path((string)self::DOCS_PATH.$realm)) {
			$this->err = 'Invalid Realm, Unsafe Path: `'.$realm.'`';
			return;
		} //end if
		if(!SmartFileSystem::is_type_dir((string)self::DOCS_PATH.$realm)) {
			$this->err = 'Invalid Realm, N/A: `'.self::DOCS_PATH.$realm.'`';
			return;
		} //end if
		$jsondb = (string) SmartFileSysUtils::add_dir_last_slash(self::DOCS_PATH.$realm).Smart::safe_filename((string)self::DOCS_FILE);
		if(!SmartFileSysUtils::check_if_safe_path((string)$jsondb)) {
			$this->err = 'Invalid Realm JSON DB, Unsafe Path: `'.$jsondb.'`';
			return;
		} //end if
		if(!SmartFileSystem::is_type_file((string)$jsondb)) {
			$this->err = 'Invalid Realm JSON DB, N/A: `'.$jsondb.'`';
			return;
		} //end if
		//--

		//--
		$jsondb = (string) SmartFileSysUtils::add_dir_last_slash(self::DOCS_PATH.$realm).Smart::safe_filename((string)self::DOCS_FILE);
		if(!SmartFileSysUtils::check_if_safe_path((string)$jsondb)) {
			$this->err = 'Invalid Realm JSON DB, Unsafe Path: `'.$jsondb.'`';
			return;
		} //end if
		if(!SmartFileSystem::is_type_file((string)$jsondb)) {
			$this->err = 'Invalid Realm JSON DB, N/A: `'.$jsondb.'`';
			return;
		} //end if
		//--
		$this->EchoTextMessage('Decoding JSON ...', true);
		$arr = Smart::json_decode((string)SmartFileSystem::read((string)$jsondb)); // mixed
		if(Smart::array_size($arr) <= 0) {
			$this->err = 'Malformed Realm JSON DB: `'.$jsondb.'`';
			return;
		} //end if
		$this->EchoTextMessage('Processing JSON ...', true);
		//--
		$optimized_arr = [];
		$processed_num = 0;
		$total_docs = (int) Smart::array_size($arr);
		$arr_disabled = [];
		foreach($arr as $key => $val) {
			//--
			$source = (string) $val;
			//--
			$source = (string) (new SmartHtmlParser((string)$source, false, 2, false))->get_clean_html(); // do not use signature here
			//--
			$arr_process = (array) \SmartModExtLib\Docs\OptimizationUtils::validateSvgAndImagesCompressToWebp((string)$source);
			$source 			= (string) $arr_process['source'];
			$all_imgs_and_svgs 	= (int)    $arr_process['all-imgs-and-svgs'];
			$svgs 				= (int)    $arr_process['svgs'];
			$imgs 				= (int)    $arr_process['imgs'];
			$invalid_data_urls 	= (int)    $arr_process['invalid-data-urls'];
			$urls_disabled 		= (array)  $arr_process['urls-disabled'];
			//--
			if((int)Smart::array_size($urls_disabled) > 0) {
				$arr_disabled[(string)$key] = (array) $urls_disabled;
			} //end if
			//--
			if((int)((int)$all_imgs_and_svgs - (int)Smart::array_size($urls_disabled)) != (int)((int)$svgs + (int)$imgs)) {
				$this->err = 'KEY: `'.$key.'` :: Some Data Images or Data SVGs could not be processed: ALL#'.(int)$all_imgs_and_svgs.' ; IMG#'.(int)$imgs.' ; SVG#'.(int)$svgs;
				return;
				break;
			} elseif((int)$invalid_data_urls > 0) {
				$this->err = 'KEY: `'.$key.'` :: Invalid Data URLs detected: #[###INVALID-DATA-URLS|int###]'.(int)$invalid_data_urls;
				return;
				break;
			} //end if
			//--
			$source = (string) (new SmartHtmlParser((string)$source, true, 2, false))->get_clean_html();
			//--
			$optimized_arr[(string)$key] = (string) $source;
			//--
			$processed_num++;
			$this->EchoTextMessage('#'.(int)$processed_num.' of [#'.(int)$total_docs.'] :: Key `'.$key.'` was optimized ...');
			if(($processed_num % 50) === 25) {
				$this->PageScrollDown();
			} //end if
			//--
		} //end foreach
		//--

		//--
		if(SmartFileSystem::is_type_file((string)$jsondb.'.optimized.json')) {
			SmartFileSystem::delete((string)$jsondb.'.optimized.json');
		} //end if
		if(SmartFileSystem::path_exists((string)$jsondb.'.optimized.json')) {
			$this->err = 'Failed to delete the Optimized JSON DB: `'.$jsondb.'.optimized.json'.'`';
			return;
		} //end if
		//--
		SmartFileSystem::write((string)$jsondb.'.optimized.json', (string)Smart::json_encode(
			(array) $optimized_arr,
			false,
			true,
			false
		));
		//--
		if(!SmartFileSystem::is_type_file((string)$jsondb.'.optimized.json')) {
			$this->err = 'Failed to save the Optimized JSON DB: `'.$jsondb.'.optimized.json'.'`';
			return;
		} //end if
		if(SmartFileSystem::get_file_size((string)$jsondb.'.optimized.json') <= 0) {
			$this->err = 'The saved Optimized JSON DB: `'.$jsondb.'.optimized.json'.'` have size zero !';
			return;
		} //end if
		//--
		if(Smart::array_size($arr_disabled) > 0) {
			$this->EchoTextMessage('DISABLED URLs List for Keys', true);
			$this->EchoHtmlMessage('<pre>');
			$this->EchoTextMessage(SmartUtils::pretty_print_var($arr_disabled));
			$this->EchoHtmlMessage('</pre>');
		} //end if
		//--
		$this->msg = 'DONE ... URLs Disabled: #'.(int)Smart::array_size($urls_disabled);
		//--

	} //END FUNCTION


} //END CLASS

// end of php code
