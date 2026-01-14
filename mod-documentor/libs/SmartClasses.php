<?php
// Class: \SmartModExtLib\Documentor\SmartClasses
// (c) 2008-present unix-world.org - all rights reserved
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
 * @version 	v.20260114
 * @package 	Documentor
 *
 */
final class SmartClasses {

	// ::


	const DOCGENERATOR_VERSION = '20260113';


	public static function getJavascriptSfFile() {
		//--
		return 'lib/js/framework/smart-framework.pak.js';
		//--
	} //END FUNCTION


	public static function listJavascriptSfClasses() {
		//--
		return [
			'smartJ$Utils' 					=> 'lib/js/framework/src/core_utils.js',
			'smartJ$Date' 					=> 'lib/js/framework/src/date_utils.js',
		//	'smartJ$BaseConv' 				=> 'lib/js/framework/src/crypt_utils.js?#smartJ$BaseConv',
			'smartJ$CryptoHash' 			=> 'lib/js/framework/src/crypt_utils.js?#smartJ$CryptoHash',
			'smartJ$CipherCrypto' 			=> 'lib/js/framework/src/crypt_utils.js?#smartJ$CipherCrypto',
		//	'smartJ$CryptoCipherTwofish' 	=> 'lib/js/framework/src/crypt_utils.js?#smartJ$CryptoCipherTwofish',
		//	'smartJ$CryptoCipherBlowfish' 	=> 'lib/js/framework/src/crypt_utils.js?#smartJ$CryptoCipherBlowfish',
		//	'smartJ$DhKx' 					=> 'lib/js/framework/src/crypt_utils.js?#smartJ$DhKx',
			'smartJ$ModalBox' 				=> 'lib/js/framework/src/ifmodalbox.js',
			'smartJ$Browser' 				=> 'lib/js/framework/src/browser_utils.js',
		//	'smartJ$TestBrowser' 			=> 'lib/js/framework/src/browser_check.js'
		];
		//--
	} //END FUNCTION


	public static function listJavascriptSfmClasses() {
		//--
		return [
			'smartJ$UI' 				=> 'modules/mod-auth-admins/views/js/jquery.smartframework.ui.js',
			'ArchLzs'					=> 'modules/mod-js-components/views/js/arch-lzs/arch-lzs.js'
		];
		//--
	} //END FUNCTION


	public static function listPhpSfClasses() {
		//--
		return [ // {{{SYNC-SMART-FRAMEWORK-LIBS-ORDER}}}
			//-- framework
			'\\SmartUnicode',
			'\\SmartEnvironment',
			'\\SmartFrameworkSecurity',
			'\\Smart',
			'\\SmartFileSysUtils',
			'\\SmartCache',
			'\\SmartAbstractPersistentCache', // dev
			'\\SmartPersistentCache',
			'\\SmartHashCrypto',
		//	'\\SmartHashPoly1305',
			'\\SmartCipherCrypto',
		//	'\\SmartCryptoCiphersHashCryptOFB',
		//	'\\SmartCryptoCiphersBlowfishCBC',
		//	'\\SmartCryptoCiphersTwofishCBC',
		//	'\\SmartCryptoCiphersThreefishCBC',
		//	'\\SmartCryptoCiphersOpenSSL',
		//	'\\SmartDhKx',
			'\\SmartCsrf',
			'\\SmartCryptoEddsaSodium',
			'\\SmartCryptoEcdsaOpenSSL',
		//	'\\SmartCryptoEcdsaAsn1Sig',
			'\\SmartMarkersTemplating',
			'\\SmartValidator',
			'\\SmartParser',
			'\\SmartHttpUtils',
			'\\SmartHttpClient',
			'\\SmartAuth',
			//-- framework plugins
			'\\SmartYamlConverter',
		//	'\\SmartDomUtils',
			'\\SmartXmlParser',
			'\\SmartXmlComposer',
			'\\SmartHtmlParser',
			'\\SmartMarkdownToHTML',
			'\\SmartPunycode',
			'\\SmartDetectImages',
			'\\SmartMailerSmtpClient',
			'\\SmartMailerSend',
			'\\SmartMailerImap4Client',
			'\\SmartMailerPop3Client',
		//	'\\SmartMailerNotes',
		//	'\\SmartMailerMimeExtract',
			'\\SmartMailerMimeDecode',
			'\\SmartRedisDb',
			'\\SmartMongoDb',
			'\\SmartPgsqlDb',
			'\\SmartPgsqlExtDb',
			'\\SmartMysqliDb',
			'\\SmartMysqliExtDb',
		//	'\\SmartRedisPersistentCache',
		//	'\\SmartMongoDbPersistentCache',
			'\\SmartSpreadSheetExport',
			'\\SmartSpreadSheetImport',
			'\\SmartQR2DBarcode',
			'\\SmartAsciiCaptcha',
			'\\SmartImageGdProcess',
			//-- registry
			'\\SmartFrameworkRegistry',
			//-- core file sys
			'\\SmartFileSystem',
			'\\SmartGetFileSystem',
			//-- core utils
			'\\SmartUtils',
			//-- translations
			'\\SmartInterfaceAdapterTextTranslations', // dev
		//	'\\SmartAdapterTextTranslations', // custom
			'\\SmartTextTranslator',
			'\\SmartTextTranslations',
			//-- components
			'\\SmartComponents',
			//-- runtime
		//	'\\SmartFrameworkRuntime', // internal only
			//-- profiler
		//	'\\SmartDebugProfiler', // internal only
			//-- controller
			'\\SmartAbstractAppController', // dev
			'\\SmartAppIndexController',
			'\\SmartAppAdminController',
			'\\SmartAppTaskController',
			//-- app management
		//	'\\SmartAppBootstrap', // internal only
			'\\SmartAppInfo',
			//-- app plugins
			'\\SmartRobot',
			'\\SmartMailerMimeParser',
			'\\SmartMailerUtils',
			'\\SmartDbaUtilDb',
			'\\SmartDbaDb',
		//	'\\SmartSQliteFunctions',
		//	'\\SmartSQliteUtilDb',
			'\\SmartSQliteDb',
		//	'\\SmartDbaPersistentCache',
		//	'\\SmartSQlitePersistentCache',
			'\\SmartAbstractCustomSession', // dev
			'\\SmartSession',
		//	'\\SmartCustomSession', // custom: require_once('lib/app/custom-session-redis.php');
			'\\SmartSVGCaptcha',
			'\\SmartCaptcha',
			'\\SmartViewHtmlHelpers',
			//--
		];
		//--
	} //END FUNCTION


	public static function listPhpSfmClasses() {
		//--
		return [
			//--
			'\\SmartModExtLib\\AuthAdmins\\SmartAuthAdminsHandler',
			'\\SmartModExtLib\\AuthAdmins\\SmartAdmViewHtmlHelpers',
			//--
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendPageBuilder',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendController',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendPlugin',
			//--
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
			'\\SmartModExtLib\\TplTwist\\SmartTwistTemplating',
			'\\SmartModExtLib\\TplTwig\\SmartTwigTemplating',
			'\\SmartModExtLib\\TplTypo3Fluid\\SmartTypo3FluidTemplating',
			//--
			'\\SmartPMarkdownToHTML',
			//--
			'\\SmartSolrDb',
			'\\SmartAbstractPgsqlExtDb', // dev
			'\\SmartModExtLib\\DbalPdoMedoo\\DbalPDO',
			'\\SmartModExtLib\\DbOrmRedbean\\ORM',
			//--
			'\\SmartFtpClient',
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
		];
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
