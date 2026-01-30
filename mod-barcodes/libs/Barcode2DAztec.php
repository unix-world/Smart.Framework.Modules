<?php
// Aztec Barcode 2D for Smart.Framework
// Module Library
// (c) 2006-present unix-world.org - all rights reserved

// this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Barcodes;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================

//============================================================
// BarCode 2D: Aztec Barcode
// License: GPLv3
//============================================================
// Copyright 2013 Metzli and ZXing authors
// Licensed under the Apache License, Version 2.0 (the "License");
// Metzli is heavily based on ZXing and is basically a port of its Aztec encoding part.
// TECHNICAL DATA / FEATURES OF Aztec Code:
// * A bit more compact than QRCode but with a smaller limit
// * Encodable Character Set: 	UTF-8 + Binary
// * Code Type: 				Matrix
// * Maximum Data Characters: 	3067 alphanumeric, 3832 numeric, 1914 Binary (UTF-8)
//============================================================
//
// This class is derived from the following projects:
// https://github.com/z38/metzli
// Metzli is a PHP library to generate Aztec 2D barcodes.
//
//============================================================

/**
 * Class: Class BarCode 2D Aztec Code
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @depends 	classes: \Metzli
 * @version 	v.20260130
 * @package 	modules:Barcodes2D
 *
 */
final class Barcode2DAztec {

	// ->

	private string $code;

	private static bool $initialized = false;


	public function __construct(?string $y_code) {
		//--
		$this->code = (string) $y_code;
		//--
		$this->init();
		//--
	} //END FUNCTION


	public function getBarcodeArray() : array {
		//--
		try {
			//--
			$barcode_array = array();
			$barcode_array['code'] = (string) $this->code;
			//--
			$aztec = (object) \Metzli\Encoder\Encoder::encode((string)$this->code);
			$matrix = $aztec->getMatrix();
			//--
			$barcode_array['num_rows'] = $matrix->getWidth();
			$barcode_array['num_cols'] = $matrix->getHeight();
			$barcode_array['bcode'] = array();
			//--
			for($x=0; $x<$matrix->getWidth(); $x++) {
				for($y=0; $y<$matrix->getHeight(); $y++) {
					if($matrix->get($x, $y)) {
						$barcode_array['bcode'][$x][$y] = 1;
					} else {
						$barcode_array['bcode'][$x][$y] = 0;
					} //end if else
				} //end for
			} //end for
		} catch(\Exception $err) { // don't throw if MongoDB error !
			\Smart::log_warning(__METHOD__.' # ERROR: '.$err->getMessage());
			return [];
		} //end try catch
		//--
		return (array) $barcode_array;
		//--
	} //END FUNCTION


	private function init() : void {
		//--
		if(self::$initialized === true) {
			return;
		} //end if
		//--
		\spl_autoload_register(function(string $classname) : void {
			//--
			if((\strpos($classname, '\\') === false) OR (!\preg_match('/^[a-zA-Z0-9_\\\]+$/', $classname))) { // if have no namespace or not valid character set
				return;
			} //end if
			//--
			if(\str_starts_with((string)$classname, 'Metzli\\') !== true) { // class name must start with Metzli\
				return;
			} //end if
			//--
			$parts = (array) \explode('\\', (string)$classname);
			//--
			$max = (int) \count((array)$parts) - 1; // the last is the class
			if((int)$max < (int)(3 - 1)) { // must have at least 3 segments as: `\Metzli\Encoder\{Class}` or `\Metzli\Utils\{Class}` or or `\Metzli\Exception\{Class}`
				return;
			} //end if
			//--
			$dir = (string) \SmartFileSysUtils::getSmartFsRootPath().'modules/mod-barcodes/libs/Barcode2DAztec/Metzli/';
			//--
			for($i=1; $i<$max; $i++) {
				$dir .= (string) $parts[$i].'/';
			} //end for
			//--
			$dir  = (string) $dir;
			$file = (string) $parts[(int)$max];
			$path = (string) $dir.$file;
			$path = (string) \str_replace(array('\\', "\0"), array('', ''), $path); // filter out null byte and backslash
			//--
			if(!\preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
				return; // invalid path characters in file
			} //end if
			//--
			if(!\is_file($path.'.php')) {
				return; // file does not exists
			} //end if
			//--
			require_once($path.'.php');
			//--
		}, true, false); // throw / append
		//--
		self::$initialized = true;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
