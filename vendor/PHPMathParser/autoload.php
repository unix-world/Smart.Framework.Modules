<?php

/**
 * Function AutoLoad Extra Libs for Vendor / PHPMathParser
 * they are loaded via Dependency Injection
 *
 * @access 		private
 * @internal
 *
 */
function autoload__VendorPHPMathParser($classname) {
	//--
	$classname = (string) $classname;
	//--
	if((strpos($classname, '\\') === false) OR (!preg_match('/^[a-zA-Z0-9_\\\]+$/', $classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(strpos($classname, 'PHPMathParser\\') === false) { // must start with this namespaces only
		return;
	} //end if
	//--
	$parts = (array) explode('\\', $classname);
	$max = (int) count($parts) - 1; // the last is the class
	//--
	if(($max < 1) OR ((string)$parts[1] == '')) {
		return;
	} //end if
	//--
	$dir = 'modules/vendor/PHPMathParser/';
	//--
	for($i=1; $i<$max; $i++) {
		$dir .= (string) $parts[$i].'/';
	} //end for
	//--
	$dir  = (string) $dir;
	$file = (string) $parts[(int)$max];
	$path = (string) $dir.$file;
	$path = (string) str_replace(array('\\', "\0"), array('', ''), $path); // filter out null byte and backslash
	//--
	if(!preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in file
	} //end if
	//--
	if(!is_file($path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once($path.'.php');
	//--
} //END FUNCTION
//--
spl_autoload_register('autoload__VendorPHPMathParser', true, false); // throw / append
//--

// end of php code
