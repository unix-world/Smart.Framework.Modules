<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / SVG Draw (MeyFa SVG)
// Route: ?page=zzz-tests.test-svg-draw
// (c) 2006-2020 unix-world.org - all rights reserved
// r.5.7.2 / smart.framework.v.5.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(SMART_FRAMEWORK_TEST_MODE !== true) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!class_exists('\\SVG')) {
			if(!is_file('modules/vendor/MeyFaSvg/autoload.php')) {
				$this->PageViewSetErrorStatus(500, 'ERROR: Cannot Load SVG ...');
				return;
			} //end if
			require_once('modules/vendor/MeyFaSvg/autoload.php');
		} //end if
		//--

		//--
		$image = new \SVG\SVG(100, 100); // image with 100x100 viewport
		$doc = $image->getDocument();
		$square = new \SVG\Nodes\Shapes\SVGRect(0, 0, 40, 40); // blue 40x40 square at (0, 0)
		$square->setStyle('fill', '#0000FF');
		$doc->addChild($square);
		//--
		$circle = new \SVG\Nodes\Shapes\SVGCircle(50, 50, 20);
		$doc->addChild(
			$circle
				->setStyle('fill', 'none')
				->setStyle('stroke', '#0F0')
				->setStyle('stroke-width', '2px')
		);
		//--
		$image2 = \SVG\SVG::fromString($image);
		$imgres = $image->toRasterImage(100, 100);
		imagefilter($imgres, IMG_FILTER_GRAYSCALE);
		ob_start();
		imagepng($imgres);
		$png = ob_get_clean();
		//--
		$this->PageViewSetVars([
			'title' => 'ZZZ Tests: SVG Draw',
			'main' => '<h1 id="qunit-test-result">SVG Test Result: OK</h1><pre><h2>SVG Source Code</h2><pre>'.Smart::escape_html(str_replace('><', '>'."\n".'<', (string)$image)).'</pre><hr>'.'<h2>SVG Vector Image</h2><img src="data:image/svg+xml;base64,'.base64_encode((string)$image).'"><hr>'.'<h2>SVG Raster (PNG + GreyScale Filter)</h2><img src="data:image/png;base64,'.base64_encode((string)$png).'"><hr>'.'<br><br>'
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


// end of php code
