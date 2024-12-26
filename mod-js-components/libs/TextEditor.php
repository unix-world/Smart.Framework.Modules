<?php
// Class: \SmartModExtLib\JsComponents\TextEditor
// [Smart.Framework.Modules - JsComponents / Text Editor]
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\JsComponents;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: TextEditor (JS Component)
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	javascript: CKEditor
 * @version 	v.20200121
 * @package 	modules:ViewComponents
 *
 */
class TextEditor {

	// ::


//================================================================
/**
 * Outputs the HTML Code to init the Text Editor component
 *
 * @param $y_filebrowser_link STRING 		URL to Image Browser (Example: script.php?op=image-gallery&type=images)
 *
 * @return STRING							[HTML Code]
 */
public static function html_jsload_texteditarea($y_filebrowser_link='') {
	//--
	return \SmartMarkersTemplating::render_file_template(
		'modules/mod-js-components/libs/templates/text-editor-init.inc.htm',
		[
			'LANG' => (string) \SmartTextTranslations::getLanguage(),
			'FILE-BROWSER-CALLBACK-URL' => (string) $y_filebrowser_link
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draw a TextArea with the javascript Text Editor component
 *
 * @param STRING $yid					[Unique HTML Page Element ID]
 * @param STRING $yvarname				[HTML Form Variable Name]
 * @param STRING $yvalue				[HTML Data]
 * @param INTEGER+ $ywidth				[Area Width: (Example) 720px or 75%]
 * @param INTEGER+ $yheight				[Area Height (Example) 480px or 50%]
 * @param BOOLEAN $y_allow_scripts		[Allow JavaScripts]
 * @param BOOLEAN $y_allow_script_src	[Allow JavaScript SRC attribute]
 * @param MIXED $y_cleaner_deftags 		['' or array of HTML Tags to be allowed / dissalowed by the cleaner ... see HTML Cleaner Documentation]
 * @param ENUM $y_cleaner_mode 			[HTML Cleaner mode for defined tags: ALLOW / DISALLOW]
 * @param STRING $y_toolbar_ctrls		[Toolbar Controls: ... see CKEditor Documentation]
 *
 * @return STRING						[HTML Code]
 *
 */
public static function html_js_texteditarea($yid, $yvarname, $yvalue='', $ywidth='720px', $yheight='480px', $y_allow_scripts=false, $y_allow_script_src=false, $y_cleaner_deftags='', $y_cleaner_mode='', $y_toolbar_ctrls='') {
	//--
	return \SmartMarkersTemplating::render_file_template(
		'modules/mod-js-components/libs/templates/text-editor-draw.inc.htm',
		[
			'TXT-AREA-ID' 					=> (string) $yid, 				// HTML or JS ID
			'TXT-AREA-VAR-NAME' 			=> (string) $yvarname, 			// HTML variable name
			'TXT-AREA-WIDTH' 				=> (string) $ywidth, 			// 100px or 100%
			'TXT-AREA-HEIGHT' 				=> (string) $yheight, 			// 100px or 100%
			'TXT-AREA-CONTENT' 				=> (string) $yvalue,
			'TXT-AREA-ALLOW-SCRIPTS' 		=> (bool) $y_allow_scripts, 	// boolean
			'TXT-AREA-ALLOW-SCRIPT-SRC' 	=> (bool) $y_allow_script_src, 	// boolean
			'CLEANER-REMOVE-TAGS' 			=> $y_cleaner_deftags, 			// mixed, will be encoded as json
			'CLEANER-MODE-TAGS' 			=> (string) $y_cleaner_mode,
			'TXT-AREA-TOOLBAR' 				=> (string) $y_toolbar_ctrls
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Returns the HTML / Javascript code for CallBack Mapping for Text Editor component - FileBrowser Integration
 *
 * @param STRING $yurl					The Callback URL
 * @param BOOLEAN $is_popup 			Set to True if Popup (incl. Modal)
 *
 * @return STRING						[JS Code]
 */
public static function html_js_texteditarea_fm_callback($yurl, $is_popup=false) {
	//--
	return (string) \str_replace(["\r\n", "\r", "\n", "\t"], [' ', ' ', ' ', ' '], (string)\SmartMarkersTemplating::render_file_template(
		'modules/mod-js-components/libs/templates/text-editor-callback.inc.js',
		[
			'IS_POPUP' 	=> (int) $is_popup,
			'URL' 		=> (string) $yurl
		],
		'yes' // export to cache
	));
	//--
} //END FUNCTION
//================================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
