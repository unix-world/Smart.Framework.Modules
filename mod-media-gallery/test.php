<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Media Gallery Test Sample
// Route: ?/page/media-gallery.test (?page=media-gallery.test)
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, TASK, SHARED

/*
define('SMART_FRAMEWORK_MEDIAGALLERY_IMG_CONVERTER', 	'/usr/local/bin/convert'); 				// `@gd` | path to ImagMagick Convert (change to match your system) ; can be `/usr/bin/convert` or `/usr/local/bin/convert` or `c:/open_runtime/image_magick/convert.exe`
define('SMART_FRAMEWORK_MEDIAGALLERY_IMG_COMPOSITE', 	'/usr/local/bin/composite'); 			// `@gd` | path to ImagMagick Composite/Watermark (change to match your system) ; can be `/usr/bin/composite` or `/usr/local/bin/composite` or `c:/open_runtime/image_magick/composite.exe`
define('SMART_FRAMEWORK_MEDIAGALLERY_MOV_THUMBNAILER', 	'/usr/local/bin/ffmpeg'); 				// path to FFMpeg (Video Thumbnailer to extract a preview Image from a movie) ; (change to match your system) ; can be `/usr/bin/ffmpeg` or `/usr/local/bin/ffmpeg` or `c:/open_runtime/ffmpeg/ffmpeg.exe`
*/

//define('SMART_FRAMEWORK_MEDIAGALLERY_SECUREMODE', true);
//define('SMART_FRAMEWORK_MEDIAGALLERY_WATERMARK', 'modules/mod-media-gallery/views/img/delete.png');


/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {


	public function Initialize() {
		//--
		// this is pre-run
		//--
		$this->PageViewSetCfg('template-path', 'default');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
	} //END FUNCTION


	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(defined('SMART_FRAMEWORK_MEDIAGALLERY_SECUREMODE') AND (SMART_FRAMEWORK_MEDIAGALLERY_SECUREMODE === true)) {
			//--
			$key = sha1((string)date('Y-m-d').(defined('SMART_FRAMEWORK_SECURITY_KEY') ? SMART_FRAMEWORK_SECURITY_KEY : ''));
			//--
			$lnk = $this->RequestVarGet('lnk', '', 'string');
			if((string)$lnk != '') {
				$this->PageViewSetCfgs([
					'download-key' 		=> (string) $key,
					'download-packet' 	=> (string) $lnk
				]);
				return;
			} //end if
			//--
		} //end if
		//--

		//--
		$mg = new \SmartModExtLib\MediaGallery\Manager();
		//--
		if(defined('SMART_FRAMEWORK_MEDIAGALLERY_SECUREMODE') AND (SMART_FRAMEWORK_MEDIAGALLERY_SECUREMODE === true)) {
			//--
			$mg->use_secure_links 			= 'yes';
			$mg->secure_download_link 		= '?page='.Smart::escape_url($this->ControllerGetParam('controller')).'&lnk=';
			$mg->secure_download_ctrl_key 	= (string) $key;
			//--
		} //end if
		//--
		if(defined('SMART_FRAMEWORK_MEDIAGALLERY_WATERMARK')) {
			$mg->img_watermark 			= (string) (defined('SMART_FRAMEWORK_MEDIAGALLERY_WATERMARK') ? SMART_FRAMEWORK_MEDIAGALLERY_WATERMARK : '');
			$mg->preview_watermark 		= (string) (defined('SMART_FRAMEWORK_MEDIAGALLERY_WATERMARK') ? SMART_FRAMEWORK_MEDIAGALLERY_WATERMARK : '');
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Sample Media Gallery',
			'main' => (string) '<link rel="stylesheet" type="text/css" href="'.Smart::escape_html($this->ControllerGetParam('module-path')).'views/css/mediagallery.css">'.$mg->draw(
				'Sample Media Gallery',
				'wpub/sample-media-gallery',
				'yes', // process img and movies
				'no', // remove originals
				0 // display limit
			)
		]);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php

} //END CLASS


/**
 * Task Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppTaskController extends SmartAppAdminController {

	// this will clone the SmartAppIndexController to run exactly the same action in task.php

} //END CLASS


// end of php code
