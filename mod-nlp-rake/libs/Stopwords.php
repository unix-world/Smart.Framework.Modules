<?php
// Class: \SmartModExtLib\NlpRake\Stopwords
// [Smart.Framework.Modules - NLP Rake]
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\NlpRake;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


final class Stopwords {

	private static $arrStopwords = [];

	/**
	 * Load stop words from an input file
	 */
	public static function load(string $path) {
		if(!\SmartFileSysUtils::checkIfSafePath((string)$path)) {
			\Smart::raise_error(
				'Rake Stopwords File path is invalid !',
				__METHOD__.'() The Stopwords File path is invalid: '.$path
			);
			return array();
		} //end if
		if(\Smart::array_size(self::$arrStopwords[(string)$path]) <= 0) {
			if(!\SmartFileSystem::is_type_file($path)) {
				\Smart::raise_error(
					'Rake Stopwords File cannot be found !',
					__METHOD__.'() The Stopwords File is missing: '.$path
				);
				return array();
			} //end if
			if(!\SmartFileSystem::have_access_read($path)) {
				\Smart::raise_error(
					'Rake Stopwords File cannot be read !',
					__METHOD__.'() The Stopwords File is unreadable: '.$path
				);
				return array();
			} //end if
			$stopwords = array();
			$all = (string) \SmartFileSystem::read($path);
			$all = (string) \trim((string)$all);
			$all = (string) \str_replace(["\r\n", "\r", "\t"], ["\n", "\n", ' '], (string)$all);
			$arr = (array)  \explode("\n", $all);
			foreach($arr as $kk => $line) {
				$line = (string) \trim((string)$line);
				if(((string)$line != '') AND (\strpos($line, '#') !== 0)) {
					\array_push($stopwords, $line);
				} //end if
			} //end foreach
			if(\Smart::array_size($stopwords) < 1) {
				\Smart::raise_error(
					'Rake Stopwords File is empty !',
					__METHOD__.'() The Stopwords File is empty: '.$path
				);
				return array();
			} //end if
			self::$arrStopwords[(string)$path] = (array) $stopwords;
		} //end if
		return (array) self::$arrStopwords[(string)$path];
	} //END FUNCTION


} //END CLASS


// end of php code
