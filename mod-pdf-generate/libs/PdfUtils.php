<?php
// Class: \SmartModExtLib\PdfGenerate\PdfUtils
// [Smart.Framework.Modules - PdfGenerate / PDF Utils]
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModExtLib\PdfGenerate;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: PdfUtils - Provide various utility functions for exporting / generating PDFs.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartUtils, SmartFileSysUtils
 * @version 	v.20200121
 * @package 	modules:PDF-Generate
 *
 */
final class PdfUtils {

	// ::


	//=====================================================================
	/**
	 * Get the PDF Document Mime Type Header Data
	 *
	 * @return STRING		'application/pdf'
	 */
	public static function pdf_mime_header() {
		//--
		return (string) 'application/pdf';
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Get the PDF Document FileName Header Data
	 *
	 * @param STRING 	$y_filename		:: The PDF Document file name: default is: file.pdf
	 * @param ENUM 		$y_disp 		:: The content disposition, default is: inline ; can be also: attachment
	 *
	 * @return STRING		'inline; filename="somedoc.pdf"' or 'attachment; filename="somedoc.pdf"'
	 *
	 */
	public static function pdf_disposition_header($y_filename='file.pdf', $y_disp='inline') {
		//--
		switch((string)$y_disp) {
			case 'attachment':
				$y_disp = 'attachment';
				break;
			case 'inline':
			default:
				$y_disp = 'inline';
		} //end switch
		//--
		return (string) $y_disp.'; filename="'.\Smart::safe_filename($y_filename).'"';
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Escape shell command arguments
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return STRING
	 */
	public static function escape_arg_cmd($arg) {
		//--
		$arg = (string) \trim((string)\Smart::normalize_spaces((string)$arg));
		//--
		return (string) \escapeshellarg((string)$arg);
		//--
	} //END FUNCTION
	//=====================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
