
# Using the Smart PageBuilder Module for Smart.Framework

## required settings in etc/init.php
```php
define('SMART_PAGEBUILDER_DB_TYPE', 'sqlite'); // to use PageBuilder with SQLite DB
//define('SMART_PAGEBUILDER_DB_TYPE', 'sqlite'); // or comment the above and uncomment this to use PageBuilder with PostgreSQL DB
```

## for PostgreSQL only, must edit and activate the $configs['pgsql'] from etc/config.php

## optional settings in etc/config-admin.php
```php
define('SMART_PAGEBUILDER_DISABLE_DELETE', true); // this can be set in etc/config-admin.php to disable page deletions in PageBuilder Manager (optional)
```

# Managing PageBuilder Pages - Backend:
admin.php?page=page-builder.manage

# Samples - Frontend (requires to install Sample Data in DB from mod-page-builder/models/sql/{postgresql|sqlite}/data/):
index.php?/page/page-builder.test-frontend
index.php?/page/page-builder.test-frontend-segment
index.php?/page/page-builder.test-frontend-segment-with-markers
