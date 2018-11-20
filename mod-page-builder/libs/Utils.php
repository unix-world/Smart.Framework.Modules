<?php
// Class: \SmartModExtLib\PageBuilder\Utils
// (c) 2006-2018 unix-world.org - all rights reserved
// Author: Radu Ovidiu I.

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
 * @version 	v.181120
 * @package 	PageBuilder
 *
 */
final class Utils {

	// ::


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


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>