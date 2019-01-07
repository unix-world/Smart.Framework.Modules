<?php
// Class: \SmartModDataModel\PageBuilder\PageBuilderFrontend
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

namespace SmartModDataModel\PageBuilder;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * SQLite/PostgreSQL Model for ModPageBuilder/Frontend
 * @ignore
 */
final class PageBuilderFrontend {

	// ::
	// v.20190107

	private static $db = null;
	private static function dbType() {
		//--
		if((string)\SmartModExtLib\PageBuilder\Utils::getDbType() == 'pgsql') {
			//--
			if(\Smart::array_size(\Smart::get_from_config('pgsql')) <= 0) {
				throw new \Exception(__CLASS__.': PostgreSQL DB CONFIG Not Found !');
				return;
			} //end if
			//--
			return 'pgsql';
			//--
		} else {
			//--
			if(self::$db === null) {
				//--
				$sqlitedbfile = '#db/page-builder.sqlite';
				//--
				if(!\SmartFileSysUtils::check_if_safe_path((string)$sqlitedbfile, 'yes', 'yes')) { // dissalow absolute ; allow protected
					throw new \Exception(__CLASS__.': SQLite DB PATH is UNSAFE !');
					return;
				} //end if
				//--
				self::$db = new \SmartSQliteDb((string)$sqlitedbfile);
				self::$db->open();
				//--
				if(!\SmartFileSystem::is_type_file((string)$sqlitedbfile)) {
					if(self::$db instanceof \SmartSQliteDb) {
						self::$db->close();
					} //end if
					throw new \Exception(__CLASS__.': SQLite DB File does NOT Exists !');
					return;
				} //end if
				//--
			} //end if
			//--
			return 'sqlite';
			//--
		} //end if else
		//--
	} //END FUNCTION


	public static function checkIfPageOrSegmentExist($y_id) {
		//--
		$y_id = (string) trim((string)$y_id);
		//--
		if((string)self::dbType() == 'pgsql') {
			if((string)substr($y_id, 0, 1) == '#') { // segment
				$query = 'SELECT "id" FROM "web"."page_builder" WHERE ("id" = $1) LIMIT 1 OFFSET 0';
			} else { // page
				$query = 'SELECT "id" FROM "web"."page_builder" WHERE (("id" = $1) AND ("active" = 1)) LIMIT 1 OFFSET 0';
			} //end if else
			$arr = (array) \SmartPgsqlDb::read_asdata(
				(string) $query,
				[
					(string) $y_id
				]
			);
		} elseif((string)self::dbType() == 'sqlite') {
			if((string)substr($y_id, 0, 1) == '#') { // segment
				$query = 'SELECT `id` FROM `page_builder` WHERE (`id` = ?) LIMIT 1 OFFSET 0';
			} else { // page
				$query = 'SELECT `id` FROM `page_builder` WHERE ((`id` = ?) AND (`active` = 1)) LIMIT 1 OFFSET 0';
			} //end if else
			$arr = (array) self::$db->read_asdata(
				(string) $query,
				[
					(string) $y_id
				]
			);
		} else {
			return false; // n/a
		} //end if else
		//--
		if((string)$arr['id'] === (string)$y_id) {
			return true; // exists
		} else {
			return false; // does not exists
		} //end if else
		//--
	} //END FUNCTION


	public static function getPage($y_id, $y_lang='') { // page must be active
		//--
		$y_id = (string) trim((string)$y_id);
		if((string)substr($y_id, 0, 1) == '#') {
			return array(); // avoid to load a segment
		} //end if
		//--
		if((string)self::dbType() == 'pgsql') {
			$arr = (array) \SmartPgsqlDb::read_asdata(
				'SELECT "id", "name", "mode", "auth", "layout", "data", "code", "translations" FROM "web"."page_builder" WHERE (("id" = $1) AND ("active" = 1)) LIMIT 1 OFFSET 0',
				[
					(string) $y_id
				]
			);
		} elseif((string)self::dbType() == 'sqlite') {
			$arr = (array) self::$db->read_asdata(
				'SELECT `id`, `name`, `mode`, `auth`, `layout`, `data`, `code`, `translations` FROM `page_builder` WHERE ((`id` = ?) AND (`active` = 1)) LIMIT 1 OFFSET 0',
				[
					(string) $y_id
				]
			);
		} else {
			return array();
		} //end if else
		//--
		if((string)SMART_ERROR_HANDLER == 'dev') {
			if((string)self::dbType() == 'pgsql') {
				\SmartPgsqlDb::write_data(
					'UPDATE "web"."page_builder" SET "counter" = "counter" + 1 WHERE (("id" = $1) AND ("active" = 1))',
					[
						(string) $y_id
					]
				);
			} elseif((string)self::dbType() == 'sqlite') {
				self::$db->write_data(
					'UPDATE `page_builder` SET `counter` = `counter` + 1 WHERE ((`id` = ?) AND (`active` = 1))',
					[
						(string) $y_id
					]
				);
			} //end if else
		} //end if
		//--
		$y_lang = (string) trim((string)$y_lang);
		if((string)$y_lang != '') {
			if((string)$arr['translations'] == '1') {
				$tarr = (array) self::getTranslation($y_id, $y_lang);
				if(((string)$tarr['id'] == (string)$arr['id']) AND ((string)trim((string)$tarr['code']) != '')) {
					$arr['code'] = (string) $tarr['code'];
					$arr['@lang'] = (string) $tarr['lang'];
				} //end if
			} //end if
		} //end if
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	public static function getSegment($y_id, $y_lang='') {
		//--
		$y_id = (string) trim((string)$y_id);
		if((string)substr($y_id, 0, 1) != '#') {
			return array(); // avoid to load a page
		} //end if
		//--
		if((string)self::dbType() == 'pgsql') {
			$arr = (array) \SmartPgsqlDb::read_asdata(
				'SELECT "id", "name", "mode", 0 AS "auth", \'\' AS "layout", "data", "code", "translations" FROM "web"."page_builder" WHERE ("id" = $1) LIMIT 1 OFFSET 0',
				[
					(string) $y_id
				]
			);
		} elseif((string)self::dbType() == 'sqlite') {
			$arr = (array) self::$db->read_asdata(
				'SELECT `id`, `name`, `mode`, 0 AS `auth`, ? AS `layout`, `data`, `code`, `translations` FROM `page_builder` WHERE (`id` = ?) LIMIT 1 OFFSET 0',
				[
					'',
					(string) $y_id
				]
			);
		} else {
			return array();
		} //end if else
		//--
		if((string)SMART_ERROR_HANDLER == 'dev') {
			if((string)self::dbType() == 'pgsql') {
				\SmartPgsqlDb::write_data(
					'UPDATE "web"."page_builder" SET "counter" = "counter" + 1 WHERE ("id" = $1)',
					[
						(string) $y_id
					]
				);
			} elseif((string)self::dbType() == 'sqlite') {
				self::$db->write_data(
					'UPDATE `page_builder` SET `counter` = `counter` + 1 WHERE (`id` = ?)',
					[
						(string) $y_id
					]
				);
			} //end if else
		} //end if
		//--
		$y_lang = (string) trim((string)$y_lang);
		if((string)$y_lang != '') {
			if((string)$arr['translations'] == '1') {
				$tarr = (array) self::getTranslation($y_id, $y_lang);
				if(((string)$tarr['id'] == (string)$arr['id']) AND ((string)trim((string)$tarr['code']) != '')) {
					$arr['code'] = (string) $tarr['code'];
					$arr['@lang'] = (string) $tarr['lang'];
				} //end if
			} //end if
		} //end if
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	private static function getTranslation($y_id, $y_lang) {
		//--
		if((string)self::dbType() == 'pgsql') {
			return (array) \SmartPgsqlDb::read_asdata(
				'SELECT "id", "lang", "code" FROM "web"."page_translations" WHERE (("id" = $1) AND ("lang" = $2)) LIMIT 1 OFFSET 0',
				[
					(string) $y_id,
					(string) $y_lang
				]
			);
		} elseif((string)self::dbType() == 'sqlite') {
			return (array) self::$db->read_asdata(
				'SELECT `id`, `lang`, `code` FROM `page_translations` WHERE ((`id` = ?) AND (`lang` = ?)) LIMIT 1 OFFSET 0',
				[
					(string) $y_id,
					(string) $y_lang
				]
			);
		} else {
			return array();
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>