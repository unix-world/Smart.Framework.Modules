<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: PdfCreate Demo
// Route: ?page=pdf-create.test&altfont=no|yes&cache=no|yes
// (c) 2026-present unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, TASK, SHARED


/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	// r.20260728

	private const SF_URL = 'http://demo.unix-world.org/smart-framework/';
	private const BARCODE_CACHE_TIME = -1; // -1 | 3600


	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!\SmartAppInfo::TestIfModuleExists('mod-barcodes')) {
			$this->PageViewSetErrorStatus(503, 'Mod Barcodes is missing !');
			return false;
		} //end if
		//--

		$paramUseAltFont = (string) trim((string)$this->RequestVarGet('altfont', '', [ '', 'yes' ]));
		$paramUseCaching = (string) trim((string)$this->RequestVarGet('cache', '', [ '', 'yes' ]));

	//$start = microtime(true);

		$useAlternateDefaultFont = false;
		if($paramUseAltFont === 'yes') {
			$useAlternateDefaultFont = true;
		} //end if

		$useFontCaching = false;
		if($paramUseCaching === 'yes') {
			$useFontCaching = true;
		} //end if


	//	\SmartModExtLib\PdfCreate\Module::init(); // no need, will be called by the newPdf method below
		$pdf = \SmartModExtLib\PdfCreate\Module::newPdf(true, (bool)$useAlternateDefaultFont, (bool)$useFontCaching);
		$pdf->SetCompression(true);

	//	$pdf->AliasNbPages(); // if AliasNbPages is used in the header() protected method this needs to be initialized before set default font
		$pdf->AddDefaultFontSet(); // Add a Unicode font (uses UTF-8) ; call before first AddPage() because header() needs a font !
		$pdf->AddPage();
		$pdf->SetDefaultFontSet(); // call only after AddPage() needs to write to out()

		$pdf->Image('modules/mod-pdf-create/doc/test-files/sf-logo.png', 160, 10, 32, 32);
	//	$pdf->Image('modules/mod-pdf-create/doc/test-files/sf-logo.gif', 160, 10, 32, 32);
	//	$pdf->Image('modules/mod-pdf-create/doc/test-files/sf-logo.jpg', 160, 10, 32, 32);


		$pdf->SetFontStyle('BI', 11);

		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetDrawColor(194, 32, 63); // #c2203f
		$pdf->SetFillColor(237, 37, 89); // #ed2559
		$pdf->Cell(50, 10, 'Smart.PDF', 1, 2, 'C', true);
		$pdf->SetDrawColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);

		$pdf->Ln(4);

		$pdf->SetFontStyle('', 14);

		$txt = (string) SmartFileSysUtils::readStaticFile('modules/mod-pdf-create/doc/test-files/HelloWorld.txt'); // Load a UTF-8 string from a file and print it
		$pdf->Write(8, $txt);

		$pdf->Ln(10);

		$border = 1;

		$pdf->SetFontStyle('I', 9);
		$pdf->Ln(2);
		$pdf->MultiCell(150, 4, $txt, $border);

		$pdf->SetFontStyle('B', 9);
		$pdf->Ln(2);
		$pdf->MultiCell(150, 4, $txt, 0);

		$pdf->SetFontStyle('BI', 9);
		$pdf->Ln(2);
		$pdf->MultiCell(150, 4, $txt, 'TB');

		$info = 'The file size of this PDF is only 64 KB.';
		$size = 10;
		$ln = 6;

		$pdf->AddPage();


		//--
		$header = [ 'UpperCase', 'LowerCase', 'Character Type', 'Language' ];
		$data = [
			[ 'Ă', 'ă', 'special',  'RO' ],
			[ 'Â', 'â', 'special',  'RO' ],
			[ 'Î', 'î', 'special',  'RO' ],
			[ 'Ș', 'ș', 'special',  'RO' ],
			[ 'Ț', 'ț', 'special',  'RO' ],
			[ '€', '¢', 'currency', '-'  ],
		];
		//-- Colors, line width and bold font
		$pdf->SetDrawColor(194, 32, 63); // #c2203f
		$pdf->SetFillColor(237, 37, 89); // #ed2559
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetLineWidth(0.3);
		$pdf->SetFontStyle('B');
		//-- Header
		$w = [ 45, 45, 60, 40 ];
		for($i=0;$i<count($header);$i++) {
			$pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
		} //end for
		$pdf->Ln();
		//-- Color and font restoration
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFontStyle('');
		// Data
		$fill = false;
		foreach($data as $k => $row) {
			$pdf->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
			$pdf->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
			$pdf->SetFontStyle('I');
			$pdf->Cell($w[2], 6, $row[2], 'LR', 0, 'C', $fill);
			$pdf->SetFontStyle('BI');
			$pdf->Cell($w[3], 6, $row[3], 'LR', 0, 'R', $fill);
			$pdf->SetFontStyle('');
			$pdf->Ln();
			$fill = !$fill;
		}
		// Closing line
		$pdf->Cell(array_sum($w), 0, '', 'T');


		$pdf->Ln(25);

		$pdf->SetFontStyle('', $size);
		$pdf->Ln($ln);
		$pdf->Write(5, $info);

		$pdf->SetFontStyle('I', $size);
		$pdf->Ln($ln);
		$pdf->Write(5, $info);

		$pdf->SetFontStyle('B', $size);
		$pdf->Ln($ln);
		$pdf->Write(5, $info);

		$pdf->SetFontStyle('BI', $size);
		$pdf->Ln($ln);
		$pdf->Write(5, $info);

		$pdf->SetDrawColor(189, 200, 200); // for BarCode Line Borders

		$barcode1DTxt = (string) strtoupper((string)SmartHashCrypto::crc32b((string)self::SF_URL, true));
		$barcode1DType = 'RMS';
		$barcode1DJson = (string) \SmartModExtLib\Barcodes\SmartBarcodes1D::getBarcode((string)$barcode1DTxt, (string)$barcode1DType, 'json', null, null, null, false, (int)self::BARCODE_CACHE_TIME);
		if((string)trim((string)$barcode1DJson) == '') {
			$this->PageViewSetErrorStatus(500, 'ERROR: The 1D Barcode Failed (1) ...');
			return;
		} //end if
		$barcode1DArr = Smart::json_decode((string)$barcode1DJson, true, 3);
		if((int)Smart::array_size($barcode1DArr) <= 0) {
			$this->PageViewSetErrorStatus(500, 'ERROR: The 1D Barcode Failed (2) ...');
			return;
		} //end if
	//	print_r($barcode1DArr); die();
		$pdf->SetFontStyle('B', 10);
		$pdf->SetTextColor(77, 88, 88);
		$pdf->Text(10, 142, (string)strtoupper((string)$barcode1DType).': '.$barcode1DTxt);
		$pdf->DrawBarcode1D((array)$barcode1DArr, 10, 145, 1, 8, [189, 200, 200]);

		/*
		$barcode2DType = 'pdf417';
		$barcode2DOpts = 3;
		$barcode2DPtSz = 0.5;
		*/
		$barcode2DType = 'aztec';
		$barcode2DOpts = null;
		$barcode2DPtSz = 1;
		$barcode2DJson = (string) \SmartModExtLib\Barcodes\SmartBarcodes2D::getBarcode((string)self::SF_URL, (string)$barcode2DType, 'json', null, null, $barcode2DOpts, (int)self::BARCODE_CACHE_TIME);
		if((string)trim((string)$barcode2DJson) == '') {
			$this->PageViewSetErrorStatus(500, 'ERROR: The 2D Barcode Failed (1) ...');
			return;
		} //end if
		$barcode2DArr = Smart::json_decode((string)$barcode2DJson, true, 3);
		if((int)Smart::array_size($barcode2DArr) <= 0) {
			$this->PageViewSetErrorStatus(500, 'ERROR: The 1D Barcode Failed (2) ...');
			return;
		} //end if
	//	print_r($barcode2DArr); die();
		$pdf->SetFontStyle('B', 10);
		$pdf->SetTextColor(77, 88, 88);
		$pdf->Text(10, 202, (string)strtoupper((string)$barcode2DType).': '.self::SF_URL);
		$pdf->DrawBarcode2D((array)$barcode2DArr, 10, 205, $barcode2DPtSz, [189, 200, 200], 'F');

		$pdf->Ln();
		$pdf->SetTextColor(237, 37, 89); // #ed2559
		$pdf->SetFont($pdf->GetDefaultIconicFontName(), '', 128);
		$pdf->Text(140, 110, "\u{e277}");


		$pdf->Close();

	/*
	$end = microtime(true) - $start;
	die((string)$end);
	*/

		//--
		$this->PageViewSetCfg('rawpage', true);
		$this->PageViewSetCfg('rawmime', 'application/pdf');
		$this->PageViewSetCfg('rawdisp', 'inline; filename="doc.pdf"');
		//--
		$this->PageViewSetVar('main', (string)$pdf->Output());
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php

} //END CLASS


/**
 * Task Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppTaskController extends SmartAppAdminController {

	// this will clone the SmartAppIndexController to run exactly the same action in task.php

} //END CLASS


// end of php code
