<?php
// Class: \SmartModDataModel\TranslRepo\PgTranslRepoTranslationss
// (c) 2019-2021 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

namespace SmartModDataModel\TranslRepo;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//-- Model: PgSQL / TranslRepo.Translations/Static [PHP8]


final class PgTranslRepoTranslationss {

	// ::

	public static function updateTranslationByText($text_en, $lang, $text_lang) {
		//--
		$obj = new \SmartModDataModel\TranslRepo\PgTranslRepoTranslations();
		//--
		return (int) $obj->updateTranslationByText($text_en, $lang, $text_lang);
		//--
	} //END FUNCTION


} //END CLASS

// end of php code
