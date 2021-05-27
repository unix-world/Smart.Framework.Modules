<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Test Samples
// Route: ?/page/js-components.test-archlzs (?page=js-components.test-archlzs)
// (c) 2006-2021 unix-world.org - all rights reserved

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
		$myString = (string) str_repeat('Some string to archive as LZS ('.substr((string)time(), -7, 7).'). ', 99);
		$time = microtime(true);
		$archString = \SmartModExtLib\JsComponents\ArchLzs::compressToBase64($myString); // archive the string
		$time_arch = Smart::format_number_dec((microtime(true) - $time), 9, '.', '');
		$time = microtime(true);
		$unarchString = \SmartModExtLib\JsComponents\ArchLzs::decompressFromBase64($archString); // unarchive it back
		$time_unarch = Smart::format_number_dec((microtime(true) - $time), 9, '.', '');
		if((string)$unarchString === (string)$myString) { // Test: check if unarchive is the same as archive
			$result = 'PHP ArchLzs Compress/Decompress OK.';
		} else {
			$result = 'PHP ArchLzs Compress/Decompress Failed !';
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Sample LZS Archiver',
			'main' => '<h1 id="qunit-test-result">'.Smart::escape_html($result).'</h1><h2 id="js-test-result"></h2><h3>Test Archive/Unarchive for a string with length of '.(int)strlen($myString).' bytes. The archived string has a length of '.(int)strlen($archString).' bytes. Compress Ratio is '.Smart::escape_html(Smart::format_number_dec((100 - (strlen($archString) / strlen($myString)) * 100), 2, '.', '')).'%</h3><hr><br>Archived String: `<div id="str-arch" style="font-size:11px; line-height:11px;">'.Smart::escape_html($archString).'</div>`<br>'.'<hr>'.'Original String: `<div id="str-orig" style="font-size:11px; line-height:11px;">'.Smart::escape_html($myString).'</div>`<hr><br><b>Benchmark</b>'.'<br>Archive time: '.Smart::escape_html($time_arch).' sec.'.'<br>Unarchive time: '.Smart::escape_html($time_unarch).' sec.'.
				'<script src="modules/mod-js-components/views/js/arch-lzs/arch-lzs.js"></script>'.
				'<script>(function(){'."\n".
				'var strArch = String(jQuery(\'div#str-arch\').text());'."\n".
				'var strOrig = String(jQuery(\'div#str-orig\').text());'."\n".
				'if(ArchLzs.decompressFromBase64(strArch) !== strOrig) { jQuery(\'#js-test-result\').text(\'Javascript ArchLzs Decompress FAILED !\'); return; }'."\n".
				'if(ArchLzs.compressToBase64(strOrig) !== strArch) { jQuery(\'#js-test-result\').text(\'Javascript ArchLzs Compress FAILED !\'); return; }'."\n".
				'jQuery(\'#js-test-result\').text(\'Javascript ArchLzs Compress/Decompress OK.\');'."\n".
				'})();</script>'
		]);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller
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
