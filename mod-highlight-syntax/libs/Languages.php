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


/**
 * Class: \SmartModExtLib\HighlightSyntax\Languages
 *
 * @access 		private
 * @internal
 *
 * @depends 	\SmartModExtLib\HighlightSyntax\Language
 * @version 	v.20200121
 * @package 	modules:HighlightSyntax
 *
 */
class Languages {

	// ::

	private static $classMap = array();
	private static $languages = array();
	private static $aliases = array();
	private static $allLanguagesRegistered = false;

	const DIR_OF_LANGUAGES = 'modules/mod-highlight-syntax/libs/data/';


	// pre-register all languages to be able to use aliases
	public static function registerLanguages() {
		//--
		if(self::$allLanguagesRegistered === true) {
			return; // avoid run this again in the same execution
		} //end if
		//--
		$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage((string)self::DIR_OF_LANGUAGES, false, false, '.json');
		//--
		for($i=0; $i<\Smart::array_size($files_n_dirs['list-files']); $i++) {
			$lang = (string) \Smart::base_name(
				(string)$files_n_dirs['list-files'][$i],
				'.json'
			);
			self::registerLanguage($lang);
		} //end for
		//--
		self::$allLanguagesRegistered = true;
		//--
	} //END FUNCTION


	/**
	 * Register a language definition with the Highlighter's internal language
	 * storage. Languages are stored in a static variable, so they'll be available
	 * across all instances. You only need to register a language once.
	 *
	 * @param string $languageId The unique name of a language
	 *
	 * @return Language The object containing the definition for a language's markup
	 */
	public static function registerLanguage($languageId) {
		//--
		if(!isset(self::$classMap[$languageId])) {
			//--
			$lang = new \SmartModExtLib\HighlightSyntax\Language($languageId);
			self::$classMap[$languageId] = $lang;
			if(isset($lang->mode->aliases)) {
				foreach ($lang->mode->aliases as $alias) {
					self::$aliases[$alias] = $languageId;
				} //end foreach
			} //end if
			//--
		} //end if
		//--
		self::$languages = (array) \array_keys(self::$classMap);
		//--
		return self::$classMap[$languageId];
		//--
	} //END FUNCTION


	/**
	 * Returns the List of registered languages as Array
	 */
	public static function getLanguages() {
		//--
		return (array) self::$languages;
		//--
	} //END FUNCTION


	public static function getLanguage($name) {
		//--
		if(isset(self::$classMap[$name])) {
			return self::$classMap[$name];
		} elseif(isset(self::$aliases[$name]) && isset(self::$classMap[self::$aliases[$name]])) {
			return self::$classMap[self::$aliases[$name]];
		} else {
			return self::registerLanguage($name);
		} //end if else
		//--
		\Smart::raise_error(__METHOD__.' :: Unknown language: '.$name);
		return null;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
