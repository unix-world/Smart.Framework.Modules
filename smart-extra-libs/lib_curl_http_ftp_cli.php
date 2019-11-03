<?php
// [LIB - Smart.Framework.Modules / ExtraLibs / CURL HTTP(S) Client with Optional Proxy Support]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - CURL HTTP(S) Client w. (TLS/SSL * Proxy)
// DEPENDS:
//	* Smart::
// DEPENDS-EXT: PHP CURL Extension with SSL support
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartCurlHttpFtpClient - provides a CURL based HTTP / HTTPS / FTP Client (browser) with Proxy Support.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	extensions: PHP CURL, PHP OpenSSL (optional, just for HTTPS) ; classes: Smart
 * @version 	v.171007
 * @package 	extralibs:Network
 *
 */
final class SmartCurlHttpFtpClient {

	// ->

	//==============================================
	//--
	public $useragent = 'SFM :: PHP.CURL/Robot'; 			// User agent (must have the robot in the name to avoid start un-necessary sessions)
	public $connect_timeout = 30;							// Connect timeout in seconds
	public $exec_timeout = 0;								// Exec timeout in seconds for CURL (0 or 30..300)
	public $debug = 0;										// DEBUG
	//--
	public $rawheaders;										// Array of RawHeaders (to send)
	public $cookies;										// Array of Cookies (to send)
	public $postvars;										// Array of PostVars (to send)
	public $poststring;										// Pre-Built Post String (as alternative to PostVars) ; must not contain unencoded \r\n ; must use the RFC 3986 standard.
	public $jsonrequest;									// JSON Request (to send) ; must not contain unencoded \r\n but only \n ; hint: can be json-encoded without pretty-print.
	public $xmlrequest;										// XML Request (to send) ; must not contain \r\n but only \n
	//--
	//============================================== privates
	//-- set
	private $protocol = '1.0';								// HTTP Protocol :: 1.0 (default) or 1.1
	//-- returns
	private $header;										// Header (answer)
	private $body;											// Body (answer)
	private $status;										// STATUS (answer) :: 200, 401, 403 ...
	//-- log
	private $log;											// Operations Log (debug only)
	//-- internals
	private $cproxy = array();								// The CURL Proxy
	private $curl = false;									// The CURL Handle
	private $raw_headers = array();							// Raw-Headers (internals)
	private $url_parts = array();							// URL Parts
	private $method = 'GET';								// method: GET / POST / HEAD / PUT / DELETE ...
	private $no_content_stop_if_unauth = true;				// Return no Content (response body) if Not Auth (401)
	//--
	private $cafile = '';									// Certificate Authority File (instead of using the global SMART_FRAMEWORK_SSL_CA_FILE can use a private cafile
	//--
	//==============================================


	//==============================================
	// [CONSTRUCTOR] :: init object
	public function __construct($y_protocol='', $y_no_content_stop_if_unauth=true) {

		//-- preset debugging
		$this->debug = 0;
		//--

		//-- set protocol: 1.0 or 1.1
		switch((string)$y_protocol) {
			case '1.1':
				$this->protocol = '1.1'; // for 1.1 the time can be significant LONGER than 1.0
				break;
			default:
			case '1.0':
				$this->protocol = '1.0'; // default is 1.0
		} //end switch
		//--

		//-- reset
		$this->reset();
		//--

		//-- inits
		$this->cookies = array();
		$this->postvars = array();
		$this->rawheaders = array();
		$this->jsonrequest = '';
		$this->xmlrequest = '';
		//--

		//-- signature
		$this->useragent = 'Mozilla/5.0 PHP.CURL.SFM ('.SMART_APP_MODULES_EXTRALIBS_VER.'/'.php_uname().')';
		//--

		//-- option
		$this->no_content_stop_if_unauth = (bool) $y_no_content_stop_if_unauth;
		//--

	} //END FUNCTION
	//==============================================


	//==============================================
	// [PUBLIC] :: set the SSL/TLS Certificate Authority File
	public function set_ssl_tls_ca_file($cafile) {
		//--
		$this->cafile = (string) $cafile;
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	// [PUBLIC] :: browse the url as a robot (auth works only with Basic authentication)
	public function browse_url($url, $method='GET', $ssl_version='', $user='', $pwd='', $proxy=array()) {

		//-- reset
		$this->reset();
		//--

		//--
		if($this->debug) {
			$run_time = microtime(true);
		} //end if
		//--

		//--
		$this->connect_timeout = (int) $this->connect_timeout;
		if($this->connect_timeout < 1) {
			$this->connect_timeout = 1;
		} //end if
		if($this->connect_timeout > 120) {
			$this->connect_timeout = 120;
		} //end if
		//--
		$this->exec_timeout = (int) $this->exec_timeout;
		if($this->exec_timeout > 0) {
			if($this->exec_timeout < 30) {
				$this->exec_timeout = 30;
			} //end if
			if($this->exec_timeout > 300) {
				$this->exec_timeout = 300;
			} //end if
		} else {
			$this->exec_timeout = 0;
		} //end if else
		//--


		//--
		$this->status = 999;
		//--

		//-- log action
		if($this->debug) {
			$this->log .= '[INF] CURL HTTP(S)/FTP Robot Browser :: Browse :: url \''.$url.'\' @ Auth-User: '.$user.' // Auth-Pass-Length: ('.strlen($pwd).') // Method: '.$method.' // SSLVersion: '.$ssl_version."\n";
			$this->log .= '[INF] CURL Protocol: '.$this->protocol."\n";
			$this->log .= '[INF] Connection TimeOut: '.$this->connect_timeout."\n";
			$this->log .= '[INF] Execution TimeOut: '.$this->exec_timeout."\n";
		} //end if
		//--

		//-- method
		$this->method = (string) strtoupper(trim((string)$method));
		//--

		//-- separations
		$this->url_parts = (array) Smart::url_parse($url);
		$protocol = (string) $this->url_parts['protocol'];
		$host = (string) $this->url_parts['host'];
		$port = (string) $this->url_parts['port'];
		$path = (string) $this->url_parts['suffix']; // path + query
		//--
		if($this->debug) {
			$this->log .= '[INF] Analize of the URL: '.@print_r($this->url_parts,1)."\n";
		} //end if
		//--

		//--
		$is_ftp = false;
		$use_ssl_tls = false;
		switch((string)$protocol) {
			case 'http://':
				break;
			case 'https://':
				$use_ssl_tls = true;
				break;
			case 'ftp://':
				$is_ftp = true;
				break;
			case 'ftps://':
				$is_ftp = true;
				$use_ssl_tls = true;
				break;
			default:
				//--
				if($this->debug) {
					$this->log .= '[ERR] Unsupported URL Type: ['.$protocol.'] for URL: '.$url."\n";
				} //end if
				//--
				Smart::log_warning('LibCurlHttp(s)Ftp // GetFromURL () // Unsupported URL Type: ['.$protocol.'] for URL: '.$url);
				//--
				return (array) $this->answer(0, $url, $ssl_version, $user);
				//--
		} //end switch
		//--

		//--
		if(!function_exists('curl_init')) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] PHP CURL Extension is missing'."\n";
			} //end if
			//--
			Smart::log_warning('LibCurlHttp(s)Ftp // GetFromURL () // CURL Extension is missing ...');
			//--
			return (array) $this->answer(0, $url, $ssl_version, $user);
			//--
		} //end if
		//--

		//--
		$this->curl = @curl_init();  // Initialise a cURL handle
		//--
		if(!$this->curl) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] PHP CURL Init Failed'."\n";
			} //end if
			//--
			Smart::log_warning('LibCurlHttp(s)Ftp // GetFromURL () // CURL Init Failed ...');
			//--
			return (array) $this->answer(0, $url, $ssl_version, $user);
			//--
		} //end if
		//--

		if(Smart::array_size($this->rawheaders) > 0) {
			foreach($this->rawheaders as $key => $val) {
				$this->raw_headers[] = (string) $key.': '.$val;
			} //end foreach
		} //end if

		//-- set allowed protocols: HTTP / HTTPS / FTP / FTPS
		@curl_setopt($this->curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FTP | CURLPROTO_FTPS);
		//--

		//-- set user agent
		@curl_setopt($this->curl, CURLOPT_USERAGENT, (string)$this->useragent);
		//--

		//-- timeouts
		@curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 	(int)$this->connect_timeout);
		if($this->exec_timeout > 0) {
			@curl_setopt($this->curl, CURLOPT_TIMEOUT, 		(int)$this->exec_timeout);
		} //end if
		//--

		//-- protocol
		if((string)$this->protocol == '1.1') {
			$this->raw_headers[] = (string) 'Connection: close';
			@curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		} else { // 1.0
			@curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		} //end if else
		//--

		//-- proxy
		$is_using_proxy = false;
		if(Smart::array_size($proxy) > 0) { // If the $proxy variable is set, then use: $proxy['ip:port'] ; $proxy['type'] ; $proxy['auth-user'] ; $proxy['auth-pass']
			//--
			if((string)$proxy['ip:port'] != '') {
				//--
				$pxy_type = '';
				switch((string)strtoupper(trim((string)$proxy['type']))) {
					case 'SOCKS4':
						$is_using_proxy = true;
						$pxy_type = CURLPROXY_SOCKS4;
						break;
					case 'SOCKS4A':
						$is_using_proxy = true;
						$pxy_type = CURLPROXY_SOCKS4A;
						break;
					case 'SOCKS5':
						$is_using_proxy = true;
						$pxy_type = CURLPROXY_SOCKS5;
						break;
					case 'SOCKS5H':
						$is_using_proxy = true;
						$pxy_type = CURLPROXY_SOCKS5_HOSTNAME;
						break;
					case 'HTTP':
					default:
						if($is_ftp) {
							$proxy['type'] = 'N/A';
						} else {
							$is_using_proxy = true;
							$proxy['type'] = 'HTTP';
						} //end if
						$pxy_type = CURLPROXY_HTTP;
				} //end switch
				//--
				if($is_using_proxy) {
					//--
					if($this->debug) {
						$this->log .= '[INF] Using Proxy: '.$proxy['ip:port'].' [Type: '.$proxy['type'].']'."\n";
					} //end if
					//--
					$this->cproxy = (array) $proxy;
					if((string)$this->cproxy['auth-pass'] != '') {
						$this->cproxy['auth-pass'] = '('.strlen($proxy['auth-pass']).') *****';
					} //end if
					//--
					@curl_setopt($this->curl, CURLOPT_PROXY, (string)$proxy['ip:port']);
					@curl_setopt($this->curl, CURLOPT_PROXYTYPE, $pxy_type);
					//--
					if((string)$proxy['auth-user'] != '') {
						//--
						if($this->debug) {
							$this->log .= '[INF] Proxy Authentication will be attempted for USERNAME = \''.$proxy['auth-user'].'\' ; PASSWORD('.strlen($proxy['auth-pass']).') *****'."\n";
						} //end if
						//--
						@curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, (string)$proxy['auth-user'].':'.$proxy['auth-pass']);
						//@curl_setopt($this->curl, CURLOPT_PROXYAUTH, CURLAUTH_ANY); // this does not work at all, thus let CURL choose ...: CURLAUTH_BASIC | CURLAUTH_DIGEST
						//--
					} //end if
					//--
				} //end if
				//--
			} //end if
			//--
		} //end if
		//--

		//-- auth
		if((string)$user != '') {
			//--
			if($this->debug) {
				$this->log .= '[INF] Authentication will be attempted for USERNAME = \''.$user.'\' ; PASSWORD('.strlen($pwd).') *****'."\n";
			} //end if
			//-- $this->raw_headers[] = 'Authorization: Basic '.base64_encode($user.':'.$pwd); // it is better to use as below as it can handle more auth types :-)
			@curl_setopt($this->curl, CURLOPT_USERPWD, (string) $user.':'.$pwd);
			//@curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY); // this does not work at all, thus let CURL choose ...: CURLAUTH_BASIC | CURLAUTH_DIGEST
			//--
		} //end if
		//--

		//-- SSL/TLS Options
		$browser_protocol = '';
		//--
		if($use_ssl_tls) {
			//--
			if(!function_exists('openssl_open')) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] PHP OpenSSL Extension is required to perform SSL requests'."\n";
				} //end if
				//--
				Smart::log_warning('LibCurlHttp(s)Ftp // GetFromURL ('.$browser_protocol.$host.':'.$port.$path.') // PHP OpenSSL Extension not installed ...');
				//--
				return (array) $this->answer(0, $url, $ssl_version, $user);
				//--
			} //end if
			//--
			switch(strtolower((string)$ssl_version)) {
				case 'ssl':
					$browser_protocol = CURL_SSLVERSION_DEFAULT; // default SSL
					break;
				case 'sslv3':
					$browser_protocol = CURL_SSLVERSION_SSLv3; // SSLv3
					break;
				case 'tls':
				default:
					$browser_protocol =  CURL_SSLVERSION_TLSv1; // TLSv1.x
			} //end switch
			//--
			@curl_setopt($this->curl, CURLOPT_SSLVERSION, $browser_protocol);
			//--
			$cafile = '';
			if((string)$this->cafile != '') {
				$cafile = (string) $this->cafile;
			} elseif(defined('SMART_FRAMEWORK_SSL_CA_FILE')) {
				if((string)SMART_FRAMEWORK_SSL_CA_FILE != '') {
					$cafile = (string) SMART_FRAMEWORK_SSL_CA_FILE;
				} //end if
			} //end if
			//--
			if((string)$cafile != '') {
				@curl_setopt($this->curl, CURLOPT_CAINFO, Smart::real_path((string)$cafile));
			} //end if
			//--
			@curl_setopt($this->curl, CURLOPT_SSL_CIPHER_LIST, (string)SMART_FRAMEWORK_SSL_CIPHERS);
			@curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, (bool)SMART_FRAMEWORK_SSL_VFY_PEER_NAME); // FIX: use vfy peer name instead of SMART_FRAMEWORK_SSL_VFY_HOST as there is no fine tunning here ...
			@curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, (bool)SMART_FRAMEWORK_SSL_VFY_PEER);
			// (bool)SMART_FRAMEWORK_SSL_VFY_PEER_NAME 		:: CURL is missing the option to specific allow/dissalow the peer name (allow also wildcard names *)
			// (bool)SMART_FRAMEWORK_SSL_ALLOW_SELF_SIGNED 	:: CURL is missing the option to specific allow/disallow self-signed certificates but verified above
			// (bool)SMART_FRAMEWORK_SSL_DISABLE_COMPRESS 	:: CURL is missing the option to disable SSL/TLS compression (help mitigate the CRIME attack vector)
			//--
		} //end if
		//--

		//-- other cURL options that are required
		@curl_setopt($this->curl, CURLOPT_HEADER, true);
		@curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
		@curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		@curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		//--

		//--
		if(Smart::array_size($this->cookies) > 0) {
			$send_cookies = '';
			foreach($this->cookies as $key => $value) {
				if((string)$key != '') {
					if((string)$value != '') {
						$send_cookies .= (string) SmartHttpUtils::encode_var_cookie($key, $value);
					} //end if
				} //end if
			} //end foreach
			if((string)$send_cookies != '') {
				$this->raw_headers[] = (string) 'Cookie: '.$send_cookies;
			} //end if
			$send_cookies = '';
		} //end if
		//--
		$have_post_vars = false;
		if(Smart::array_size($this->postvars) > 0) {
			$post_string = '';
			foreach((array)$this->postvars as $key => $val) {
				$post_string .= (string) SmartHttpUtils::encode_var_post($key, $val);
			} //end foreach
			if((string)$post_string != '') {
				if((string)$this->method == 'GET') {
					$this->method = 'POST';
				} //end if
				$have_post_vars = true;
				@curl_setopt($this->curl, CURLOPT_POSTFIELDS, (string)$post_string);
			} //end if
			$post_string = '';
		} elseif((string)$this->poststring != '') {
			if((string)$this->method == 'GET') {
				$this->method = 'POST';
			} //end if
			$have_post_vars = true;
			@curl_setopt($this->curl, CURLOPT_POSTFIELDS, (string)$this->poststring);
		} elseif((string)$this->jsonrequest != '') {
			$this->raw_headers[] = 'Content-Type: application/json';
			$this->raw_headers[] = 'Content-Length: '.strlen($this->jsonrequest);
		} elseif((string)$this->xmlrequest != '') {
			$this->raw_headers[] = 'Content-Type: application/xml';
			$this->raw_headers[] = 'Content-Length: '.strlen($this->xmlrequest);
		} //end if else
		//--
		switch((string)$this->method) {
			case 'GET':
				break;
			case 'POST':
				if($have_post_vars) {
					@curl_setopt($this->curl, CURLOPT_POST, true);
				} //end if
				break;
			case 'HEAD':
			case 'PUT':
			case 'DELETE':
			default:
				if($have_post_vars) {
					@curl_setopt($this->curl, CURLOPT_POST, true);
				} //end if
				@curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, (string)$this->method);
		} //end switch
		//--
		if(Smart::array_size($this->raw_headers) > 0) { // request headers are constructed above
			@curl_setopt($this->curl, CURLOPT_HTTPHEADER, (array)$this->raw_headers);
		} //end if
		//--

		//-- Execute a Curl request
		@curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
		@curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, true);
		@curl_setopt($this->curl, CURLOPT_FORBID_REUSE, true);
		@curl_setopt($this->curl, CURLOPT_URL, (string)$url);
		//--
		if(!$this->curl) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] CURL Aborted before Execution'."\n";
			} //end if
			//--
			Smart::log_warning('LibCurlHttp(s)Ftp // GetFromURL () // CURL Aborted before Execution ...');
			//--
			return (array) $this->answer(0, $url, $ssl_version, $user);
			//--
		} //end if
		//--
		$results = @curl_exec($this->curl);
		$error = @curl_errno($this->curl);
		//--

		//-- eval results
		$bw_info = array();
		$is_ok = 0;
		//--
		if($results) {
			//--
			$is_ok = 1;
			//--
			$bw_info = (array) @curl_getinfo($this->curl);
			//--
			if($is_ftp) {
				//--
				$this->header = 'CURL Browser :: FTP(s) have no headers ...';
				$this->body = (string) $results;
				//--
			} else { // http
				//--
				$hd_len = (int) $bw_info['header_size']; // get header length
				//--
				if($hd_len > 0) {
					//--
					$this->header = (string) substr((string)$results, 0, $hd_len);
					$this->body = (string) substr((string)$results, $hd_len);
					//--
				} else {
					//--
					$this->header = (string) $results;
					$this->body = '';
					//--
					$is_ok = 0;
					//--
					if($this->debug) {
						Smart::log_notice('LibCurlHttp(s)Ftp // GetFromURL () // CURL Execution Failed to Separe HTTP Header from Body. Reported (invalid) Header size is: ['.$hd_len.']');
						$this->log .= '[ERR] CURL Execution Failed to Separe HTTP Header from Body. Invalid Header size: ['.$hd_len.']'."\n";
					} //end if
					//--
				} //end if else
				//--
			} //end if else
			//--
			$results = ''; // free memory
			//--
			$is_unauth = false;
			if((string)$bw_info['http_code'] == '401') {
				//--
				$is_unauth = true;
				//--
				if($this->debug) {
					if((string)$user != '') {
						$this->log .= '[ERR] HTTP Authentication Failed for URL: [User='.$user.']: '.$url."\n";
						Smart::log_notice('LibCurlHttp(s)Ftp // GetFromURL // HTTP Authentication Failed for URL: [User='.$user.']: '.$url);
					} else {
						$this->log .= '[ERR] HTTP Authentication is Required for URL: '.$url."\n";
						Smart::log_notice('LibCurlHttp(s)Ftp // GetFromURL // HTTP Authentication is Required for URL: '.$url);
					} //end if
				} //end if
				//--
			} //end if
			//--
			if(($is_unauth) AND ($this->no_content_stop_if_unauth)) {
				//--
				$this->body = ''; // in this case (by settings) no content (response body) should be returned
				//--
			} //end if
			//--
			if($error) {
				//--
				$is_ok = 0;
				//--
				if($this->debug) {
					$this->log .= '[ERR] CURL Execution Reported some Errors. ErrorCode: ['.$error.']'."\n";
					Smart::log_notice('LibCurlHttp(s)Ftp // GetFromURL () // CURL Execution Reported some Errors. ErrorCode: ['.$error.']');
				} //end if
				//--
			} //end if
			//--
			$this->status = (int) $bw_info['http_code'];
			//--
		} else {
			//--
			$is_ok = 0;
			//--
			$this->log .= '[ERR] CURL Returned No Results. ErrorCode: ['.$error.']'."\n";
			//--
		} //end if
		//--
		if($is_unauth) {
			//--
			$is_ok = 0;
			//--
		} //end if
		//--

		//--
		$this->close_connection();
		//--

		//--
		if($this->debug) {
			$run_time = microtime(true) - $run_time;
			$this->log .= '[INF] Total Time: '.$run_time.' sec.'."\n";
		} //end if
		//--

		//--
		return (array) $this->answer($is_ok, $url, $ssl_version, $user, $bw_info);
		//--

	} //END FUNCTION
	//==============================================


	## PRIVATES


	//==============================================
	private function answer($result, $url, $ssl_version, $user, $curl_getinfo=array()) {
		//--
		return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
			'client' 		=> (string) __CLASS__,
			'date-time' 	=> (string) date('Y-m-d H:i:s O'),
			'protocol' 		=> (string) $this->protocol,
			'method' 		=> (string) $this->method,
			'url' 			=> (string) $url,
			'ssl'			=> (string) $ssl_version,
			'auth-user' 	=> (string) $user,
			'cookies-len' 	=> (int) Smart::array_size($this->cookies),
			'post-vars-len' => (int) Smart::array_size($this->postvars),
			'post-str-len' 	=> (int) strlen($this->poststring),
			'json-req-len' 	=> (int) strlen($this->jsonrequest),
			'xml-req-len' 	=> (int) strlen($this->jsonrequest),
			'mode' 			=> (string) trim((string)$this->url_parts['protocol']),
			'result' 		=> (int) $result,
			'code' 			=> (string) $this->status,
			'headers' 		=> (string) $this->header,
			'content' 		=> (string) $this->body,
			'log' 			=> (string) 'User-Agent: '.$this->useragent."\n", // this is reserved for calltime functions
			'debuglog' 		=> (string) $this->log, // this is for internal use
			'curl-proxy' 	=> (array) $this->cproxy, // the Proxy if Any
			'curl-info' 	=> (array) $curl_getinfo // CUSTOM (just for CURL)
		);
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	private function reset() {
		//-- the log
		$this->log = '';
		//-- outputs
		$this->status = '';
		$this->header = '';
		$this->body = '';
		//-- internals
		$this->method = 'GET';
		$this->raw_headers = array();
		$this->url_parts = array();
		$this->curl = false;
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	// [PRIVATE] :: close connection
	private function close_connection() {
		//--
		if($this->curl) {
			//--
			@curl_close($this->curl); // Closing the cURL handle
			//--
			if($this->debug) {
				$this->log .= '[INF] Connection Closed: OK.'."\n";
			} //end if
			//--
		} //end if
		//--
		$this->curl = false;
		//--
	} //END FUNCTION
	//==============================================


} //END CLASS


//===================================================== USAGE
/*
$browser = new SmartCurlHttpFtpClient();
$browser->connect_timeout = 20;
$browser->exec_timeout = 300;
print_r(
	$browser->browse_url('https://some-website.ext:443/some-path/', 'GET', 'tls')
);
*/
//=====================================================


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>