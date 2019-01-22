<?php
// Controller: Agile, FlowChartEditor
// Route: admin.php?page=agile.flowchart-editor
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

		$this->PageViewSetCfg('template-file', 'template-modal.htm');

		$uuid = (string) $this->RequestVarGet('uuid', '', 'string');
		$edit = (string) $this->RequestVarGet('edit', '', 'string');
		$import = (string) $this->RequestVarGet('import', '', 'string');

		if((string)$import == 'yes') {

			$this->PageViewSetVars([
				'title' 	=> 'Agile :: FlowCharts / Import',
				'main' 		=> SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'flowchart-import.htm', // the view
					[
					]
				)
			]);

			return;

		} elseif((string)$import == 'done') {

			$flowchart_title = (string) $this->RequestVarGet('flowchart_title', '', 'string');
			$flowchart_data = (string) $this->RequestVarGet('flowchart_data', '', 'string');

		} //end if

		$model = new \SmartModDataModel\Agile\SqFlowcharts();

		if($flowchart_data) {
			$sq_rd = array();
			$new_data = (string) $flowchart_data;
			if($flowchart_title) {
				$sq_rd['title'] = (string) $flowchart_title;
			} //end if
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = (string) Smart::json_encode([ 'numberOfElements' => 0, 'nodes' => [], 'connections' => [] ]);
		} //end if else
		$old_data = (string) SmartUtils::data_unarchive((string)$sq_rd['saved_data']);
		if($old_data) {
			$old_data = Smart::json_decode((string)$old_data); // mixed
		} //end if
		if(Smart::array_size($old_data) <= 0) {
			$old_data = $new_data;
		} else {
			$old_data = (string) Smart::json_encode((array)$old_data);
		} //end if

		$isnew = false;
		$sq_rd['uuid'] = (string) trim((string)$sq_rd['uuid']);
		if((string)$sq_rd['uuid'] == '') {
			$sq_rd['uuid'] = (string) $uuid;
			if((string)$sq_rd['uuid'] == '') {
				$isnew = true;
				$sq_rd['uuid'] = $model->getNewUuid();
			} //end if
		} //end if

		if((string)$edit == 'yes') {
			if($isnew) {
				$opmode = 'create';
			} else {
				$opmode = 'edit';
			} //end if else
			$tpl = 'flowchart-editor.htm';
		} else {
			$opmode = 'read';
			$tpl = 'flowchart-reader.htm';
		} //end if else

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: FlowCharts / Editor',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$tpl, // the view
				[
					'OP-MODE' 		=> (string) $opmode,
					'JSON-DATA'		=> (string) $old_data,
					'UUID' 			=> (string) $sq_rd['uuid'],
					'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled Flowchart',
					'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
					'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-',
				]
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
?>