<?php
// Controller: DnAdmin.Mongodb
// Route: admin.php?/page/db-admin.mongodb (admin.php?page=db-admin.mongodb)
// (c) 2006-2020 unix-world.org - all rights reserved
// r.5.7.2 / smart.framework.v.5.7

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
		$error = '';
		try {
			$count = (int) \SmartModDataModel\DbAdmin\MongoDbAdmin::getRecordsCount((array)$query);
		} catch(Exception $e) {
			$error = (string) $e->getMessage();
			$query = [];
			$count = (int) \SmartModDataModel\DbAdmin\MongoDbAdmin::getRecordsCount((array)$query);
		} //end try catch
		$time = microtime(true);
		$data = (array) \SmartModDataModel\DbAdmin\MongoDbAdmin::getRecordsData((array)$query, (int)$ofs, (int)$limit, (array)$sorting);
		$time = microtime(true) - $time;
		//--
		$records = [];
		for($i=0; $i<Smart::array_size($data); $i++) {
			//--
			if((string)$data[$i]['_id'] != '') {
				$tmp_arr = (array) $data[$i];
				unset($tmp_arr['_id']);
				$records[] = [
					'_id' 	=> (string) $data[$i]['_id'],
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
		$navbox_url = (string) Smart::url_add_params('admin.php?page='.$this->ControllerGetParam('controller'), (array)$arr_url_ok_params);
		$arr_url_ok_params = [];
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'DB Admin :: MongoDB',
			'main'  => (string) SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'mongodb-list.mtpl.htm',
				[
					'QMODE' 			=> (string) $mode, // raw | visual
					'LANG' 				=> (string) $this->ControllerGetParam('lang'), // codeMirror
					'CSS-THEME' 		=> (string) 'github', // highlightJs
					'PAGE-URL' 			=> (string) 'admin.php?page='.$this->ControllerGetParam('controller'),
					'DATABASE' 			=> (string) \SmartModDataModel\DbAdmin\MongoDbAdmin::getDbName(),
					'COLLECTION' 		=> 'installer_log',
					'EXECUTION-TIME' 	=> (string) Smart::format_number_dec($time, 10, '.', ''),
					'ERROR' 			=> (string) $error,
					'QUERY' 			=> (string) (Smart::array_size($query_) > 0) ? Smart::json_encode((array)$query_, true, true, false) : '{'."\n\n".'}',
					'SORT-MAX' 			=> (int) $sort_max,
					'OFFSET' 			=> (int) (ceil((int)$ofs / (int)$limit) + 1),
					'PAGES' 			=> (int) ceil((int)$count / (int)$limit),
					'FILTER-ID_' 		=> (string) $id_,
					'SORTING' 			=> (array) $html_sorting,
					'NAV-PAGER-HTML' 	=> SmartViewHtmlHelpers::html_navpager(
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
					'NUM-RECORDS' 		=> (int) Smart::array_size($records),
					'RECORDS' 			=> (array) $records
				]
			)
		]);
		//--

	} //END FUNCTION

} //END CLASS


// end of php code
