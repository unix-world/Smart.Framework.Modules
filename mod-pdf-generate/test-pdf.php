<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Test Samples
// Route: ?page=pdf-generate.test-tcpdf-1
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

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		\SmartModExtLib\PdfGenerate\PDFCreate::init();
		//--

//$start = microtime(true);

		$useFontCaching = false;

		$pdf = new \PDF\zFPDF\zFPDF((bool)$useFontCaching);
		$pdf->SetCompression(true);
		$pdf->AddPage();

		$pdf->Image('modules/mod-pdf-generate/doc/PDF/sf-logo.png', 160, 10, 32, 32);

		// Add a Unicode font (uses UTF-8)
		$pdf->AddFont('IBMPlexSans', '',   'IBMPlexSans-Regular.ttf');
		$pdf->AddFont('IBMPlexSans', 'I',  'IBMPlexSans-Italic.ttf');
		$pdf->AddFont('IBMPlexSans', 'B',  'IBMPlexSans-Bold.ttf');
		$pdf->AddFont('IBMPlexSans', 'BI', 'IBMPlexSans-BoldItalic.ttf');
		$pdf->SetFont('IBMPlexSans', '', 10);

		$pdf->SetFontStyle('BI', 11);

		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetDrawColor(194, 32, 63); // #c2203f
		$pdf->SetFillColor(237, 37, 89); // #ed2559
		$pdf->Cell(50, 10, 'Smart.PDF', 1, 2, 'C', true);
		$pdf->SetDrawColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);

		$pdf->Ln(4);

		$pdf->SetFontStyle('', 14);

		$txt = (string) SmartFileSysUtils::readStaticFile('modules/mod-pdf-generate/doc/PDF/HelloWorld.txt'); // Load a UTF-8 string from a file and print it
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

		$info = 'The file size of this PDF is only 60 KB.';
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
