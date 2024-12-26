<?php
// [LIB - Smart.Framework.Modules / ExtraLibs / LangID Service Client]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.8.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - LangID Service Client for: https://pypi.python.org/pypi/langid
// DEPENDS:
//	* Smart::
// DEPENDS-EXT: LangId.Py (https://github.com/saffsd/langid.py) installed as a service
// 				LangId.Py must be started with the following parameters: --normalize --serve
// 				Example: # ./langid.py --normalize --serve --host=127.0.0.1 --port=9008
//======================================================
// Tested and Stable on LangId.Py versions:
// 1.1.x
//======================================================
// # Sample Configuration #
/*
//-- LangID related configuration of Default LangID Service (add this in etc/config.php)
$configs['langid']['url'] 			= 'http://langid.host:9008/detect';			// LangId.Py Service URL to Detect
$configs['langid']['ssl']			= '';										// LangId.Py Service URL SSL Mode
$configs['langid']['auth-user']		= '';										// LangId.Py Service Auth User
$configs['langid']['auth-pass']		= '';										// LangId.Py Service Auth Pass
//--
*/
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartLangIdClient - provides a LangId.Py Service Client that can be used to validate Language / GetLanguage Confidence for a text.
 *
 * This class can be used just with the DEFAULT settings which must be set in etc/config.php: $configs['langid'] or can be used with CUSTOM settings.
 *
 * <code>
 *
 * $check_with_default_service = (array) (new SmartLangIdClient())->getLanguageConfidence('Your text to check goes here ...');
 * $check_with_custom_service = (array) (new SmartLangIdClient([ 'url' => 'http://langid.host:9008/detect', 'ssl' => '', 'auth-user' => '', 'auth-pass' => '' ]))->getLanguageConfidence('Your text to check goes here ...');
 *
 * </code>
 *
 * @usage 		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		If the DEFAULT settings are not available will simply fallback for not using any service.
 *
 * @depends 	classes: Smart, SmartHttpClient
 * @version 	v.20221220
 * @package 	extralibs:LanguageDetection
 *
 */
final class SmartLangIdClient {

	// ->

	private $langid_service_cfg = array();
	private $is_service_available = false;


	/**
	 * Class Constructor - will initiate also the LangId.Py Client with the DEFAULT or CUSTOM connection.
	 *
	 * @param ARRAY $cfg 					:: *OPTIONAL* The Array of Configuration parameters - if not provided will use the DEFAULT config from config.php: $configs['langid'].
	 *
	 */
	public function __construct($cfg=array()) {
		//--
		$this->langid_service_cfg = array(); // reset
		$this->is_service_available = false; // reset
		//--
		if(Smart::array_size($cfg) > 0) { // if config is specified, use it
			$this->langid_service_cfg = (array) $cfg;
		} else { // otherwise use the default config
			$this->langid_service_cfg = (array) Smart::get_from_config('langid');
		} //end if else
		//--
		// check for valid configuration array must not be done, if n/a then simply the service is n/a and will return negative value for confidence checks
		//--
	} //END FUNCTION


	/**
	 * Checks and Get the Language Confidence information for the best detected Language (from the available list) for a given text using the LangId.Py Service.
	 * If LangId.Py is not set in configuration as it may not be available will return a negative confidence score and the default language: en.
	 *
	 * @param STRING $the_text						:: The text to be checked
	 * @return ARRAY 								:: The LangId.Py detection result: [ service-available, lang-id, confidence-score, error-message ]
	 *
	 */
	public function getLanguageConfidence($the_text) {
		//--
		if((string)$this->langid_service_cfg['url'] == '') {
			return (array) $this->formatLanguageConfidenceAnswer('en', -1); // OK (LangID Service is not available ...)
		} //end if
		//--
		$this->is_service_available = true;
		//--
		$the_text = (string) trim((string)$the_text);
		if((string)$the_text == '') {
			return (array) $this->formatLanguageConfidenceAnswer('en', -2, 'NOTICE: Empty Text to check ...');
		} //end if
		//--
		$http_client = new SmartHttpClient();
		if(SmartEnvironment::ifDebug()) {
			$http_client->debug = 1;
		} //end if
		//--
		$http_timeout = (int) $http_timeout;
		if($http_timeout < 30) {
			$http_timeout = 30;
		} //end if
		if($http_timeout > 300) {
			$http_timeout = 300;
		} //end if
		$http_client->connect_timeout = $http_timeout;
		//--
		$http_client->postvars = array(
			'q' => (string) $the_text // the text
		);
		//--
		$http_data = (array) $http_client->browse_url(
			(string) $this->langid_service_cfg['url'], 'POST',
			(string) $this->langid_service_cfg['ssl'],
			(string) $this->langid_service_cfg['auth-user'],
			(string) $this->langid_service_cfg['auth-pass']
		);
		//--
		if((string)$http_data['code'] != '200') {
			return (array) $this->formatLanguageConfidenceAnswer('en', -3, 'Invalid HTTP Code != 200');
		} //end if
		//--
		$result = (array) Smart::json_decode($http_data['content']);
		//--
		if($result['responseStatus'] != 200) {
			return (array) $this->formatLanguageConfidenceAnswer('en', -4, 'Invalid (Json) Response Status != 200');
		} //end if
		if(!is_array($result['responseData'])) {
			return (array) $this->formatLanguageConfidenceAnswer('en', -5, 'Invalid (Json) Response Data Array');
		} //end if
		//--
		$result['responseData']['language'] = (string) trim((strtolower((string)$result['responseData']['language'])));
		$result['responseData']['confidence'] = (float) $result['responseData']['confidence'];
		//--
		if(strlen($result['responseData']['language']) != 2) {
			return (array) $this->formatLanguageConfidenceAnswer('en', -6, 'Invalid Language ID Length: '.$result['responseData']['language']);
		} //end if
		//--
		return (array) $this->formatLanguageConfidenceAnswer($result['responseData']['language'], $result['responseData']['confidence']); // OK
		//--
	} //END FUNCTION


	//===== PRIVATES


	/**
	 * Format Answer for getLanguageConfidence
	 *
	 * @param STRING 	$langid		Language ID ; Ex: en
	 * @param INTEGER 	$score		Confidence Score
	 * @param STRING 	$errmsg 	*OPTIONAL* Error Message
	 * @return integer
	 */
	private function formatLanguageConfidenceAnswer($langid, $score, $errmsg='') {
		//--
		return (array) [
			'service-version' 	=> (string) SMART_APP_MODULES_EXTRALIBS_VER,
			'service-available' => (bool) $this->is_service_available,
			'lang-id' 			=> (string) substr((string)strtolower((string)trim((string)$langid)), 0, 2),
			'confidence-score' 	=> (string) Smart::format_number_dec((float)$score, 5, '.', ''),
			'error-message' 	=> (string) trim((string)$errmsg),
		];
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
