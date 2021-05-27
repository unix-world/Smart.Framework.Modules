<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Test Samples
// Route: ?/page/captcha.test-image (?page=captcha.test-image)
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, TASK, SHARED


/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!function_exists('gd_info')) {
			$this->PageViewSetErrorStatus(500, 'NOTICE: PHP GD Extension is missing ...');
			return;
		} //end if
		//--
		$format = $this->RequestVarGet('format', '', 'string');
		//--

		//--
		if((string)$format == 'svg') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetCfg('rawmime', 'image/svg+xml');
			$this->PageViewSetCfg('rawdisp', 'inline; filename="captcha-'.time().'.svg"');
			$this->PageViewSetVar(
				'main',
				(string) $this->generateCaptchaImage($format)
			);
			return;
			//--
		} elseif((string)$format == 'png') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetCfg('rawmime', 'image/png');
			$this->PageViewSetCfg('rawdisp', 'inline; filename="captcha-'.time().'.png"');
			$this->PageViewSetVar(
				'main',
				(string) $this->generateCaptchaImage($format)
			);
			return;
		} elseif((string)$format == 'gif') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetCfg('rawmime', 'image/gif');
			$this->PageViewSetCfg('rawdisp', 'inline; filename="captcha-'.time().'.gif"');
			$this->PageViewSetVar(
				'main',
				(string) $this->generateCaptchaImage($format)
			);
			return;
		} elseif((string)$format == 'jpg') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetCfg('rawmime', 'image/jpeg');
			$this->PageViewSetCfg('rawdisp', 'inline; filename="captcha-'.time().'.jpg"');
			$this->PageViewSetVar(
				'main',
				(string) $this->generateCaptchaImage($format)
			);
			return;
			//--
		} //end if
		//--
		$this->PageViewSetVars([
			'title' => 'Sample Captcha Image',
			'main' => '<h1>Captcha Images Demo</h1><br>'.
				'<img style="float:left; margin-right:20px; margin-bottom:20px; border:1px solid #ECECEC;" title="SVG" src="'.SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&format=svg'.'">'.
				'<img style="float:left; margin-right:20px; margin-bottom:20px; border:1px solid #ECECEC;" title="PNG" src="'.SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&format=png'.'">'.
				'<img style="float:left; margin-right:20px; margin-bottom:20px; border:1px solid #ECECEC;" title="GIF" src="'.SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&format=gif'.'">'.
				'<img style="float:left; margin-right:20px; margin-bottom:20px; border:1px solid #ECECEC;" title="JPG" src="'.SmartUtils::get_server_current_script().'?page='.$this->ControllerGetParam('controller').'&format=jpg'.'">'.
				'<div style="clear:both;"></div><br>'
		]);
		//--

	} //END FUNCTION


	protected function generateCaptchaImage($format) {
		//--
		$rand = \Smart::random_number(0,8);
		$noise = 250;
		//--
		switch((int)$rand) {
			case 8:
				$font = ''; // auto, build-in
				$ttfsize = 1;
				break;
			case 7:
				$noise = 50;
				$font = 'modules/mod-captcha/fonts/3dlet.ttf';
				$ttfsize = 32;
				break;
			case 6:
				$font = 'modules/mod-captcha/fonts/akronim.ttf';
				$ttfsize = 22;
				break;
			case 5:
				$font = 'modules/mod-captcha/fonts/blocktilt.ttf';
				$ttfsize = 22;
				break;
			case 4:
				$font = 'modules/mod-captcha/fonts/fasterone.ttf';
				$ttfsize = 22;
				break;
			case 3:
				$font = 'modules/mod-captcha/fonts/frijole.ttf';
				$ttfsize = 22;
				break;
			case 2:
				$font = 'modules/mod-captcha/fonts/macondo-swash-caps.ttf';
				$ttfsize = 22;
				break;
			case 1:
				$font = 'modules/mod-captcha/fonts/mystery-quest.ttf';
				$ttfsize = 22;
				break;
			case 0:
			default:
				$font = 'modules/mod-captcha/fonts/barrio.ttf';
				$ttfsize = 24;
		} //end switch
		//--
		if((int)$rand >= 5) {
			$mode = 'hashed';
		} else {
			$mode = 'dotted';
		} //end if else
		//--
		$captcha = new \SmartModExtLib\Captcha\SmartImageCaptcha();
		//--
		$captcha->overlines = (int) \Smart::random_number(0,9);
		$captcha->distort = (bool) \Smart::random_number(0,1);
		$captcha->sketchy = (bool) \Smart::random_number(0,1);
		$captcha->emboss = (bool) \Smart::random_number(0,1);
		$captcha->scatter = (bool) \Smart::random_number(0,1);
		$captcha->negate = (bool) \Smart::random_number(0,1);
		$captcha->contrast = (bool) \Smart::random_number(0,1);
		$captcha->colorize = (bool) \Smart::random_number(0,1);
		$captcha->grayscale = (bool) \Smart::random_number(0,1);
		$captcha->noise = (int) $noise;
		//--
		$captcha->mode = (string) $mode;
		$captcha->format = (string) $format;
		$captcha->width = 175 * 2;
		$captcha->height = 50 * 2;
		$captcha->pool = (string) '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$captcha->chars = 5;
		//--
		$captcha->charfont = $font;
		$captcha->charttfsize = $ttfsize * 2;
		$captcha->charspace = 20 * 2;
		$captcha->charxvar = 15 * 2;
		$captcha->charyvar = 7 * 2;
		//--
		$captcha->colors_chars = [0x111111, 0x333333, 0x778899, 0x666699, 0x003366, 0x669966, 0x006600, 0xFF3300];
		$captcha->colors_noise = [0x888888, 0x999999, 0xAAAAAA, 0xBBBBBB, 0xCCCCCC, 0xDDDDDD, 0xEEEEEE, 0x8080C0];
		//--
		$image = (string) $captcha->draw_image();
		$code = (string) strtoupper((string)$captcha->get_code()); // this is the captcha code, but unused in this demo as it only generate the images
		//--
		return (string) $image;
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
