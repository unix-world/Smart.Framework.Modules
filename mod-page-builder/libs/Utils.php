<?php
// Class: \SmartModExtLib\PageBuilder\Utils
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

namespace SmartModExtLib\PageBuilder;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: PageBuilder Utils
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20190521
 * @package 	PageBuilder
 *
 */
final class Utils {

	// ::

	const REGEX_PLACEHOLDERS 	= '/\{\{\:[A-Z0-9_\-\.]+\:\}\}/';
	const REGEX_MARKERS 		= '/\{\{\=\#[A-Z0-9_\-\.]+(\|[a-z0-9]+)*\#\=\}\}/';


	public static function getDbType() {
		//--
		$type = '';
		//--
		if(defined('SMART_PAGEBUILDER_DB_TYPE')) {
			if((string)SMART_PAGEBUILDER_DB_TYPE == 'sqlite') {
				$type = 'sqlite';
			} elseif((string)SMART_PAGEBUILDER_DB_TYPE == 'pgsql') {
				$type = 'pgsql';
			} //end if
		} //end if
		//--
		return (string) $type;
		//--
	} //END FUNCTION


	public static function allowPages() {
		//--
		$allow = true;
		//--
		if(defined('SMART_PAGEBUILDER_DISABLE_PAGES')) {
			if(SMART_PAGEBUILDER_DISABLE_PAGES === true) {
				$allow = false;
			} //end if
		} //end if
		//--
		return (bool) $allow;
		//--
	} //END FUNCTION


	public static function getAvailableLayouts() {
		//--
		$layouts = [];
		//--
		$layouts[''] = 'DEFAULT';
		//--
		$available_layouts = \Smart::get_from_config('pagebuilder.layouts');
		$cnt_available_layouts = (int) \Smart::array_size($available_layouts);
		if($cnt_available_layouts > 0) {
			if(\Smart::array_type_test($available_layouts) == 1) { // non-associative
				for($i=0; $i<$cnt_available_layouts; $i++) {
					$available_layouts[$i] = (string) trim((string)$available_layouts[$i]);
					if((string)$available_layouts[$i] != '') {
						if(\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$available_layouts[$i])) {
							$layouts[(string)$available_layouts[$i]] = (string) $available_layouts[$i];
						} //end if
					} //end if
				} //end for
			} //end if
		} //end if
		//--
		return (array) $layouts;
		//--
	} //END FUNCTION


	public static function fixSafeCode($y_html) {
		//--
		$y_html = (string) $y_html;
		//--
		$y_html = \SmartUtils::comment_php_code($y_html); // avoid PHP code
		$y_html = str_replace([' />', '/>'], ['>', '>'], $y_html); // cleanup XHTML tag style
		//--
		return (string) $y_html;
		//--
	} //END FUNCTION


	public static function renderMarkdown($markdown_code) {
		//--
		return (string) self::fixSafeCode((new \SmartMarkdownToHTML(true, true, true, false))->text((string)$markdown_code)); // Breaks=1,Markup=0,Links=1,Entities=1
		//--
	} //END FUNCTION


	public static function composePluginClassName($str) {
		//--
		$arr = (array) explode('-', (string)$str);
		//--
		$class = '';
		//--
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			//--
			$arr[$i] = (string) trim((string)$arr[$i]);
			//--
			if((string)$arr[$i] != '') {
				//--
				$arr[$i] = (string) \Smart::safe_varname((string)$arr[$i]);
				//--
				if((string)$arr[$i] != '') {
					$class .= (string) ucfirst((string)$arr[$i]);
				} //end if
				//--
			} //end if
			//--
		} //end for
		//--
		return (string) $class;
		//--
	} //END FUNCTION


	public static function comparePlaceholdersAndMarkers($original_str, $transl_str) {
		//--
		$arr_placeholder_diffs 	= (array) self::comparePlaceholders($original_str, $transl_str);
		$arr_marker_diffs 		= (array) self::compareMarkers($original_str, $transl_str);
		//--
		return (array) array_merge((array)$arr_placeholder_diffs, (array)$arr_marker_diffs);
		//--
	} //END FUNCTION


	public static function comparePlaceholders($original_str, $transl_str) {
		//--
		$original_arr 	= (array) self::extractPlaceholders((string)$original_str);
		$transl_arr 	= (array) self::extractPlaceholders((string)$transl_str);
		//--
		return (array) array_diff($original_arr, $transl_arr);
		//--
	} //END FUNCTION


	public static function compareMarkers($original_str, $transl_str) {
		//--
		$original_arr 	= (array) self::extractMarkers((string)$original_str);
		$transl_arr 	= (array) self::extractMarkers((string)$transl_str);
		//--
		return (array) array_diff($original_arr, $transl_arr);
		//--
	} //END FUNCTION


	//#####


	private static function extractPlaceholders($str) {
		//--
		$re = (string) self::REGEX_PLACEHOLDERS;
		//--
		preg_match_all((string)$re, (string)$str, $matches);
		$arr = (array) \Smart::array_sort((array)$matches[0], 'natcasesort');
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	private static function extractMarkers($str) {
		//--
		$re = (string) self::REGEX_MARKERS;
		//--
		preg_match_all((string)$re, (string)$str, $matches);
		$arr = (array) \Smart::array_sort((array)$matches[0], 'natcasesort');
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>