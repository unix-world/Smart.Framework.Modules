
# Setup mod SVN manager (admin.php area) for Smart.Framework

## Add the following setup lines in etc/config-admin.php

```php
$configs['svn']['cmd'] = '/usr/local/bin/svn';
$configs['svn']['7za'] = '/usr/local/bin/7za';
$configs['svn']['repos'] = [
	'some-repo' => [
		'url' 				=> 'https://repos/svn/some-repo/',
		'path' 				=> null, // or 'trunk/'
		'user' 				=> 'user123', // plain or encrypted as: SmartUtils::crypto_blowfish_encrypt('pass123'); // if you ever change the SMART_FRAMEWORK_SECURITY_KEY, this password must be regenerated again !!
		'pass' 				=> 'pass123'
		'encrypted-pass' 	=> false, // or set to true if password is blowfish encrypted
		'allow-download' 	=> null, // '7z'
		'readonly' 			=> false // or true
	],
	// ...
];
```
