<?php
// EAN/UPC Barcode 1D for Smart.Framework
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
// BarCode 1D:	EAN13 / UPC-A
// License: GPLv3
//============================================================
// Class to create EAN / UPC 1D barcodes.
// Very wide use world-wide, for retail
// TECHNICAL DATA / FEATURES OF Code39:
// * Encodable Character Set: 			0..9
// * Maximum Data Characters: 			12
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
 * @version 	v.20250714
 * @package 	modules:Barcodes1D
 *
 */
final class Barcode1DEANUPC {

	// ->

	private $code = '';
	private $len = 13;


	// currently could not find a barcode reader to support code39 checksum so is disabled by default (this is a very rare used option in practice)
	public function __construct($code, $len=13) {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
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
	public function getBarcodeArray() {
		//--
		$code = (string) $this->code;
		$len = (int) $this->len;
		//--
		$upce = false;
		if($len == 6) {
			$len = 12; // UPC-A
			$upce = true; // UPC-E mode
		} //end if
		$data_len = $len - 1;
		//-- padding
		$code = (string) str_pad($code, $data_len, '0', STR_PAD_LEFT);
		$code_len = strlen($code);
		//-- calculate check digit
		$sum_a = 0;
		for($i = 1; $i < $data_len; $i+=2) {
			$sum_a += $code[$i];
		} //end for
		if($len > 12) {
			$sum_a *= 3;
		} //end if
		$sum_b = 0;
		for($i = 0; $i < $data_len; $i+=2) {
			$sum_b += ($code[$i]);
		} //end for
		if($len < 13) {
			$sum_b *= 3;
		} //end if
		$r = ($sum_a + $sum_b) % 10;
		if($r > 0) {
			$r = (10 - $r);
		} //end if
		if($code_len == $data_len) {
			// add check digit
			$code .= $r;
		} elseif($r !== intval($code[$data_len])) {
			// wrong checkdigit
			return false;
		} //end if else
		if($len == 12) {
			// UPC-A
			$code = (string) '0'.$code;
			++$len;
		} //end if
		if($upce) {
			// convert UPC-A to UPC-E
			$tmp = (string) substr((string)$code, 4, 3);
			if (((string)$tmp == '000') OR ((string)$tmp == '100') OR ((string)$tmp == '200')) {
				// manufacturer code ends in 000, 100, or 200
				$upce_code = (string) substr((string)$code, 2, 2).substr((string)$code, 9, 3).substr((string)$code, 4, 1);
			} else {
				$tmp = (string) substr((string)$code, 5, 2);
				if((string)$tmp == '00') {
					// manufacturer code ends in 00
					$upce_code = (string) substr((string)$code, 2, 3).substr((string)$code, 10, 2).'3';
				} else {
					$tmp = (string) substr((string)$code, 6, 1);
					if((string)$tmp == '0') {
						// manufacturer code ends in 0
						$upce_code = (string) substr((string)$code, 2, 4).substr((string)$code, 11, 1).'4';
					} else {
						// manufacturer code does not end in zero
						$upce_code = (string) substr((string)$code, 2, 5).substr((string)$code, 11, 1);
					} //end if else
				} //end if else
			} //end if else
		} //end if
		//-- Convert digits to bars
		$codes = array(
			'A'=>array( // left odd parity
				'0'=>'0001101',
				'1'=>'0011001',
				'2'=>'0010011',
				'3'=>'0111101',
				'4'=>'0100011',
				'5'=>'0110001',
				'6'=>'0101111',
				'7'=>'0111011',
				'8'=>'0110111',
				'9'=>'0001011'),
			'B'=>array( // left even parity
				'0'=>'0100111',
				'1'=>'0110011',
				'2'=>'0011011',
				'3'=>'0100001',
				'4'=>'0011101',
				'5'=>'0111001',
				'6'=>'0000101',
				'7'=>'0010001',
				'8'=>'0001001',
				'9'=>'0010111'),
			'C'=>array( // right
				'0'=>'1110010',
				'1'=>'1100110',
				'2'=>'1101100',
				'3'=>'1000010',
				'4'=>'1011100',
				'5'=>'1001110',
				'6'=>'1010000',
				'7'=>'1000100',
				'8'=>'1001000',
				'9'=>'1110100')
		);
		$parities = array(
			'0'=>array('A','A','A','A','A','A'),
			'1'=>array('A','A','B','A','B','B'),
			'2'=>array('A','A','B','B','A','B'),
			'3'=>array('A','A','B','B','B','A'),
			'4'=>array('A','B','A','A','B','B'),
			'5'=>array('A','B','B','A','A','B'),
			'6'=>array('A','B','B','B','A','A'),
			'7'=>array('A','B','A','B','A','B'),
			'8'=>array('A','B','A','B','B','A'),
			'9'=>array('A','B','B','A','B','A')
		);
		$upce_parities = array();
		$upce_parities[0] = array(
			'0'=>array('B','B','B','A','A','A'),
			'1'=>array('B','B','A','B','A','A'),
			'2'=>array('B','B','A','A','B','A'),
			'3'=>array('B','B','A','A','A','B'),
			'4'=>array('B','A','B','B','A','A'),
			'5'=>array('B','A','A','B','B','A'),
			'6'=>array('B','A','A','A','B','B'),
			'7'=>array('B','A','B','A','B','A'),
			'8'=>array('B','A','B','A','A','B'),
			'9'=>array('B','A','A','B','A','B')
		);
		$upce_parities[1] = array(
			'0'=>array('A','A','A','B','B','B'),
			'1'=>array('A','A','B','A','B','B'),
			'2'=>array('A','A','B','B','A','B'),
			'3'=>array('A','A','B','B','B','A'),
			'4'=>array('A','B','A','A','B','B'),
			'5'=>array('A','B','B','A','A','B'),
			'6'=>array('A','B','B','B','A','A'),
			'7'=>array('A','B','A','B','A','B'),
			'8'=>array('A','B','A','B','B','A'),
			'9'=>array('A','B','B','A','B','A')
		);
		$k = 0;
		$seq = '101'; // left guard bar
		if($upce) {
			$bararray = array('code' => $upce_code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
			$p = $upce_parities[$code[1]][$r];
			for($i = 0; $i < 6; ++$i) {
				$seq .= $codes[$p[$i]][$upce_code[$i]];
			} //end for
			$seq .= '010101'; // right guard bar
		} else {
			$bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
			$half_len = intval(ceil((string)($len / 2))); // unixman: fix ceil
			if($len == 8) {
				for($i = 0; $i < $half_len; ++$i) {
					$seq .= $codes['A'][$code[$i]];
				} //end for
			} else {
				$p = $parities[$code[0]];
				for($i = 1; $i < $half_len; ++$i) {
					$seq .= $codes[$p[$i-1]][$code[$i]];
				} //end for
			} //end if else
			$seq .= '01010'; // center guard bar
			for($i = $half_len; $i < $len; ++$i) {
				$seq .= $codes['C'][$code[$i]];
			} //end for
			$seq .= '101'; // right guard bar
		} //end if else
		$clen = strlen($seq);
		$w = 0;
		for($i = 0; $i < $clen; ++$i) {
			$w += 1;
			if(($i == ($clen - 1)) OR (($i < ($clen - 1)) AND ($seq[$i] != $seq[$i+1]))) {
				if((string)$seq[$i] == '1') {
					$t = true; // bar
				} else {
					$t = false; // space
				}
				$bararray['bcode'][$k] = array('t' => $t, 'w' => $w, 'h' => 1, 'p' => 0);
				$bararray['maxw'] += $w;
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
