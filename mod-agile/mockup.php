<?php
// Controller: Agile, Mockup
// Route: admin.php?page=agile.mockup
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN');
define('SMART_APP_MODULE_AUTH', true);

class SmartAppAdminController extends SmartAbstractAppController {


	public function Run() {

		//--
		$sq_rd = (array) (new \SmartModDataModel\Agile\SqMockups())->getAllByUuid();
		//--
		$this->PageViewSetVars([
			'title' 	=> 'Agile :: Mockups / List',
			'main' 			=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-path').'views/mockup.htm', // the view
				[
					'MOCKUPS' => (array) $sq_rd
				]
			)
		]);
		//--

	} // END FUNCTION


} // END CLASS

// end of php code
?>