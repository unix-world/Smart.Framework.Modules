<?php
// Module Lib: \SmartModExtLib\Cloud\cloudUtils
// (c) 2006-2023 unix-world.org - all rights reserved

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

	// r.20231110
	// ::


	public static function ensureCloudHtAccess() : void {
		//--
		$dir = 'wpub/cloud/';
		//--
		if(!\SmartFileSystem::is_type_file((string)$dir.'.htaccess')) {
			\SmartFileSystem::write((string)$dir.'.htaccess', '### Smart.Framework // Cloud @ HtAccess Data Protection ###'."\n\n".\trim((string)\SMART_FRAMEWORK_HTACCESS_NOINDEXING)."\n".\trim((string)\SMART_FRAMEWORK_HTACCESS_NOEXECUTION)."\n".\trim((string)\SMART_FRAMEWORK_HTACCESS_FORBIDDEN)."\n");
			if(!\SmartFileSystem::is_type_file((string)$dir.'.htaccess')) {
				\Smart::raise_error((string)__METHOD__.' # A required file cannot be created in #CLOUD: `'.$dir.'.htaccess`');
				die(__METHOD__.' # Failed ...');
				return;
			} //end if
		} //end if
		//--
	} //END FUNCTION


	public static function getUserDirPrefixedFirstLetter(string $safe_user_dir)  {
		//--
		$safe_user_dir = (string) \trim((string)$safe_user_dir);
		$prefix_letter = (string) \substr((string)$safe_user_dir, 0, 1);
		//--
		if(
			((string)$safe_user_dir == '')
			OR
			((string)$prefix_letter == '')
			OR
			((int)\strlen((string)$safe_user_dir) < 3)
			OR
			((int)\strlen((string)$prefix_letter) != 1)
			OR
			(!\preg_match('/^[a-z]{1}$/', (string)$prefix_letter))
		) {
			//--
			\Smart::raise_error((string)__METHOD__.' # Failed to create User Prefixed Path for: `'.$safe_user_dir.'`');
			die(__METHOD__.' # Failed ...');
			return '_/_invalid_'; // _ can't be contained by username, thus is safe in this error context
			//--
		} //end if
		//--
		return (string) $prefix_letter.'/'.$safe_user_dir;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
