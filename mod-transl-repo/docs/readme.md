
# Module Translations Repo
Provides 2 services: a Translations Repo manager + a Custom Translation Adapter based on PostgreSQL
The both services below can use the same DB but is recommended to use separate DBs

## Service 1: Custom Translations Adapter

- implements a custom translations adapter for Smart.Framework
- requires: _sql/postgresql/init-smart-framework.sql + adapters/sql/pgsql-schema.sql
- requires: after setup the DB schema as above need to import all EN @core translations from YAML files to this table: transl_repo.translations prior to be used

### configs
```
// setup in init.php
const SMART_FRAMEWORK_DEFAULT_LANG = 'en';
const SMART_FRAMEWORK_URL_PARAM_LANGUAGE = 'lang';
const SMART_FRAMEWORK_TRANSLATIONS_ADAPTER_CUSTOM = 'modules/mod-transl-repo/adapters/translations-adapter-pgsql.php';

// setup in config.php
$configs['pgsql']['type'] 			= 'postgresql';
$configs['pgsql']['server-host'] 	= '127.0.0.1';
$configs['pgsql']['server-port']	= 5432;
$configs['pgsql']['dbname']			= 'webapp';
$configs['pgsql']['username']		= 'user';
$configs['pgsql']['password']		= '*****';
$configs['pgsql']['timeout']		= 10;
$configs['pgsql']['slowtime']		= 0.0050;
$configs['pgsql']['transact']		= 'READ COMMITTED';

```

## Service 2: Translations Repo Manager

- DB support: PostgreSQL
- requires: _sql/postgresql/init-smart-framework.sql + models/sql/pgsql-schema.sql
- config arrays: (projects -> sync IN (xls export API), sync OUT (xls import API)
- integrates with Service 1: Custom Translations Adapter (PostgreSQL)
- integrates with PageBuilder (PostgreSQL or SQlite)

### config.php
```php

// transl-repo projects definition, config-admin.php
$configs['transl-repo-projects'] = [
	'Test1' => [
		'url-import' => 'https://127.0.0.1/sites/frameworks/smart-framework/admin.php?page=transl-repo.export-api',
		'url-export' => 'https://127.0.0.1/sites/frameworks/smart-framework/admin.php?page=transl-repo.import-api',
		'auth-user' => 'admin',
		'auth-pass' => 'pass',
	],
	'Test2' => [
		'url-import' => 'http://127.0.0.1/sites/frameworks/smart-framework/admin.php?page=transl-repo.export-api',
		'url-export' => 'http://127.0.0.1/sites/frameworks/smart-framework/admin.php?page=transl-repo.import-api',
		'auth-user' => 'admin',
		'auth-pass' => 'pass',
	]
];

// custom pgsql connection for translations, config.php
$configs['pgsql-transl-repo']['type'] 			= 'postgresql';
$configs['pgsql-transl-repo']['server-host'] 	= '127.0.0.1';
$configs['pgsql-transl-repo']['server-port']	= 5432;
$configs['pgsql-transl-repo']['dbname']			= 'translations_repo';
$configs['pgsql-transl-repo']['username']		= 'user';
$configs['pgsql-transl-repo']['password']		= '*****';
$configs['pgsql-transl-repo']['timeout']		= 15;
$configs['pgsql-transl-repo']['slowtime']		= 0.0050;
$configs['pgsql-transl-repo']['transact']		= 'READ COMMITTED';

```

##### END
