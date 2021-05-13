<?php
// Class: \SmartModDataModel\TranslRepo\PgTranslRepoMetainfo
// (c) 2019-2021 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

namespace SmartModDataModel\TranslRepo;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//-- Model: PgSQL / TranslRepo.Metainfo [PHP8]


final class PgTranslRepoMetainfo extends \SmartModDataModel\TranslRepo\PgDbTranslRepo {

	// ->


	public function clearAllData() {
		//--
		$wr = (array) $this->getConnection()->write_data(
			'TRUNCATE TABLE "transl_repo"."metainfo"'
		);
		//--
		return (int) $wr[1];
		//--
	} //END FUNCTION


	public function getOneByUniqueKey(?string $field, ?string $value) {
		//--
		return (array) $this->getOneByKeyTableSchema('transl_repo', 'metainfo', $field, $value, [], '');
		//--
	} //END FUNCTION


	public function getAll() {
		//--
		return (array) $this->getConnection()->read_adata(
			'SELECT "id", "val" FROM "transl_repo"."metainfo" WHERE ("id" != \'errors-json\') ORDER BY "id" ASC'
		);
		//--
	} //END FUNCTION


	public function insertOne(?string $id, ?string $val) {
		//--
		$wr = (array) $this->getConnection()->write_data(
			'INSERT INTO "transl_repo"."metainfo" '.$this->getConnection()->prepare_statement(
				[
					'id' 			=> (string) $id,
					'val' 			=> (string) $val
				],
				'insert'
			)
		);
		//--
		return (int) $wr[1];
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
