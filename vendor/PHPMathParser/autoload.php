<?php

/**
 * Function AutoLoad Extra Libs for Vendor / PHPMathParser
 * they are loaded via Dependency Injection
 *
 * @access 		private
 * @internal
 *
 */
spl_autoload_register(function(string $classname) : void {
	//--
	if((strpos((string)$classname, '\\') === false) OR (!preg_match('/^[a-zA-Z0-9_\\\]+$/', (string)$classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if(str_starts_with((string)$classname, 'PHPMathParser\\') === false) { // if class name is starting with PHPMathParser\
		return;
	} //end if
	//--
	$parts = (array) explode('\\', (string)$classname);
	$max = (int) count((array)$parts) - 1; // the last is the class
	//--
	if((int)$max < (int)(2 - 1)) {
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
	$path = (string) str_replace([ '\\', "\0" ], [ '', '' ], (string)$path); // filter out null byte and backslash
	//--
	if(!preg_match('/^[_a-zA-Z0-9\-\/]+$/', (string)$path)) {
		return; // invalid path characters in file
	} //end if
	//--
	if(!is_file((string)$path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once((string)$path.'.php');
	//--
}, true, false); // throw / append
//--

// end of php code
