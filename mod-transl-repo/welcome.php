<?php
// Controller: TranslRepo/Welcome
// Route: admin.php?/page/transl-repo.welcome (admin.php?page=transl-repo.welcome)
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


	public function Initialize() {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->PageViewSetErrorStatus(500, 'Mod AuthAdmins is missing !');
			return false;
		} //end if
		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-auth-admins/templates/');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
		return true;
		//--
	} //END FUNCTION


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
		if(!class_exists('SmartAbstractPgsqlExtDb')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: The Smart Extra Libs module is not loaded ...');
			return;
		} //end if
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-page-builder')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: The PageBuilder Module is missing ...');
			return;
		} //end if
		//--

		//--
		$action = $this->RequestVarGet('action', '', 'string');
		//--

		//--
		$languages_list = (array) SmartTextTranslations::getListOfLanguages();
		foreach($languages_list as $key => $val) {
			if((string)$key == (string)SmartTextTranslations::getDefaultLanguage()) {
				unset($languages_list[(string)$key]);
			} //end if
		} //end if
		//--
		$projects_cfg_list = Smart::get_from_config('transl-repo-projects');
		if(!is_array($projects_cfg_list)) {
			$projects_cfg_list = array();
		} //end if
		//--

		//--
		if((string)$action == 'import-form') {
			//--
			$this->PageViewSetVars([
				'title' => (string) $title,
				'main' => (string) \SmartModExtLib\PageBuilder\Manager::ViewDisplayImportData('custom', 'XLS', 'admin.php?page='.$this->ControllerGetParam('controller').'&action=import-xls')
			]);
			return;
			//--
		} elseif((string)$action == 'import-xls') {
			//--
			$this->PageViewSetVars([
				'title' => (string) $title,
				'main' => (string) \SmartModExtLib\PageBuilder\Manager::ViewDisplayImportDoData(
					'custom',
					'Texts',
					'\\SmartModDataModel\\TranslRepo\\PgTranslRepoTranslationss'
				)
			]);
			return;
			//--
		} elseif((string)$action == 'errors-list') {
			//--
			$mmodel = new \SmartModDataModel\TranslRepo\PgTranslRepoMetainfo();
			$errors = (array) $mmodel->getOneByUniqueKey('id', 'errors-json');
			$errors['val'] = Smart::json_decode((string)$errors['val']);
			if(!is_array($errors['val'])) {
				$errors['val'] = array();
			} //end if
			//--
			$this->PageViewSetVars([
				'title' => 'Translations Repo - Errors List',
				'main' => SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'errors-list'.'.mtpl.htm',
					[
						'DATA-ARR' => (array) $errors['val'],
						'LAST-OP-MSG' => (array) $mmodel->getAll(),
						'LAST-OP-ACT' => 'errors-xls',
						'LAST-OP-BTN' => 'Export XLS'
					]
				)
			]);
			return;
			//--
		} elseif((string)$action == 'errors-xls') {
			//--
			$mmodel = new \SmartModDataModel\TranslRepo\PgTranslRepoMetainfo();
			//--
			$exportlang = '??';
			$arr = (array) $mmodel->getAll();
			for($i=0; $i<Smart::array_size($arr); $i++) {
				if(is_array($arr[$i])) {
					if((string)$arr[$i]['id'] == 'language') {
						if(SmartTextTranslations::validateLanguage((string)$arr[$i]['val'])) {
							$exportlang = (string) $arr[$i]['val'];
						} //end if
						break;
					} //end if
				} //end if
			} //end for
			$arr = [];
			//--
			$this->PageViewSetCfg('rawpage', true);
			$spreadsheet = new SmartSpreadSheetExport(500, 50);
			$this->PageViewSetCfg('rawmime', (string)$spreadsheet->getMimeType());
			$this->PageViewSetCfg('rawdisp', (string)$spreadsheet->getDispositionHeader('translations-repo-en_'.$exportlang.'-all-'.date('Ymd_His').'.xml', 'attachment'));
			//--
			$errors = (array) $mmodel->getOneByUniqueKey('id', 'errors-json');
			$errors['val'] = Smart::json_decode((string)$errors['val']);
			if(!is_array($errors['val'])) {
				$errors['val'] = array();
			} //end if
			//--
			$arrsheets = [
				'[lang_en]',
				'[lang_'.$exportlang.']'
			];
			//--
			$arr = [];
			for($i=0; $i<Smart::array_size($errors['val']); $i++) {
				$arr[] = [
					'lang_en' => (string) $errors['val'][$i]['text'],
					'lang_'.$exportlang => (string) $errors['val'][$i]['transl'],
				];
			} //end for
			//--
			$this->PageViewSetVar(
				'main',
				(string) $spreadsheet->getFileContents(
					'PageBuilder Transl. - Repo - ALL',
					(array) $arrsheets,
					(array) $arr
				)
			);
			$spreadsheet = null;
			return;
			//--
		} elseif((string)$action == 'list-data') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			//--
			$ofs = $this->RequestVarGet('ofs', 0, 'integer+');
			$sortby = $this->RequestVarGet('sortby', 'txt', 'string');
			$sortdir = $this->RequestVarGet('sortdir', 'ASC', 'string');
			$sorttype = $this->RequestVarGet('sorttype', 'text', 'string');
			$src = $this->RequestVarGet('src', '', 'string'); // filter var
			$lng = $this->RequestVarGet('lng', '', 'string'); // filter var
			$proj = $this->RequestVarGet('proj', '', 'string'); // filter var
			$dts = $this->RequestVarGet('dts', '', 'string'); // filter var
			$dte = $this->RequestVarGet('dte', '', 'string'); // filter var
			//--
			$model = new \SmartModDataModel\TranslRepo\PgTranslRepoTranslations();
			//--
			$arr = [
				'status'  			=> 'OK',
				'crrOffset' 		=> (int) $ofs,
				'itemsPerPage' 		=> 25,
				'sortBy' 			=> (string) $sortby,
				'sortDir' 			=> (string) $sortdir,
				'sortType' 			=> (string) $sorttype,
				'filter' 			=> [
					'src' 			=> (string) $src,
					'lng' 			=> (string) $lng,
					'proj' 			=> (string) $proj,
					'dts' 			=> (string) $dts, 'dtfmt__dts' => (string) $dts,
					'dte' 			=> (string) $dte, 'dtfmt__dte' => (string) $dte
				]
			];
			$arr['totalRows'] 	= (int)   $model->countListAll($src, $lng, $proj, $dts, $dte);
			$arr['rowsList'] 	= (array) $model->getListAll($arr['itemsPerPage'], $arr['crrOffset'], $arr['sortBy'], $arr['sortDir'], $src, $lng, $proj, $dts, $dte);
			//--
			$this->PageViewSetVar(
				'main',
				(string) Smart::json_encode((array)$arr)
			);
			return;
			//--
		} elseif((string)$action == 'delete-data') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			//--
			$id = $this->RequestVarGet('id', '', 'string'); // filter var
			$id = (string) trim((string)$id);
			//--
			$is_ok = false;
			if((string)$id != '') {
				$test = (new \SmartModDataModel\TranslRepo\PgTranslRepoTranslations())->deleteById($id);
				if($test == 1) {
					$is_ok = true;
				} //end if
			} //end if
			//--
			if($is_ok) {
				$this->PageViewSetVar(
					'main',
					SmartViewHtmlHelpers::js_ajax_replyto_html_form(
						'OK',
						'Record Deleted',
						'The Record id: `'.Smart::escape_html($id).'` was successfuly deleted.',
						'admin.php?page=transl-repo.welcome&action=list'
					)
				);
			} else {
				$this->PageViewSetVar(
					'main',
					SmartViewHtmlHelpers::js_ajax_replyto_html_form(
						'ERROR',
						'Record Deletion FAILED',
						'There were some errors deleting the Record id: `'.Smart::escape_html($id).'` ...',
						'admin.php?page=transl-repo.welcome&action=list'
					)
				);
			} //end if else
			return;
			//--
		} elseif((string)$action == 'cleardata') {
			//--
			(new \SmartModDataModel\TranslRepo\PgTranslRepoTranslations())->clearAllData();
			//--
			$this->PageViewSetVars([
				'title' => 'Clear All Data from Translations Repo',
				'main' => SmartComponents::operation_ok('<h1>All Data Cleared !</h1>')
			]);
			return;
			//--
		} elseif((string)$action == 'list') {
			//--
			$plist_lst = [ '[]' => '[None]'];
			if(is_array($projects_cfg_list)) {
				foreach($projects_cfg_list as $key => $val) {
					$plist_lst[(string)$key] = (string) $key;
				} //end foreach
			} //end if
			$this->PageViewSetVars([
				'title' => 'Translations Repo - Data List',
				'main' => SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'list'.'.mtpl.htm',
					[
						'RELEASE-HASH' 		=> (string) SmartUtils::get_app_release_hash(),
						'HTML-LIST-LANGS' 	=> (string) \SmartModExtLib\AuthAdmins\SmartAdmViewHtmlHelpers::html_select_list_single(
												'filter-languages-list',
												(string) $lng,
												'form',
												(array) $languages_list,
												'lng',
												'100/0'
											),
						'HTML-LIST-PROJS' 	=> (string) \SmartModExtLib\AuthAdmins\SmartAdmViewHtmlHelpers::html_select_list_single(
												'filter-projects-list',
												(string) $proj,
												'form',
												(array) $plist_lst,
												'proj',
												'150/0'
											),
						'HTML-DATE-START' 	=> (string) \SmartModExtLib\AuthAdmins\SmartAdmViewHtmlHelpers::html_js_date_field(
												'filter-date-start',
												'dts',
												(string) $dts,
												'Date Start',
												'',
												'',
												[
													'format' => 'YYYY-MM-DD'
												]
											),
						'HTML-DATE-END' 	=> (string) \SmartModExtLib\AuthAdmins\SmartAdmViewHtmlHelpers::html_js_date_field(
												'filter-date-end',
												'dte',
												(string) $dte,
												'Date End',
												'',
												'',
												[
													'format' => 'YYYY-MM-DD'
												]
											)
					]
				)
			]);
			return;
			//--
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Translations Repo - Main Screen',
			'main' => SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$this->ControllerGetParam('action').'.mtpl.htm',
				[
					'WEBSITE-APP-NAME' 	=> (string) $this->ConfigParamGet('app.name'),
					'PAGE-URL-PREFIX' 	=> (string) $this->ControllerGetParam('controller'),
					'HTML-LIST-LANGS' 	=> (string) \SmartModExtLib\AuthAdmins\SmartAdmViewHtmlHelpers::html_select_list_single(
												'languages-list',
												'',
												'form',
												(array) $languages_list,
												'languages_list',
												'100/0'
											),
					'ARR-PROJECTS' 		=> (array) array_keys((array)$projects_cfg_list),
					'LAST-OP-MSG' 		=> (array) (new \SmartModDataModel\TranslRepo\PgTranslRepoMetainfo())->getAll(),
					'LAST-OP-ACT' 		=> 'errors-list',
					'LAST-OP-BTN' 		=> 'View List'
				]
			)
		]);
		//--

	} //END FUNCTION

} //END CLASS


//end of php code
