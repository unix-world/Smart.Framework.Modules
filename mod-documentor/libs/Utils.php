<?php
// Class: \SmartModExtLib\Documentor\Utils
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

namespace SmartModExtLib\Documentor;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


/**
 * Class: Documentor Utils
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20250107
 * @package 	Documentor
 *
 */
final class Utils {

	// ::

	private static $modulePath = 'modules/mod-documentor/';


	public static function checkRequiredConstants() {
		//--
		if(!\defined('\\SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO')) {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO not defined.';
		} //end if
		if((string)\trim((string)\SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO) == '') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO is defined but is empty.';
		} //end if
		if((string)\substr((string)\SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO, -4, 4) != '.svg') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO must be a SVG image `.svg`.';
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)\SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO)) {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO path is unsafe.';
		} //end if
		if(!\SmartFileSystem::is_type_file((string)\SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO)) {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO path is not a file.';
		} //end if
		//--
		if(!\defined('\\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS')) {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS not defined.';
		} //end if
		if((string)\trim((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS) == '') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS is defined but is empty.';
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS)) {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS path is unsafe.';
		} //end if
		if((string)\substr((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS, -1, 1) != '/') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS must end with a slash `/`.';
		} //end if
		if((string)\substr((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS, 0, 15) != 'tmp/documentor-') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS must start with `tmp/documentor-`.';
		} //end if
		//--
		if(!\defined('\\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS')) {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS not defined.';
		} //end if
		if((string)\trim((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS) == '') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS is defined but is empty.';
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS)) {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS path is unsafe.';
		} //end if
		if((string)\substr((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS, -1, 1) != '/') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS must end with a slash `/`.';
		} //end if
		if((string)\substr((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS, 0, 15) != 'tmp/documentor-') {
			return 'Documentor SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS must start with `tmp/documentor-`.';
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	public static function cleanupDocumentationDirectory() {
		//--
		$test = self::checkRequiredConstants();
		if($test !== true) {
			return 'ERROR: '.$test;
		} //end if
		//--
		if(\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS)) {
			\SmartFileSystem::dir_delete((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS);
			if(\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS)) {
				return 'Documentation Packages directory cannot be cleared';
			} //end if
		} //end if
		//--
		if(\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS)) {
			\SmartFileSystem::dir_delete((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS);
			if(\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS)) {
				return 'Documentation directory cannot be cleared';
			} //end if
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	public static function indexDocumentationPackages($language, $heading, $mode, $extra, $js_path) {
		//--
		$test = self::checkRequiredConstants();
		if($test !== true) {
			return 'ERROR: '.$test;
		} //end if
		//--
		$language = (string) \trim((string)$language);
		if((string)$language == '') {
			return 'A required parameter is empty: Language';
		} //end if
		//--
		$heading = (string) \trim((string)$heading);
		if((string)$heading == '') {
			return 'A required parameter is empty: Heading';
		} //end if
		//--
		if(!\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS)) {
			return 'Documentation directory not found';
		} //end if
		if(!\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS)) {
			return 'Documentation Packages directory not found';
		} //end if
		//--
		$arr_packages = [];
		$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS, false, false, '.json'); // non-recuring, no dot files, only JSON files
		for($i=0; $i<\Smart::array_size($files_n_dirs['list-files']); $i++) {
			//--
			$tmp_json = (string) \SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS.$files_n_dirs['list-files'][$i];
			if(\SmartFileSystem::is_type_file($tmp_json)) {
				$tmp_json = (string) \SmartFileSystem::read($tmp_json);
			} else {
				$tmp_json = '';
			} //end if
			//--
			if((string)\trim((string)$tmp_json) == '') {
				return 'Documentation Package NOT FOUND: '.$files_n_dirs['list-files'][$i];
			} //end if
			//--
			$tmp_json = \Smart::json_decode((string)$tmp_json); // mixed
			//--
			if(\Smart::array_size($tmp_json) <= 0) {
				return 'Documentation Package is INVALID: '.$files_n_dirs['list-files'][$i];
			} else {
				$tmp_json['package'] = (string) \trim((string)$tmp_json['package']);
				$tmp_json['name'] = (string) \trim((string)$tmp_json['name']);
				$tmp_json['type'] = (string) \trim((string)$tmp_json['type']);
				if((string)$tmp_json['package'] == '') {
					$tmp_json['package'] = '@No-Package'; // {{{SYNC-DOCUMENTOR-EMPTY-PACKAGE}}}
				} //end if
				$tmp_json['file'] = (string) \Smart::base_name((string)$files_n_dirs['list-files'][$i], '.json');
				if((!isset($arr_packages[(string)$tmp_json['package']])) OR (!\is_array($arr_packages[(string)$tmp_json['package']]))) {
					$arr_packages[(string)$tmp_json['package']] = [];
				} //end if
				$arr_packages[(string)$tmp_json['package']][] = (array) $tmp_json;
			} //end if
			$tmp_json = null;
		} //end for
		$files_n_dirs = null; // free mem
		//--
		\ksort($arr_packages);
		// \print_r($arr_packages); die();
		if(\Smart::array_size($arr_packages) <= 0) {
			return 'Documentation Packages is Empty';
		} //end if
		//--
		$main = (string) \SmartMarkersTemplating::render_file_template(
			(string) self::$modulePath.'views/packages.mtpl.inc.htm',
			[
				'js-path' 		=> (string) $js_path,
				'language' 		=> (string) $language,
				'packages' 		=> (array)  $arr_packages,
				'generated-on' 	=> (string) \date('Y-m-d H:i:s O')
			]
		);
		//-- {{{SYNC-DOCUMENTOR-SAVE-MODE}}}
		$url_index = '';
		$url_fonts = '';
		$url_img   = '';
		$url_limg  = '';
		$extdir    = '';
		if((string)$mode == 'multi') {
			if((string)$extra != '') {
				if((string)$extra == '@') {
					$url_fonts 	= 'fonts/';
					$url_img 	= 'img/sf-logo.svg';
					if((string)$language == 'php') {
						$url_limg 	= 'img/php-logo.svg';
					} elseif(((string)$language == 'js') OR ((string)$language == 'javascript')) {
						$url_limg 	= 'img/javascript-logo.svg';
					} //end if else
				} else {
					$url_fonts 	= '../fonts/';
					$url_index = '../index.html';
					$extdir 	= (string) \SmartFileSysUtils::addPathTrailingSlash((string)\Smart::safe_filename((string)$extra));
					$url_img 	= '../img/sf-logo.svg';
					if((string)$language == 'php') {
						$url_limg 	= '../img/php-logo.svg';
					} elseif(((string)$language == 'js') OR ((string)$language == 'javascript')) {
						$url_limg 	= '../img/javascript-logo.svg';
					} //end if else
				} //end if else
			} //end if
		} //end if else
		//-- #end sync
		$doc = (string) \SmartMarkersTemplating::render_file_template(
			(string) self::$modulePath.'templates/template-documentor.htm',
			(array)  \SmartComponents::set_app_template_conform_metavars([
				//--
				'fonts-path' 		=> (string) $url_fonts,
				'logo-img' 			=> (string) $url_img,
				'lang-img' 			=> (string) $url_limg,
				'year' 				=> (string) \date('Y'),
				//--
				'title' 			=> (string) \strtoupper((string)$language).' Documentation',
				'heading-title' 	=> (string) $heading,
				'seo-description' 	=> (string) \SmartUtils::extract_description($main),
				'seo-keywords' 		=> (string) \SmartUtils::extract_keywords($main),
				'seo-summary' 		=> (string) \SmartUtils::extract_title($heading),
				'main' 				=> (string) $main,
				'url-index' 		=> (string) $url_index
				//--
			])
		);
		//--
		$dir = (string) \SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.$extdir;
		//--
		if(\SmartFileSystem::is_type_file($dir.'index.html')) {
			\SmartFileSystem::delete($dir.'index.html');
			if(\SmartFileSystem::is_type_file($dir.'index.html')) {
				return 'Cannot delete Documentation Packages Index file';
			} //end if
		} //end if
		if(!\SmartFileSystem::write($dir.'index.html', (string)$doc)) {
			return 'Cannot save Documentation Packages Index file';
		} //end if
		if(!\SmartFileSystem::is_type_file($dir.'index.html')) {
			return 'Cannot find Documentation Packages Index file';
		} //end if
		//--
		if(\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS)) {
			\SmartFileSystem::dir_delete((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS);
			if(\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS)) {
				return 'Documentation Packages directory cannot be cleared';
			} //end if
		} //end if
		//--
		if((string)$extra != '') {
			//--
			if(\SmartFileSystem::dir_copy((string)self::$modulePath.'fonts/', (string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'fonts/', true) != 1) {
				return 'Failed to copy font files to Documentation font directory';
			} //end if
			if(!\SmartFileSystem::is_type_file((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'fonts/index.html')) {
				return 'Cannot find fonts directory Index file for Documentation';
			} //end if
			//--
			if((string)$js_path != '') {
				if(\SmartFileSystem::dir_copy('lib/js/jsselect/', (string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'js/', true) != 1) {
					return 'Failed to copy files to Documentation js directory';
				} //end if
				\SmartFileSystem::dir_delete((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'js/demo/');
				\SmartFileSystem::dir_delete((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'js/docs/');
				if(!\SmartFileSystem::is_type_file((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'js/index.html')) {
					return 'Cannot find js directory Index file for Documentation';
				} //end if
			} //end if
			//--
			if(!\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/')) {
				\SmartFileSystem::dir_create((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/', true);
				if(!\SmartFileSystem::is_type_dir((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/')) {
					return 'Failed to create img directory into Documentation directory';
				} //end if
			} //end if
			if(!\SmartFileSystem::write((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/index.html', '')) {
				return 'Cannot create img directory Index file for Documentation';
			} //end if
			if(!\SmartFileSystem::is_type_file((string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/index.html')) {
				return 'Cannot find img directory Index file for Documentation';
			} //end if
			if(!\SmartFileSystem::copy((string)\SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO, (string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/'.\SmartFileSysUtils::extractPathFileName((string)\SMART_FRAMEWORK_DOCUMENTOR_IMG_LOGO), false, true)) {
				return 'Failed to copy font img Logo to Documentation img directory';
			} //end if

			if(!\SmartFileSystem::copy('lib/framework/img/php-logo.svg', (string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/php-logo.svg', false, true)) {
				return 'Failed to copy font img PhpLogo to Documentation img directory';
			} //end if
			if(!\SmartFileSystem::copy('lib/framework/img/javascript-logo.svg', (string)\SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.'img/javascript-logo.svg', false, true)) {
				return 'Failed to copy font img JsLogo to Documentation img directory';
			} //end if

			//--
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	public static function indexDocumentationDocument($language, $package, $type, $cls, $main, $heading, $mode, $extra) {
		//--
		$test = self::checkRequiredConstants();
		if($test !== true) {
			return 'ERROR: '.$test;
		} //end if
		//--
		$language = (string) \trim((string)$language);
		if((string)$language == '') {
			return 'A required parameter is empty: Language';
		} //end if
		//--
		$package = (string) \trim((string)$package);
		if((string)$package == '') {
			$package = '@No-Package'; // {{{SYNC-DOCUMENTOR-EMPTY-PACKAGE}}}
		} //end if
		//--
		$type = (string) \trim((string)$type);
		if((string)$type == '') {
			$type = 'unknown'; // {{{SYNC-DOCUMENTOR-EMPTY-OBJTYPE}}}
		} //end if
		//--
		if((string)\trim((string)$main) == '') {
			return 'A required parameter is empty: Main (part)';
		} //end if
		$heading = (string) \trim((string)$heading);
		if((string)$heading == '') {
			return 'A required parameter is empty: Heading';
		} //end if
		//-- {{{SYNC-DOCUMENTOR-SAVE-MODE}}}
		$url_index = '';
		$url_fonts = '';
		$url_img   = '';
		$url_limg  = '';
		$extdir    = '';
		if((string)$mode == 'multi') {
			$url_index = 'index.html#Package--'.\Smart::create_htmid((string)$package).'-';
			if((string)$extra != '') {
				if((string)$extra == '@') {
					$url_fonts 	= 'fonts/';
					$url_img 	= 'img/sf-logo.svg';
					if((string)$language == 'php') {
						$url_limg 	= 'img/php-logo.svg';
					} elseif(((string)$language == 'js') OR ((string)$language == 'javascript')) {
						$url_limg 	= 'img/javascript-logo.svg';
					} //end if else
				} else {
					$extdir 	= (string) \SmartFileSysUtils::addPathTrailingSlash((string)\Smart::safe_filename((string)$extra));
					$url_fonts 	= '../fonts/';
					$url_img 	= '../img/sf-logo.svg';
					if((string)$language == 'php') {
						$url_limg 	= '../img/php-logo.svg';
					} elseif(((string)$language == 'js') OR ((string)$language == 'javascript')) {
						$url_limg 	= '../img/javascript-logo.svg';
					} //end if else
				} //end if else
			} //end if
		} //end if else
		//--
		if((string)$extdir != '') {
			if(!\SmartFileSysUtils::checkIfSafePath((string)$extdir)) {
				return 'Unsafe Extra Dir: '.$extdir;
			} //end if
		} //end if
		//-- #end sync
		$doc = (string) \SmartMarkersTemplating::render_file_template(
			(string) self::$modulePath.'templates/template-documentor.htm',
			(array)  \SmartComponents::set_app_template_conform_metavars([
				//--
				'fonts-path' 		=> (string) $url_fonts,
				'logo-img' 			=> (string) $url_img,
				'lang-img' 			=> (string) $url_limg,
				'year' 				=> (string) \date('Y'),
				//--
				'title' 			=> (string) \strtoupper((string)$language).' Documentation for: '.$cls,
				'heading-title' 	=> (string) $heading,
				'seo-description' 	=> (string) \SmartUtils::extract_description($cls.' '.$main),
				'seo-keywords' 		=> (string) \SmartUtils::extract_keywords($cls.' '.$main),
				'seo-summary' 		=> (string) \SmartUtils::extract_title($heading.': '.$cls),
				'main' 				=> (string) $main,
				'url-index' 		=> (string) $url_index
				//--
			])
		);
		//--
		$slug = (string) \Smart::safe_filename($type.'@'.\Smart::create_slug((string)str_replace('$', 's', (string)$cls)).'.html'); // support also $ for Javascript classes
		//--
		$dir = (string) \SMART_FRAMEWORK_DOCUMENTOR_DIR_DOCS.$extdir;
		//--
		if(!\SmartFileSystem::is_type_dir($dir)) {
			\SmartFileSystem::dir_create($dir, true);
			if(!\SmartFileSystem::is_type_dir($dir)) {
				return 'Cannot create Documentation directory for: `'.$cls.'` as: '.$dir;
			} //end if
		} //end if
		if(\SmartFileSystem::is_type_file($dir.$slug)) {
			\SmartFileSystem::delete($dir.$slug);
			if(\SmartFileSystem::is_type_file($dir.$slug)) {
				return 'Cannot delete Documentation file for: `'.$cls.'` as: '.$dir.$slug;
			} //end if
		} //end if
		if(!\SmartFileSystem::write($dir.$slug, $doc)) {
			return 'Cannot save Documentation file for: `'.$cls.'` as: '.$dir.$slug;
		} //end if
		if(!\SmartFileSystem::is_type_file($dir.$slug)) {
			return 'Cannot find Documentation file for: `'.$cls.'` as: '.$dir.$slug;
		} //end if
		//--
		$xdir = (string) \SMART_FRAMEWORK_DOCUMENTOR_DIR_PKGS;
		if(!\SmartFileSystem::is_type_dir($xdir)) {
			\SmartFileSystem::dir_create($xdir, true);
			if(!\SmartFileSystem::is_type_dir($xdir)) {
				return 'Cannot create Documentation Packages directory for: `'.$cls.'` as: '.$xdir;
			} //end if
		} //end if
		if(!\SmartFileSystem::write($xdir.$slug.'.json', (string)\Smart::json_encode([ 'package' => (string)$package, 'name' => (string)$cls, 'type' => (string)$type ]))) {
			return 'Cannot save Documentation Package file for: `'.$cls.'` as: '.$xdir.$slug.'.json';
		} //end if
		if(!\SmartFileSystem::is_type_file($xdir.$slug.'.json')) {
			return 'Cannot find Documentation Package file for: `'.$cls.'` as: '.$xdir.$slug.'.json';
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
