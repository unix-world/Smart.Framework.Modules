<?php
// Class: \SmartModExtLib\NlpLemmatizer\Lemmatizer
// [Smart.Framework.Modules - NLP Lemmatizer]
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\NlpLemmatizer;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Class Lemmatizer.
 *
 * A non-part-of-speech lemmatizer tool.
 * @author markfullmer <mfullmer@gmail.com>
 * @link https://github.com/writecrow/lemmatizer/
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @author unixman, (c) 2020 unix-world.org
 * @version 1.1
 *
 */
final class Lemmatizer {

	private static $arrLemas = [];


	/**
	* Given a word, return its lemma form.
	*
	* @param string $input
	*    A string (word).
	*
	* @return string
	*    The lemmatized word.
	*/
	public static function getLemma(string $input) {
		$path = 'modules/mod-nlp-lemmatizer/libs/data/en/lemmas.json';
		return self::getFromMap((string)$input, (string)$path);
	} //END FUNCTION


	/**
	* Given a lemma root, return all words that would map to that lemma.
	*
	* @param string $input
	*    A string (word).
	*
	* @return string
	*    Comma-separated list of words.
	*/
	public static function getWordsFromLemma(string $input) {
		$path = 'modules/mod-nlp-lemmatizer/libs/data/en/roots.json';
		return self::getFromMap((string)$input, (string)$path);
	} //END FUNCTION


	private static function getFromMap(string $input, string $path) {
		$input = (string) \trim((string)$input);
		if((string)$input == '') {
			return '';
		} //end if
		if(!\SmartFileSysUtils::checkIfSafePath((string)$path)) {
			\Smart::raise_error(
				'Lemmatizer Data File path is invalid !',
				__METHOD__.'() The Data File path is invalid: '.$path
			);
			return '';
		} //end if
		if(\Smart::array_size(self::$arrLemas[(string)$path]) <= 0) {
			if(!\SmartFileSystem::is_type_file($path) OR !\SmartFileSystem::have_access_read($path)) {
				\Smart::raise_error(
					'Lemmatizer Data File cannot be read !',
					__METHOD__.'() The Data File is unreadable: '.$path
				);
				return '';
			} //end if
			$contents = (string) \SmartFileSystem::read($path);
			$map = \Smart::json_decode($contents);
			if(!\is_array($map)) {
				$map = array();
			} //end if
			if(\Smart::array_size($map) <= 0) {
				\Smart::raise_error(
					'Lemmatizer Data File cannot is empty or invalid !',
					__METHOD__.'() The Data File is empty or invalid: '.$path
				);
				return '';
			} //end if
			self::$arrLemas[(string)$path] = (array) $map;
			$map = null; // free mem
		} //end if
		if(\array_key_exists((string)$input, (array)self::$arrLemas[(string)$path])) {
			return (string) self::$arrLemas[(string)$path][(string)$input];
		} //end if
		return (string) $input;
	} //END FUNCTION


} //END CLASS


// end of php file
