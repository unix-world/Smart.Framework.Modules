
# Setup mod SVN manager (admin.php area) for Smart.Framework

## Add the following setup lines in etc/config-admin.php

```php
$configs['svn']['cmd'] = '/usr/local/bin/svn';
$configs['svn']['7za'] = '/usr/local/bin/7za'; // '7z' | 'zip'
$configs['svn']['tar'] = '/usr/local/bin/gtar'; // 'tar.gz'
$configs['svn']['repos'] = [
	'some-repo' => [
		'url' 				=> 'https://repos/svn/some-repo/', // 'file:///home/SVN-REPOS/some-repo/'
		'path' 				=> null, // or 'trunk/'
		'user' 				=> 'user123',
		'pass' 				=> 'pass123', // for file:/// use null ; otherwise must set a plain or encrypted as: SmartUtils::crypto_blowfish_encrypt('pass123') ; if you ever change the SMART_FRAMEWORK_SECURITY_KEY, this password must be regenerated again !!
		'encrypted-pass' 	=> false, // or set to true if password is blowfish encrypted
		'allow-download' 	=> null, // '7z' | 'zip' | 'tar.gz'
		'readonly' 			=> false // or true
	],
	// ...
];
```
