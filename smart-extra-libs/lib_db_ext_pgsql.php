<?php
// [LIB - Smart.Framework.Modules / ExtraLibs / Extended PgSQL Database Client (Abstract)]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.8.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// Smart-Framework - Extended PostgreSQL Database Client (Abstract)
// DEPENDS:
//	* Smart::
//	* SmartUnicode::
//	* SmartUtils::
//	* SmartPgsqlExtDb->
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Abstract Class: SmartAbstractPgsqlExtDb - provides a basic Extended PostgreSQL DB Server Client that can be used with custom made connections.
 *
 * This class is based and extended from SmartPgsqlExtDb.
 * It should be extended further ...
 *
 * @usage 		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		needs to be extended and a constructor to be defined to init this class as: $this->initConnection('pgsql-custom');
 *
 * @depends 	extensions: PHP PostgreSQL ; classes: Smart, SmartUnicode, SmartUtils, SmartPgsqlExtDb
 * @version 	v.20200121
 * @package 	development:extralibs:Database
 *
 */
abstract class SmartAbstractPgsqlExtDb {

	// ->

	private $pgsql = null;
	private $configs = array();


	abstract public function __construct();
	/*
	{
		//--
		$this->initConnection('pgsql-custom'); // set connection using settings from configs section: pgsql-custom
		//--
	} //END FUNCTION
	*/


	final public function __destruct() {
		//--
		// must not be used
		//--
	} //END FUNCTION


	final protected function initConnection($cfg_pgsql_area) {
		//--
		if($this->pgsql !== null) {
			Smart::log_warning('WARNING: The '.__CLASS__.'->'.__FUNCTION__.'() was already initialized !');
			return;
		} //end if
		//--
		$this->configs = (array) Smart::get_from_config((string)$cfg_pgsql_area);
		if(Smart::array_size($this->configs) <= 0) {
			Smart::raise_error(__CLASS__.' :: No Connection Params Defined in Config for PgSQL-Area: '.$cfg_pgsql_area);
			die('');
			return;
		} //end if
		//--
		$this->pgsql = new SmartPgsqlExtDb((array)$this->configs);
		//--
	} //END FUNCTION


	final public function getConfig() {
		//--
		return (array) $this->configs;
		//--
	} //END FUNCTION


	final public function getConnection() {
		//--
		return $this->pgsql;
		//--
	} //END FUNCTION


	final public function startTransaction() {
		//--
		return $this->getConnection()->write_data('BEGIN');
		//--
	} //END FUNCTION


	final public function commitTransaction() {
		//--
		return $this->getConnection()->write_data('COMMIT');
		//--
	} //END FUNCTION


	final public function rollbackTransaction() {
		//--
		return $this->getConnection()->write_data('ROLLBACK');
		//--
	} //END FUNCTION


	//=====


	final public function getOneByKeyTableSchema($schema, $table, $field, $value, $fields=[], $where='') {
		//--
		$replacements = '';
		//--
		if(is_array($where)) { // {{{SYNC-PG-EXT-WHERE-BUILD-UP}}}
			$tmp_where = (array) $this->parseArrWhere($where);
			$where = (string) $tmp_where['where']; // string
			$replacements = $tmp_where['replacements']; // mixed
			unset($tmp_where);
		} //end if # End Sync
		//--
		if((string)$where != '') {
			$where = ' AND ('.$where.')'; // a condition already exists here so there is a WHERE ; we add extra conditions with AND()
		} //end if
		//--
		return (array) $this->getConnection()->read_asdata(
			'SELECT '.$this->parseArrFieldsToSqlSelectStatement((array)$fields).' FROM '.$this->getConnection()->escape_identifier((string)$schema).'.'.$this->getConnection()->escape_identifier((string)$table).' WHERE (('.$this->getConnection()->escape_identifier((string)$field).' = '.$this->getConnection()->escape_literal((string)$value).')'.$where.') LIMIT 1 OFFSET 0',
			$replacements
		);
		//--
	} //END FUNCTION


	final public function getManyByConditionTableSchema($schema, $table, $where, $limit, $offset, $fields=[], $orderby=[]) {
		//--
		$limit  = (int) $limit;
		if($limit < 1) {
			$limit = 1;
		} //end if
		if($limit > 100000) { // hard limit 100k (don't allow get more than this)
			$limit = 100000;
		} //end if
		//--
		$offset = (int) $offset;
		if($offset < 0) {
			$offset = 0;
		} //end if
		//--
		$order = '';
		$ord_by = [];
		if(Smart::array_size($orderby) > 0) {
			foreach($orderby as $key => $val) {
				$key = (string) trim((string)$key);
				$escape = true;
				if(is_array($val)) {
					if($val['expr'] === true) {
						$escape = false;
					} //end if
					$val = (string) strtoupper(trim((string)$val['order']));
				} else {
					$val = (string) strtoupper(trim((string)$val));
				} //end if else
				if((string)$key != '') {
					if($escape !== false) {
						$key = (string) $this->getConnection()->escape_identifier((string)$key);
					} //end if
					if((string)$val == 'ASC') {
						$ord_by[] = (string) $key.' ASC';
					} elseif((string)$val == 'DESC') {
						$ord_by[] = (string) $key.' DESC';
					} else {
						Smart::log_warning('Invalid Order Syntax in '.__CLASS__.'->'.__FUNCTION__.'() # Table: '.$schema.'.'.$table.' @ Order-By: '.print_r($orderby,1));
					} //end if else
				} //end if
			} //end foreach
			if(Smart::array_size($ord_by) > 0) {
				$order .= ' ORDER BY '.implode(', ', (array)$ord_by);
			} //end if
		} //end if
		//--
		$replacements = '';
		//--
		if(is_array($where)) { // {{{SYNC-PG-EXT-WHERE-BUILD-UP}}}
			$tmp_where = (array) $this->parseArrWhere($where);
			$where = (string) $tmp_where['where']; // string
			$replacements = $tmp_where['replacements']; // mixed
			unset($tmp_where);
		} //end if # End Sync
		//--
		if((string)$where != '') {
			$where = ' WHERE ('.$where.')';
		} //end if
		//--
		return (array) $this->getConnection()->read_adata(
			'SELECT '.$this->parseArrFieldsToSqlSelectStatement((array)$fields).' FROM '.$this->getConnection()->escape_identifier((string)$schema).'.'.$this->getConnection()->escape_identifier((string)$table).$where.$order.' LIMIT '.(int)$limit.' OFFSET '.(int)$offset,
			$replacements
		);
		//--
	} //END FUNCTION


	final public function getCountByConditionTableSchema($schema, $table, $where) {
		//--
		$replacements = '';
		//--
		if(is_array($where)) { // {{{SYNC-PG-EXT-WHERE-BUILD-UP}}}
			$tmp_where = (array) $this->parseArrWhere($where);
			$where = (string) $tmp_where['where']; // string
			$replacements = $tmp_where['replacements']; // mixed
			unset($tmp_where);
		} //end if # End Sync
		//--
		if((string)$where != '') {
			$where = ' WHERE ('.$where.')';
		} //end if
		//--
		return (int) $this->getConnection()->count_data(
			'SELECT COUNT(1) FROM '.$this->getConnection()->escape_identifier((string)$schema).'.'.$this->getConnection()->escape_identifier((string)$table).$where,
			$replacements
		);
		//--
	} //END FUNCTION


	//=====


	final protected function parseArrWhere($where) {
		//--
		$replacements = '';
		//--
		if(is_array($where)) {
			$tmp_where = (array) $where;
			$where = (string) trim((string)$tmp_where[0]);
			if((string)$where != '') {
				if((Smart::array_size($tmp_where[1]) > 0) AND (Smart::array_type_test($tmp_where[1]) == 1)) { // array must be non-associative and have at least one element
					$replacements = (array) $tmp_where[1];
				} //end if
			} //end if
			unset($tmp_where);
		} //end if
		//--
		return [
			'where' => (string) $where, 		// string
			'replacements' => $replacements 	// mixed: array or string
		];
		//--
	} //END FUNCTION


	final protected function parseArrFieldsToSqlSelectStatement($fields) {
		//--
		if(Smart::array_size((array)$fields) <= 0) {
			return '*'; // default
		} //end if
		//--
		$arr = [];
		//--
		foreach((array)$fields as $key => $val) {
			if(is_int($key)) {
				$val = (string) trim((string)$val);
				if((string)$val != '') {
					$val = (string) $this->getConnection()->escape_identifier((string)$val);
					$arr[] = $val;
				} //end if
			} else {
				$key = (string) trim((string)$key);
				if((string)$key != '') {
					if($val !== true) { // if true, it is an expression
						$key = (string) $this->getConnection()->escape_identifier((string)$key);
					} //end if else
					$arr[] = $key;
				} //end if
			} //end if else
		} //end foreach
		//--
		if(Smart::array_size($arr) <= 0) {
			return '*'; // default
		} //end if
		//--
		return (string) implode(', ', (array)$arr);
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
