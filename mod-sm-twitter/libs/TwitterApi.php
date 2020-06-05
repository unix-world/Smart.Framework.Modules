<?php
// Class: \SmartModExtLib\SmTwitter\TwitterApi
// Twitter SDK for Smart.Framework
// Module Library
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\SmTwitter;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


/**
 * Provides a PHP connector for Twitter Api inside the Smart.Framework.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		PUBLIC
 * @depends 	extensions: classes: Twitter Api
 * @version 	v.20200121
 * @package 	modules:SocialMedia
 *
 */
final class TwitterApi {

	// ->

	private $twitt = null;

	private $app_id = ''; 		// consumer key
	private $app_secret = ''; 	// consumer secret

	private $last_error = '';
	private $usrdata = array();


	public function __construct($app_id, $app_secret) {
		//--
		if((string)$app_id == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty Consumer Key';
			return;
		} //end if
		if((string)$app_secret == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty Consumer Secret';
			return;
		} //end if
		//--
		$this->app_id = (string) $app_id;
		$this->app_secret = (string) $app_secret;
		//--
		\Codebird\Codebird::setConsumerKey( // static, see README
			(string) $this->app_id, // consumer key
			(string) $this->app_secret // consumer secret
		);
		//--
		$this->twitt = \Codebird\Codebird::getInstance();
		//--
	} //END FUNCTION


	public function getApiObject() {
		//--
		return $this->twitt; // object or null
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
		if(!is_object($this->twitt)) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Object not Initialized';
			return (array) $this->usrdata;
		} //end if
		//--
		$jsdata = (string) base64_decode((string)$_COOKIE['smarttwittjsapi_data']);
		if((string)$jsdata != '') {
			$jsdata = \Smart::json_decode((string)$jsdata);
		} else {
			$jsdata = null;
		} //end if
		//--
		if(\Smart::array_size($jsdata) <= 0) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty Js Arr-Data';
			return (array) $this->usrdata;
		} //end if
		//--
		$access_token = (string) $jsdata['token'];
		$access_secret = (string) $jsdata['secret'];
		//--
		if((string)$access_token == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty Token';
			return (array) $this->usrdata;
		} //end if
		if((string)$access_secret == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Empty Secret';
			return (array) $this->usrdata;
		} //end if
		//--
		$this->twitt->setToken((string)$access_token, (string)$access_secret);
		//--
		$data = (array) $this->twitt->account_verifyCredentials([
			'include_entities' => false,
			'skip_status' => true,
			'include_email' => true
		]);
		//print_r($data); die();
		//--
		if((int)$data['httpstatus'] !== 200) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Authentication Failed :: Invalid HTTP-Status '.' # '.$this->getReplyError($data);
			return (array) $this->usrdata;
		} //end if
		//--
		if((string)$data['id'] == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Authentication Failed :: Empty User ID';
			return (array) $this->usrdata;
		} //end if
		if((string)$data['id_str'] == '') {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Authentication Failed :: Empty User IDS';
			return (array) $this->usrdata;
		} //end if
		//-- # security check # backed PHP Twitter Api must return and match the same user ID as frontend Js Twitter Api
		if((string)sha1((string)$data['id_str']) !== (string)sha1((string)$jsdata['uid'])) {
			$this->last_error = (string) 'ERROR: '.__METHOD__.'(): Authentication Failed :: User IDS does not match the Js-Api UID: '.$data['id_str'].' / '.$jsdata['uid'];
			return (array) $this->usrdata;
		} //end if
		//--
		$this->usrdata = [ // {{{SYNC-TWITT-DATA}}}
			//--
			'httpstatus' 	=> (string) '200/'.$data['httpstatus'],
			//--
			'token' 		=> (string) $access_token,
			'secret' 		=> (string) $access_secret,
			//--
			'uid'			=> (string) $data['id_str'], // When consuming the API using JSON, it is important to always use the field id_str instead of id. This is due to the way Javascript and other languages that consume JSON evaluate large integers (https://dev.twitter.com/overview/api/twitter-ids-json-and-snowflake)
			'email'			=> (string) $data['email'],
			'name'			=> (string) $data['name'],
			'timezone' 		=> (string) ((int)round($data['utc_offset'] / 60)), // timezone in minutes
			'location' 		=> (string) $data['location'],
			//--
			'locale' 		=> (string) $data['lang'],
			'username'		=> (string) $data['screen_name'],
			//--
			'verified'		=> (int) 	$data['verified'] ? 1 : 0,
			'permissions' 	=> (array)  []
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


	//=====


	private function getReplyError($data) {
		//--
		$err = 'Unknown';
		//--
		if(is_array($data)) {
			$err = 'ERROR: HTTP-Status: '.(int)$data['httpstatus'];
			if(is_array($data['errors'])) {
				foreach($data['errors'] as $key => $val) {
					if(is_array($val)) {
						$err .= ' / Code: '.$val['code'].' / Message: '.$val['message'];
						break;
					} //end if
				} //end foreach
			} //end if
		} //end if
		//--
		return (string) $err;
		//--
	} //END FUNCTION


} //END CLASS


//--
function autoload__TwitterCodebirdApi_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(strpos((string)$classname, '\\') === false) { // if have namespace
		return;
	} //end if
	//--
	if((string)substr((string)$classname, 0, 8) !== 'Codebird') { // if class name is not starting with Codebird
		return;
	} //end if
	//--
	$path = 'modules/mod-sm-twitter/libs/'.str_replace(array('\\', "\0"), array('/', ''), (string)$classname);
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
spl_autoload_register('\\SmartModExtLib\\SmTwitter\\autoload__TwitterCodebirdApi_SFM', true, false); // throw / append
//--


// end of php code
