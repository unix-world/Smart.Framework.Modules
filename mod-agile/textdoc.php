<?php
// Controller: Agile, TextDoc
// Route: admin.php?page=agile.textdoc
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

	// v.20200121

	public function Run() {

		//--
		$sq_rd = (array) (new \SmartModDataModel\Agile\SqTextdocs())->getAllByUuid();
		//--
		$this->PageViewSetVars([
			'title' 	=> 'Agile :: TextDoc / List',
			'main' 			=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-path').'views/textdoc.htm', // the view
				[
					'LINK' => 'admin.php?/page/agile.textdoc-editor/uuid/',
					'DOCS' => (array) $sq_rd
				]
			)
		]);
		//--

	} // END FUNCTION


} // END CLASS

// end of php code
