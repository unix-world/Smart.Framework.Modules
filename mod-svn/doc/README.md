
# Setup mod SVN manager (admin.php area) for Smart.Framework

## Add the following setup lines in etc/config-admin.php

```php
$configs['svn']['cmd'] = '/usr/local/bin/svn';
$configs['svn']['7za'] = '/usr/local/bin/7za';
$configs['svn']['repos'] = [
	'some-repo' => [ 'url' => 'https://repos/svn/some-repo/', 'user' => 'user123', 'pass' => 'pass123' ], // 'allow-download' => '7z'
	// ...
];
```
