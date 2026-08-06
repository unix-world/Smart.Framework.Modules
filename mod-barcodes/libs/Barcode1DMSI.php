<?php
// MSI/MSI+ Barcode 1D for Smart.Framework
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
// BarCode 1D:	MSI
// (c) 2016-present, unix-world.org
// License: aGPLv3 (GNU AFFERO GENERAL PUBLIC LICENSE Version 3)
//============================================================
// Class to create MSI and MSI+ 1D barcodes.
// Very wide use world-wide, for retail
// TECHNICAL DATA / FEATURES OF MSI / MSI+:
// * Encodable Character Set: 			0..9
// * Maximum Data Characters: 			variable
//============================================================
//
// These class is derived from the following projects:
//
// "TcPDF" / Barcodes 1D / 6.10.1 / 20260203
// License: GNU-LGPL v3
// Copyright (C) 2010-2026  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class BarCode 1D MSI / MSI+
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
final class Barcode1DMSI {

	// ->

	private string $code = '';
	private bool   $checksum = false;


	private const CODES = [
		'0' => '100100100100',
		'1' => '100100100110',
		'2' => '100100110100',
		'3' => '100100110110',
		'4' => '100110100100',
		'5' => '100110100110',
		'6' => '100110110100',
		'7' => '100110110110',
		'8' => '110100100100',
		'9' => '110100100110',
		'A' => '110100110100',
		'B' => '110100110110',
		'C' => '110110100100',
		'D' => '110110100110',
		'E' => '110110110100',
		'F' => '110110110110',
	];


	public function __construct(string|int $code, bool $checksum=false) {
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
		$this->checksum = (bool) $checksum;
		//--
	} //END FUNCTION



	/**
	 * MSI: variation of Plessey code, with similar applications
	 * Contains digits (0 to 9) and encodes the data only in the width of bars.
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() : array {
		//--
		$code = (string) $this->code;
		//--
		$bararray = [ 'code' => (string)$code, 'maxw' => 0, 'maxh' => 1, 'bcode' => [] ];
		//--
		if($this->checksum === true) { // add checksum
			$clen = (int) \strlen($code);
			$p = 2;
			$check = 0;
			for($i=($clen - 1); $i>=0; --$i) {
				$check += (int) ((int)\hexdec($code[$i]) * $p);
				++$p;
				if((int)$p > 7) {
					$p = 2;
				} //end if
			} //end for
			$check %= 11;
			if((int)$check > 0) {
				$check = (int) (11 - $check);
			} //end if
			$code .= (string) $check;
		} //end if
		//--
		$seq = '110'; // left guard
		$clen = (int) \strlen($code);
		for($i=0; $i<$clen; ++$i) {
			$digit = (string) $code[$i];
			if(!isset(self::CODES[$digit])) {
				return (array) $bararray; // invalid character
			} //end if
			$seq .= (string) self::CODES[$digit];
		} //end for
		$seq .= '1001'; // right guard
		//--
		return (array) $this->binseq_to_array((string)$seq, (array)$bararray);
		//--
	} //END FUNCTION


	/**
	 * Convert binary barcode sequence to WarnockPDF barcode array.
	 * @param string $seq barcode as binary sequence.
	 * @param array $bararray barcode array to fill up
	 * @return array barcode representation.
	 * @protected
	 */
	private function binseq_to_array(string $seq, array $bararray) : array {
		//--
		$len = (int) \strlen($seq);
		if((int)$len <= 0) {
			return (array) $bararray;
		} //end if
		//--
		$w = 0;
		$k = 0;
		for($i=0; $i<$len; ++$i) {
			$w += 1;
			if(((int)$i == (int)((int)$len - 1)) OR (((int)$i < (int)((int)$len - 1)) AND ((string)$seq[$i] != (string)$seq[($i+1)]))) {
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
