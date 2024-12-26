<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Documentor/Generate (qunit:tasks)
// Route: task.php?page=documentor.generate
// (c) 2008-present unix-world.org - all rights reserved
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
 * @version 20241216
 * @ignore
 *
 * @requires define('SMART_FRAMEWORK_DOCUMENTOR_GENERATE_ALLOW', true);
 */
final class SmartAppTaskController extends SmartAbstractAppController {


	public function Run() {

		//--
		if(!defined('SMART_FRAMEWORK_DOCUMENTOR_GENERATE_ALLOW') OR (SMART_FRAMEWORK_DOCUMENTOR_GENERATE_ALLOW !== true)) {
			$this->PageViewSetErrorStatus(403, 'INFO: The access to this Mod Documentor Area is disabled. Read the module documentation how to enable it ...');
			return;
		} //end if
		//--

		//--
		if(defined('SMART_HTML_CLEANER_USE_VALIDATOR')) {
			$this->PageViewSetErrorStatus(503, 'ERROR: a constant has been already defined and should not: `SMART_HTML_CLEANER_USE_VALIDATOR` ...');
			return;
		} //end if
		//--

		//--
		if((!class_exists('DOMDocument')) AND (!class_exists('tidy'))) { // req. for HTML Cleaner Safety
			$this->PageViewSetErrorStatus(500, 'ERROR: At least one of: tidy or DOMDocument PHP extensions is required ...');
			return;
		} //end if
		//--

		//--
		if($this->IfDebug()) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Documentor cannot be used when Debug is ON ...'); // QUnit cannot operate with Debug ON
			return;
		} //end if
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-qunit')) {
			$this->PageViewSetErrorStatus(500, 'ERROR: mod-qunit is required and not found ...');
			return;
		} //end if
		//--

		//--
		$ver = (string) \SmartModExtLib\Documentor\SmartClasses::DOCGENERATOR_VERSION; // required to pre-load the class + checks
		//--

		//--
		$action = $this->RequestVarGet('action', '', 'string');
		$area = $this->RequestVarGet('area', '', 'string');
		$heading = $this->RequestVarGet('heading', '', 'string');
		//--

		if((string)$action == '') {
			//-- {{{SYNC-DOCUMENTOR-TPL}}}
			$this->PageViewSetCfg('template-path', '@'); // set template path to this module
			$this->PageViewSetCfg('template-file', 'template-documentor.htm'); // the default template
			//--
			$this->PageViewSetVars([
				//--
				'fonts-path' 		=> (string) $this->ControllerGetParam('module-path').'fonts/',
				'logo-img' 			=> (string) 'lib/framework/img/sf-logo.svg',
				'lang-img' 			=> (string) '',
				'year' 				=> (string) date('Y'),
				//--
				'title' 			=> (string) 'Documentation Generator for Smart.Framework and Smart.Framework.Modules',
				'heading-title' 	=> (string) 'Documentation Generator',
				'seo-description'	=> (string) 'Smart.Framework Documentation',
				'seo-keywords'		=> (string) 'php, smart, framework, documentor',
				'seo-summary' 		=> (string) 'Smart.Framework, a PHP / Javascript Framework for Web',
				'url-index' 		=> ''
				//--
			]);
			//-- #end sync
			$url_base = 'task.php?page='.Smart::escape_url($this->ControllerGetParam('controller')).'&action=run';
			$this->PageViewSetVar(
				'main',
				(string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').$this->ControllerGetParam('action').'.mtpl.htm',
					[
						//--
						'URL-PHP-SF' 	=> (string) $url_base.'&area=php-sf&mode=multi&heading='.Smart::escape_url('Smart.Framework : PHP Documentation'),
						'URL-PHP-SFM' 	=> (string) $url_base.'&area=php-sfm&heading='.Smart::escape_url('Smart.Framework.Modules : PHP Documentation'),
						'URL-PHP-SFD' 	=> (string) $url_base.'&area=php-sfd&extra=docs&mode=multi&heading='.Smart::escape_url('Smart.Framework and Smart.Framework.Modules : PHP Documentation'),
						//--
						'URL-JS-SF' 	=> (string) $url_base.'&area=js-sf&mode=multi&heading='.Smart::escape_url('Smart.Framework : Javascript Documentation'),
						'URL-JS-SFM' 	=> (string) $url_base.'&area=js-sfm&heading='.Smart::escape_url('Smart.Framework.Modules : Javascript Documentation'),
						'URL-JS-SFD' 	=> (string) $url_base.'&area=js-sfd&extra=docs&mode=multi&heading='.Smart::escape_url('Smart.Framework and Smart.Framework.Modules : Javascript Documentation'),
						//--
					]
				)
			);
			//--
			return;
			//--
		} //end if
		//--

		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-qunit/templates/'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-qunit.htm'); // the default template
		//--

		//--
		$the_page = '';
		//--
		switch((string)$area) {
			//--
			case 'php-sf':
				$the_page = 'documentor.doc';
				$arr_sf_tasks = (array) \SmartModExtLib\Documentor\SmartClasses::listPhpSfClasses();
				$arr_sfm_tasks = []; // do not generate php-sfm
				break;
			case 'php-sfm':
				$the_page = 'documentor.doc';
				$arr_sf_tasks = []; // do not generate php-sf
				$arr_sfm_tasks = (array) \SmartModExtLib\Documentor\SmartClasses::listPhpSfmClasses();
				break;
			case 'php-sfd':
				$the_page = 'documentor.doc';
				$arr_sf_tasks = (array) \SmartModExtLib\Documentor\SmartClasses::listPhpSfClasses();
				$arr_sfm_tasks = (array) \SmartModExtLib\Documentor\SmartClasses::listPhpSfmClasses();
				break;
			//--
			case 'js-sf':
				$the_page = 'documentor.docjs';
				$arr_sf_tasks = (array) array_flip((array)\SmartModExtLib\Documentor\SmartClasses::listJavascriptSfClasses());
				$arr_sfm_tasks = []; // do not generate php-sfm
				break;
			case 'js-sfm':
				$the_page = 'documentor.docjs';
				$arr_sf_tasks = []; // do not generate php-sf
				$arr_sfm_tasks = (array) array_flip((array)\SmartModExtLib\Documentor\SmartClasses::listJavascriptSfmClasses());
				break;
			case 'js-sfd':
				$the_page = 'documentor.docjs';
				$arr_sf_tasks = (array) array_flip((array)\SmartModExtLib\Documentor\SmartClasses::listJavascriptSfClasses());
				$arr_sfm_tasks = (array) array_flip((array)\SmartModExtLib\Documentor\SmartClasses::listJavascriptSfmClasses());
				break;
			//--
			default:
				return 400; // bad request
			//--
		} //end switch
		//--
		$arr_pre_tasks = [
			'cleanup@documentation'
		];
		//--
		$arr_post_tasks = [
			'index@packages'
		];
		//--

		//--
		$mode = $this->RequestVarGet('mode', '', 'string');
		$extra = $this->RequestVarGet('extra', '', 'string');
		//--
		if((string)$mode != 'multi') { // {{{SYNC-DOCUMENTOR-SAVE-MODE}}}
			$mode = '';
		} //end if
		//--
		$php_v_ok = 'OK:PHP-VERSION';
		$php_min_ver = '7.3'; // {{{SYNC-DOCUMENTOR-PHP-MIN-VERSION}}}
		//--
		$this->PageViewSetVars([
			'title' 	=> 'QUnit Engine :: Generate Docs'.($area ? ' ('.$area.')' : '').($mode ? ' :: M='.$mode : '').($extra ? ' :: E='.$extra : ''),
			'semaphore' => 'Smart.Framework '.SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION.(defined('SMART_APP_MODULES_EXTRALIBS_VER') ? ' '.SMART_APP_MODULES_EXTRALIBS_VER : ''),
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').$this->ControllerGetParam('action').'.mtpl.js',
				[
					//--
					'CHARSET' 				=> (string) $this->ControllerGetParam('charset'),
					'PHP-VERSION' 			=> (string) phpversion(),
					'PHP-MIN-VERSION' 		=> (string) $php_min_ver,
					'PHP-COMPARE-VERSIONS' 	=> (string) (version_compare((string)phpversion(), (string)$php_min_ver) < 0) ? 'Service N/A: PHP '.$php_min_ver.' or later is required for this service' : (string)$php_v_ok,
					'PHP-OK-VERSION' 		=> (string) $php_v_ok,
					'SF-VERSION' 			=> (string) SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION,
					'APP-REALM' 			=> (string) $this->ControllerGetParam('app-realm'),
					'DEBUG-MODE' 			=> (string) ($this->IfDebug() ? 'yes' : 'no'),
					'LANG' 					=> (string) $this->ControllerGetParam('lang'),
					'MODULE-PATH' 			=> (string) $this->ControllerGetParam('module-path'),
					'SRV-SCRIPT' 			=> (string) $this->ControllerGetParam('url-script'),
					//--
					'PAGE' 					=> (string) $the_page,
					//--
					'PRE-TASKS' 			=> (array)  $arr_pre_tasks,
					'POST-TASKS' 			=> (array)  $arr_post_tasks,
					//--
					'MODE' 					=> (string) $mode,
					'EXTRA' 				=> (string) $extra,
					'HEADING' 				=> (string) $heading,
					'TASKS' 				=> (array)  array_merge((array)$arr_sf_tasks, (array)$arr_sfm_tasks),
					//--
				]
			)
		]);
		//--

	} //END FUNCTION


} //END CLASS


// end of php code
