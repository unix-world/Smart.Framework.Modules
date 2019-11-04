<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Documentor/Generate (qunit:tasks)
// Route: admin.php?page=documentor.generate
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


define('SMART_APP_MODULE_AREA', 'ADMIN'); // INDEX, ADMIN, SHARED
define('SMART_APP_MODULE_AUTH', true); // if set to TRUE requires auth always


/**
 * Admin Area Controller
 * @version 20191104
 * @ignore
 */
final class SmartAppAdminController extends SmartAbstractAppController {


	public function Run() {

		//--
		if(!defined('SMART_FRAMEWORK_DOCUMENTOR_ALLOW') OR (SMART_FRAMEWORK_DOCUMENTOR_ALLOW !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Documentor is disabled ...');
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
				'year' 				=> (string) date('Y'),
				//--
				'title' 			=> (string) 'Documentation',
				'heading-title' 	=> (string) 'PHP Documentation',
				'seo-description'	=> (string) 'Smart.Framework Documentation',
				'seo-keywords'		=> (string) 'php, smart, framework, documentor',
				'seo-summary' 		=> (string) 'Smart.Framework, a PHP / Javascript Framework for Web',
				'url-index' 		=> ''
				//--
			]);
			//-- #end sync
			$url_base = 'admin.php?page='.Smart::escape_url($this->ControllerGetParam('controller')).'&action=run';
			$this->PageViewSetVar(
				'main',
				(string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').$this->ControllerGetParam('action').'.mtpl.htm',
					[
						'URL-SF' 	=> (string) $url_base.'&area=sf&mode=multi&heading='.Smart::escape_url('Smart.Framework : PHP Documentation'),
						'URL-SFM' 	=> (string) $url_base.'&area=sfm&heading='.Smart::escape_url('Smart.Framework.Modules : PHP Documentation'),
						'URL-SFD' 	=> (string) $url_base.'&area=sfd&extra=docs&mode=multi&heading='.Smart::escape_url('Smart.Framework and Smart.Framework.Modules : PHP Documentation')
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
		$arr_sf_tasks = [
			//--
			'\\Smart',
			'\\SmartUnicode',
			'\\SmartParser',
			'\\SmartValidator',
			'\\SmartCache',
			'\\SmartAbstractPersistentCache', // dev
			'\\SmartPersistentCache',
			'\\SmartInterfaceAdapterTextTranslations', // dev
		//	'\\SmartAdapterTextTranslations', // custom
			'\\SmartTextTranslator',
			'\\SmartTextTranslations',
			'\\SmartHashCrypto',
			'\\SmartCipherCrypto',
			'\\SmartFileSysUtils',
			'\\SmartFileSystem',
			'\\SmartGetFileSystem',
			'\\SmartHttpClient',
			'\\SmartHttpUtils',
			'\\SmartMarkersTemplating',
			'\\SmartAuth',
			'\\SmartUtils',
			//--
			'\\SmartAbstractAppController', // dev
			'\\SmartAppIndexController',
			'\\SmartAppAdminController',
			//--
			'\\SmartAppInfo',
			'\\SmartComponents',
			//--
			'\\SmartPunycode',
			'\\SmartDetectImages',
			'\\SmartRobot',
			'\\SmartMailerUtils',
			'\\SmartMailerMimeDecode',
			'\\SmartMailerMimeParser',
			'\\SmartMailerSend',
			'\\SmartMailerSmtpClient',
			'\\SmartMailerImap4Client',
			'\\SmartMailerPop3Client',
			'\\SmartYamlConverter',
			'\\SmartXmlParser',
			'\\SmartXmlComposer',
			'\\SmartHtmlParser',
			'\\SmartMarkdownToHTML',
			'\\SmartArchiverLZS',
			'\\SmartImageGdProcess',
			'\\SmartBarcode1D',
			'\\SmartBarcode2D',
			'\\SmartCaptchaFormCheck',
			'\\SmartViewHtmlHelpers',
			'\\SmartHTMLCalendar',
			'\\SmartCalendarComponent',
			'\\SmartFtpClient',
			'\\SmartRedisDb',
			'\\SmartMysqliDb',
			'\\SmartMysqliExtDb',
			'\\SmartPgsqlDb',
			'\\SmartPgsqlExtDb',
			'\\SmartSQliteDb',
			'\\SmartMongoDb',
			'\\SmartAbstractCustomSession', // dev
			'\\SmartSession',
		//	'\\SmartCustomSession', // custom: require_once('lib/app/custom-session-redis.php');
			'\\SmartSpreadSheetExport',
			'\\SmartSpreadSheetImport',
			'\\SmartPdfExport',
			//--
		];
		//--
		$arr_sfm_tasks = [
			//--
			'\\SmartTemplating',
			'\\SmartModExtLib\\TplDust\\SmartDustTemplating',
			'\\SmartModExtLib\\TplTwig\\SmartTwigTemplating',
			'\\SmartModExtLib\\TplNetteLatte\\SmartNetteLatteTemplating',
			'\\SmartModExtLib\\TplTypo3Fluid\\SmartTypo3FluidTemplating',
			//--
			'\\SmartSolrDb',
			'\\SmartAbstractPgsqlExtDb', // dev
			'\\SmartModExtLib\\DbalZend\\DbalPdo',
			'\\SmartModExtLib\\DbOrmRedbean\\ORM',
			//--
			'\\SmartCurlHttpFtpClient',
			//--
			'\\SmartLangIdClient',
			'\\SmartModExtLib\\LangDetect\\LanguageNgrams',
			//--
			'\\SmartZipArchive',
			'\\SmartExportToOpenOffice',
			'\\SmartImportFromOpenOffice',
			//--
			'\\SmartModExtLib\\MediaGallery\\Manager',
			'\\SmartModExtLib\\JsComponents\\TextEditor',
			//--
			'\\SmartImgGfxCharts',
			'\\SmartImgBizCharts',
			//--
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendController',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendPlugin',
			//--
			'\\SmartModExtLib\\SmFacebook\\FacebookApi',
			'\\SmartModExtLib\\SmTwitter\\TwitterApi',
			//--
		];
		//--

		//--
		switch((string)$area) {
			case 'sf':
				$arr_sfm_tasks = []; // do not generate sfm
				break;
			case 'sfm':
				$arr_sf_tasks = []; // do not generate sf
				break;
			case 'sfd':
			default:
				// all
		} //end switch
		//--

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
		$php_min_ver = '7.1'; // {{{SYNC-DOCUMENTOR-PHP-MIN-VERSION}}}
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


//end of php code
?>