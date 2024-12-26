<?php
// Class: \SmartModExtLib\PdfGenerate\HtmlToPdfExport
// [Smart.Framework.Modules - PdfGenerate / HTML2PDF Export]
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModExtLib\PdfGenerate;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// PDF Export - using HTMLDoc and RSvg
// DEPENDS:
//		* Smart::
//		* SmartUtils::
//		* SmartFileSysUtils::
//		* SmartHtmlParser->
// DEPENDS-EXT: HTMLDOC Executable 1.8.x (external) ; Rsvg-Convert Executable 2.x
// 		tested with htmldoc-1.8.24 / htmldoc-1.8.25 / htmldoc-1.8.26 / htmldoc-1.8.27 ; rsvg-convert-2.40.20
//======================================================

// [REGEX-SAFE-OK]

/* Config settings required for this library:
define('SMART_HTMLTOPDF_HTMLDOC_BIN_PATH', 		'/usr/local/bin/htmldoc'); 			// path to HtmlDoc Utility (change to match your system) ; can be `/usr/bin/htmldoc` or `/usr/local/bin/htmldoc` or `c:/open_runtime/htmldoc/htmldoc.exe` or any custom path
define('SMART_HTMLTOPDF_RSVG_BIN_PATH', 		'/usr/local/bin/rsvg-convert');		// path to RsvgConvert Utility (used to convert SVG to PNG)
define('SMART_HTMLTOPDF_DOCUMENT_FORMAT', 		'pdf13'); 							// PDF format: `pdf14` | `pdf13` | `pdf12`
define('SMART_HTMLTOPDF_DOCUMENT_MODE', 		'color'); 							// PDF mode: `color` | `gray`
*/

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: HtmlToPdfExport - Exports HTML Code to PDF Document using HTMLDoc and RSvg.
 * This is a very safe but limited PDF export of HTML code.
 *
 * It does support most of the HTML5 and CSS3.
 * It does not support Javascript.
 * It does support images: PNG, GIF, JPG as link or base64 embedded.
 * It does support SVG vector images as link or base64 embedded (requires RSvg to convert them to PNG format).
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	executables: HTMLDoc ; classes: Smart, SmartUtils, SmartFileSysUtils, SmartHtmlParser
 * @version 	v.20231005
 * @package 	modules:PDF-Generate
 *
 */
final class HtmlToPdfExport {

	// ::


	//=====================================================================
	/**
	 * Check if HTMLDoc exists and is set correctly
	 *
	 * @return '' OR '/path/to/htmldoc.exe'
	 */
	public static function is_active() {
		//--
		$out = '';
		//--
		if((\defined('\\SMART_HTMLTOPDF_HTMLDOC_BIN_PATH')) AND ((string)\SMART_HTMLTOPDF_HTMLDOC_BIN_PATH != '')) {
			if(\SmartFileSystem::have_access_executable((string)\SMART_HTMLTOPDF_HTMLDOC_BIN_PATH)) {
				$out = (string) \SMART_HTMLTOPDF_HTMLDOC_BIN_PATH;
			} //end if
		} //end if
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Check if HTMLDoc exists and is set correctly
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return '' OR '/path/to/rsvg-convert.exe'
	 */
	public static function is_rsvg_active() {
		//--
		$out = '';
		//--
		if((\defined('\\SMART_HTMLTOPDF_RSVG_BIN_PATH')) AND ((string)\SMART_HTMLTOPDF_RSVG_BIN_PATH != '')) {
			if(\SmartFileSystem::have_access_executable((string)\SMART_HTMLTOPDF_RSVG_BIN_PATH)) {
				$out = (string) \SMART_HTMLTOPDF_RSVG_BIN_PATH;
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
	 * @param STRING $y_html_content				:: The HTML Code
	 * @param ENUM $y_orientation					:: Page Orientation: 'normal' | 'wide'
	 * @param ENUM $y_allow_set_credentials 		:: DEFAULT IS SET to 'no' ; set this value to 'yes' must be set just for internal URLs as this can be a security issue ; if set to 'auto' (this is safe) will auto detect if can trust based on admin.php / index.php local framework scripts
	 *
	 * @returns STRING 								:: The PDF Document Contents
	 *
	 */
	public static function generate($y_html_content, $y_orientation='normal', $y_allow_set_credentials='no') {
		//--
		$pdfdata = '';
		//--
		$htmldoc = (string) self::is_active();
		$rsvg = (string) self::is_rsvg_active();
		//--
		if((string)$htmldoc != '') {
			//--
			if((string)$y_orientation == 'wide') {
				$orientation = self::tag_page_wide();
			} else {
				$orientation = self::tag_page_normal();
			} //end if else
			//--
			$tmp_prefix_dir = 'tmp/cache/pdf#export/';
			$protect_file = (string) $tmp_prefix_dir.'.htaccess';
			$dir = (string) $tmp_prefix_dir.\SMART_FRAMEWORK_SESSION_PREFIX.'/'; // we use different for index / admin / @
			//--
			$uniquifier = (string) \SmartUtils::unique_auth_client_private_key().\SMART_APP_VISITOR_COOKIE;
			$the_dir = (string) $dir.\strtolower(\Smart::safe_varname(\Smart::uuid_10_seq().'_'.\Smart::uuid_10_num().'_'.\SmartHashCrypto::sha1($uniquifier))).'/'; // from camelcase to lower
			//--
			$tmp_uuid = (string) \Smart::uuid_45($uniquifier).\Smart::uuid_36($uniquifier);
			$file = (string) $the_dir.'__document_'.\SmartHashCrypto::sha256('@@PDF#File::Cache@@'.$tmp_uuid).'.html' ;
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
			//-- process the code
			$y_html_content = (string) self::remove_between_tags((string)$y_html_content);
			$y_html_content = (string) self::safe_charset((string)$y_html_content);
			//-- extract images
			$arr_imgs = (array) (new \SmartHtmlParser((string)$y_html_content))->get_tags('img');
			//--
			$chk_duplicates_arr = array();
			//--
			for($i=0; $i<\Smart::array_size($arr_imgs); $i++) {
				//--
				$tmp_img_src = (string) \trim((string)$arr_imgs[$i]['src']);
				//--
				$chk_duplicates_arr[(string)$tmp_img_src] = $chk_duplicates_arr[(string)$tmp_img_src] ?? null;
				//--
				if(!$chk_duplicates_arr[(string)$tmp_img_src]) {
					//--
					$tmp_fcontent = '';
					$tmp_fake_fname = '';
					$tmp_img_ext = ''; // extension
					//--
					$tmp_getimg_arr = (array) \SmartRobot::load_url_img_content((string)$tmp_img_src, (string)$y_allow_set_credentials);
					if($tmp_getimg_arr['result'] == 1) {
						$tmp_fcontent = (string) $tmp_getimg_arr['content'];
						$tmp_fake_fname = (string) $tmp_getimg_arr['filename'];
						$tmp_img_ext = (string) $tmp_getimg_arr['extension'];
					} //end if
					//--
					if(\SmartEnvironment::ifDebug()) { // if debug, append information to log
						\SmartFileSystem::write($logfile, '=========='."\n".'========== [FILE # '.$i.' = \''.$tmp_img_src.'\']'."\n\n".'=== [GUESS EXTENSION] :: '.$tmp_img_ext."\n\n".'=== LOG: '.$tmp_getimg_arr['log']."\n\n".'=========='."\n\n\n\n", 'a');
					} //end if
					//--
					$tmp_getimg_arr = null;
					//--
					if(((string)$tmp_fcontent != '') AND ((string)$tmp_fake_fname != '')) {
						//-- not SVG !
						if(((string)$tmp_img_ext == '.png') OR ((string)$tmp_img_ext == '.gif') OR ((string)$tmp_img_ext == '.jpg')) {
							//-- !! customize !!
							$tmp_fname = (string) 'pdf_img_'.\SmartHashCrypto::sha256('@@PDF#File::Cache::IMG@@'.'#'.$i.'@'.$tmp_img_src.'//'.$tmp_uuid);
							$y_html_content = (string) \str_replace('src="'.$tmp_img_src.'"', 'src="'.$tmp_fname.$tmp_img_ext.'"', (string)$y_html_content);
							\SmartFileSystem::write($the_dir.$tmp_fname.$tmp_img_ext, $tmp_fcontent);
							//--
						} elseif((string)$tmp_img_ext == '.svg') {
							//-- SVG not supported !
							if((string)$rsvg != '') {
								$tmp_fname = (string) 'pdf_img_'.\SmartHashCrypto::sha256('@@PDF#File::Cache::IMG@@'.'#'.$i.'@'.$tmp_img_src.'//'.$tmp_uuid);
								\SmartFileSystem::write($the_dir.$tmp_fname.$tmp_img_ext, $tmp_fcontent);
								$svg2png_options = (string) self::svg2png_options($the_dir.$tmp_fname.$tmp_img_ext);
								if((string)$svg2png_options != '') {
									@\exec($rsvg.' '.$svg2png_options);
								} //end if
								if(\SmartFileSystem::is_type_file($the_dir.$tmp_fname.$tmp_img_ext.'.png')) {
									$y_html_content = (string) \str_replace('src="'.$tmp_img_src.'"', 'src="'.$tmp_fname.$tmp_img_ext.'.png'.'"', (string)$y_html_content);
								} else { // get rid of SVG images
									$y_html_content = (string) \str_replace('src="'.$tmp_img_src.'"', 'src=""', (string)$y_html_content);
								} //end if else
							} else { // ignore SVG images
									$y_html_content = (string) \str_replace('src="'.$tmp_img_src.'"', 'src="'.$tmp_fname.$tmp_img_ext.'"', (string)$y_html_content);
							} //end if else
							//--
						} else {
							//--
							$tmp_img_ext = '.png';
							$tmp_fname = 'img-unknown.png';
							$tmp_fname = (string) 'pdf_img-unknown_'.\SmartHashCrypto::sha256('@@PDF#File::Cache::IMG@@'.'#'.$i.'@'.$tmp_img_src.'//'.$tmp_uuid);
							$y_html_content = (string) \str_replace('src="'.$tmp_img_src.'"', 'src="'.$tmp_fname.$tmp_img_ext.'"', (string)$y_html_content);
							\SmartFileSystem::write($the_dir.$tmp_fname.$tmp_img_ext, \SmartFileSystem::read('modules/mod-pdf-generate/views/img/pdfexportimg-unknown.png'));
							//--
						} //end if else
						//--
					} else {
						//--
						$tmp_img_ext = '.png';
						$tmp_fname = 'img-fail.png';
						$tmp_fname = (string) 'pdf_img-fail_'.\SmartHashCrypto::sha256('@@PDF#File::Cache::IMG@@'.'#'.$i.'@'.$tmp_img_src.'//'.$tmp_uuid);
						$y_html_content = (string) \str_replace('src="'.$tmp_img_src.'"', 'src="'.$tmp_fname.$tmp_img_ext.'"', (string)$y_html_content);
						\SmartFileSystem::write($the_dir.$tmp_fname.$tmp_img_ext, \SmartFileSystem::read('modules/mod-pdf-generate/views/img/pdfexportimg-fail.png'));
						//--
					} //end if
					//--
				} //end if
				//--
				$chk_duplicates_arr[(string)$tmp_img_src] = true;
				//--
			} //end for
			//--
			$chk_duplicates_arr = array();
			$arr_imgs = array();
			//--
			$y_html_content = (string) $orientation."\n".$y_html_content;
			//--
			$y_html_content = (string) \str_replace(['<hr ','<hr>'], ['<hr size="1" noshade ', '<hr size="1" noshade>'], (string)$y_html_content);
			$y_html_content = (string) (new \SmartHtmlParser((string)$y_html_content, true, 'any:required:tidy', false))->get_clean_html(); // security fix: cleanup HTML to avoid security issues with the old HTMLDoc
			if(\stripos($y_html_content, '<html') === false) {
				$y_html_content = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>PDF</title></head><body>'.$y_html_content.'</body></html>';
			} //end if
			//--
			\SmartFileSystem::write($file, $y_html_content);
			//--
			if(\SmartFileSystem::is_type_file($file)) {
				//--
				$pdf_options = (string) self::pdf_options($file);
				if((string)$pdf_options != '') {
					if((int)strlen($htmldoc.' '.$pdf_options) <= (int)PHP_MAXPATHLEN) {
						\ob_start();
						@\passthru($htmldoc.' '.$pdf_options);
						$pdfdata = (string) \ob_get_clean();
					} else {
						\Smart::log_warning(__CLASS__.' # ERROR: PDF Generator command is too long: `'.$htmldoc.' '.$pdf_options.'`');
					} //end if else
				} else {
					\Smart::log_warning(__CLASS__.' # ERROR: PDF Generator detected Invalid Options for the PDF Document: '.$file);
					if(\SmartEnvironment::ifDebug()) {
						\Smart::log_notice(__CLASS__.' # ERROR: PDF Generator HTML Document: '.$y_html_content);
					} //end if
				} //end if else
				//--
			} else {
				//--
				\Smart::log_warning(__CLASS__.' # ERROR: PDF Generator Failed to find the PDF Document: '.$file);
				if(\SmartEnvironment::ifDebug()) {
					\Smart::log_notice(__CLASS__.' # ERROR: PDF Generator HTML Document: '.$y_html_content);
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
	 * Set an HTML Comment TAG END (all code between START/END like these tags will be removed)
	 *
	 * @return HTML Comment
	 */
	public static function tag_remove_start() {
		//--
		return '<!-- PDF REMOVE START -->';
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Set an HTML Comment TAG START (all code between START/END like these tags will be removed)
	 *
	 * @return HTML Comment
	 */
	public static function tag_remove_end() {
		//--
		return '<!-- PDF REMOVE END -->';
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Set an HTML Comment TAG HTMLDoc PageBreak
	 *
	 * @return HTML Comment
	 */
	public static function tag_page_break() {
		//--
		return '<!-- PAGE BREAK -->';
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Set an HTML Comment TAG HTMLDoc Page SIZE
	 *
	 * @return HTML Comment
	 */
	public static function tag_page_size() {
		//--
		return '<!-- MEDIA SIZE 215x279mm -->'; // A4
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Set an HTML Comment TAG HTMLDoc Page PORTRAIT
	 *
	 * @return HTML Comment
	 */
	public static function tag_page_normal() {
		//--
		return self::tag_page_size().'<!-- MEDIA LANDSCAPE NO -->';
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Set an HTML Comment TAG HTMLDoc Page LANDSCAPE
	 *
	 * @return HTML Comment
	 */
	public static function tag_page_wide() {
		//--
		return self::tag_page_size().'<!-- MEDIA LANDSCAPE YES -->';
		//--
	} //END FUNCTION
	//=====================================================================


	//===== PRIVATES


	//=====================================================================
	/**
	 * Remove all HTML Code between PDF-REMOVE Tags
	 *
	 * @param HTML Code $y_html
	 * @return HTML Code
	 */
	private static function remove_between_tags($y_html) {
		//--
		return (string) \preg_replace("'".self::tag_remove_start().".*?".self::tag_remove_end()."'si", '&nbsp;', (string)$y_html); // insensitive
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Replace HTML type accents in UTF-8 encoded strings, and then DE-ACCENT
	 * Replace accented UTF-8 characters like by unaccented ASCII-7 equivalents
	 *
	 * @param STRING $string
	 * @return STRING
	 */
	private static function safe_charset($html) {
		//--
		return (string) \SmartUnicode::html_entities((string)$html, '', true); // detect charset ; force ISO-8859-1
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Return HTMLDoc Options, Safe, Shell Escaped
	 *
	 * @param STRING $y_html_file 	:: HTMLDoc Input File as Relative Path: path/to/file.html
	 * @return STRING
	 */
	private static function pdf_options($y_html_file) {
		//--
		$y_html_file = (string) \trim((string)$y_html_file);
		if((string)$y_html_file == '') {
			return '';
		} //end if
		//--
		if((\defined('\\SMART_HTMLTOPDF_DOCUMENT_MODE')) AND ((string)\strtolower((string)\SMART_HTMLTOPDF_DOCUMENT_MODE == 'gray'))) {
			$pdf_color = '--gray';
		} else {
			$pdf_color = '--color';
		} //end if else
		//--
		if(\defined('\\SMART_HTMLTOPDF_DOCUMENT_FORMAT')) {
			switch((string)\strtolower((string)\SMART_HTMLTOPDF_DOCUMENT_FORMAT)) {
				case 'pdf14':
					$pdf_ver = 'pdf14';
					break;
				case 'pdf12':
					$pdf_ver = 'pdf12';
					break;
				case 'pdf13':
				default:
					$pdf_ver = 'pdf13';
			} //end switch
		} else {
			$pdf_ver = 'pdf13';
		} //end if else
		//--
		$y_html_file = (string) \trim((string)\str_replace('"', '', (string)$y_html_file)); // replace out "
		//-- executable convert options as FILE/STDIN output
		return (string) '--quiet '.$pdf_color.' --format '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($pdf_ver).' --charset ISO-8859-1 --browserwidth 900 --embedfonts --permissions no-modify,no-annotate,no-copy --encryption --no-links --no-strict --no-toc --jpeg=100 --compression 5 --numbered --fontspacing 1.2 --textfont Helvetica --fontsize 9.0 --headfootfont Helvetica --headfootsize 7.0 --header "..." --footer "../" --continuous --left 10mm --right 10mm --top 7mm --bottom 7mm --pagelayout single --pagemode document '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($y_html_file);
		//--
	} //END FUNCTION
	//=====================================================================


	//=====================================================================
	/**
	 * Return RsvgConvert Options, Safe, Shell Escaped
	 *
	 * @param STRING $y_svg_file 	:: RsvgConvert Input File as Relative Path: path/to/file.svg
	 * @return STRING
	 */
	private static function svg2png_options($y_svg_file) {
		//--
		$y_svg_file = (string) \trim((string)$y_svg_file);
		if((string)$y_svg_file == '') {
			return '';
		} //end if
		//--
		return (string) '--zoom=1 --keep-aspect-ratio '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($y_svg_file).' --output '.\SmartModExtLib\PdfGenerate\PdfUtils::escape_arg_cmd($y_svg_file.'.png');
		//--
	} //END FUNCTION
	//=====================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
