
# Using CSS Minify

```php

ini_set('display_errors', '1');	// display runtime errors
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED); // error reporting
set_exception_handler(function($exception) { // no type for EXCEPTION to be PHP 7 compatible
	//--
	if(!headers_sent()) {
		http_response_code(500);
	} //end if
	die('<h1 style="color:#FF3300;">Exception: '.htmlspecialchars($exception->getMessage()).'</h1>');
	//--
});

require('vendor/autoload.php');

$minifier = new \MatthiasMullie\Minify\CSS('/* CSS */');
$minifier->add('tmp/path/to/css-file1.css');
$minifier->add('tmp/path/to/css-file2.css');
echo '<h1>Minified CSS with Embedded Images</h1><textarea style="width:750px; height:500px;">'.htmlspecialchars((string)$minifier->minify()).'</textarea>';

//end of php code
```