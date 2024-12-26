<?php
// Controller: TranslRepo/Import
// Route: admin.php?/page/transl-repo.import (admin.php?page=transl-repo.import)
// (c) 2008-present unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN');
define('SMART_APP_MODULE_AUTH', true);


class SmartAppAdminController extends SmartAbstractAppController {

	private $prj = '';


	public function Run() {

		//--
		if(defined('SMART_FRAMEWORK_TEST_MODE')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: This service will not run if Test mode is enabled ...');
			return;
		} //end if
		//--

		//--
		if(Smart::array_size(Smart::get_from_config('transl-repo-projects')) <= 0) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Translations Repo configs are missing ...');
			return;
		} //end if
		//--
		if(Smart::array_size(Smart::get_from_config('pgsql-transl-repo')) <= 0) {
			$this->PageViewSetErrorStatus(503, 'ERROR: PgSQL Translations Repo configs are missing ...');
			return;
		} //end if
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('smart-extra-libs')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: The Smart Extra Libs module is missing ...');
			return;
		} //end if
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-page-builder')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: The PageBuilder Module is missing ...');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetCfg('rawpage', true);
		//--

		//--
		$proj = (string) $this->RequestVarGet('proj', '', 'string');
		//--

		//--
		if((string)$proj == '') {
			$this->jsonAnswer('Bad Request: Empty Project Name');
			return;
		} //end if
		$this->prj = (string) $proj;
		//--

		//--
		$cfgs_projs = Smart::get_from_config('transl-repo-projects');
		if(!is_array($cfgs_projs)) {
			$cfgs_projs = array();
		} //end if
		//--
		if(Smart::array_size($cfgs_projs) <= 0) {
			$this->jsonAnswer('Internal Error: No Projects Configurations');
			return;
		} //end if
		//--
		if(Smart::array_size($cfgs_projs[(string)$proj]) <= 0) {
			$this->jsonAnswer('Internal Error: Empty Projects Configurations');
			return;
		} //end if
		//--

		//--
		$data = (array) SmartRobot::load_url_content(
			(string) $cfgs_projs[(string)$proj]['url-import'],
			(int)    30,
			(string) 'GET',
			(string) '',
			(string) $cfgs_projs[(string)$proj]['auth-user'], // to work with 2FA, this should be token user
			(string) $cfgs_projs[(string)$proj]['auth-pass'] // to work with 2FA, this should be token pass
		);
		if(((string)$data['result'] != 1) OR ((string)$data['code'] != 200)) {
			$this->jsonAnswer('URL Failed: Invalid Status Code: '.$data['code']);
			return;
		} //end if
		$data['content'] = (string) trim((string)$data['content']);
		if((string)$data['content'] == '') {
			$this->jsonAnswer('URL Failed: Empty Data');
			return;
		} //end if
		//--
		$data['content'] = Smart::json_decode((string)$data['content']);
		if(!is_array($data['content'])) {
			$data['content'] = array();
		} //end if
		if(Smart::array_size($data['content']) <= 0) {
			$this->jsonAnswer('URL Failed: Invalid Data (1)');
			return;
		} //end if
		//--
		if((Smart::array_size($data['content']['languages']) <= 0) OR (!in_array('en', $data['content']['languages']))) {
			$this->jsonAnswer('URL Failed: Invalid Data (2)');
			return;
		} //end if
		//--
		$arr_langs = [ (string) SmartTextTranslations::getDefaultLanguage() ];
		foreach($data['content']['languages'] as $key => $val) {
			$val = (string) trim((string)$val);
			if(SmartTextTranslations::validateLanguage($val)) {
				if((string)$val != (string)SmartTextTranslations::getDefaultLanguage()) {
					$arr_langs[] = (string) $val;
				} //end if
			} //end if
		} //end foreach
		//--
		$arr_texts = [];
		for($i=0; $i<Smart::array_size($arr_langs); $i++) {
			if(!is_array($data['content']['texts-'.$arr_langs[$i]])) {
				$this->jsonAnswer('URL Failed: Invalid Data (3)');
				return;
			} //end if
			$arr_texts[(string)$arr_langs[$i]] = (array) $data['content']['texts-'.$arr_langs[$i]];
		} // end for
		//--
		$data = null; // free mem
		//--

		//--
		$model = new \SmartModDataModel\TranslRepo\PgTranslRepoTranslations();
		//--
		foreach($arr_texts as $key => $val) {
			if(Smart::array_size($val) > 0) {
				for($i=0; $i<Smart::array_size($val); $i++) {
					if(Smart::array_size($val[$i]) > 0) {
						if(((string)trim((string)$val[$i]['lang_en']) != '') AND ((string)trim((string)$val[$i]['lang_'.$key]) != '')) {
							$test = (int) $model->insertOrUpdateOne([
								'text' => (string) $val[$i]['lang_en'],
								'transl' => (array) [ (string)$key => (string) $val[$i]['lang_'.$key ] ],
								'projects' => (array) [ (string)$proj ]
							]);
							if(!$test) {
							//	$this->jsonAnswer('URL Failed: Invalid Data (4)');
							//	return;
							} //end if
						} //end if
					} //end if
				} //end for
			} //end if
		} //end foreach
		//--

		//--
		$this->jsonAnswer(); // OK
		//--

	} //END FUNCTION


	private function jsonAnswer($err='') {
		//--
		if((string)$err == '') {
			$this->PageViewSetVar(
				'main',
				SmartViewHtmlHelpers::js_ajax_replyto_html_form(
					'OK',
					'Import Completed',
					'Project Translations Import Completed: `'.Smart::escape_html($this->prj).'`',
					'admin.php?page=transl-repo.welcome'
				)
			);
		} else {
			$this->PageViewSetVar(
				'main',
				SmartViewHtmlHelpers::js_ajax_replyto_html_form(
					'ERROR',
					'Import Failed: '.$err,
					'Project Translations Import Failed: `'.Smart::escape_html($this->prj).'`',
					'admin.php?page=transl-repo.welcome'
				)
			);
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


//end of php code
