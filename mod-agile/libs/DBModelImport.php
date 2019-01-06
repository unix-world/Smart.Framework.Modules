<?php
// Module Lib: \SmartModExtLib\Agile\DBModelImport
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

namespace SmartModExtLib\Agile;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


class DBModelImport {

	// ::
	// v.181115

public static function SmartDbModelerPgsqlExportToXml($y_host, $y_port, $y_dbname, $y_username, $y_pass, $y_schema='') {

	$conn = pg_connect('host='.$y_host.' port='.(int)$y_port.' dbname='.$y_dbname.' user='.$y_username.' password='.$y_pass.' connect_timeout=30');
	if(!$conn){
		throw new \Exception('ERROR: Failed to connect to: '.'host='.$y_host.' port='.(int)$y_port.' dbname='.$y_dbname.' user='.$y_username.' password=***** connect_timeout=30');
		return '';
	} //end if

	$xml = '';
	$arr = array();

	//@ $datatypes = file('dbmodel/postgresql/datatypes.xml');
	//$arr[] = $datatypes[0];
	$arr[] = '<sql db="postgresql">';
	$arr[] = '<meta-info>';
	$arr[] = '<server>'.\Smart::escape_html($y_host).':'.(int)$y_port.'</server>';
	$arr[] = '<db>'.\Smart::escape_html($y_dbname).'</db>';
	$arr[] = '<schema>'.\Smart::escape_html($y_schema).'</schema>';
	$arr[] = '</meta-info>';
	//for($i=1;$i<count($datatypes);$i++) {
	//	$arr[] = $datatypes[$i];
	//} //end for

	// in Postgresql comments are not stored in the ANSI information_schema (compliant to the standard);
	// so we will need to access the pg_catalog and may as well get the table names at the same time.
	/*	$qstr = "
			SELECT 	relname as table_name,
					c.oid as table_oid,
					(SELECT pg_catalog.obj_description(c.oid, 'pg_class')) as comment
			FROM pg_catalog.pg_class c
			WHERE c.relname !~ '^(pg_|sql_)' AND relkind = 'r'
			ORDER BY table_name;
	;"; */
$qstr = <<<'SQL'
SELECT pn.nspname AS schema_name, pc.relname AS table_name, pc.oid AS table_oid,
COALESCE((SELECT pg_catalog.obj_description(pc.oid, 'pg_class')), '') as comment
FROM pg_catalog.pg_class pc, pg_catalog.pg_namespace pn
WHERE
pc.relnamespace = pn.oid
AND pc.relkind = 'r'
AND (pn.nspname NOT LIKE $_PATERN_$pg_%$_PATERN_$) AND (pn.nspname NOT LIKE $_PATERN_$sql_%$_PATERN_$) AND (pn.nspname != 'information_schema')
AND (pc.relname NOT LIKE $_PATERN_$pg_%$_PATERN_$) AND (pc.relname NOT LIKE $_PATERN_$sql_%$_PATERN_$) AND (pc.relname != 'information_schema')
ORDER BY pn.nspname ASC, pc.relname ASC, pc.oid ASC
;
SQL;

	// list schema: SELECT 'SCHEMA' AS type, oid, NULL AS schemaname, NULL AS relname, nspname AS name FROM pg_catalog.pg_namespace pn WHERE pn.nspname NOT LIKE $_PATERN_$pg_%$_PATERN_$ AND pn.nspname != 'information_schema'
	$result = pg_query($conn, $qstr);
	if(!$result) {
		throw new \Exception(__METHOD__.' A query failed: '.$qstr);
		return '';
	} //end if
	while($row = pg_fetch_array($result)) {

		$tblschema = $row['schema_name'];
		if($y_schema && ((string)$y_schema != (string)$tblschema)) {
			continue;
		} //end if
		$table = $row['table_name'];
		$table_oid = $row['table_oid'];
		$xml .= '<table name="'.\Smart::escape_html($tblschema.'.'.$table).'">';
		//$xml .= '<table name="'.\Smart::escape_html($table).'">';
		$comment = (isset($row['comment']) ? $row['comment'] : "");
		if($comment) {
			$xml .= '<comment>'.\Smart::escape_html($comment).'</comment>';
		} //end if
		/*	$qstr = "
			SELECT *, col_description(".$table_oid.",ordinal_position) as column_comment
			FROM information_schema.columns
			WHERE table_name = '".$table."' AND table_schema = '".$tblschema."'
			ORDER BY ordinal_position
		;"; */
$qstr = <<<SQL
SELECT *, col_description({$table_oid},ordinal_position) as column_comment
	FROM information_schema.columns
	WHERE
		table_name = '{$table}'
		AND table_schema = '{$tblschema}'
	ORDER BY ordinal_position
;
SQL;

		$result2 = pg_query($conn, $qstr);
		if(!$result2) {
			throw new \Exception(__METHOD__.' A query failed: '.$qstr);
			return '';
		} //end if
		while($row = pg_fetch_array($result2)) {
			//print_r($row);
			$name = $row['column_name'];
			$type = $row['data_type'];		// maybe use "udt_name" instead to consider user types
			if((string)$row['character_maximum_length']) {
				$type .= '('.$row['character_maximum_length'].')';
			} elseif(((string)strtoupper((string)$type) == 'NUMERIC') AND (((string)$row['numeric_precision'] != '') OR ((string)$row['numeric_scale'] != ''))) {
				$type .= '('.(int)$row['numeric_precision'].','.(int)$row['numeric_scale'].')';
			} //end if
			$comment = (isset($row['column_comment']) ? $row['column_comment'] : "");
			$null = ($row['is_nullable'] == "YES" ? "1" : "0");
			$def = $row['column_default'];
			$ai = '0'; // $ai:autoincrement... Not in postgresql, but there are serial classes as nextval
			if(stripos((string)trim((string)strtolower((string)$def)), 'nextval(') === 0) {
				$ai = '1'; // just for info ...
			} //end if
			if($def == "NULL") {
				$def = "";
			} //end if
			$xml .= '<row name="'.\Smart::escape_html($name).'" null="'.\Smart::escape_html($null).'" autoincrement="'.\Smart::escape_html($ai).'">';
			$xml .= '<datatype>'.strtoupper($type).'</datatype>';
			$xml .= '<default>'.\Smart::escape_html($def).'</default>';
			if($comment) {
				$xml .= '<comment>'.\Smart::escape_html($comment).'</comment>';
			} //end if

			/* fk constraints */
			/* $qstr = "
				SELECT 	kku.column_name,
						ccu.table_name AS references_table,
						ccu.column_name AS references_field
				FROM information_schema.table_constraints tc
				LEFT JOIN information_schema.constraint_column_usage ccu
					ON tc.constraint_name = ccu.constraint_name
				LEFT JOIN information_schema.key_column_usage kku
					ON kku.constraint_name = ccu.constraint_name
				WHERE constraint_type = 'FOREIGN KEY'
					AND kku.table_name = '".$table."' AND kku.table_schema = '".$tblschema."'
					AND kku.column_name = '".$name."'
			;"; */
$qstr = <<<SQL
SELECT 	kku.column_name, ccu.table_name AS references_table, ccu.column_name AS references_field
	FROM information_schema.table_constraints tc
		LEFT JOIN information_schema.constraint_column_usage ccu ON tc.constraint_name = ccu.constraint_name
		LEFT JOIN information_schema.key_column_usage kku ON kku.constraint_name = ccu.constraint_name
	WHERE
		constraint_type = 'FOREIGN KEY'
		AND kku.table_name = '{$table}' AND kku.table_schema = '{$tblschema}'
		AND kku.column_name = '{$name}'
;
SQL;
			$result3 = pg_query($conn, $qstr);
			if(!$result3) {
				throw new \Exception(__METHOD__.' A query failed: '.$qstr);
				return '';
			} //end if
			while($row = pg_fetch_array($result3)) {
				$xml .= '<relation table="'.\Smart::escape_html($tblschema.'.'.$row['references_table']).'" row="'.\Smart::escape_html($row['references_field']).'" />';
			} //end while
			$xml .= '</row>';
		} //end while

		// keys
		/* $qstr = "
			SELECT	tc.constraint_name,
					tc.constraint_type,
					kcu.column_name
			FROM information_schema.table_constraints tc
			LEFT JOIN information_schema.key_column_usage kcu
				ON tc.constraint_catalog = kcu.constraint_catalog
				AND tc.constraint_schema = kcu.constraint_schema
				AND tc.constraint_name = kcu.constraint_name
			WHERE tc.table_name = '".$table."' AND constraint_type != 'FOREIGN KEY'
			ORDER BY tc.constraint_name
		;"; */
$qstr = <<<SQL
SELECT tc.constraint_name, tc.constraint_type, kcu.column_name
	FROM information_schema.table_constraints tc
		LEFT JOIN information_schema.key_column_usage kcu ON tc.constraint_catalog = kcu.constraint_catalog AND tc.constraint_schema = kcu.constraint_schema AND tc.constraint_name = kcu.constraint_name
	WHERE
		tc.table_name = '{$table}' AND tc.table_schema = '{$tblschema}'
		AND constraint_type != 'FOREIGN KEY'
	ORDER BY tc.constraint_name
;
SQL;

		$result2 = pg_query($conn, $qstr);
		if(!$result2) {
			throw new \Exception(__METHOD__.' A query failed: '.$qstr);
			return '';
		} //end if
		$keyname1 = '';
		$reg_keys = [];
		while($row2 = pg_fetch_array($result2)) {
			if((string)$row2['constraint_type'] != "CHECK") {
				$keyname = $row2['constraint_name'];
				if((string)$keyname != (string)$keyname1) {
					if((string)$keyname1 != "") {
						$xml .= '</key>';
					} //end if
					if((string)$row2['constraint_type'] == "PRIMARY KEY") {
						$row2['constraint_type'] = "PRIMARY";
					} //end if
					$reg_keys[(string)$keyname]++;
					$xml .= '<key name="'.\Smart::escape_html($keyname).'" type="'.\Smart::escape_html($row2['constraint_type']).'">';
					$xml .= isset($row2['column_name']) ? '<part>'.\Smart::escape_html($row2['column_name']).'</part>' : '';
				} else {
					$xml .= isset($row2['column_name']) ? '<part>'.\Smart::escape_html($row2['column_name']).'</part>' : '';
				} //end if
				$keyname1 = $keyname;
			} //end if
		} //end while
		if((string)$keyname1 != "") {
			$xml .= '</key>';
		} //end if

		// index
		/* $qstr = 'SELECT pcx."relname" as "INDEX_NAME", pa."attname" as
			"COLUMN_NAME", * FROM "pg_index" pi LEFT JOIN "pg_class" pcx ON pi."indexrelid"  =
			pcx."oid" LEFT JOIN "pg_class" pci ON pi."indrelid" = pci."oid" LEFT JOIN
			"pg_attribute" pa ON pa."attrelid" = pci."oid" AND pa."attnum" = ANY(pi."indkey")
			WHERE pci."relname" = \''.$table.'\' order by pa."attnum"'; */
		$qstr = <<<SQL
SELECT
	pcx."relname" as "INDEX_NAME",
	pa."attname" as "COLUMN_NAME",
	* FROM "pg_index" pi
		LEFT JOIN "pg_class" pcx ON pi."indexrelid"  = pcx."oid"
		LEFT JOIN "pg_class" pci ON pi."indrelid" = pci."oid"
		LEFT JOIN "pg_namespace" pn ON pci."relnamespace" = pn."oid"
		LEFT JOIN "pg_attribute" pa ON pa."attrelid" = pci."oid" AND pa."attnum" = ANY(pi."indkey")
	WHERE pci."relname" = '{$table}' AND pn.nspname = '{$tblschema}'
	ORDER BY pa."attnum"
;
SQL;
		$result2 = pg_query($conn, $qstr);
		if(!$result2) {
			throw new \Exception(__METHOD__.' A query failed: '.$qstr);
			return '';
		} //end if
		$idx = array();
		while($row2 = pg_fetch_array($result2)) {
			$name = $row2['INDEX_NAME'];
			if(array_key_exists($name, $idx)) {
				$obj = $idx[$name];
			} else {
				$t = 'INDEX';
				if($row2['indisunique'] == 't') {
					$t = 'UNIQUE';
					//break;
				} //end if
				if($row2['indisprimary'] == 't') {
					$t = 'PRIMARY';
					//break;
				} //end if
				$obj = [
					'columns'	=> [],
					'type' 		=> $t
				];
			} //end if else
			$obj['columns'][] = $row2['COLUMN_NAME'];
			$idx[$name] = $obj;
		} //end while
		foreach($idx as $name => $obj) {
			if(!$reg_keys[(string)$name]) {
				$xmlkey = '<key name="'.\Smart::escape_html($name).'" type="'.\Smart::escape_html($obj['type']).'">';
				for($i=0;$i<count($obj['columns']);$i++) {
					$col = $obj['columns'][$i];
					$xmlkey .= '<part>'.\Smart::escape_html($col).'</part>';
				} //end for
				$xmlkey .= '</key>';
				$xml .= $xmlkey;
			} //end if
		} //end foreach

		$xml .= '</table>';

	} //end while

	$arr[] = $xml;
	$arr[] = '</sql>';

	return (string) implode("\n", (array)$arr);

} //END FUNCTION


public static function SmartDbModelerMySQLExportToXml($y_host, $y_port, $y_dbname, $y_username, $y_pass) {


	$conn = mysqli_init();
	mysqli_options($conn, MYSQLI_OPT_LOCAL_INFILE, false);
	if(!mysqli_real_connect($conn, (string)$y_host, (string)$y_username, (string)$y_pass, false, (int)$y_port)) {
		throw new \Exception('ERROR: Failed to connect to: '.'host='.$y_host.' port='.(int)$y_port.' dbname='.$y_dbname.' user='.$y_username.' password=*****');
		return '';
	} //end if
	if(!is_object($conn)) {
		throw new \Exception('ERROR: Connection Dropped to: '.'host='.$y_host.' port='.(int)$y_port.' dbname='.$y_dbname.' user='.$y_username.' password=*****');
		return '';
	} //end if
	if((string)$conn->thread_id == '') {
		throw new \Exception('ERROR: Connection have NO Thread ID for: '.'host='.$y_host.' port='.(int)$y_port.' dbname='.$y_dbname.' user='.$y_username.' password=*****');
		return '';
	} //end if
	mysqli_query($conn, "SET CHARACTER SET 'utf8'", MYSQLI_STORE_RESULT);
	if(@mysqli_errno($conn) !== 0) {
		throw new \Exception(__METHOD__.' Failed to Set Charset to UTF-8');
		return '';
	} //end if
	mysqli_query($conn, "SET COLLATION_CONNECTION = 'utf8_bin'", MYSQLI_STORE_RESULT);
	if(@mysqli_errno($conn) !== 0) {
		throw new \Exception(__METHOD__.' Failed to Set Collation for connection to UTF-8');
		return '';
	} //end if

	$res = mysqli_select_db($conn, 'information_schema');
	if(!$res) {
		throw new \Exception(__METHOD__.' Select DB failed for: information_schema');
		return '';
	} //end if
	$db = mysqli_real_escape_string($conn, (string)$y_dbname);

	$xml = '';

	$arr = array();
	//@ $datatypes = file('dbmodel/mysql/datatypes.xml');
	//$arr[] = $datatypes[0];
	$arr[] = '<sql db="mysql">';
	$arr[] = '<meta-info>';
	$arr[] = '<server>'.\Smart::escape_html($y_host).':'.(int)$y_port.'</server>';
	$arr[] = '<db>'.\Smart::escape_html($y_dbname).'</db>';
	$arr[] = '</meta-info>';
	//for($i=1;$i<count($datatypes);$i++) {
	//	$arr[] = $datatypes[$i];
	//} //end for

	$result = mysqli_query($conn, "SELECT * FROM TABLES WHERE TABLE_SCHEMA = '".$db."'", MYSQLI_STORE_RESULT);
	if(@mysqli_errno($conn) !== 0) {
		throw new \Exception(__METHOD__.' A query failed (1): '.@mysqli_error($conn));
		return '';
	} //end if
	while($row = mysqli_fetch_array($result)) {
		$table = $row['TABLE_NAME'];
		$xml .= '<table name="'.\Smart::escape_html($table).'">';
		$comment = (isset($row['TABLE_COMMENT']) ? $row['TABLE_COMMENT'] : '');
		if($comment) {
			$xml .= '<comment>'.\Smart::escape_html($comment).'</comment>';
		} //end if
		$q = "SELECT * FROM COLUMNS WHERE TABLE_NAME = '".mysqli_real_escape_string($conn, (string)$table)."' AND TABLE_SCHEMA = '".$db."'";
		$result2 = mysqli_query($conn, $q, MYSQLI_STORE_RESULT);
		if(@mysqli_errno($conn) !== 0) {
			throw new \Exception(__METHOD__.' A query failed (2): '.@mysqli_error($conn));
			return '';
		} //end if
		while($row = mysqli_fetch_array($result2)) {
			$name  = $row['COLUMN_NAME'];
			$type  = $row['COLUMN_TYPE'];
			$comment = (isset($row['COLUMN_COMMENT']) ? $row['COLUMN_COMMENT'] : '');
			$null = ($row['IS_NULLABLE'] == 'YES' ? '1' : '0');
			if(preg_match("/binary/i", $row['COLUMN_TYPE'])) {
				$def = bin2hex($row['COLUMN_DEFAULT']);
			} else {
				$def = $row['COLUMN_DEFAULT'];
			} //end if else
			$ai = (preg_match('/auto_increment/i', $row['EXTRA']) ? '1' : '0');
			if($def == 'NULL') {
				$def = '';
			} //end if
			$xml .= '<row name="'.\Smart::escape_html($name).'" null="'.\Smart::escape_html($null).'" autoincrement="'.\Smart::escape_html($ai).'">';
			$xml .= '<datatype>'.\Smart::escape_html(strtoupper($type)).'</datatype>';
			$xml .= '<default>'.\Smart::escape_html($def).'</default>';
			if($comment) {
				$xml .= '<comment>'.\Smart::escape_html($comment).'</comment>';
			} //end if
			// fk constraints
			$q = "SELECT
				REFERENCED_TABLE_NAME AS 'table', REFERENCED_COLUMN_NAME AS 'column'
				FROM KEY_COLUMN_USAGE k
				LEFT JOIN TABLE_CONSTRAINTS c
				ON k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
				WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
				AND c.TABLE_SCHEMA = '".$db."' AND c.TABLE_NAME = '".mysqli_real_escape_string($conn, (string)$table)."'
				AND k.COLUMN_NAME = '".mysqli_real_escape_string($conn, (string)$name)."'";
			$result3 = mysqli_query($conn, $q, MYSQLI_STORE_RESULT);
			if(@mysqli_errno($conn) !== 0) {
				throw new \Exception(__METHOD__.' A query failed (3): '.@mysqli_error($conn));
				return '';
			} //end if
			while($row = mysqli_fetch_array($result3)) {
				$xml .= '<relation table="'.\Smart::escape_html($row["table"]).'" row="'.\Smart::escape_html($row["column"]).'" />';
			} //end while
			$xml .= '</row>';
		} //end while
		// keys
		$q = "SELECT * FROM STATISTICS WHERE TABLE_NAME = '".mysqli_real_escape_string($conn, (string)$table)."' AND TABLE_SCHEMA = '".$db."' ORDER BY SEQ_IN_INDEX ASC";
		$result2 = mysqli_query($conn, $q, MYSQLI_STORE_RESULT);
		if(@mysqli_errno($conn) !== 0) {
			throw new \Exception(__METHOD__.' A query failed (4): '.@mysqli_error($conn));
			return '';
		} //end if
		$idx = array();
		while($row = mysqli_fetch_array($result2)) {
			$name = $row['INDEX_NAME'];
			if(array_key_exists($name, $idx)) {
				$obj = $idx[$name];
			} else {
				$type = $row['INDEX_TYPE'];
				$t = 'INDEX';
				if($type == 'FULLTEXT') {
					$t = $type;
				} //end if
				if($row['NON_UNIQUE'] == '0') {
					$t = 'UNIQUE';
				} //end if
				if($name == 'PRIMARY') {
					$t = 'PRIMARY';
				} //end if
				$obj = [
					'columns' 	=> [],
					'type' 		=> $t
				];
			} //end if else
			$obj['columns'][] = $row['COLUMN_NAME'];
			$idx[$name] = $obj;
		} //end while
		foreach($idx as $name => $obj) {
			$xml .= '<key name="'.\Smart::escape_html($name).'" type="'.\Smart::escape_html($obj["type"]).'">';
			for($i=0; $i<count($obj['columns']); $i++) {
				$col = $obj['columns'][$i];
				$xml .= '<part>'.\Smart::escape_html($col).'</part>';
			} //end for
			$xml .= '</key>';
		} //end foreach
		$xml .= "</table>";
	} //end while

	$arr[] = $xml;
	$arr[] = '</sql>';

	return (string) implode("\n", (array)$arr);

} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>