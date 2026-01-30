<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / SVG Draw (MeyFa SVG)
// Route: ?page=zzz-tests.test-svg-draw
// (c) 2006-present unix-world.org - all rights reserved

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
		if(!class_exists('\\SVG\\SVG')) {
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
		$image = (string) $image; // toString()
		//-- #use for extended test purposes only (will use lib robot with external URL or dataURL)
		$image = (string) str_ireplace('</svg>', '<image href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABEAAAAPCAYAAAACsSQRAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6ODhBMDIwQzhDRUI3MTFFMjg4RUJDNUMzQkZEREM2RDIiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6ODhBMDIwQzlDRUI3MTFFMjg4RUJDNUMzQkZEREM2RDIiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4OEEwMjBDNkNFQjcxMUUyODhFQkM1QzNCRkREQzZEMiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo4OEEwMjBDN0NFQjcxMUUyODhFQkM1QzNCRkREQzZEMiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PntuzoIAAACySURBVHjaYmTAAjo6Ov4z4AAVFRWM6GJM6ALt7e0OUCYjFowsDwfIEjDbDRgZGSf8//8fUzEj4wGgeAGQeQFJ+CIj0IALQEl9BjIB0NADTJQYAHWdAxMDFQBVDGEB+ukgxYYAcQNSzCCnh4NY0o89VkOAAbMfV5gBNR0AytuDXAs01AEUxbhcwoAj1c4HaUTi9wMNwWnb/0ERO0xA/z6k0IyP4NgBGpRApgEfgHgBQIABAAcyQOF3e6FiAAAAAElFTkSuQmCC" height="16" width="16"/></svg>', (string)$image);
	//	$image = (string) str_ireplace('</svg>', '<image href="http://mdn.mozillademos.org/files/6457/mdn_logo_only_color.png" height="24" width="24"/></svg>', (string)$image);
		//-- #end test
		$image2 = \SVG\SVG::fromString($image);
		$imgres = $image2->toRasterImage(100, 100);
		//--
		$extratext = '';
		if(rand(0,1) == 1) {
			$extratext = ' + GreyScale Filter';
			imagefilter($imgres, IMG_FILTER_GRAYSCALE);
		} //end if
		ob_start();
		imagepng($imgres);
		$png = ob_get_clean();
		//--
		$this->PageViewSetVars([
			'title' => 'ZZZ Tests: SVG Draw',
			'main' => '<h1 id="qunit-test-result">SVG Test Result: OK</h1><pre><h2>SVG Source Code</h2><pre>'.Smart::escape_html(str_replace('><', '>'."\n".'<', (string)$image)).'</pre><hr>'.'<h2>SVG Vector Image</h2><img src="data:image/svg+xml;base64,'.base64_encode((string)$image).'"><hr>'.'<h2>SVG Raster (PNG'.$extratext.')</h2><img src="data:image/png;base64,'.base64_encode((string)$png).'"><hr>'.'<br><br>'
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
