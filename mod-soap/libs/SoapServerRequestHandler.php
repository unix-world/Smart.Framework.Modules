<?php
// Class: \SmartModExtLib\Soap\SoapServerRequestHandler
// [Smart.Framework.Modules - Soap]
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Soap;

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
 * Provides a SOAP Server Request Handler, full unicode compliant (UTF-8).
 * This class does NOT depend on the PHP SOAP extension
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @hints 		Use this class to easy manage SOAP calls
 *
 * @access 		PUBLIC
 * @depends 	classes: Smart, SmartXmlParser, PHP DomXML extension (DOMDocument), SmartMarkersTemplating
 * @version 	v.20210430
 * @package 	modules:Network
 *
 */
final class SoapServerRequestHandler {

	// ::

	private static $isSoapParsed 			= false;
	private static $isSoapRequest 			= null;
	private static $soapUrl 				= null;
	private static $soapAction 				= null;
	private static $soapXmlRequestStr 		= null;
	private static $soapXmlRequestArr 		= null;
	private static $soapXmlRequestBodyArr 	= null;


	/**
	 * Check if this is a SOAP Request
	 *
	 * @return BOOLEAN 		:: TRUE if this is a SOAP Request or FALSE if not
	 */
	public static function isSoapRequest() {
		//--
		return (bool) self::parseSoapRequest();
		//--
	} //END FUNCTION


	/**
	 * Get the SOAP URL from SOAP Request
	 * The SOAP clients will have to send the HTTP_SOAPACTION to comply with this Server
	 *
	 * @hints This comes from SOAP Request, raw
	 *
	 * @return STRING 		:: if this is a SOAP Request will return a non-empty URL aka the HttpSoapAction ; else will return an empty URL
	 */
	public static function getSoapRequestUrl() {
		//--
		if(self::parseSoapRequest() !== true) {
			return '';
		} //end if
		//--
		return (string) self::$soapUrl;
		//--
	} //END FUNCTION


	/**
	 * Get the SOAP Action from SOAP Request
	 * The Valid URL format to recognize the SOAP Action must be something like: urn://some-request.url/SomeSoapAction#SomeSoapAction
	 * For using with Smart.Framework URL format can be: http(s)://smart-framework.url/?/page/some.controller/action/SomeSoapAction/#SomeSoapAction
	 * If the SOAP Client is not able to handle the fragment (after the hashmark #), it can be used without it but in this case the soap action must be detected separately by parsing the getSoapRequestAction()
	 *
	 * @hints This comes from SOAP Request, processed
	 *
	 * @return STRING 		:: if this is a SOAP Request have a non-empty URL aka the HttpSoapAction will return the #SoapAction ; else will return an empty string
	 */
	public static function getSoapRequestAction() {
		//--
		if(self::parseSoapRequest() !== true) {
			return '';
		} //end if
		//--
		return (string) self::$soapAction;
		//--
	} //END FUNCTION


	/**
	 * Get the SOAP Request XML Str from SOAP Request
	 *
	 * @hints This comes from SOAP Request, raw
	 *
	 * @return STRING		:: Soap Request XML
	 */
	public static function getSoapRequestXmlStr() {
		//--
		if(self::parseSoapRequest() !== true) {
			return '';
		} //end if
		//--
		return (string) self::$soapXmlRequestStr;
		//--
	} //END FUNCTION


	/**
	 * Get the SOAP Request XML Str parsed as Array from SOAP Request
	 *
	 * @hints This comes from SOAP Request, processed
	 *
	 * @return ARRAY		:: Parsed SOAP Request XML as ARRAY
	 */
	public static function getSoapRequestXmlArr() {
		//--
		if(self::parseSoapRequest() !== true) {
			return array();
		} //end if
		//--
		return (array) self::$soapXmlRequestArr;
		//--
	} //END FUNCTION


	/**
	 * Get the SOAP Request XML Array re-parsed only for the Body section of the SOAP XML Request
	 *
	 * @hints This comes from SOAP Request, processed
	 *
	 * @return ARRAY		:: Parsed SOAP Request XML[Body] as ARRAY
	 */
	public static function getSoapRequestXmlBodyArr() {
		//--
		if(self::parseSoapRequest() !== true) {
			return array();
		} //end if
		//--
		return (array) self::$soapXmlRequestBodyArr;
		//--
	} //END FUNCTION


	/**
	 * Get the SOAP Envelope XML Response to return to the SOAP Client
	 *
	 * @hints This have to be sent as returning response to the SOAP Client
	 *
	 * @param STRING $url 		:: The current URL of the SOAP Server ; Ex: http(s)://smart-framework.url/?/page/some.controller/
	 * @param STRING $xml 		:: The XML response that will be enveloped ; this will be done using CDATA to be more compliant
	 * @param STRING $action 	:: The Response Action ; *OPTIONAL* ; Default is empty string ; If non-empty string it will use this, else will use the Action from SOAP Request
	 *
	 * @return STRING			:: The SOAP Envelope with the XML Response
	 */
	public static function getSoapResponseXmlEnvelope($url, $xml, $action='') {
		//--
		$action = (string) \trim((string)$action);
		//--
		if((string)$action == '') {
			$action = (string) \trim((string)self::getSoapRequestAction());
			if((string)$action == '') {
				return (string) self::getSoapResponseXmlError('Empty SOAP Action');
			} //end if
		} //end if
		//--
		return (string) self::buildSoapXmlEnvelopeResponse($action, $url, $xml);
		//--
	} //END FUNCTION


	/**
	 * Get the SOAP Error XML Response to return to the SOAP Client
	 *
	 * @hints This have to be sent as returning error response to the SOAP Client
	 *
	 * @param STRING $msg 	:: The Error Message
	 * @param STRING $code 	:: The Error Code ; *OPTIONAL* ; Default is: 'Server'
	 *
	 * @return STRING		:: The SOAP Error (Envelope) with the XML Error Response
	 */
	public static function getSoapResponseXmlError($msg, $code='Server') {
		//--
		return (string) self::buildSoapXmlErrorResponse($msg, $code);
		//--
	} //END FUNCTION


	//===== PRIVATES


	/*
	 * Parse the full SOAP Request to get all variables
	 *
	 * @return BOOLEAN		:: TRUE if all OK ; FALSE if something Fails
	 */
	private static function parseSoapRequest() {
		//--
		if(self::$isSoapParsed === true) {
			return true;
		} //end if
		//--
		self::$isSoapParsed = true; // set
		//--
		if(!\class_exists('\\DOMDocument')) { // must check after set above to avoid re-check on each method call
			\Smart::raise_error(__CLASS__.' requires PHP DOMDocument class from PHP DomXML extension which could not be found.');
			return false;
		} //end if
		//--
		if(self::testIsSoapRequest() !== true) {
			return false;
		} //end if
		if(self::parseSoapUrl() !== true) {
			return false;
		} //end if
		if(self::parseSoapAction() !== true) {
			return false;
		} //end if
		if(self::parseSoapXmlRequestStr() !== true) {
			return false;
		} //end if
		if(self::parseSoapXmlRequestArr() !== true) {
			return false;
		} //end if
		if(self::parseSoapXmlRequestBodyArr() !== true) {
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	private static function buildSoapXmlEnvelopeResponse($action, $url, $xml) {
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-soap/views/soap-envelope.mtpl.xml',
			[
				'ACTION' 	=> (string) $action,
				'URL' 		=> (string) $url,
				'XML' 		=> (string) $xml
			]
		);
		//--
	} //END FUNCTION


	private static function buildSoapXmlErrorResponse($msg, $code) {
		//--
		$msg = (string) \trim((string)$msg);
		if((string)$msg == '') {
			$msg = 'unknown error ...';
		} //end if
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-soap/views/soap-error.mtpl.xml',
			[
				'ERR-CODE' 	=> (string) $code,
				'ERR-MSG' 	=> (string) $msg
			]
		);
		//--
	} //END FUNCTION


	//===== All below functions are required just by self::parseSoapRequest() !!!


	private static function testIsSoapRequest() {
		//--
		if(self::$isSoapRequest !== null) {
			return (bool) self::$isSoapRequest;
		} //end if
		//--
		self::$isSoapRequest = (bool) \array_key_exists('HTTP_SOAPACTION', (array)$_SERVER); // set
		//--
		return (bool) self::$isSoapRequest;
		//--
	} //END FUNCTION


	private static function parseSoapUrl() {
		//--
		if(self::testIsSoapRequest() !== true) {
			return false;
		} //end if
		//--
		$url = (string) \SmartFrameworkSecurity::FilterUnsafeString((string)$_SERVER['HTTP_SOAPACTION']);
		$url = (string) \trim((string)$url);
		$url = (string) \trim((string)$url, '"\'');
		$url = (string) \trim((string)$url);
		//--
		self::$soapUrl = (string) $url; // set
		//--
		return true;
		//--
	} //END FUNCTION


	private static function parseSoapAction() {
		//--
		if(self::testIsSoapRequest() !== true) {
			return false;
		} //end if
		//--
		$action = '';
		if(self::$soapUrl !== null) {
			$action = (string) \trim((string)self::$soapUrl);
			if((string)$action != '') {
				$action = (string) \trim((string)\parse_url($action, \PHP_URL_FRAGMENT));
			} //end if
		} //end if
		//--
		self::$soapAction = (string) $action; // set
		//--
		return true;
		//--
	} //END FUNCTION


	private static function parseSoapXmlRequestStr() {
		//--
		if(self::testIsSoapRequest() !== true) {
			return false;
		} //end if
		//--
		self::$soapXmlRequestStr = (string) \trim((string)@\file_get_contents('php://input')); // set
		//--
		return true;
		//--
	} //END FUNCTION


	private static function parseSoapXmlRequestArr() {
		//--
		if(self::testIsSoapRequest() !== true) {
			return false;
		} //end if
		//--
		if((string)\trim((string)self::$soapXmlRequestStr) == '') {
			self::$soapXmlRequestArr = array();
		} else {
			self::$soapXmlRequestArr = (array) (new \SmartXmlParser('domxml'))->transform((string)self::$soapXmlRequestStr);
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	private static function parseSoapXmlRequestBodyArr() {
		//--
		if(self::testIsSoapRequest() !== true) {
			return false;
		} //end if
		//--
		self::$soapXmlRequestBodyArr = array(); // pre-set
		//--
		if(\Smart::array_size(self::$soapXmlRequestArr) > 0) { // set
			//--
			if(\Smart::array_size(self::$soapXmlRequestArr) > 0) {
				if((\strpos((string)self::$soapXmlRequestArr['@root'], ':Envelope') > 0)) {
					$ns = (string) \substr((string)self::$soapXmlRequestArr['@root'], 0, (int)\strpos((string)self::$soapXmlRequestArr['@root'], ':Envelope'));
					if(((string)$ns != '') AND ((string)self::$soapXmlRequestArr['@root'] == $ns.':Envelope')) {
						if(\Smart::array_size(self::$soapXmlRequestArr[$ns.':Body']) > 0) {
							foreach((array)self::$soapXmlRequestArr[$ns.':Body'] as $key => $val) {
								if((string)\trim((string)$key) != '') {
									if(\Smart::array_size($val) > 0) {
										if(!\is_array(self::$soapXmlRequestBodyArr[(string)\trim((string)$key)])) {
											self::$soapXmlRequestBodyArr[(string)\trim((string)$key)] = [];
										} //end if
										foreach($val as $kk => $vv) {
											if(\is_array($vv)) {
												if(\count($vv) > 0) {
													self::$soapXmlRequestBodyArr[(string)\trim((string)$key)][(string)$kk] = (array) $vv;
												} else {
													self::$soapXmlRequestBodyArr[(string)\trim((string)$key)][(string)$kk] = ''; // fix empty array as empty string
												} //end if else
											} else {
												self::$soapXmlRequestBodyArr[(string)\trim((string)$key)][(string)$kk] = (string) $vv;
											} //end if else
										} //end foreach
									} elseif(\is_array($val)) {
										self::$soapXmlRequestBodyArr[(string)\trim((string)$key)] = ''; // fix empty array as empty string
									} else {
										self::$soapXmlRequestBodyArr[(string)\trim((string)$key)] = (string) $val;
									} //end if else
								} //end if
							} //end foreach
						} //end if else
					} //end if
				} //end if else
			} //end if
			//--
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
