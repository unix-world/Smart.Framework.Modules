<?php

/**
 * Function AutoLoad Extra Libs for Vendor / Medoo
 * they are loaded via Dependency Injection
 *
 * @access 		private
 * @internal
 *
 */
function autoload__VendorMedoo($classname) {
	//--
	$classname = (string) $classname;
	//--
	if((strpos($classname, '\\') === false) OR (!preg_match('/^[a-zA-Z0-9_\\\]+$/', $classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(strpos($classname, 'Medoo\\') === false) { // must start with this namespaces only
		return;
	} //end if
	//--
	switch((string)$classname) {
		case 'Medoo\\Medoo':
		case 'Medoo\\Raw':
			//--
			$path = 'modules/vendor/Medoo/Medoo';
			if(!is_file($path.'.php')) {
				return; // file does not exists
			} //end if
			//--
			require_once($path.'.php');
			//--
			break;
		default:
			return;
	} //end switch
	//--
} //END FUNCTION
//--
spl_autoload_register('autoload__VendorMedoo', true, false); // throw / append
//--

// end of php code
?>