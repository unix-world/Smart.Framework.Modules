<?php
// [LIB - Smart.Framework / Plugins / ZIP Archiver]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.8.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// ZIP Archive - Creates a Zip Archive
// DEPENDS:
// DEPENDS-EXT: PHP ZLib Extension
//======================================================


//--
// gzcompress / gzuncompress (rfc1950) which uses ADLER32 minimal checksums
//--
if((!function_exists('gzcompress')) OR (!function_exists('gzuncompress'))) {
	@http_response_code(500);
	die('ERROR: The PHP ZLIB Extension (gzcompress/gzuncompress) is required for Smart.Framework / Lib ZipArchive');
} //end if
//--


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartZipArchive - Generates a ZIP Archive.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	extensions: PHP ZLIB
 * @version 	v.20200121
 * @package 	extralibs:Archivers
 *
 */
final class SmartZipArchive {

	// ->


//================================================= PRIV VARS
private $datasec = array(); // store compressed data
private $ctrl_dir = array(); // central directory
private $eof_ctrl_dir = ''; // End of central directory record
private $old_offset = 0; // Last offset position
//=================================================


//=====================================================================
public function __construct() {

	//--
	$this->eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	$this->old_offset = 0;
	//--

} //END FUNCTION
//=====================================================================


//=====================================================================
/**
 * Adds "file" to archive
 *
 * @param  string   file contents
 * @param  string   name of the file in the archive (may contains the path)
 * @param  integer  the current timestamp
 *
 * @access public
 */
public function add_file($name, $data, $time=0) {

	//--
	$data = (string) $data;
	//--

	//--
	$name = str_replace('\\', '/', $name);
	//--
	$dtime = dechex($this->unix_2_dos_time($time));
	//--

	//--
	$hexdtime = (string) '\x'.$dtime[6].$dtime[7].'\x'.$dtime[4].$dtime[5].'\x'.$dtime[2].$dtime[3].'\x'.$dtime[0].$dtime[1];
	//--
	//eval('$hexdtime = "'.str_replace('"', '', (string)$hexdtime).'";');
	$hexdtime = (string) @hex2bin(str_replace('\x', '', $hexdtime)); // fix by unixman: modern PHP can do this which is safer and faster
	//--

	//--
	$fr  = "\x50\x4b\x03\x04";
	$fr .= "\x14\x00"; // ver needed to extract
	$fr .= "\x00\x00"; // gen purpose bit flag
	$fr .= "\x08\x00"; // compression method
	$fr .= $hexdtime;  // last mod time and date
	//--

	//-- local file header segment
	$unc_len 	= strlen($data);
	$crc 		= crc32($data);
	//--
	$len_data 	= strlen($data);
	$zdata 		= gzcompress($data);
	$data 		= ''; // free mem
	//--

	//-- check for possible zlib-pack errors
	if(($zdata === false) OR ((string)$zdata == '')) {
		Smart::log_warning(__METHOD__.'() / FileName: ['.$name.'] / Zlib GZ-Encode ERROR ! ...');
		return;
	} //end if
	$len_arch = strlen((string)$zdata);
	if(($len_data > 0) AND ($len_arch > 0)) {
		$ratio = $len_data / $len_arch;
	} else {
		$ratio = 0;
	} //end if
	/* not applied, can be a file with an empty content
	if($ratio <= 0) { // check for empty input / output !
		Smart::log_warning(__METHOD__.'() / FileName: ['.$name.'] / ZLib Data Ratio is zero ! ...');
		return;
	} //end if
	*/
	if($ratio > 32768) { // check for this bug in ZLib {{{SYNC-GZ-ARCHIVE-ERR-CHECK}}}
		Smart::log_warning(__METHOD__.'() / FileName: ['.$name.'] / ZLib Data Ratio is higher than 32768 ! ...');
		return;
	} //end if
	//--

	//--
	$zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
	$c_len   = strlen($zdata);
	//--

	//--
	$fr     .= pack('V', $crc);             // crc32
	$fr     .= pack('V', $c_len);           // compressed filesize
	$fr     .= pack('V', $unc_len);         // uncompressed filesize
	$fr     .= pack('v', strlen($name));    // length of filename
	$fr     .= pack('v', 0);                // extra field length
	$fr     .= $name;
	//-- "file data" segment
	$fr .= $zdata;
	//--

	//-- add this entry to array
	$this->datasec[] = $fr;
	//--

	//-- now add to central directory record
	$cdrec  = "\x50\x4b\x01\x02";
	$cdrec .= "\x00\x00";                // version made by
	$cdrec .= "\x14\x00";                // version needed to extract
	$cdrec .= "\x00\x00";                // gen purpose bit flag
	$cdrec .= "\x08\x00";                // compression method
	$cdrec .= $hexdtime;                 // last mod time & date
	$cdrec .= pack('V', $crc);           // crc32
	$cdrec .= pack('V', $c_len);         // compressed filesize
	$cdrec .= pack('V', $unc_len);       // uncompressed filesize
	$cdrec .= pack('v', strlen($name)); // length of filename
	$cdrec .= pack('v', 0);             // extra field length
	$cdrec .= pack('v', 0);             // file comment length
	$cdrec .= pack('v', 0);             // disk number start
	$cdrec .= pack('v', 0);             // internal file attributes
	$cdrec .= pack('V', 32);            // external file attributes - 'archive' bit set
	//--
	$cdrec .= pack('V', $this->old_offset); // relative offset of local header
	$this->old_offset += strlen($fr);
	$cdrec .= $name;
	//--

	//-- optional extra field, file comment goes here :: save to central directory
	$this->ctrl_dir[] = $cdrec;
	//--

} //END FUNCTION
//=====================================================================


//=====================================================================
/**
 * Dumps out file
 *
 * @return  string  the zipped file
 *
 * @access public
 */
public function output() {

	//--
	$data    = (string) implode('', (array)$this->datasec);
	$ctrldir = (string) implode('', (array)$this->ctrl_dir);
	//--

	//--
	return (string) $data.$ctrldir.$this->eof_ctrl_dir.
		pack('v', sizeof($this->ctrl_dir)).  // total # of entries "on this disk"
		pack('v', sizeof($this->ctrl_dir)).  // total # of entries overall
		pack('V', strlen($ctrldir)).         // size of central dir
		pack('V', strlen($data)).            // offset to start of central dir
		"\x00\x00";                          // .zip file comment length
	//--

} //END FUNCTION
//=====================================================================


//=====================================================================
/**
 * Converts an Unix timestamp to a four byte DOS date and time format (date
 * in high two bytes, time in low two bytes allowing magnitude comparison).
 *
 * @param  integer  the current Unix timestamp
 *
 * @return integer  the current date in a four byte DOS format
 *
 * @access private
 */
private function unix_2_dos_time($unixtime=0) {

	//--
	$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
	//--
	if($timearray['year'] < 1980) {
		$timearray['year']    = 1980;
		$timearray['mon']     = 1;
		$timearray['mday']    = 1;
		$timearray['hours']   = 0;
		$timearray['minutes'] = 0;
		$timearray['seconds'] = 0;
	} // end if
	//--

	//--
	return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	//--

} //END FUNCTION
//=====================================================================


} //END CLASS


/*** SAMPLE USAGE
$zip = new SmartZipArchive();
$zip->add_file('file1.txt', $file1);
$zip->add_file('file2.xml', $file2);
echo $zip->output();
***/

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
