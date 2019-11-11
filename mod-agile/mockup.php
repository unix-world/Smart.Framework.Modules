<?php
// Controller: Agile, Mockup
// Route: admin.php?page=agile.mockup
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN');
define('SMART_APP_MODULE_AUTH', true);

class SmartAppAdminController extends SmartAbstractAppController {

	// v.20190405

	public function Run() {

		//--
		$sq_rd = (array) (new \SmartModDataModel\Agile\SqMockups())->getAllByUuid();
		//--
		$this->PageViewSetVars([
			'title' 	=> 'Agile :: Mockups / List',
			'main' 			=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-path').'views/mockup.htm', // the view
				[
					'LINK' => 'admin.php?/page/agile.mockup-editor/uuid/',
					'DOCS' => (array) $sq_rd
				]
			)
		]);
		//--

	} // END FUNCTION


} // END CLASS

// end of php code
?>