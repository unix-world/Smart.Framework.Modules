<?php
// Controller: Agile, DbModelEditor
// Route: admin.php?page=agile.dbmodel-editor
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
		$type = (string) $this->RequestVarGet('type', '', ['sqlite', 'postgresql', 'mysql']);
		$import = (string) $this->RequestVarGet('import', '', 'string');

		if((string)$import == 'yes') {

			$this->PageViewSetVars([
				'title' 	=> 'Agile :: DbModel / Import',
				'main' 		=> SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'dbmodel-import.htm', // the view
					[
					]
				)
			]);

			return;

		} elseif((string)$import == 'done') {

			$dbmodel_data = (string) $this->RequestVarGet('dbmodel_data', '', 'string');

		} //end if

		$model = new \SmartModDataModel\Agile\SqDbmodels();

		if($dbmodel_data) {
			$dbmodel_data = Smart::json_decode((string)$dbmodel_data);
			if(Smart::array_size($dbmodel_data) <= 0) {
				$dbmodel_data = null;
			} elseif(Smart::array_size($dbmodel_data['data']) <= 0) {
				$dbmodel_data = null;
			} //end if else
		} else {
			$dbmodel_data = null;
		} //end if

		if(is_array($dbmodel_data)) {
			$sq_rd = array();
			$new_data = (array) $dbmodel_data;
			if($dbmodel_data['docTitle']) {
				$sq_rd['title'] = (string) $dbmodel_data['docTitle'];
			} //end if
			$dbmodel_data = null;
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = ['data' => [ 'type' => $type ? (string) $type : 'sqlite', 'xml' => '' ]];
		} //end if else
		$old_data = (string) SmartUtils::data_unarchive((string)$sq_rd['saved_data']);
		if($old_data) {
			$old_data = Smart::json_decode((string)$old_data); // mixed
		} //end if
		$the_type = '';
		if(Smart::array_size($old_data) <= 0) {
			if(is_array($new_data['data'])) {
				$the_type = (string) $new_data['data']['type'];
			} //end if
			$old_data = (string) Smart::json_encode($new_data);
		} else {
			$the_type = (string) $old_data['data']['type'];
			$old_data = (string) Smart::json_encode((array)$old_data);
		} //end if
		if(!in_array((string)$the_type, ['sqlite', 'postgresql', 'mysql'])) {
			$the_type = 'sqlite';
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
			$tpl = 'dbmodel-editor.htm';
		} else {
			$opmode = 'read';
			$tpl = 'dbmodel-reader.htm';
		} //end if else

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: DbModel / Editor',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$tpl, // the view
				[
					'OP-MODE' 		=> (string) $opmode,
					'DB-TYPE' 		=> (string) $the_type,
					'JSON-DATA'		=> (string) $old_data,
					'UUID' 			=> (string) $sq_rd['uuid'],
					'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled DB-Model',
					'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
					'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-',
				]
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
?>