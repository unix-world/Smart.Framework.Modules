<?php
// Controller: Agile, DbModelEditor
// Route: admin.php?page=agile.dbmodel-editor
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

		$this->PageViewSetCfg('template-file', 'template-modal.htm');

		$uuid = (string) $this->RequestVarGet('uuid', '', 'string');
		$edit = (string) $this->RequestVarGet('edit', '', 'string');
		$type = (string) $this->RequestVarGet('type', '', ['postgresql', 'mysql', 'sqlite']);

		$model = new \SmartModDataModel\Agile\SqDbmodeler();
		$sq_rd = (array) $model->getOneByUuid($uuid);

		$new_data = [
			'type' => $type ? (string) $type : 'sqlite',
			'data' => ''
		];
		$old_data = (string) SmartUtils::data_unarchive((string)$sq_rd['saved_data']);
		if($old_data) {
			$old_data = Smart::json_decode((string)$old_data); // mixed
		} //end if
		if(Smart::array_size($old_data) <= 0) {
			$old_data = (array) $new_data;
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
					'DB-TYPE' 		=> (string) $old_data['type'],
					'XML-DATA'		=> (string) base64_encode((string)$old_data['data']),
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