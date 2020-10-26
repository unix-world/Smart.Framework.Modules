<?php

namespace SmartModExtLib\NlpLemmatizer;

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
class Lemmatizer {


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
		if(!\SmartFileSysUtils::check_if_safe_path($path)) {
			\Smart::raise_error(
				'Lemmatizer Data File path is invalid !',
				__METHOD__.'() The Data File path is invalid: '.$path
			);
			return '';
		} //end if
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
		if(\array_key_exists((string)$input, (array)$map)) {
			return (string) $map[(string)$input];
		} //end if
		return (string) $input;
	} //END FUNCTION


} //END CLASS


// end of php file
