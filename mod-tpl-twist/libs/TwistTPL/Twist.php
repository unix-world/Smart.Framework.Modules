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
final class Twist {

	// ::

	public const MAJOR_VERSION = 1;
	public const MINOR_VERSION = 4;
	public const EXTRA_VERSION = 3;

	public const VERSION = '1.4.3'; // branch derived from 1.2.1

	public const INVALID_PATH = [ '.', '..', '/', './', '../', '/.', '/..', ':', '@', '@/', '|' ]; // spaces are not includded here, must be trimmed !

	public const REGEX_SAFE_PATH_NAME 	= '/^[_a-zA-Z0-9\-\.@\#\/]+$/';
	public const REGEX_SAFE_FILE_NAME 	= '/^[_a-zA-Z0-9\-\.@\#]+$/';

	/**
	 * We cannot make settings constants, because we cannot create compound
	 * constants in PHP (before 5.6).
	 *
	 * @var array configuration array
	 */
	private static $config = [

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

		//--

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
		if(\array_key_exists((string)$key, self::$config)) {
			return (string) self::$config[(string)$key]; // mixed
		} //end if
		//--
		switch((string)$key) { // This case is needed for compound settings
			case 'QUOTED_FRAGMENT':
				return (string) '(?:'.self::get('QUOTED_STRING').'|(?:[^\s,\|\'"]|'.self::get('QUOTED_STRING').')+)';
			case 'TAG_ATTRIBUTES':
				return (string) '/(\w+)\s*\:\s*('.self::get('QUOTED_FRAGMENT').')/';
			case 'TOKENIZATION_REGEXP':
				return (string) '/('.self::$config['TAG_START'].'.*?'.self::$config['TAG_END'].'|'.self::$config['VARIABLE_START'].'.*?'.self::$config['VARIABLE_END'].')/s';
			default:
				// will return null below
		} //end switch
		//--
		return null; // TODO: log these ...
		//--
	} //END FUNCTION


	/**
	 * Changes/creates a setting.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public static function set(string $key, string $value) : bool {
		//-- allow here just: ESCAPE_HTML_BY_DEFAULT
		switch((string)$key) {
			case 'ESCAPE_HTML_BY_DEFAULT':
				if(\array_key_exists((string)$key, self::$config)) {
					if(((string)$value == 'yes') OR ((string)$value == 'no')) {
						self::$config[(string)$key] = (string) $value;
						return true;
					} //end if
				} //end if
				break;
			default:
				// will return false below
		} //end switch
		//--
		return false; // TODO: log these ...
		//--
	} //END FUNCTION



	/**
	 * @param array|AbstractCache $cache
	 *
	 * @throws \Exception
	 */
	public static function setCache($cache) {
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
	}


	/**
	 * Flatten a multidimensional array into a single array. Does not maintain keys.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function arrayFlatten(?array $array) {
		if(!\is_array($array)) {
			$array = [];
		} //end if
		$return = [];
		foreach($array as $key => $element) {
			if(\is_array($element)) {
				$return = \array_merge($return, self::arrayFlatten((array)$element));
			} else {
				$return[] = $element; // mixed
			} //end if
		} //end foreach
		return $return;
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


	public static function getRenderedTplRecords(string $type='') : array {
		//--
		$arr = [];
		switch((string)$type) {
			case 'string':
			case 'tpl':
			case 'sub-tpl':
				foreach((array)self::$tpls as $key => $val) {
					if(\is_array($val) && isset($val['type']) && ($val['type'] === (string)$type)) {
						$arr['position:'.$key] = (array) $val;
					} //end if
				} //end foreach
				break;
			default:
				$arr = (array) self::$tpls;
		} //end switch
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	public static function tplHash(string $source, string $tplPath, bool $isRoot) : string {
		//--
		$prefix = 's-';
		if(((string)\trim((string)$tplPath) == '') OR ($isRoot === null)) {
			$tplPath = '(string)';
		} else {
			if($isRoot === true) {
				$prefix = 't-';
			} elseif($isRoot === false) {
				$prefix = 't-';
			} //end if else
		} //end if
		//--
		return (string) $prefix.\sha1('{*'.$prefix.'*}'.'{@'.$tplPath.'@}'."\n".$source); // {{{SYNC-TWIST-TPL-HASHING}}}
		//--
	} //END FUNCTION


} //END CLASS

// #end
