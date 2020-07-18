<?php
// Controller: DnAdmin.Mongodb
// Route: admin.php?/page/db-admin.mongodb (admin.php?page=db-admin.mongodb)
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // INDEX, ADMIN, SHARED

/**
 * Admin Controller
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAbstractAppController {

	public function Run() {

		//--
		if(Smart::array_size(Smart::get_from_config('mongodb')) <= 0) {
			$this->PageViewSetErrorStatus(500, 'MongoDB Server: Not Configured ...');
			return;
		} //end if
		//--

		//--
		$the_base_url = 'admin.php?page='.$this->ControllerGetParam('controller');
		$the_cookiename_collection = 'SmartDbAdminMongoCollection';
		//--

		//--
		$the_collection = (string) trim((string)$this->CookieVarGet((string)$the_cookiename_collection));
		//--
		$collections_list = (array) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbCollections();
		$collection_exists = null;
		if((string)$the_collection != '') {
			$collection_exists = false;
			for($i=0; $i<Smart::array_size($collections_list); $i++) {
				if(is_array($collections_list[$i])) {
					if((string)$collections_list[$i]['name'] === (string)$the_collection) {
						$collection_exists = true;
						break;
					} //end if
				} //end if
			} //end for
		} //end if
		//--
	//	if($collection_exists !== true) {
	//		$the_collection = '';
	//	} //end if
		//--

		//--
		if((string)$the_collection == '') {
			$build_info = (array) \SmartModDataModel\DbAdmin\MongoDbAdmin::getServerBuildInfo();
			if(Smart::array_size($build_info) <= 0) {
				$this->PageViewSetErrorStatus(500, 'MongoDB Server: Cannot Get Build Info ...');
				return;
			} //end if
			$this->PageViewSetVars([
				'title' => 'DB Admin :: MongoDB',
				'main'  => (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'mongodb-buildinfo.mtpl.htm',
					[
						'DATABASE' 				=> (string) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbName(),
						'HOST' 					=> (string) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbHost(),
						'PORT' 					=> (string) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbPort(),
						'COLLECTION' 			=> (string) '', // this must be empty here !
						'COLLECTIONS' 			=> (array)  $collections_list,
						'BUILD-INFO' 			=> (string) SmartUtils::pretty_print_var($build_info),
						'PAGE-LIST-URL' 		=> (string) $the_base_url,
						'COOKIENAME-COLLECTION' => (string) $the_cookiename_collection
					]
				)
			]);
			return;
		} //end if
		//--

		//--
		$collection_indexes = [];
		if($collection_exists === true) {
			$tmp_indexes = (array) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbCollectionIndexes((string)$the_collection);
			for($i=0; $i<Smart::array_size($tmp_indexes); $i++) {
				if(Smart::array_size($tmp_indexes[$i]) > 0) {
					$collection_indexes[(string)$tmp_indexes[$i]['name']] = (array) $tmp_indexes[$i]['key'];
				} //end if
			} //end for
			$tmp_indexes = array();
		} //end if
		//--

		//--
		$mode = $this->RequestVarGet('mode', 'raw', ['raw','visual']);
		//--
		$ofs = (int) $this->RequestVarGet('ofs', 0, 'integer+');
		//--
		$limit = 10;
		//--
		$id_ = (string) trim((string)$this->RequestVarGet('id_', '', 'string'));
		$query_ = Smart::json_decode((string)trim((string)$this->RequestVarGet('query_', '', 'string')));
		//--
		$query = [];
		if($id_) {
			$query = [ '_id' => (string) $id_ ];
			$query_ = [];
		} elseif(Smart::array_size($query_) > 0) {
			$query = (array) $query_;
		} else {
			$query_ = [];
		} //end if else
		//--
		$sorting = $this->RequestVarGet('sorting', [], 'array');
		if(Smart::array_type_test($sorting) != 2) {
			$sorting = [];
		} //end if
		if(Smart::array_size($sorting) <= 0) {
			$sorting = [
				'_id' => 'ASC'
			];
		} //end if
		//--

		//--
		$query = (array) $this->convertQueryToRealMongoId((array)$query);
		//--

		//--
		$error = [];
		try {
			$count = (int) \SmartModDataModel\DbAdmin\MongoDbAdmin::getRecordsCount((string)$the_collection, (array)$query);
		} catch(Exception $e) {
			$error[] = (string) $e->getMessage();
			$query = [];
			$count = 0;
		} //end try catch
		$time = microtime(true);
		try {
			$data = (array) \SmartModDataModel\DbAdmin\MongoDbAdmin::getRecordsData((string)$the_collection, (array)$query, (int)$ofs, (int)$limit, (array)$sorting);
		} catch(Exception $e) {
			$error[] = (string) $e->getMessage();
			$query = [];
			$data = [];
		} //end try catch
		$time = microtime(true) - $time;
		//--
		$records = [];
		for($i=0; $i<Smart::array_size($data); $i++) {
			//--
			if(is_array($data[$i]['_id'])) {
				$data[$i]['_id'] = (string) 'ObjectId('.$data[$i]['_id']['$oid'].')';
			} //end if
			//--
			if((string)$data[$i]['_id'] != '') {
				$tmp_arr = (array) $data[$i];
				unset($tmp_arr['_id']);
				$records[] = [
					'_id' 	=> (string) $data[$i]['_id'],
					'-id' 	=> (string) $data[$i]['id'],
					'-num' 	=> (int) ((int)$i + 1 + (int)$ofs),
					'-json' => (string) Smart::json_encode((array)$tmp_arr)
				];
				$tmp_arr = array();
			} //end if
			//--
		} //end if
		//--

		//--
		$ascdesc = [ 'ASC' => 'ASC', 'DESC' => 'DESC' ];
		//--
		$html_sorting = [];
		$i = 0;
		foreach($sorting as $key => $val) {
			$key = (string) trim((string)$key);
			$val = (string) strtoupper((string)trim((string)$val));
			if(!in_array($val, array_values($ascdesc))) {
				$val = 'ASC';
			} //end if
			if((string)$key != '') {
				$html_sorting[] = [
					'id-field' => (string) $key,
					'html-field' => (string) SmartViewHtmlHelpers::html_select_list_single('sort-m'.(int)$i, (string)$val, 'form', (array)$ascdesc, 'sort[m'.(int)$i.']', '70/0', '', 'no', 'no', 'class:filter-direction')
				];
				$i++;
			} //end if
		} //end foreach
		//--
		$sort_size = (int) Smart::array_size($html_sorting);
		$sort_max = 6;
		for($i=$sort_size; $i<$sort_max; $i++) {
			$html_sorting[] = [
				'id-field' => '',
				'html-field' => (string) SmartViewHtmlHelpers::html_select_list_single('sort-m'.(int)$i, 'ASC', 'form', (array)$ascdesc, 'sort[m'.(int)$i.']', '70/0', '', 'no', 'no', 'class:filter-direction')
			];
		} //end for
		//--

		//--
		$arr_url_params = (array) $this->RequestVarsGet();
		$arr_url_ok_params = [];
		foreach($arr_url_params as $key => $val) {
			switch(strtolower((string)trim((string)$key))) {
				case '':
				case 'page':
				case 'ofs':
					// skip
					break;
				default:
					$arr_url_ok_params[(string)$key] = $val;
			} //end if
		} //end if
		$arr_url_params = [];
		$arr_url_ok_params['ofs'] = '{{{offset}}}';
		$navbox_url = (string) Smart::url_add_params((string)$the_base_url, (array)$arr_url_ok_params);
		$arr_url_ok_params = [];
		//--

		//--
		$num_pages = ceil((int)$count / (int)$limit);
		if($num_pages <= 0) {
			$num_pages = 1;
		} //end if
		//--
		$this->PageViewSetVars([
			'title' => 'DB Admin :: MongoDB',
			'main'  => (string) SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'mongodb-list.mtpl.htm',
				[
					'QMODE' 				=> (string) $mode, // raw | visual
					'LANG' 					=> (string) $this->ControllerGetParam('lang'), // codeMirror
					'CODEED-PREFIX-URL' 	=> (string) '',
					'HLJS-PREFIX-URL' 		=> (string) '',
					'CSS-THEME' 			=> (string) 'github', // highlightJs
					'PAGE-URL' 				=> (string) $the_base_url,
					'COOKIENAME-COLLECTION' => (string) $the_cookiename_collection,
					'HOST' 					=> (string) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbHost(),
					'PORT' 					=> (string) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbPort(),
					'DATABASE' 				=> (string) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbName(),
					'COLLECTIONS' 			=> (array)  $collections_list,
					'COLLECTION' 			=> (string) $the_collection,
					'COLLINDEXES' 			=> (string) SmartUtils::pretty_print_var($collection_indexes),
					'EXECUTION-TIME' 		=> (string) Smart::format_number_dec($time, 10, '.', ''),
					'ERROR' 				=> (string) implode("\n", (array)$error),
					'QUERY' 				=> (string) (Smart::array_size($query_) > 0) ? Smart::json_encode((array)$query_, true, true, false) : '{'."\n\n".'}',
					'SORT-MAX' 				=> (int)    $sort_max,
					'LIMIT-PER-PAGE' 		=> (int)    $limit,
					'OFFSET' 				=> (int)    (ceil((int)$ofs / (int)$limit) + 1),
					'PAGES' 				=> (int)    $num_pages,
					'TOTAL-RECORDS' 		=> (int)    $count,
					'FILTER-ID_' 			=> (string) $id_,
					'SORTING' 				=> (array)  $html_sorting,
					'NAV-PAGER-HTML' 		=> (string) SmartViewHtmlHelpers::html_navpager(
						(string) $navbox_url,
						(int) $count,
						(int) $limit,
						(int) $ofs,
						false,
						5,
						[
							'show-first' => true,
							'show-last' => true
						]
					),
					'NUM-RECORDS' 			=> (int) Smart::array_size($records),
					'RECORDS' 				=> (array) $records
				]
			)
		]);
		//--

	} //END FUNCTION


	private function convertQueryToRealMongoId($query, $level=0) {
		//--
		$level = (int) $level;
		if($level < 0) {
			return array();
		} //end if
		//--
		if(!is_array($query)) {
			return array();
		} //end if
		//--
		foreach($query as $key => $val) {
			if(((string)$key == '_id') OR ($level > 0)) {
				if(is_array($val)) {
					$query[(string)$key] = $this->convertQueryToRealMongoId($val, $level+1);
				} else {
					$query[(string)$key] = \SmartModDataModel\DbAdmin\MongoDbAdmin::getRealMongoId($val);
				} //end if
				break;
			} //end if
		} //end foreach
		//--
		return (array) $query;
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
