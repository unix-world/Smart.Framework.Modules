<?php
// PHP Syntax Highlight for Smart.Framework
// Module Library
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

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
 * @version 	v.20210327
 * @package 	Plugins:ViewComponents
 *
 */
class SmartViewHtmlHelpers {

	// ::


	//================================================================
	/**
	 * Return the HTML / CSS / Javascript code to Load the extended syntax Javascripts for the Highlight.Js
	 * This function should not be rendered more than once in a HTML page
	 *
	 * @param MIXED 	$plugins 				NULL to load all or ARRAY with enum of packages to load (available Plugins: 'web', 'tpl', 'lnx', 'lnx', 'srv', 'net', 'lang') ; Default is set to load only 'web'
	 * @param BOOL 		$loadpacks 				If TRUE will load syntax packed files instead of syntax single files which are many ; Default is TRUE
	 * @param BOOL 		$use_absolute_url 		If TRUE will use full URL prefix to load CSS and Javascripts ; Default is FALSE
	 *
	 * @return STRING							[HTML Code]
	 */
	public static function html_jsload_highlightsyntax($plugins, $loadpacks=true, $use_absolute_url=false) {
		//--
		if(!\is_array($plugins)) {
			$plugins = [ 'tpl', 'lang', 'ms', 'net', 'hw' ];
		} //end if
		//--
		if($use_absolute_url !== true) {
			$the_abs_url = '';
		} else {
			$the_abs_url = (string) SmartUtils::get_server_current_url();
		} //end if else
		//--
		$arr_packs = [ // {{{SYNC-HIGHLIGHT-FTYPE-PACK}}}
			'tpl'  => 'dust, twig, latte, django',
			'lang' => 'basic, cs, d, delphi, erlang, fortran, fsharp, groovy, haskell, haxe, java, kotlin, objectivec, ocaml, openscad, r, scala, swift',
			'ms'  => 'dos, powershell, typescript, vbnet, vbscript',
			'net'  => 'ldif, protobuf',
			'hw'  => 'vhdl, llvm, x86asm, armasm, mipsasm'
		];
		//--
		$arr_stx_plugs = [];
		$arr_check_keys = [];
		foreach($arr_packs as $key => $val) {
			$key = (string) \strtolower((string)\trim((string)$key));
			if((\Smart::array_size($plugins) <= 0) OR (\in_array((string)$key, (array)$plugins))) {
				if((string)$key != '') {
					if($loadpacks === false) { // load single files
						//--
						$tmp_arr = (array) \explode(',', (string)$val);
						for($i=0; $i<\Smart::array_size($tmp_arr); $i++) {
							$tmp_arr[$i] = (string) \trim((string)$tmp_arr[$i]);
							if((string)$tmp_arr[$i] != '') {
								$arr_stx_plugs[] = (string) 'syntax/'.$key.'/'.\strtolower((string)$tmp_arr[$i]);
							} //end if
						} //end if
						$tmp_arr = [];
						//--
					} else { // load packs
						//--
						$arr_stx_plugs[] = (string) 'syntax-'.$key.'-ext.pak';
						$arr_check_keys[] = (string) $key;
						//--
					} //end if else
				} //end if
			} //end if
		} //end foreach
		//--
		if($loadpacks === false) { // load single files
			$syntax_packs = 'src';
		} else { // load packs
			$syntax_packs = 'pak';
			if(\Smart::array_size($arr_check_keys) > 0) {
				if(\array_keys($arr_packs) === $arr_check_keys) {
					$arr_stx_plugs = [ 'syntax-ext.pak' ]; // if all packs need to be loaded, replace with this one
				} //end if
			} //end if
		} //end if
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-highlight-syntax/libs/templates/syntax-highlight-ext-plugins.inc.htm',
			[
				'HLJS-PREFIX-URL' 	=> (string) $the_abs_url,
				'SYNTAX-PLUGINS' 	=> (array)  $arr_stx_plugs,
				'SYNTAX-PACKS' 		=> (string) $syntax_packs
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
	public static function get_highlightsyntax_by_filetype($path) {
		//--
		$path = (string) $path;
		//--
		$fname = (string) \SmartFileSysUtils::get_file_name_from_path((string)$path);
		$fext = (string) \SmartFileSysUtils::get_file_extension_from_path((string)$fname);
		$fext = (string) \strtolower((string)\trim((string)$fext));
		//--
		$fpack = 'unknown'; // avoid return empty string on pack as this will load all packs
		$ftype = '';
		switch((string)$fext) { // {{{SYNC-HIGHLIGHT-FTYPE-PACK}}}
			//-- tpl (depends on SF.web)
			case 'dust':
				$fpack = 'tpl';
				$ftype = 'dust';
				break;
			case 'twig':
				$fpack = 'tpl';
				$ftype = 'twig';
				break;
			case 'latte':
				$fpack = 'tpl';
				$ftype = 'latte';
				break;
			case 'django':
				$fpack = 'tpl';
				$ftype = 'django';
				break;
			//-- lang
			case 'r':
				$fpack = 'lang';
				$ftype = 'r';
				break;
			case 'd':
				$fpack = 'lang';
				$ftype = 'd';
				break;
			case 'basic':
			case 'bas':
				$fpack = 'lang';
				$ftype = 'basic';
				break;
			case 'pas':
				$fpack = 'lang';
				$ftype = 'delphi';
				break;
			case 'f':
				$fpack = 'lang';
				$ftype = 'fortran';
				break;
			case 'fsharp':
			case 'fs':
				$fpack = 'lang';
				$ftype = 'fsharp';
				break;
			case 'csharp':
			case 'cs':
				$fpack = 'lang';
				$ftype = 'cs';
				break;
			case 'swift':
				$fpack = 'lang';
				$ftype = 'swift';
				break;
			case 'm':
				$fpack = 'lang';
				$ftype = 'objectivec';
				break;
			case 'hx':
			case 'hxml':
				$fpack = 'lang';
				$ftype = 'haxe';
				break;
			case 'hs':
			case 'lhs':
				$fpack = 'lang';
				$ftype = 'haskell';
				break;
			case 'java':
				$fpack = 'lang';
				$ftype = 'java';
				break;
			case 'groovy':
			case 'gvy':
			case 'gy':
				$fpack = 'lang';
				$ftype = 'groovy';
				break;
			case 'kotlin':
			case 'kt':
			case 'ktm':
				$fpack = 'lang';
				$ftype = 'kotlin';
				break;
			case 'scala':
			case 'sc':
				$fpack = 'lang';
				$ftype = 'scala';
				break;
			case 'ocaml':
			case 'ml':
				$fpack = 'lang';
				$ftype = 'ocaml';
				break;
			case 'erl':
			case 'hrl':
				$fpack = 'lang';
				$ftype = 'erlang';
				break;
			case 'openscad':
			case 'jscad':
			case 'scad':
			case 'stl':
			case 'obj':
				$fpack = 'lang';
				$ftype = 'openscad';
				break;
			//-- ms
			case 'cmd':
			case 'bat':
				$fpack = 'ms';
				$ftype = 'dos';
				break;
			case 'ps1':
			case 'psm1':
			case 'psd1':
				$fpack = 'ms';
				$ftype = 'powershell';
				break;
			case 'ts':
			case 'tsx':
				$fpack = 'ms';
				$ftype = 'typescript';
				break;
			case 'vb':
				$fpack = 'ms';
				$ftype = 'vbnet';
				break;
			case 'vbs':
				$fpack = 'ms';
				$ftype = 'vbscript';
				break;
			//-- net
			case 'ldif':
				$fpack = 'net';
				$ftype = 'ldif';
				break;
			case 'protobuf':
			case 'pb':
				$fpack = 'net';
				$ftype = 'protobuf';
				break;
			//-- hw
			case 'vhd':
				$fpack = 'net';
				$ftype = 'vhdl';
				break;
			case 'll':
			case 's':
				$fpack = 'net';
				$ftype = 'llvm';
				break;
			case 'asm':
				$fpack = 'net';
				$ftype = 'x86asm';
				break;
			case 'aasm':
				$fpack = 'net';
				$ftype = 'armasm';
				break;
			case 'masm':
				$fpack = 'net';
				$ftype = 'mipsasm';
				break;
			//--
			default:
				// no handler
		} //end switch
		//--
		if(\stripos((string)$fname, '.dust.') !== false) {
			$fpack = 'tpl';
			$ftype = 'dust';
		} elseif(\stripos((string)$fname, '.twig.') !== false) {
			$fpack = 'tpl';
			$ftype = 'twig';
		} elseif(\stripos((string)$fname, '.latte.') !== false) {
			$fpack = 'tpl';
			$ftype = 'latte';
		} elseif(\stripos((string)$fname, '.django.') !== false) {
			$fpack = 'tpl';
			$ftype = 'django';
		} //end if
		//--
		return array(
			'type' => (string) $ftype,
			'pack' => (array)  \explode(',', (string)$fpack)
		);
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
