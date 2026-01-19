<?php
// Class: Mod PDF Generate :: PDF Create
// [Smart.Framework.Modules - PdfGenerate / PDF Create]
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModExtLib\PdfGenerate;

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
 * Class: \SmartModExtLib\PdfGenerate\PDFCreate - Open Source PHP class for creating PDF documents
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP Date, PHP Pcre ; classes: Smart, \PDF\zFPDF and \PDF\zFPDF\zTTFontFile
 * @version 	v.20260120
 * @package 	modules:PDF-Generate
 *
 */
final class PDFCreate {

	// ::

	private static bool $initialized = false;


	public static function init() : void {
		//--
		if(self::$initialized === true) {
			return;
		} //end if
		//--
		\spl_autoload_register(function(string $classname) : void {
			//--
			$classname = (string) $classname;
			//--
			if(\strpos((string)$classname, '\\') === false) { // if have namespace
				return;
			} //end if
			//--
			if(
				((string)\substr((string)$classname, 0, 10) !== 'PDF\\zFPDF\\')
			) {
				return;
			} //end if
			//--
			$path = 'modules/mod-pdf-generate/libs/pdf-create/'.\str_replace([ '\\', "\0" ], [ '/', '' ], (string)$classname);
			//--
			if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', (string)$path)) {
				return; // invalid path characters in path
			} //end if
			//--
			if(!\is_file((string)$path.'.php')) {
				return; // file does not exists
			} //end if
			//--
			require_once((string)$path.'.php');
			//--
		}, true, false); // throw / append
		//--
		self::$initialized = true;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
