<?php
// Class: \SmartModDataModel\TranslRepo\PgTranslRepoTranslations
// (c) 2008-present unix-world.org - all rights reserved

namespace SmartModDataModel\TranslRepo;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//-- Model: PgSQL / TranslRepo.Translations [PHP8]


final class PgTranslRepoTranslations extends \SmartModDataModel\TranslRepo\PgDbTranslRepo {

	// ->


	public function clearAllData() {
		//--
		$wr = (array) $this->getConnection()->write_data(
			'TRUNCATE TABLE "transl_repo"."translations"'
		);
		//--
		return (int) $wr[1];
		//--
	} //END FUNCTION


	public function getOneByUniqueKey(?string $field, ?string $value) {
		//--
		return (array) $this->getOneByKeyTableSchema('transl_repo', 'translations', (string)$field, (string)$value, [], '');
		//--
	} //END FUNCTION


	public function getAll(?string $lang='', ?string $proj='') {
		//--
		$ktransl = '"transl"';
		if(\SmartTextTranslations::validateLanguage($lang)) {
			if((string)$lang != (string)\SmartTextTranslations::getDefaultLanguage()) {
				$ktransl = '\''.$this->getConnection()->escape_str($lang).'\' AS "lang", COALESCE("transl"->>\''.$this->getConnection()->escape_str($lang).'\', \'\') AS '.$this->getConnection()->escape_identifier('transl_'.$lang);
			} //end if
		} //end if
		//--
		$proj = (string) \strtolower((string)\trim((string)$proj));
		//--
		if((string)$proj != '') {
			return (array) $this->getConnection()->read_adata(
				'SELECT "id", "txt", '.$ktransl.', "projects", "created", "modified", "status" FROM "transl_repo"."translations" WHERE ("projects" ? $1)',
				[
					(string) $proj
				]
			);
		} else {
			return (array) $this->getConnection()->read_adata(
				'SELECT "id", "txt", '.$ktransl.', "projects", "created", "modified", "status" FROM "transl_repo"."translations"'
			);
		} //end if else
		//--
	} //END FUNCTION


	public function getListAll(?int $limit, ?int $ofs, ?string $sortby, ?string $sortdir, ?string $src, ?string $lng, ?string $proj, ?string $dts, ?string $dte) {
		//--
		$limit = (int) $limit;
		if($limit < 1) {
			$limit = 1;
		} //end if
		//--
		$ofs = (int) $ofs;
		if($ofs < 0) {
			$ofs = 0;
		} //end if
		//--
		switch((string)$sortby) {
			case 'created':
			case 'modified':
				break;
			case 'txt':
			default:
				$sortby = 'txt';
		} //end switch
		//--
		switch((string)\strtoupper((string)$sortdir)) {
			case 'DESC':
				$sortdir = 'DESC';
				break;
			case 'ASC':
			default:
				$sortdir = 'ASC';
		} //end switch
		//--
		$where = (string) $this->whereConditionList($src, $lng, $proj, $dts, $dte);
		//--
		return (array) $this->getConnection()->read_adata(
			'SELECT "id", "txt", "transl", "projects", "created", "modified", "status" FROM "transl_repo"."translations"'.$where.' ORDER BY '.$this->getConnection()->escape_identifier($sortby).' '.$sortdir.' LIMIT '.(int)$limit.' OFFSET '.(int)$ofs
		);
		//--
	} //END FUNCTION


	public function countListAll(?string $src, ?string $lng, ?string $proj, ?string $dts, ?string $dte) {
		//--
		$where = (string) $this->whereConditionList($src, $lng, $proj, $dts, $dte);
		//--
		return (int) $this->getConnection()->count_data(
			'SELECT COUNT(1) FROM "transl_repo"."translations"'.$where
		);
		//--
	} //END FUNCTION


	public function insertOrUpdateOne(?array $data) {
		//--
		$test = (int) $this->insertOne($data);
		//--
		if($test === 1) {
			return 1;
		} elseif($test === -1) {
			return 0;
		} else {
			$test = (int) $this->updateOne($data);
		} //end if
		//--
		return (int) $test;
		//--
	} //END FUNCTION


	public function removeProjectForOne(?array $data) {
		//--
		if(\Smart::array_type_test($data) != 2) {
			return -10; // invalid format
		} //end if
		//--
		$data['text'] = (string) (isset($data['text']) ? $data['text'] : null);
		//--
		if((string)\trim((string)$data['text']) == '') {
			return -1; // empty text
		} //end if
		//--
		$id = (string) \sha1((string)$data['text']);
		$test = (array) $this->getOneByUniqueKey('id', (string)$id);
		if(\Smart::array_size($test) <= 0) {
			return 1; // not exists, OK
		} //end if
		$test = null;
		//--
		$projects = (array) $this->formatProjsList($data);
		if((\Smart::array_size($projects) > 0) AND (\Smart::array_type_test($projects) == 1)) { // req. non-associative array
			$projects  = (string) $this->getConnection()->json_encode((array)$projects);
		} else {
			$projects = '[]';
		} //end if
		//--
		if((string)$projects == '[]') {
			return 1; // nothing to remove
		} //end if
		$projects = \Smart::json_decode((string)$projects);
		if(!\is_array($projects)) {
			return 1; // nothing to remove
		} //end if
		$projects = (string) (isset($projects[0]) ? $projects[0] : null);
		if((string)\trim((string)$projects) == '') {
			return 0; // this is an error, if get this point should have at least one project
		} //end if
		//--
		$wr = (array) $this->getConnection()->write_data(
			'UPDATE "transl_repo"."translations" SET "projects" = smart_jsonb_arr_delete("projects", $2) WHERE ("id" = $1)',
			[
				(string) $id,
				(string) $projects
			]
		);
		//--
		return (int) $wr[1];
		//--
	} //END FUNCTION


	public function updateTranslationByText(?string $text_en, ?string $lang, ?string $text_lang) {
		//--
		$text_en   = (string) $text_en;
		$text_lang = (string) $text_lang;
		//--
		if((string)\trim((string)$text_en) == '') {
			return -1;
		} //end if
		if((string)trim((string)$text_lang) == '') {
			return -2;
		} //end if
		//--
		if((!\SmartTextTranslations::validateLanguage((string)$lang)) OR ((string)$lang == (string)\SmartTextTranslations::getDefaultLanguage())) {
			return -3;
		} //end if
		//--
		$id   = (string) \sha1((string)$text_en);
		$test = (array) $this->getOneByUniqueKey('id', (string)$id);
		//--
		if(\Smart::array_size($test) <= 0) {
			return -4;
		} //end if
		//--
		return (int) $this->updateOne(
			[
				'text' => (string) $text_en,
				'transl' => (array) [
					(string) $lang => (string) $text_lang
				]
			],
			true
		);
		//--
	} //END FUNCTION


	//=====


	private function whereConditionList(?string $src, ?string $lng, ?string $proj, ?string $dts, ?string $dte) {
		//--
		$where = [];
		//--
		$src = (string) \trim((string)$src);
		//--
		$is_by_lang = false;
		$lng = (string) \trim((string)$lng);
		if(\SmartTextTranslations::validateLanguage($lng)) {
			if((string)$lng != (string)\SmartTextTranslations::getDefaultLanguage()) {
				$where[] = '(COALESCE("transl"->>\''.$this->getConnection()->escape_str($lng).'\', \'\') != \'\')';
				if((string)$src != '') {
					$is_by_lang = true;
				} //end if
			} //end if
		} //end if else
		//--
		if((string)$src != '') {
			if($is_by_lang === true) {
				$where[] = '(("txt" ILIKE \''.$this->getConnection()->escape_str($src).'\') OR (transl->>\''.$this->getConnection()->escape_str($lng).'\'::text ILIKE \''.$this->getConnection()->escape_str($src).'\'))';
			} else {
				$where[] = '(("txt" ILIKE \''.$this->getConnection()->escape_str($src).'\') OR ("transl"::text ILIKE \''.$this->getConnection()->escape_str($src).'\'))';
			} //end if else
		} //end if else
		//--
		$proj = (string) \trim((string)$proj);
		if((string)$proj != '') {
			if((string)$proj != '[]') {
				$parr = (array) \explode(',', (string)\trim((string)$proj));
				for($i=0; $i<\Smart::array_size($parr); $i++) {
					$parr[$i] = (string) \strtolower((string)\trim((string)$parr[$i]));
					if(\strpos((string)$parr[$i], '!') === 0) {
						$parr[$i] = (string) \ltrim((string)$parr[$i], '!');
						$is_negation = true;
					} else {
						$is_negation = false;
					} //end if else
					if((string)$parr[$i] != '') {
						if(\preg_match('/^[a-z0-9]+$/', (string)$parr[$i])) {
							if($is_negation === true) {
								$where[] = '(("projects" ? \''.$this->getConnection()->escape_str($parr[$i]).'\') IS FALSE)';
							} else {
								$where[] = '("projects" ? \''.$this->getConnection()->escape_str($parr[$i]).'\')';
							} //end if else
						} //end if
					} //end if
				} //end for
			} else {
				$where[] = '(("projects"::text = \'[]\') OR (COALESCE("projects"::text, \'\') = \'\'))';
			} //end if else
		} //end if else
		//--
		$dts = (string) \trim((string)$dts);
		$dte = (string) \trim((string)$dte);
		//--
		if(((string)$dts != '') AND ((string)$dte != '')) {
			$dts = (string) \date('Y-m-d', @\strtotime((string)$dts));
			$dte = (string) \date('Y-m-d', @\strtotime((string)$dte));
			if((string)$dts <= (string)$dte) {
				$dte = (string) \date('Y-m-d', @strtotime($dte.' +1 day'));
				$where[] = '("modified" BETWEEN \''.$this->getConnection()->escape_str($dts).'\' AND \''.$this->getConnection()->escape_str($dte).'\')';
			} //end if
		} //end if
		//--
		if(\Smart::array_size($where) > 0) {
			return ' WHERE ('.\implode(' AND ', (array)$where).')';
		} else {
			return '';
		} //end if else
		//--
	} //END FUNCTION


	private function insertOne(?array $data) {
		//--
		if(\Smart::array_type_test($data) != 2) {
			return -10; // invalid format
		} //end if
		//--
		$data['text'] = (string) (isset($data['text']) ? $data['text'] : null);
		//--
		if((string)\trim((string)$data['text']) == '') {
			return -1; // empty text
		} //end if
		//--
		$id = (string) \sha1((string)$data['text']);
		$test = (array) $this->getOneByUniqueKey('id', (string)$id);
		if(\Smart::array_size($test) > 0) {
			return 0; // already exists
		} //end if
		$test = null;
		//--
		$transl = (array) $this->formatTranslList($data);
		if((\Smart::array_size($transl) > 0) AND (\Smart::array_type_test($transl) == 2)) { // req. associative array
			$transl = (string) $this->getConnection()->json_encode((array)$transl);
		} else {
			$transl = '{}';
		} //end if
		//--
		$projects = (array) $this->formatProjsList($data);
		if((\Smart::array_size($projects) > 0) AND (\Smart::array_type_test($projects) == 1)) { // req. non-associative array
			$projects  = (string) $this->getConnection()->json_encode((array)$projects);
		} else {
			$projects = '[]';
		} //end if
		//--
		$date = (string) \date('Y-m-d H:i:s');
		//--
		$wr = (array) $this->getConnection()->write_igdata(
			'INSERT INTO "transl_repo"."translations" '.$this->getConnection()->prepare_statement(
				[
					'id' 			=> (string) $id,
					'txt' 			=> (string) $data['text'],
					'transl' 		=> (string) $transl, // {en: txt1, fr: txt2, ...} :: JSON / ASSOCIATIVE-ARRAY
					'projects' 		=> (string) $projects, // [p1, p2, ..., px] :: JSON / ARRAY
					'created' 		=> (string) $date,
					'modified' 		=> (string) $date,
					'status' 		=> (int)    0
				],
				'insert'
			)
		);
		//--
		return (int) $wr[1];
		//--
	} //END FUNCTION


	private function updateOne(?array $data, bool $rewrite=false) {
		//--
		if(\Smart::array_type_test($data) != 2) {
			return -10; // invalid format
		} //end if
		//--
		$data['text'] = (string) (isset($data['text']) ? $data['text'] : null);
		//--
		if((string)\trim((string)$data['text']) == '') {
			return -1; // empty text
		} //end if
		//--
		$id = (string) \sha1((string)$data['text']);
		//--
		$transl = (array) $this->formatTranslList((array)$data);
		if((\Smart::array_size($transl) > 0) AND (\Smart::array_type_test($transl) == 2)) { // req. associative array
			$transl = (string) $this->getConnection()->json_encode((array)$transl);
		} else {
			$transl = '{}';
		} //end if
		//--
		$projects = (array) $this->formatProjsList((array)$data);
		if((\Smart::array_size($projects) > 0) AND (\Smart::array_type_test($projects) == 1)) { // req. non-associative array
			$projects  = (string) $this->getConnection()->json_encode((array)$projects);
		} else {
			$projects = '[]';
		} //end if
		//--
		if(((string)$transl == '{}') AND ((string)$projects == '[]')) {
			return 1; // fake update
		} //end if
		//--
		$where = '';
		if(!$rewrite) {
			$tmp_arr = \Smart::json_decode($transl);
			if(\Smart::array_size($tmp_arr) > 0) {
				foreach($tmp_arr as $key => $val) {
					if((string)$key != '') {
						if(\SmartTextTranslations::validateLanguage($key)) {
							if((string)$key != (string)\SmartTextTranslations::getDefaultLanguage()) {
								$where = ' AND (COALESCE("transl"->>\''.$this->getConnection()->escape_str((string)$key).'\', \'\') = \'\')';
								break; // stop after 1st
							} //end if
						} //end if
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		if(!$rewrite) {
			$wr = (array) $this->getConnection()->write_data(
				'UPDATE "transl_repo"."translations" SET "projects" = smart_jsonb_arr_append("projects", $2) WHERE ("id" = $1)',
				[
					(string) $id,
					(string) $projects
				]
			);
		} //end if
		$wr = (array) $this->getConnection()->write_data(
			'UPDATE "transl_repo"."translations" SET "transl" = smart_jsonb_obj_append("transl", $2), "modified" = $3 WHERE ("id" = $1)'.$where,
			[
				(string) $id,
				(string) $transl,
				(string) \date('Y-m-d H:i:s')
			]
		);
		//--
		return (int) $wr[1];
		//--
	} //END FUNCTION


	public function deleteById(?string $id) {
		//--
		$wr = (array) $this->getConnection()->write_data(
			'DELETE FROM "transl_repo"."translations" WHERE ("id" = $1)',
			[
				(string) $id
			]
		);
		//--
		return (int) $wr[1];
		//--
	} //END FUNCTION


	private function formatProjsList(?array $data) {
		//--
		$projects = [];
		//--
		if(\is_array($data) AND (\Smart::array_type_test($data) == 2)) { // req. associative array
			if(isset($data['projects']) AND \is_array($data['projects'])) {
				foreach($data['projects'] as $key => $val) {
					if(\Smart::is_nscalar($val)) {
						$val = (string) \strtolower((string)\trim((string)$val));
						if((string)$val != '') {
							$projects[] = (string) $val; // TODO: validate project name from configs !!
							break; // stop after 1st
						} //end if
					} //end if
				} //end foreach
			} //end if
		} //end if
		//--
		return (array) $projects;
		//--
	} //END FUNCTION


	private function formatTranslList(?array $data) {
		//--
		$transl = [];
		//--
		if(\is_array($data) AND (\Smart::array_type_test($data) == 2)) { // req. associative array
			if(isset($data['transl']) AND \is_array($data['transl'])) {
				foreach($data['transl'] as $key => $val) {
					if(\SmartTextTranslations::validateLanguage($key)) {
						if((string)$key != (string)\SmartTextTranslations::getDefaultLanguage()) {
							if(\Smart::is_nscalar($val)) {
								$val = (string) \trim((string)$val);
								if((string)$val != '') {
									$transl[(string)$key] = (string) $val;
									break; // stop after 1st
								} //end if
							} //end if
						} //end if
					} //end if
				} //end foreach
			} //end if
		} //end if
		//--
		return (array) $transl;
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
