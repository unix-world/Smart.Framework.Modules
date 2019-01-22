<?php
// Controller: Agile, MockupEditor
// Route: admin.php?page=agile.mockup-editor
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
				'title' 	=> 'Agile :: Mockups / Import',
				'main' 		=> SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'mockup-import.htm', // the view
					[
					]
				)
			]);

			return;

		} elseif((string)$import == 'done') {

			$mockup_title = (string) $this->RequestVarGet('mockup_title', '', 'string');
			$mockup_data = (string) $this->RequestVarGet('mockup_data', '', 'string');

		} //end if

		$model = new \SmartModDataModel\Agile\SqMockups();

		if($mockup_data) {
			$sq_rd = array();
			$new_data = (string) $mockup_data;
			if($mockup_title) {
				$sq_rd['title'] = (string) $mockup_title;
			} //end if
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = (string) Smart::json_encode([ 'canvasWidth' => 1000, 'canvasHeight' => 700, 'canvasData' => '' ]);
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
			$tpl = 'mockup-editor.htm';
		} else {
			$opmode = 'read';
			$tpl = 'mockup-reader.htm';
		} //end if else

		$old_doc = (array) Smart::json_decode((string)$old_data);
		$old_doc_data = (array) $old_doc['data'];
		$old_doc = array();
		$old_doc_data['canvasData'] = (string) $old_doc_data['canvasData'];
		$old_doc_data['canvasWidth'] = (int) $old_doc_data['canvasWidth'];
		if($old_doc_data['canvasWidth'] < 500) {
			$old_doc_data['canvasWidth'] = 500;
		} //end if
		$old_doc_data['canvasHeight'] = (int) $old_doc_data['canvasHeight'];
		if($old_doc_data['canvasHeight'] < 500) {
			$old_doc_data['canvasHeight'] = 500;
		} //end if

		if((string)$edit == 'yes') {
			$arr_markers = [
				'OP-MODE' 		=> (string) $opmode,
				'JSON-DATA'		=> (string) $old_data,
				'UUID' 			=> (string) $sq_rd['uuid'],
				'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled Mockup',
				'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
				'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-',
			];
		} else { // read
			$arr_markers = [
				'OP-MODE' 		=> (string) $opmode,
				'DOC-DATA' 		=> (string) '<!DOCTYPE html><html><head><meta charset="'.Smart::escape_html(SMART_FRAMEWORK_CHARSET).'"><title>Mockup Reader</title>'.SmartFileSystem::read('lib/core/templates/base-html-styles.inc.htm').'<style>'."\n".SmartFileSystem::read('modules/mod-wflow-components/views/qmockup/qmockup-elements.css')."\n".'</style></head><body><div id="canvas">'.$old_doc_data['canvasData'].'</div></body></html>',
				'DOC-W' 		=> (int)    $old_doc_data['canvasWidth'],
				'DOC-H' 		=> (int)    $old_doc_data['canvasHeight'],
				'JSON-DATA'		=> (string) $old_data,
				'UUID' 			=> (string) $sq_rd['uuid'],
				'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled Mockup',
				'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
				'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-',
			];
		} //end if else

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: Mockups / Editor',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').$tpl, // the view
				(array)  $arr_markers
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
?>