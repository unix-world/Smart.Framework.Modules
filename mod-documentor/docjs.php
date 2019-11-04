<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Documentor/DocJs (display, save)
// Route: admin.php?page=documentor.docjs{&cls=SomeClass{&mode=multi}}
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

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
		//--
		return (string) '<pre>'.Smart::escape_html(print_r($arr,1)).'</pre>';
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
							$props['comments'] .= (string) "\n\n".$props['props']['desc']['line'];
						} //end if
						if(Smart::array_size($props['props']['summary']) > 0) {
							$props['comments'] .= (string) "\n\n".$props['props']['summary']['line'];
						} //end if
						if(Smart::array_size($props['props']['hint']) > 0) {
							$props['comments'] .= (string) "\n\n".$props['props']['hint']['line'];
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
							if(((string)$name == 'param') OR ((string)$name == 'requires')) { // parse magic methods
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
		return (string) $str;
		//--
	} //END FUNCTION


} //END CLASS


//end of php code
?>