<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Documentor/DocJs (display, save)
// Route: admin.php?page=documentor.docjs{&cls=SomeClass{&mode=multi}}
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


define('SMART_APP_MODULE_AREA', 'ADMIN'); // INDEX, ADMIN, SHARED
define('SMART_APP_MODULE_AUTH', true); // if set to TRUE requires auth always


/**
 * Admin Area Controller
 * @version 20191104
 * @ignore
 */
final class SmartAppAdminController extends SmartAbstractAppController {

	private $errMsg 	= null;
	private $classes 	= [
		'SmartJS_CoreUtils' 		=> 'lib/js/framework/src/core_utils.js',
		'SmartJS_DateUtils' 		=> 'lib/js/framework/src/date_utils.js',
		'SmartJS_Base64' 			=> 'lib/js/framework/src/crypt_utils.js',
		'SmartJS_CryptoHash' 		=> 'lib/js/framework/src/crypt_utils.js',
		'SmartJS_CryptoBlowfish' 	=> 'lib/js/framework/src/crypt_utils.js',
		'SmartJS_Archiver_LZS' 		=> 'lib/js/framework/src/arch_utils.js',
		'SmartJS_BrowserUtils' 		=> 'lib/js/framework/src/browser_utils.js',
		'Test_Browser_Compliance ' 	=> 'lib/js/framework/src/browser_check.js'
	];

	public function Initialize() {

		//--
		$this->PageViewSetCfg('template-path', '@'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-documentor.htm'); // the default template
		//--

		//--
		$this->PageViewSetVars([
			//--
			'fonts-path' 		=> (string) $this->ControllerGetParam('module-path').'fonts/',
			'logo-img' 			=> (string) 'lib/framework/img/sf-logo.svg',
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
		if(!defined('SMART_FRAMEWORK_DOCUMENTOR_ALLOW') OR (SMART_FRAMEWORK_DOCUMENTOR_ALLOW !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Documentor is disabled ...');
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

		//--
		$cls = (string) trim((string)$this->RequestVarGet('cls', '', 'string'));
		//--
		if((string)$cls == '') {
			switch((string)$action) {
				case 'save':
					$this->jsonAnswer('A JavaScript Class must be selected ...', false);
					break;
				default:
					$this->displaySelector($cls);
			} //end switch
			return;
		} //end if
		//--

		//--
		switch((string)$action) {
			case 'save':
				//--

				//--
				break;
			default:
				//--
				$url_index = (string) $this->ControllerGetParam('url-script').'?page='.Smart::escape_url($this->ControllerGetParam('controller'));
				$url_cls = '&cls=';
				//--
				$this->PageViewSetVars([
					'title' 			=> (string) 'Documentation for: '.$cls,
					'main' 				=> SmartMarkersTemplating::render_file_template(
									(string) $this->ControllerGetParam('module-view-path').'display.mtpl.htm',
									[
										'DISPLAY' 	=> 'documentation',
										'MESSAGE' 	=> '',
										'DOCS-HTML' => (string) $this->displayDocs(
											(string) $cls,
											(string) $url_index.$url_cls
										)
									]
								),
					'url-index' => (string) $url_index
				]);
				//--
		} //end switch


	} //END FUNCTION


	//##### PRIVATES


	private function displaySelector($cls, $message='') {
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


	private function displayDocs($cls, $base_url='') {
		//--
		if(!in_array((string)$cls, (array)array_keys((array)$this->classes))) {
			$this->errMsg = 'The selected JavaScript Class could not be found: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		//--
		$file = (string) $this->classes[(string)$cls];
		if((string)$file == '') {
			$this->errMsg = 'Cannot get the definition (1) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(!SmartFileSystem::is_type_file((string)$file)) {
			$this->errMsg = 'Cannot get the definition (2) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		$js = (string) SmartFileSystem::read((string)$file);
		if((string)$js == '') {
			$this->errMsg = 'Cannot get the definition (3) for class: '.$cls;
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
			$this->errMsg = 'Cannot get the definition (4) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(empty($arr['class'])) {
			$this->errMsg = 'Cannot get the definition (5) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(empty($arr['class']['data'])) {
			$this->errMsg = 'Cannot get the definition (6) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(Smart::array_size($arr['class']['data']['props']) <= 0) {
			$this->errMsg = 'Cannot get the definition (7) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(Smart::array_size($arr['class']['data']['props']['class']) <= 0) {
			$this->errMsg = 'Cannot get the definition (8) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if((string)$arr['class']['data']['props']['class']['type'] !== (string)$cls) {
			$this->errMsg = 'Cannot get the definition (9) for class: '.$cls;
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
			if(Smart::array_size($arr['class']['data']['props'][(string)$keys[$i]]) <= 0) {
				$arr['class']['data']['props'][(string)$keys[$i]] = array();
			} //end if
		} //end if
		//--
		$depends = [];
		if((string)trim((string)$arr['class']['data']['props']['requires']['line']) != '') {
			$depends[] = (string) trim((string)$arr['class']['data']['props']['requires']['line']);
		} else {
			for($i=0; $i<Smart::array_size($arr['class']['data']['props']['requires']); $i++) {
				if((string)trim((string)$arr['class']['data']['props']['requires'][$i]['line']) != '') {
					$depends[] = (string) trim((string)$arr['class']['data']['props']['requires'][$i]['line']);
				} //end if
			} //end for
		} //end if
		//--
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
				$tmp_method['throws'] = '';
				$tmp_method['fires'] = '';
				$tmp_method['listens'] = '';
				$tmp_method['modifiers'] = 'public';
				$tmp_method['code-html'] = '';
				$tmp_method['comment-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html(trim((string)$arr['methods'][$i]['data']['comments']))));
				$tmp_method['is-static'] = 0;
				$tmp_method['is-internal-priv'] = 0;
				$tmp_method['returns'] = '{Void}';
				$tmp_method['_return'] = '';
				$tmp_method['params'] = [];
				if(Smart::array_size($arr['methods'][$i]['data']['props']) > 0) {
					foreach($arr['methods'][$i]['data']['props'] as $key => $val) {
						if((string)$key == 'method') {
							$tmp_method['name'] = (string) trim((string)$val['type']);
						} elseif((string)$key == 'static') {
							$tmp_method['is-static'] = 1;
						} elseif((string)$key == 'private') {
							$tmp_method['is-internal-priv'] = 1;
						} elseif((string)$key == 'throws') {
							$tmp_method['throws'] = (string) $val['line'];
						} elseif((string)$key == 'fires') {
							$tmp_method['fires'] = (string) $val['line'];
						} elseif((string)$key == 'listens') {
							$tmp_method['listens'] = (string) $val['line'];
						} elseif((string)$key == 'code') {
							$tmp_method['code-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::escape_html(trim((string)implode("\n", (array)$val))));
						} elseif((string)$key == 'param') {
							$tmp_method['params'] = (array) $val;
						} elseif((string)$key == 'return') {
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
		return (string) SmartMarkersTemplating::render_file_template(
			(string) $this->ControllerGetParam('module-view-path').'js-class.mtpl.inc.htm',
			[
				'base-url' 				=> (string) $base_url,
				'file-name' 			=> (string) $arr['class']['data']['props']['file']['line'],
				'type' 					=> (string) 'class',
				'callmode' 				=> (string) $callmode,
				'usage' 				=> (string) $usage,
				'name' 					=> (string) $arr['class']['data']['props']['class']['type'],
				'modifiers' 			=> (string) $modifier,
				'package' 				=> (string) $arr['class']['data']['props']['package']['line'],
				'version' 				=> (string) $arr['class']['data']['props']['version']['line'],
				'depends' 				=> (string) implode(', ', (array)$depends),
				'hints' 				=> (string) $arr['class']['data']['props']['hint']['line'],
				'throws' 				=> (string) $arr['class']['data']['props']['throws']['line'],
				'fires' 				=> (string) $arr['class']['data']['props']['fires']['line'],
				'listens' 				=> (string) $arr['class']['data']['props']['listens']['line'],
				'arr-methods' 			=> (array)  $methods,
				'doc-comments-html' 	=> (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html($arr['class']['data']['comments']))),
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
		$hl = (new \SmartModExtLib\HighlightSyntax\Highlighter())->highlight('javascript', (string)$code);
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
					if(Smart::array_size($props['props']['class']) > 0) {
						if((string)$props['props']['class']['type'] == (string)$cls) {
							$is_class_found = 1;
						} //end if
					} elseif(Smart::array_size($props['props']['memberof']) > 0) {
						if((string)$props['props']['memberof']['type'] == (string)$cls) {
							if(Smart::array_size($props['props']['method']) > 0) {
								$is_class_found = 2;
							} elseif(Smart::array_size($props['props']['var']) > 0) {
								$is_class_found = 3;
							} //end if else
						} //end if
					} //end if else
					//--
					if($is_class_found > 0) {
						if(Smart::array_size($props['props']['desc']) > 0) {
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
		$regex_dc_props = '/@([a-z]+)\s+([^\s]+)(\s+[a-zA-Z0-9_]+)?(.*)/';
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
			$line = (string) $arr[$l];
			//echo "\n\n\n".'('.$l.'.) '.$line."\n";
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
								if(!is_array($arr_props[(string)$name])) {
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


//end of php code
?>