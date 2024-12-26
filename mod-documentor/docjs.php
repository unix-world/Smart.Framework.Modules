<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Documentor/DocJs (display, save)
// Route: task.php?page=documentor.docjs{&cls=SomeClass{&mode=multi}}
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//-----------------------------------------------------
define('SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO', 'lib/framework/img/sf-logo.svg');
define('SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS', 'tmp/documentor-js/');
define('SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS', 'tmp/documentor-js@packages/');
//-----------------------------------------------------


define('SMART_APP_MODULE_AREA', 'TASK'); // INDEX, ADMIN, TASK, SHARED
define('SMART_APP_MODULE_AUTH', true); // if set to TRUE requires auth always


/**
 * Task Area Controller
 * @version 20241216
 * @ignore
 */
final class SmartAppTaskController extends SmartAbstractAppController {

	private $sfjfile 	= '';
	private $classes 	= [];

	private $errMsg 	= null;
	private $clsType 	= '';
	private $clsPackage = '';


	public function Initialize() {

		//--
		if(defined('SMART_HTML_CLEANER_USE_VALIDATOR')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: a constant has been already defined and should not: `SMART_HTML_CLEANER_USE_VALIDATOR` ...');
			return;
		} //end if
		//--

		//--
		$this->sfjfile = (string) \SmartModExtLib\Documentor\SmartClasses::getJavascriptSfFile();
		$this->classes = (array) array_merge(
			(array) \SmartModExtLib\Documentor\SmartClasses::listJavascriptSfClasses(),
			(array) \SmartModExtLib\Documentor\SmartClasses::listJavascriptSfmClasses()
		);
		//--
		$this->clsType = 'class'; // for JS there are no interface or trait
		//--

		//--
		$this->PageViewSetCfg('template-path', '@'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-documentor.htm'); // the default template
		//--

		//--
		$this->PageViewSetVars([
			//--
			'fonts-path' 		=> (string) $this->ControllerGetParam('module-path').'fonts/',
			'logo-img' 			=> (string) SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO,
			'lang-img' 			=> (string) 'lib/framework/img/javascript-logo.svg',
			'year' 				=> (string) date('Y'),
			//--
			'title' 			=> (string) 'Documentation',
			'heading-title' 	=> (string) 'JavaScript Documentation',
			'seo-description'	=> (string) 'Smart.Framework Documentation',
			'seo-keywords'		=> (string) 'javascript, smart, framework, documentor',
			'seo-summary' 		=> (string) 'Smart.Framework, a PHP / Javascript Framework for Web',
			'url-index' 		=> ''
			//--
		]);
		//--

	} //END FUNCTION


	public function Run() {

		//--
		if((!class_exists('DOMDocument')) AND (!class_exists('tidy'))) { // req. for HTML Cleaner Safety
			$this->PageViewSetErrorStatus(500, 'ERROR: At least one of: tidy or DOMDocument PHP extensions is required ...');
			return;
		} //end if
		//--

		//--
		if($this->IfDebug()) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Documentor cannot be used when Debug is ON ...'); // results are unpredictable ...
			return;
		} //end if
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-highlight-syntax')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Highlight Syntax module (mod-highlight-syntax) is missing ...');
			return;
		} //end if
		//--

		//--
		$action = $this->RequestVarGet('action', '', 'string');
		$mode = $this->RequestVarGet('mode', '', 'string');
		$extra = $this->RequestVarGet('extra', '', 'string');
		$heading = $this->RequestVarGet('heading', 'JavaScript Documentation', 'string');
		//--
		if(!defined('SMART_FRAMEWORK_DOCUMENTOR_GENERATE_ALLOW') OR (SMART_FRAMEWORK_DOCUMENTOR_GENERATE_ALLOW !== true)) {
			if(((string)$action != '') OR ((string)$mode != '') OR ((string)$extra != '')) {
				$this->PageViewSetErrorStatus(503, 'ERROR: Documentor Generate Mode is disabled. Must define SMART_FRAMEWORK_DOCUMENTOR_GENERATE_ALLOW = TRUE to enable it.');
				return;
			} //end if
		} //end if
		//--

		//-- {{{SYNC-DOCUMENTOR-SAVE-MODE}}}
		$js_path = '';
		if((string)$mode == 'multi') {
			if((string)$extra != '') {
				if((string)$extra == '@') {
					$js_path = 'js/';
				} else {
					$js_path = '../js/';
				} //end if else
			} //end if
		} //end if else
		//--

		//--
		if((string)$action == 'cleanup@documentation') {
			//--
			$proc = \SmartModExtLib\Documentor\Utils::cleanupDocumentationDirectory(); // mixed
			if($proc !== true) {
				$this->jsonAnswer($proc.' ...', false);
				return;
			} //end if
			$this->jsonAnswer('Documentation directory and Documentation Packages directory Cleared: Javascript');
			return;
			//--
		} elseif((string)$action == 'index@packages') {
			//--
			$proc = \SmartModExtLib\Documentor\Utils::indexDocumentationPackages('js', $heading, $mode, $extra, $js_path); // mixed
			if($proc !== true) {
				$this->jsonAnswer($proc.' ...', false);
				return;
			} //end if
			$this->jsonAnswer('Documentation Packages Indexed: Javascript');
			return;
			//--
		} //end if
		//--

		//--
		$cls = (string) trim((string)$this->RequestVarGet('cls', '', 'string'));
		//--
		if((string)$cls == '') {
			switch((string)$action) {
				case 'save':
					$this->jsonAnswer('A JavaScript Class must be selected ...', false);
					break;
				default:
					$this->displaySelector('', $cls);
			} //end switch
			return;
		} //end if
		//--
		$file = (string) (isset($this->classes[(string)$cls]) ? $this->classes[(string)$cls] : '');
		if((string)$file == '') {
			$this->sfjfile = '';
			$file = (string) trim((string)$this->RequestVarGet('file', '', 'string'));
		} //end if
		//--

		//--
		switch((string)$action) {
			case 'save':
				//--
				$main = (string) $this->displayDocs(
					(string) $file,
					(string) $cls,
					(string) $js_path
				);
				//--
				if($this->errMsg === null) { // OK
					//--
					$proc = \SmartModExtLib\Documentor\Utils::indexDocumentationDocument('js', $this->clsPackage, $this->clsType, $cls, $main, $heading, $mode, $extra); // mixed
					if($proc !== true) {
						$this->jsonAnswer($proc.' ...', false);
						return;
					} //end if
					$this->jsonAnswer('Documentation saved for: `'.$cls.'`');
					//--
				} else { // ERR
					//--
					$this->jsonAnswer((string)$this->errMsg, false);
					//--
				} //end if
				//--
				return;
				//--
				break;
			default:
				//--
				$url_index = (string) $this->ControllerGetParam('url-script').'?page='.Smart::escape_url($this->ControllerGetParam('controller'));
				$url_cls = '&cls=';
				//--
				$main = (string) $this->displayDocs(
					(string) $file,
					(string) $cls,
					(string) 'lib/js/jsselect/',
					(string) $url_index.$url_cls
				);
				//--
				if($this->errMsg === null) { // OK
					$this->PageViewSetVars([
						'title' => (string) 'Documentation for: '.$cls,
						'main' 	=> SmartMarkersTemplating::render_file_template(
							(string) $this->ControllerGetParam('module-view-path').'display.mtpl.htm',
							[
								'DISPLAY' 	=> 'documentation',
								'MESSAGE' 	=> '',
								'DOCS-HTML' => (string) $main
							]
						),
						'url-index' => (string) $url_index
					]);
				} else {
					$this->displaySelector($file, $cls, (string)$this->errMsg);
				} //end if else
				//--
		} //end switch


	} //END FUNCTION


	//##### PRIVATES


	private function displaySelector($file, $cls, $message='') {
		//--
		$title = 'Select a JavaScript Class to display Documentation';
		//--
		$this->PageViewSetVars([
			'title' 	=> (string) 'Smart.Framework Documentation: '.$title,
			'main' 		=> (string) SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'display.mtpl.htm',
				[
					'DISPLAY' 		=> 'selection',
					'MESSAGE' 		=> (string) $message,
					'DOCS-HTML' 	=> '',
					//-- form
					'form-title' 	=> (string) $title,
					'form-cls' 		=> (string) $cls,
					'hint-cls' 		=> (string) 'Javascript Class',
					'form-ref' 		=> (string) '',
					'hint-ref' 		=> (string) 'N/A',
					'show-ref' 		=> (string) 'no',
					'form-file' 	=> (string) $file,
					'hint-file' 	=> (string) 'path/to/file.js',
					'show-file' 	=> (string) 'yes',
					'form-action' 	=> (string) $this->ControllerGetParam('url-script'),
					'form-page' 	=> (string) $this->ControllerGetParam('controller')
				]
			),
			'url-index' => ''
		]);
		//--
	} //END FUNCTION


	private function jsonAnswer($msg, $is_ok=true) {
		//--
		$this->PageViewSetCfg('rawpage', true);
		//--
		if($is_ok !== true) {
			$status = 'ERROR';
		} else {
			$status = 'OK';
		} //end if else
		//--
		$this->PageViewSetVar(
			'main',
			(string) Smart::json_encode([
				'status' 	=> (string) $status,
				'message' 	=> (string) $msg
			])
		);
		//--
	} //END FUNCTION


	private function displayDocs($file, $cls, $jsPath, $base_url='') {
		//--
		if(strpos((string)$file, '?#') !== false) {
			$file = (array) explode('?#', (string)$file);
			$file = (string) trim((string)(isset($file[0]) ? $file[0] : ''));
		} //end if
		//--
		if((string)\trim((string)$cls) == '') {
			$this->errMsg = 'No class selected';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		//--
		if(((string)$file == '') OR ((string)substr((string)$file, -3, 3) != '.js') OR (!SmartFileSysUtils::checkIfSafePath((string)$file))) {
			$this->errMsg = 'The selected Javascript Class could not be found (1):'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(!SmartFileSystem::is_type_file((string)$file)) {
			$this->errMsg = 'The selected Javascript Class could not be found (2):'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		$js = (string) SmartFileSystem::read((string)$file);
		if((string)$js == '') {
			$this->errMsg = 'The selected Javascript Class could not be found (3):'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		//--
		$arr = (array) $this->parseClassOnlyDocComments($cls, $js);
		//print_r($arr); die();
		if(empty($arr)) {
			$this->errMsg = 'Cannot get the definition (1) for class:'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(empty($arr['class'])) {
			$this->errMsg = 'Cannot get the definition (2) for class:'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(empty($arr['class']['data'])) {
			$this->errMsg = 'Cannot get the definition (3) for class:'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(Smart::array_size($arr['class']['data']['props']) <= 0) {
			$this->errMsg = 'Cannot get the definition (4) for class:'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(Smart::array_size($arr['class']['data']['props']['class']) <= 0) {
			$this->errMsg = 'Cannot get the definition (5) for class:'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if((string)$arr['class']['data']['props']['class']['type'] !== (string)$cls) {
			$this->errMsg = 'Cannot get the definition (6) for class:'."\n".'`'.$cls.'`';
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		//--
	//	print_r($arr); die();
		//--
		$keys = [
			'requires', // can be array
			'file',
			'package',
			'version',
			'hint',
			'throws',
			'desc',
			'code'
		];
		for($i=0; $i<Smart::array_size($keys); $i++) {
			if((!isset($arr['class']['data']['props'][(string)$keys[$i]])) OR (Smart::array_size($arr['class']['data']['props'][(string)$keys[$i]]) <= 0)) {
				$arr['class']['data']['props'][(string)$keys[$i]] = array();
			} //end if
		} //end if
		//--
		$depends = [];
		if(isset($arr['class']['data']['props']['requires']['line']) AND ((string)trim((string)$arr['class']['data']['props']['requires']['line']) != '')) {
			$depends[] = (string) trim((string)$arr['class']['data']['props']['requires']['line']);
		} else {
			for($i=0; $i<Smart::array_size($arr['class']['data']['props']['requires']); $i++) {
				if((string)trim((string)$arr['class']['data']['props']['requires'][$i]['line']) != '') {
					$depends[] = (string) trim((string)$arr['class']['data']['props']['requires'][$i]['line']);
				} //end if
			} //end for
		} //end if
		//--
		$isfrozen = false;
		if(array_key_exists('frozen', (array)$arr['class']['data']['props'])) {
			$isfrozen = true;
		} //end if
		if(array_key_exists('static', (array)$arr['class']['data']['props'])) {
			$modifier = 'static';
			$usage = (string) $cls.'.method();';
		} else {
			$modifier = 'object';
			$usage = (string) 'var obj = new '.$cls.'(); obj.Method();';
		} //end if
		if((string)$modifier == 'static') {
			$callmode = '.';
		} else {
			$callmode = '(new) .';
		} //end if else
		//--
		$methods = [];
		for($i=0; $i<Smart::array_size($arr['methods']); $i++) {
			if(Smart::array_size($arr['methods'][$i]['data']) > 0) {
				$tmp_method = [];
				$tmp_method['name'] = '';
				$tmp_method['depends'] = [];
				$tmp_method['hint'] = '';
				$tmp_method['throws'] = '';
				$tmp_method['fires'] = '';
				$tmp_method['listens'] = '';
				$tmp_method['modifiers'] = 'public';
				$tmp_method['code-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template($this->highlightJsCode(trim((string)implode("\n", (array)$arr['methods'][$i]['data']['code']))));
				$tmp_method['comment-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html(trim((string)$arr['methods'][$i]['data']['comments']))), true);
				$tmp_method['is-arrow'] = 0;
				$tmp_method['is-static'] = 0;
				$tmp_method['is-internal-priv'] = 0;
				$tmp_method['returns'] = '{Void}';
				$tmp_method['_return'] = '';
				$tmp_method['params'] = [];
				if(Smart::array_size($arr['methods'][$i]['data']['props']) > 0) {
					foreach($arr['methods'][$i]['data']['props'] as $key => $val) {
						if((string)$key == 'method') {
							$tmp_method['name'] = (string) trim((string)$val['type']);
						} elseif((string)$key == 'arrow') {
							$tmp_method['is-arrow'] = 1;
						} elseif((string)$key == 'static') {
							$tmp_method['is-static'] = 1;
						} elseif((string)$key == 'private') {
							$tmp_method['is-internal-priv'] = 1;
						} elseif((string)$key == 'hint') {
							$tmp_method['hint'] = (string) $val['line'];
						} elseif((string)$key == 'throws') {
							$tmp_method['throws'] = (string) $val['line'];
						} elseif((string)$key == 'fires') {
							$tmp_method['fires'] = (string) $val['line'];
						} elseif((string)$key == 'listens') {
							$tmp_method['listens'] = (string) $val['line'];
						} elseif((string)$key == 'code') {
							$tmp_method['code-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::escape_html(trim((string)implode("\n", (array)$val))), true);
						} elseif((string)$key == 'param') {
							$tmp_method['params'] = (array) $val;
						} elseif((string)$key == 'requires') {
							if(isset($val['line']) AND ((string)trim((string)$val['line']) != '')) {
								$tmp_method['depends'][] = (string) trim((string)$val['line']);
							} else {
								for($z=0; $z<Smart::array_size($val); $z++) {
									if((string)trim((string)$val[$z]['line']) != '') {
										$tmp_method['depends'][] = (string) trim((string)$val[$z]['line']);
									} //end if
								} //end for
							} //end if
						} elseif(((string)$key == 'return') OR ((string)$key == 'returns')) {
							$tmp_method['returns'] = (string) $val['type'];
							$tmp_method['_return'] = (string) $val['line'];
						} else {
							$tmp_method[(string)$key] = (string) $val['line'];
						} //end if
					} //end foreach
				} //end if
				if($modifier === 'static') { // let the class overwrite this values
					$tmp_method['is-static'] = 1;
					$tmp_method['modifiers'] .= ' static';
				} //end if
				if((string)$tmp_method['name'] != '') {
					$methods[] = (array) $tmp_method;
				} //end if
				$tmp_method = null;
			} //end if
		} //end for
		//--
		$properties = []; // variables
		$constants = []; // constants
		for($i=0; $i<Smart::array_size($arr['properties']); $i++) {
			if(Smart::array_size($arr['properties'][$i]['data']) > 0) {
				$tmp_property = [];
				$tmp_property['name'] = '';
				$tmp_property['value'] = '';
				$tmp_property['set'] = '';
				$tmp_property['get'] = '';
				$tmp_property['prop-type'] = (string) $arr['properties'][$i]['type'];
				$tmp_property['doc-var-type'] = '';
				$tmp_property['modifiers'] = 'public';
				$tmp_property['code-html'] = '';
				$tmp_property['comment-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html(trim((string)$arr['properties'][$i]['data']['comments']))));
				$tmp_property['is-static'] = 0;
				$tmp_property['is-internal-priv'] = 0;
				if(Smart::array_size($arr['properties'][$i]['data']['props']) > 0) {
					foreach($arr['properties'][$i]['data']['props'] as $key => $val) {
						if(((string)$key == 'var') OR ((string)$key == 'let') OR ((string)$key == 'const')) {
							$tmp_property['name'] = (string) trim((string)$val['var']);
							$tmp_property['doc-var-type'] = (string) trim((string)$val['type']);
						} elseif((string)$key == 'set') {
							$tmp_property['set'] = (string) trim((string)$val['line']);
						} elseif((string)$key == 'get') {
							$tmp_property['get'] = (string) trim((string)$val['line']);
						} elseif((string)$key == 'default') {
							$tmp_property['value'] = (string) trim((string)$val['line']);
						} elseif((string)$key == 'static') {
							$tmp_property['is-static'] = 1;
						} elseif((string)$key == 'private') {
							$tmp_property['is-internal-priv'] = 1;
						}
					} //end foreach
				} //end if
				if($modifier === 'static') { // let the class overwrite this values
					$tmp_property['is-static'] = 1;
				} //end if
				if((string)$tmp_property['name'] != '') {
					if(((string)$tmp_property['prop-type'] == 'var') OR ((string)$tmp_property['prop-type'] == 'let')) {
						if($tmp_property['is-static'] == 1) {
							$tmp_property['modifiers'] .= ' static';
						} //end if
						$properties[] = (array) $tmp_property;
					} elseif((string)$tmp_property['prop-type'] == 'constant') {
						if($tmp_property['is-static'] == 1) {
							$tmp_property['modifiers'] .= ' static';
						} //end if
						$constants[] = (array) $tmp_property;
					} //end if else
				} //end if
				$tmp_property = null;
			} //end if
		} //end for
		//--
		$this->clsPackage = (string) (isset($arr['class']['data']['props']['package']['line']) ? $arr['class']['data']['props']['package']['line'] : '');
		//--
		return (string) SmartMarkersTemplating::render_file_template(
			(string) $this->ControllerGetParam('module-view-path').'js-class.mtpl.inc.htm',
			[
				'base-url' 				=> (string) $base_url,
				'js-path' 				=> (string) $jsPath,
				'file-name' 			=> (string) implode('; ', [ (string)($this->sfjfile ? $this->sfjfile : $file), (string)(isset($arr['class']['data']['props']['file']['line']) ? $arr['class']['data']['props']['file']['line'] : '') ]),
				'type' 					=> (string) $this->clsType,
				'callmode' 				=> (string) $callmode,
				'usage' 				=> (string) $usage,
				'name' 					=> (string) (isset($arr['class']['data']['props']['class']['type']) ? $arr['class']['data']['props']['class']['type'] : ''),
				'is-frozen' 			=> (string) ($isfrozen ? 'yes': 'no'),
				'modifiers' 			=> (string) $modifier,
				'package' 				=> (string) $this->clsPackage,
				'version' 				=> (string) $arr['class']['data']['props']['version']['line'],
				'depends' 				=> (string) implode(', ', (array)$depends),
				'hints' 				=> (string) (isset($arr['class']['data']['props']['hint']['line'])    ? $arr['class']['data']['props']['hint']['line']    : ''),
				'throws' 				=> (string) (isset($arr['class']['data']['props']['throws']['line'])  ? $arr['class']['data']['props']['throws']['line']  : ''),
				'fires' 				=> (string) (isset($arr['class']['data']['props']['fires']['line'])   ? $arr['class']['data']['props']['fires']['line']   : ''),
				'listens' 				=> (string) (isset($arr['class']['data']['props']['listens']['line']) ? $arr['class']['data']['props']['listens']['line'] : ''),
				'arr-methods' 			=> (array)  $methods,
				'arr-properties' 		=> (array)  $properties,
				'arr-constants' 		=> (array)  $constants,
				'doc-comments-html' 	=> (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html($arr['class']['data']['comments'])), true),
				'doc-code-html' 		=> (string) SmartMarkersTemplating::prepare_nosyntax_html_template($this->highlightJsCode(trim((string)implode("\n", (array)$arr['class']['data']['code'])))),
				'generated-on' 			=> (string) date('Y-m-d H:i:s O')
			]
		);
		//--
	} //END FUNCTION


	private function highlightJsCode($code) {
		//--
		if((string)trim((string)$code) == '') {
			return '';
		} //end if
		//--
		$hl = (new \SmartModExtLib\HighlightSyntax\Highlighter())->highlight('javascript', (string)' '.$code);
		//--
		return (string) $hl->value;
		//--
	} //END FUNCTION


	private function parseClassOnlyDocComments($cls, $js) {
		//--
		$regex_comments = '/(\/\*\*)(.*)(\*\/)/sU';
		//--
		$matches = array();
		preg_match_all((string)$regex_comments, (string)$js, $matches, PREG_SET_ORDER);
		$arr = [
			'@metainfo' 	=> (string) __FUNCTION__,
			'class' 		=> [],
			'methods' 		=> [],
			'properties' 	=> []
		];
		for($i=0; $i<Smart::array_size($matches); $i++) {
			//--
			$is_class_found = 0;
			//--
			$props = (array) $this->parseDocComment($matches[$i][2]);
			//--
			if(Smart::array_size($props) > 0) {
				//--
				if(Smart::array_size($props['props']) > 0) {
					//--
					if(isset($props['props']['class']) AND (Smart::array_size($props['props']['class']) > 0)) {
						if((string)$props['props']['class']['type'] == (string)$cls) {
							$is_class_found = 1;
						} //end if
					} elseif(Smart::array_size($props['props']['memberof']) > 0) {
						if((string)$props['props']['memberof']['type'] == (string)$cls) {
							if(isset($props['props']['method']) AND (Smart::array_size($props['props']['method']) > 0)) {
								$is_class_found = 2;
							} elseif(isset($props['props']['var']) AND (Smart::array_size($props['props']['var']) > 0)) {
								$is_class_found = 3;
							} elseif(isset($props['props']['let']) AND (Smart::array_size($props['props']['let']) > 0)) {
								$is_class_found = 4;
							} elseif(isset($props['props']['const']) AND (Smart::array_size($props['props']['const']) > 0)) {
								$is_class_found = 5;
							} //end if else
						} //end if
					} //end if else
					//--
					if((int)$is_class_found > 0) {
						if(isset($props['props']['desc']) AND (Smart::array_size($props['props']['desc']) > 0)) {
							$props['comments'] .= (string) "\n".$props['props']['desc']['line'];
						} //end if
					} //end if
					$props['comments'] = (string) trim((string)$props['comments']);
					//--
					switch((int)$is_class_found) {
						case 1:
							$arr['class'] = [
								'doc-comment' 	=> (string) $matches[$i][2],
								'data' 			=> (array)  $props
							];
							break;
						case 2:
							$arr['methods'][] = [
								'doc-comment' 	=> (string) $matches[$i][2],
								'data' 			=> (array)  $props
							];
							break;
						case 3:
							$arr['properties'][] = [
								'type' 			=> 'var',
								'doc-comment' 	=> (string) $matches[$i][2],
								'data' 			=> (array)  $props
							];
							break;
						case 4:
							$arr['properties'][] = [
								'type' 			=> 'let',
								'doc-comment' 	=> (string) $matches[$i][2],
								'data' 			=> (array)  $props
							];
							break;
						case 5:
							$arr['properties'][] = [
								'type' 			=> 'constant',
								'doc-comment' 	=> (string) $matches[$i][2],
								'data' 			=> (array)  $props
							];
							break;
						default:
							// skip
					} //end switch
					//--
				} //end if
				//--
			} //end if
			//--
		} //end for
		$matches = array();
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	private function parseDocComment($str) {
		//--
		$str = (string) trim((string)$str);
		//--
		if((string)$str == '') {
			return array();
		} //end if
		//--
		$str = (string) str_replace(["\r\n", "\r"], "\n", (string)$str);
		//--
		$regex_dc_code = '/@example\s+([^@]+)/';
		$regex_dc_propx = '/^@([a-z]+)$/';
		$regex_dc_props = '/@([a-z]+)\s+([^\s]+)(\s+[a-zA-Z0-9_\$]+)?(.*)/';
		//--
		$matches = array();
		preg_match_all((string)$regex_dc_code, (string)$str, $matches, PREG_SET_ORDER); // OK to get code
		$arr_code = array();
		for($i=0; $i<Smart::array_size($matches); $i++) {
			if(Smart::array_size($matches[$i]) === 2) {
				$code = (string) $this->fixDocCommentCodePart($matches[$i][1]);
				if((string)$code != '') {
					$arr_code[] = (string) $code;
				} //end if
			} //end if
		} //end for
		//if(strpos($str, '@example') !== false) { print_r($matches); print_r($arr_code); die('----- Code -----'); }
		$matches = array();
		//--
		$str = (string) preg_replace((string)$regex_dc_code, '', (string)$str); // cleanup all code before getting comments to avoid mixings
		//--
		$arr_props = array();
		$arr_comments = array();
		$arr = (array) explode("\n", (string)$str);
		//print_r($arr);
		for($l=0; $l<=Smart::array_size($arr); $l++) {
			$line = (string) (isset($arr[$l]) ? $arr[$l] : '');
			//echo "\n\n".'('.$l.'.) '.$line."\n";
			$line = (string) trim((string)$line);
			$line = (string) ltrim((string)$line, '/*');
			$line = (string) trim((string)$line);
			//echo $line."\n";
			if((string)$line != '') {
				//echo "\n".'#####['.$line.']#####'."\n";
				if(strpos((string)$line, '@') !== 0) { // comment
					$arr_comments[] = (string) $line;
				} elseif(preg_match((string)$regex_dc_propx, (string)trim((string)$line))) { // simple tag with no extra tale
					$name = (string) trim((string)$line);
					$name = (string) ltrim((string)$line, '@');
					if((string)$name != '') {
						$arr_props[(string)$name] = array(
							'type' 		=> '',
							'var' 		=> '',
							'comment' 	=> '',
							'line' 		=> ''
						);
					} //end if
					$name = '';
				} else { // tag
					$matches = array();
					preg_match((string)$regex_dc_props, (string)$line, $matches); // OK to get @properties
					//echo $line."\n"; print_r($matches); echo "\n=====\n"; //die();
					if(Smart::array_size($matches) === 5) {
						$name = (string) $this->fixDocCommentPropPart($matches[1]);
						$prop = [];
						for($j=2; $j<Smart::array_size($matches); $j++) {
							$prop[] = (string) $this->fixDocCommentPropPart($matches[$j]);
						} //end for
						$prop = (string) implode(' ', (array)$prop);
						$prop = (string) $this->fixDocCommentPropPart($prop);
						if((string)$name != '') {
							$tmp_arr = [
								'type' 		=> (string) $this->fixDocCommentPropPart($matches[2]),
								'var' 		=> (string) $this->fixDocCommentPropPart($matches[3]),
								'comment' 	=> (string) $this->fixDocCommentPropPart($matches[4]),
								'line' 		=> (string) $prop
							];
							if(((string)$name == 'param') OR ((string)$name == 'requires')) { // parse special params
								if((!isset($arr_props[(string)$name])) OR (!is_array($arr_props[(string)$name]))) {
									$arr_props[(string)$name] = array();
								} //end if
								$arr_props[(string)$name][] = (array) $tmp_arr;
							} else {
								$arr_props[(string)$name] = (array) $tmp_arr;
							} //end if else
						} //end if
						$name = '';
						$prop = '';
					} //end if
					//print_r($matches); print_r($arr_props); die('----- Props -----');
					$matches = array();
				} //end if else
			} //end if
		} //end for
		//--
		return array(
			'comments' 	=> (string) trim((string)implode("\n", (array)$arr_comments)),
			'props' 	=> (array)  $arr_props,
			'code' 		=> (array)  $arr_code
		);
		//--
	} //END FUNCTION


	private function fixDocCommentPropPart($str) {
		//--
		$str = (string) trim((string)$str);
		if((string)trim((string)$str, '*/') == '') {
			return ''; // avoid if only stars
		} //end if
		//--
		return (string) $str;
		//--
	} //END FUNCTION


	private function fixDocCommentCodePart($str) {
		//--
		$str = (string) trim((string)$str);
		$str = (string) str_replace(["\r\n", "\r"], "\n", (string)$str);
		//--
		$arr = (array) explode("\n", (string)$str);
		$str = '';
		for($i=0; $i<Smart::array_size($arr); $i++) {
			$arr[$i] = (string) ltrim((string)$arr[$i]);
			$arr[$i] = (string) ltrim((string)$arr[$i], '*');
			$arr[$i] = (string) rtrim((string)$arr[$i]);
		} //end if
		$str = (string) implode("\n", (array)$arr);
		if((string)trim((string)$str, "* \t\n\r\0\x0B") == '') {
			return ''; // avoid if only stars and spaces or newlines
		} //end if
		//--
		return (string) trim((string)$str);
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
