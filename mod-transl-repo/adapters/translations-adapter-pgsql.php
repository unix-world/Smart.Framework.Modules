<?php
// [LIB - SmartFramework / PgSQL Text Translations Adapter]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.8.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// REQUIRED
//======================================================
// Smart-Framework - Parse Regional Text # PgSQL DB
define('SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER', 'PgSQL: DB based');
if((!is_array($configs)) OR (!isset($configs['pgsql'])) OR (!is_array($configs['pgsql']))) {
	@http_response_code(500);
	die('PgSQL Custom Translations Adapter: PgSQL default configs are missing');
} //end if
//======================================================

// [PHP8]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class SmartAdapterTextTranslations - PgSQL DB text translations adapter, using the DEFAULT Connection
 * This class should not be used directly it is just an adapter for the SmartTextTranslations. Use SmartTextTranslations to get translations not this class
 *
 * @access 		private
 * @internal
 *
 * @depends 	classes: Smart, SmartFileSysUtils, SmartTextTranslations ; constants: SMART_APP_TRANSLATIONS_MODIFY_DATE, SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER
 *
 * @version		20221220
 * @package 	Application:Translations:Adapters:Pgsql
 *
 */
final class SmartAdapterTextTranslations implements SmartInterfaceAdapterTextTranslations {

	// ::


	//==================================================================
	// This returns the last update version of the translations
	public static function getTranslationsVersion() {
		//--
		$version = (string) trim('#TextTranslations::Version#'."\n".(defined('SMART_APP_TRANSLATIONS_MODIFY_DATE') ? SMART_APP_TRANSLATIONS_MODIFY_DATE : '')."\n".'App.Adapter :: '.(defined('SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER') ? SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER : '')."\n".'#.#');
		//--
		return (string) $version;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	// This reads and parse the PgSQL/DB translation files by language, area and sub-area
	public static function getTranslationsFromSource(?string $the_lang, ?string $y_area, ?string $y_subarea) {
		//--
		global $configs;
		if(Smart::array_size($configs['pgsql']) <= 0) {
			Smart::raise_error(
				'Invalid Language Adapter DB Connector: The PgSQL Default Connection is not defined in config ...',
				'Invalid Language Adapter DB Connector' // msg to display
			);
			return array();
		} //end if
		//--
		$the_lang = (string) Smart::safe_varname((string)$the_lang);
		if(((string)$the_lang == '') OR (!SmartFileSysUtils::checkIfSafeFileOrDirName((string)$the_lang))) {
			Smart::log_warning(__METHOD__.'() :: Invalid/Empty parameter for Translation Language: '.$the_lang);
			return array();
		} //end if
		//--
		$y_area = (string) Smart::safe_filename((string)$y_area);
		if(((string)$y_area == '') OR (!SmartFileSysUtils::checkIfSafeFileOrDirName((string)$y_area))) {
			Smart::log_warning(__METHOD__.'() :: Invalid/Empty parameter for Translation Area: '.$y_area);
			return array();
		} //end if
		//--
		$y_subarea = (string) Smart::safe_filename((string)$y_subarea);
		if(((string)$y_subarea == '') OR (!SmartFileSysUtils::checkIfSafeFileOrDirName((string)$y_subarea))) {
			Smart::log_warning(__METHOD__.'() :: Invalid/Empty parameter for Translation SubArea: '.$y_subarea);
			return array();
		} //end if
		//--
		$arr = (array) SmartPgsqlDb::read_asdata(
			'SELECT COALESCE(json_object_agg("key", "val")::text, $1) AS translations FROM "web"."app_translations" WHERE (("lang" = $2) AND ("area" = $3) AND ("subarea" = $4))',
			[
				(string) '{}',
				(string) $the_lang,
				(string) $y_area,
				(string) $y_subarea
			]
		);
		//--
		$arr = Smart::json_decode((string)$arr['translations']);
		if(Smart::array_size($arr) <= 0) {
			if((string)SmartTextTranslations::getDefaultLanguage() == (string)$the_lang) {
				Smart::raise_error(
					'Parse Error / TRANSLATIONS for: '.$the_lang.'/'.$y_area.'/'.$y_subarea,
					'Parse Error / TRANSLATIONS for: '.$the_lang.'/'.$y_area.'/'.$y_subarea // msg to display
				);
			} //end if
			return array();
		} //end if
		//--
		return (array) $arr;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	// This register the usage of every translation as pair of language, area and sub-area, key
	public static function setTranslationsKeyUsageCount(?string $the_lang, ?string $y_area, ?string $y_subarea, ?string $y_textkey) {
		//--
		if(SmartEnvironment::ifDevMode() !== true) {
			return; // this can be used only in DEV mode
		} //end if
		//--
		// check for SMART_FRAMEWORK__DEBUG__TEXT_TRANSLATIONS is not used, this is a DB adapter and can set in DB the usage count, will not polute with extra usage count log files like the filesystem based adapters
		//--
		SmartPgsqlDb::write_data(
			'UPDATE "web"."app_translations" SET "counter" = "counter" + 1 WHERE (("lang" = $1) AND ("area" = $2) AND ("subarea" = $3) AND ("key" = $4))',
			[
				(string) $the_lang,
				(string) $y_area,
				(string) $y_subarea,
				(string) $y_textkey
			]
		);
		//--
	} //END FUNCTION
	//==================================================================


	//======= [CUSTOM]


	// Add Support For Translations Repo
	public static function exportTranslationsByLang(?string $lang, ?string $mode='all', ?string $arrmode='non-associative') {
		//--
		// $lang: 'en' | 'de' | ... (must be a valid language ID)
		// $mode: 'all' | 'missing'
		// $arrmode: 'non-associative' | 'associative'
		//--
		$lang = (string) trim((string)$lang);
		//--
		if(((string)$lang == '') OR (strlen((string)$lang) != 2) OR SmartTextTranslations::validateLanguage((string)$lang) !== true) {
			return array(); // invalid language
		} //end if
		//--
		$deflang = (string) SmartTextTranslations::getDefaultLanguage();
		//--
		if((string)$lang == (string)$deflang) {
			//--
			$query = 'SELECT DISTINCT "val" AS "lang_en", \'\' AS '.SmartPgsqlDb::escape_identifier((string)'lang__'.$lang).' FROM "web"."app_translations" WHERE ("is_translatable" != \'f\') AND ("lang" = \''.SmartPgsqlDb::escape_str((string)$deflang).'\')';
			//--
			if((string)$mode == 'missing') {
				$query .= ' AND ("val" = \'\')';
			} else {
				$query .= ' AND ("val" != \'\')';
			} //end if
			//--
			$query .= ' ORDER BY "val" ASC';
			//--
		} else {
			//--
			$query = '
				SELECT DISTINCT "a"."val" AS "lang_en", COALESCE("b"."val", \'\') AS '.SmartPgsqlDb::escape_identifier((string)'lang_'.$lang).'
				FROM "web"."app_translations" "a"
				LEFT OUTER JOIN "web"."app_translations" "b" ON
					"a"."area" = "b"."area" AND
					"a"."subarea" = "b"."subarea" AND
					"a"."key" = "b"."key" AND
					"a"."lang" = \''.SmartPgsqlDb::escape_str((string)$deflang).'\' AND
					"b"."lang" = \''.SmartPgsqlDb::escape_str((string)$lang).'\'
				WHERE
					("a"."is_translatable" != \'f\') AND ("a"."lang" = \''.SmartPgsqlDb::escape_str((string)$deflang).'\') AND ("a"."val" != \'\')
			';
			//--
			if((string)$mode == 'missing') {
				$query .= ' AND ("b"."lang" IS NULL)';
			} //end if
			//--
			$query .= ' ORDER BY "a"."val" ASC';
			//--
		} //end if
		//--
		if((string)$arrmode == 'associative') {
			return (array) SmartPgsqlDb::read_adata((string)$query);
		} else {
			return (array) SmartPgsqlDb::read_data((string)$query);
		} //end if else
		//--
	} //END FUNCTION


	public static function updateTranslationByText(?string $text_deflang, ?string $lang, ?string $text_lang) {
		//--
		if((string)trim((string)$text_lang) == '') {
			return -1;
		} //end if
		//--
		if((string)trim((string)$text_deflang) == '') {
			return -2;
		} //end if
		if(((string)trim((string)$lang) == '') OR (strlen((string)$lang) != 2) OR (SmartTextTranslations::validateLanguage($lang) !== true) OR ((string)$lang == (string)SmartTextTranslations::getDefaultLanguage())) {
			return -3;
		} //end if
		//--
		SmartPgsqlDb::write_data('BEGIN');
		//--
		$arr = (array) SmartPgsqlDb::read_adata(
			'SELECT "area", "subarea", "key" FROM "web"."app_translations" WHERE (("lang" = $1) AND ("val" = $2))',
			[
				(string) SmartTextTranslations::getDefaultLanguage(),
				(string) $text_deflang
			]
		);
		//--
		if(Smart::array_size($arr) <= 0) {
			return -4;
		} //end if
		//--
		$upd = 0;
		//--
		if(Smart::array_size($arr) > 0) {
			//--
			for($i=0; $i<Smart::array_size($arr); $i++) {
				//--
				SmartPgsqlDb::write_data(
					'DELETE FROM "web"."app_translations" WHERE (("lang" = $1) AND ("area" = $2) AND ("subarea" = $3) AND ("key" = $4))',
					[
						(string) $lang,
						(string) $arr[$i]['area'],
						(string) $arr[$i]['subarea'],
						(string) $arr[$i]['key']
					]
				);
				//--
				$wr = (array) SmartPgsqlDb::write_data(
					'INSERT INTO "web"."app_translations" '.SmartPgsqlDb::prepare_statement(
						[
							'lang' 		=> (string) $lang,
							'area' 		=> (string) $arr[$i]['area'],
							'subarea' 	=> (string) $arr[$i]['subarea'],
							'key' 		=> (string) $arr[$i]['key'],
							'val' 		=> (string) $text_lang
						],
						'insert'
					)
				);
				//--
				$upd += (int) $wr[1];
				//--
			} //end for
			//--
		} //end if
		//--
		SmartPgsqlDb::write_data('COMMIT'); // COMMIT
		//--
		return (int) $upd;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
