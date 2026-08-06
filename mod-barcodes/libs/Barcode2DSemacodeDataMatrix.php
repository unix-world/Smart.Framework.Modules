<?php
// SemaCode DataMatrix Barcode 2D for Smart.Framework
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
// BarCode 2D: DataMatrix (Semacode)
// (c) 2016-present, unix-world.org
// License: aGPLv3 (GNU AFFERO GENERAL PUBLIC LICENSE Version 3)
//============================================================
// Class to create DataMatrix ECC 200 barcode arrays.
// DataMatrix (ISO/IEC 16022:2006) is a 2-D bar code.
// TECHNICAL DATA / FEATURES OF SEMACODE:
// * Encodable Character Set: 		UTF-8
// * Code Type: 					Matrix
// * Error Correction: 				Auto
// * Maximum Data Characters: 		3116 numeric, 2335 alphanumeric (ISO-8859-1), 1556 Binary / Bytes (UTF-8)
//============================================================
//
// This class is derived from the following projects:
//
// "TcPDF" / Barcodes 2D / 1.0.008 / 20140506
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class BarCode 2D DataMatrix
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20260203
 * @package 	modules:Barcodes2D
 *
 */
final class Barcode2DSemacodeDataMatrix {

	// ->


	/**
	 * @const INT
	 * ASCII encoding: ASCII character 0 to 127 (1 byte per CW)
	 */
	public const SEMACODE_ENCODE_ASCII = 0;

	/**
	 * @const INT
	 * C40 encoding: Upper-case alphanumeric (3/2 bytes per CW)
	 */
	public const SEMACODE_ENCODE_C40 = 1;

	/**
	 * @const INT
	 * TEXT encoding: Lower-case alphanumeric (3/2 bytes per CW)
	 */
	public const SEMACODE_ENCODE_TXT = 2;

	/**
	 * @const INT
	 * X12 encoding: ANSI X12 (3/2 byte per CW)
	 */
	public const SEMACODE_ENCODE_X12 = 3;

	/**
	 * @const INT
	 * EDIFACT encoding: ASCII character 32 to 94 (4/3 bytes per CW)
	 */
	public const SEMACODE_ENCODE_EDF = 4;

	/**
	 * @const INT
	 * BASE 256 encoding: ASCII character 0 to 255 (1 byte per CW)
	 */
	public const SEMACODE_ENCODE_B256 = 5;

	/**
	 * @const INT
	 * ASCII extended encoding: ASCII character 128 to 255 (1/2 byte per CW)
	 */
	public const SEMACODE_ENCODE_EXTASCII = 6;

	/**
	 * @const INT
	 * ASCII number encoding: ASCII digits (2 bytes per CW)
	 */
	public const SEMACODE_ENCODE_NUMASCII = 7;


	/**
	 * Barcode array to be returned which is readable by TCPDF.
	 * @private
	 */
	private array $barcode_array = [];

	/**
	 * Store last used encoding for data codewords.
	 * @private
	 */
	private int $last_enc = self::SEMACODE_ENCODE_ASCII;

	/**
	 * Table of Data Matrix ECC 200 Symbol Attributes:<ul>
	 * <li>total matrix rows (including finder pattern)</li>
	 * <li>total matrix cols (including finder pattern)</li>
	 * <li>total matrix rows (without finder pattern)</li>
	 * <li>total matrix cols (without finder pattern)</li>
	 * <li>region data rows (with finder pattern)</li>
	 * <li>region data col (with finder pattern)</li>
	 * <li>region data rows (without finder pattern)</li>
	 * <li>region data col (without finder pattern)</li>
	 * <li>horizontal regions</li>
	 * <li>vertical regions</li>
	 * <li>regions</li>
	 * <li>data codewords</li>
	 * <li>error codewords</li>
	 * <li>blocks</li>
	 * <li>data codewords per block</li>
	 * <li>error codewords per block</li>
	 * </ul>
	 * @private
	 */
	private const SYMBATTR = [
		// square form ---------------------------------------------------------------------------------------
		[ 0x00a,0x00a,0x008,0x008,0x00a,0x00a,0x008,0x008,0x001,0x001,0x001,0x003,0x005,0x001,0x003,0x005 ], // 10x10
		[ 0x00c,0x00c,0x00a,0x00a,0x00c,0x00c,0x00a,0x00a,0x001,0x001,0x001,0x005,0x007,0x001,0x005,0x007 ], // 12x12
		[ 0x00e,0x00e,0x00c,0x00c,0x00e,0x00e,0x00c,0x00c,0x001,0x001,0x001,0x008,0x00a,0x001,0x008,0x00a ], // 14x14
		[ 0x010,0x010,0x00e,0x00e,0x010,0x010,0x00e,0x00e,0x001,0x001,0x001,0x00c,0x00c,0x001,0x00c,0x00c ], // 16x16
		[ 0x012,0x012,0x010,0x010,0x012,0x012,0x010,0x010,0x001,0x001,0x001,0x012,0x00e,0x001,0x012,0x00e ], // 18x18
		[ 0x014,0x014,0x012,0x012,0x014,0x014,0x012,0x012,0x001,0x001,0x001,0x016,0x012,0x001,0x016,0x012 ], // 20x20
		[ 0x016,0x016,0x014,0x014,0x016,0x016,0x014,0x014,0x001,0x001,0x001,0x01e,0x014,0x001,0x01e,0x014 ], // 22x22
		[ 0x018,0x018,0x016,0x016,0x018,0x018,0x016,0x016,0x001,0x001,0x001,0x024,0x018,0x001,0x024,0x018 ], // 24x24
		[ 0x01a,0x01a,0x018,0x018,0x01a,0x01a,0x018,0x018,0x001,0x001,0x001,0x02c,0x01c,0x001,0x02c,0x01c ], // 26x26
		[ 0x020,0x020,0x01c,0x01c,0x010,0x010,0x00e,0x00e,0x002,0x002,0x004,0x03e,0x024,0x001,0x03e,0x024 ], // 32x32
		[ 0x024,0x024,0x020,0x020,0x012,0x012,0x010,0x010,0x002,0x002,0x004,0x056,0x02a,0x001,0x056,0x02a ], // 36x36
		[ 0x028,0x028,0x024,0x024,0x014,0x014,0x012,0x012,0x002,0x002,0x004,0x072,0x030,0x001,0x072,0x030 ], // 40x40
		[ 0x02c,0x02c,0x028,0x028,0x016,0x016,0x014,0x014,0x002,0x002,0x004,0x090,0x038,0x001,0x090,0x038 ], // 44x44
		[ 0x030,0x030,0x02c,0x02c,0x018,0x018,0x016,0x016,0x002,0x002,0x004,0x0ae,0x044,0x001,0x0ae,0x044 ], // 48x48
		[ 0x034,0x034,0x030,0x030,0x01a,0x01a,0x018,0x018,0x002,0x002,0x004,0x0cc,0x054,0x002,0x066,0x02a ], // 52x52
		[ 0x040,0x040,0x038,0x038,0x010,0x010,0x00e,0x00e,0x004,0x004,0x010,0x118,0x070,0x002,0x08c,0x038 ], // 64x64
		[ 0x048,0x048,0x040,0x040,0x012,0x012,0x010,0x010,0x004,0x004,0x010,0x170,0x090,0x004,0x05c,0x024 ], // 72x72
		[ 0x050,0x050,0x048,0x048,0x014,0x014,0x012,0x012,0x004,0x004,0x010,0x1c8,0x0c0,0x004,0x072,0x030 ], // 80x80
		[ 0x058,0x058,0x050,0x050,0x016,0x016,0x014,0x014,0x004,0x004,0x010,0x240,0x0e0,0x004,0x090,0x038 ], // 88x88
		[ 0x060,0x060,0x058,0x058,0x018,0x018,0x016,0x016,0x004,0x004,0x010,0x2b8,0x110,0x004,0x0ae,0x044 ], // 96x96
		[ 0x068,0x068,0x060,0x060,0x01a,0x01a,0x018,0x018,0x004,0x004,0x010,0x330,0x150,0x006,0x088,0x038 ], // 104x104
		[ 0x078,0x078,0x06c,0x06c,0x014,0x014,0x012,0x012,0x006,0x006,0x024,0x41a,0x198,0x006,0x0af,0x044 ], // 120x120
		[ 0x084,0x084,0x078,0x078,0x016,0x016,0x014,0x014,0x006,0x006,0x024,0x518,0x1f0,0x008,0x0a3,0x03e ], // 132x132
		[ 0x090,0x090,0x084,0x084,0x018,0x018,0x016,0x016,0x006,0x006,0x024,0x616,0x26c,0x00a,0x09c,0x03e ], // 144x144
		// rectangular form (currently unused) ---------------------------------------------------------------------------
		[ 0x008,0x012,0x006,0x010,0x008,0x012,0x006,0x010,0x001,0x001,0x001,0x005,0x007,0x001,0x005,0x007 ], // 8x18
		[ 0x008,0x020,0x006,0x01c,0x008,0x010,0x006,0x00e,0x001,0x002,0x002,0x00a,0x00b,0x001,0x00a,0x00b ], // 8x32
		[ 0x00c,0x01a,0x00a,0x018,0x00c,0x01a,0x00a,0x018,0x001,0x001,0x001,0x010,0x00e,0x001,0x010,0x00e ], // 12x26
		[ 0x00c,0x024,0x00a,0x020,0x00c,0x012,0x00a,0x010,0x001,0x002,0x002,0x00c,0x012,0x001,0x00c,0x012 ], // 12x36
		[ 0x010,0x024,0x00e,0x020,0x010,0x012,0x00e,0x010,0x001,0x002,0x002,0x020,0x018,0x001,0x020,0x018 ], // 16x36
		[ 0x010,0x030,0x00e,0x02c,0x010,0x018,0x00e,0x016,0x001,0x002,0x002,0x031,0x01c,0x001,0x031,0x01c ], // 16x48
	];

	/**
	 * Map encodation modes whit character sets.
	 * @private
	 */
	private const CHSET_ID = [
		self::SEMACODE_ENCODE_C40 => 'C40',
		self::SEMACODE_ENCODE_TXT => 'TXT',
		self::SEMACODE_ENCODE_X12 => 'X12',
	];

	/**
	 * Basic set of characters for each encodation mode.
	 * @private
	 */
	private const CHSET_ARR = [
		'C40' => [ // Basic set for C40 ----------------------------------------------------------------------------
			'S1'=>0x00,'S2'=>0x01,'S3'=>0x02,0x20=>0x03,0x30=>0x04,0x31=>0x05,0x32=>0x06,0x33=>0x07,0x34=>0x08,0x35=>0x09, //
			0x36=>0x0a,0x37=>0x0b,0x38=>0x0c,0x39=>0x0d,0x41=>0x0e,0x42=>0x0f,0x43=>0x10,0x44=>0x11,0x45=>0x12,0x46=>0x13, //
			0x47=>0x14,0x48=>0x15,0x49=>0x16,0x4a=>0x17,0x4b=>0x18,0x4c=>0x19,0x4d=>0x1a,0x4e=>0x1b,0x4f=>0x1c,0x50=>0x1d, //
			0x51=>0x1e,0x52=>0x1f,0x53=>0x20,0x54=>0x21,0x55=>0x22,0x56=>0x23,0x57=>0x24,0x58=>0x25,0x59=>0x26,0x5a=>0x27, //
		],
		'TXT' => [ // Basic set for TEXT ---------------------------------------------------------------------------
			'S1'=>0x00,'S2'=>0x01,'S3'=>0x02,0x20=>0x03,0x30=>0x04,0x31=>0x05,0x32=>0x06,0x33=>0x07,0x34=>0x08,0x35=>0x09, //
			0x36=>0x0a,0x37=>0x0b,0x38=>0x0c,0x39=>0x0d,0x61=>0x0e,0x62=>0x0f,0x63=>0x10,0x64=>0x11,0x65=>0x12,0x66=>0x13, //
			0x67=>0x14,0x68=>0x15,0x69=>0x16,0x6a=>0x17,0x6b=>0x18,0x6c=>0x19,0x6d=>0x1a,0x6e=>0x1b,0x6f=>0x1c,0x70=>0x1d, //
			0x71=>0x1e,0x72=>0x1f,0x73=>0x20,0x74=>0x21,0x75=>0x22,0x76=>0x23,0x77=>0x24,0x78=>0x25,0x79=>0x26,0x7a=>0x27, //
		],
		'SH1' => [ // Shift 1 set ----------------------------------------------------------------------------------
			0x00=>0x00,0x01=>0x01,0x02=>0x02,0x03=>0x03,0x04=>0x04,0x05=>0x05,0x06=>0x06,0x07=>0x07,0x08=>0x08,0x09=>0x09, //
			0x0a=>0x0a,0x0b=>0x0b,0x0c=>0x0c,0x0d=>0x0d,0x0e=>0x0e,0x0f=>0x0f,0x10=>0x10,0x11=>0x11,0x12=>0x12,0x13=>0x13, //
			0x14=>0x14,0x15=>0x15,0x16=>0x16,0x17=>0x17,0x18=>0x18,0x19=>0x19,0x1a=>0x1a,0x1b=>0x1b,0x1c=>0x1c,0x1d=>0x1d, //
			0x1e=>0x1e,0x1f=>0x1f,                                                                                         //
		],
		'SH2' => [ // Shift 2 set ----------------------------------------------------------------------------------
			0x21=>0x00,0x22=>0x01,0x23=>0x02,0x24=>0x03,0x25=>0x04,0x26=>0x05,0x27=>0x06,0x28=>0x07,0x29=>0x08,0x2a=>0x09, //
			0x2b=>0x0a,0x2c=>0x0b,0x2d=>0x0c,0x2e=>0x0d,0x2f=>0x0e,0x3a=>0x0f,0x3b=>0x10,0x3c=>0x11,0x3d=>0x12,0x3e=>0x13, //
			0x3f=>0x14,0x40=>0x15,0x5b=>0x16,0x5c=>0x17,0x5d=>0x18,0x5e=>0x19,0x5f=>0x1a,'F1'=>0x1b,'US'=>0x1e,            //
		],
		'S3C' => [ // Shift 3 set for C40 --------------------------------------------------------------------------
			0x60=>0x00,0x61=>0x01,0x62=>0x02,0x63=>0x03,0x64=>0x04,0x65=>0x05,0x66=>0x06,0x67=>0x07,0x68=>0x08,0x69=>0x09, //
			0x6a=>0x0a,0x6b=>0x0b,0x6c=>0x0c,0x6d=>0x0d,0x6e=>0x0e,0x6f=>0x0f,0x70=>0x10,0x71=>0x11,0x72=>0x12,0x73=>0x13, //
			0x74=>0x14,0x75=>0x15,0x76=>0x16,0x77=>0x17,0x78=>0x18,0x79=>0x19,0x7a=>0x1a,0x7b=>0x1b,0x7c=>0x1c,0x7d=>0x1d, //
			0x7e=>0x1e,0x7f=>0x1f,                                                                                         //
		],
		'S3T' => [ // Shift 3 set for TEXT -------------------------------------------------------------------------
			0x60=>0x00,0x41=>0x01,0x42=>0x02,0x43=>0x03,0x44=>0x04,0x45=>0x05,0x46=>0x06,0x47=>0x07,0x48=>0x08,0x49=>0x09, //
			0x4a=>0x0a,0x4b=>0x0b,0x4c=>0x0c,0x4d=>0x0d,0x4e=>0x0e,0x4f=>0x0f,0x50=>0x10,0x51=>0x11,0x52=>0x12,0x53=>0x13, //
			0x54=>0x14,0x55=>0x15,0x56=>0x16,0x57=>0x17,0x58=>0x18,0x59=>0x19,0x5a=>0x1a,0x7b=>0x1b,0x7c=>0x1c,0x7d=>0x1d, //
			0x7e=>0x1e,0x7f=>0x1f,                                                                                         //
		],
		'X12' => [ // Set for X12 ----------------------------------------------------------------------------------
			0x0d=>0x00,0x2a=>0x01,0x3e=>0x02,0x20=>0x03,0x30=>0x04,0x31=>0x05,0x32=>0x06,0x33=>0x07,0x34=>0x08,0x35=>0x09, //
			0x36=>0x0a,0x37=>0x0b,0x38=>0x0c,0x39=>0x0d,0x41=>0x0e,0x42=>0x0f,0x43=>0x10,0x44=>0x11,0x45=>0x12,0x46=>0x13, //
			0x47=>0x14,0x48=>0x15,0x49=>0x16,0x4a=>0x17,0x4b=>0x18,0x4c=>0x19,0x4d=>0x1a,0x4e=>0x1b,0x4f=>0x1c,0x50=>0x1d, //
			0x51=>0x1e,0x52=>0x1f,0x53=>0x20,0x54=>0x21,0x55=>0x22,0x56=>0x23,0x57=>0x24,0x58=>0x25,0x59=>0x26,0x5a=>0x27, //
		],
	];


	/**
	 * This is the class constructor.
	 * Creates a datamatrix object
	 * @param $code (string) Code to represent using Datamatrix.
	 * @public
	 */
	public function __construct(?string $code) {
		//--
		if(((string)$code == '') OR ((string)$code === '\0')) {
			return;
		} //end if
		//--
		$code = (string) $code; // force string
		//--
		$this->barcode_array = [];
		$this->barcode_array['code'] = $code;
		//-- get data codewords
		$cw = $this->getHighLevelEncoding($code);
		// number of data codewords
		$nd = (int) \count($cw);
		// check size
		if((int)$nd > 1558) {
			return;
		} //end if
		//-- get minimum required matrix size.
		foreach(self::SYMBATTR as $u => $params) {
			if((int)$params[11] >= (int)$nd) {
				break;
			} //end if
		} //end foreach
		//--
		if((int)$params[11] < (int)$nd) {
			//-- too much data
			return;
			//--
		} elseif((int)$params[11] > (int)$nd) {
			//-- add padding
			if(((int)((int)$params[11] - (int)$nd) > 1) AND ((int)$cw[($nd - 1)] != 254)) {
				if((int)$this->last_enc == (int)self::SEMACODE_ENCODE_EDF) {
					//-- switch to ASCII encoding
					$cw[] = 124;
					++$nd;
					//--
				} elseif(((int)$this->last_enc != (int)self::SEMACODE_ENCODE_ASCII) AND ((int)$this->last_enc != (int)self::SEMACODE_ENCODE_B256)) {
					//-- switch to ASCII encoding
					$cw[] = 254;
					++$nd;
					//--
				} //end if else
			} //end if
			//--
			if((int)$params[11] > (int)$nd) {
				//-- add first pad
				$cw[] = 129;
				++$nd;
				//-- add remaining pads
				for($i=$nd; $i<(int)$params[11]; ++$i) {
					$cw[] = (int) $this->get253StateCodeword(129, $i);
				} //end for
				//--
			} //end if
			//--
		} //end if else
		//-- add error correction codewords
		$cw = (array) $this->getErrorCorrection($cw, $params[13], $params[14], $params[15]);
		//-- initialize empty arrays
		$grid = \array_fill(0, ($params[2] * $params[3]), 0);
		//-- get placement map
		$places = $this->getPlacementMap($params[2], $params[3]);
		//-- fill the grid with data
		$grid = [];
		$i = 0;
		//-- region data row max index
		$rdri = (int) ($params[4] - 1);
		//-- region data column max index
		$rdci = (int) ($params[5] - 1);
		//-- for each vertical region
		for($vr=0; $vr<$params[9]; ++$vr) {
			//-- for each row on region
			for($r=0; $r<$params[4]; ++$r) {
				//-- get row
				$row = (int) (($vr * $params[4]) + $r);
				//-- for each horizontal region
				for($hr=0; $hr<$params[8]; ++$hr) {
					//-- for each column on region
					for($c=0; $c<$params[5]; ++$c) {
						//-- get column
						$col = (int) (($hr * $params[5]) + $c);
						//-- braw bits by case
						if((int)$r == 0) {
							//-- top finder pattern
							if($c % 2) {
								$grid[$row][$col] = 0;
							} else {
								$grid[$row][$col] = 1;
							} //end if else
							//--
						} elseif((int)$r == (int)$rdri) {
							//-- bottom finder pattern
							$grid[$row][$col] = 1;
							//--
						} elseif((int)$c == 0) {
							//-- left finder pattern
							$grid[$row][$col] = 1;
							//--
						} elseif((int)$c == (int)$rdci) {
							//-- right finder pattern
							if($r % 2) {
								$grid[$row][$col] = 1;
							} else {
								$grid[$row][$col] = 0;
							} //end if else
							//--
						} else {
							//-- data bit
							if((int)$places[$i] < 2) {
								//--
								$grid[$row][$col] = $places[$i];
								//--
							} else {
								//-- codeword ID
								$cw_id = (int) (\floor((string)($places[$i] / 10)) - 1); // unixman: fix floor
								//-- codeword BIT mask
								$cw_bit = \pow(2, (8 - ($places[$i] % 10)));
								$grid[$row][$col] = (($cw[$cw_id] & $cw_bit) == 0) ? 0 : 1;
								//--
							} //end if else
							//--
							++$i;
							//--
						} //end if else
						//--
					} //end for
					//--
				} //end for
				//--
			} //end for
			//--
		} //end for
		//--
		$this->barcode_array['num_rows'] = (int)   $params[0];
		$this->barcode_array['num_cols'] = (int)   $params[1];
		$this->barcode_array['bcode']    = (array) $grid;
		//--
	} //END FUNCTION


	/**
	 * Returns a barcode array which is readable by TCPDF
	 * @return array barcode array readable by TCPDF;
	 * @public
	 */
	public function getBarcodeArray() : array {
		//--
		return (array) $this->barcode_array;
		//--
	} //END FUNCTION


	/**
	 * Product of two numbers in a Power-of-Two Galois Field
	 * @param $a (int) first number to multiply.
	 * @param $b (int) second number to multiply.
	 * @param $log (array) Log table.
	 * @param $alog (array) Anti-Log table.
	 * @param $gf (int) Number of Factors of the Reed-Solomon polynomial.
	 * @return int product
	 * @private
	 */
	private function getGFProduct(int $a, int $b, array $log, array $alog, int $gf) : int {
		//--
		if(((int)$a == 0) OR ((int)$b == 0)) {
			return 0;
		} //end if
		//--
		return (int) ($alog[($log[$a] + $log[$b]) % ($gf - 1)]);
		//--
	} //END FUNCTION


	/**
	 * Add error correction codewords to data codewords array (ANNEX E).
	 * @param $wd (array) Array of datacodewords.
	 * @param $nb (int) Number of blocks.
	 * @param $nd (int) Number of data codewords per block.
	 * @param $nc (int) Number of correction codewords per block.
	 * @param $gf (int) numner of fields on log/antilog table (power of 2).
	 * @param $pp (int) The value of its prime modulus polynomial (301 for ECC200).
	 * @return array data codewords + error codewords
	 * @private
	 */
	private function getErrorCorrection(array $wd, int $nb, int $nd, int $nc, int $gf=256, int $pp=301) : array {
		//-- generate the log ($log) and antilog ($alog) tables
		$log[0] = 0;
		$alog[0] = 1;
		for($i=1; $i<$gf; ++$i) {
			$alog[$i] = (int) ($alog[($i - 1)] * 2);
			if((int)$alog[$i] >= (int)$gf) {
				$alog[$i] ^= $pp;
			} //end if
			$log[$alog[$i]] = $i;
		} //end for
		ksort($log);
		//-- generate the polynomial coefficients (c)
		$c = \array_fill(0, ($nc + 1), 0);
		$c[0] = 1;
		for($i=1; $i<=$nc; ++$i) {
			$c[$i] = $c[($i-1)];
			for($j=($i-1); $j>= 1; --$j) {
				$c[$j] = (int)($c[($j - 1)]) ^ (int)$this->getGFProduct($c[$j], $alog[$i], $log, $alog, $gf);
			} //end for
			$c[0] = (int) $this->getGFProduct($c[0], $alog[$i], $log, $alog, $gf);
		} //end for
		ksort($c);
		//-- total number of data codewords
		$num_wd = (int) ($nb * $nd);
		//-- total number of error codewords
		$num_we = (int) ($nb * $nc);
		//-- for each block
		for($b=0; $b<$nb; ++$b) {
			//-- create interleaved data block
			$block = [];
			for($n=$b; $n<$num_wd; $n+=$nb) {
				$block[] = $wd[$n];
			} //end for
			//-- initialize error codewords
			$we = \array_fill(0, ($nc + 1), 0);
			//-- calculate error correction codewords for this block
			for($i=0; $i<$nd; ++$i) {
				$k = (int) ($we[0] ^ $block[$i]);
				for($j=0; $j<$nc; ++$j) {
					$we[$j] = (int) ((int)$we[($j + 1)] ^ (int)$this->getGFProduct($k, $c[($nc - $j - 1)], $log, $alog, $gf));
				} //end for
			} //end for
			//-- add error codewords at the end of data codewords
			$j = 0;
			for($i=$b; $i<$num_we; $i+=$nb) {
				$wd[($num_wd + $i)] = (int) $we[$j];
				++$j;
			} //end for
			//--
		} //end for
		//-- reorder codewords
		ksort($wd);
		//--
		return $wd;
		//--
	} //END FUNCTION


	/**
	 * Return the 253-state codeword
	 * @param $cwpad (int) Pad codeword.
	 * @param $cwpos (int) Number of data codewords from the beginning of encoded data.
	 * @return pad codeword
	 * @private
	 */
	private function get253StateCodeword(int $cwpad, int $cwpos) : int {
		//--
		$pad = (int) ($cwpad + (((149 * $cwpos) % 253) + 1));
		//--
		if((int)$pad > 254) {
			$pad -= 254;
		} //end if
		//--
		return (int) $pad;
		//--
	} //END FUNCTION


	/**
	 * Return the 255-state codeword
	 * @param $cwpad (int) Pad codeword.
	 * @param $cwpos (int) Number of data codewords from the beginning of encoded data.
	 * @return pad codeword
	 * @private
	 */
	private function get255StateCodeword(int $cwpad, int $cwpos) : int {
		//--
		$pad = (int) ($cwpad + (((149 * $cwpos) % 255) + 1));
		//--
		if((int)$pad > 255) {
			$pad -= 256;
		} //end if
		//--
		return (int) $pad;
		//--
	} //END FUNCTION


	/**
	 * Returns true if the char belongs to the selected mode
	 * @param $chr (int) Character (byte) to check.
	 * @param $mode (int) Current encoding mode.
	 * @return boolean true if the char is of the selected mode.
	 * @private
	 */
	private function isCharMode(int $chr, int $mode) : bool {
		//--
		$status = false;
		//--
		switch($mode) {
			case self::SEMACODE_ENCODE_ASCII:  // ASCII character 0 to 127
				$status = (($chr >= 0) AND ($chr <= 127));
				break;
			case self::SEMACODE_ENCODE_C40:  // Upper-case alphanumeric
				$status = (($chr == 32) OR (($chr >= 48) AND ($chr <= 57)) OR (($chr >= 65) AND ($chr <= 90)));
				break;
			case self::SEMACODE_ENCODE_TXT:  // Lower-case alphanumeric
				$status = (($chr == 32) OR (($chr >= 48) AND ($chr <= 57)) OR (($chr >= 97) AND ($chr <= 122)));
				break;
			case self::SEMACODE_ENCODE_X12:  // ANSI X12
				$status = (($chr == 13) OR ($chr == 42) OR ($chr == 62));
				break;
			case self::SEMACODE_ENCODE_EDF:  // ASCII character 32 to 94
				$status = (($chr >= 32) AND ($chr <= 94));
				break;
			case self::SEMACODE_ENCODE_B256:  // Function character (FNC1, Structured Append, Reader Program, or Code Page)
				$status = (($chr == 232) OR ($chr == 233) OR ($chr == 234) OR ($chr == 241));
				break;
			case self::SEMACODE_ENCODE_EXTASCII:  // ASCII character 128 to 255
				$status = (($chr >= 128) AND ($chr <= 255));
				break;
			case self::SEMACODE_ENCODE_NUMASCII:  // ASCII digits
				$status = (($chr >= 48) AND ($chr <= 57));
				break;
		} //end switch
		//--
		return (bool) $status;
		//--
	} //END FUNCTION


	/**
	 * The look-ahead test scans the data to be encoded to find the best mode (Annex P - steps from J to S).
	 * @param $data (string) data to encode
	 * @param $pos (int) current position
	 * @param $mode (int) current encoding mode
	 * @return int encoding mode
	 * @private
	 */
	private function lookAheadTest(?string $data, int $pos, int $mode) : int {
		//--
		$data = (string) $data;
		//--
		$data_length = (int) \strlen($data);
		if((int)$pos >= (int)$data_length) {
			return (int) $mode;
		} //end if
		$charscount = 0; // count processed chars
		//-- STEP J
		if((int)$mode == (int)self::SEMACODE_ENCODE_ASCII) {
			$numch = [ 0, 1, 1, 1, 1, 1.25 ];
		} else {
			$numch = [ 1, 2, 2, 2, 2, 2.25 ];
			$numch[$mode] = 0;
		} //end if else
		//--
		while(true) {
			//-- STEP K
			if((int)((int)$pos + (int)$charscount) == (int)$data_length) { // unixman: fix ceil
				if((int)$numch[self::SEMACODE_ENCODE_ASCII] <= (int)\ceil((string)(\min($numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])))) {
					return (int) self::SEMACODE_ENCODE_ASCII;
				} //end if
				if((int)$numch[self::SEMACODE_ENCODE_B256] < (int)\ceil((string)(\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF])))) {
					return (int) self::SEMACODE_ENCODE_B256;
				} //end if
				if((int)$numch[self::SEMACODE_ENCODE_EDF] < (int)\ceil((string)(\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_B256])))) {
					return (int) self::SEMACODE_ENCODE_EDF;
				} //end if
				if((int)$numch[self::SEMACODE_ENCODE_TXT] < (int)\ceil((string)(\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])))) {
					return (int) self::SEMACODE_ENCODE_TXT;
				} //end if
				if((int)$numch[self::SEMACODE_ENCODE_X12] < (int)\ceil((string)(\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])))) {
					return (int) self::SEMACODE_ENCODE_X12;
				} //end if
				return (int) self::SEMACODE_ENCODE_C40;
			} //end while
			//-- get char
			$chr = (int) \ord((string)$data[(int)$pos + (int)$charscount]);
			//--
			$charscount++;
			//-- STEP L
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_NUMASCII)) {
				$numch[self::SEMACODE_ENCODE_ASCII] += (1 / 2);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_ASCII] = ceil((string)$numch[self::SEMACODE_ENCODE_ASCII]); // unixman: fix ceil
				$numch[self::SEMACODE_ENCODE_ASCII] += 2;
			} else {
				$numch[self::SEMACODE_ENCODE_ASCII] = ceil((string)$numch[self::SEMACODE_ENCODE_ASCII]); // unixman: fix ceil
				$numch[self::SEMACODE_ENCODE_ASCII] += 1;
			} //end if else
			//-- STEP M
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_C40)) {
				$numch[self::SEMACODE_ENCODE_C40] += (2 / 3);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_C40] += (8 / 3);
			} else {
				$numch[self::SEMACODE_ENCODE_C40] += (4 / 3);
			} //end if else
			//-- STEP N
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_TXT)) {
				$numch[self::SEMACODE_ENCODE_TXT] += (2 / 3);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_TXT] += (8 / 3);
			} else {
				$numch[self::SEMACODE_ENCODE_TXT] += (4 / 3);
			} //end if else
			//-- STEP O
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_X12) OR $this->isCharMode($chr, self::SEMACODE_ENCODE_C40)) {
				$numch[self::SEMACODE_ENCODE_X12] += (2 / 3);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_X12] += (13 / 3);
			} else {
				$numch[self::SEMACODE_ENCODE_X12] += (10 / 3);
			} //end if else
			//-- STEP P
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_EDF)) {
				$numch[self::SEMACODE_ENCODE_EDF] += (3 / 4);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_EDF] += (17 / 4);
			} else {
				$numch[self::SEMACODE_ENCODE_EDF] += (13 / 4);
			} //end if else
			//-- STEP Q
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_B256)) {
				$numch[self::SEMACODE_ENCODE_B256] += 4;
			} else {
				$numch[self::SEMACODE_ENCODE_B256] += 1;
			} //end if else
			//-- STEP R
			if((int)$charscount >= 4) {
				if((int)($numch[self::SEMACODE_ENCODE_ASCII] + 1) <= (int)\min($numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					return (int) self::SEMACODE_ENCODE_ASCII;
				} //end if
				if(((int)($numch[self::SEMACODE_ENCODE_B256] + 1) <= (int)$numch[self::SEMACODE_ENCODE_ASCII]) OR ((int)($numch[self::SEMACODE_ENCODE_B256] + 1) < (int)\min($numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF]))) {
					return (int) self::SEMACODE_ENCODE_B256;
				} //end if
				if((int)($numch[self::SEMACODE_ENCODE_EDF] + 1) < (int)\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_B256])) {
					return (int) self::SEMACODE_ENCODE_EDF;
				} //end if
				if((int)($numch[self::SEMACODE_ENCODE_TXT] + 1) < (int)\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					return (int) self::SEMACODE_ENCODE_TXT;
				} //end if
				if((int)($numch[self::SEMACODE_ENCODE_X12] + 1) < (int)\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					return (int) self::SEMACODE_ENCODE_X12;
				} //end if
				if((int)($numch[self::SEMACODE_ENCODE_C40] + 1) < (int)\min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					if((int)$numch[self::SEMACODE_ENCODE_C40] < (int)$numch[self::SEMACODE_ENCODE_X12]) {
						return (int) self::SEMACODE_ENCODE_C40;
					} //end if
					if((int)$numch[self::SEMACODE_ENCODE_C40] == (int)$numch[self::SEMACODE_ENCODE_X12]) {
						$k = (int) ($pos + $charscount + 1);
						while((int)$k < (int)$data_length) {
							$tmpchr = (int) \ord((string)$data[$k]);
							if($this->isCharMode($tmpchr, self::SEMACODE_ENCODE_X12)) {
								return (int) self::SEMACODE_ENCODE_X12;
							} elseif(!($this->isCharMode($tmpchr, self::SEMACODE_ENCODE_X12) OR $this->isCharMode($tmpchr, self::SEMACODE_ENCODE_C40))) {
								break;
							} //end if else
							++$k;
						} //end while
						return (int) self::SEMACODE_ENCODE_C40;
					} //end if
				} //end if
			} //end if
			//--
		} // end of while
		//--
		return (int) self::SEMACODE_ENCODE_ASCII;
		//--
	} //END FUNCTION


	/**
	 * Get the switching codeword to a new encoding mode (latch codeword)
	 * @param $mode (int) New encoding mode.
	 * @return (int) Switch codeword.
	 * @private
	 */
	private function getSwitchEncodingCodeword(int $mode) : int {
		//--
		$cw = 0;
		//--
		switch($mode) {
			case self::SEMACODE_ENCODE_ASCII:  // ASCII character 0 to 127
				$cw = 254;
				if((int)$this->last_enc == (int)self::SEMACODE_ENCODE_EDF) {
					$cw = 124;
				} //end if
				break;
			case self::SEMACODE_ENCODE_C40:  // Upper-case alphanumeric
				$cw = 230;
				break;
			case self::SEMACODE_ENCODE_TXT:  // Lower-case alphanumeric
				$cw = 239;
				break;
			case self::SEMACODE_ENCODE_X12:  // ANSI X12
				$cw = 238;
				break;
			case self::SEMACODE_ENCODE_EDF:  // ASCII character 32 to 94
				$cw = 240;
				break;
			case self::SEMACODE_ENCODE_B256:  // Function character (FNC1, Structured Append, Reader Program, or Code Page)
				$cw = 231;
				break;
		} //end switch
		//--
		return (int) $cw;
		//--
	} //END FUNCTION


	/**
	 * Choose the minimum matrix size and return the max number of data codewords.
	 * @param $numcw (int) Number of current codewords.
	 * @return number of data codewords in matrix
	 * @private
	 */
	private function getMaxDataCodewords(int $numcw) : int {
		//--
		foreach(self::SYMBATTR as $key => $matrix) {
			if((int)$matrix[11] >= (int)$numcw) {
				return (int) $matrix[11];
			} //end if
		} //end foreach
		//--
		return 0;
		//--
	} //END FUNCTION


	/**
	 * Get high level encoding using the minimum symbol data characters for ECC 200
	 * @param $data (string) data to encode
	 * @return array of codewords
	 * @private
	 */
	private function getHighLevelEncoding(?string $data) : array {
		//--
		$data = (string) $data;
		//-- STEP A. Start in ASCII encodation.
		$enc = (int) self::SEMACODE_ENCODE_ASCII; // current encoding mode
		$pos = 0; // current position
		$cw = []; // array of codewords to be returned
		$cw_num = 0; // number of data codewords
		$data_length = (int) \strlen($data); // number of chars
		//--
		while((int)$pos < (int)$data_length) {
			//-- set last used encoding
			$this->last_enc = (int) $enc;
			switch($enc) {
				case self::SEMACODE_ENCODE_ASCII:  // STEP B. While in ASCII encodation
					//--
					if(((int)$data_length > 1) AND ((int)$pos < (int)((int)$data_length - 1)) AND ($this->isCharMode(ord($data[$pos]), self::SEMACODE_ENCODE_NUMASCII) AND $this->isCharMode(ord($data[$pos + 1]), self::SEMACODE_ENCODE_NUMASCII))) {
						// 1. If the next data sequence is at least 2 consecutive digits, encode the next two digits as a double digit in ASCII mode.
						$cw[] = (int) (\intval(\substr($data, $pos, 2)) + 130);
						++$cw_num;
						$pos += 2;
					} else {
						// 2. If the look-ahead test (starting at step J) indicates another mode, switch to that mode.
						$newenc = (int) $this->lookAheadTest($data, $pos, $enc);
						if((int)$newenc != (int)$enc) {
							// switch to new encoding
							$enc = (int) $newenc;
							$cw[] = (int) $this->getSwitchEncodingCodeword($enc);
							++$cw_num;
						} else {
							// get new byte
							$chr = (int) \ord((string)$data[$pos]);
							++$pos;
							if($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
								// 3. If the next data character is extended ASCII (greater than 127) encode it in ASCII mode first using the Upper Shift (value 235) character.
								$cw[] = 235;
								$cw[] = (int) ((int)$chr - 127);
								$cw_num += 2;
							} else {
								// 4. Otherwise process the next data character in ASCII encodation.
								$cw[] = (int) ((int)$chr + 1);
								++$cw_num;
							} //end if else
						} //end if else
					} //end if else
					//--
					break;
				case self::SEMACODE_ENCODE_C40 :   // Upper-case alphanumeric
				case self::SEMACODE_ENCODE_TXT :   // Lower-case alphanumeric
				case self::SEMACODE_ENCODE_X12 :   // ANSI X12
					//--
					$temp_cw = [];
					$p = 0;
					$epos = $pos;
					// get charset ID
					$set_id = self::CHSET_ID[$enc];
					// get basic charset for current encoding
					$charset = self::CHSET_ARR[$set_id];
					do {
						//-- 2. process the next character in C40 encodation.
						$chr = (int) \ord((string)$data[$epos]);
						++$epos;
						//-- check for extended character
						if($chr & 0x80) {
							if((int)$enc == (int)self::SEMACODE_ENCODE_X12) {
								return [];
							} //end if
							$chr = (int) ($chr & 0x7f);
							$temp_cw[] = 1; // shift 2
							$temp_cw[] = 30; // upper shift
							$p += 2;
						} //end if
						if(isset($charset[$chr])) {
							$temp_cw[] = $charset[$chr];
							++$p;
						} else {
							if(isset(self::CHSET_ARR['SH1'][$chr])) {
								$temp_cw[] = 0; // shift 1
								$shiftset = self::CHSET_ARR['SH1'];
							} elseif(isset($chr, self::CHSET_ARR['SH2'][$chr])) {
								$temp_cw[] = 1; // shift 2
								$shiftset = self::CHSET_ARR['SH2'];
							} elseif(($enc == self::SEMACODE_ENCODE_C40) AND isset(self::CHSET_ARR['S3C'][$chr])) {
								$temp_cw[] = 2; // shift 3
								$shiftset = self::CHSET_ARR['S3C'];
							} elseif(($enc == self::SEMACODE_ENCODE_TXT) AND isset(self::CHSET_ARR['S3T'][$chr])) {
								$temp_cw[] = 2; // shift 3
								$shiftset = self::CHSET_ARR['S3T'];
							} else {
								return [];
							} //end if else
							$temp_cw[] = $shiftset[$chr];
							$p += 2;
						} //end if else
						if($p >= 3) {
							$c1 = \array_shift($temp_cw);
							$c2 = \array_shift($temp_cw);
							$c3 = \array_shift($temp_cw);
							$p -= 3;
							$tmp = ((1600 * $c1) + (40 * $c2) + $c3 + 1);
							$cw[] = (int) ($tmp >> 8);
							$cw[] = (int) ($tmp % 256);
							$cw_num += 2;
							$pos = (int) $epos;
							// 1. If the C40 encoding is at the point of starting a new double symbol character and if the look-ahead test (starting at step J) indicates another mode, switch to that mode.
							$newenc = (int) $this->lookAheadTest($data, $pos, $enc);
							if((int)$newenc != (int)$enc) {
								// switch to new encoding
								$enc = (int) $newenc;
								if((int)$enc != (int)self::SEMACODE_ENCODE_ASCII) {
									// set unlatch character
									$cw[] = (int) $this->getSwitchEncodingCodeword(self::SEMACODE_ENCODE_ASCII);
									++$cw_num;
								} //end if
								$cw[] = (int) $this->getSwitchEncodingCodeword($enc);
								++$cw_num;
								$pos -= $p;
								$p = 0;
								break;
							} //end if
						} //end if
					} while(((int)$p > 0) AND ((int)$epos < (int)$data_length));
					//-- process last data (if any)
					if($p > 0) {
						// get remaining number of data symbols
						$cwr = (int) ((int)$this->getMaxDataCodewords($cw_num) - (int)$cw_num);
						if(((int)$cwr == 1) AND ((int)$p == 1)) {
							// d. If one symbol character remains and one C40 value (data character) remains to be encoded
							$c1 = \array_shift($temp_cw);
							--$p;
							$cw[] = (int) ($chr + 1);
							++$cw_num;
							$pos = (int) $epos;
							$enc = self::SEMACODE_ENCODE_ASCII;
							$this->last_enc = (int) $enc;
						} elseif(((int)$cwr == 2) AND ((int)$p == 1)) {
							// c. If two symbol characters remain and only one C40 value (data character) remains to be encoded
							$c1 = \array_shift($temp_cw);
							--$p;
							$cw[] = 254;
							$cw[] = (int) ($chr + 1);
							$cw_num += 2;
							$pos = (int) $epos;
							$enc = self::SEMACODE_ENCODE_ASCII;
							$this->last_enc = (int) $enc;
						} elseif(((int)$cwr == 2) AND ((int)$p == 2)) {
							// b. If two symbol characters remain and two C40 values remain to be encoded
							$c1 = \array_shift($temp_cw);
							$c2 = \array_shift($temp_cw);
							$p -= 2;
							$tmp = (int) ((1600 * $c1) + (40 * $c2) + 1);
							$cw[] = (int) ($tmp >> 8);
							$cw[] = (int) ($tmp % 256);
							$cw_num += 2;
							$pos = (int) $epos;
							$enc = self::SEMACODE_ENCODE_ASCII;
							$this->last_enc = (int) $enc;
						} else {
							// switch to ASCII encoding
							if((int)$enc != (int)self::SEMACODE_ENCODE_ASCII) {
								$enc = self::SEMACODE_ENCODE_ASCII;
								$this->last_enc = (int) $enc;
								$cw[] = (int) $this->getSwitchEncodingCodeword($enc);
								++$cw_num;
								$pos = (int) ($epos - $p);
							} //end if
						} //end if else
					} //end if
					//--
					break;
				case self::SEMACODE_ENCODE_EDF:  // F. While in EDIFACT (EDF) encodation
					//-- initialize temporary array with 0 length
					$temp_cw = [];
					$epos = $pos;
					$field_length = 0;
					$newenc = $enc;
					do {
						// 2. process the next character in EDIFACT encodation.
						$chr = (int) \ord((string)$data[$epos]);
						if($this->isCharMode($chr, self::SEMACODE_ENCODE_EDF)) {
							++$epos;
							$temp_cw[] = $chr;
							++$field_length;
						} //end if
						if(((int)$field_length == 4) OR ((int)$epos == (int)$data_length) OR !$this->isCharMode($chr, self::SEMACODE_ENCODE_EDF)) {
							if(((int)$epos == (int)$data_length) AND ((int)$field_length < 3)) {
								$enc = self::SEMACODE_ENCODE_ASCII;
								$cw[] = (int) $this->getSwitchEncodingCodeword($enc);
								++$cw_num;
								break;
							} //end if
							if((int)$field_length < 4) {
								// set unlatch character
								$temp_cw[] = 0x1f;
								++$field_length;
								// fill empty characters
								for($i=$field_length; $i<4; ++$i) {
									$temp_cw[] = 0;
								} //end for
								$enc = self::SEMACODE_ENCODE_ASCII;
								$this->last_enc = (int) $enc;
							} //end if
							//-- encodes four data characters in three codewords
							$tcw = (int) ((($temp_cw[0] & 0x3F) << 2) + (($temp_cw[1] & 0x30) >> 4));
							if((int)$tcw > 0) {
								$cw[] = (int) $tcw;
								$cw_num++;
							} //end if
							$tcw = (int) ((($temp_cw[1] & 0x0F) << 4) + (($temp_cw[2] & 0x3C) >> 2));
							if((int)$tcw > 0) {
								$cw[] = (int) $tcw;
								$cw_num++;
							} //end if
							$tcw = (int) ((($temp_cw[2] & 0x03) << 6) + ($temp_cw[3] & 0x3F));
							if((int)$tcw > 0) {
								$cw[] = (int) $tcw;
								$cw_num++;
							} //end if
							$temp_cw = [];
							$pos = (int) $epos;
							$field_length = 0;
							if((int)$enc == (int)self::SEMACODE_ENCODE_ASCII) {
								break; // exit from EDIFACT mode
							} //end if
						} //end if
					} while((int)$epos < (int)$data_length);
					//--
					break;
				case self::SEMACODE_ENCODE_B256: // G. While in Base 256 (B256) encodation
					//-- initialize temporary array with 0 length
					$temp_cw = [];
					$field_length = 0;
					while(((int)$pos < (int)$data_length) AND ((int)$field_length <= 1555)) {
						$newenc = (int) $this->lookAheadTest($data, $pos, $enc);
						if((int)$newenc != (int)$enc) {
							// 1. If the look-ahead test (starting at step J) indicates another mode, switch to that mode.
							$enc = (int) $newenc;
							break; // exit from B256 mode
						} else {
							// 2. Otherwise, process the next character in Base 256 encodation.
							$chr = (int) \ord((string)$data[$pos]);
							++$pos;
							$temp_cw[] = (int) $chr;
							++$field_length;
						} //end if else
					} //end while
					//-- set field length
					if((int)$field_length <= 249) {
						$cw[] = (int) $this->get255StateCodeword($field_length, ($cw_num + 1));
						++$cw_num;
					} else {
						$cw[] = (int) $this->get255StateCodeword((\floor((string)($field_length / 250)) + 249), ($cw_num + 1)); // unixman: fix floor
						$cw[] = (int) $this->get255StateCodeword(($field_length % 250), ($cw_num + 2));
						$cw_num += 2;
					} //end if else
					//--
					if(!empty($temp_cw)) {
						// add B256 field
						foreach($temp_cw as $p => $cht) {
							$cw[] = (int) $this->get255StateCodeword($cht, ($cw_num + $p + 1));
						} //end foreach
					} //end if
					//--
					break;
			} // end switch
			//--
		} // end of while
		//--
		return (array) $cw;
		//--
	} //END FUNCTION


	/**
	 * Places "chr+bit" with appropriate wrapping within array[].
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $row (int) Row number.
	 * @param $col (int) Column number.
	 * @param $chr (int) Char byte.
	 * @param $bit (int) Bit.
	 * @return array
	 * @private
	 */
	private function placeModule(array $marr, int $nrow, int $ncol, int $row, int $col, int $chr, int $bit) : array {
		//--
		if($row < 0) {
			$row += $nrow;
			$col += (4 - (($nrow + 4) % 8));
		} //end if
		//--
		if($col < 0) {
			$col += $ncol;
			$row += (4 - (($ncol + 4) % 8));
		} //end if
		//--
		$marr[(($row * $ncol) + $col)] = ((10 * $chr) + $bit);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of a utah-shaped symbol character.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $row (int) Row number.
	 * @param $col (int) Column number.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeUtah(array $marr, int $nrow, int $ncol, int $row, int $col, int $chr) : array {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-2, $col-2, $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-2, $col-1, $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col-2, $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col-1, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col,   $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row,   $col-2, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row,   $col-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row,   $col,   $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the first special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerA(array $marr, int $nrow, int $ncol, int $chr) : array {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 1,       $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 2,       $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 2,       $ncol-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 3,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the second special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerB(array $marr, int $nrow, int $ncol, int $chr) : array {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-3, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-2, 0,       $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-4, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-3, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the third special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerC(array $marr, int $nrow, int $ncol, int $chr) : array {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-3, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-2, 0,       $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 2,       $ncol-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 3,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the fourth special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerD(array $marr, int $nrow, int $ncol, int $chr) : array {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, $ncol-1, $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-3, $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-3, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-2, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Build a placement map.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @return array
	 * @private
	 */
	private function getPlacementMap(int $nrow, int $ncol) : array {
		//-- initialize array with zeros
		$marr = \array_fill(0, ($nrow * $ncol), 0);
		//-- set starting values
		$chr = 1;
		$row = 4;
		$col = 0;
		//--
		do {
			//-- repeatedly first check for one of the special corner cases, then
			if(($row == $nrow) AND ($col == 0)) {
				$marr = $this->placeCornerA($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			if(($row == ($nrow - 2)) AND ($col == 0) AND ($ncol % 4)) {
				$marr = $this->placeCornerB($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			if(($row == ($nrow - 2)) AND ($col == 0) AND (($ncol % 8) == 4)) {
				$marr = $this->placeCornerC($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			if(($row == ($nrow + 4)) AND ($col == 2) AND (!($ncol % 8))) {
				$marr = $this->placeCornerD($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			//-- sweep upward diagonally, inserting successive characters,
			do {
				if(($row < $nrow) AND ($col >= 0) AND (!$marr[(($row * $ncol) + $col)])) {
					$marr = $this->placeUtah($marr, $nrow, $ncol, $row, $col, $chr);
					++$chr;
				} //end if
				$row -= 2;
				$col += 2;
			} while (($row >= 0) AND ($col < $ncol));
			++$row;
			$col += 3;
			//-- & then sweep downward diagonally, inserting successive characters,...
			do {
				if(($row >= 0) AND ($col < $ncol) AND (!$marr[(($row * $ncol) + $col)])) {
					$marr = $this->placeUtah($marr, $nrow, $ncol, $row, $col, $chr);
					++$chr;
				} //end if
				$row += 2;
				$col -= 2;
			} while (($row < $nrow) AND ($col >= 0));
			$row += 3;
			++$col;
			//-- ... until the entire array is scanned
		} while (($row < $nrow) OR ($col < $ncol));
		//-- lastly, if the lower righthand corner is untouched, fill in fixed pattern
		if(!$marr[(($nrow * $ncol) - 1)]) {
			$marr[(($nrow * $ncol) - 1)] = 1;
			$marr[(($nrow * $ncol) - $ncol - 2)] = 1;
		} //end if
		//--
		return $marr;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
