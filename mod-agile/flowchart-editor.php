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

	// v.20190327

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

			$flowchart_data = (string) $this->RequestVarGet('flowchart_data', '', 'string');

		} //end if

		$model = new \SmartModDataModel\Agile\SqFlowcharts();

		if($flowchart_data) {
			$flowchart_data = Smart::json_decode((string)$flowchart_data);
			if(Smart::array_size($flowchart_data) <= 0) {
				$flowchart_data = null;
			} elseif(Smart::array_size($flowchart_data['data']) <= 0) {
				$flowchart_data = null;
			} //end if else
		} else {
			$flowchart_data = null;
		} //end if

		if(is_array($flowchart_data)) {
			$sq_rd = array();
			$new_data = (string) Smart::json_encode((array)$flowchart_data);
			if($flowchart_data['docTitle']) {
				$sq_rd['title'] = (string) $flowchart_data['docTitle'];
			} //end if
			$flowchart_data = null;
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = (string) Smart::json_encode([ 'data' => [ 'numberOfElements' => 0, 'nodes' => [], 'connections' => [] ]]);
		} //end if else
		$old_data = (string) SmartUtils::data_unarchive((string)$sq_rd['saved_data']);
		if($old_data) {
			$old_data = Smart::json_decode((string)$old_data); // mixed
		} //end if
		if(Smart::array_size($old_data) <= 0) {
			$old_data = (string) $new_data;
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

		$arr_icns = [];
		$arr_fa_icns = [];
		if(SmartAppInfo::TestIfModuleExists('mod-ui-fonts')) {
			$load_fontawesome = 'yes';
		} else {
			$load_fontawesome = 'no';
		} //end if
		if((string)$edit == 'yes') {
			//--
			if($isnew) {
				$opmode = 'create';
			} else {
				$opmode = 'edit';
			} //end if else
			$tpl = 'flowchart-editor.htm';
			//--
			$arr_icns = (array) $this->getListIcons('lib/css/toolkit/sf-icons.txt');
			if((string)$load_fontawesome == 'yes') {
				$arr_fa_icns = (array) $this->getListIcons('modules/mod-ui-fonts/fonts/icons/fontawesome/fontawesome.txt');
			} //end if
			//--
		} else {
			$opmode = 'read';
			$tpl = 'flowchart-reader.htm';
		} //end if else

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: FlowCharts / Editor',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$tpl, // the view
				[
					'VIEWS-PATH' 	=> (string) $this->ControllerGetParam('module-view-path'),
					'OP-MODE' 		=> (string) $opmode,
					'JSON-DATA'		=> (string) $old_data,
					'UUID' 			=> (string) $sq_rd['uuid'],
					'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled Flowchart',
					'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
					'DTIME' 		=> (string) $sq_rd['dtime'] ? date('Ymd_His', @strtotime((string)$sq_rd['dtime'])) : '-',
					'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-',
					'THE-ICONS' 	=> (string) Smart::json_encode($arr_icns),
					'FA-ICONS' 		=> (string) Smart::json_encode($arr_fa_icns),
					'LOAD-FA' 		=> (string) $load_fontawesome
				]
			)
		]);

	} // END FUNCTION


	private function getListIcons($y_icons_list_file) {
		//--
		if((!SmartFileSysUtils::check_if_safe_path((string)$y_icons_list_file)) OR (!SmartFileSystem::is_type_file((string)$y_icons_list_file))) {
			return array();
		} //end if
		//--
		$the_icns_list = (string) SmartFileSystem::read((string)$y_icons_list_file);
		$the_icns_list = (string) trim((string)str_replace(["\r\n", "\r"], "\n", (string)$the_icns_list));
		if((string)$the_icns_list == '') {
			return array();
		} //end if
		//--
		$the_icns_list = (array)  explode("\n", (string)$the_icns_list);
		$the_icns_arr = [];
		for($i=0; $i<Smart::array_size($the_icns_list); $i++) {
			$the_icns_list[$i] = (string) trim((string)$the_icns_list[$i]);
			if((string)$the_icns_list[$i] != '') {
				$the_icns_arr[] = (string) $the_icns_list[$i];
			} //end if
		} //end for
		$the_icns_list = null; // free mem
		//--
		return (array) $the_icns_arr;
		//--
	} //END FUNCTION


} // END CLASS

// end of php code
?>