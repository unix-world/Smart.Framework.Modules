<?php
// Controller: Agile, DbModelImport
// Route: admin.php?page=agile.dbmodel-import
// Author: unix-world.org
// r.181120

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN');
define('SMART_APP_MODULE_AUTH', true);

class SmartAppAdminController extends SmartAbstractAppController {


	public function Run() {

		$mode = (string) $this->RequestVarGet('mode', '', 'string');

		if((string)$mode == 'import') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			$this->PageViewSetCfg('rawmime', 'application/json');
			$this->PageViewSetCfg('rawdisp', 'inline');
			//--
			$frm = (array) $this->RequestVarGet('frm', [], 'array');
			//--
			$xml = '';
			$err = '';
			try {
				if((string)$frm['type'] == 'postgresql') {
					$xml = (string) \SmartModExtLib\Agile\DBModelImport::SmartDbModelerPgsqlExportToXml(
						(string) $frm['host'],
						(int)    $frm['port'],
						(string) $frm['db'],
						(string) $frm['user'],
						(string) $frm['pass'],
						(string) $frm['schema']
					);
				} elseif((string)$frm['type'] == 'mysql') {
					$xml = (string) \SmartModExtLib\Agile\DBModelImport::SmartDbModelerMySQLExportToXml(
						(string) $frm['host'],
						(int)    $frm['port'],
						(string) $frm['db'],
						(string) $frm['user'],
						(string) $frm['pass']
					);
				} else {
					$err = 'Invalid Server Type: '.$frm['type'];
				} //end if else
				if($xml) {
					$xml = (string) (new SmartXmlParser())->format((string)$xml);
				} //end if
			} catch(Exception $e) {
				$err = (string) $e->getMessage();
			} //end if
			//--
			$this->PageViewSetVar(
				'main',
				SmartComponents::js_ajax_replyto_html_form(
					(!$err) ? 'OK' : 'ERROR',
					'Import DbModel',
					(!$err) ? 'DbModel Imported Successfuly' : 'Cannot import the DbModel: '.$err,
					'',
					'the-xml',
					(string) Smart::escape_html((string)$xml),
					'SmartJS_Custom_Syntax_Highlight(\'div\');'
				)
			);
			//--
			return;
			//--
		} //end if

		$this->PageViewSetVars([
			'title' 	=> 'Agile :: DbModeler / Import from DB',
			'main' 			=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-path').'views/dbmodel-import.htm', // the view
				[
					'HTML-JS-HIGHLIGHT' => SmartComponents::js_code_highlightsyntax('', ['web'])
				]
			)
		]);

	} // END FUNCTION


} // END CLASS

// end of php code
?>