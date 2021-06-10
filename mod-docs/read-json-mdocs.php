<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Docs/ReadJsonMDocs
// Route: ?page=docs.read-json-mdocs
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'TASK');
define('SMART_APP_MODULE_AUTH', true);


/**
 * Task Controller: Import JSON Docs
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20210531
 *
 */
final class SmartAppTaskController extends SmartAbstractAppController {

	private const DOCS_PATH = 'wpub/devdocs/';
	private const DOCS_FILE = 'db-md.json';

	public function Initialize() {
		//--
		$this->PageViewSetCfg('template-path', 'default');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
		if(defined('SMART_HTML_CLEANER_USE_TIDY')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: a constant has been already defined and should not: `SMART_HTML_CLEANER_USE_TIDY` ...');
			return;
		} //end if
		define('SMART_HTML_CLEANER_USE_TIDY', 'tidy');
		//--
	} //END FUNCTION


	public function Run() {

		//--
		$semaphores = [];
		//--
		$semaphores[] = 'skip:js-ui';
	//	$semaphores[] = 'load:searchterm-highlight-js';
		$semaphores[] = 'load:code-highlight-js';
		$semaphores[] = 'theme:dark';
		$semaphores[] = 'skip:unveil-js';
		//--

		//--
		if((!class_exists('DOMDocument')) OR (!class_exists('tidy'))) { // req. for HTML Cleaner Safety
			$this->PageViewSetErrorStatus(500, 'ERROR: tidy and DOMDocument PHP extensions are required ...');
			return;
		} //end if
		//--

		//--
		$realm = (string) trim((string)$this->RequestVarGet('realm', '', 'string'));
		if((string)$realm == '') {
			$this->PageViewSetErrorStatus(400, 'Empty Realm ...');
			return;
		} //end if
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$realm)) {
			$this->PageViewSetErrorStatus(400, 'Invalid Realm, Unsafe Name: `'.$realm.'`');
			return;
		} //end if
		if(!SmartFileSysUtils::check_if_safe_path((string)self::DOCS_PATH.$realm)) {
			$this->PageViewSetErrorStatus(500, 'Invalid Realm, Unsafe Path: `'.$realm.'`');
			return;
		} //end if
		if(!SmartFileSystem::is_type_dir((string)self::DOCS_PATH.$realm)) {
			$this->PageViewSetErrorStatus(500, 'Invalid Realm, N/A: `'.self::DOCS_PATH.$realm.'`');
			return;
		} //end if
		$jsondb = (string) SmartFileSysUtils::add_dir_last_slash(self::DOCS_PATH.$realm).Smart::safe_filename((string)self::DOCS_FILE);
		if(!SmartFileSysUtils::check_if_safe_path((string)$jsondb)) {
			$this->PageViewSetErrorStatus(500, 'Invalid Realm JSON DB, Unsafe Path: `'.$jsondb.'`');
			return;
		} //end if
		if(!SmartFileSystem::is_type_file((string)$jsondb)) {
			$this->PageViewSetErrorStatus(500, 'Invalid Realm JSON DB, N/A: `'.$jsondb.'`');
			return;
		} //end if
		//--

		//--
		$key_str = (string) trim((string)$this->RequestVarGet('id', '', 'string'));
		$key_num = (int) $this->RequestVarGet('key', '', 'integer+');
		//--
		if((int)$key_num < 0) { // 1 is the index
			$this->PageViewSetErrorStatus(400, 'Provide a documentation Key Number ...');
			return;
		} //end if
		//--
		$arr = Smart::json_decode((string)SmartFileSystem::read((string)$jsondb)); // mixed
		if(Smart::array_size($arr) <= 0) {
			$this->PageViewSetErrorStatus(500, 'Malformed Realm JSON DB: `'.$jsondb.'`');
			return;
		} //end if
		//--
		$key_id = '';
		$source = '';
		$srclen = 0;
		$index = 0;
		$maxindex = (int) Smart::array_size($arr);
		foreach($arr as $key => $val) {
			if(
				(((string)$key_str != '') AND ((string)$key === (string)$key_str)) OR
				(((string)$key_str == '') AND ((int)$key_num === (int)$index))
			) {
				$key_id = (string) $key;
				$srclen = (int) strlen((string)trim((string)$val));
				$source = (string) $val; // prefer Tidy here, it is more safe for untrusted inputs ...
				break;
			} //end if
			$index++;
		} //end foreach
		$key = null;
		$val = null;
		$arr = null;
		//--
		if((int)$srclen <= 0) {
			$this->PageViewSetErrorStatus(400, 'Invalid documentation Key ID: '.$key_id);
			return;
		} //end if
		//--

		//--
		$html = (string) \SmartModExtLib\PageBuilder\Utils::renderMarkdown(
			(string) $source,
			2, // prefer tidy
			(string) '#!R='.Smart::escape_url((string)$realm).'L=', // $this->ControllerGetParam('url-script').'?page='.$this->ControllerGetParam('controller').'&realm='.Smart::escape_url((string)$realm).'&id=',
		);
		//--
		$errors = [];
		if(stripos((string)$html, '{!DEF!=') !== false) { // {{{SYNC-TBL-DEFS-MARKER}}}
			$errors[] = 'Invalid Table Defs';
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'semaphore' 		=> (string) Smart::array_to_list($semaphores),
			'title' 			=> 'Docs :: Read JSON Markdown Docs :: Display Doc',
			'main' 				=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'tasks/'.$this->ControllerGetParam('action').'.mtpl.htm',
				[
					'URL-SCRIPT' 				=> (string) $this->ControllerGetParam('url-script'),
					'CONTROLLER' 				=> (string) $this->ControllerGetParam('controller'),
					'KEY-REALM' 				=> (string) $realm,
					'KEY-ID' 					=> (string) $key_id,
					'MARKDOWN-SOURCE' 			=> (string) $source,
					'HTML-MARKDOWN' 			=> (string) $html,
					'ERRORS' 					=> (string) implode(' ; ', (array)$errors),
					'URL-DOC-PREV' 				=> (string) (($index > 0) ? $this->ControllerGetParam('url-script').'?page='.$this->ControllerGetParam('controller').'&realm='.Smart::escape_url((string)$realm).'&key='.(int)((int)$index - 1) : ''),
					'URL-DOC-NEXT' 				=> (string) (($index < ($maxindex - 1)) ? $this->ControllerGetParam('url-script').'?page='.$this->ControllerGetParam('controller').'&realm='.Smart::escape_url((string)$realm).'&key='.(int)((int)$index + 1) : ''),
				]
			)
		]);
		//--

	} //END FUNCTION


} //END CLASS


// end of php code
