<?php
// Controller: TranslRepo/ExportApi
// Route: admin.php?/page/transl-repo.export-api (admin.php?page=transl-repo.export-api)
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
		$arr_langs = (array) SmartTextTranslations::getAvailableLanguages();
		//--
		$arr_out = [ 'languages' => (array) $arr_langs ];
		for($i=0; $i<Smart::array_size($arr_langs); $i++) {
			$arr_out['texts-'.$arr_langs[$i]] = (array) array_merge(
				(array) SmartAdapterTextTranslations::exportTranslationsByLang((string)$arr_langs[$i], 'all', 'associative'),
				(array) (defined('SMART_PAGEBUILDER_DB_TYPE') ? \SmartModDataModel\PageBuilder\PageBuilderBackend::exportTranslationsByLang((string)$arr_langs[$i], 'all', 'associative') : [])
			);
		} //end for
		//--
		$this->PageViewSetVar(
			'main',
			(string) Smart::json_encode((array)$arr_out)
		);
		//--

	} // END FUNCTION

} //END CLASS


//end of php code
