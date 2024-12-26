<?php
// RMS4CC Barcode 1D for Smart.Framework
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
// BarCode 1D:	RMS4CC (CBC / KIX)
// License: GPLv3
//============================================================
// Class to create RMS 1D barcodes.
// RMS4CC (Royal Mail 4-state Customer Code)
// * CBC (Customer Bar Code)
// * KIX (Klant Index - Customer Index)
// RM4SCC is the name of the barcode symbology used by the Royal Mail but also other uses.
// TECHNICAL DATA / FEATURES OF RMS4CC:
// * Encodable Character Set: 		0..9 A..Z
// * Code Type: 					Linear, 3 types height bars
// * Error Correction: 				Checksum
// * Maximum Data Characters: 		CBC: 20 ; KIX: 11
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
 * Class BarCode 1D RMS4CC (CBC and KIX)
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
final class Barcode1DRMS4CC {

	// ->

	private $code = '';
	private $mode = '';


	public function __construct($code, $type='CBC') {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$this->code = (string) $code; // force string
		//--
		switch((string)$type) {
			case 'KIX':
				$this->mode = 'KIX'; // Klant Index (Customer Index)
				break;
			case 'CBC':
			default:
				$this->mode = 'CBC'; // Customer Bar Code
				break;
		} //end switch
		//--
	} //END FUNCTION


	/**
	 * RMS4CC - CBC - KIX
	 * @param $code (string) code to print
	 * @param $kix (boolean) if true prints the KIX variation (doesn't use the start and end symbols, and the checksum) - in this case the house number must be sufficed with an X and placed at the end of the code.
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() { // barcode_rms4cc()
		//--
		$code = $this->code;
		//--
		$bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 3, 'bcode' => array());
		//--
		if((string)$this->mode == 'KIX') {
			$kix = true;
		} else {
			$kix = false;
		} //end if else
		//--
		$notkix = !$kix;
		// bar mode
		// 1 = pos 1, length 2
		// 2 = pos 1, length 3
		// 3 = pos 2, length 1
		// 4 = pos 2, length 2
		$barmode = array(
			'0' => array(3,3,2,2),
			'1' => array(3,4,1,2),
			'2' => array(3,4,2,1),
			'3' => array(4,3,1,2),
			'4' => array(4,3,2,1),
			'5' => array(4,4,1,1),
			'6' => array(3,1,4,2),
			'7' => array(3,2,3,2),
			'8' => array(3,2,4,1),
			'9' => array(4,1,3,2),
			'A' => array(4,1,4,1),
			'B' => array(4,2,3,1),
			'C' => array(3,1,2,4),
			'D' => array(3,2,1,4),
			'E' => array(3,2,2,3),
			'F' => array(4,1,1,4),
			'G' => array(4,1,2,3),
			'H' => array(4,2,1,3),
			'I' => array(1,3,4,2),
			'J' => array(1,4,3,2),
			'K' => array(1,4,4,1),
			'L' => array(2,3,3,2),
			'M' => array(2,3,4,1),
			'N' => array(2,4,3,1),
			'O' => array(1,3,2,4),
			'P' => array(1,4,1,4),
			'Q' => array(1,4,2,3),
			'R' => array(2,3,1,4),
			'S' => array(2,3,2,3),
			'T' => array(2,4,1,3),
			'U' => array(1,1,4,4),
			'V' => array(1,2,3,4),
			'W' => array(1,2,4,3),
			'X' => array(2,1,3,4),
			'Y' => array(2,1,4,3),
			'Z' => array(2,2,3,3)
		);
		//--
		$code = strtoupper($code);
		$len = strlen($code);
		//--
		if($notkix) {
			//-- table for checksum calculation (row,col)
			$checktable = array(
				'0' => array(1,1),
				'1' => array(1,2),
				'2' => array(1,3),
				'3' => array(1,4),
				'4' => array(1,5),
				'5' => array(1,0),
				'6' => array(2,1),
				'7' => array(2,2),
				'8' => array(2,3),
				'9' => array(2,4),
				'A' => array(2,5),
				'B' => array(2,0),
				'C' => array(3,1),
				'D' => array(3,2),
				'E' => array(3,3),
				'F' => array(3,4),
				'G' => array(3,5),
				'H' => array(3,0),
				'I' => array(4,1),
				'J' => array(4,2),
				'K' => array(4,3),
				'L' => array(4,4),
				'M' => array(4,5),
				'N' => array(4,0),
				'O' => array(5,1),
				'P' => array(5,2),
				'Q' => array(5,3),
				'R' => array(5,4),
				'S' => array(5,5),
				'T' => array(5,0),
				'U' => array(0,1),
				'V' => array(0,2),
				'W' => array(0,3),
				'X' => array(0,4),
				'Y' => array(0,5),
				'Z' => array(0,0)
			);
			//--
			$row = 0;
			$col = 0;
			//--
			for($i = 0; $i < $len; ++$i) {
				$row += $checktable[$code[$i]][0];
				$col += $checktable[$code[$i]][1];
			} //end for
			//--
			$row %= 6;
			$col %= 6;
			$chk = array_keys($checktable, array($row,$col));
			$code .= $chk[0];
			++$len;
			//--
		} //end if
		//--
		$k = 0;
		//--
		if($notkix) {
			//-- start bar
			$bararray['bcode'][$k++] = array('t' => 1, 'w' => 1, 'h' => 2, 'p' => 0);
			$bararray['bcode'][$k++] = array('t' => 0, 'w' => 1, 'h' => 2, 'p' => 0);
			$bararray['maxw'] += 2;
			//--
		} //end if
		//--
		for($i = 0; $i < $len; ++$i) {
			//--
			for($j = 0; $j < 4; ++$j) {
				//--
				switch($barmode[$code[$i]][$j]) {
					case 1:
						$p = 0;
						$h = 2;
						break;
					case 2:
						$p = 0;
						$h = 3;
						break;
					case 3:
						$p = 1;
						$h = 1;
						break;
					case 4:
						$p = 1;
						$h = 2;
						break;
				} //end switch
				//--
				$bararray['bcode'][$k++] = array('t' => 1, 'w' => 1, 'h' => $h, 'p' => $p);
				$bararray['bcode'][$k++] = array('t' => 0, 'w' => 1, 'h' => 2, 'p' => 0);
				$bararray['maxw'] += 2;
				//--
			} //end for
			//--
		} //end for
		//--
		if($notkix) {
			// stop bar
			$bararray['bcode'][$k++] = array('t' => 1, 'w' => 1, 'h' => 3, 'p' => 0);
			$bararray['maxw'] += 1;
		} //end if
		//--
		return (array) $bararray;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
