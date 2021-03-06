<?php
// Class: \SmartModExtLib\Documentor\SmartClasses
// (c) 2006-2021 unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

namespace SmartModExtLib\Documentor;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//-----------------------------------------------------
if(\version_compare((string)\phpversion(), '7.3') < 0) { // {{{SYNC-DOCUMENTOR-PHP-MIN-VERSION}}}
	@\http_response_code(503);
	die(\SmartComponents::http_message_503_serviceunavailable('Service N/A: PHP 7.3 or later is required for this service'));
} //end if
if(\function_exists('\\opcache_get_status')) {
	if((string)\ini_get('opcache.save_comments') != '1') {
		@\http_response_code(503);
		die(\SmartComponents::http_message_503_serviceunavailable('Service N/A: PHP Opcache is active and Opcache.SaveComments is Disabled (and must be Enabled)'));
	} //end if
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


/**
 * Class: Documentor SmartClasses
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20210525
 * @package 	Documentor
 *
 */
final class SmartClasses {

	// ::


	const DOCGENERATOR_VERSION = '20210419';


	public static function getJavascriptSfFile() {
		//--
		return 'lib/js/framework/smart-framework.pak.js';
		//--
	} //END FUNCTION


	public static function listJavascriptSfClasses() {
		//--
		return [
			'smartJ$Utils' 				=> 'lib/js/framework/src/core_utils.js',
			'smartJ$Date' 				=> 'lib/js/framework/src/date_utils.js',
			'smartJ$Base64' 			=> 'lib/js/framework/src/crypt_utils.js?#smartJ$Base64',
			'smartJ$CryptoHash' 		=> 'lib/js/framework/src/crypt_utils.js?#smartJ$CryptoHash',
			'smartJ$CryptoBlowfish' 	=> 'lib/js/framework/src/crypt_utils.js?#smartJ$CryptoBlowfish',
			'smartJ$ModalBox' 			=> 'lib/js/framework/src/ifmodalbox.js',
			'smartJ$Browser' 			=> 'lib/js/framework/src/browser_utils.js',
			'smartJ$TestBrowser' 		=> 'lib/js/framework/src/browser_check.js'
		];
		//--
	} //END FUNCTION


	public static function listJavascriptSfmClasses() {
		//--
		return [
			'smartJ$UI' 				=> 'lib/js/jquery/jquery.smartframework.ui.js',
			'ArchLzs'					=> 'modules/mod-js-components/views/js/arch-lzs/arch-lzs.js'
		];
		//--
	} //END FUNCTION


	public static function listPhpSfClasses() {
		//--
		return [
			//--
			'\\SmartFrameworkSecurity',
			'\\SmartFrameworkRegistry',
			'\\SmartUnicode',
			'\\Smart',
			'\\SmartHashCrypto',
			'\\SmartCipherCrypto',
			'\\SmartFileSysUtils',
			'\\SmartFileSystem',
			'\\SmartGetFileSystem',
			'\\SmartHttpUtils',
			'\\SmartHttpClient',
			'\\SmartAuth',
			'\\SmartParser',
			'\\SmartValidator',
			'\\SmartUtils',
			'\\SmartCache',
			'\\SmartAbstractPersistentCache', // dev
			'\\SmartPersistentCache',
			'\\SmartMarkersTemplating',
			//--
			'\\SmartInterfaceAdapterTextTranslations', // dev
		//	'\\SmartAdapterTextTranslations', // custom
			'\\SmartTextTranslator',
			'\\SmartTextTranslations',
			'\\SmartComponents',
		//	'\\SmartFrameworkRuntime', // internal only
		//	'\\SmartDebugProfiler', // internal only
			//--
			'\\SmartAbstractAppController', // dev
			'\\SmartAppIndexController',
			'\\SmartAppAdminController',
			'\\SmartAppTaskController',
			//--
		//	'\\SmartAppBootstrap', // internal only
			'\\SmartAppInfo',
			//--
			'\\SmartPunycode',
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
			'\\SmartDetectImages',
			'\\SmartImageGdProcess',
			'\\SmartCaptcha',
			'\\SmartAsciiCaptcha',
			'\\SmartSVGCaptcha',
			'\\SmartQR2DBarcode',
			'\\SmartViewHtmlHelpers',
			'\\SmartFtpClient',
			'\\SmartRedisDb',
			'\\SmartSQliteDb',
			'\\SmartDbaUtilDb',
			'\\SmartDbaDb',
			'\\SmartMysqliDb',
			'\\SmartMysqliExtDb',
			'\\SmartPgsqlDb',
			'\\SmartPgsqlExtDb',
			'\\SmartMongoDb',
			'\\SmartAbstractCustomSession', // dev
			'\\SmartSession',
		//	'\\SmartCustomSession', // custom: require_once('lib/app/custom-session-redis.php');
			'\\SmartSpreadSheetExport',
			'\\SmartSpreadSheetImport'
			//--
		];
		//--
	} //END FUNCTION


	public static function listPhpSfmClasses() {
		//--
		return [
			//--
			'\\SmartModExtLib\\AuthAdmins\\SimpleAuthAdminsHandler',
			'\\SmartModExtLib\\AuthAdmins\\AuthAdminsHandler',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendPageBuilder',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendController',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendPlugin',
			'\\SmartModExtLib\\Webdav\\ControllerAdmDavFs',
			'\\SmartModExtLib\\Webdav\\ControllerAdmCalDavFs',
			'\\SmartModExtLib\\Webdav\\ControllerAdmCardDavFs',
			//--
			'\\SmartModExtLib\\Barcodes\\SmartBarcodes1D',
			'\\SmartModExtLib\\Barcodes\\SmartBarcodes2D',
			//--
			'\\SmartModExtLib\\Captcha\\SmartImageCaptcha',
			//--
			'\\SmartModExtLib\\HighlightSyntax\\Highlighter',
			//--
			'\\SmartTemplating',
			'\\SmartModExtLib\\TplDust\\SmartDustTemplating',
			'\\SmartModExtLib\\TplTwig\\SmartTwigTemplating',
			'\\SmartModExtLib\\TplTypo3Fluid\\SmartTypo3FluidTemplating',
			//--
			'\\SmartSolrDb',
			'\\SmartAbstractPgsqlExtDb', // dev
			'\\SmartModExtLib\\DbalZend\\DbalPdo',
			'\\SmartModExtLib\\DbOrmRedbean\\ORM',
			//--
			'\\SmartCurlHttpFtpClient',
			'\\SmartModExtLib\\Soap\\SoapServerRequestHandler',
			//--
			'\\SmartLangIdClient',
			'\\SmartModExtLib\\LangDetect\\LanguageNgrams',
			//--
			'\\SmartHTMLCalendar',
			'\\SmartCalendarComponent',
			'\\SmartZipArchive',
			'\\SmartExportToOpenOffice',
			'\\SmartImportFromOpenOffice',
			//--
			'\\SmartModExtLib\\MediaGallery\\Manager',
			'\\SmartModExtLib\\JsComponents\\TextEditor',
			'\\SmartModExtLib\\JsComponents\\ArchLzs',
			//--
			'\\SmartModExtLib\\PdfGenerate\\PdfUtils',
			'\\SmartModExtLib\\PdfGenerate\\HtmlToPdfExport',
			'\\SmartModExtLib\\PdfGenerate\\HtmlUrlToPdfExport',
			//--
			'\\SmartImgGfxCharts',
			'\\SmartImgBizCharts',
			//--
			'\\SmartModExtLib\\SmFacebook\\FacebookApi',
			'\\SmartModExtLib\\SmTwitter\\TwitterApi',
			//--
		];
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
