<?php
// Controller: Agile, ToDoEditor
// Route: admin.php?page=agile.todo-editor
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
		$mode = (string) $this->RequestVarGet('mode', '', 'string');
		$edit = (string) $this->RequestVarGet('edit', '', 'string');
		$import = (string) $this->RequestVarGet('import', '', 'string');

		if((string)$import == 'yes') {

			$this->PageViewSetVars([
				'title' 	=> 'Agile :: ToDo-List / Import',
				'main' 		=> SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'todo-import.htm', // the view
					[
					]
				)
			]);

			return;

		} elseif((string)$import == 'done') {

			$todo_data = (string) $this->RequestVarGet('todo_data', '', 'string');

		} //end if

		$model = new \SmartModDataModel\Agile\SqTodos();

		if($todo_data) {
			$todo_data = Smart::json_decode((string)$todo_data);
			if(Smart::array_size($todo_data) <= 0) {
				$todo_data = null;
			} elseif(Smart::array_size($todo_data['data']) <= 0) {
				$todo_data = null;
			} //end if else
		} else {
			$todo_data = null;
		} //end if

		if(is_array($todo_data)) {
			$sq_rd = array();
			$new_data = (string) Smart::json_encode((array)$todo_data);
			if($todo_data['docTitle']) {
				$sq_rd['title'] = (string) $todo_data['docTitle'];
			} //end if
			$todo_data = null;
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = (string) Smart::json_encode([ 'data' => [ 'view' => 'day', 'date' => date('Y-m-d'), 'now' => true, 'todos' => [ 'data' => [], 'links' => [] ] ]]);
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

		if((string)$edit == 'yes') {
			if($isnew) {
				$opmode = 'create';
			} else {
				$opmode = 'edit';
			} //end if else
			if((string)$mode == 'kanban') {
				$tpl = 'todo-k-editor.htm';
			} else {
				$tpl = 'todo-editor.htm';
			} //end if else
		} else {
			$opmode = 'read';
			if((string)$mode == 'kanban') {
				$tpl = 'todo-k-reader.htm';
			} else {
				$tpl = 'todo-reader.htm';
			} //end if else
		} //end if else

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: ToDo-List / Editor',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$tpl, // the view
				[
					'OP-MODE' 		=> (string) $opmode,
					'JSON-DATA'		=> (string) $old_data,
					'UUID' 			=> (string) $sq_rd['uuid'],
					'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled ToDo-List',
					'DATE-FLD-HTML' => (string) SmartComponents::html_js_date_field('gantchangedate', '', '', '', '', '', ['format'=>'yy-mm-dd'], 'SmartGanttManager.changeDate(gChart, date);'),
					'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
					'DTIME' 		=> (string) $sq_rd['dtime'] ? date('Ymd_His', @strtotime((string)$sq_rd['dtime'])) : '-',
					'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-'
				]
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
?>