<?php
// Controller: TranslRepo/Export
// Route: admin.php?/page/transl-repo.export (admin.php?page=transl-repo.export)
// (c) 2008-present unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN');
define('SMART_APP_MODULE_AUTH', true);


class SmartAppAdminController extends SmartAbstractAppController { // r.20250216

	private $prj = '';
	private $lng = '';


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
		$lang = (string) $this->RequestVarGet('lang', '', 'string');
		//--

		//--
		if((string)$proj == '') {
			$this->jsonAnswer('Bad Request: Empty Project Name');
			return;
		} //end if
		$this->prj = (string) $proj;
		//--
		if(!SmartTextTranslations::validateLanguage($lang)) {
			$this->jsonAnswer('Bad Request: Invalid Language Code');
			return;
		} //end if
		if((string)$lang == (string)SmartTextTranslations::getDefaultLanguage()) { // not for EN
			$this->jsonAnswer('Bad Request: Invalid Language Code - Same as Default');
			return;
		} //end if
		$this->lng = (string) $lang;
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
		$browser = new SmartHttpClient();
		$browser->postvars = [
			'json' => (string) Smart::json_encode((new SmartModDataModel\TranslRepo\PgTranslRepoTranslations)->getAll($lang, $proj))
		];
		$data = (array) $browser->browse_url((string)$cfgs_projs[(string)$proj]['url-export'], 'POST', '', (string)$cfgs_projs[(string)$proj]['auth-user'], (string)$cfgs_projs[(string)$proj]['auth-pass']);
		//--
		if(((string)$data['result'] != 1) OR ((string)$data['code'] != 200)) {
			$this->jsonAnswer('URL Failed: Invalid Status Code: '.$data['code']);
			return;
		} //end if
		$data['content'] = (string) trim((string)$data['content']);
		if((string)$data['content'] != '') {
			$data['content'] = Smart::json_decode((string)$data['content']);
		} //end if
		if(!is_array($data['content'])) {
			$data['content'] = array();
		} //end if
		//--
	//	Smart::log_warning(print_r($data['content'],1));
		$errors = [];
		$arr = [];
		if(Smart::array_size($data['content']) > 0) {
			$model = new \SmartModDataModel\TranslRepo\PgTranslRepoTranslations();
			for($i=0; $i<Smart::array_size($data['content']); $i++) {
				$arr = [
					'text' => (string) $data['content'][$i]['txt'],
					'projects' => (array) [ (string) $proj ]
				];
				foreach((array)$data['content'][$i] as $key => $val) {
					if(strpos($key, 'transl_') === 0) {
						$arr['transl'] = (string) $val;
						break;
					} //end if
				} //end if
				$test = (int) $model->removeProjectForOne($arr);
				$arr['result'] = (int) $test;
				$errors[] = (array) $arr;
			} //end for
		} //end if
		$mmodel = new \SmartModDataModel\TranslRepo\PgTranslRepoMetainfo();
		$arr = [
			'login-id' => (string) SmartAuth::get_auth_id(),
			'date-time' => (string) date('Y-m-d H:i:s'),
			'operation' => 'SYNC-OUT',
			'project' 	=> (string) $proj,
			'language' 	=> (string) $lang
		];
		$mmodel->startTransaction();
		$mmodel->clearAllData();
		foreach($arr as $key => $val) {
			$mmodel->insertOne($key, $val);
		} //end foreach
		$mmodel->insertOne('errors-count', (int) Smart::array_size($errors));
		$mmodel->insertOne('errors-json', (string) Smart::json_encode($errors));
		$mmodel->commitTransaction();
		//--

		//--
		$this->jsonAnswer(); // OK
		//--

		$this->PageViewSetVar(
			'main',
			SmartViewHtmlHelpers::js_ajax_replyto_html_form(
				'OK',
				'Export Completed',
				'Project Translations Export Completed: `'.Smart::escape_html($proj).'`',
				'admin.php?page=transl-repo.welcome'
			)
		);
		return 200;
		//--

	} //END FUNCTION


	private function jsonAnswer($err='') {
		//--
		if((string)$err == '') {
			$this->PageViewSetVar(
				'main',
				SmartViewHtmlHelpers::js_ajax_replyto_html_form(
					'OK',
					'Export Completed ['.$this->lng.']',
					'Project Translations Export Completed: `'.Smart::escape_html($this->prj).'`',
					'admin.php?page=transl-repo.welcome'
				)
			);
		} else {
			$this->PageViewSetVar(
				'main',
				SmartViewHtmlHelpers::js_ajax_replyto_html_form(
					'ERROR',
					'Export Failed ['.$this->lng.']: '.$err,
					'Project Translations Export Failed: `'.Smart::escape_html($this->prj).'`',
					'admin.php?page=transl-repo.welcome'
				)
			);
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


//end of php code
