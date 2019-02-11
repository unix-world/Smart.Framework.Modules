<?php

die('Uncomment this line to enable this demo test ...');

ini_set( 'date.timezone', 'UTC' );
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

/**
 * Class: Flexigrid Test (This is just a sample ...)
 *
 * @access 		private
 * @internal
 *
 */
class TestFlexigrid {


//==================================================================
public static function test_sqlite3_json_flexigrid($flexigrid_page, $flexigrid_resperpage, $flexigrid_sortfld, $flexigrid_sortdir, $flexigrid_srcby, $flexigrid_search, $flexigrid_xsrcby, $flexigrid_xsearch) {

	//-- ensure the correct types
	$flexigrid_page = (int) $flexigrid_page;
	$flexigrid_resperpage = (int) $flexigrid_resperpage;
	$flexigrid_srcby = (string) $flexigrid_srcby;
	$flexigrid_search = (string) $flexigrid_search;
	$flexigrid_xsrcby = (string) $flexigrid_xsrcby;
	$flexigrid_xsearch = (string) $flexigrid_xsearch;
	$flexigrid_sortfld = (string) $flexigrid_sortfld;
	$flexigrid_sortdir = (string) $flexigrid_sortdir;
	//--

	//-- db init
	$model = new SmartTestSQLite3Model();
	if(!is_object($model)) {
		return Smart::json_encode(array('error' => 'Flexigrid JSON DB Object Failed to initialize'));
	} //end if
	//--

	//-- where management (we have 2 situations: 1st is normal search ; 2nd is search by starting letter)
	if(((string)$flexigrid_srcby != '') AND ((string)$flexigrid_search != '')) {
		//-- normal search (by field and value)
		$where_field = $flexigrid_srcby;
		$where_value = $flexigrid_search;
		$where_mode = '';
		//--
	} else {
		//-- search by starting letters
		$where_field = $flexigrid_xsrcby;
		$where_value = $flexigrid_xsearch;
		$where_mode = 'letters';
		//--
	} //end if else
	//--

	//-- output data init
	$jsonData = array(
		'page' => $flexigrid_page,
		'total' => $model->table_flexigrid_count($where_field, $where_value, $where_mode), // count total result (can be different if no search)
		'rows' => array()
	);
	//--

	//-- read data
	$rows = $model->table_flexigrid_read($where_field, $where_value, $where_mode, $flexigrid_sortfld, $flexigrid_sortdir, $flexigrid_resperpage, $flexigrid_page);
	//--
	if(is_array($rows)) {
		foreach($rows AS $row) {
			//--
			$entry = array(
					'id' => 'ID_'.$row['iso'],
					'cell' => array(
						'iso' => $row['iso'],
						'name' => $row['name'],
						'printable_name' => $row['printable_name'],
						'iso3' => $row['iso3'],
						'numcode' => $row['numcode']
					),
			);
			//--
			$jsonData['rows'][] = $entry;
			//--
		} //end foreach
	} //end if
	//--

	//--
	return Smart::json_encode($jsonData);
	//--

} //END FUNCTION
//==================================================================


//============================================================
public function table_flexigrid_count($where_field, $where_value, $where_mode) {

	//--
	$query = 'SELECT COUNT(1) FROM "table_flexigrid_sample"';
	//-- where
	$where = $this->table_flexigrid_build_where($where_field, $where_value, $where_mode);
	if((string)$where != '') {
		$query .= ' '.$where;
	} //end if
	//--

	//--
	return (int) $this->db->count_data($query);
	//--

} //END FUNCTION
//============================================================


//============================================================
public function table_flexigrid_read($where_field, $where_value, $where_mode, $sort_field, $sort_direction, $results_per_page, $offset_page) {

	//--
	$query = 'SELECT * FROM "table_flexigrid_sample"';
	//-- where
	$where = $this->table_flexigrid_build_where($where_field, $where_value, $where_mode);
	if((string)$where != '') {
		$query .= ' '.$where;
	} //end if
	//-- sort
	$sort = 'ORDER BY "iso" ASC'; // default sort
	switch((string)$sort_field) {
		case 'name':
		case 'iso':
		case 'iso3':
		case 'numcode':
			if((string)$sort_direction == 'desc') {
				$sort = 'ORDER BY "'.$sort_field.'" DESC';
			} else {
				$sort = 'ORDER BY "'.$sort_field.'" ASC';
			} //end if
			break;
		default:
			// nothing (invalid field)
	} //end switch
	$query .= ' '.$sort;
	//-- limit offset
	if($results_per_page <= 0) {
		$results_per_page = 10; // default
	} //end if
	if($offset_page <= 0) {
		$offset_page = 1;
	} //end if
	$query .= ' LIMIT '.((int)$results_per_page).' OFFSET '.((int) $results_per_page * ($offset_page - 1));
	//--

	//--
	$this->db->read_asdata('SELECT * FROM "table_flexigrid_sample" LIMIT 1 OFFSET 0'); // just for test
	//--
	return (array) $this->db->read_adata($query);
	//--

} //END FUNCTION
//============================================================


//============================================================
private function table_flexigrid_build_where($where_field, $where_value, $where_mode) {

	//--
	$where = '';
	//--
	if($where_mode == 'letters') { // search by starting letter
		switch((string)$where_field) {
			case 'name': // for letters
			case 'iso':
			case 'iso3':
			case 'numcode':
				if((strlen($where_value) > 0) AND ((string)$where_value != '#')) {
					$where = 'WHERE "'.$where_field.'" LIKE \''.$this->db->escape_str($where_value).'%\'';
				} //end if
				break;
			default:
				// nothing
		} //end switch
	} else { // normal search by field and value
		switch((string)$where_field) {
			case 'name':
				if((strlen($where_value) > 0) AND ((string)$where_value != '#')) {
					$where = 'WHERE "'.$where_field.'" LIKE \'%'.$this->db->escape_str($where_value).'%\'';
				} //end if
				break;
			case 'iso':
			case 'iso3':
			case 'numcode':
				if(strlen($where_value) > 0) {
					$where = 'WHERE "'.$where_field.'" LIKE \''.$this->db->escape_str($where_value).'\'';
				} //end if
				break;
			default:
				// nothing
		} //end switch
	} //end if
	//--

	//--
	return (string) $where;
	//--

} //END FUNCTION
//============================================================


} //END CLASS

?>