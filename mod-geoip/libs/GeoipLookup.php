<?php
// PHP GeoIP Lookup for Smart.Framework
// Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Geoip;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


/* Config settings required for this library:
define('SMART_GEOIPLOOKUP_BIN_PATH', '/usr/local/bin/geoiplookup');
define('SMART_GEOIPLOOKUP6_BIN_PATH', '/usr/local/bin/geoiplookup6');
*/


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: \SmartModExtLib\Geoip\GeoipLookup
 * Lookup in GeoIP DB
 *
 * @depends 	executables: geoiplookup, geoiplookup6
 * @version 	v.20200217
 * @package 	modules:GeoIP
 *
 */
class GeoipLookup {


	/**
	 * Get the 2 letters country code for an IP address
	 * If IP address is not found or if something is wrong will return FALSE
	 *
	 * @param STRING $address 	The IP address to lookup for (IPV4 or IPV6)
	 * @param BOOLEAN $ipv6 	TRUE / FALSE or NULL to autodetect
	 */
	public static function getCountryCode($address, $ipv6=null) {
		//--
		$address = (string) \trim((string)$address);
		if((string)$address == '') {
			return null;
		} //end if
		if(!\filter_var((string)$address, \FILTER_VALIDATE_IP)) {
			return null;
		} //end if
		//--
		if(($ipv6 !== true) AND ($ipv6 !== false)) { // AUTODETECT
			if(\strpos((string)$address, ':') !== false) {
				$ipv6 = true;
			} else {
				$ipv6 = false;
			} //end if else
		} //end if
		//--
		if($ipv6 === true) { // IPV6
			if(!\defined('\\SMART_GEOIPLOOKUP6_BIN_PATH')) {
				return null;
			} //end if
			$cmd = (string) \SMART_GEOIPLOOKUP6_BIN_PATH;
		} else { // default: IPV4
			if(!\defined('\\SMART_GEOIPLOOKUP_BIN_PATH')) {
				return null;
			} //end if
			$cmd = (string) \SMART_GEOIPLOOKUP_BIN_PATH;
		} //end if else
		//--
		$cmd = (string) \trim((string)$cmd);
		if((string)$cmd == '') {
			return null;
		} //end if
		if(!\SmartFileSystem::have_access_executable((string)$cmd)) {
			return null;
		} //end if
		@\exec($cmd.' '.\escapeshellarg((string)$address), $result, $code);
		//-- Note: At time of writing, $result is always zero for this program
		if($code !== 0) {
			return null;
		} //end if
		if(empty($result)) {
			return null;
		} //end if
		//-- Always returns one line of code, ex: `GeoIP Country Edition: GB, United Kingdom` ; on error may return `GeoIP Country Edition: IP Address not found`
		$country = (string) \trim((string)$result[0]);
		if(\strpos($country, ':') === false) {
			return null;
		} //end if
		if((\strpos($country, ',') === false) OR (\stripos($country, ': IP Address not found') !== false)) {
			return null;
		} //end if
		//--
		$start = (int) (\strpos($country, ':') + 2); // skip the : and space after
		$code = (string) \strtolower((string)\trim((string)\substr($country, $start, 2)));
		//--
		if((\strlen($code) != 2) OR ((string)\strtoupper((string)$code) == 'ip') OR (!\preg_match('/^[a-z]+$/', (string)$code))) {
			return null;
		} //end if
		//--
		return (string) $code;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
