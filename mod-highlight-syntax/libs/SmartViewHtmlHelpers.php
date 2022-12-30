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
 * Class: SmartViewHelpers - Easy to use HTML ViewHelper Components for Syntax Highlight.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20221219
 * @package 	Plugins:ViewComponents
 *
 */
class SmartViewHtmlHelpers {

	// ::


	//================================================================
	/**
	 * Return the HTML / CSS / Javascript code to Load the required Javascripts for the prism.js
	 * If a valid DOM Selector is specified all code areas in that dom selector will be highlighted
	 * This function should not be rendered more than once in a HTML page
	 *
	 * @param STRING 	$dom_selector			A valid jQuery HTML-DOM Selector as container(s) for Pre>Code (see jQuery ...) ; Can be: 'body', '#id-element', ...
	 * @param ENUM 		$theme 					The Visual CSS Theme to Load ; By default is set to '' which loads the default theme ('github-gist') ; List of Available Themes: 'atom-one-light', 'dark', 'default', 'github', 'github-dark', 'googlecode', 'ocean', 'tomorrow-night-blue', 'xcode', 'zenburn'
	 * @param BOOL 		$use_absolute_url 		If TRUE will use full URL prefix to load CSS and Javascripts ; Default is FALSE
	 *
	 * @return STRING							[HTML Code]
	 */
	public static function htmlJsLoadHilightCodeSyntax($dom_selector, $theme='', $use_absolute_url=false) {
		//--
		if($use_absolute_url !== true) {
			$the_abs_url = '';
		} else {
			$the_abs_url = (string) \SmartUtils::get_server_current_url();
		} //end if else
		//--
		$theme = (string) \strtolower((string)$theme);
		switch((string)$theme) {
			case 'atom-one-light':
			case 'dark':
			case 'default':
			case 'dracula':
			case 'github':
			case 'github-dark':
			case 'googlecode':
			case 'ocean':
			case 'tomorrow-night-blue':
			case 'xcode':
			case 'zenburn':
				$theme = (string) $theme;
				break;
			case 'github-gist':
			case '':
			default:
				$theme = 'github-gist';
		} //end switch
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-highlight-syntax/libs/templates/syntax-highlight-init-and-process.inc.htm',
			[
				'HLJS-PREFIX-URL' 	=> (string) $the_abs_url,
				'CSS-THEME' 		=> (string) $theme,
				'AREAS-SELECTOR' 	=> (string) $dom_selector,
			]
		);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Get the HighlightJs Syntax type for a file type
	 *
	 * @param STRING 	$path				The file path or file name (includding file extension)
	 *
	 * @return ARRAY						[ type, pack ]
	 */
	public static function getSyntaxByFileType(?string $path) {
		//--
		$path = (string) $path;
		//--
		$fname = (string) \SmartFileSysUtils::extractPathFileName((string)$path);
		$fext = (string) \SmartFileSysUtils::extractPathFileExtension((string)$fname);
		$fext = (string) \strtolower((string)\trim((string)$fext));
		//--
		$packs = []; // avoid return empty string on pack as this will load all packs
		$ftype = '';
		//--
		switch((string)$fext) { // {{{SYNC-HIGHLIGHT-FTYPE-PACK}}}
			//-- web
			case 'css':
				$ftype = 'css';
				break;
			case 'diff':
			case 'patch':
				$ftype = 'diff';
				break;
			case 'ini':
			case 'toml': // rust cargo def
				$ftype = 'ini';
				break;
			case 'js':
			case 'gjs':
				$ftype = 'javascript';
				break;
			case 'json':
				$ftype = 'json';
				break;
			case 'less':
				$ftype = 'less';
				break;
			case 'rst': // similar with markdown
			case 'md':
			case 'markdown':
				$ftype = 'markdown';
				break;
			case 'sql':
				$ftype = 'sql';
				break;
			case 'pgsql':
				$ftype = 'pgsql';
				break;
			case 'php':
			case 'phps':
			case 'php3':
			case 'php4':
			case 'php5':
			case 'php6': // n/a
			case 'php7':
			case 'php8':
			case 'php9':
			case 'hh': // hip hop, a kind of static PHP
				$ftype = 'php';
				break;
			case 'scss':
				$ftype = 'scss';
				break;
			case 'yaml':
			case 'yml':
				$ftype = 'yaml';
				break;
				break;
			case 'xml':
			case 'svg':
			case 'html':
			case 'glade': // gnome glade xml
			case 'ui': // qt ui xml
			case 'jsp': // java server page
			case 'asp': // active server page
				$ftype = 'xml';
				break;
			//-- tpl (depends on web)
			case 'mtpl':
			case 'htm':
				$ftype = 'markertpl';
				break;
			//-- lnx
			case 'awk':
				$ftype = 'awk';
				break;
			case 'pl':
			case 'pm':
				$ftype = 'perl';
				break;
			case 'bash':
				$ftype = 'bash';
				break;
			case 'sh':
				$ftype = 'shell';
				break;
			//-- srv
			case 'dns':
				$ftype = 'dns';
				break;
			//-- net
			case 'csp':
				$ftype = 'csp';
				break;
			case 'httph':
				$ftype = 'http';
				break;
			//-- lang
			case 'coffee':
			case 'cson':
				$ftype = 'coffeescript';
				break;
			case 'c':
			case 'h':
			case 'cpp':
			case 'hpp':
			case 'cxx':
			case 'hxx':
				$ftype = 'cpp';
				break;
			case 'go':
				$ftype = 'go';
				break;
			case 'dart':
				$ftype = 'dart';
				break;
			case 'lua':
				$ftype = 'lua';
				break;
			case 'py':
				$ftype = 'python';
				break;
			case 'rb':
				$ftype = 'ruby';
				break;
			case 'rs':
				$ftype = 'rust';
				break;
			case 'tcl':
			case 'tk':
				$ftype = 'tcl';
				break;
			case 'vala':
			case 'vapi':
				$ftype = 'vala';
				break;
			//--
			default:
				// no handler
		} //end switch
		//--
		if(\stripos((string)$fname, '.mtpl.') !== false) {
			$ftype = 'markertpl';
		} elseif(
			(\in_array((string)$ftype, ['html', 'htm', 'js', 'json', 'css', 'xml', 'markdown', 'md', 'txt', 'log', 'yaml', 'yml'])) AND
			((\stripos((string)$fname, '.inc.') !== false) OR (\stripos((string)$fname, '.tpl.') !== false))
		) {
			$ftype = 'markertpl';
		} elseif(\stripos((string)$fname, '.t3fluid.') !== false) {
			$ftype = 'xml';
		} elseif((string)\strtolower((string)$fname) == 'cmake') {
			$ftype = 'cmake';
		} elseif((string)\strtolower((string)$fname) == 'makefile') {
			$ftype = 'makefile';
		} elseif((string)$fname == 'pf.conf') {
			$ftype = 'pf';
		} elseif(
			((string)$fname == 'httpd.conf') OR
			((string)$fname == 'httpd2.conf') OR
			((string)$fname == 'apache.conf') OR
			((string)$fname == 'apache2.conf') OR
			((string)$fname == 'hosts.conf') OR
			((string)$fname == 'svn.conf') OR // apache svn conf
			((string)$fname == '.htaccess') OR // apache .htaccess (.htpasswd is plain text only)
			((\strpos((string)$fname, 'php-') === 0) AND ((string)\substr((string)$fname, -5, 5) == '.conf')) OR // apache php conf
			((\strpos((string)$fname, 'mod-') === 0) AND ((string)\substr((string)$fname, -5, 5) == '.conf')) OR // apache module conf
			(((\strpos((string)$fname, 'httpd-') === 0) OR (\strpos((string)$fname, 'httpd_') === 0)) AND ((string)\substr((string)$fname, -5, 5) == '.conf'))
		) {
			$ftype = 'apache';
		} //end if
		//--
		if((string)$ftype != '') {
			$packs[] = 'base';
		} else {
			//--
			switch((string)$fext) { // {{{SYNC-HIGHLIGHT-FTYPE-PACK}}}
				//-- tpl (depends on SF.web)
				case 'dust':
					$ftype = 'dust';
					break;
				case 'twist':
				case 'twig':
					$ftype = 'twig';
					break;
				case 'latte':
					$ftype = 'latte';
					break;
				case 'django':
					$ftype = 'django';
					break;
				//-- lang
				case 'r':
					$ftype = 'r';
					break;
				case 'd':
					$ftype = 'd';
					break;
				case 'basic':
				case 'bas':
					$ftype = 'basic';
					break;
				case 'pas':
					$ftype = 'delphi';
					break;
				case 'f':
					$ftype = 'fortran';
					break;
				case 'fsharp':
				case 'fs':
					$ftype = 'fsharp';
					break;
				case 'csharp':
				case 'cs':
					$ftype = 'cs';
					break;
				case 'swift':
					$ftype = 'swift';
					break;
				case 'm':
					$ftype = 'objectivec';
					break;
				case 'hx':
				case 'hxml':
					$ftype = 'haxe';
					break;
				case 'hs':
				case 'lhs':
					$ftype = 'haskell';
					break;
				case 'java':
					$ftype = 'java';
					break;
				case 'groovy':
				case 'gvy':
				case 'gy':
					$ftype = 'groovy';
					break;
				case 'kotlin':
				case 'kt':
				case 'ktm':
					$ftype = 'kotlin';
					break;
				case 'scala':
				case 'sc':
					$ftype = 'scala';
					break;
				case 'ocaml':
				case 'ml':
					$ftype = 'ocaml';
					break;
				case 'erl':
				case 'hrl':
					$ftype = 'erlang';
					break;
				case 'openscad':
				case 'jscad':
				case 'scad':
				case 'stl':
				case 'obj':
					$ftype = 'openscad';
					break;
				//-- ms
				case 'cmd':
				case 'bat':
					$ftype = 'dos';
					break;
				case 'ps1':
				case 'psm1':
				case 'psd1':
					$ftype = 'powershell';
					break;
				case 'ts':
				case 'tsx':
					$ftype = 'typescript';
					break;
				case 'vb':
					$ftype = 'vbnet';
					break;
				case 'vbs':
					$ftype = 'vbscript';
					break;
				//-- net
				case 'ldif':
					$ftype = 'ldif';
					break;
				case 'protobuf':
				case 'pb':
					$ftype = 'protobuf';
					break;
				//-- hw
				case 'vhd':
					$ftype = 'vhdl';
					break;
				case 'll':
				case 's':
					$ftype = 'llvm';
					break;
				case 'asm':
					$ftype = 'x86asm';
					break;
				case 'aasm':
					$ftype = 'armasm';
					break;
				case 'masm':
					$ftype = 'mipsasm';
					break;
				//--
				default:
					// no handler
			} //end switch
			//--
			if(\stripos((string)$fname, '.dust.') !== false) {
				$ftype = 'dust';
			} elseif(\stripos((string)$fname, '.twist.') !== false) {
				$ftype = 'twig';
			} elseif(\stripos((string)$fname, '.twig.') !== false) {
				$ftype = 'twig';
			} elseif(\stripos((string)$fname, '.latte.') !== false) {
				$ftype = 'latte';
			} elseif(\stripos((string)$fname, '.django.') !== false) {
				$ftype = 'django';
			} //end if
			//--
			if((string)$ftype != '') {
				$packs[] = 'extra';
			} //end if
			//--
		} //end if
		//--
		return array(
			'type' => (string) $ftype,
			'pack' => (array)  $packs,
		);
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
