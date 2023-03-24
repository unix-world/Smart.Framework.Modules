<?php
// Controller: Agile, BpmnEditor
// Route: admin.php?page=agile.bpmn-editor
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
		$this->PageViewSetCfg('template-file', 'template-modal.htm');
		//--
		return true;
		//--
	} //END FUNCTION


	public function Run() {

		$uuid = (string) $this->RequestVarGet('uuid', '', 'string');
		$edit = (string) $this->RequestVarGet('edit', '', 'string');
		$import = (string) $this->RequestVarGet('import', '', 'string');

		$bpmn_data = null;

		if((string)$import == 'yes') {

			$this->PageViewSetVars([
				'title' 	=> 'Agile :: BPMN-Diagrams / Import',
				'main' 		=> SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'bpmn-import.htm', // the view
					[
					]
				)
			]);

			return;

		} elseif((string)$import == 'done') {

			$bpmn_data = (string) $this->RequestVarGet('bpmn_data', '', 'string');

		} //end if

		$model = new \SmartModDataModel\Agile\SqBpmndiagrams();

		$initial_data = [ 'data' => [ 'bpmnProps' => [], 'bpmnVersion' => '2.0', 'bpmnXML' => '' ]];
		if($bpmn_data) {
			if(stripos((string)trim((string)$bpmn_data), '<'.'?xml ') === 0) { // try xml
				$bpmn_data = (string) trim((string)(new SmartXmlParser('simple'))->format($bpmn_data)); // validate xml
				if((string)$bpmn_data != '') {
					$tmp_data = (array) $initial_data;
					$tmp_data['data']['bpmnXML'] = (string) $bpmn_data;
					$bpmn_data = (array) $tmp_data;
					$tmp_data = null;
				} else {
					$bpmn_data = null;
				} //end if else
			} else { // try json
				$bpmn_data = Smart::json_decode((string)$bpmn_data);
				if(Smart::array_size($bpmn_data) <= 0) {
					$bpmn_data = null;
				} elseif(Smart::array_size($bpmn_data['data']) <= 0) {
					$bpmn_data = null;
				} //end if else
			} //end if else
		} else {
			$bpmn_data = null;
		} //end if

		if(is_array($bpmn_data)) {
			$sq_rd = array();
			$new_data = (string) Smart::json_encode((array)$bpmn_data);
			if(isset($bpmn_data['docTitle'])) {
				$sq_rd['title'] = (string) $bpmn_data['docTitle'];
			} //end if
			$bpmn_data = null;
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = (array) $initial_data;
			$new_data['data']['bpmnXML'] = (string) SmartFileSystem::read('modules/mod-wflow-components/views/bpmn-flow/bpmn-init.xml');
			$new_data = (string) Smart::json_encode($new_data);
		} //end if else
		$old_data = (string) SmartUtils::data_unarchive((string)($sq_rd['saved_data'] ?? null));
		if($old_data) {
			$old_data = Smart::json_decode((string)$old_data); // mixed
		} //end if
		if(Smart::array_size($old_data) <= 0) {
			$old_data = (string) $new_data;
		} else {
			$old_data = (string) Smart::json_encode((array)$old_data);
		} //end if

		$isnew = false;
		$sq_rd['uuid'] = (string) trim((string)($sq_rd['uuid'] ?? null));
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
			$tpl = 'bpmn-editor.htm';
		} else {
			$opmode = 'read';
			$tpl = 'bpmn-reader.htm';
		} //end if else

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: BPMN-Diagrams / Editor',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$tpl, // the view
				[
					'VIEWS-PATH' 	=> (string) $this->ControllerGetParam('module-view-path'),
					'OP-MODE' 		=> (string) $opmode,
					'JSON-DATA'		=> (string) $old_data,
					'UUID' 			=> (string) ($sq_rd['uuid'] ?? null),
					'TITLE'			=> (string) ($sq_rd['title'] ?? 'Untitled BPMN-Diagram'),
					'DATE' 			=> (string) ($sq_rd['dtime'] ?? '-'),
					'DTIME' 		=> (string) isset($sq_rd['dtime']) ? date('Ymd_His', @strtotime((string)$sq_rd['dtime'])) : '-',
					'AUTHOR' 		=> (string) ($sq_rd['user'] ?? '-'),
				]
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
