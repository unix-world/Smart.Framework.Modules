<?php
// Controller: Agile, DbModel
// Route: admin.php?page=agile.dbmodel
// Author: unix-world.org
// r.181120

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
		$sq_rd = (array) (new \SmartModDataModel\Agile\SqDbmodeler())->getAllByUuid();
		//--
		$this->PageViewSetVars([
			'title' 	=> 'Agile :: DbModeler / List',
			'main' 			=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-path').'views/dbmodel.htm', // the view
				[
					'DB-MODELS' => (array) $sq_rd
				]
			)
		]);
		//--

	} // END FUNCTION


} // END CLASS

// end of php code
?>