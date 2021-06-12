<?php
// Controller: Agile, DbModel
// Route: admin.php?page=agile.dbmodel
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN');
define('SMART_APP_MODULE_AUTH', true);

class SmartAppAdminController extends SmartAbstractAppController {

	// v.20210612

	public function Initialize() {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->PageViewSetErrorStatus(500, ' # Mod AuthAdmins is missing !');
			return false;
		} //end if
		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-auth-admins/templates/');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
		return true;
		//--
	} //END FUNCTION


	public function Run() {

		//--
		$sq_rd = (array) (new \SmartModDataModel\Agile\SqDbmodels())->getAllByUuid();
		//--
		$this->PageViewSetVars([
			'title' 	=> 'Agile :: DbModeler / List',
			'main' 			=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-path').'views/dbmodel.htm', // the view
				[
					'LINK' => 'admin.php?/page/agile.dbmodel-editor/uuid/',
					'DOCS' => (array) $sq_rd
				]
			)
		]);
		//--

	} // END FUNCTION


} // END CLASS

// end of php code
