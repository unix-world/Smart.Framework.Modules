<?php
// Module Lib: \SmartModExtLib\Cloud\vCardParser

namespace SmartModExtLib\Cloud;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

//namespace vCard;


/**
 * vCard class for parsing a vCard and/or creating one
 *
 * link: https://github.com/nuovo/vCard-parser
 * author: Martins Pilsetnieks, Roberts Bruveris
 * see: RFC 2426, RFC 2425
 * version: 0.4.8.uxm.180220
 *
 * Modified by unixman
 * (c) 2018 unix-world.org
 *
*/


class vCardParser {


	private $mode_ERROR = 'error';
	private $mode_SINGLE = 'single';
	private $mode_MULTIPLE = 'multiple';

	private $endl = "\n";

	/**
	 * @var string Current object mode - error, single or multiple (for a single vCard within a file and multiple combined vCards)
	 */
	private $Mode;  //single, multiple, error

	private $RawData = '';

	/**
	 * @var array Internal data container. Contains vCard objects for multiple vCards and just the data for single vCards.
	 */
	private $Data = array();

	/**
	 * Parts of structured elements according to the spec.
	 */
	private $Spec_StructuredElements = array(
		'n'   => array('LastName', 'FirstName', 'AdditionalNames', 'Prefixes', 'Suffixes'),
		'adr' => array('POBox', 'ExtendedAddress', 'StreetAddress', 'Locality', 'Region', 'PostalCode', 'Country'),
		'geo' => array('Latitude', 'Longitude'),
		'org' => array('Name', 'Unit1', 'Unit2')
	);
	private $Spec_MultipleValueElements = array('nickname', 'categories');

	private $Spec_ElementTypes = array(
		'email' => array('internet', 'x400', 'pref'),
		'adr' 	=> array('dom', 'intl', 'postal', 'parcel', 'home', 'work', 'pref'),
		'label' => array('dom', 'intl', 'postal', 'parcel', 'home', 'work', 'pref'),
		'tel' 	=> array('home', 'msg', 'work', 'pref', 'voice', 'fax', 'cell', 'video', 'pager', 'bbs', 'modem', 'car', 'isdn', 'pcs'),
		'impp' 	=> array('personal', 'business', 'home', 'work', 'mobile', 'pref')
	);

	private $Spec_FileElements = array('photo', 'logo', 'sound');


	/**
	 * vCard Parse constructor
	 *
	 * @param string Raw data
	 *
	 * One of these parameters must be provided, otherwise an exception is thrown.
	 */
	public function __construct($RawData) {

		// Checking preconditions for the parser.
		// If path is given, the file should be accessible.
		// If raw data is given, it is taken as it is.
		// In both cases the real content is put in $this->RawData

		$this->RawData = (string) $RawData;
		if(!$this->RawData) {
			return false;
		} //end if

		// Counting the begin/end separators. If there aren't any or the count doesn't match, there is a problem with the file.
		// If there is only one, this is a single vCard, if more, multiple vCards are combined.
		$Matches = array();
		$vCardBeginCount = preg_match_all('{^BEGIN\:VCARD}miS', $this->RawData, $Matches);
		$vCardEndCount = preg_match_all('{^END\:VCARD}miS', $this->RawData, $Matches);
		// echo ('a:'.$vCardBeginCount.'/'.$vCardEndCount).'<br>';
		if(($vCardBeginCount != $vCardEndCount) || !$vCardBeginCount) {
			$this->Mode = $this->mode_ERROR;
		//	throw new Exception('vCard: invalid vCard');
			return false;
		} //end if

		$this->Mode = $vCardBeginCount == 1 ? $this->mode_SINGLE : $this->mode_MULTIPLE;

		// Removing/changing inappropriate newlines, i.e., all CRs or multiple newlines are changed to a single newline
		$this->RawData = str_replace(["\r\n", "\r"], ["\n", "\n"], $this->RawData); // fix by unixman
	//	$this->RawData = preg_replace('{(\n+)}', "\n", $this->RawData); // fix by unixman

		$ClassName = get_class($this);

		// In multiple card mode the raw text is split at card beginning markers and each
		//	fragment is parsed in a separate vCard object.
		if($this->Mode == $this->mode_MULTIPLE) {

			$this->RawData = explode("\n".'END:VCARD', $this->RawData); // fix by unixman as it fails to explode by begin because may be not \n before and if space then fails
			$this->RawData = array_filter($this->RawData);

			foreach($this->RawData as $kk => $SinglevCardRawData) {
				// Prepending "BEGIN:VCARD" to the raw string because we exploded on that one.
				// If there won't be the BEGIN marker in the new object, it will fail.
				$SinglevCardRawData = $SinglevCardRawData."\n".'END:VCARD'; // fix by unixman: recompose with what exploded by
				$this->Data[] = (array) (new $ClassName((string)$SinglevCardRawData))->getParsedData();
			} //end foreach

		} else {

			if(strpos($this->RawData, "\n".'X-ADDRESSBOOKSERVER-KIND:group')) {
				return false; // avoid parse apple addressbook groups
			} //end if

			// Protect the BASE64 final = sign (detected by the line beginning with whitespace), otherwise the next replace will get rid of it
			$this->RawData = preg_replace('{(\n\s.+)=(\n)}', '$1-base64=-$2', $this->RawData);

			// Joining multiple lines that are split with a hard wrap and indicated by an equals sign at the end of line
			// (quoted-printable-encoded values in v2.1 vCards)
			$this->RawData = str_replace("=\n", '', $this->RawData);

			// Joining multiple lines that are split with a soft wrap (space or tab on the beginning of the next line
			$this->RawData = str_replace(array("\n ", "\n\t"), '-^#^_^wr@p^_^#^-', $this->RawData);

			// Restoring the BASE64 final equals sign (see a few lines above)
			$this->RawData = str_replace("-base64=-\n", "=\n", $this->RawData);

			$Lines = explode("\n", $this->RawData);

			foreach($Lines as $kk => $Line) {

				// Lines without colons are skipped because, most likely, they contain no data.
				if(strpos($Line, ':') === false) {
					continue;
				} //end if

				// Each line is split into two parts. The key contains the element name and additional parameters, if present,
				//	value is just the value
				list($Key, $Value) = explode(':', $Line, 2);

				// Key is transformed to lowercase because, even though the element and parameter names are written in uppercase,
				//	it is quite possible that they will be in lower- or mixed case.
				// The key is trimmed to allow for non-significant WSP characters as allowed by v2.1
				$Key = strtolower(trim($this->Unescape($Key)));

				// These two lines can be skipped as they aren't necessary at all.
				if($Key == 'begin' || $Key == 'end') {
					continue;
				} //end if

				if((strpos($Key, 'agent') === 0) && (stripos($Value, 'begin:vcard') !== false)) {
					$Value = (array) (new $ClassName((string)str_replace('-^#^_^wr@p^_^#^-', "\n", $Value)))->getParsedData();
					if(!isset($this->Data[$Key])) {
						$this->Data[$Key] = array();
					} //end if
					$this->Data[$Key][] = $Value;
					continue;
				} else {
					$Value = str_replace('-^#^_^wr@p^_^#^-', '', $Value);
				} //end if else

				$Value = trim($this->Unescape($Value));
				$Type = array();

				// Here additional parameters are parsed
				$KeyParts = explode(';', $Key);
				$Key = $KeyParts[0];
				$Encoding = false;

				if(strpos($Key, 'item') === 0) {
					$TmpKey = explode('.', $Key, 2);
					$Key = $TmpKey[1];
					$ItemIndex = (int)str_ireplace('item', '', $TmpKey[0]);
				} //end if

				if(count($KeyParts) > 1) {
					$Parameters = $this->ParseParameters($Key, array_slice($KeyParts, 1));
					foreach($Parameters as $ParamKey => $ParamValue) {
						switch($ParamKey) {
							case 'encoding':
								$Encoding = $ParamValue;
								if(in_array($ParamValue, array('b', 'base64'))) {
									//$Value = base64_decode($Value);
								} elseif($ParamValue == 'quoted-printable') { // v2.1
									$Value = quoted_printable_decode($Value);
								} //end if else
								break;
							case 'charset': // v2.1
								if($ParamValue != 'utf-8' && $ParamValue != 'utf8') {
									$Value = mb_convert_encoding($Value, 'UTF-8', $ParamValue);
								} //end if
								break;
							case 'type':
								$Type = $ParamValue;
								break;
						} //end switch
					} //end foreach
				} //end if

				// Checking files for colon-separated additional parameters (Apple's Address Book does this), for example, "X-ABCROP-RECTANGLE" for photos
				if(in_array($Key, $this->Spec_FileElements) && isset($Parameters['encoding']) && in_array($Parameters['encoding'], array('b', 'base64'))) {
					// If colon is present in the value, it must contain Address Book parameters
					//	(colon is an invalid character for base64 so it shouldn't appear in valid files)
					if(strpos($Value, ':') !== false) {
						$Value = explode(':', $Value);
						$Value = array_pop($Value);
					} //end if
				} //end if

				// Values are parsed according to their type
				if(isset($this->Spec_StructuredElements[$Key])) {
					$Value = $this->ParseStructuredValue($Value, $Key);
					if($Type) {
						$Value['Type'] = $Type;
					} //end if
				} else {
					if(in_array($Key, $this->Spec_MultipleValueElements)) {
						$Value = $this->ParseMultipleTextValue($Value, $Key);
					} //end if
					if($Type) {
						$Value = array(
							'Value' => $Value,
							'Type' => $Type
						);
					} //end if
				} //end if else

				if(is_array($Value) && $Encoding) {
					$Value['Encoding'] = $Encoding;
				} //end if

				if(!isset($this->Data[$Key])) {
					$this->Data[$Key] = array();
				} //end if

				$this->Data[$Key][] = $Value;

			} //end foreach

		} //end if else

		return true;

	} //END FUNCTION


	public function getParsedData() {
		return (array) $this->Data;
	} //END FUNCTION


	//##### PRIVATES


 	/**
	 * Removes the escaping slashes from the text.
	 *
	 * @access private
	 *
	 * @param string Text to prepare.
	 *
	 * @return string Resulting text.
	 */
	private function Unescape($Text) {
		return str_replace(array('\:', '\;', '\,', "\n"), array(':', ';', ',', ''), $Text);
	} //END FUNCTION


	/**
	 * Separates the various parts of a structured value according to the spec.
	 *
	 * @access private
	 *
	 * @param string Raw text string
	 * @param string Key (e.g., N, ADR, ORG, etc.)
	 *
	 * @return array Parts in an associative array.
	 */
	private function ParseStructuredValue($Text, $Key) {
		$Text = array_map('trim', explode(';', $Text));
		$Result = array();
		$Ctr = 0;
		foreach($this->Spec_StructuredElements[$Key] as $Index => $StructurePart) {
			$Result[$StructurePart] = isset($Text[$Index]) ? $Text[$Index] : null;
		} //end foreach
		return $Result;
	} //END FUNCTION


	/**
	 * @access private
	 */
	private function ParseMultipleTextValue($Text) {
		return explode(',', (string)$Text);
	} //END FUNCTION


	/**
	 * @access private
	 */
	private function ParseParameters($Key, array $RawParams = null) {

		if(!$RawParams) {
			return array();
		} //end if

		// Parameters are split into (key, value) pairs
		$Parameters = array();
		foreach($RawParams as $kk => $Item) {
			$Parameters[] = explode('=', strtolower($Item));
		} //end foreach

		$Type = array();
		$Result = array();

		// And each parameter is checked whether anything can/should be done because of it
		foreach($Parameters as $Index => $Parameter) {

			// Skipping empty elements
			if(!$Parameter) {
				continue;
			} //end if

			// Handling type parameters without the explicit TYPE parameter name (2.1 valid)
			if(count($Parameter) == 1) {
				// Checks if the type value is allowed for the specific element
				// The second part of the "if" statement means that email elements can have non-standard types (see the spec)
				if((isset($this->Spec_ElementTypes[$Key]) && in_array($Parameter[0], $this->Spec_ElementTypes[$Key])) || ($Key == 'email' && is_scalar($Parameter[0]))) {
					$Type[] = $Parameter[0];
				} //end if
			} elseif(count($Parameter) > 2) {
				$TempTypeParams = $this->ParseParameters($Key, explode(',', $RawParams[$Index]));
				if($TempTypeParams['type']) {
					$Type = array_merge($Type, $TempTypeParams['type']);
				} //end if
			} else {
				switch($Parameter[0]) {
					case 'encoding':
						if(in_array($Parameter[1], array('quoted-printable', 'b', 'base64'))) {
							$Result['encoding'] = $Parameter[1] == 'base64' ? 'b' : $Parameter[1];
						} //end if
						break;
					case 'charset':
						$Result['charset'] = $Parameter[1];
						break;
					case 'type':
						$Type = array_merge($Type, explode(',', $Parameter[1]));
						break;
					case 'value':
						if(strtolower($Parameter[1]) == 'url') {
							$Result['encoding'] = 'uri';
						} //end if
						break;
				} //end switch
			} //end if else

		} //end foreach

		$Result['type'] = $Type;

		return $Result;

	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>