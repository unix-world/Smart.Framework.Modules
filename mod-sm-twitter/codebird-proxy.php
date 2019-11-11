<?php
// Controller: SmTwitter/CodebirdProxy
// Route: ?page=sm-twitter.codebird-proxy
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED');


// This class is based on the Codebird Twitter Proxy,
// Proxy to the Twitter API, adding CORS headers to replies.
// version 1.5.0.uxm-181002
// author Jublo Solutions <support@jublo.net>
// copyright 2013-2015 Jublo Solutions <support@jublo.net>
// license: GPL

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	private $media_file = '';

	private $CONST_CURLXE_SSL_CERTPROBLEM = 58;
	private $CONST_CURLXE_SSL_CACERT_ISSUE = 60;
	private $CONST_CURLXE_SSL_CACERT_BADFILE = 77;
	private $CONST_CURLXE_SSL_CRL_BADFILE = 82;
	private $CONST_CURLXE_SSL_ISSUER_ERROR = 83;

	public function Run() {

		$this->PageViewSetCfg('rawpage', true);

		if(strpos((string)strtolower((string)$_SERVER['HTTP_REFERER']), (string)$this->ControllerGetParam('url-addr')) !== 0) { // check referer
			$this->PageViewSetErrorStatus(403, 'ERROR: Proxy Invalid Referer.');
			return;
		} //end if

		if((string)$_SERVER['HTTP_Z_SFK'] == '') {
			$this->PageViewSetErrorStatus(403, 'ERROR: Proxy Empty Token.');
			return;
		} //end if
		$crr_req_url = (string) $this->ControllerGetParam('url-proto-addr').$this->ControllerGetParam('url-domain').$this->ControllerGetParam('url-port-addr').$this->ControllerGetParam('url-path').$this->ControllerGetParam('url-script').$this->ControllerGetParam('uri-path').$this->ControllerGetParam('url-query');
		$crr_req_ua = (string) SmartUtils::get_os_browser_ip('signature');
		if((string)SmartHashCrypto::sha512((string)$crr_req_url.'^'.(string)$crr_req_ua) !== (string)$_SERVER['HTTP_Z_SFK']) {
			$this->PageViewSetErrorStatus(403, 'ERROR: Proxy Invalid Token.');
			return;
		} //end if

		/* fix by unixman: avoid save on disk
		if(!is_dir('tmp/cache/codebird-proxy')) {
			SmartFileSystem::dir_create('tmp/cache/codebird-proxy', true); // recursive
		} //end if
		if(!is_dir('tmp/cache/codebird-proxy')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Cannot find media folder ...');
			return;
		} //end if
		*/

		$url = (string) $_SERVER['REQUEST_URI']; // (original) this works with both: ApacheRewrite and SmartFramework crafted PathInfo
		$method = (string) $_SERVER['REQUEST_METHOD'];

		$cors_headers = [
			'Access-Control-Allow-Origin' 	=> '*',
			'Access-Control-Allow-Headers' 	=> 'Origin, X-Authorization, Content-Type, Content-Range, X-TON-Expires, X-TON-Content-Type, X-TON-Content-Length',
			'Access-Control-Allow-Methods' 	=> 'POST, GET, OPTIONS',
			'Access-Control-Expose-Headers' => 'X-Rate-Limit-Limit, X-Rate-Limit-Remaining, X-Rate-Limit-Reset'
		];
		$this->PageViewResetRawHeaders();
		foreach($cors_headers as $key => $val) {
			$this->PageViewSetRawHeader($key, $val);
		} //end foreach
		$cors_headers = array(); // free mem

		if((string)$method == 'OPTIONS') {
			return; // method n/a
		} //end if

		// initialize CURL headers
		$headers = array();
		$headers[] = 'Expect:';

		// get request headers
		$received_headers = (array) $this->httpGetRequestHeaders();

		// extract authorization header
		if(isset($received_headers['X-Authorization'])) {
			$headers[] = 'Authorization: '.$received_headers['X-Authorization'];
		} //end if

		// get request body
		$body = null;
		if((string)$method === 'POST') {

			$body = (string) $this->httpGetRequestBody();

			// allow custom content types
			if(isset($_SERVER['CONTENT_TYPE'])) {
				$headers[] = 'Content-Type: '.str_replace(["\r", "\n"], [' ', ' '], (string)$_SERVER['CONTENT_TYPE']);
			} //end if

			// check for media parameter
			// for uploading multiple medias, use media_data, see
			// https://dev.twitter.com/docs/api/multiple-media-extended-entities

			if(isset($_POST['media']) && is_array($_POST['media'])) {

				$body = (array) $_POST;

				/* fix by unixman: avoid save on disk
				$this->media_file = 'tmp/cache/codebird-proxy/media-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str().'-'.Smart::uuid_10_seq(); // write media file to temp
				SmartFileSystem::write($this->media_file, (string)base64_decode((string)$_POST['media'][0]));
				unset($body['media']);
				$body['media[]'] = '@'.$this->media_file; // add file to uploads
				*/

				// fix by unixman: avoid save on disk
				if(base64_decode((string)$_POST['media'][0], true) !== false) {
					$body['media[]'] = (string) base64_decode((string)$_POST['media'][0]);
					unset($body['media']);
				} //end if


			} //end if

			// check for other base64 parameters
			$possible_files = [
				// media[] is checked above
				'image',
				'banner'
			];
			foreach((array)$_POST as $key => $value) {

				if(!in_array((string)$key, (array)$possible_files)) {
					continue;
				} //end if

				// skip arrays
				if(!is_scalar($value)) {
					continue;
				} //end if

				// check if valid base64
				if(base64_decode((string)$value, true) === false) {
					continue;
				} //end if

				if(!is_array($body)) {
					$body = array();
				} //end if
				$body[(string)$key] = (string) base64_decode((string)$value);

			} //end foreach

		} //end if

		// URLs always start with 1.1, oauth or a separate API prefix
		$api_host = 'ton.twitter.com';
		$version_pos = strpos($url, '/ton/1.1/');
		if($version_pos !== false) {
			$version_pos += 4; // strip '/ton' prefix
		} //end if
		if($version_pos === false) {
			$version_pos = strpos($url, '/1.1/');
			$api_host = 'api.twitter.com';
		} //end if
		if($version_pos === false) {
			$version_pos = strpos($url, '/oauth/');
		} //end if
		if($version_pos === false) {
			$version_pos = strpos($url, '/oauth2/');
		} //end if
		if($version_pos === false) {
		//	$version_pos = strpos($url, '/ads/0/');
			$version_pos = strpos($url, '/ads/2/');
			$api_host = 'ads-api.twitter.com';
			if($version_pos !== false) {
				$version_pos += 4; // strip '/ads' prefix
			} //end if
		} //end if
		if($version_pos === false) {
		//	$version_pos = strpos($url, '/ads-sandbox/0/');
			$version_pos = strpos($url, '/ads-sandbox/2/');
			$api_host = 'ads-api-sandbox.twitter.com';
			if($version_pos !== false) {
				$version_pos += 12; // strip '/ads-sandbox' prefix
			} //end if
		} //end if
		if($version_pos === false) {
			//header('HTTP/1.1 412 Precondition failed');
			$this->PageViewSetErrorStatus(400, 'This is a private Proxy to support requests to REST API version 1.1 / Twitter TON API / Twitter Ads API / CodeBird.');
			return;
		} //end if
		// use media endpoint if necessary
		$is_media_upload = strpos($url, 'media/upload.json') !== false;
		if($is_media_upload) {
			$api_host = 'upload.twitter.com';
		} //end if
		$url = 'https://'.$api_host.substr($url, $version_pos);

		// send request to Twitter API
		//Smart::log_notice($url);
		$ch = curl_init($url);
		if(!$ch) {
			$this->PageViewSetErrorStatus(502, 'ERROR: while initialize connection to Twitter API URL.');
			return;
		} //end if

		if($method === 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		} //end if

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CAINFO, 'etc/cacert.pem');
		curl_setopt($ch, CURLOPT_HTTPHEADER, (array)$headers);
		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

		$reply = curl_exec($ch);
		if(!$reply) {
			$this->PageViewSetErrorStatus(502, 'ERROR: Twitter API connection return Empty Answer.');
			return;
		} //end if

		// certificate validation results
		$validation_result = curl_errno($ch);
		if(in_array(
				$validation_result,
				[
					$this->CONST_CURLXE_SSL_CERTPROBLEM,
					$this->CONST_CURLXE_SSL_CACERT_ISSUE,
					$this->CONST_CURLXE_SSL_CACERT_BADFILE,
					$this->CONST_CURLXE_SSL_CRL_BADFILE,
					$this->CONST_CURLXE_SSL_ISSUER_ERROR
				]
			)
		) {
			$this->PageViewSetErrorStatus(502, 'ERROR: ['.$validation_result.'] while validating the Twitter API certificate.');
			return;
		} //end if

		$httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// split off headers
		$reply = (array) explode("\r\n\r\n", (string)$reply, 2);
		$reply_headers = (array) explode("\r\n", (string)$reply[0]);

		foreach($reply_headers as $kk => $reply_header) {
			if(strpos($reply_header, ':') !== false) {
				//header($reply_header);
				$tmp_hdr_arr = (array) explode(':', (string)$reply_header);
				$this->PageViewSetRawHeader(trim((string)$tmp_hdr_arr[0]), trim((string)$tmp_hdr_arr[1]));
				$tmp_hdr_arr = array();
			} //end if
		} //end foreach
		if(isset($reply[1])) {
			$reply = $reply[1];
		} //end if

		// \Smart::log_warning($url."\n".$reply);
		// send back all data untouched
		$this->PageViewSetVar(
			'main',
			(string) $reply
		);

	} //END FUNCTION


	public function ShutDown() {
		//--
		/* fix by unixman: avoid save on disk
		if(((string)$this->media_file != '') AND (strpos((string)$this->media_file, 'tmp/cache/codebird-proxy/media-') === 0) AND SmartFileSystem::path_exists((string)$this->media_file) AND is_file((string)$this->media_file)) {
			SmartFileSystem::delete((string)$this->media_file); delete media file, if any
		} //end if
		*/
		//--
	} //END FUNCTION


	private function httpGetRequestHeaders() {
		//--
		$arh = [];
		//--
		$rx_http = '/\AHTTP_/';
		foreach((array)$_SERVER as $key => $val) {
			if(preg_match($rx_http, $key)) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = [];
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = (array) explode('_', $arh_key);
				if(Smart::array_size($rx_matches) > 0 && strlen($arh_key) > 2) {
					foreach ($rx_matches as $ak_key => $ak_val) {
						$rx_matches[$ak_key] = ucfirst(strtolower($ak_val));
					} //end foreach
					$arh_key = implode('-', $rx_matches);
				} //end if
				$arh[(string)$arh_key] = $val;
			} //end if
		} //end foreach
		//--
		return (array) $arh;
		//--
	} //END FUNCTION


	private function httpGetRequestBody() {
		//--
		$body = '';
		//--
		$fh = fopen('php://input', 'r');
		if($fh) {
			while(!feof($fh)) {
				$s = fread($fh, 1024);
				if(is_string($s)) {
					$body .= (string) $s;
				} //end if
			} //end while
			fclose($fh);
		} //end if
		//--
		return (string) $body;
		//--
	} //END FUNCTION


} //END CLASS


/**
 * Admin Controller
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php
	// or this can implement a completely different controller if it is accessed via admin.php

} //END CLASS


//end of php code
?>