<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL;

/**
 * Twist for PHP.
 */
final class Twist { // r.20260130

	// ::

	public const MAJOR_VERSION = 1;
	public const MINOR_VERSION = 8;
	public const EXTRA_VERSION = 7;

	public const VERSION = '1.8.7'; // branch derived from 1.2.1

	public const NAME = 'Twist-TPL';

	public const INVALID_PATH = [ '/', '.', '..', '|', ':', '\\', './', '../', '/.', '/..', '@', '@/' ]; // {{{SYNC-APP-SPECIAL-PATHS}}} ; spaces are not includded here, must be trimmed !

	public const REGEX_SAFE_PATH_NAME 	= '/^[_a-zA-Z0-9\-\.@\#\/]+$/';
	public const REGEX_SAFE_FILE_NAME 	= '/^[_a-zA-Z0-9\-\.@\#]+$/';

	private const CONFIG = [

		// Separator between filters.
		'FILTER_SEPARATOR' => '\|',

		// Separator for arguments.
		'ARGUMENT_SEPARATOR' => ',',

		// Separator for argument names and values.
		'FILTER_ARGUMENT_SEPARATOR' => ':',

		// Separator for variable attributes.
		'VARIABLE_ATTRIBUTE_SEPARATOR' => '.',

		// Whitespace control.
		'WHITESPACE_CONTROL' => '-',

		// Tag start.
		'TAG_START' => '{%',

		// Tag end.
		'TAG_END' => '%}',

		// Variable start.
		'VARIABLE_START' => '{{',

		// Variable end.
		'VARIABLE_END' => '}}',

		// Variable name.
		'VARIABLE_NAME' => '[a-zA-Z_][a-zA-Z_0-9.-]*',

		'QUOTED_STRING' => '(?:"[^"]*"|\'[^\']*\')',
		'QUOTED_STRING_FILTER_ARGUMENT' => '"[^"]*"|\'[^\']*\'',

		// Suffix for include files.
		'INCLUDE_SUFFIX' => '.twist.htm',

		// Automatically escape (html) any variables unless told otherwise by a 'raw' filter
		'ESCAPE_HTML_BY_DEFAULT' => 'yes', // can be also 'no'

	];


	private static $tpls = [];

	private static $cache = null;

	private const CACHE_PREFIX = '\\TwistTPL\\Cache\\';


	/**
	 * Get a configuration setting.
	 *
	 * @param string $key setting key
	 *
	 * @return mixed: string, null, boolean
	 */
	public static function get(?string $key) : ?string {
		//--
		if(\array_key_exists((string)$key, (array)self::CONFIG)) {
			return (string) self::CONFIG[(string)$key]; // mixed
		} //end if
		//--
		switch((string)$key) { // This case is needed for compound settings
			case 'QUOTED_FRAGMENT':
				return (string) '(?:'.self::get('QUOTED_STRING').'|(?:[^\s,\|\'"]|'.self::get('QUOTED_STRING').')+)';
			case 'TAG_ATTRIBUTES':
				return (string) '/(\w+)\s*\:\s*('.self::get('QUOTED_FRAGMENT').')/';
			case 'TOKENIZATION_REGEXP':
				return (string) '/('.self::CONFIG['TAG_START'].'.*?'.self::CONFIG['TAG_END'].'|'.self::CONFIG['VARIABLE_START'].'.*?'.self::CONFIG['VARIABLE_END'].')/s';
			default:
				// will return null below
		} //end switch
		//--
		return null; // TODO: log these ...
		//--
	} //END FUNCTION


	/**
	 * @param array|AbstractCache $cache
	 *
	 * @throws \Exception
	 */
	public static function setCache($cache) : void {
		if(\is_array($cache)) {
			$classname = self::CACHE_PREFIX.ucwords((string)$cache['cache']);
			if(isset($cache['cache']) && \class_exists($classname)) {
				self::$cache = new $classname($cache);
			} else {
				throw new \Exception('Invalid cache options!');
			} //end if else
		} //end if
		if($cache instanceof AbstractCache) {
			self::$cache = $cache;
		} //end if
		if(\is_null($cache)) {
			self::$cache = null;
		} //end if
	} //END FUNCTION


	/**
	 * @return Cache
	 */
	public static function getCache() {
		return self::$cache;
	} //END FUNCTION


	/**
	 * Flatten a multidimensional array into a single array. Does not maintain keys.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function arrayFlatten(?array $array) : array {
		if(!\is_array($array)) {
			$array = [];
		} //end if
		$return = [];
		foreach($array as $key => $element) {
			if(\is_array($element)) {
				$return = (array) \array_merge((array)$return, (array)self::arrayFlatten((array)$element));
			} else {
				$return[] = $element; // mixed
			} //end if
		} //end foreach
		return (array) $return;
	} //END FUNCTION


	/**
	 * Tokenizes the given source string
	 *
	 * @param string $source
	 *
	 * @return array
	 */
	public static function tokenize(?string $source, string $tplPath, ?bool $isMain=null) : array {
		//--
		if((string)$source == '') {
			array();
		} //end if
		//--
		if((string)\trim((string)$tplPath) == '') {
			$isMain = null; // force string, have no tpl path !
		} //end if
		//--
		if($isMain === true) { // tpl
			self::$tpls[] 	= [ 'type' => 'tpl',     'hash' => (string)self::tplHash((string)$source, (string)$tplPath, true), 'name' => (string)$tplPath ];
		} elseif($isMain === false) { // sub-tpl
			self::$tpls[] 	= [ 'type' => 'sub-tpl', 'hash' => (string)self::tplHash((string)$source, (string)$tplPath, false), 'name' => (string)$tplPath ];
		} else { // string
			self::$tpls[] 	= [ 'type' => 'string',  'hash' => (string)self::tplHash((string)$source, (string)'',        null), 'name' => null ];
		} //end if else
		//--
		return (array) \preg_split(self::get('TOKENIZATION_REGEXP'), (string)$source, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
		//--
	} //END FUNCTION


	public static function setRenderedTplRecord(string $tplPath, string $tplType, string $tplHash) : void { // req. by cache to re-register
		//--
		self::$tpls[] = [ 'type' => $tplType, 'hash' => (string)$tplHash, 'name' => (string)$tplPath ];
		//--
	} //END FUNCTION


	public static function getRenderedTplRecords(string $type='') : array {
		//--
		$arr = [];
		switch((string)$type) {
			case 'string':
				foreach((array)self::$tpls as $key => $val) {
					if(\is_array($val) && isset($val['type']) && ((string)$val['type'] === (string)$type)) {
						$arr['position:'.$key] = (array) $val;
					} //end if
				} //end foreach
				break;
			case 'tpl':
			case 'sub-tpl':
				foreach((array)self::$tpls as $key => $val) {
					if(\is_array($val) && isset($val['type']) && ((string)$val['type'] === (string)$type)) {
						$arr[(string)($val['name'] ?? null)] = (array) $val; // fix for duplicates
					} //end if
				} //end foreach
				break;
			default:
				foreach((array)self::$tpls as $key => $val) {
					if(\is_array($val) && isset($val['type'])) {
						if(((string)$val['type'] === 'tpl') || ((string)$val['type'] === 'sub-tpl')) {
							$arr[(string)($val['name'] ?? null)] = (array) $val; // fix for duplicates, tpl / sub-tpl
						} else {
							$arr['position:'.$key] = (array) $val; // fix for duplicates, string
						} //end if else
					} //end if
				} //end foreach
		} //end switch
		//--
		return (array) \array_values((array)$arr);
		//--
	} //END FUNCTION


	public static function securityKey() : string {
		//--
		if(\defined('\\SMART_FRAMEWORK_SECURITY_KEY')) {
			return (string) \SMART_FRAMEWORK_SECURITY_KEY;
		} //end if
		//--
		return '';
		//--
	} //END FUNCTION


	public static function tplHash(string $source, string $tplPath, ?bool $isRoot) : string { // {{{SYNC-TWIST-TPL-HASHING}}}
		//--
		$prefix = 's-';
		if(((string)\trim((string)$tplPath) == '') OR ($isRoot === null)) {
			$tplPath = '(string)';
		} else {
			if($isRoot === true) {
				$prefix = 't-'; // tpl
			} elseif($isRoot === false) {
				$prefix = 'u-'; // sub-tpl
			} //end if else
		} //end if
		//--
		$hexHash = (string) \SmartHashCrypto::sha256((string)self::NAME.':'.self::VERSION.\chr(0).'{*'.$prefix.'*}'.self::securityKey().'{@'.$tplPath.'@}'.\chr(0).$source); // hex
		$b36Hash = (string) \trim((string)\Smart::base_from_hex_convert((string)$hexHash, 36));
		if((string)$b36Hash == '') {
			\Smart::log_warning(__METHOD__.' # B36 Hash is Empty, hex hash is: `'.$hexHash.'`');
			$b36Hash = (string) $hexHash;
		} //end if
		//--
		return (string) $prefix.$b36Hash;
		//--
	} //END FUNCTION


} //END CLASS


// #end
