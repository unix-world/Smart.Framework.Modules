<?php
// Class: \SmartModExtLib\Html2markdown\SmartHTML2Markdown
// (c) 2006-2021 unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

namespace SmartModExtLib\Html2markdown;

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
 * Class: Smart HTML2Markdown : A HTML5 to Markdown v2 Converter
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20220918
 * @package 	Html2markdown
 *
 */
final class SmartHTML2Markdown {

	// ::


	public static function convert(?string $html, array $options=[]) : string {
		//--
		if((string)\trim((string)$html) == '') {
			return '';
		} //end if
		//--
		if(\Smart::array_size($options) <= 0) {
			$options = [];
		} //end if
		//--
		return (string) (new \HTML2Markdown\HtmlConverter((array)$options))->convert((string)$html);
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//--
/**
 *
 * @access 		private
 * @internal
 *
 */
function autoload__HTML2Markdown_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(\strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)\substr((string)$classname, 0, 14) !== 'HTML2Markdown\\') { // if class name is not starting with HTML2Markdown
		return;
	} //end if
	//--
	$path = 'modules/mod-html2markdown/libs/'.\str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
	//--
	if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in path
	} //end if
	//--
	if(!\is_file($path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once($path.'.php');
	//--
} //END FUNCTION
//--
\spl_autoload_register('\\SmartModExtLib\\Html2markdown\\autoload__HTML2Markdown_SFM', true, false); // throw / append
//--


// end of php code
