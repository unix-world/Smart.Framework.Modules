<?php
// Class: \SmartModExtLib\Documentor\SmartClasses
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

namespace SmartModExtLib\Documentor;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
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
 * @version 	v.20191124
 * @package 	Documentor
 *
 */
final class SmartClasses {

	// ::


	public static function getJavascriptSfFile() {
		//--
		return 'lib/js/framework/smart-framework.pak.js';
		//--
	} //END FUNCTION


	public static function listJavascriptSfClasses() {
		//--
		return [
			'SmartJS_CoreUtils' 		=> 'lib/js/framework/src/core_utils.js',
			'SmartJS_DateUtils' 		=> 'lib/js/framework/src/date_utils.js',
			'SmartJS_Base64' 			=> 'lib/js/framework/src/crypt_utils.js',
			'SmartJS_CryptoHash' 		=> 'lib/js/framework/src/crypt_utils.js',
			'SmartJS_CryptoBlowfish' 	=> 'lib/js/framework/src/crypt_utils.js',
			'SmartJS_Archiver_LZS' 		=> 'lib/js/framework/src/arch_utils.js',
			'SmartJS_ModalBox' 			=> 'lib/js/framework/src/ifmodalbox.js',
			'SmartJS_BrowserUtils' 		=> 'lib/js/framework/src/browser_utils.js',
			'Test_Browser_Compliance' 	=> 'lib/js/framework/src/browser_check.js'
		];
		//--
	} //END FUNCTION


	public static function listJavascriptSfmClasses() {
		//--
		return [
			'SmartJS_BrowserUIUtils' 	=> 'lib/js/jquery/jquery.smartframework.ui.js'
		];
		//--
	} //END FUNCTION


	public static function listPhpSfClasses() {
		//--
		return [
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
	} //END FUNCTION


	public static function listPhpSfmClasses() {
		//--
		return [
			//--
			'\\SmartModExtLib\\AuthAdmins\\SimpleAuthAdminsHandler',
			'\\SmartModExtLib\\AuthAdmins\\AuthAdminsHandler',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendController',
			'\\SmartModExtLib\\PageBuilder\\AbstractFrontendPlugin',
			'\\SmartModExtLib\\Webdav\\ControllerAdmDavFs',
			'\\SmartModExtLib\\Webdav\\ControllerAdmCalDavFs',
			'\\SmartModExtLib\\Webdav\\ControllerAdmCardDavFs',
			//--
			'\\SmartModExtLib\\HighlightSyntax\\Highlighter',
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
			'\\SmartModExtLib\\Soap\\SoapServerRequestHandler',
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
?>