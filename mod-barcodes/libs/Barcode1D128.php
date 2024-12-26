<?php
// Code128 Barcode 1D for Smart.Framework
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
//===================================================================================== CLASS START
//=====================================================================================


//============================================================
// BarCode 1D:	Code128 128-A / 128-B / 128-C
// License: GPLv3
//============================================================
// Class to create Code128 1D barcodes.
// Very capable code, excellent density, high reliability
// Wide use world-wide
// TECHNICAL DATA / FEATURES OF Code128:
// * Encodable Character Set A: 	0..9 A..Z      SPACE ! " # $ % & \ ' ( ) * + , - . / : ; < = > ? @ [ ] ^ _
// * Encodable Character Set B: 	0..9 A..Z a..z SPACE ! " # $ % & \ ' ( ) * + , - . / : ; < = > ? @ [ ] ^ _ ` { | } ~
// * Encodable Character Set C:		0..9
// * Code Type: 					Linear, 1 type height bars
// * Error Correction: 				Checksum
// * Maximum Data Characters: 		48
//============================================================
//
// These class is derived from the following projects:
//
// "TcPDF" / Barcodes 1D / 1.0.027 / 20141020
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class BarCode 1D 128 (A / B / C)
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20200121
 * @package 	modules:Barcodes1D
 *
 */
final class Barcode1D128 {

	// ->

	private $code = '';
	private $mode = '';


	public function __construct($code, $type='B') {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$this->code = (string) $code; // force string
		//--
		switch((string)$type) {
			case 'C':
				$this->mode = 'C';
				break;
			case 'A':
				$this->mode = 'A';
				break;
			case 'B':
			default:
				$this->mode = 'B';
		} //end switch
		//--
	} //END FUNCTION


	/**
	 * C128 barcodes.
	 * @param $code (string) code to represent.
	 * @param $type (string) barcode type: A, B, C or empty for automatic switch (AUTO mode)
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() { // barcode_c128()
		//--
		$code = $this->code;
		$type = $this->mode;
		//--
		$bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
		//--
		$chr = array(
			'212222', /* 00 */
			'222122', /* 01 */
			'222221', /* 02 */
			'121223', /* 03 */
			'121322', /* 04 */
			'131222', /* 05 */
			'122213', /* 06 */
			'122312', /* 07 */
			'132212', /* 08 */
			'221213', /* 09 */
			'221312', /* 10 */
			'231212', /* 11 */
			'112232', /* 12 */
			'122132', /* 13 */
			'122231', /* 14 */
			'113222', /* 15 */
			'123122', /* 16 */
			'123221', /* 17 */
			'223211', /* 18 */
			'221132', /* 19 */
			'221231', /* 20 */
			'213212', /* 21 */
			'223112', /* 22 */
			'312131', /* 23 */
			'311222', /* 24 */
			'321122', /* 25 */
			'321221', /* 26 */
			'312212', /* 27 */
			'322112', /* 28 */
			'322211', /* 29 */
			'212123', /* 30 */
			'212321', /* 31 */
			'232121', /* 32 */
			'111323', /* 33 */
			'131123', /* 34 */
			'131321', /* 35 */
			'112313', /* 36 */
			'132113', /* 37 */
			'132311', /* 38 */
			'211313', /* 39 */
			'231113', /* 40 */
			'231311', /* 41 */
			'112133', /* 42 */
			'112331', /* 43 */
			'132131', /* 44 */
			'113123', /* 45 */
			'113321', /* 46 */
			'133121', /* 47 */
			'313121', /* 48 */
			'211331', /* 49 */
			'231131', /* 50 */
			'213113', /* 51 */
			'213311', /* 52 */
			'213131', /* 53 */
			'311123', /* 54 */
			'311321', /* 55 */
			'331121', /* 56 */
			'312113', /* 57 */
			'312311', /* 58 */
			'332111', /* 59 */
			'314111', /* 60 */
			'221411', /* 61 */
			'431111', /* 62 */
			'111224', /* 63 */
			'111422', /* 64 */
			'121124', /* 65 */
			'121421', /* 66 */
			'141122', /* 67 */
			'141221', /* 68 */
			'112214', /* 69 */
			'112412', /* 70 */
			'122114', /* 71 */
			'122411', /* 72 */
			'142112', /* 73 */
			'142211', /* 74 */
			'241211', /* 75 */
			'221114', /* 76 */
			'413111', /* 77 */
			'241112', /* 78 */
			'134111', /* 79 */
			'111242', /* 80 */
			'121142', /* 81 */
			'121241', /* 82 */
			'114212', /* 83 */
			'124112', /* 84 */
			'124211', /* 85 */
			'411212', /* 86 */
			'421112', /* 87 */
			'421211', /* 88 */
			'212141', /* 89 */
			'214121', /* 90 */
			'412121', /* 91 */
			'111143', /* 92 */
			'111341', /* 93 */
			'131141', /* 94 */
			'114113', /* 95 */
			'114311', /* 96 */
			'411113', /* 97 */
			'411311', /* 98 */
			'113141', /* 99 */
			'114131', /* 100 */
			'311141', /* 101 */
			'411131', /* 102 */
			'211412', /* 103 START A */
			'211214', /* 104 START B */
			'211232', /* 105 START C */
			'233111', /* STOP */
			'200000'  /* END */
		);
		//-- ASCII characters for code A (ASCII 00 - 95)
		$keys_a = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
		$keys_a .= chr(0).chr(1).chr(2).chr(3).chr(4).chr(5).chr(6).chr(7).chr(8).chr(9);
		$keys_a .= chr(10).chr(11).chr(12).chr(13).chr(14).chr(15).chr(16).chr(17).chr(18).chr(19);
		$keys_a .= chr(20).chr(21).chr(22).chr(23).chr(24).chr(25).chr(26).chr(27).chr(28).chr(29);
		$keys_a .= chr(30).chr(31);
		//-- ASCII characters for code B (ASCII 32 - 127)
		$keys_b = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~'.chr(127);
		//-- special codes
		$fnc_a = array(241 => 102, 242 => 97, 243 => 96, 244 => 101);
		$fnc_b = array(241 => 102, 242 => 97, 243 => 96, 244 => 100);
		//-- array of symbols
		$code_data = array();
		//-- length of the code
		$len = strlen($code);
		//--
		switch(strtoupper($type)) {
			case 'A':  // MODE A
				$startid = 103;
				for($i = 0; $i < $len; ++$i) {
					$char = $code[$i];
					$char_id = ord($char);
					if(($char_id >= 241) AND ($char_id <= 244)) {
						$code_data[] = $fnc_a[$char_id];
					} elseif(($char_id >= 0) AND ($char_id <= 95)) {
						$code_data[] = strpos($keys_a, $char);
					} else {
						return false;
					} //end if else
				} //end for
				break;
			case 'C': // MODE C
				$startid = 105;
				if(ord($code[0]) == 241) {
					$code_data[] = 102;
					$code = substr($code, 1);
					--$len;
				} //end if
				if(($len % 2) != 0) {
					// the length must be even
					return false;
				} //end if
				for($i = 0; $i < $len; $i+=2) {
					$chrnum = $code[$i].$code[$i+1];
					if(preg_match('/([0-9]{2})/', (string)$chrnum) > 0) {
						$code_data[] = intval($chrnum);
					} else {
						return false;
					} //end if else
				} //end for
				break;
			case 'B':  // MODE B
			default:
				$startid = 104;
				for($i = 0; $i < $len; ++$i) {
					$char = $code[$i];
					$char_id = ord($char);
					if(($char_id >= 241) AND ($char_id <= 244)) {
						$code_data[] = $fnc_b[$char_id];
					} elseif(($char_id >= 32) AND ($char_id <= 127)) {
						$code_data[] = strpos($keys_b, $char);
					} else {
						return false;
					} //end if else
				} //end for
		} //end switch
		//-- calculate check character
		$sum = $startid;
		foreach($code_data as $key => $val) {
			$sum += ($val * ($key + 1));
		} //end foreach
		//-- add check character
		$code_data[] = ($sum % 103);
		//-- add stop sequence
		$code_data[] = 106;
		$code_data[] = 107;
		//-- add start code at the beginning
		array_unshift($code_data, $startid);
		//--
		foreach($code_data as $u => $val) {
			$seq = $chr[$val];
			for($j = 0; $j < 6; ++$j) {
				if(($j % 2) == 0) {
					$t = true; // bar
				} else {
					$t = false; // space
				} //end if else
				$w = $seq[$j];
				$bararray['bcode'][] = array('t' => $t, 'w' => $w, 'h' => 1, 'p' => 0);
				$bararray['maxw'] += $w;
			} //end for
		} //end foreach
		//--
		return (array) $bararray;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
