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
 * @version 	v.20190323
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


	public static function parseFodsXmlSpreadSheetToArray($input_str) {
		//--
		$input_str = (string) trim((string)$input_str);
		if((string)$input_str == '') {
			return array();
		} //end if
		//--
		if(stripos($input_str, '<?xml ') !== 0) {
			return array();
		} //end if
		//-- FIX: Line Break
		$input_str = (string) str_ireplace(['<text:line-break/>'], "\n", $input_str);
		//-- FIX: Many Spaces
		$regex = '\<text\:s text\:c\="([0-9]+)"\/\>';
		$input_str = (string) preg_replace_callback(
			(string) '/'.$regex.'/i',
			function($matches) use ($val) {
				$matches[1] = (int) $matches[1];
				if($matches[1] < 0) {
					$matches[1] = 0;
				} elseif($matches[1]>1000) {
					$matches[1] = 1000;
				} //end if
				$spaces = '';
				if($matches[1] > 0) {
					for($i=0; $i<$matches[1]; $i++) {
						$spaces .= ' ';
					} //end for
				} //end if
				return (string) $spaces;
			}, //end function
			$input_str
		);
		//-- #END FIX
		$csv_arr = (new \SmartXmlParser('domxml'))->transform($input_str);
		$input_str = ''; // free mem
		//print_r($csv_arr); die();
		if(\Smart::array_size($csv_arr) <= 0) {
			return array();
		} //end if
		if(\Smart::array_size($csv_arr['office:body']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['office:body'];
		if(\Smart::array_size($csv_arr['office:spreadsheet']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['office:spreadsheet'];
		if(\Smart::array_size($csv_arr['table:table']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['table:table'];
		if(\Smart::array_size($csv_arr['table:table-row']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['table:table-row'];
		//print_r($csv_arr); die();
		if(\Smart::array_size($csv_arr) <= 0) {
			return array();
		} //end if
		//--
		$hdr_arr = array();
		$data_arr = array();
		//--
		$cnt_csv_arr = (int) \Smart::array_size($csv_arr);
		//--
		for($l=0; $l<$cnt_csv_arr; $l++) {
			//--
			$val = $csv_arr[$l]['table:table-cell'];
			//--
			if(\Smart::array_type_test($val) == 1) {
				//--
				for($i=0; $i<\Smart::array_size($val); $i++) {
					if(is_array($val[$i]['text:p'])) {
						for($p=0; $p<\Smart::array_size($val[$i]['text:p']); $p++) {
							if(is_array($val[$i]['text:p'][$p])) {
								$val[$i]['text:p'][$p] = '';
							} //end if
						} //end for
						$val[$i]['text:p'] = (string) implode("\n", $val[$i]['text:p']);
					} //end if
					if($l > 0) {
						$data_arr[(string)$hdr_arr[$i]][] = (string) $val[$i]['text:p'];
					} else {
						$hdr_arr[] = (string) substr((string)$val[$i]['text:p'], 6, 2);
					} //end if else
				} //end for
				//--
			} elseif(\Smart::array_size($hdr_arr) > 0) {
				//--
				if(is_array($val['text:p'])) {
					for($p=0; $p<\Smart::array_size($val['text:p']); $p++) {
						if(is_array($val['text:p'][$p])) {
							$val['text:p'][$p] = '';
						} //end if
					} //end for
					$val['text:p'] = (string) implode("\n", $val['text:p']);
				} //end if
				//--
				$data_arr[(string)$hdr_arr[0]][] = (string) $val['text:p'];
				//--
				if(is_array($val['@attributes'])) {
					if((int)$val['@attributes']['number-columns-repeated'] > 1) {
						for($i=1; $i<$val['@attributes']['number-columns-repeated']; $i++) {
							$data_arr[(string)$hdr_arr[$i]][] = (string) $val['text:p'];
						} //end for
					} //end if
				} //end if
				//--
			} //end if else
			//--
		} //end for
		//--
		return array(
			'header' 	=> (array) $hdr_arr,
			'data' 		=> (array) $data_arr
		);
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