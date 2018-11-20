<?php
// Controller: Agile, DbModelSave
// Route: admin.php?page=agile.dbmodel-save
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

		$this->PageViewSetCfg('rawpage', true);

		$uuid = (string) $this->RequestVarGet('uuid', '', 'string');
		$title = (string) $this->RequestVarGet('dbmodel_title', '', 'string');
		$data = (string) $this->RequestVarGet('dbmodel_data', '', 'string');
		$type = (string) $this->RequestVarGet('dbmodel_type', '', ['postgresql', 'mysql', 'sqlite']);
		if($data) {
			$data = (string) base64_decode((string)$data);
		} //end if

		if($type AND $data) {
			$wr = (int) (new \SmartModDataModel\Agile\SqDbmodeler())->saveData(
				[
					'uuid' 			=> (string) $uuid,
					'title' 		=> (string) $title,
					'saved_data' 	=> (string) SmartUtils::data_archive((string)Smart::json_encode(
						[
							'type' => (string) $type,
							'data' => (string) $data
						],
						true,
						true,
						false
					))
				],
				(string) SmartAuth:: get_login_id()
			);
		} else {
			$wr = -99; // empty XML data
		} //end if else

		$this->PageViewSetVar(
			'main',
			SmartComponents::js_ajax_replyto_html_form(($wr === 1) ? 'OK' : 'ERROR', 'Save DbModel', ($wr === 1) ? 'DbModel Saved Successfuly: '.$wr : 'Failed to save the DbModel: '.$wr, ($wr === 1) ? 'admin.php?/page/agile.dbmodel-editor/uuid/'.Smart::escape_url($uuid) : '')
		);

	} // END FUNCTION


} // END CLASS

// end of php code
?>