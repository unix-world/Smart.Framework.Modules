<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Docs/ImportHtmlDocs
// Route: ?page=docs.import-html-docs
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
 * Task Controller: Import HTML Docs
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20210614
 *
 */
final class SmartAppTaskController extends SmartAbstractAppController {

	private const MAX_MEMORY_SIZE = '512M';

	public function Initialize() {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->PageViewSetErrorStatus(500, ' # Mod AuthAdmins is missing !');
			return false;
		} //end if
		if(!SmartAppInfo::TestIfModuleExists('vendor')) {
			$this->PageViewSetErrorStatus(500, ' # Vendor module is missing !');
			return false;
		} //end if
		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-auth-admins/templates/');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
		if(defined('SMART_HTML_CLEANER_USE_VALIDATOR')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: a constant has been already defined and should not: `SMART_HTML_CLEANER_USE_VALIDATOR` ...');
			return;
		} //end if
		define('SMART_HTML_CLEANER_USE_VALIDATOR', 'tidy:required');
		//--
		ini_set('memory_limit', (string)self::MAX_MEMORY_SIZE);
		if((string)ini_get('memory_limit') !== (string)self::MAX_MEMORY_SIZE) {
			$this->err = 'Failed to set PHP.INI memory_limit as: '.(string)self::MAX_MEMORY_SIZE;
			return;
		} //end if
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
		$realm = (string) trim((string)$realm, '/');
		if((string)$realm == '') {
			$this->PageViewSetErrorStatus(400, 'Empty Realm ...');
			return;
		} //end if
		if(!SmartFileSysUtils::check_if_safe_path((string)$realm)) {
			$this->PageViewSetErrorStatus(400, 'Invalid Realm, Unsafe Name: `'.$realm.'`');
			return;
		} //end if
		if(!SmartFileSysUtils::check_if_safe_path((string)\SmartModExtLib\Docs\OptimizationUtils::THE_HDOCS_PATH.$realm)) {
			$this->PageViewSetErrorStatus(500, 'Invalid Realm, Unsafe Path: `'.$realm.'`');
			return;
		} //end if
		if(!SmartFileSystem::is_type_dir((string)\SmartModExtLib\Docs\OptimizationUtils::THE_HDOCS_PATH.$realm)) {
			$this->PageViewSetErrorStatus(500, 'Invalid Realm, N/A: `'.\SmartModExtLib\Docs\OptimizationUtils::THE_HDOCS_PATH.$realm.'`');
			return;
		} //end if
		//--

		//--
		$action = (string) $this->RequestVarGet('action', '', 'string');
		if((string)trim((string)$action) != '') {
			switch((string)$action) {
				case 'render-html':
					//--
					$id = (string) trim((string)$this->RequestVarGet('id', '', 'string'));
					if((string)trim((string)$id) == '') {
						$this->PageViewSetErrorStatus(400, 'Empty ID name for render action `'.$action.'` ...');
						return;
					} //end if
					//--
					$code = (string) $this->RequestVarGet('htmlcode', '', 'string');
					if((string)trim((string)$code) == '') {
						$this->PageViewSetErrorStatus(400, 'Empty code for render action `'.$action.'` ...');
						return;
					} //end if
					//--
					$this->PageViewSetVars([
						'semaphore' 		=> (string) Smart::array_to_list($semaphores),
						'title' 			=> 'Docs :: Render JSON Docs',
						'main' 				=> SmartMarkersTemplating::render_file_template(
							$this->ControllerGetParam('module-view-path').'tasks/import-docs-render.mtpl.htm',
							[
								'RENDER-MODE' 	=> (string) 'Live HTML (Optimized)',
								'URL-SCRIPT' 	=> (string) $this->ControllerGetParam('url-script'),
								'CONTROLLER' 	=> (string) $this->ControllerGetParam('controller'),
								'KEY-REALM' 	=> (string) $realm,
								'KEY-ID' 		=> (string) $id,
								'HTML-CODE' 	=> (string) $code,
								'ERRORS' 		=> (string) '',
							]
						)
					]);
					//--
					return;
					//--
					break;
				case 'render-markdown':
					//--
					$id = (string) trim((string)$this->RequestVarGet('id', '', 'string'));
					if((string)trim((string)$id) == '') {
						$this->PageViewSetErrorStatus(400, 'Empty ID name for render action `'.$action.'` ...');
						return;
					} //end if
					//--
					$code = (string) $this->RequestVarGet('code', '', 'string');
					if((string)trim((string)$code) == '') {
						$this->PageViewSetErrorStatus(400, 'Empty code for render action `'.$action.'` ...');
						return;
					} //end if
					//--
					$markdown = (string) \SmartModExtLib\Docs\OptimizationUtils::renderDocMarkdown((string)$code, '<validate:html:tidy:required>', '#!R='.Smart::escape_url((string)$realm).';L='); // prefer tidy
					$code = null; // free mem
					//--
					$errors = [];
					if(stripos((string)$markdown, '{!DEF!=') !== false) { // {{{SYNC-TBL-DEFS-MARKER}}}
						$errors[] = 'Invalid Table Defs';
					} //end if
					//--
					$this->PageViewSetVars([
						'semaphore' 		=> (string) Smart::array_to_list($semaphores),
						'title' 			=> 'Docs :: Render HTML Docs',
						'main' 				=> SmartMarkersTemplating::render_file_template(
							$this->ControllerGetParam('module-view-path').'tasks/import-docs-render.mtpl.htm',
							[
								'RENDER-MODE' 	=> (string) 'Live HTML (Optimized)',
								'URL-SCRIPT' 	=> (string) $this->ControllerGetParam('url-script'),
								'CONTROLLER' 	=> (string) $this->ControllerGetParam('controller'),
								'KEY-REALM' 	=> (string) $realm,
								'KEY-ID' 		=> (string) $id,
								'HTML-CODE' 	=> (string) $markdown,
								'ERRORS' 		=> (string) implode(' ; ', (array)$errors),
							]
						)
					]);
					//--
					return;
					//--
					break;
				default:
					$this->PageViewSetErrorStatus(400, 'Invalid render action `'.$action.'` ...');
					return;
			} //end switch
		} //end if
		//--

		//--
		$key_str = (string) trim((string)$this->RequestVarGet('id', '', 'string'));
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$key_str)) {
			$this->PageViewSetErrorStatus(400, 'Invalid Key, Unsafe Name: `'.$key_str.'`');
			return;
		} //end if
		if(((string)substr($key_str, -5, 5) != '.html') AND ((string)substr($key_str, -4, 4) != '.htm')) {
			$this->PageViewSetErrorStatus(400, 'Invalid Key, Not a HTML File: `'.$key_str.'`');
			return;
		} //end if
		//--
		$htmlfile = (string) SmartFileSysUtils::add_dir_last_slash((string)\SmartModExtLib\Docs\OptimizationUtils::THE_HDOCS_PATH.$realm).$key_str;
		if(!SmartFileSysUtils::check_if_safe_path((string)$htmlfile)) {
			$this->PageViewSetErrorStatus(500, 'Unsafe Path for Key: `'.$key_str.'`');
			return;
		} //end if
		if(!SmartFileSystem::is_type_file((string)$htmlfile)) {
			$this->PageViewSetErrorStatus(400, 'Invalid Key, Not a File: `'.$key_str.'`');
			return;
		} //end if
		//--
		$source = (string) SmartFileSystem::read((string)$htmlfile);
		if((string)trim((string)$source) == '') {
			$this->PageViewSetErrorStatus(400, 'Invalid documentation Key ID: '.$key_str);
			return;
		} //end if
		//--
		$key_id = (string) $key_str;
		//--

		//--
		$source = (string) '<h1>'.Smart::escape_html((string)ucwords((string)str_replace('/', ' / ', (string)$realm)).' - '.$key_id).'</h1>'."\n".$source;
		//--
		$img_prefix_url = (string) SmartUtils::get_server_current_url().SmartFileSysUtils::add_dir_last_slash((string)\SmartModExtLib\Docs\OptimizationUtils::THE_HDOCS_PATH.$realm);
		//--
		$arr_process 		= (array) \SmartModExtLib\Docs\OptimizationUtils::processHtml((string)$source, (string)$realm, (string)$img_prefix_url);
		$source 			= (string) $arr_process['source'];
		//--
		$all_imgs_and_svgs 	= (int)    $arr_process['all-imgs-and-svgs'];
		$svgs 				= (int)    $arr_process['svgs'];
		$imgs 				= (int)    $arr_process['imgs'];
		$invalid_data_urls 	= (int)    $arr_process['invalid-data-urls'];
		$urls_disabled 		= (array)  $arr_process['urls-disabled'];
		//--
		$arr_process 		= null; // free mem
		//--

		//--
		$this->PageViewSetVars([
			'semaphore' 		=> (string) Smart::array_to_list($semaphores),
			'title' 			=> 'Docs :: Import HTML Docs :: Display Doc',
			'main' 				=> (string) SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'tasks/import-docs.mtpl.htm',
				[
					'URL-SCRIPT' 				=> (string) $this->ControllerGetParam('url-script'),
					'CONTROLLER' 				=> (string) $this->ControllerGetParam('controller'),
					'KEY-REALM' 				=> (string) $realm,
					'KEY-ID' 					=> (string) $key_id,
					'DOC-SOURCE' 				=> (string) $source,
					'ERRORS-IMGS-OR-SVGS' 		=> (string) ((int)($all_imgs_and_svgs - (int)Smart::array_size($urls_disabled)) != (int)((int)$svgs + (int)$imgs) ? 'yes' : 'no'),
					'ALL-IMGS-AND-SVGS' 		=> (int)    $all_imgs_and_svgs,
					'SVGS' 						=> (int)    $svgs,
					'IMGS' 						=> (int)    $imgs,
					'INVALID-DATA-URLS' 		=> (int)    $invalid_data_urls,
					'URLS-DISABLED' 			=> (string) implode(' ; ', (array)$urls_disabled),
					'HTML-EDITCODE-INIT' 		=> (string) SmartViewHtmlHelpers::html_jsload_editarea(false, [ 'oceanic-next', 'zenburn', 'neo' ]),
					'HTML-EDITCODE-SOURCE' 		=> (string) SmartViewHtmlHelpers::html_js_editarea(
						'edit-area-source',
						'htmlcode',
						(string) $source, // value
						'html', // mode
						false, // editable
						'calc(50vw - 25px)',
						'calc(100vh - 125px)',
						true, // show line numbers
						'oceanic-next' // theme
					),
					'HTML-EDITCODE-PROCESSED' 	=> (string) SmartViewHtmlHelpers::html_js_editarea(
						'edit-area-processed',
						'code',
						(string) \SmartModExtLib\Docs\OptimizationUtils::convertHtml2Markdown((string)$source), //(string)
						'markdown',
						true, // editable
						'calc(50vw - 15px)',
						'calc(100vh - 125px)',
						true, // show line numbers
						'zenburn' // theme
					),
					'URL-DOC-PREV' 				=> '',
					'URL-DOC-NEXT' 				=> '',
				]
			)
		]);
		//--

	} //END FUNCTION


} //END CLASS


// end of php code
