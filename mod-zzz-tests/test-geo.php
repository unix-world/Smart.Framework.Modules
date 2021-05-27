<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ZZZ Tests / Geo
// Route: ?page=zzz-tests.test-geo
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
		if(!class_exists('\\MatthiasMullie\\Geo\\Geo')) {
			if(!is_file('modules/vendor/MatthiasMullie/autoload.php')) {
				$this->PageViewSetErrorStatus(500, 'ERROR: Cannot Load MatthiasMullie/Geo/Geo ...');
				return;
			} //end if
			require_once('modules/vendor/MatthiasMullie/autoload.php');
		} //end if
		//-- sample calculations
		$unit = 'km';
		$geo = new \MatthiasMullie\Geo\Geo($unit);
		$coord = new \MatthiasMullie\Geo\Coordinate(50.824167, 3.263889); // coord of Kortrijk railway station
		$ncoord = new \MatthiasMullie\Geo\Coordinate(50.835167, 3.272889);
		$bounds = $geo->bounds($coord, 10); // calculate bounding box of 10km around this coordinate
		$distance = (float) $geo->distance($coord, $ncoord);
		//-- sample clusterer
		$clusterer = new \MatthiasMullie\Geo\Clusterer(
			// your viewport: in this case an approximation of bounding box around Belgium
			new \MatthiasMullie\Geo\Bounds(
				new \MatthiasMullie\Geo\Coordinate(51.474654, 6.344604),
				new \MatthiasMullie\Geo\Coordinate(49.481639, 2.470924)
			)
		);
		$clusterer->setNumberOfClusters(12); // create a matrix of about 12 cells (this may differ from 12, depending on the exact measurements of the bounding box)
		$clusterer->setMinClusterLocations(2); // start clustering after 2 locations in the same cell
		//-- add locations to clusterer
		$clusterer->addCoordinate(new \MatthiasMullie\Geo\Coordinate(50.824167, 3.263889)); // Kortrijk railway station
		$clusterer->addCoordinate(new \MatthiasMullie\Geo\Coordinate(51.035278, 3.709722)); // Gent-Sint-Pieters railway station
		$clusterer->addCoordinate(new \MatthiasMullie\Geo\Coordinate(50.881365, 4.715682)); // Leuven railway station
		$clusterer->addCoordinate(new \MatthiasMullie\Geo\Coordinate(50.860526, 4.361787)); // Brussels North railway station
		$clusterer->addCoordinate(new \MatthiasMullie\Geo\Coordinate(50.836712, 4.337521)); // Brussels South railway station
		$clusterer->addCoordinate(new \MatthiasMullie\Geo\Coordinate(50.845466, 4.357113)); // Brussels Central railway station
		$clusterer->addCoordinate(new \MatthiasMullie\Geo\Coordinate(51.216227, 4.421180)); // Antwerpen Central railway station
		//-- now get the results...
		$clusters = $clusterer->getClusters(); // returns 1 cluster: all 3 Brussels stations
		$coordinates = $clusterer->getCoordinates(); // returns 4 non-clustered coordinates
		//-- prepare vars for display
		$bounds = (string) SmartUtils::pretty_print_var((array)Smart::json_decode(Smart::json_encode($bounds))); // convert object to array
		$clusters = (string) SmartUtils::pretty_print_var((array)Smart::json_decode(Smart::json_encode($clusters))); // convert object to array
		$coordinates = (string) SmartUtils::pretty_print_var((array)Smart::json_decode(Smart::json_encode($coordinates))); // convert object to array
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'ZZZ Tests: Geo',
			'main' => '<h1 id="qunit-test-result">Geo Test: OK</h1>'.
						'<h2>Geo Bounds:</h2><pre>'.Smart::escape_html($bounds).'</pre><hr>'.
						'<h2>Geo Distance ('.Smart::escape_html($unit).'):</h2><pre>'.Smart::escape_html($distance).'</pre><hr>'.
						'<h2>Geo Clusters:</h2><pre>'.Smart::escape_html($clusters).'</pre><hr>'.
						'<h2>Geo Cluster Coordinates:</h2><pre>'.Smart::escape_html($coordinates).'</pre><hr>'.
						'<br><br>'
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
