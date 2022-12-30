<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: ValidateSvg/TaskValidateSvgs (manual:task)
// Route: task.php?page=validate-svg.task-validate-svgs
// (c) 2006-2022 unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


define('SMART_APP_MODULE_AREA', 'TASK'); // INDEX, ADMIN, TASK, SHARED
define('SMART_APP_MODULE_AUTH', true); // if set to TRUE requires auth always


/**
 * Task Area Controller
 * @version 20221219
 * @ignore
 *
 * @requires define('SMART_TESTUNIT_XML_DTD_SVG_URL', 'modules/mod-validate-svg/dtd/svg11/svg11.dtd');
 */
final class SmartAppTaskController extends SmartAbstractAppController {


	public function Initialize() {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->PageViewSetErrorStatus(500, ' # Mod AuthAdmins is missing !');
			return false;
		} //end if
		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-auth-admins/templates/');
		$this->PageViewSetCfg('template-file', 'template-simple.htm');
		//--
		return true;
		//--
	} //END FUNCTION


	public function Run() {

		//--
		if(!defined('SMART_TESTUNIT_XML_DTD_SVG_URL')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: SVG DTD Validator is Not Enabled ...');
			return;
		} //end if
		//--
		if(((string)trim((string)SMART_TESTUNIT_XML_DTD_SVG_URL) == '') OR (!SmartFileSysUtils::checkIfSafePath((string)SMART_TESTUNIT_XML_DTD_SVG_URL))) {
			$this->PageViewSetErrorStatus(500, 'ERROR: SVG DTD Validator Path is unsafe ...');
			return;
		} //end if
		//--
		if(substr((string)SMART_TESTUNIT_XML_DTD_SVG_URL, -9, 9) != 'svg11.dtd') {
			$this->PageViewSetErrorStatus(500, 'ERROR: SVG DTD Validator Path must end with `svg11.dtd` ...');
			return;
		} //end if
		//--
		if(!SmartFileSystem::is_type_file((string)SMART_TESTUNIT_XML_DTD_SVG_URL)) {
			$this->PageViewSetErrorStatus(500, 'ERROR: SVG DTD Validator Path does not exists or is not a file ...');
			return;
		} //end if
		//--
		if(!class_exists('DOMDocument')) { // explicit require this for XML Validation ... tidy has nothing to do here !
			$this->PageViewSetErrorStatus(503, 'ERROR: DOMDocument PHP extension is missing ...');
			return;
		} //end if
		//--

		//--
		$files_n_dirs = array();
		//--
		$svg_errs = [];
		$svg_num = 0;
		//--
		$folders = [ 'etc', 'lib', 'modules' ];
		//--
		for($f=0; $f<Smart::array_size($folders); $f++) {
			//--
			$files_n_dirs = (array) (new \SmartGetFileSystem(true))->search_files(true, (string)$folders[$f], false, '.svg', 0);
			for($i=0; $i<\Smart::array_size($files_n_dirs['list-files']); $i++) {
				$svg_num++;
				$tmp_svg_err = (string) $this->validateSvg(SmartFileSystem::read((string)$files_n_dirs['list-files'][$i]));
				if((string)trim((string)$tmp_svg_err) != '') {
					$svg_errs[] = [ 'file' => (string)$files_n_dirs['list-files'][$i], 'err' => $tmp_svg_err ];
				} //end if
			} //end for
			$files_n_dirs = array();
			//--
		} //end for
		//--
		$files_n_dirs = array();
		//--

		//--
		$this->PageViewSetVars([
			'title' 	=> 'Validate SVGs',
			'main' 		=> '<h1>Validate all SVGs in: '.Smart::escape_html((string)implode(', ', $folders)).'</h1>'.
							'<h2>DTD Path: '.Smart::escape_html((string)SMART_TESTUNIT_XML_DTD_SVG_URL).'</h2>'.
							'<h3>Total Processed SVGs: '.(int)$svg_num.'</h3>'.
							'<h4>Total Errors: '.(int)Smart::array_size($svg_errs).'</h4>'.
							'<hr><div><b>ERRORS:<br>'.
							'<pre style="background:#ECECEC; color:#333333; border:1px solid #CCCCCC; padding:10px;">'.Smart::escape_html(SmartUtils::pretty_print_var($svg_errs)).'</pre>'.
							'</div>'
		]);
		//--

	} //END FUNCTION


	private function validateSvg($xmlStr) {
		//--
		if(!defined('SMART_TESTUNIT_XML_DTD_SVG_URL')) {
			return 'SVG Validation Failed, SMART_TESTUNIT_XML_DTD_SVG_URL is not defined ...';
		} //end if
		//--
		if((string)trim((string)$xmlStr) == '') {
			return 'SVG XML String is Empty';
		} //end if
		//--
		if(stripos((string)$xmlStr, '<!-- Smart.Framework.SVG-Validate:SKIP -->') !== false) {
			return ''; // OK, Skip
		} //end if
		//--
		if(stripos((string)$xmlStr, '?xml') !== false) {
			return 'SVG contains the XML Definition Tag !';
		//	$xmlStr = (string) trim((string)preg_replace('#<\?xml (.*?)>#si', '', (string)$xmlStr));
		} //end if
		if(stripos((string)$xmlStr, '!DOCTYPE') !== false) {
			return 'SVG contains the DocType Definition Tag !';
		//	$xmlStr = (string) trim((string)preg_replace('#<\!DOCTYPE (.*?)>#si', '', (string)$xmlStr));
		} //end if
		$xmlStr = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "'.SMART_TESTUNIT_XML_DTD_SVG_URL.'">'."\n".$xmlStr;
		//--
		@libxml_use_internal_errors(true);
		@libxml_clear_errors();
		//--
		$dom = new DOMDocument('1.0', (string)SMART_FRAMEWORK_CHARSET);
		$dom->encoding = (string) SMART_FRAMEWORK_CHARSET;
		$dom->strictErrorChecking = false; 							// do not throw errors
		$dom->preserveWhiteSpace = false; 							// remove or not redundant white space
		$dom->formatOutput = false; 								// try to format pretty-print the code (will work just partial as the preserve white space is true ...)
		$dom->resolveExternals = false; 							// disable load external entities from a doctype declaration
		$dom->validateOnParse = true; 								// this must be explicit disabled as if set to true it may try to download the DTD and after to validate (insecure ...)
		@$dom->loadXML(
			(string) $xmlStr, // need to fix just xml header
			LIBXML_ERR_WARNING | LIBXML_PARSEHUGE | LIBXML_BIGLINES | LIBXML_NOCDATA // {{{SYNC-LIBXML-OPTIONS}}} ; Fix: LIBXML_NOCDATA converts all CDATA to String
		);
		$errors = (array) @libxml_get_errors();
		$notice_log = '';
		if(Smart::array_size($errors) > 0) {
			foreach($errors as $z => $error) {
				if(is_object($error)) {
					$notice_log .= 'FORMAT-ERROR: ['.$error->code.'] / Level: '.$error->level.' / Line: '.$error->line.' / Column: '.$error->column.' / Message: '.trim((string)$error->message)."\n";
				} //end if
			} //end foreach
		} //end if
		//--
		@libxml_clear_errors();
		@libxml_use_internal_errors(false);
		//--
		return (string) $notice_log;
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
