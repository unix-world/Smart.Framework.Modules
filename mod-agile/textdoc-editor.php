<?php
// Controller: Agile, TextDocEditor
// Route: admin.php?page=agile.textdoc-editor
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

		if((string)$import == 'yes') {

			$this->PageViewSetVars([
				'title' 	=> 'Agile :: TextDocuments / Import',
				'main' 		=> SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'textdoc-import.htm', // the view
					[
					]
				)
			]);

			return;

		} elseif((string)$import == 'done') {

			$textdoc_data = (string) $this->RequestVarGet('textdoc_data', '', 'string');

		} //end if

		$model = new \SmartModDataModel\Agile\SqTextdocs();

		if($textdoc_data) {
			$textdoc_data = Smart::json_decode((string)$textdoc_data);
			if(Smart::array_size($textdoc_data) <= 0) {
				$textdoc_data = null;
			} elseif(Smart::array_size($textdoc_data['data']) <= 0) {
				$textdoc_data = null;
			} //end if else
		} else {
			$textdoc_data = null;
		} //end if

		if(is_array($textdoc_data)) {
			$sq_rd = array();
			$new_data = (string) Smart::json_encode((array)$textdoc_data);
			if($textdoc_data['docTitle']) {
				$sq_rd['title'] = (string) $textdoc_data['docTitle'];
			} //end if
			$textdoc_data = null;
		} else {
			$sq_rd = (array) $model->getOneByUuid($uuid);
			$new_data = (string) Smart::json_encode([ 'data' => [ 'paperSize' => 'A4', 'textDoc' => '' ]]);
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
			$tpl = 'textdoc-editor.htm';
		} else {
			$opmode = 'read';
			$tpl = 'textdoc-reader.htm';
		} //end if else

		$old_doc = (array) Smart::json_decode((string)$old_data);
		$old_doc_data = (array) $old_doc['data'];
		$old_doc = array();
		$old_doc_data['paperSize'] = (string) $old_doc_data['paperSize'];
		$rd_width = 'auto';
		$rd_extstyles = '';
		switch((string)$old_doc_data['paperSize']) {
			case 'A4':
				$rd_extstyles = 'body { margin: 0 !important; padding: 15mm !important; }'."\n\n";
				$rd_width = '210mm';
				break;
			case 'A4 Landscape':
				$rd_extstyles = 'body { margin: 0 !important; padding: 15mm !important; }'."\n\n";
				$rd_width = '297mm';
				break;
			case 'Screen':
			default:
				$rd_width = '96vw';
				$rd_extstyles = '';
		} //end switch

		if((string)$edit == 'yes') {
			$arr_markers = [
				'VIEWS-PATH' 	=> (string) $this->ControllerGetParam('module-view-path'),
				'OP-MODE' 		=> (string) $opmode,
				'JSON-DATA'		=> (string) $old_data,
				'UUID' 			=> (string) $sq_rd['uuid'],
				'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled TextDocument',
				'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
				'DTIME' 		=> (string) $sq_rd['dtime'] ? date('Ymd_His', @strtotime((string)$sq_rd['dtime'])) : '-',
				'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-'
			];
		} else { // read
			$arr_markers = [
				'VIEWS-PATH' 	=> (string) $this->ControllerGetParam('module-view-path'),
				'OP-MODE' 		=> (string) $opmode,
				'DOC-DATA' 		=> (string) SmartMarkersTemplating::render_file_template(
					(string) $this->ControllerGetParam('module-view-path').'partials/reader-ifrm.htm', // the view
					[
						'CHARSET' 					=> (string) $this->ControllerGetParam('charset'),
						'TITLE' 					=> (string) 'Textdoc :: '.$sq_rd['dtime'].' @ '.$sq_rd['user'].' / '.$sq_rd['uuid'],
						'HTML-STYLES-BASE' 			=> (string) '<style>'."\n".SmartComponents::app_default_css()."\n".'</style>',
						'HTML-STYLES-DOC-ELEMENTS' 	=> (string) $rd_extstyles.SmartFileSystem::read('modules/mod-wflow-components/views/texteditor/plugins/summernote-print-styles.css')."\n\n".SmartFileSystem::read('modules/mod-wflow-components/views/texteditor/plugins/summernote-table-styles.css')."\n\n".SmartFileSystem::read('modules/mod-wflow-components/views/texteditor/plugins/summernote-pagebreak.css'),
						'HTML-STYLE-CLASS' 			=> (string) 'note-printable',
						'HTML-DOC-DATA' 			=> (string) $old_doc_data['textDoc']
					]
				),
				'DOC-W' 		=> (string) $rd_width,
				'JSON-DATA'		=> (string) $old_data,
				'UUID' 			=> (string) $sq_rd['uuid'],
				'TITLE'			=> (string) $sq_rd['title'] ? $sq_rd['title'] : 'Untitled TextDocument',
				'DATE' 			=> (string) $sq_rd['dtime'] ? $sq_rd['dtime'] : '-',
				'DTIME' 		=> (string) $sq_rd['dtime'] ? date('Ymd_His', @strtotime((string)$sq_rd['dtime'])) : '-',
				'AUTHOR' 		=> (string) $sq_rd['user'] ? $sq_rd['user'] : '-'
			];
		} //end if else

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: TextDocuments / Editor',
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$tpl, // the view
				(array) $arr_markers
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
