<?php
// Controller: Agile, DbModelEditor
// Route: admin.php?page=agile.dbmodel-editor
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

		$allowed_types = ['sqlite', 'postgresql', 'mysql'];

		$uuid = (string) $this->RequestVarGet('uuid', '', 'string');
		$edit = (string) $this->RequestVarGet('edit', '', 'string');
		$type = (string) $this->RequestVarGet('type', '', (array)$allowed_types);
		$import = (string) $this->RequestVarGet('import', '', 'string');

		$dbmodel_data = null;

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

		$initial_data = ['data' => [ 'type' => $type ? (string) $type : 'sqlite', 'xml' => '' ]];
		if($dbmodel_data) {
			if(stripos((string)trim((string)$dbmodel_data), '<'.'?xml ') === 0) { // try xml
				$xml = new SmartXmlParser('extended');
				$dbmodel_data = (string) trim((string)$xml->format($dbmodel_data)); // validate xml
				if((string)$dbmodel_data != '') {
					$tmp_arr = (array) $xml->transform((string)$dbmodel_data);
					$xml_db_type = ''; // sql|@attributes
					if(Smart::array_size($tmp_arr) > 0) {
						if(Smart::array_size($tmp_arr['sql|@attributes']) > 0) {
							if(Smart::array_size($tmp_arr['sql|@attributes'][0]) > 0) {
								$xml_db_type = (string) trim((string)strtolower((string)$tmp_arr['sql|@attributes'][0]['db']));
							} //end if
						} //end if
					} //end if
					if(in_array((string)$xml_db_type, (array)$allowed_types)) {
						$tmp_data = (array) $initial_data;
						$tmp_data['data']['type'] = (string) $xml_db_type;
						$tmp_data['data']['xml'] = (string) $dbmodel_data;
						$dbmodel_data = (array) $tmp_data;
						$tmp_data = null;
					} else { // invalid type
						$dbmodel_data = null;
					} //end if else
					$tmp_arr = null;
				} else {
					$dbmodel_data = null;
				} //end if else
				$xml = null;
			} else { // try json
				$dbmodel_data = Smart::json_decode((string)$dbmodel_data);
				if(Smart::array_size($dbmodel_data) <= 0) {
					$dbmodel_data = null;
				} elseif(Smart::array_size($dbmodel_data['data']) <= 0) {
					$dbmodel_data = null;
				} //end if else
			} //end if else
		} else {
			$dbmodel_data = null;
		} //end if

		if(is_array($dbmodel_data)) {
			$sq_rd = array();
			$new_data = (array) $dbmodel_data;
			if(isset($dbmodel_data['docTitle'])) {
				$sq_rd['title'] = (string) $dbmodel_data['docTitle'];
			} //end if
			$dbmodel_data = null;
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = (array) $initial_data;
		} //end if else
		$old_data = (string) SmartUtils::data_unarchive((string)($sq_rd['saved_data'] ?? null));
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
					'UUID' 			=> (string) ($sq_rd['uuid'] ?? null),
					'TITLE'			=> (string) ($sq_rd['title'] ?? 'Untitled DB-Model'),
					'DATE' 			=> (string) ($sq_rd['dtime'] ?? '-'),
					'DTIME' 		=> (string) isset($sq_rd['dtime']) ? date('Ymd_His', @strtotime((string)$sq_rd['dtime'])) : '-',
					'AUTHOR' 		=> (string) ($sq_rd['user'] ?? '-'),
				]
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
