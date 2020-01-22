<?php
// Class: \SmartModExtLib\Vanilla\CSSShrinkMinifier
// [Smart.Framework.Modules - Vanilla / CSS Shrink Minifier]
// (c) 2006-2019 unix-world.org - all rights reserved

namespace SmartModExtLib\Vanilla;


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
 * CSSShrink
 * A very basic CSS Minifier
 *
 * <code>
 *
 * $out = (string) \SmartModExtLib\Vanilla\CSSShrinkMinifier::minifyCssCode($css);
 *
 * </code>
 *
 * @package Minify
 * @author iradu@unix-world.org
 * @license BSD License
 */

final class CSSShrinkMinifier {

	// ->
	// v.181225


	public static function minifyCssCode($input) {
		//--
		if((string)trim((string)$input) == '') {
			return '';
		} //end if
		//--
		$regex = [
			(string) APPCODE_REGEX_STRIP_MULTILINE_CSS_COMMENTS => ' ', // remove multi-line comments (this is OK for CSS)
			"`^([\t\s]+)`ism" => '', // remove line start spaces
			"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "\n" // normalize line endings
		];
		//--
		$output = (string) preg_replace(array_keys($regex), array_values($regex), (string)$input);
		//--
		return (string) trim((string)$output);
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
