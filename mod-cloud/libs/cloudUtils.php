<?php
// Module Lib: \SmartModExtLib\Cloud\cloudUtils
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\Cloud;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


final class cloudUtils {

	// r.20200401
	// ::


	public static function ensureCloudHtAccess() {
		//--
		$dir = 'wpub/cloud/';
		//--
		if(!\SmartFileSystem::is_type_file($dir.'.htaccess')) {
			\SmartFileSystem::write($dir.'.htaccess', '### Smart.Framework // Cloud @ HtAccess Data Protection ###'."\n\n".\trim((string)\SMART_FRAMEWORK_HTACCESS_NOINDEXING)."\n".\trim((string)\SMART_FRAMEWORK_HTACCESS_NOEXECUTION)."\n".\trim((string)\SMART_FRAMEWORK_HTACCESS_FORBIDDEN)."\n");
			if(!\SmartFileSystem::is_type_file($dir.'.htaccess')) {
				\Smart::raise_error(
					'#SMART-FRAMEWORK-CLOUD-REQUIRED-FILES#'."\n".'A required file cannot be created in #CLOUD: `'.$dir.'.htaccess`',
					'Cloud.WebMail Init ERROR'
				);
				die(__METHOD__.'() # Failed to create the Cloud Protection HtAccess file ...');
			} //end if
		} //end if
		//--
	} //END FUNCTION



} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
