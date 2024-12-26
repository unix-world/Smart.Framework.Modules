<?php
// Aztec Barcode 2D for Smart.Framework
// Module Library
// (c) 2006-2021 unix-world.org - all rights reserved

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
 * @version 	v.20200121
 * @package 	modules:Barcodes2D
 *
 */
final class Barcode2DAztec {

	// ->

	private $code;


	public function __construct($y_code) {
		//--
		$this->code = (string) $y_code;
		//--
	} //END FUNCTION


	public function getBarcodeArray() {
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
			return array();
		} //end try catch
		//--
		return (array) $barcode_array;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//--
/**
 *
 * @access 		private
 * @internal
 *
 */
function autoload__Aztec2DBarcodeMetzli_SFM($classname) {
	//--
	$classname = (string) $classname;
	//--
	if((\strpos($classname, '\\') === false) OR (!\preg_match('/^[a-zA-Z0-9_\\\]+$/', $classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(\strpos($classname, 'Metzli\\') === false) { // must start with this namespaces only
		return;
	} //end if
	//--
	$parts = (array) \explode('\\', $classname);
	//--
	$max = (int) \count($parts) - 1; // the last is the class
	//--
	$dir = 'modules/mod-barcodes/libs/Barcode2DAztec/Metzli/';
	//--
	if((string)$parts[1] != '') {
		for($i=1; $i<$max; $i++) {
			$dir .= (string) $parts[$i].'/';
		} //end for
	} else {
		return; // module not handled by this loader
	} //end if
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
} //END FUNCTION
//--
\spl_autoload_register('\\SmartModExtLib\\Barcodes\\autoload__Aztec2DBarcodeMetzli_SFM', true, false); // throw / append
//--


// end of php code
