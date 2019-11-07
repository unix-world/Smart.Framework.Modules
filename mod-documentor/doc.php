<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Documentor/Doc (display, save)
// Route: admin.php?page=documentor.doc{&cls=SomeClass&ref={&action=save{&mode=multi}}}
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
 * @version 20191106
 * @package Application
 */
final class SmartAppAdminController extends SmartAbstractAppController {


	private $errMsg 	= null;
	private $clsType 	= '';
	private $clsPackage = '';

	const IMG_LOGO 		= 'lib/framework/img/sf-logo.svg';
	const DIR_DOCS 		= 'tmp/documentor-php/';
	const DIR_PACKAGES 	= 'tmp/documentor-php@packages/';

	public function Initialize() {

		//--
		if(!defined('SMART_APP_MODULES_EXTRALIBS_VER')) {
			require_once('modules/smart-extra-libs/autoload.php');
		} //end if
		//--

		//-- {{{SYNC-DOCUMENTOR-TPL}}}
		$this->PageViewSetCfg('template-path', '@'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-documentor.htm'); // the default template
		//--
		$this->PageViewSetVars([
			//--
			'fonts-path' 		=> (string) $this->ControllerGetParam('module-path').'fonts/',
			'logo-img' 			=> (string) self::IMG_LOGO,
			'year' 				=> (string) date('Y'),
			//--
			'title' 			=> (string) 'Documentation',
			'heading-title' 	=> (string) 'PHP Documentation',
			'seo-description'	=> (string) 'Smart.Framework Documentation',
			'seo-keywords'		=> (string) 'php, smart, framework, documentor',
			'seo-summary' 		=> (string) 'Smart.Framework, a PHP / Javascript Framework for Web',
			'url-index' 		=> ''
			//--
		]);
		//-- #end sync

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
		if(version_compare((string)phpversion(), '7.1') < 0) { // {{{SYNC-DOCUMENTOR-PHP-MIN-VERSION}}}
			$this->PageViewSetErrorStatus(503, 'Service N/A: PHP 7.1 or later is required for this service');
			return;
		} //end if
		//--
		if(function_exists('\\opcache_get_status')) {
			if((string)ini_get('opcache.save_comments') != '1') {
				$this->PageViewSetErrorStatus(503, 'Service N/A: PHP Opcache is active and Opcache.SaveComments is Disabled (and must be Enabled)');
				return;
			} //end if
		} //end if
		//--

		//--
		$action = $this->RequestVarGet('action', '', 'string');
		$mode = $this->RequestVarGet('mode', '', 'string');
		$extra = $this->RequestVarGet('extra', '', 'string');
		$heading = $this->RequestVarGet('heading', 'PHP Documentation', 'string');
		//--
		if((string)$action == 'cleanup@documentation') {
			//--
			if(SmartFileSystem::is_type_dir((string)self::DIR_PACKAGES)) {
				SmartFileSystem::dir_delete((string)self::DIR_PACKAGES);
				if(SmartFileSystem::is_type_dir((string)self::DIR_PACKAGES)) {
					$this->jsonAnswer('Documentation Packages directory cannot be cleared ...', false);
					return;
				} //end if
			} //end if
			if(SmartFileSystem::is_type_dir((string)self::DIR_DOCS)) {
				SmartFileSystem::dir_delete((string)self::DIR_DOCS);
				if(SmartFileSystem::is_type_dir((string)self::DIR_DOCS)) {
					$this->jsonAnswer('Documentation directory cannot be cleared ...', false);
					return;
				} //end if
			} //end if
			//--
			$this->jsonAnswer('Documentation directory and Documentation Packages directory Cleared');
			return;
			//--
		} elseif((string)$action == 'index@packages') {
			//--
			if(!SmartFileSystem::is_type_dir((string)self::DIR_DOCS)) {
				$this->jsonAnswer('Documentation directory not found ...', false);
				return;
			} //end if
			if(!SmartFileSystem::is_type_dir((string)self::DIR_PACKAGES)) {
				$this->jsonAnswer('Documentation Packages directory not found ...', false);
				return;
			} //end if
			//--
			$arr_packages = [];
			$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage((string)self::DIR_PACKAGES, false, false, '.json'); // non-recuring, no dot files, only JSON files
			for($i=0; $i<\Smart::array_size($files_n_dirs['list-files']); $i++) {
				$tmp_json = (string) self::DIR_PACKAGES.$files_n_dirs['list-files'][$i];
				if(SmartFileSystem::is_type_file($tmp_json)) {
					$tmp_json = (string) SmartFileSystem::read($tmp_json);
				} else {
					$tmp_json = '';
				} //end if
				if((string)trim((string)$tmp_json) == '') {
					$this->jsonAnswer('Documentation Package NOT FOUND: '.$files_n_dirs['list-files'][$i], false);
					return;
				} //end if
				$tmp_json = Smart::json_decode((string)$tmp_json);
				if(Smart::array_size($tmp_json) <= 0) {
					$this->jsonAnswer('Documentation Package is INVALID: '.$files_n_dirs['list-files'][$i], false);
					return;
				} else {
					$tmp_json['package'] = (string) trim((string)$tmp_json['package']);
					$tmp_json['name'] = (string) trim((string)$tmp_json['name']);
					$tmp_json['type'] = (string) trim((string)$tmp_json['type']);
					if((string)$tmp_json['package'] == '') {
						$tmp_json['package'] = '@No-Package';
					} //end if
					$tmp_json['file'] = (string) Smart::base_name((string)$files_n_dirs['list-files'][$i], '.json');
					if(!is_array($arr_packages[(string)$tmp_json['package']])) {
						$arr_packages[(string)$tmp_json['package']] = [];
					} //end if
					$arr_packages[(string)$tmp_json['package']][] = (array) $tmp_json;
				} //end if
				$tmp_json = null;
			} //end for
			$files_n_dirs = null; // free mem
			//--
			ksort($arr_packages);
			//print_r($arr_packages); die();
			if(Smart::array_size($arr_packages) <= 0) {
				$this->jsonAnswer('Documentation Packages is Empty', false);
				return;
			} //end if
			//--
			$main = (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'packages.mtpl.inc.htm',
				[
					'packages' 		=> (array) $arr_packages,
					'generated-on' 	=> (string) date('Y-m-d H:i:s O')
				]
			);
			//-- {{{SYNC-DOCUMENTOR-SAVE-MODE}}}
			$url_index = '';
			$url_img   = '';
			$url_fonts = '';
			$extdir = '';
			if((string)$mode == 'multi') {
				if((string)$extra != '') {
					if((string)$extra == '@') {
						$url_img 	= 'img/sf-logo.svg';
						$url_fonts 	= 'fonts/';
					} else {
						$url_index = '../index.html';
						$extdir 	= (string) SmartFileSysUtils::add_dir_last_slash(Smart::safe_filename((string)$extra));
						$url_img 	= '../img/sf-logo.svg';
						$url_fonts 	= '../fonts/';
					} //end if else
				} //end if
			} //end if else
			//-- #end sync
			$doc = (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-path').'templates/template-documentor.htm',
				(array)  SmartComponents::set_app_template_conform_metavars([
					//--
					'fonts-path' 		=> (string) $url_fonts,
					'logo-img' 			=> (string) $url_img,
					'year' 				=> (string) date('Y'),
					//--
					'title' 			=> (string) 'PHP Documentation',
					'heading-title' 	=> (string) $heading,
					'seo-description' 	=> (string) SmartUtils::extract_description($main),
					'seo-keywords' 		=> (string) SmartUtils::extract_keywords($main),
					'seo-summary' 		=> (string) SmartUtils::extract_title($heading),
					'main' 				=> (string) $main,
					'url-index' 		=> (string) $url_index
					//--
				])
			);
			//--
			$dir = self::DIR_DOCS.$extdir;
			if(SmartFileSystem::is_type_file($dir.'index.html')) {
				SmartFileSystem::delete($dir.'index.html');
				if(SmartFileSystem::is_type_file($dir.'index.html')) {
					$this->jsonAnswer('Cannot delete Documentation Packages Index file', false);
					return;
				} //end if
			} //end if
			if(!SmartFileSystem::write($dir.'index.html', $doc)) {
				$this->jsonAnswer('Cannot save Documentation Packages Index file', false);
				return;
			} //end if
			if(!SmartFileSystem::is_type_file($dir.'index.html')) {
				$this->jsonAnswer('Cannot find Documentation Packages Index file', false);
				return;
			} //end if
			//--
			if(SmartFileSystem::is_type_dir((string)self::DIR_PACKAGES)) {
				SmartFileSystem::dir_delete((string)self::DIR_PACKAGES);
				if(SmartFileSystem::is_type_dir((string)self::DIR_PACKAGES)) {
					$this->jsonAnswer('Documentation Packages directory cannot be cleared ...', false);
					return;
				} //end if
			} //end if
			//--
			if((string)$extra != '') {
				//--
				if(SmartFileSystem::dir_copy($this->ControllerGetParam('module-path').'fonts/', self::DIR_DOCS.'fonts/', true) != 1) {
					$this->jsonAnswer('Failed to copy font files to Documentation font directory ...', false);
					return;
				} //end if
				if(!SmartFileSystem::is_type_file(self::DIR_DOCS.'fonts/index.html')) {
					$this->jsonAnswer('Cannot find fonts directory Index file for Documentation', false);
					return;
				} //end if
				//--
				if(!SmartFileSystem::is_type_dir(self::DIR_DOCS.'img/')) {
					SmartFileSystem::dir_create(self::DIR_DOCS.'img/', true);
					if(!SmartFileSystem::is_type_dir(self::DIR_DOCS.'img/')) {
						$this->jsonAnswer('Failed to create img directory into Documentation directory ...', false);
						return;
					} //end if
				} //end if
				if(!SmartFileSystem::write(self::DIR_DOCS.'img/index.html', '')) {
					$this->jsonAnswer('Cannot create img directory Index file for Documentation', false);
					return;
				} //end if
				if(!SmartFileSystem::is_type_file(self::DIR_DOCS.'img/index.html')) {
					$this->jsonAnswer('Cannot find img directory Index file for Documentation', false);
					return;
				} //end if
				if(!SmartFileSystem::copy((string)self::IMG_LOGO, self::DIR_DOCS.'img/'.SmartFileSysUtils::get_file_name_from_path((string)self::IMG_LOGO), false, true)) {
					$this->jsonAnswer('Failed to copy font img Logo to Documentation img directory ...', false);
					return;
				} //end if
				if(!SmartFileSystem::is_type_file(self::DIR_DOCS.'img/sf-logo.svg')) {
					$this->jsonAnswer('Cannot find img Logo file for Documentation', false);
					return;
				} //end if
				//--
			} //end if
			//--
			$this->jsonAnswer('Documentation Packages Indexed');
			return;
			//--
		} //end if
		//--

		//--
		$ref = (string) trim((string)$this->RequestVarGet('ref', '', 'string')); // sometimes loading a class / interface / trait needs to pre-load another one
		if((string)$ref != '') {
			$ref = (string) '\\'.ltrim((string)$ref, '\\');
			if((!class_exists((string)$ref, true)) AND (!interface_exists((string)$ref, true)) AND (!trait_exists((string)$ref, true))) {
				$errmsg = 'Info: The selected PHP Class / Interface / Trait does could not be loaded: `'.$cls.'` as it depends on: `'.$ref.'` which could not be found';
				switch((string)$action) {
					case 'save':
						$this->jsonAnswer((string)$errmsg, false);
						break;
					default:
						$this->displaySelector($cls, (string)$errmsg);
				} //end switch
				return;
			} //end if
		} //end if
		//--
		$cls = (string) trim((string)$this->RequestVarGet('cls', '', 'string'));
		if((string)$cls == '') {
			switch((string)$action) {
				case 'save':
					$this->jsonAnswer('A Class / Interface / Trait must be selected ...', false);
					break;
				default:
					$this->displaySelector($cls);
			} //end switch
			return;
		} //end if
		$cls = (string) '\\'.ltrim((string)$cls, '\\');
		//--
		if((!class_exists((string)$cls, true)) AND (!interface_exists((string)$cls, true)) AND (!trait_exists((string)$cls, true))) {
			$errmsg = 'Info: The selected PHP Class / Interface / Trait could not be found: `'.$cls.'`';
			switch((string)$action) {
				case 'save':
					$this->jsonAnswer((string)$errmsg, false);
					break;
				default:
					$this->displaySelector($cls, (string)$errmsg);
			} //end switch
			return;
		} //end if
		//--

		//--
		switch((string)$action) {
			case 'save':
				//--
				$main = $this->displayDocs((string)$cls);
				//--
				if($this->errMsg === null) { // OK
					//-- {{{SYNC-DOCUMENTOR-SAVE-MODE}}}
					$url_index = '';
					$url_img   = '';
					$url_fonts = '';
					$extdir = '';
					if((string)$mode == 'multi') {
						$url_index = 'index.html#Package--'.Smart::create_htmid($this->clsPackage).'-';
						if((string)$extra != '') {
							if((string)$extra == '@') {
								$url_img 	= 'img/sf-logo.svg';
								$url_fonts 	= 'fonts/';
							} else {
								$extdir 	= (string) SmartFileSysUtils::add_dir_last_slash(Smart::safe_filename((string)$extra));
								$url_img 	= '../img/sf-logo.svg';
								$url_fonts 	= '../fonts/';
							} //end if else
						} //end if
					} //end if else
					//-- #end sync
					$doc = (string) SmartMarkersTemplating::render_file_template(
						(string) $this->ControllerGetParam('module-path').'templates/template-documentor.htm',
						(array)  SmartComponents::set_app_template_conform_metavars([
							//--
							'fonts-path' 		=> (string) $url_fonts,
							'logo-img' 			=> (string) $url_img,
							'year' 				=> (string) date('Y'),
							//--
							'title' 			=> (string) 'PHP Documentation for: '.$cls,
							'heading-title' 	=> (string) $heading,
							'seo-description' 	=> (string) SmartUtils::extract_description($cls.' '.$main),
							'seo-keywords' 		=> (string) SmartUtils::extract_keywords($cls.' '.$main),
							'seo-summary' 		=> (string) SmartUtils::extract_title($heading.': '.$cls),
							'main' 				=> (string) $main,
							'url-index' 		=> (string) $url_index
							//--
						])
					);
					//--
					$type = 'unknown';
					if((string)$this->clsType != '') {
						$type = (string) $this->clsType;
					} //end if
					//--
					$slug = (string) Smart::safe_filename($type.'@'.Smart::create_slug((string)$cls).'.html');
					//--
					$dir = (string) self::DIR_DOCS.$extdir;
					if(!SmartFileSystem::is_type_dir($dir)) {
						SmartFileSystem::dir_create($dir, true);
						if(!SmartFileSystem::is_type_dir($dir)) {
							$this->jsonAnswer('Cannot create Documentation directory for: `'.$cls.'` as: '.$dir, false);
							return;
						} //end if
					} //end if
					if(SmartFileSystem::is_type_file($dir.$slug)) {
						SmartFileSystem::delete($dir.$slug);
						if(SmartFileSystem::is_type_file($dir.$slug)) {
							$this->jsonAnswer('Cannot delete Documentation file for: `'.$cls.'` as: '.$dir.$slug, false);
							return;
						} //end if
					} //end if
					if(!SmartFileSystem::write($dir.$slug, $doc)) {
						$this->jsonAnswer('Cannot save Documentation file for: `'.$cls.'` as: '.$dir.$slug, false);
						return;
					} //end if
					if(!SmartFileSystem::is_type_file($dir.$slug)) {
						$this->jsonAnswer('Cannot find Documentation file for: `'.$cls.'` as: '.$dir.$slug, false);
						return;
					} //end if
					//--
					$xdir = (string) self::DIR_PACKAGES;
					if(!SmartFileSystem::is_type_dir($xdir)) {
						SmartFileSystem::dir_create($xdir, true);
						if(!SmartFileSystem::is_type_dir($xdir)) {
							$this->jsonAnswer('Cannot create Documentation Packages directory for: `'.$cls.'` as: '.$xdir, false);
							return;
						} //end if
					} //end if
					if(!SmartFileSystem::write($xdir.$slug.'.json', (string)Smart::json_encode([ 'package' => (string)$this->clsPackage, 'name' => (string)$cls, 'type' => (string)$this->clsType ]))) {
						$this->jsonAnswer('Cannot save Documentation Package file for: `'.$cls.'` as: '.$xdir.$slug.'.json', false);
						return;
					} //end if
					if(!SmartFileSystem::is_type_file($xdir.$slug.'.json')) {
						$this->jsonAnswer('Cannot find Documentation Package file for: `'.$cls.'` as: '.$xdir.$slug.'.json', false);
						return;
					} //end if
					//--
					$this->jsonAnswer('Documentation saved for: '.$type.' `'.$cls.'` as: '.$slug);
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
				$url_ref = '&ref=';
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
											(string) $url_index.$url_cls,
											(string) $url_ref
										)
									]
								),
					'url-index' => (string) $url_index
				]);
				//--
		} //end switch
		//--

	} //END FUNCTION


	public function ShutDown() {} // re-implement for documentation purposes


	//##### PRIVATES


	private function displaySelector($cls, $message='') {
		//--
		$title = 'Select a PHP Class / Interface or Trait to display Documentation';
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


	private function displayDocs($cls, $base_url='', $ref_url='') {
		//--
		$arr = (array) $this->parseClass($cls);
		//print_r($arr); die();
		if(empty($arr)) {
			$this->errMsg = 'Cannot get the definition (1) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		if(empty($arr['class'])) {
			$this->errMsg = 'Cannot get the definition (2) for class: '.$cls;
			return (string) SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').'message.mtpl.inc.htm',
				[
					'MESSAGE' => (string) $this->errMsg
				]
			);
		} //end if
		//--
		$doc_comments = '';
		$doc_code = '';
		if(Smart::array_size($arr['class']['doc-comments']) > 0) {
			if((string)trim((string)$arr['class']['doc-comments']['comments']) != '') {
				$doc_comments = (string) trim((string)$arr['class']['doc-comments']['comments']);
				$doc_comments = (string) Smart::nl_2_br(Smart::escape_html($doc_comments)); // need to be HTML for prepare nosyntax
			} //end if
			if(Smart::array_size($arr['class']['doc-comments']['code']) > 0) {
				$doc_code = (string) $this->prettyPrintPhpCode((string)implode("\n\n\n", (array)$arr['class']['doc-comments']['code']));
			} //end if
		} //end if
		//--
		$doc_package = '';
		$doc_version = '';
		$doc_depends = '';
		$doc_hints = '';
		$doc_usage = '';
		$doc_throws = '';
		if(Smart::array_size($arr['class']['doc-@comments']) > 0) {
			$doc_package = (string) $arr['class']['doc-@comments']['package'];
			$doc_version = (string) $arr['class']['doc-@comments']['version'];
			$doc_depends = (string) $arr['class']['doc-@comments']['depends'];
			$doc_hints = (string) $arr['class']['doc-@comments']['hints'];
			$doc_usage = (string) $arr['class']['doc-@comments']['usage'];
			$doc_throws = (string) $arr['class']['doc-@comments']['throws'];
		} //end if
		//--
		if(Smart::array_size($arr['constants']) > 0) {
			for($i=0; $i<Smart::array_size($arr['constants']); $i++) {
				//--
				$arr['constants'][$i]['doc-const-type'] = '';
				//--
				if(Smart::array_size($arr['constants'][$i]['doc-comments']['props']) > 0) {
					if(Smart::array_size($arr['constants'][$i]['doc-comments']['props']['const']) > 0) {
						$arr['constants'][$i]['doc-const-type'] = (string) trim((string)$arr['constants'][$i]['doc-comments']['props']['const']['type']);
					} //end if
				} //end if
				//--
				$arr['constants'][$i]['comment-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html($arr['constants'][$i]['doc-comments']['comments'])));
				//--
			} //end for
		} //end if
		//--
		if(Smart::array_size($arr['properties']) > 0) {
			for($i=0; $i<Smart::array_size($arr['properties']); $i++) {
				//--
				$arr['properties'][$i]['doc-var-type'] = '';
				//--
				if(Smart::array_size($arr['properties'][$i]['doc-comments']['props']) > 0) {
					if(Smart::array_size($arr['properties'][$i]['doc-comments']['props']['var']) > 0) {
						$arr['properties'][$i]['doc-var-type'] = (string) trim((string)$arr['properties'][$i]['doc-comments']['props']['var']['type']);
					} //end if
				} //end if
				//--
				$arr['properties'][$i]['comment-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html($arr['properties'][$i]['doc-comments']['comments'])));
				//--
			} //end for
		} //end if
		//--
		if(Smart::array_size($arr['methods']) > 0) {
			for($i=0; $i<Smart::array_size($arr['methods']); $i++) {
				//--
				$arr['methods'][$i]['ret-param-html'] = '';
				$tmp_arr_ret_param = [];
				//--
				if(Smart::array_size($arr['methods'][$i]['doc-comments']['props']['throws']) > 0) {
					$tmp_arr_ret_param[] = '@throws: {'.trim((string)$arr['methods'][$i]['doc-comments']['props']['throws']['type'].'} : '.ltrim($arr['methods'][$i]['doc-comments']['props']['throws']['comment'], ' :'));
				} //end if
				if(Smart::array_size($arr['methods'][$i]['doc-comments']['props']['hints']) > 0) {
					$tmp_arr_ret_param[] = '@hints: '.ltrim($arr['methods'][$i]['doc-comments']['props']['hints']['line'], ' :');
				} //end if
				if($arr['methods'][$i]['is-magic'] === true) {
					if(Smart::array_size($arr['methods'][$i]['doc-comments']['props']) > 0) {
						if(Smart::array_size($arr['methods'][$i]['doc-comments']['props']['method']) > 0) {
							for($j=0; $j<Smart::array_size($arr['methods'][$i]['doc-comments']['props']['method']); $j++) {
									$tmp_arr_ret_param[] = rtrim('@method: {@return: '.trim((string)$arr['methods'][$i]['doc-comments']['props']['method'][$j]['type']).'} '.trim((string)ltrim($arr['methods'][$i]['doc-comments']['props']['method'][$j]['comment'], ' :')), ' :');
							} //end for
						} //end if
					} //end if
				} else {
					if(Smart::array_size($arr['methods'][$i]['doc-comments']['props']) > 0) {
						if(Smart::array_size($arr['methods'][$i]['doc-comments']['props']['return']) > 0) {
							$tmp_arr_ret_param[] = '@return: {'.trim((string)$arr['methods'][$i]['doc-comments']['props']['return']['type'].'} : '.ltrim($arr['methods'][$i]['doc-comments']['props']['return']['comment'], ' :'));
						} //end if
						if(Smart::array_size($arr['methods'][$i]['doc-comments']['props']['param']) > 0) {
							for($j=0; $j<Smart::array_size($arr['methods'][$i]['doc-comments']['props']['param']); $j++) {
								if(strpos(trim((string)$arr['methods'][$i]['doc-comments']['props']['param'][$j]['var']), '$') === 0) {
									$tmp_arr_ret_param[] = rtrim('@param: {'.trim((string)$arr['methods'][$i]['doc-comments']['props']['param'][$j]['var']).'} '.trim((string)ltrim($arr['methods'][$i]['doc-comments']['props']['param'][$j]['comment'], ' :')), ' :');
								} //end if
							} //end for
						} //end if
					} //end if
				} //end if else
				//--
				if(Smart::array_size($tmp_arr_ret_param) > 0) {
					$arr['methods'][$i]['ret-param-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html(implode("\n", (array)$tmp_arr_ret_param))));
				} //end if else
				$tmp_arr_ret_param = [];
				//--
				$arr['methods'][$i]['comment-html'] = (string) SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::nl_2_br(Smart::escape_html($arr['methods'][$i]['doc-comments']['comments'])));
				//--
			} //end for
		} //end if
		//--
		$this->clsType = (string) $arr['class']['type'];
		$this->clsPackage = (string) $doc_package;
		//--
		return (string) SmartMarkersTemplating::render_file_template(
			(string) $this->ControllerGetParam('module-view-path').'class.mtpl.inc.htm',
			[
				'base-url' 				=> (string) $base_url,
				'ref-url' 				=> (string) $ref_url,
				'file-name' 			=> (string) $arr['class']['file-name'],
				'is-internal-priv' 		=> (bool)   $arr['class']['is-internal-priv'],
				'type' 					=> (string) $arr['class']['type'],
				'callmode' 				=> (string) $arr['class']['callmode'],
				'name' 					=> (string) $arr['class']['class'],
				'short-name' 			=> (string) $arr['class']['short-name'],
				'namespace' 			=> (string) $arr['class']['namespace'],
				'modifiers' 			=> (string) $arr['class']['modifiers'],
				'parents' 				=> (array)  $arr['class']['parents'],
				'extends' 				=> (string) $arr['class']['extends'],
				'implements' 			=> (string) implode(', ', (array)$arr['class']['interfaces']),
				'use' 					=> (string) implode(', ', (array)$arr['class']['traits']),
				'constructor' 			=> (string) $arr['class']['constructor'],
				'doc-comments-html' 	=> (string) SmartMarkersTemplating::prepare_nosyntax_html_template($doc_comments),
				'doc-code-html' 		=> (string) SmartMarkersTemplating::prepare_nosyntax_html_template($doc_code),
				'arr-constants' 		=> (array)  $arr['constants'],
				'arr-properties' 		=> (array)  $arr['properties'],
				'arr-methods' 			=> (array)  $arr['methods'],
				'package' 				=> (string) $doc_package,
				'version' 				=> (string) $doc_version,
				'depends' 				=> (string) $doc_depends,
				'hints' 				=> (string) $doc_hints,
				'usage' 				=> (string) $doc_usage,
				'throws' 				=> (string) $doc_throws,
				'generated-on' 			=> (string) date('Y-m-d H:i:s O')
			]
		);
		//--
	} //END FUNCTION


	private function parseClass($class_name) {
		//--
		if((string)trim((string)$class_name) == '') {
			return array();
		} //end if
		//--
		if((!class_exists((string)$class_name, true)) AND (!interface_exists((string)$class_name, true)) AND (!trait_exists((string)$class_name, true))) {
			return array();
		} //end if
		//--
		$rc = new ReflectionClass((string)$class_name);
		if(!is_a($rc, 'ReflectionClass')) {
			return array();
		} //end if
		if($rc->isInternal() === true) {
			return array();
		} //end if
		//--
		$fname = (string) $rc->getFileName();
		if(strpos((string)$fname, '/lib/') !== false) {
			$fname = (string) substr((string)$fname, (strpos((string)$fname, '/lib/')+1));
		} elseif(strpos((string)$fname, 'modules/') !== false) {
			$fname = (string) substr((string)$fname, (strpos((string)$fname, 'modules/'))); // must be without first slash as it can be in the Smart.Framework.Modules repo
		} else {
			$fname = (string) Smart::base_name($fname);
		} //end if else
		//--
		$pclass = $rc->getParentClass(); // mixed
		if(is_a($pclass, 'ReflectionClass')) {
			$pclass = (string) $pclass->getName();
		} else {
			$pclass = '';
		} //end if
		$interfaces = (array) $rc->getInterfaceNames();
		$traits = (array) $rc->getTraitNames();
		//--
		for($i=0; $i<Smart::array_size($interfaces); $i++) {
			$interfaces[$i] = (string) '\\'.ltrim((string)$interfaces[$i], '\\');
		} //end for
		for($i=0; $i<Smart::array_size($traits); $i++) {
			$traits[$i] = (string) '\\'.ltrim((string)$traits[$i], '\\');
		} //end for
		//--
		$parents = (array) $this->getClassParents($rc);
		//print_r($parents); die();
		$cparents = [];
		foreach($parents as $key => $val) {
			$fixname = (string) '\\'.ltrim((string)$key, '\\');
			$cparents[] = [
				'name' => (string) $fixname,
				'type' => (string) $val
			];
			$fixname = '';
		} //end foreach
		//--
		$comments = (array) $this->parseDocComment($rc->getDocComment()); // NO need to overwrite class doc comments with parent doc comments .. this is only for methods !!!
		//--
		$class = [
			'@definition' 		=> 'class.@',
			'type' 				=> '', // will be rewrite later
			'callmode' 			=> '', // will be rewrite later
			'class' 			=> (string) '\\'.ltrim((string)$rc->getName(), '\\'),
			'short-name' 		=> (string) $rc->getShortName(),
			'namespace' 		=> (string) ($rc->getNamespaceName() ? '\\'.trim((string)$rc->getNamespaceName(), '\\').'\\' : '\\'),
			'modifiers' 		=> (string) implode(' ', Reflection::getModifierNames($rc->getModifiers())),
			'parents' 			=> (array)  $cparents,
			'file-name' 		=> (string) $fname,
			'is-abstract' 		=> (bool)   $rc->isAbstract(),
			'is-final' 			=> (bool)   $rc->isFinal(),
			'extends' 			=> (string) ($pclass ? '\\'.ltrim((string)$pclass, '\\') : ''),
			'is-interface' 		=> (bool)   $rc->isInterface(),
			'interfaces' 		=> (array)  $interfaces,
			'is-trait' 			=> (bool)   $rc->isTrait(),
			'traits' 			=> (array)  $traits,
			'is-instantiable' 	=> (bool)   $rc->isInstantiable(),
			'constructor' 		=> (string) ($rc->getConstructor() ? $rc->getConstructor()->getName() : ''),
		//	'is-iterable' 		=> (bool)   $rc->isIterable(), // PHP 7.2+
			'is-anonymous' 		=> (bool)   $rc->isAnonymous(),
			'is-cloneable' 		=> (bool)   $rc->isCloneable(),
			'is-user-defined' 	=> (bool)   $rc->isUserDefined(),
			'is-internal-priv' 	=> (bool)   false, // will be rewrite later
			'doc-@comments' 	=> (array)  [], // will be rewrite later
			'doc-comments' 		=> (array)  $comments
		];
		//--
		$type = 'class';
		$callmode = '';
		if($class['is-abstract'] === true) {
			if((string)$class['constructor'] != '') {
				$callmode = '@->';
			} else {
				$callmode = '@::';
			} //end if
		} else {
			if((string)$class['constructor'] != '') {
				$callmode = '->';
			} else {
				$callmode = '::';
			} //end if
		} //end if
		if($class['is-interface'] === true) {
			$type = 'interface';
			$callmode = '^::';
		} elseif($class['is-trait'] === true) {
			$type = 'trait';
			$callmode = '^*';
		} //end if
		$class['type'] = (string) $type;
		$class['callmode'] = (string) $callmode;
		//-- {{{SYNC-PARSE-DOC-COMMENTS-ACCESS-INTERNAL}}}
		$is_internal_priv = false;
		if(Smart::array_size($comments) > 0) {
			if(Smart::array_size($comments['props']) > 0) {
				if(array_key_exists('internal', (array)$comments['props'])) {
					$is_internal_priv = true;
				} // end if
				if(Smart::array_size($comments['props']['access']) > 0) {
					if((string)trim((string)strtolower((string)$comments['props']['access']['type'])) == 'private') {
						$is_internal_priv = true;
					} //end if
				} //end if
			} //end if
		} //end if
		//-- # end sync
		$class['is-internal-priv'] = (bool) $is_internal_priv;
		//--
		$class['doc-@comments'] = [];
		if(Smart::array_size($comments['props']) > 0) {
			foreach($comments['props'] as $key => $val) {
				if(Smart::array_size($val) > 0) {
					switch((string)$key) {
						case 'package':
							$class['doc-@comments']['package'] = (string) trim($val['type'].' '.$val['var'].' '.$val['comment']);
							break;
						case 'version':
							$class['doc-@comments']['version'] = (string) trim($val['type'].' '.$val['var'].' '.$val['comment']);
							break;
						case 'depends':
							$class['doc-@comments']['depends'] = (string) trim($val['type'].' '.$val['var'].' '.$val['comment']);
							break;
						case 'hints':
							$class['doc-@comments']['hints'] = (string) trim($val['type'].' '.$val['var'].' '.$val['comment']);
							break;
						case 'usage':
							$class['doc-@comments']['usage'] = (string) trim($val['type'].' '.$val['var'].' '.$val['comment']);
							break;
						case 'throws':
							$class['doc-@comments']['throws'] = (string) trim($val['type'].' '.$val['var'].' '.$val['comment']);
							break;
					} //end switch
				} //end if
			} //end foreach
		} //end if
		//--
		$akeys = [
			'public' 				=> true,
			'abstract-public' 		=> true,
			'protected' 			=> true,
			'abstract-protected' 	=> true
		];
		//--
		$marr = (array) $this->parseClassMethods($rc);
		$methods = [];
		foreach($akeys as $key => $val) {
			if($val === true) {
				for($i=0; $i<Smart::array_size($marr[(string)$key]); $i++) {
					if((string)$marr[(string)$key][$i]['decl'] != '') {
						$marr[(string)$key][$i]['decl'] = (string) '\\'.ltrim((string)$marr[(string)$key][$i]['decl'], '\\');
					} //end if
					if((string)$marr[(string)$key][$i]['rdecl'] != '') {
						$marr[(string)$key][$i]['rdecl'] = (string) '\\'.ltrim((string)$marr[(string)$key][$i]['rdecl'], '\\');
					} //end if
					$methods[] = (array) $marr[(string)$key][$i];
				} //end for
			} //end if
		} //end foreach
		//--
		$parr = (array) $this->parseClassProperties($rc);
		$properties = [];
		foreach($akeys as $key => $val) {
			if($val === true) {
				for($i=0; $i<Smart::array_size($parr[(string)$key]); $i++) {
					if((string)$parr[(string)$key][$i]['decl'] != '') {
						$parr[(string)$key][$i]['decl'] = (string) '\\'.ltrim((string)$parr[(string)$key][$i]['decl'], '\\');
					} //end if
					$properties[] = (array) $parr[(string)$key][$i];
				} //end for
			} //end if
		} //end foreach
		//--
		$akeys['abstract-public'] = false; // N/A
		$akeys['abstract-protected'] = false; // N/A
		$carr = (array) $this->parseClassConstants($rc);
		$constants = [];
		foreach($akeys as $key => $val) {
			if($val === true) {
				for($i=0; $i<Smart::array_size($carr[(string)$key]); $i++) {
					if((string)$carr[(string)$key][$i]['decl'] != '') {
						$carr[(string)$key][$i]['decl'] = (string) '\\'.ltrim((string)$carr[(string)$key][$i]['decl'], '\\');
					} //end if
					$constants[] = (array) $carr[(string)$key][$i];
				} //end for
			} //end if
		} //end foreach
		//--
		return array(
			'@metainfo' 	=> (string) __FUNCTION__,
			'class' 		=> (array) $class,
			'constants' 	=> (array) $constants,
			'properties' 	=> (array) $properties,
			'methods' 		=> (array) $methods
		);
		//--
	} //END FUNCTION


	private function parseClassConstants($rc) {
		//--
		$constants = [
			'@metainfo' 			=> (string) __FUNCTION__,
			'public' 				=> [],
			'protected' 			=> []
		];
		//--
		if(!is_a($rc, 'ReflectionClass')) {
			return (array) $constants;
		} //end if
		//--
		$arr = $rc->getReflectionConstants();
		$class = (string) $rc->getName();
		//--
		if(Smart::array_size($arr) > 0) {
			//--
			for($i=0; $i<Smart::array_size($arr); $i++) {
				//--
				$property = $arr[$i]; // mixed
				//--
				if(is_a($property, 'ReflectionClassConstant')) {
					//--
					$visibility = '';
					if($property->isPublic()) {
						$visibility = 'public';
					} elseif($property->isProtected()) {
						$visibility = 'protected';
					} else {
						$visibility = ''; // private or unknown
					} //end if else
					//--
					if((string)$visibility != '') {
						//--
						$comments = (array) $this->parseDocComment($property->getDocComment());
						//-- {{{SYNC-PARSE-DOC-COMMENTS-ACCESS-INTERNAL}}}
						$is_internal_priv = false;
						if(Smart::array_size($comments) > 0) {
							if(Smart::array_size($comments['props']) > 0) {
								if(array_key_exists('ignore', (array)$comments['props'])) {
									$is_internal_priv = true;
								} // end if
							} //end if
						} //end if
						//-- #end sync
						$pval = (string) $this->prettyPrintMethodParam($property->getValue());
						//--
						$decl = (array) $property->getDeclaringClass();
						if((string)$decl['name'] != '') {
							if((string)$decl['name'] == (string)$class) {
								$decl = '';
							} else {
								$decl = (string) $decl['name'];
							} //end if
						} else {
							$decl = '';
						} //end if else
						//--
						$constants[(string)$visibility][] = [
							'@definition' 		=> 'class.constant',
							'decl' 				=> (string) $decl,
							'class' 			=> (string) $class, // $method->class,
							'name' 				=> (string) $property->getName(),
							'modifiers' 		=> (string) implode(' ', Reflection::getModifierNames($property->getModifiers())),
							'value' 			=> (string) $pval,
							'visibility' 		=> (string) $visibility,
							'is-public' 		=> (bool)   $property->isPublic(),
							'is-protected' 		=> (bool)   $property->isProtected(),
							'is-internal-priv' 	=> (bool)   $is_internal_priv,
							'doc-comments' 		=> (array)  $comments
						];
						//--
					} //end if
					//--
				} //end if
				//--
			} //end for
			//--
		} //end if
		//--
		return (array) $constants;
		//--
	} //END FUNCTION


	private function parseClassProperties($rc) {
		//--
		$props = [
			'@metainfo' 			=> (string) __FUNCTION__,
			'public' 				=> [],
			'protected' 			=> []
		];
		//--
		if(!is_a($rc, 'ReflectionClass')) {
			return (array) $props;
		} //end if
		//--
		$arr = $rc->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
		$class = (string) $rc->getName();
		//--
		if(Smart::array_size($arr) > 0) {
			//--
			for($i=0; $i<Smart::array_size($arr); $i++) {
				//--
				$property = $arr[$i]; // mixed
				//--
				if(is_a($property, 'ReflectionProperty')) {
					//--
					$visibility = '';
					if($property->isPublic()) {
						$visibility = 'public';
					} elseif($property->isProtected()) {
						$visibility = 'protected';
					} else {
						$visibility = ''; // private or unknown
					} //end if else
					//--
					if((string)$visibility != '') {
						//--
						$comments = (array) $this->parseDocComment($property->getDocComment());
						//-- {{{SYNC-PARSE-DOC-COMMENTS-ACCESS-INTERNAL}}}
						$is_internal_priv = false;
						if(Smart::array_size($comments) > 0) {
							if(Smart::array_size($comments['props']) > 0) {
								if(array_key_exists('ignore', (array)$comments['props'])) {
									$is_internal_priv = true;
								} // end if
							} //end if
						} //end if
						//-- #end sync
						$pval = '';
						/* On Private Properties this FAILS !!
						 * Also on Public Properties this is not well balanced at the moment ... by getting values only from Object Classes ... which is not safe as may call the destructor anyway !!
						 * In the case of static classes, the variables may be poluted with the result of previous execution !
						if($property->isStatic()) {
							$pval = (string) $this->prettyPrintMethodParam($property->getValue()); // if using with static class will get the actual value not default ... which is not OK
						} elseif($rc->isInstantiable()) {
							$pval = (string) $this->prettyPrintMethodParam($property->getValue($rc->newInstanceWithoutConstructor())); // still may call the destructor, is not safe
						} //end if else
						*/
						//--
						$decl = (array) $property->getDeclaringClass();
						if((string)$decl['name'] != '') {
							if((string)$decl['name'] == (string)$class) {
								$decl = '';
							} else {
								$decl = (string) $decl['name'];
							} //end if
						} else {
							$decl = '';
						} //end if else
						//--
						$props[(string)$visibility][] = [
							'@definition' 		=> 'class.property',
							'decl' 				=> (string) $decl,
							'class' 			=> (string) $class, // $method->class,
							'name' 				=> (string) $property->getName(),
							'modifiers' 		=> (string) implode(' ', Reflection::getModifierNames($property->getModifiers())),
							'value' 			=> (string) $pval,
							'visibility' 		=> (string) $visibility,
							'is-default' 		=> (bool)   $property->isDefault(),
							'is-static' 		=> (bool)   $property->isStatic(),
							'is-public' 		=> (bool)   $property->isPublic(),
							'is-protected' 		=> (bool)   $property->isProtected(),
							'is-internal-priv' 	=> (bool)   $is_internal_priv,
							'doc-comments' 		=> (array)  $comments
						];
						//--
					} //end if
					//--
				} //end if
				//--
			} //end for
			//--
		} //end if
		//--
		return (array) $props;
		//--
	} //END FUNCTION


	private function parseClassMethods($rc) {
		//--
		$methods = [
			'@metainfo' 			=> (string) __FUNCTION__,
			'abstract-public' 		=> [],
			'abstract-protected' 	=> [],
			'public' 				=> [],
			'protected' 			=> []
		];
		//--
		if(!is_a($rc, 'ReflectionClass')) {
			return (array) $methods;
		} //end if
		//--
		$arr = $rc->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);
		$class = (string) $rc->getName();
		//--
		if(Smart::array_size($arr) > 0) {
			//--
			$parents = (array) $this->getClassParents($rc);
			//--
			for($i=0; $i<Smart::array_size($arr); $i++) {
				//--
				$method = $arr[$i]; // mixed
				//--
				if(is_a($method, 'ReflectionMethod')) {
					//--
					$visibility = '';
					if($method->isAbstract()) {
						if($method->isPublic()) {
							$visibility = 'abstract-public';
						} elseif($method->isProtected()) {
							$visibility = 'abstract-protected';
						} else {
							$visibility = ''; // unknown
						} //end if else
					} else {
						if($method->isPublic()) {
							$visibility = 'public';
						} elseif($method->isProtected()) {
							$visibility = 'protected';
						} else {
							$visibility = ''; // private or unknown
						} //end if else
					} //end if
					//--
					if((string)$visibility != '') {
						//--
						$rdecl = '';
						$ruses = '';
						//--
						$comments = (array) $this->parseDocComment($method->getDocComment());
						//-- {{{SYNC-GET-DOC-FROM-PARENT-CLASSES}}}
						if(Smart::array_size($parents) > 0) {
							foreach($parents as $key => $val) {
								if(method_exists((string)$key, (string)$method->getName())) {
									$rcm = new ReflectionClass((string)$key);
									if(is_a($rcm, 'ReflectionClass')) {
										if($rcm->isInternal() !== true) {
											$rmeth = $rcm->getMethod((string)$method->getName());
											if(is_a($rmeth, 'ReflectionMethod')) {
												if((string)$val == 'trait') {
													$ruses = (string) $rcm->getName();
												} else {
													$rdecl = (string) $rcm->getName();
													$comments = (array) $this->parseDocComment($rmeth->getDocComment());
												} //end if else
												if($rmeth->isAbstract()) {
													break;
												} //end if
											} //end if
											$rmeth = null;
											/* do not stop on first abstract class because it may extend another abstract class !!!
											if((string)$val == 'class') {
												if($rcm->isAbstract()) {
													break;
												} //end if
											} //end if
											*/
										} //end if
									} //end if
									$rcm = null;
								} //end if
							} //end foreach
						} //end if
						//-- {{{SYNC-PARSE-DOC-COMMENTS-ACCESS-INTERNAL}}}
						$is_magic = false;
						$is_internal_priv = false;
						$returns = '';
						if(Smart::array_size($comments) > 0) {
							if(Smart::array_size($comments['props']) > 0) {
								if(array_key_exists('magic', (array)$comments['props'])) {
									$is_magic = true;
								} // end if
								if(array_key_exists('internal', (array)$comments['props'])) {
									$is_internal_priv = true;
								} // end if
								if(Smart::array_size($comments['props']['access']) > 0) {
									if((string)trim((string)strtolower((string)$comments['props']['access']['type'])) == 'private') {
										$is_internal_priv = true;
									} //end if
								} //end if
								if(Smart::array_size($comments['props']['return']) > 0) {
									$returns = (string) trim((string)$comments['props']['return']['type']);
								} elseif(Smart::array_size($comments['props']['returns']) > 0) {
									$returns = (string) trim((string)$comments['props']['returns']['type']);
								} //end if else
							} //end if
						} //end if
						//-- # end sync
						$params = $method->getParameters();
						$cparams = [];
						if(Smart::array_size($params) > 0) {
							//--
							for($j=0; $j<Smart::array_size($params); $j++) {
								$param = $params[$j];
								if(is_a($param, 'ReflectionParameter')) {
									$isparamoptional = $param->isOptional();
									$defavail = false;
									$defconst = false;
									$defval = '';
									if($isparamoptional) {
										$defavail = (bool) $param->isDefaultValueAvailable();
										$defconst = (bool) $param->isDefaultValueConstant();
										if($defconst) {
											$defval = (string) $param->getDefaultValueConstantName();
										} else {
											$defval = (string) $this->prettyPrintMethodParam($param->getDefaultValue());
										} //end if else
									} //end if
									$havetypeparam = $param->hasType();
									$deftype = '';
									if($havetypeparam) {
										$deftype = $param->getType();
									} //end if
									$cparams[] = [
										'@definition' 		=> 'class.method.param',
										'class' 			=> (string) $class, // $method->class,
										'method' 			=> (string) $method->getName(),
										'name' 				=> (string) '$'.$param->getName(),
										'position' 			=> (int)    $param->getPosition(),
										'can-pass-by-ref' 	=> (bool)   $param->isPassedByReference(),
										'can-pass-by-val' 	=> (bool)   $param->canBePassedByValue(),
										'is-optional' 		=> (bool)   $isparamoptional,
										'default-value' 	=> (string) $defval,
										'defval-allow-null' => (bool)   $param->allowsNull(),
										'defval-is-avail' 	=> (bool)   $defavail,
										'defval-is-const' 	=> (bool)   $defconst,
										'have-type' 		=> (bool)   $havetypeparam,
										'type' 				=> (string) $deftype,
										'def-type' 			=> (string) $deftype, // store default type ; the above type may be overwritten by doc type
										'is-type-array' 	=> (bool)   $param->isArray(),
										'is-callable' 		=> (bool)   $param->isCallable(),
										'is-variadic' 		=> (bool)   $param->isVariadic()
									];
								} //end if
								$param = null;
							} //end for
							//-- assign type from doc-comments if no type
							if(Smart::array_size($cparams) > 0) {
								if(Smart::array_size($comments) > 0) {
									if(Smart::array_size($comments['props']) > 0) {
										if(Smart::array_size($comments['props']['param']) > 0) {
											foreach($comments['props']['param'] as $key => $val) {
												for($p=0; $p<Smart::array_size($cparams); $p++) {
													if(is_array($val)) {
														if((string)$val['var'] == (string)$cparams[$p]['name']) {
															if((string)trim((string)$val['type']) != '') {
																if((string)trim((string)$cparams[$p]['type']) == '') {
																	$cparams[$p]['type'] = (string) trim((string)$val['type']);
																} //end if
															} //end if
															break;
														} //end if
													} //end if
												} //end for
											} //end foreach
										} //end if
									} //end if
								} //end if
							} //end if
							//--
						} //end if
						//--
						$decl = (array) $method->getDeclaringClass();
						if((string)$decl['name'] != '') {
							if((string)$decl['name'] == (string)$class) {
								$decl = '';
							} else {
								$decl = (string) $decl['name'];
							} //end if
						} else {
							$decl = '';
						} //end if else
						//--
						$methods[(string)$visibility][] = [
							'@definition' 		=> 'class.method',
							'decl' 				=> (string) $decl,
							'ruses' 			=> (string) $ruses,
							'rdecl' 			=> (string) $rdecl,
							'class' 			=> (string) $class, // $method->class,
							'name' 				=> (string) $method->getName(),
							'modifiers' 		=> (string) implode(' ', Reflection::getModifierNames($method->getModifiers())),
							'visibility' 		=> (string) $visibility,
							'is-special' 		=> (bool)   (strpos((string)$method->getName(), '__') === 0),
							'is-static' 		=> (bool)   $method->isStatic(),
							'is-final' 			=> (bool)   $method->isFinal(),
							'is-abstract' 		=> (bool)   $method->isAbstract(),
							'is-public' 		=> (bool)   $method->isPublic(),
							'is-protected' 		=> (bool)   $method->isProtected(),
							'is-constructor' 	=> (bool)   $method->isConstructor(),
							'is-destructor' 	=> (bool)   $method->isDestructor(),
							'is-magic' 			=> (bool)   $is_magic,
							'is-internal-priv' 	=> (bool)   $is_internal_priv,
							'returns' 			=> (string) $returns,
							'params' 			=> (array)  $cparams,
							'doc-comments' 		=> (array)  $comments
						];
						//--
					} //end if
					//--
				} //end if
				//--
			} //end for
			//--
		} //end if
		//--
		return (array) $methods;
		//--
	} //END FUNCTION


	private function getClassParents($rc) {
		//--
		if(!is_a($rc, 'ReflectionClass')) {
			return array();
		} //end if
		//--
		$pclass = $rc->getParentClass(); // mixed
		$interfaces = (array) $rc->getInterfaceNames();
		$traits = (array) $rc->getTraitNames();
		//--
		$parents = array();
		//--
		if(is_a($pclass, 'ReflectionClass')) {
			//--
			$parents[(string)$pclass->getName()] = 'class';
			//--
			$rcx = new ReflectionClass((string)$pclass->getName());
			if(is_a($rcx, 'ReflectionClass')) {
				$parents = (array) array_merge((array)$parents, (array)$this->getClassParents($rcx));
			} //end if
			$rcx = null;
			//--
		} //end if
		for($i=0; $i<Smart::array_size($interfaces); $i++) {
			//--
			$parents[(string)$interfaces[$i]] = 'interface';
			//--
			$rcx = new ReflectionClass((string)$interfaces[$i]);
			if(is_a($rcx, 'ReflectionClass')) {
				$parents = (array) array_merge((array)$parents, (array)$this->getClassParents($rcx));
			} //end if
			$rcx = null;
			//--
		} //end for
		for($i=0; $i<Smart::array_size($traits); $i++) {
			//--
			$parents[(string)$traits[$i]] = 'trait';
			//--
			$rcx = new ReflectionClass((string)$traits[$i]);
			if(is_a($rcx, 'ReflectionClass')) {
				$parents = (array) array_merge((array)$parents, (array)$this->getClassParents($rcx));
			} //end if
			$rcx = null;
			//--
		} //end for
		//--
		return (array) $parents;
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
		$regex_dc_code = '/\*\s+(\<code\>)(.*)(\<\/code\>)/s';
		$regex_dc_propx = '/^@([a-z]+)$/';
		$regex_dc_props = '/@([a-z]+)\s+([^\s]+)(\s+\$[a-zA-Z0-9_]+)?(.*)/';
		//--
		$matches = array();
		preg_match_all((string)$regex_dc_code, (string)$str, $matches, PREG_SET_ORDER); // OK to get code
		$arr_code = array();
		for($i=0; $i<Smart::array_size($matches); $i++) {
			if(Smart::array_size($matches[$i]) === 4) {
				$code = (string) $this->fixDocCommentCodePart($matches[$i][2]);
				if((string)$code != '') {
					$arr_code[] = (string) $code;
				} //end if
			} //end if
		} //end for
		//if(strpos($str, '<code>') !== false) { print_r($matches); print_r($arr_code); die('----- Code -----'); }
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
							if(((string)$name == 'param') OR ((string)$name == 'method')) { // parse magic methods
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


	private function prettyPrintMethodParam($defval) {
		//--
		if(is_array($defval)) {
			$arr = [];
			foreach($defval as $key => $val) {
				$arr[] = (string) $key.' => '.$this->prettyPrintMethodParam($val);
			} //end foreach
			$defval = (string) 'array('.implode(', ', (array)$arr).')';
		} elseif(is_object($defval)) {
			$defval = (string) 'object()';
		} else {
			$defval = (string) Smart::json_encode($defval, false, true, false);
		} //end if
		//--
		return $defval; // mixed
		//--
	} //END FUNCTION


	private function prettyPrintPhpCode($code) {
		//-- custom render settings
		$arr_highlight_custom = [ // background: #232B50
			'highlight.string' 	=> '#5F9CCE',
			'highlight.comment' => '#999999',
			'highlight.keyword' => '#FFCC00; font-weight: bold',
			'highlight.default' => '#FFFFFF',
			'highlight.html' 	=> '#57A64A'
		];
		//-- initialize defaults to store before changing
		$arr_highlight_default = [];
		//-- save defaults and set new / custom
		foreach($arr_highlight_custom as $key => $val) {
			$arr_highlight_default[(string)$key] = (string) @ini_get((string)$key); // get defaults from INI to restore later
			@ini_set((string)$key, $val); // set custom to INI for custom render
		} //end for
		//-- render
		$code = (string) highlight_string((string)'<'.'?php'."\n".SmartUtils::comment_php_code($code, [])."\n".'?'.'>', true);
		$code = (new SmartHtmlParser((string)$code, true, true, false))->get_clean_html(false); // fix XHTML Tags and deliver clean HTML
		//-- restore render settings to INI
		foreach($arr_highlight_default as $key => $val) {
			@ini_set((string)$key, $val);
		} //end for
		//--
		return (string) $code;
		//--
	} //END FUNCTION


} //END CLASS


/**
 * Index Area Controller
 * @version 20191106
 * @package Application
 */
final class SmartAppIndexController extends SmartAbstractAppController {

	// this is just for the purpose of documentation of Smart.Framework as this controller only serves ADMIN area

	public function Initialize() {} // re-implement for documentation purposes

	public function Run() { // re-implement for documentation purposes
		//--
		return 503;
		//--
	} //END FUNCTION

	public function ShutDown() {} // re-implement for documentation purposes

} //END CLASS


//end of php code
?>