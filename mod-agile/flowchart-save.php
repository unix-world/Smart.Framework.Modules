<?php
// Controller: Agile, FlowChartSave
// Route: admin.php?page=agile.flowchart-save
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

	// v.20190405

	public function Run() {

		$this->PageViewSetCfg('rawpage', true);

		$uuid = (string) $this->RequestVarGet('uuid', '', 'string');
		$title = (string) $this->RequestVarGet('flowchart_title', '', 'string');
		$data = (string) $this->RequestVarGet('flowchart_data', '', 'string');

		if($data) {
			$wr = (int) (new \SmartModDataModel\Agile\SqFlowcharts())->saveData(
				[
					'uuid' 			=> (string) $uuid,
					'title' 		=> (string) $title,
					'saved_data' 	=> (string) SmartUtils::data_archive((string)Smart::json_encode(Smart::json_decode($data, true), true, true, false))
				],
				(string) SmartAuth:: get_login_id()
			);
		} else {
			$wr = -99; // empty data
		} //end if else

		$this->PageViewSetVar(
			'main',
			SmartViewHtmlHelpers::js_ajax_replyto_html_form(($wr === 1) ? 'OK' : 'ERROR', 'Save FlowChart', ($wr === 1) ? 'FlowChart Saved Successfuly' : 'Failed to save the FlowChart: '.$wr, ($wr === 1) ? 'admin.php?/page/agile.flowchart-editor/uuid/'.Smart::escape_url($uuid) : '')
		);

	} // END FUNCTION


} // END CLASS

// end of php code
?>