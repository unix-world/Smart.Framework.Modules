<?php
// Class: \SmartModDataModel\TranslRepo\PgTranslRepoTranslationss
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModDataModel\TranslRepo;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
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
