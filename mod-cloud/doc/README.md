
# Using the Smart Cloud Module for Smart.Framework

## required settings in etc/init.php
```php
define('SMART_SOFTWARE_URL_ALLOW_PATHINFO', 1); // enable PathInfo (set any of the following ; recommended in 1 except if you need 2 for other purposes): 1 = only admin ; 2 = both index & admin
```

## required settings in etc/config.php
```php
//--------------------------------------- Info URL
$configs['app']['info-url'] 		= 'smart.ncloud';						// Info URL: this must be someting like `www . mydomain . net`
//---------------------------------------

//--------------------------------------- SQLite related configuration
$configs['sqlite']['timeout'] 		= 60;									// connection timeout
$configs['sqlite']['slowtime'] 		= 0.0025;								// slow query time (for debugging)
//---------------------------------------

//--------------------------------------- REGIONAL SETTINGS
$configs['regional']['language-id']					= 'en';					// Language `en` | `ro` (must exists as defined)
$configs['regional']['decimal-separator']			= '.';					// decimal separator `.` | `,`
$configs['regional']['thousands-separator']			= ',';					// thousand separator `,` | `.` | ` `
$configs['regional']['calendar-week-start']			= '0';					// 0=start on sunday | 1=start on Monday ; used for both PHP and Javascript
$configs['regional']['calendar-date-format-client'] = 'dd.mm.yy';			// Client Date Format - Javascript (allow only these characters: yy mm dd . - [space])
$configs['regional']['calendar-date-format-server']	= 'd.m.Y';				// Server Date Format - PHP (allow only these characters: Y m d . - [space])
//---------------------------------------
$languages = array('en' => '[EN]');											// default associative array of available languages for this software (do not change without installing new languages support files)
//---------------------------------------
```

## required settings in etc/config-admin.php
```php
//--------------------------------------- Templates and Home Page
$configs['app']['admin-domain'] 					= 'your-domain.ext'; 		// admin domain as yourdomain.ext
$configs['app']['admin-home'] 						= 'cloud.welcome';			// admin home page action
$configs['app']['admin-default-module'] 			= 'cloud';					// admin default module
$configs['app']['admin-template-path'] 				= 'default';				// default admin templates folder from etc/templates/
$configs['app']['admin-template-file'] 				= 'template.htm';			// default admin template file
//---------------------------------------
//--
$configs['app']['url']								= 'https://'.$configs['app']['admin-domain'].'/cloud/';
//--
//define('APP_INSTALL_PASSWORD', '...');
define('APP_AUTH_PRIVILEGES', '<admin>,<webdav>,<caldav>,<carddav>');
$configs['app-auth']['adm-namespaces'] = [
	'Cloud - Files' 			=> $configs['app']['url'].'admin.php/page/cloud.files/~',
	'Cloud - Calendar' 			=> $configs['app']['url'].'admin.php/page/cloud.ical/~',
	'Cloud - WebCalendar' 		=> $configs['app']['url'].'admin.php?/page/cloud.icalweb',
	'Cloud - Addressbook' 		=> $configs['app']['url'].'admin.php/page/cloud.abook/~',
	'Cloud - WebAddressbook' 	=> $configs['app']['url'].'admin.php?/page/cloud.abookweb'
];
//--
//define('NCLOUD_WEBDAV_PROPFIND_ETAG_MAX_FSIZE', 25000000); // PROPFIND ETag up to 25 MB Files (this will slow down things and is good to be enabled only if sync operations are used ...)
define('NCLOUD_WEBDAV_SHOW_QUOTA', true);  // files
define('NCLOUD_CALDAV_SHOW_QUOTA', true);  // ical
define('NCLOUD_CARDDAV_SHOW_QUOTA', true); // abook
//--
```

## required settings in etc/config-index.php
```php
//--------------------------------------- Templates and Home Page
$configs['app']['index-domain'] 					= 'mail.unix-world.org'; 	// index domain as yourdomain.ext
$configs['app']['index-home'] 						= 'cloud.welcome';			// index home page action
$configs['app']['index-default-module'] 			= 'cloud'; 					// index default module ; check also SMART_FRAMEWORK_SEMANTIC_URL_SKIP_MODULE
$configs['app']['index-template-path'] 				= 'default';				// default index templates folder from etc/templates/
$configs['app']['index-template-file'] 				= 'template.htm';			// default index template file
//---------------------------------------
```

## Cloud Authentication (based on mod-auth-admins): add the following line of code in modules/app/app-auth-admin.inc.php
```php
if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
	http_response_code(500);
	die(SmartComponents::http_error_message('500 Internal Server Error', 'A required module is missing: `mod-auth-admins` ...'));
} //end if
\SmartModExtLib\AuthAdmins\AuthAdminsHandler::Authenticate(
	false // enforce SSL: TRUE/FALSE
);
```
