<?php
// Class: \SmartModExtLib\PdfGenerate\HtmlUrlToPdfExport
// [Smart.Framework.Modules - PdfGenerate / HTML URL to PDF Export]
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModExtLib\PdfGenerate;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// PDF Export - using WkHtmlToPdf
// DEPENDS:
//		* Smart::
//		* SmartUtils::
//		* SmartFileSysUtils::
// DEPENDS-EXT: WkHtmlToPdf Executable 0.12.x or later (external)
// 		tested with wkhtmltopdf-0.12
//======================================================

// [REGEX-SAFE-OK]

/* Config settings required for this library:
define('SMART_HTMLTOPDF_WKHTMLTOPDF_BIN_PATH', 	'/usr/local/bin/wkhtmltopdf'); 		// path to WkHtmlToPdf Utility (change to match your system) ; can be `/usr/bin/wkhtmltopdf` or `/usr/local/bin/wkhtmltopdf` or `c:/open_runtime/wkhtmltopdf/wkhtmltopdf.exe` or any custom path
define('SMART_HTMLTOPDF_DOCUMENT_MODE', 		'color'); 							// PDF mode: `color` | `gray`
*/

// TODO: for using with 2FA can send Bearer Token or an Auth Token

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: HtmlToPdfExport - Exports HTML Code to PDF Document using WkHtmlToPdf.
 * This is an usafe but reach featured PDF export of HTML URL
 * It is based on a headless webkit
 *
 * It does support full HTML5, CSS3 and Javascript.
 * It does support images: PNG, GIF, JPG, SVG as link or base64 embedded
 * It does not support HTML5 canvas
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	executables: WkHtmlToPdf ; classes: Smart, SmartUtils, SmartFileSysUtils
 * @version 	v.20221220
 * @package 	modules:PDF-Generate
 *
 */
final class HtmlUrlToPdfExport {

	// ::


	//=====================================================================
	/**
	 * Check if WkHtmlToPdf exists and is set correctly
	 *
	 * @return '' OR '/path/to/wkhtmltopdf.exe'
	 */
	public static function is_active() {
		//--
		$out = '';
		//--
		if((\defined('\\SMART_HTMLTOPDF_WKHTMLTOPDF_BIN_PATH')) AND ((string)\SMART_HTMLTOPDF_WKHTMLTOPDF_BIN_PATH != '')) {
			if(\SmartFileSystem::have_access_executable((string)\SMART_HTMLTOPDF_WKHTMLTOPDF_BIN_PATH)) {
				$out = (string) \SMART_HTMLTOPDF_WKHTMLTOPDF_BIN_PATH;
			} //end if
		} //end if
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Generate a PDF Document on the fly from a piece of HTML code.
	 *
	 * Notice: this is using a secured cache folder, unique per visitor ID
	 *
	 * @param STRING $y_html_url					:: The URL to the HTML Page
	 * @param ENUM $y_orientation					:: Page Orientation: 'normal' | 'wide'
	 * @param BOOLEAN $y_credentials 				:: The credentials to send ; Default is empty (will send no credentials) ; To send autnetication credentials for the loaded URL you must set them explicit (as this is a very big security issue if you send credentials for non-trusted URLs) ; to explicit send credentials set this parameter as [ 'username' => 'some-username', 'password' => 'the password ...' ] ; to clear understand what is happening behind you must understand that this executable is a non-trusted headless browser so sending authentication credentials is not recommended at all, except maybe if you use this with a trusted url from a trusted environment such as internal networks
	 *
	 * @returns STRING 								:: The PDF Document Contents
	 *
	 */
	public static function generate($y_html_url, $y_orientation='normal', $y_timeout=3, $y_credentials=[]) {
		//--
		$pdfdata = '';
		//--
		$wkhtmltopdf = (string) self::is_active();
		//--
		if((string)$wkhtmltopdf != '') {
			//--
			$tmp_prefix_dir = 'tmp/cache/pdf#export/';
			$protect_file = (string) $tmp_prefix_dir.'.htaccess';
			$dir = (string) $tmp_prefix_dir.\SMART_FRAMEWORK_SESSION_PREFIX.'/'; // we use different for index / admin / @
			//--
			$uniquifier = (string) \SmartUtils::unique_auth_client_private_key().\SMART_APP_VISITOR_COOKIE;
			$the_dir = (string) $dir.\strtolower(\Smart::safe_varname(\Smart::uuid_10_seq().'_'.\Smart::uuid_10_num().'_'.\SmartHashCrypto::sha1($uniquifier))).'/'; // from camelcase to lower
			//--
			$tmp_uuid = (string) \Smart::uuid_45($uniquifier).\Smart::uuid_36($uniquifier);
			$file = (string) $the_dir.'__document_'.\SmartHashCrypto::sha256('@@PDF#File::Cache@@'.$tmp_uuid).'.pdf' ;
			$logfile = (string) $the_dir.'__headers_'.\SmartHashCrypto::sha256('@@PDF#File::Cache@@'.$tmp_uuid).'.log';
			//--
			if(\SmartFileSystem::is_type_dir($the_dir)) {
				\SmartFileSystem::dir_delete($the_dir);
			} //end if
			//--
			if(!\SmartFileSystem::is_type_dir($the_dir)) {
				\SmartFileSystem::dir_create($the_dir, true); // recursive
			} // end if
			//--
			\SmartFileSystem::write_if_not_exists($protect_file, \trim((string)\SMART_FRAMEWORK_HTACCESS_FORBIDDEN)."\n", 'yes');
			//--
			if(!\SmartFileSystem::is_type_file($file)) {
				//--
				$pdf_options = (string) self::pdf_options((string)$file, (string)$y_orientation, (int)$y_timeout, (string)$y_html_url, (array)$y_credentials);
				if((string)$pdf_options != '') {
					if((int)strlen($wkhtmltopdf.' '.$pdf_options) <= (int)PHP_MAXPATHLEN) {
						$arr_output = array();
						@\exec($wkhtmltopdf.' '.$pdf_options, $arr_output, $result_code);
						if($result_code === 0) {
							if(\SmartFileSystem::is_type_file($file)) {
								$pdfdata = (string) \SmartFileSystem::read($file);
							} else {
								\Smart::log_warning(__CLASS__.' # ERROR: PDF Generator command failed to find the PDF Document: '.$file);
							} //end if
						} else {
							if(\SmartEnvironment::ifDebug()) {
								\Smart::log_notice(__CLASS__.' # ERROR: PDF Generator command failed with code ['.$result_code.']: `'.$wkhtmltopdf.' '.$pdf_options.'`'."\n".print_r($arr_output,1));
							} //end if
						} //end if
					} else {
						\Smart::log_warning(__CLASS__.' # ERROR: PDF Generator command is too long: `'.$wkhtmltopdf.' '.$pdf_options.'`');
					} //end if else
				} else {
					\Smart::log_warning(__CLASS__.' # ERROR: PDF Generator detected Invalid Options for the PDF Document: '.$file);
					if(\SmartEnvironment::ifDebug()) {
						\Smart::log_notice(__CLASS__.' # ERROR: PDF Generator HTML URL: '.$y_html_url);
					} //end if
				} //end if else
				//--
			} else {
				//--
				\Smart::log_warning(__CLASS__.' # ERROR: PDF Generator Found the PDF Document before generation: '.$file);
				if(\SmartEnvironment::ifDebug()) {
					\Smart::log_notice(__CLASS__.' # ERROR: PDF Generator HTML URL: '.$y_html_url);
				} //end if
				//--
			} //end if else
			//-- cleanup
			if(!\SmartEnvironment::ifDebug()) { // if not debug, cleanup the dir
				if(\SmartFileSystem::is_type_dir($the_dir)) {
					\SmartFileSystem::dir_delete($the_dir);
				} //end if
			} //end if
			//--
		} else {
			//--
			\Smart::log_notice(__CLASS__.' # NOTICE: PDF Generator is INACTIVE ...');
			//--
		} //end if
		//--
		return (string) $pdfdata;
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Return WkHtmlToPdf Options, Safe, Shell Escaped
	 *
	 * @param STRING $y_html_url 	:: The HTML URL to Load
	 * @param STRING $y_pdf_file 	:: WkHtmlToPdf Output File as Relative Path: path/to/file.pdf
	 * @return STRING
	 */
	private static function pdf_options($y_pdf_file, $y_orientation, $y_timeout, $y_html_url, $y_credentials=[]) {
		//--
		$y_timeout = (int) $y_timeout;
		if((int)$y_timeout < 1) {
			$y_timeout = 1;
		} elseif((int)$y_timeout > 30) {
			$y_timeout = 30;
		} //end if
		//--
		if((string)$y_orientation == 'wide') {
			$orientation = 'Landscape';
		} else {
			$orientation = 'Portrait';
		} //end if else
		//--
		$y_pdf_file = (string) \trim((string)$y_pdf_file);
		if((string)$y_pdf_file == '') {
			return '';
		} //end if
		//--
		if((\defined('\\SMART_HTMLTOPDF_DOCUMENT_MODE')) AND ((string)\strtolower((string)\SMART_HTMLTOPDF_DOCUMENT_MODE == 'gray'))) {
			$pdf_color = '--grayscale';
		} else {
			$pdf_color = '';
		} //end if else
		//--
		$url_credentials = '';
		if(\Smart::array_size($y_credentials) > 0) {
			if(((string)$y_credentials['username'] != '') AND ((string)$y_credentials['password'] != '')) {
				$url_credentials = '--username '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($y_credentials['username']).' --password '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($y_credentials['password']);
			} //end if
		} //end if
		//--
		$y_pdf_file = (string) \trim((string)\str_replace('"', '', (string)$y_pdf_file)); // replace out "
		//-- executable convert options as FILE/STDIN output
		return (string) '--quiet '.$pdf_color.' --disable-plugins --encoding UTF-8 --no-print-media-type --dpi 96 --viewport-size 1024x768 --no-collate --page-size A4 --orientation '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($orientation).' --disable-local-file-access --disable-external-links --disable-internal-links --disable-forms --enable-javascript --javascript-delay '.(int)((int)$y_timeout * 1000).' --stop-slow-scripts --no-debug-javascript --no-outline --image-quality 90 '.$url_credentials.' '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($y_html_url).' '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($y_pdf_file);
		//--
	} //END FUNCTION
	//=====================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
