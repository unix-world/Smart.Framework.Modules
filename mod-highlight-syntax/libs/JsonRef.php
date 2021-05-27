<?php
// PHP Syntax Highlight for Smart.Framework
// Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\HighlightSyntax;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


//----------------------------------------------------------------------
// This class is based on scrivo/highlight.php v.9.15.10.0
// https://github.com/scrivo/highlight.php
//
// Copyright (c)
// - 2014-2019  Geert Bergman (geert@scrivo.nl), highlight.php
// - 2019-2020  unixman (unix-world.org)
// License: BSD
//
//----------------------------------------------------------------------


/**
 * Class: \SmartModExtLib\HighlightSyntax\JsonRef
 *
 * Decode JSON data that contains path-based references.
 *
 * The language data file for highlight.js are written as JavaScript classes
 * and therefore may contain variables. This allows for inner references in
 * the language data. This kind of data can be converterd to JSON using the
 * path based references. This class can be used to decode such JSON
 * structures. It follows the conventions for path based referencing as
 * used in dojox.json.ref form the Dojo toolkit (Javascript). A typical
 * example of such a structure is as follows:
 *
 * @access 		private
 * @internal
 *
 * @depends 	-
 * @version 	v.20200121
 * @package 	modules:HighlightSyntax
 *
 * <code>
 *
 * //-- json data structure: $data_json
 * // {
 * //   "name":"John Doe",
 * //   "children":[{"name":"Marry Doe"},{"name":"Ian Doe"}],
 * //   "spouse":{
 * //     "name":"Nicole Doe",
 * //     "spouse":{"$ref":"#"},
 * //     "children":{"$ref":"#children"}
 * //   },
 * //   "oldestChild":{"$ref":"#children.0"}
 * // }
 * //-- Sample Usage:
 * $jr = new \SmartModExtLib\HighlightSyntax\JsonRef();
 * $data = $jr->process(json_decode($data_json));
 * echo $data->spouse->spouse->name; // outputs 'John Doe'
 * echo $data->oldestChild->name; // outputs 'Marry Doe'
 *
 * </code>
 *
 */
class JsonRef {

	// ->


	private $paths = null; // Array to hold all data paths in the given JSON data.


	/**
	 * Decode JSON data that may contain path based references and resolve references.
	 *
	 * @param object $json JSON data string or JSON data object
	 *
	 * @return mixed The decoded JSON data
	 */
	public function process($json) {
		//-- Clear the path array.
		$this->paths = array();
		//-- Get all data paths.
		$this->getPaths($json);
		//-- Resolve all path references.
		$this->resolvePathReferences($json);
		//-- Return the data.
		return $json; // object
		//--
	} //END FUNCTION


	/**
	 * Recurse through the data tree and fill an array of paths that reference
	 * the nodes in the decoded JSON data structure.
	 *
	 * @param mixed  $s Decoded JSON data (decoded with json_decode)
	 * @param string $r The current path key (for example: '#children.0').
	 */
	private function getPaths(&$s, $r='#') {
		$this->paths[$r] = &$s;
		if(\is_array($s) || \is_object($s)) {
			foreach($s as $k => &$v) {
				if($k !== '$ref') {
					$this->getPaths($v, $r == '#' ? '#'.$k : $r.'.'.$k);
				} //end if
			} //end foreach
		} //end if
	} //END FUNCTION


	/**
	 * Recurse through the data tree and resolve all path references.
	 *
	 * @param mixed $s Decoded JSON data (decoded with json_decode)
	 */
	private function resolvePathReferences(&$s, $limit=20, $depth=1) {
		if($depth >= $limit) {
			return;
		} //end if
		++$depth;
		if(\is_array($s) || \is_object($s)) {
			foreach($s as $k => &$v) {
				if($k === '$ref') {
					$s = $this->paths[$v];
				} else {
					$this->resolvePathReferences($v, $limit, $depth);
				} //end if else
			} //end foreach
		} //end if
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
