<?php
// Controller: Agile, DbModelSave
// Route: admin.php?page=agile.dbmodel-save
// (c) 2006-2023 unix-world.org - all rights reserved

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

	public function Run() {

		$this->PageViewSetCfg('rawpage', true);

		$uuid = (string) $this->RequestVarGet('uuid', '', 'string');
		$title = (string) $this->RequestVarGet('dbmodel_title', '', 'string');
		$data = (string) $this->RequestVarGet('dbmodel_data', '', 'string');

		if($data) {
			$wr = (int) (new \SmartModDataModel\Agile\SqDbmodels())->saveData(
				[
					'uuid' 			=> (string) $uuid,
					'title' 		=> (string) $title,
					'saved_data' 	=> (string) SmartUtils::data_archive((string)Smart::json_encode(Smart::json_decode($data, true), true, true, false))
				],
				(string) SmartAuth:: get_auth_username()
			);
		} else {
			$wr = -99; // empty data
		} //end if else

		$this->PageViewSetVar(
			'main',
			SmartViewHtmlHelpers::js_ajax_replyto_html_form(($wr === 1) ? 'OK' : 'ERROR', 'Save DbModel', ($wr === 1) ? 'DbModel Saved Successfuly' : 'Failed to save the DbModel: '.$wr, ($wr === 1) ? 'admin.php?/page/agile.dbmodel-editor/uuid/'.Smart::escape_url($uuid) : '')
		);

	} // END FUNCTION


} // END CLASS

// end of php code
