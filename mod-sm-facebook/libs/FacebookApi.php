<?php
// Class: \SmartModExtLib\SmFacebook\FacebookApi
// Facebook SDK :: Smart.Framework Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\SmFacebook;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


/**
 * Provides a PHP connector for Facebook Graph Api (default to 5.0) inside the Smart.Framework.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: classes: Facebook Graph Api
 * @version 	v.20200121
 * @package 	modules:SocialMedia
 *
 */
final class FacebookApi {

	// ->

	private $fb = null;
	private $api_version = '';
	private $file_upload = true;

	private $app_id = '';
	private $app_secret = '';

	private $last_error = '';
	private $usrdata = array();


	public function __construct($app_id, $app_secret, $api_version='v5.0') {
		//--
		if((string)$app_id == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty App ID';
			return;
		} //end if
		if((string)$app_secret == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty App Secret';
			return;
		} //end if
		//--
		$this->api_version = (string) $api_version;
		//--
		$this->app_id 		= (string) $app_id;
		$this->app_secret 	= (string) $app_secret;
		//--
		$this->fb = new \Facebook\Facebook([
			'app_id' 				=> (string) $this->app_id,
			'app_secret' 			=> (string) $this->app_secret,
			'default_graph_version' => (string) $this->api_version,
			'fileUpload' 			=> (bool) 	$this->file_upload
		]);
		//--
	} //END FUNCTION


	public function getApiObject() {
		//--
		return $this->fb; // object or null
		//--
	} //END FUNCTION


	public function getLastError() {
		//--
		return (string) $this->last_error;
		//--
	} //END FUNCTION


	public function getUserData() {
		//--
		$this->usrdata = array();
		//--
		if(!is_object($this->fb)) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Object not Initialized';
			return (array) $this->usrdata;
		} //end if
		//--
		$jsdata = (string) base64_decode((string)$_COOKIE['smartfbookjsapi_data']);
		if((string)$jsdata != '') {
			$jsdata = \Smart::json_decode((string)$jsdata);
		} else {
			$jsdata = null;
		} //end if
		//--
		$access_token = (string) $jsdata['token'];
		//--
		if((string)$access_token == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty Token';
			return (array) $this->usrdata;
		} //end if
		//--
		$response = null;
		$userobjdata = null;
		//--
		try { // {{{SYNC-FACEBOOK-GET-ME}}}
			$response = $this->fb->get('/me?fields=id,name,email,gender,birthday,location,timezone,locale,verified,permissions', (string)$access_token); // object: \Facebook\FacebookResponse
			if(is_object($response)) {
				$userobjdata = $response->getGraphObject()->asArray();
				//print_r($userobjdata); die();
			} else {
				$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Invalid Data';
				return (array) $this->usrdata;
			} //end if
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): '.$e->getMessage();
			return (array) $this->usrdata;
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): '.$e->getMessage();
			return (array) $this->usrdata;
		} //end try catch
		//--
		if(\Smart::array_size($userobjdata) <= 0) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty Data';
			return (array) $this->usrdata;
		} //end if
		//--
		if((string)$userobjdata['id'] == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty ID';
			return (array) $this->usrdata;
		} //end if
		//-- # security check # backed PHP Twitter Api must return and match the same user ID as frontend Js Twitter Api
		if((string)$userobjdata['id'] !== (string)$jsdata['uid']) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Authentication Failed :: User IDS does not match the Js-Api UID: '.$userobjdata['id'].' / '.$jsdata['uid'];
			return (array) $this->usrdata;
		} //end if
		//--
		$tmp_obj_bday = (array) $userobjdata['birthday'];
		if(\Smart::array_size($tmp_obj_bday)) {
			$tmp_bday = (string) ($tmp_obj_bday['date'] ? date('Y-m-d', @strtotime((string)$tmp_obj_bday['date'])) : '');
		} else {
			$tmp_bday = '';
		} //end if else
		//--
		$tmp_obj_perms = (array)  $userobjdata['permissions'];
		$tmp_arr_perms = array();
		foreach($tmp_obj_perms as $key => $val) {
			if(\Smart::array_size($val) > 0) {
				if((string)$val['status'] === 'granted') {
					$tmp_arr_perms[] = (string) $val['permission'];
				} //end if
			} //end if
		} //end foreach
		//--
		$this->usrdata = [ // {{{SYNC-FB-DATA}}}
			//--
			'httpstatus' 	=> '200/200',
			//--
			'token' 		=> (string) $access_token,
			//--
			'uid'			=> (string) $userobjdata['id'],
			'email'			=> (string) $userobjdata['email'],
			'name'			=> (string) $userobjdata['name'],
			'timezone' 		=> (string) ((int)($userobjdata['timezone'] * 60)), // timezone in minutes
			'location' 		=> (string) $userobjdata['location'],
			//--
			'locale' 		=> (string) $userobjdata['locale'],
			'gender'		=> (string) $userobjdata['gender'],
			'birthday' 		=> (string) $tmp_bday,
			//--
			'verified'		=> (int) 	$userobjdata['verified'] ? 1 : 0,
			'permissions' 	=> (array)  $tmp_arr_perms
			//--
		];
		//--
		return (array) $this->usrdata;
		//--
	} //END FUNCTION


	public function validateUserData() {
		//--
		$ok = false;
		//--
		if(\Smart::array_size($this->usrdata) > 0) {
			if((string)$this->usrdata['uid'] != '') {
				$ok = true;
			} //end if
		} //end if
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION


} //END CLASS


//--
require_once('modules/mod-sm-facebook/libs/graph-sdk/Facebook/polyfills.php');
//--
function autoload__FacebookGraphApi_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)substr((string)$classname, 0, 8) !== 'Facebook') { // if class name is not starting with Facebook
		return;
	} //end if
	//--
	$path = 'modules/mod-sm-facebook/libs/graph-sdk/'.str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
	//--
	if(!preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in path
	} //end if
	//--
	if(!is_file($path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once($path.'.php');
	//--
} //END FUNCTION
//--
spl_autoload_register('\\SmartModExtLib\\SmFacebook\\autoload__FacebookGraphApi_SFM', true, false); // throw / append
//--


// end of php code
