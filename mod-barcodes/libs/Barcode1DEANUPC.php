<?php
// EAN/UPC Barcode 1D for Smart.Framework
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
//===================================================================================== CLASS START
//=====================================================================================


//============================================================
// BarCode 1D:	EAN13 / UPC-A
// (c) 2016-present, unix-world.org
// License: aGPLv3 (GNU AFFERO GENERAL PUBLIC LICENSE Version 3)
//============================================================
// Class to create EAN / UPC 1D barcodes.
// Very wide use world-wide, for retail
// TECHNICAL DATA / FEATURES OF EAN/UPC:
// * Encodable Character Set: 			0..9
// * Maximum Data Characters: 			13 (EAN) ; 12 (UPC)
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
 * Class BarCode 1D EAN13 / UPC-A
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20260203
 * @package 	modules:Barcodes1D
 *
 */
final class Barcode1DEANUPC {

	// ->

	private string $code = '';
	private int     $len = 13;


	private const CODES = [
		'A'=>[ // left odd parity
			'0' => '0001101',
			'1' => '0011001',
			'2' => '0010011',
			'3' => '0111101',
			'4' => '0100011',
			'5' => '0110001',
			'6' => '0101111',
			'7' => '0111011',
			'8' => '0110111',
			'9' => '0001011',
		],
		'B'=>[ // left even parity
			'0' => '0100111',
			'1' => '0110011',
			'2' => '0011011',
			'3' => '0100001',
			'4' => '0011101',
			'5' => '0111001',
			'6' => '0000101',
			'7' => '0010001',
			'8' => '0001001',
			'9' => '0010111',
		],
		'C' => [ // right
			'0' => '1110010',
			'1' => '1100110',
			'2' => '1101100',
			'3' => '1000010',
			'4' => '1011100',
			'5' => '1001110',
			'6' => '1010000',
			'7' => '1000100',
			'8' => '1001000',
			'9' => '1110100',
		]
	];

	private const PARITIES = [
		'0' => ['A','A','A','A','A','A'],
		'1' => ['A','A','B','A','B','B'],
		'2' => ['A','A','B','B','A','B'],
		'3' => ['A','A','B','B','B','A'],
		'4' => ['A','B','A','A','B','B'],
		'5' => ['A','B','B','A','A','B'],
		'6' => ['A','B','B','B','A','A'],
		'7' => ['A','B','A','B','A','B'],
		'8' => ['A','B','A','B','B','A'],
		'9' => ['A','B','B','A','B','A'],
	];

	private const UPCE_PARITIES = [
		[
			'0' => ['B','B','B','A','A','A'],
			'1' => ['B','B','A','B','A','A'],
			'2' => ['B','B','A','A','B','A'],
			'3' => ['B','B','A','A','A','B'],
			'4' => ['B','A','B','B','A','A'],
			'5' => ['B','A','A','B','B','A'],
			'6' => ['B','A','A','A','B','B'],
			'7' => ['B','A','B','A','B','A'],
			'8' => ['B','A','B','A','A','B'],
			'9' => ['B','A','A','B','A','B'],
		],
		[
			'0' => ['A','A','A','B','B','B'],
			'1' => ['A','A','B','A','B','B'],
			'2' => ['A','A','B','B','A','B'],
			'3' => ['A','A','B','B','B','A'],
			'4' => ['A','B','A','A','B','B'],
			'5' => ['A','B','B','A','A','B'],
			'6' => ['A','B','B','B','A','A'],
			'7' => ['A','B','A','B','A','B'],
			'8' => ['A','B','A','B','B','A'],
			'9' => ['A','B','B','A','B','A'],
		],
	];


	public function __construct(?string $code, int $len=13) {
		//--
		if(((string)$code == '') OR ((string)$code === '\0')) {
			return;
		} //end if
		//--
		$code = \intval($code);
		if((int)$code <= 0) {
			return;
		} //end if
		//--
		$this->code = (string) $code; // force string
		//--
		$len = (int) $len;
		if($len < 6) {
			$len = 6;
		} elseif($len > 13) {
			$len = 13;
		} //end if
		//--
		$this->len = (int) $len;
		//--
	} //END FUNCTION


	/**
	 * EAN13 and UPC-A barcodes.
	 * EAN13: European Article Numbering international retail product code
	 * UPC-A: Universal product code seen on almost all retail products in the USA and Canada
	 * UPC-E: Short version of UPC symbol
	 * @param $code (string) code to represent.
	 * @param $len (string) barcode type: 6 = UPC-E, 8 = EAN8, 13 = EAN13, 12 = UPC-A
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() : array {
		//--
		$code = (string) $this->code;
		$len = (int) $this->len;
		//--
		$bararray = [ 'code' => '', 'maxw' => 0, 'maxh' => 1, 'bcode' => [] ];
		//--
		$upce = false;
		if((int)$len == 6) {
			$len = 12; // UPC-A
			$upce = true; // UPC-E mode
		} //end if
		$data_len = (int) ($len - 1);
		//-- padding
		$code = (string) \str_pad($code, $data_len, '0', \STR_PAD_LEFT);
		$code_len = (int) \strlen($code);
		//-- calculate check digit
		$sum_a = 0;
		for($i=1; $i<$data_len; $i+=2) {
			$sum_a += \intval($code[$i]);
		} //end for
		if($len > 12) {
			$sum_a *= 3;
		} //end if
		$sum_b = 0;
		for($i=0; $i<$data_len; $i+=2) {
			$sum_b += \intval($code[$i]);
		} //end for
		if($len < 13) {
			$sum_b *= 3;
		} //end if
		$r = (int) (($sum_a + $sum_b) % 10);
		if($r > 0) {
			$r = (10 - $r);
		} //end if
		if((int)$code_len == (int)$data_len) {
			// add check digit
			$code .= $r;
		} elseif((int)$r !== \intval($code[$data_len])) {
			// wrong checkdigit
			return [];
		} //end if else
		if((int)$len == 12) { // UPC-A
			$code = (string) '0'.$code;
			++$len;
		} //end if
		$upce_code = '';
		if($upce === true) {
			// convert UPC-A to UPC-E
			$tmp = (string) \substr((string)$code, 4, 3);
			if (((string)$tmp == '000') OR ((string)$tmp == '100') OR ((string)$tmp == '200')) {
				// manufacturer code ends in 000, 100, or 200
				$upce_code = (string) \substr((string)$code, 2, 2).\substr((string)$code, 9, 3).\substr((string)$code, 4, 1);
			} else {
				$tmp = (string) \substr((string)$code, 5, 2);
				if((string)$tmp == '00') {
					// manufacturer code ends in 00
					$upce_code = (string) \substr((string)$code, 2, 3).\substr((string)$code, 10, 2).'3';
				} else {
					$tmp = (string) \substr((string)$code, 6, 1);
					if((string)$tmp == '0') {
						// manufacturer code ends in 0
						$upce_code = (string) \substr((string)$code, 2, 4).\substr((string)$code, 11, 1).'4';
					} else {
						// manufacturer code does not end in zero
						$upce_code = (string) \substr((string)$code, 2, 5).\substr((string)$code, 11, 1);
					} //end if else
				} //end if else
			} //end if else
		} //end if
		//-- Convert digits to bars
		$k = 0;
		$seq = '101'; // left guard bar
		if($upce === true) {
			$bararray = [ 'code' => (string)$upce_code, 'maxw' => 0, 'maxh' => 1, 'bcode' => [] ];
			$p = self::UPCE_PARITIES[(string)$code[1]][$r];
			for($i=0; $i<6; ++$i) {
				$seq .= (string) self::CODES[(string)$p[$i]][(string)$upce_code[$i]];
			} //end for
			$seq .= '010101'; // right guard bar
		} else {
			$bararray = [ 'code' => (string)$code, 'maxw' => 0, 'maxh' => 1, 'bcode' => [] ];
			$half_len = \intval(\ceil((string)($len / 2))); // unixman: fix ceil
			if($len == 8) {
				for($i=0; $i<$half_len; ++$i) {
					$seq .= (string) self::CODES['A'][(string)$code[$i]];
				} //end for
			} else {
				$p = self::PARITIES[$code[0]];
				for($i=1; $i<$half_len; ++$i) {
					$seq .= (string) self::CODES[(string)$p[$i-1]][(string)$code[$i]];
				} //end for
			} //end if else
			$seq .= '01010'; // center guard bar
			for($i = $half_len; $i < $len; ++$i) {
				$seq .= (string) self::CODES['C'][(string)$code[$i]];
			} //end for
			$seq .= '101'; // right guard bar
		} //end if else
		$clen = (int) \strlen($seq);
		$w = 0;
		for($i=0; $i<$clen; ++$i) {
			$w += 1;
			if(((int)$i == (int)($clen - 1)) OR (((int)$i < (int)($clen - 1)) AND ((string)$seq[$i] != (string)$seq[$i+1]))) {
				$t = false; // space
				if((string)$seq[$i] == '1') {
					$t = true; // bar
				} //end if
				$bararray['bcode'][$k] = [ 't' => (int)$t, 'w' => (int)$w, 'h' => 1, 'p' => 0 ];
				$bararray['maxw'] += (int) $w;
				++$k;
				$w = 0;
			} //end if
		} //end for
		//--
		return (array) $bararray;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
