<?php
// Class: Mod PDF Create :: Module
// [Smart.Framework.Modules - PdfCreate / Module]
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModExtLib\PdfCreate;

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
 * Class: \SmartModExtLib\PdfCreate\Module - Open Source PHP class for creating PDF documents
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP Date, PHP Pcre ; classes: Smart, \PDF\zFPDF and \PDF\zFPDF\zTTFontFile
 * @version 	v.20260130
 * @package 	modules:PDF-Create
 *
 */
final class Module {

	// ::

	private static bool $initialized = false;


	public static function newPdf(bool $useFontCaching=false, string $orientation='P', string $unit='mm', string $size='A4', ?string $uxmFontsPath=null) : \PDF\zFPDF\zFPDF { // {{{SYNC-ZFPDF-CONSTRUCT-PARAMS}}}
		//--
		self::init();
		//--
		return (new \PDF\zFPDF\zFPDF((bool)$useFontCaching, (string)$orientation, (string)$unit, (string)$size, $uxmFontsPath));
		//--
	} //END FUNCTION


	public static function init() : void {
		//--
		if(self::$initialized === true) {
			return;
		} //end if
		//--
		\spl_autoload_register(function(string $classname) : void {
			//--
			if(\strpos((string)$classname, '\\') === false) { // if have namespace
				return;
			} //end if
			//--
			if(\str_starts_with((string)$classname, 'PDF\\zFPDF\\') !== true) { // class name must start with PDF\zFPDF\
				return;
			} //end if
			//--
			$path = (string) \SmartFileSysUtils::getSmartFsRootPath().'modules/mod-pdf-create/libs/classes/'.\str_replace([ '\\', "\0" ], [ '/', '' ], (string)$classname);
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
