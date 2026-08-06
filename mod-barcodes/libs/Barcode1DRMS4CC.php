<?php
// RMS4CC Barcode 1D for Smart.Framework
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
// BarCode 1D:	RMS4CC (CBC / KIX)
// (c) 2016-present, unix-world.org
// License: aGPLv3 (GNU AFFERO GENERAL PUBLIC LICENSE Version 3)
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
 * @version 	v.20260723
 * @package 	modules:Barcodes1D
 *
 */
final class Barcode1DRMS4CC {

	// ->

	private string $code = '';
	private string $mode = '';


	// bar mode
	// 1 = pos 1, length 2
	// 2 = pos 1, length 3
	// 3 = pos 2, length 1
	// 4 = pos 2, length 2
	private const BAR_MODE = [
		'0' => [3,3,2,2],
		'1' => [3,4,1,2],
		'2' => [3,4,2,1],
		'3' => [4,3,1,2],
		'4' => [4,3,2,1],
		'5' => [4,4,1,1],
		'6' => [3,1,4,2],
		'7' => [3,2,3,2],
		'8' => [3,2,4,1],
		'9' => [4,1,3,2],
		'A' => [4,1,4,1],
		'B' => [4,2,3,1],
		'C' => [3,1,2,4],
		'D' => [3,2,1,4],
		'E' => [3,2,2,3],
		'F' => [4,1,1,4],
		'G' => [4,1,2,3],
		'H' => [4,2,1,3],
		'I' => [1,3,4,2],
		'J' => [1,4,3,2],
		'K' => [1,4,4,1],
		'L' => [2,3,3,2],
		'M' => [2,3,4,1],
		'N' => [2,4,3,1],
		'O' => [1,3,2,4],
		'P' => [1,4,1,4],
		'Q' => [1,4,2,3],
		'R' => [2,3,1,4],
		'S' => [2,3,2,3],
		'T' => [2,4,1,3],
		'U' => [1,1,4,4],
		'V' => [1,2,3,4],
		'W' => [1,2,4,3],
		'X' => [2,1,3,4],
		'Y' => [2,1,4,3],
		'Z' => [2,2,3,3],
	];

	private const TABLE_CHECKSUMS = [
		'0' => [1,1],
		'1' => [1,2],
		'2' => [1,3],
		'3' => [1,4],
		'4' => [1,5],
		'5' => [1,0],
		'6' => [2,1],
		'7' => [2,2],
		'8' => [2,3],
		'9' => [2,4],
		'A' => [2,5],
		'B' => [2,0],
		'C' => [3,1],
		'D' => [3,2],
		'E' => [3,3],
		'F' => [3,4],
		'G' => [3,5],
		'H' => [3,0],
		'I' => [4,1],
		'J' => [4,2],
		'K' => [4,3],
		'L' => [4,4],
		'M' => [4,5],
		'N' => [4,0],
		'O' => [5,1],
		'P' => [5,2],
		'Q' => [5,3],
		'R' => [5,4],
		'S' => [5,5],
		'T' => [5,0],
		'U' => [0,1],
		'V' => [0,2],
		'W' => [0,3],
		'X' => [0,4],
		'Y' => [0,5],
		'Z' => [0,0],
	];


	public function __construct(?string $code, ?string $type='CBC') {
		//--
		if(((string)$code == '') OR ((string)$code === '\0')) {
			return;
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
	 * @param $kix (bool) if true prints the KIX variation (doesn't use the start and end symbols, and the checksum) - in this case the house number must be sufficed with an X and placed at the end of the code.
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() : array { // barcode_rms4cc()
		//--
		$code = (string) $this->code;
		//--
		$bararray = [ 'code' => (string)$code, 'maxw' => 0, 'maxh' => 3, 'bcode' => [] ];
		//--
		$kix = false;
		if((string)$this->mode == 'KIX') {
			$kix = true;
		} //end if
		//--
		$notkix = (bool) !$kix;
		//--
		$code = (string) \strtoupper($code);
		$len  = (int)    \strlen($code);
		//--
		if($notkix) {
			//--
			$row = 0;
			$col = 0;
			//--
			for($i=0; $i<$len; ++$i) {
				if(
					isset(self::TABLE_CHECKSUMS[(string)$code[$i]])
					AND
					isset(self::TABLE_CHECKSUMS[(string)$code[$i]])
				) {
					$row += \intval(self::TABLE_CHECKSUMS[(string)$code[$i]][0]);
					$col += \intval(self::TABLE_CHECKSUMS[(string)$code[$i]][1]);
				} else {
					return (array) $bararray;
				} //end if else
			} //end for
			//--
			$row %= 6;
			$col %= 6;
			$chk = \array_keys(self::TABLE_CHECKSUMS, [ (int)$row, (int)$col ]);
			$code .= $chk[0];
			++$len;
			//--
		} //end if
		//--
		$k = 0;
		//--
		if($notkix) {
			//-- start bar
			$bararray['bcode'][$k++] = [ 't' => 1, 'w' => 1, 'h' => 2, 'p' => 0 ];
			$bararray['bcode'][$k++] = [ 't' => 0, 'w' => 1, 'h' => 2, 'p' => 0 ];
			$bararray['maxw'] += 2;
			//--
		} //end if
		//--
		for($i=0; $i<$len; ++$i) {
			//--
			for($j=0; $j<4; ++$j) {
				//--
				if(!isset(self::BAR_MODE[(string)$code[$i]])) {
					return (array) $bararray;
				} //end if
				//--
				switch(\intval(self::BAR_MODE[(string)$code[$i]][$j])) {
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
				$bararray['bcode'][$k++] = [ 't' => 1, 'w' => 1, 'h' => (int)$h, 'p' => (int)$p ];
				$bararray['bcode'][$k++] = [ 't' => 0, 'w' => 1, 'h' => 2,  'p' => 0 ];
				$bararray['maxw'] += 2;
				//--
			} //end for
			//--
		} //end for
		//--
		if($notkix) {
			// stop bar
			$bararray['bcode'][$k++] = [ 't' => 1, 'w' => 1, 'h' => 3, 'p' => 0 ];
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
