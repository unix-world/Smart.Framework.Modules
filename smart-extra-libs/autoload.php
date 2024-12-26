<?php
// [Smart.Framework.Modules - ExtraLibs AutoLoad]
// (c) 2008-present unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//--
require_once('modules/smart-extra-libs/version.php'); // extra libs version
//--

/**
 * Function AutoLoad Extra Libs from (Smart.Framework.Modules)
 * they are loaded via Dependency Injection
 *
 * @access 		private
 * @internal
 *
 * @version 	20221225
 *
 */
function autoload__SmartFrameworkModulesExtraLibs($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(substr($classname, 0, 5) !== 'Smart') { // must start with Smart
		return;
	} //end if
	//--
	switch((string)$classname) {
		//--
		case 'SmartTypo3FluidTemplating':
		case 'SmartTwigTemplating':
		case 'SmartTemplating':
			require_once('modules/smart-extra-libs/lib_templating_ext.php'); 		// extended templating
			break;
		//--
		case 'SmartPMarkdownToHTML':
			require_once('modules/smart-extra-libs/lib_pmarkdown.php'); 			// markdown to html parser, v1, classic (parsedown flavor)
			break;
		//--
		case 'SmartFtpClient':
			require_once('modules/smart-extra-libs/lib_ftp_cli.php');				// ftp client
			break;
		case 'SmartCurlHttpFtpClient':
			require_once('modules/smart-extra-libs/lib_curl_http_ftp_cli.php'); 	// curl http/ftp connector
			break;
		//--
		case 'SmartAbstractPgsqlExtDb':
			require_once('modules/smart-extra-libs/lib_db_ext_pgsql.php'); 			// pgsql extended db connector
			break;
		case 'SmartSolrDb':
			require_once('modules/smart-extra-libs/lib_db_solr.php'); 				// solr db connector
			break;
		//-- zip archive
		case 'SmartZipArchive':
			require_once('modules/smart-extra-libs/lib_export_zip.php');			// zip archive
			break;
		//-- ooffice export
		case 'SmartExportToOpenOffice':
		case 'SmartImportFromOpenOffice':
			require_once('modules/smart-extra-libs/lib_export_import_ooffice.php'); // ooffice export / import
			break;
		//-- calendar
		case 'SmartCalendarComponent':
		case 'SmartHTMLCalendar':
			require_once('modules/smart-extra-libs/lib_calendar.php');				// calendar component (html)
			break;
		//-- charts
		case 'SmartImgBizCharts':
		case 'SmartImgGfxCharts':
			require_once('modules/smart-extra-libs/lib_charts.php'); 				// gd charts
			break;
		//-- lang id
		case 'SmartLangIdClient':
			require_once('modules/smart-extra-libs/lib_langid_cli.php'); 			// langid client
			break;
		//--
		default:
			return; // other classes are not managed here ...
		//--
	} //end switch
	//--
} //END FUNCTION
//--
spl_autoload_register('autoload__SmartFrameworkModulesExtraLibs', true, false); 	// throw / append
//--


// end of php code
