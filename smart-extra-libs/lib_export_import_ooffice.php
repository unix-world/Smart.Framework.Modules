<?php
// [LIB - Smart.Framework / Plugins / OpenDocument Export]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.8.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// OpenOffice Export ODS / Import FODS
// DEPENDS:
//	* Smart::
//	* SmartZipArchive::
// DEPENDS-EXT:
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartExportToOpenOffice - Exports (structured) Data to OpenOffice ODS Spreadsheet.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart, SmartZipArchive
 * @version 	v.20200121
 * @package 	extralibs:ExportAndImport
 *
 */
final class SmartExportToOpenOffice {

	//->


//=================================================
private $font_size = '';
private $mime_ods = '';
private $open_document_ns = '';
private $open_document_styles = '';
private $class_version = 'v.2020.01.21';
//=================================================


//=====================================================================
public function __construct($y_font_size='9pt', $y_styles=false) {

	//--
	if(!class_exists('SmartZipArchive')) {
		Smart::raise_error('The ODS Exporter (SmartExportToOpenOffice) requires the class: SmartZipArchive which could not be found !', 'Export To OpenOffice cannot find Zip Archive Class !');
		die('');
		return;
	} //end if
	//--

	//--
	$this->font_size = (string) $y_font_size;
	//--
	$this->mime_ods = 'application/vnd.oasis.opendocument.spreadsheet';
	//--
	$this->open_document_ns = ''.
		'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" '.
		'xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" '.
		'xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" '.
		'xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" '.
		'xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" '.
		'';
	//--
	$this->open_document_styles = '
		<office:automatic-styles>
			<style:style style:name="co1" style:family="table-column">
				<style:table-column-properties fo:break-before="auto" style:use-optimal-column-width="true"/>
			</style:style>
			<style:style style:name="ro1" style:family="table-row">
				<style:table-row-properties fo:break-before="auto" style:use-optimal-row-height="true"/>
			</style:style>
			<style:style style:name="ta1" style:family="table" style:master-page-name="Default">
				<style:table-properties table:display="true" style:writing-mode="lr-tb"/>
			</style:style>
			<style:style style:name="he1" style:family="table-cell" style:use-optimal-column-width="true" style:parent-style-name="Default">
				<style:table-cell-properties fo:background-color="#AECF00"/>
				<style:text-properties style:font-name="Arial" fo:font-size="'.$this->font_size.'" fo:font-weight="bold" style:font-size-asian="'.$this->font_size.'" style:font-weight-asian="bold" style:font-size-complex="'.$this->font_size.'" style:font-weight-complex="bold"/>
			</style:style>
			<style:style style:name="he2" style:family="table-cell" style:use-optimal-column-width="true" style:parent-style-name="Default">
				<style:table-cell-properties fo:background-color="#FFFFCC"/>
				<style:text-properties style:font-name="Arial" fo:font-size="'.$this->font_size.'" fo:font-weight="bold" style:font-size-asian="'.$this->font_size.'" style:font-weight-asian="bold" style:font-size-complex="'.$this->font_size.'" style:font-weight-complex="bold"/>
			</style:style>
			<style:style style:name="he3" style:family="table-cell" style:use-optimal-column-width="true" style:parent-style-name="Default">
				<style:table-cell-properties fo:background-color="#FF9900"/>
				<style:text-properties style:font-name="Arial" fo:font-size="'.$this->font_size.'" fo:font-weight="bold" style:font-size-asian="'.$this->font_size.'" style:font-weight-asian="bold" style:font-size-complex="'.$this->font_size.'" style:font-weight-complex="bold"/>
			</style:style>
			<style:style style:name="ce1" style:family="table-cell" style:use-optimal-column-width="true" style:parent-style-name="Default">
				<style:table-cell-properties fo:background-color="#ECEAFF"/>
				<style:text-properties style:font-name="Arial" fo:font-size="'.$this->font_size.'" fo:font-weight="bold" style:font-size-asian="'.$this->font_size.'" style:font-weight-asian="bold" style:font-size-complex="'.$this->font_size.'" style:font-weight-complex="bold"/>
			</style:style>
			<style:style style:name="ce2" style:family="table-cell" style:use-optimal-column-width="true" style:parent-style-name="Default">
				<style:text-properties style:font-name="Arial" fo:font-size="'.$this->font_size.'" style:font-size-asian="'.$this->font_size.'" style:font-size-complex="'.$this->font_size.'"/>
			</style:style>
			<style:style style:name="ce3" style:family="table-cell" style:use-optimal-column-width="true" style:parent-style-name="Default">
				<style:table-cell-properties fo:background-color="#ECECEC"/>
				<style:text-properties style:font-name="Arial" fo:font-size="'.$this->font_size.'" fo:font-weight="bold" style:font-size-asian="'.$this->font_size.'" style:font-weight-asian="bold" style:font-size-complex="'.$this->font_size.'" style:font-weight-complex="bold"/>
			</style:style>
		</office:automatic-styles>
	';
	//--
	if($y_styles === true) {
		// use built-in styles
	} elseif($y_styles === false) {
		$this->open_document_styles = ''; // use no styles
	} elseif((string)trim((string)$y_styles) != '') {
		$this->open_document_styles = (string) trim((string)$y_styles);
	} //end if
	//--

} //END FUNCTION
//=====================================================================


//=====================================================================
/**
 * Get the ODS Document Mime Type Header Data
 *
 * @return STRING		'application/vnd.oasis.opendocument.spreadsheet'
 */
public function ODS_Mime_Header() {
	//--
	return (string) $this->mime_ods;
	//--
} //END FUNCTION
//=====================================================================


//=====================================================================
/**
 * Get the ODS Document FileName Header Data
 *
 * @param STRING 	$y_filename		:: The ODS Document file name: default is: file.ods
 * @param ENUM 		$y_disp 		:: The content disposition, default is: inline ; can be also: attachment
 *
 * @return STRING		'attachment; filename="somedoc.ods"' or 'inline; filename="somedoc.ods"'
 *
 */
public function ODS_Disposition_Header($y_filename='file.ods', $y_disp='attachment') {
	//--
	switch((string)$y_disp) {
		case 'inline':
			$y_disp = 'inline';
			break;
		case 'attachment':
		default:
			$y_disp = 'attachment';
	} //end switch
	//--
	return (string) $y_disp.'; filename="'.Smart::safe_filename($y_filename).'"';
	//--
} //END FUNCTION
//=====================================================================


//=====================================================================
/**
 * Generate the ODS Document on the fly from an array of data
 *
 * @param STRING $y_table_name					:: The Table Name
 * @param STRING $y_arr_fields					:: Array of Header Fields
 * @param STRING $y_arr_data 					:: Array of Data
 *
 * @returns STRING 								:: The ODS Document Contents
 *
 */
public function ODS_SpreadSheet($y_table_name, $y_arr_fields, $y_arr_data, $y_arr_process=array(), $y_arr_totals=array(), $y_arr_finals=array(), $y_arr_widths=array(), $y_arr_highlights=array()) {

	// font-weight="bold"
	// fo:font-style="italic"
	// style:text-underline-type="single"

	//-- header
	$header = ''.
		'<?xml version="1.0" encoding="UTF-8"?'.'>'."\n".
		'<office:document-content '.$this->open_document_ns.'office:version="1.0">'."\n".
		$this->open_document_styles.
		'<office:body>'."\n".
		'<office:spreadsheet>'."\n";
	//--

	//-- footer
	$footer = "\n".
		'</office:spreadsheet>'."\n".
		'</office:body>'."\n".
		'</office:document-content>';
	//--

	//-- data
	$data = '';
	$data .= '<table:table table:style-name="ta1" table:print="true" table:name="'.Smart::escape_html($y_table_name).'">'."\n";
	//--

	//-- table headings
	$num_cols = Smart::array_size($y_arr_fields);
	$num_data = Smart::array_size($y_arr_data);
	//--
	$data .= '<table:table-row table:style-name="ro1">'."\n";
	//--
	for($i=0; $i<$num_cols; $i++) {
		//--
		$data .= '<table:table-cell table:style-name="he1" office:value-type="string">';
		$data .= '<text:p>'.Smart::escape_html($y_arr_fields[$i]).'</text:p>';
		$data .= '</table:table-cell>'."\n";
		//--
	} //end for
	//--
	$data .= '</table:table-row>'."\n";
	//--
	for($n=0; $n<$num_data; $n++) {
		//--
		$data .= '<table:table-row table:style-name="ro1">'."\n";
		//--
		for($i=0; $i<$num_cols; $i++) {
			//--
			$kk = $i + $n ;
			//--
			if((($i % $num_cols) == 0) OR (($i % $num_cols) == ($num_cols - 1))) {
				$tmp_style = 'ce1'; // highlight first and last column
			} elseif($i % 2) {
				$tmp_style = 'ce3';
			} else {
				$tmp_style = 'ce2';
			} //end if else
			//--
			if(((string)$y_arr_process[$i] == 'number') OR ((string)$y_arr_process[$i] == 'decimal2') OR ((string)$y_arr_process[$i] == 'decimal4')) {
				//--
				if((string)$y_arr_process[$i] == 'decimal2') {
					$y_arr_data[$kk] = (string) Smart::format_number_dec($y_arr_data[$kk], 2, '.', '');
				} elseif((string)$y_arr_process[$i] == 'decimal4') {
					$y_arr_data[$kk] = (string) Smart::format_number_dec($y_arr_data[$kk], 4, '.', '');
				} else {
					$y_arr_data[$kk] = (string) $y_arr_data[$kk];
				} //end if
				//--
				//$data .= '<table:table-cell table:style-name="'.$tmp_style.'" office:value-type="float" office:value="'.Smart::escape_html($y_arr_data[$kk]).'">';
				$data .= '<table:table-cell table:style-name="'.$tmp_style.'" office:value-type="string">'; // preserve number as they are, force type string !!!
				$data .= '<text:p>'.Smart::escape_html($y_arr_data[$kk]).'</text:p>';
				$data .= '</table:table-cell>'."\n";
				//--
			} else {
				//--
				$data .= '<table:table-cell table:style-name="'.$tmp_style.'" office:value-type="string">';
				$data .= '<text:p>'.Smart::escape_html($y_arr_data[$kk]).'</text:p>';
				$data .= '</table:table-cell>'."\n";
				//--
			} //end if else
			//--
		} //end for
		//--
		$data .= '</table:table-row>'."\n";
		//--
		$n += ($num_cols-1); // salt
		//--
	} //end for
	//--

	//-- totals row
	if(Smart::array_size($y_arr_totals) > 0) {
		//--
		$data .= '<table:table-row table:style-name="ro1">'."\n";
		//--
		for($i=0; $i<$num_cols; $i++) {
			//--
			$data .= '<table:table-cell table:style-name="he2" office:value-type="string">';
			$data .= '<text:p>'.$y_arr_totals[$i].'</text:p>';
			$data .= '</table:table-cell>'."\n";
			//--
		} //end for
		//--
		$data .= '</table:table-row>'."\n";
		//--
	} //end if
	//--

	//-- final row
	if(Smart::array_size($y_arr_finals) > 0) {
		//--
		$data .= '<table:table-row table:style-name="ro1">'."\n";
		//--
		for($i=0; $i<$num_cols; $i++) {
			//--
			$data .= '<table:table-cell table:style-name="he3" office:value-type="string">';
			$data .= '<text:p>'.$y_arr_finals[$i].'</text:p>';
			$data .= '</table:table-cell>'."\n";
			//--
		} //end for
		//--
		$data .= '</table:table-row>'."\n";
		//--
	} //end if
	//--

	//--
	$data .= '</table:table>'."\n";
	//--

	//--
	return (string) $this->OpenDocument_Template((string)$this->mime_ods, $header."\n".$data."\n".$footer);
	//--

} //END FUNCTION
//=====================================================================


//=====================================================================
// PRIVATE
private function OpenDocument_Template($y_mime, $y_data) {

	//--
	$zip = new SmartZipArchive();
	//--

	//--
	$zip->add_file('mimetype', $y_mime);
	//--

	//--
	$zip->add_file('content.xml', $y_data);
	//--

	//--
	$zip->add_file('meta.xml',
		'<?xml version="1.0" encoding="UTF-8"?'.'>'.
		'<office:document-meta '.
		'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" '.
		'xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" '.
		'office:version="1.0">'.
		'<office:meta>'.
			'<meta:generator>'.'SmartExportToOpenOffice '.$this->class_version.'</meta:generator>'.
			'<meta:initial-creator>Smart-OpenFramework '.SMART_FRAMEWORK_VERSION.'</meta:initial-creator>'.
			'<meta:creation-date>'.strftime('%Y-%m-%dT%H:%M:%S').'</meta:creation-date>'.
		'</office:meta>'.
		'</office:document-meta>'
	);
	//--

	//--
	$zip->add_file('styles.xml', '<?xml version="1.0" encoding="UTF-8"?' . '>'.
		'<office:document-styles '.$this->open_document_ns.'office:version="1.0">'.
		$this->open_document_styles.
		'</office:document-styles>'
	);
	//--

	//--
	$zip->add_file('META-INF/manifest.xml',
		'<?xml version="1.0" encoding="UTF-8"?' . '>'.
		'<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">'.
		'<manifest:file-entry manifest:media-type="'.$y_mime.'" manifest:full-path="/"/>'.
		'<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="content.xml"/>'.
		'<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="meta.xml"/>'.
		'<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="styles.xml"/>'.
		'</manifest:manifest>'
	);
	//--

	//--
	return $zip->output();
	//--

} //END FUNCTION
//=====================================================================


/******** SAMPLE USAGE
$oo = new SmartExportToOpenOffice();
header('Content-Type: '.$oo->ODS_Mime_Header());
header('Content-Disposition: '.$oo->ODS_Disposition_Header('myfile.ods', 'attachment'));
echo $oo->ODS_SpreadSheet('A Table', array('column 1', 'column 2'), array('data 1.1', 'data 1.2', 'data 2.1', 'data 2.2'));
********/

} //END CLASS

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartExportToOpenOffice - Imports XML OpenOffice FODS Spreadsheet to (structured) Data.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20200121
 * @package 	extralibs:ExportAndImport
 *
 */
final class SmartImportFromOpenOffice {

	// ::

	//=====================================================================
	public static function parseFodsXmlSpreadSheetToArray($input_str) {
		//--
		$input_str = (string) trim((string)$input_str);
		if((string)$input_str == '') {
			return array();
		} //end if
		//--
		if(stripos($input_str, '<?xml ') !== 0) {
			return array();
		} //end if
		//-- FIX: Line Break
		$input_str = (string) str_ireplace(['<text:line-break/>'], "\n", $input_str);
		//-- FIX: Many Spaces
		$regex = '\<text\:s text\:c\="([0-9]+)"\/\>';
		$input_str = (string) preg_replace_callback(
			(string) '/'.$regex.'/i',
			function($matches) use ($val) {
				$matches[1] = (int) $matches[1];
				if($matches[1] < 0) {
					$matches[1] = 0;
				} elseif($matches[1]>1000) {
					$matches[1] = 1000;
				} //end if
				$spaces = '';
				if($matches[1] > 0) {
					for($i=0; $i<$matches[1]; $i++) {
						$spaces .= ' ';
					} //end for
				} //end if
				return (string) $spaces;
			}, //end function
			$input_str
		);
		//-- #END FIX
		$csv_arr = (new SmartXmlParser('domxml'))->transform($input_str);
		$input_str = ''; // free mem
		//print_r($csv_arr); die();
		if(Smart::array_size($csv_arr) <= 0) {
			return array();
		} //end if
		if(Smart::array_size($csv_arr['office:body']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['office:body'];
		if(Smart::array_size($csv_arr['office:spreadsheet']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['office:spreadsheet'];
		if(Smart::array_size($csv_arr['table:table']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['table:table'];
		if(Smart::array_size($csv_arr['table:table-row']) <= 0) {
			return array();
		} //end if
		$csv_arr = (array) $csv_arr['table:table-row'];
		//print_r($csv_arr); die();
		if(Smart::array_size($csv_arr) <= 0) {
			return array();
		} //end if
		//--
		$hdr_arr = array();
		$data_arr = array();
		//--
		$cnt_csv_arr = (int) Smart::array_size($csv_arr);
		//--
		for($l=0; $l<$cnt_csv_arr; $l++) {
			//--
			$val = $csv_arr[$l]['table:table-cell'];
			//--
			if(Smart::array_type_test($val) == 1) {
				//--
				for($i=0; $i<Smart::array_size($val); $i++) {
					if(is_array($val[$i]['text:p'])) {
						for($p=0; $p<Smart::array_size($val[$i]['text:p']); $p++) {
							if(is_array($val[$i]['text:p'][$p])) {
								$val[$i]['text:p'][$p] = '';
							} //end if
						} //end for
						$val[$i]['text:p'] = (string) implode("\n", $val[$i]['text:p']);
					} //end if
					$data_arr[(string)$hdr_arr[$i]][] = (string) $val[$i]['text:p'];
				} //end for
				//--
			} elseif(Smart::array_size($hdr_arr) > 0) {
				//--
				if(is_array($val['text:p'])) {
					for($p=0; $p<Smart::array_size($val['text:p']); $p++) {
						if(is_array($val['text:p'][$p])) {
							$val['text:p'][$p] = '';
						} //end if
					} //end for
					$val['text:p'] = (string) implode("\n", $val['text:p']);
				} //end if
				//--
				$data_arr[(string)$hdr_arr[0]][] = (string) $val['text:p'];
				//--
				if(is_array($val['@attributes'])) {
					if((int)$val['@attributes']['number-columns-repeated'] > 1) {
						for($i=1; $i<$val['@attributes']['number-columns-repeated']; $i++) {
							$data_arr[(string)$hdr_arr[$i]][] = (string) $val['text:p'];
						} //end for
					} //end if
				} //end if
				//--
			} //end if else
			//--
		} //end for
		//--
		return array(
			'header' 	=> (array) $hdr_arr,
			'data' 		=> (array) $data_arr
		);
		//--
	} //END FUNCTION
	//=====================================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


// end of php code
