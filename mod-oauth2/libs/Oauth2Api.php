<?php
// PHP Oauth2 Api for Smart.Framework
// Module Library
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Oauth2;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: \SmartModExtLib\Oauth2\Oauth2Api
 * Lookup in GeoIP DB
 *
 * @version 	v.20200715
 * @package 	modules:Oauth2
 *
 */
final class Oauth2Api {


	public const OAUTH2_STANDALONE_REFRESH_URL = 'urn:ietf:wg:oauth:2.0:oob';
	public const OAUTH2_AUTHORIZE_URL_PARAMS = 'response_type=code&client_id=[###CLIENT-ID|url###]&scope=[###SCOPE|url###]&redirect_uri=[###REDIRECT-URI|url###]&state=[###STATE|url###]';

	private static $model = null;


	/**
	 * Init the Oauth2 API Data
	 * If ALL OK will store it into the storage API (SQLite)
	 *
	 * @param ARRAY 	$data 		The Array of Input Data
	 * @param INTEGER+ 	$timeout 	The timeout in seconds to retrieve the Oauth2 Data via HTTP(S) from the Token URL
	 * @return MIXED 				Error STRING if any error occus or if OK will return the Data ARRAY (incl. access token, expire time and refresh token)
	 */
	public static function initApiData(array $data, int $timeout=15) {
		//--
		if(\Smart::array_size($data) <= 0) {
			return 'Invalid Data Format';
		} //end if else
		//--
		if(
			(!\array_key_exists('client_id', $data)) OR
			(!\array_key_exists('client_secret', $data)) OR
			(!\array_key_exists('scope', $data)) OR
			(!\array_key_exists('url_redirect', $data)) OR
			((string)$data['url_redirect'] != (string)self::OAUTH2_STANDALONE_REFRESH_URL) OR
			(!\array_key_exists('url_auth', $data)) OR
			(!\array_key_exists('url_token', $data)) OR
			(\strpos((string)$data['url_token'], 'https://') !== 0) OR // {{{SYNC-OAUTH2-VALIDATE-URL}}}
			(!\array_key_exists('code', $data)) OR
			(!\array_key_exists('description', $data)) OR
			(!\array_key_exists('id', $data))
		) {
			return 'Invalid Data Structure';
		} //end if
		//--
		$bw = new \SmartHttpClient();
		$bw->connect_timeout = (int) ($timeout > 15 && $timeout < 60) ? $timeout : 15; // {{{SYNC-OAUTH2-REQUEST-TIMEOUT}}}
		$bw->postvars = [
			'grant_type' 	=> (string) 'authorization_code',
			'redirect_uri' 	=> (string) $data['url_redirect'],
			'code' 			=> (string) $data['code'],
			'client_id' 	=> (string) $data['client_id'],
			'client_secret' => (string) $data['client_secret'],
		];
		$response = (array) $bw->browse_url((string)$data['url_token'], 'POST', 'tls');
		if(((int)$response['result'] != 1) OR (((string)$response['code'] != '200'))) {
			return 'Invalid HTTP(S) Answer: '.(int)$response['result'].'] / Status Code: '.(string)$response['code'];
		} //end if
		//--
		$json = \Smart::json_decode((string)$response['content']);
		if(\Smart::array_size($json) <= 0) {
			return 'Invalid HTTP(S) Answer: JSON Data is Invalid';
		} //end if
		//--
		if(
			((string)$json['token_type'] != 'Bearer') OR
			((string)$json['scope'] != (string)$data['scope']) OR
			((string)\trim((string)$json['refresh_token']) == '') OR
			((string)\trim((string)$json['access_token']) == '') OR
			((int)$json['expires_in'] <= 0)
		) {
			return 'Invalid HTTP(S) Answer: JSON Structure is NOT Valid';
		} //end if else
		//--
		$data['refresh_token'] 			= (string) $json['refresh_token'];
		$data['access_token'] 			= (string) $json['access_token'];
		$data['access_expire_seconds'] 	= (int)    $json['expires_in'];
		//--
		$insert = (int) self::getDataModel()->insertRecord((array)$data, (string)$data['url_redirect']);
		if((int)$insert != 1) {
			return 'Failed to Store the Tokens: #'.(int)$insert;
		} //end if
		//--
		return (array) $data;
		//--
	} //END FUNCTION


	/**
	 * Get the API Data by ID
	 *
	 * @param STRING $id 		The unique API ID
	 * @return ARRAY 			The array containing the full api data
	 */
	public static function getApiData(string $id) {
		//--
		return (array) self::getDataModel()->getById((string)$id, true);
		//--
	} //END FUNCTION


	/**
	 * Get the valid AccessToken for the given API by ID
	 * If the AccessToken is expired this function will make a sub-call to update the AccessToken using the stored RefreshToken and will return it
	 * If no valid Access Token can be returned, it will return a NULL value
	 *
	 * @param STRING $id 		The unique API ID
	 * @return MIXED 			A string containing the current, valid, unexpired Access Token ; if fail to find a valid Access Token will return a NULL value
	 */
	public static function getApiAccessToken(string $id, int $timeout=15) {
		//--
		$arr = (array) self::getApiData((string)$id);
		//--
		if(\Smart::array_size($arr) <= 0) {
			return null;
		} //end if
		//--
		if((int)$arr['active'] != 1) {
			return null; // inactive
		} //end if
		//--
		if((string)\trim((string)$arr['access_token']) == '') {
			return null;
		} //end if
		$expired = (int) ((int)\time() - 15); // make it expired with 15 sec before it real expires because the socket times must be considered also
		if((int)$arr['access_expire_time'] >= (int)$expired) {
			return (string) $arr['access_token']; // OK, not expired and not empty
		} //end if
		//-- below the expired AccessT Token must be updated
		$upd = (array) self:: updateApiAccessToken((string)$id, (int)$timeout);
		if(\Smart::array_size($upd) <= 0) {
			return null;
		} //end if
		return (string) $upd['access_token'];
		//--
	} //END FUNCTION


	public static function updateApiAccessToken(string $id, int $timeout=15) {
		//--
		$arr = (array) self::getApiData((string)$id);
		//--
		if(\Smart::array_size($arr) <= 0) {
			return array();
		} //end if
		//--
		if((string)\trim((string)$arr['refresh_token']) == '') {
			return array(); // there is no refresh token found, cannot update
		} //end if
		//--
		$url = (string) \trim((string)$arr['url_token']);
		if((string)\trim((string)$url) == '') {
			return array(); // the token URL is empty, cannot update
		} //end if
		//--
		$bw = new \SmartHttpClient();
		$bw->connect_timeout = (int) ($timeout > 15 && $timeout < 60) ? $timeout : 15; // {{{SYNC-OAUTH2-REQUEST-TIMEOUT}}}
		$bw->postvars = [
			'grant_type' 	=> (string) 'refresh_token',
			'refresh_token' => (string) $arr['refresh_token'],
			'client_id' 	=> (string) $arr['client_id'],
			'client_secret' => (string) $arr['client_secret'],
		];
		$response = (array) $bw->browse_url((string)$arr['url_token'], 'POST', 'tls');
		if(((int)$response['result'] != 1) OR (((string)$response['code'] != '200'))) {
			$logs = 'Invalid HTTP(S) Answer for Refresh Access Token: '.(int)$response['result'].'] / Status Code: '.(string)$response['code'];
			self::getDataModel()->updateRecordLogs((string)$id, (string)'# '.\date('Y-m-d H:i:s O')."\n".'# '.$logs, true);
			return array();
		} //end if
		//--
		$json = \Smart::json_decode((string)$response['content']);
		if(\Smart::array_size($json) <= 0) {
			$logs = 'Invalid HTTP(S) JSON Answer for Refresh Access Token: '."\n".(string)\base64_encode((string)$response['content']);
			self::getDataModel()->updateRecordLogs((string)$id, (string)'# '.\date('Y-m-d H:i:s O')."\n".'# '.$logs, true);
			return array();
		} //end if
		//--
		if(
			((string)$json['token_type'] != 'Bearer') OR
			((string)$json['scope'] != (string)$arr['scope']) OR
			((string)\trim((string)$json['access_token']) == '') OR
			((int)$json['expires_in'] <= 0)
		) {
			$logs = 'Invalid HTTP(S) JSON Structure Answer for Refresh Access Token: '."\n".(string)\Smart::json_encode((array)$json, false, false, true);
			self::getDataModel()->updateRecordLogs((string)$id, (string)'# '.\date('Y-m-d H:i:s O')."\n".'# '.$logs, true);
			return array();
		} //end if else
		//--
		$upd = (int) self::getDataModel()->updateRecordAccessToken((string)$id, (string)$json['access_token'], (int)$json['expires_in']);
		if((int)$upd != 1) {
			return array();
		} //end if
		//--
		return (array) $json;
		//--
	} //END FUNCTION


	public static function deleteApiAccessToken(string $id) {
		//--
		$arr = (array) self::getApiData((string)$id);
		//--
		if(\Smart::array_size($arr) <= 0) {
			return -999;
		} //end if
		//--
		return (int) self::getDataModel()->deleteRecord((string)$id);
		//--
	} //END FUNCTION


	/**
	 * Update the API Status by ID
	 *
	 * @param STRING $id 		The unique API ID
	 * @param STRING $status 	The status value: 0/1, true/false, active/inactive
	 * @return INTEGER 			On SUCCESS will return 1
	 */
	public static function updateApiStatus(string $id, string $status) {
		//--
		if(
			((string)\strtolower((string)$status) == 'active') OR
			((string)\strtolower((string)$status) == 'true') OR
			((string)$status == '1')
		) {
			$value = 1;
		} else {
			$value = 0;
		} //end if else
		//--
		return (int) self::getDataModel()->updateStatus((string)$id, (int)$value);
		//--
	} //END FUNCTION


	//##### PRIVATES


	private static function getDataModel() {
		//--
		if(self::$model === null) {
			self::$model = new \SmartModDataModel\Oauth2\SqOauth2();
		} //end if
		//--
		return self::$model;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
