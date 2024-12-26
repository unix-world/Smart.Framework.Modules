<?php
// Controller: TranslRepo/ImportApi
// Route: admin.php?/page/transl-repo.import-api (admin.php?page=transl-repo.import-api)
// (c) 2008-present unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT S EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN');
define('SMART_APP_MODULE_AUTH', true);


class SmartAppAdminController extends SmartAbstractAppController {


	public function Run() {

		//--
		if(defined('SMART_FRAMEWORK_TEST_MODE')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: This service will not run if Test mode is enabled ...');
			return;
		} //end if
		//--

		//--
		if(Smart::array_size(Smart::get_from_config('pgsql')) <= 0) {
			$this->PageViewSetErrorStatus(503, 'ERROR: PgSQL default configs are missing ...');
			return;
		} //end if
		//--
		if(!defined('SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Missing the TRANSLATIONS TEXT ADAPTER, undefined: SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER ...');
			return;
		} //end if
		//--
		$signature = 'PgSQL: DB';
		if(stripos((string)SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER, (string)$signature) === false) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Invalid TRANSLATIONS TEXT ADAPTER SIGNATURE: missing '.$signature.' ...');
			return;
		} //end if
		$signature = null;
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
		$json = (string) $this->RequestVarGet('json', '', 'string');
		$json = (string) trim((string)$json);
		if((string)$json == '') {
			$this->PageViewSetErrorStatus(400, 'Empty JSON');
			return;
		} //end if
		$json = Smart::json_decode((string)$json);
		if(Smart::array_size($json) <= 0) {
			$this->PageViewSetErrorStatus(400, 'Invalid JSON');
			return;
		} //end if
		//--
		$arr_failed = [];
		for($i=0; $i<Smart::array_size($json); $i++) {
			if(array_key_exists('lang', $json[$i])) {
				$tmp_arr = [ (string)$json[$i]['lang'] => (string)$json[$i]['transl_'.$json[$i]['lang']] ];
			} else {
				$this->PageViewSetErrorStatus(400, 'Invalid JSON Key Lang');
				return;
			} //end if else
			if(Smart::array_size($tmp_arr) > 0) {
				foreach($tmp_arr as $key => $val) {
					if(strlen((string)$key) == 2) {
						if(preg_match('/^[a-z]+$/', (string)$key)) { // language id must contain only a..z characters (iso-8859-1)
							if((string)$key != 'en') {
								if((string)trim((string)$val) != '') {
									$test1 = -1; // init
									$test2 = -1; // init
									$test1 = (int) SmartAdapterTextTranslations::updateTranslationByText((string)$json[$i]['txt'], (string)$key, (string)$val);
									$test2 = (int) (defined('SMART_PAGEBUILDER_DB_TYPE') ? \SmartModDataModel\PageBuilder\PageBuilderBackend::updateTranslationByText((string)$json[$i]['txt'], (string)$key, (string)$val, '@transl-api') : 0);
									if(($test1 <= 0) AND ($test2 <= 0)) {
										$arr_failed[] = (array) $json[$i];
									} //end if
								} //end if
							} //end if
						} //end if
					} //end if
				} //end foreach
			} //end if
		} //end for
		//--
		$this->PageViewSetVar(
			'main',
			(string) Smart::json_encode($arr_failed)
		);
		//--

	} // END FUNCTION

} //END CLASS


//end of php code
