# Smart.Framework.Modules - a collection of modules for Smart.Framework
## Dual-licensed: under BSD license or GPLv3 license (at your choice)
### This software project is open source. You must choose which license to use depending on your use case: BSD license or GPLv3 license (see LICENSE file)
**(c) 2009 - 2021 unix-world.org** / support&#64;unix-world.org

#### This software framework is compatible, stable and actively tested with PHP 7.2 / 7.3 / 7.4 / 8.0 versions.
**Prefered PHP version** is: **7.4** (LTS).

## List of available Modules for Smart.Framework:

### Extra Modules (BSD licensed):
	* UI Fonts: Web fonts + Icon fonts + Captcha fonts
	* UI Bootstrap: CSS + Javascript UI Toolkit
	* UI Uikit: CSS + Javascript UI Toolkit
	* UI jQueryUI: CSS + Javascript UI Toolkit
	* UI W3: CSS UI Toolkit
	* JS Components: a collection of Javascript components and utils
	* SOAP (Server) Request Handler, built over DomDocument XML - handle SOAP server requests without need of SoapServer class from PHP SOAP extension
	* Maps Cache: a caching API for Open Map types
	* Language Detect: NGrams Language Detection library
	* Dust Templating Engine (by LinkedIn) integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl and mod-tpl-dust into smart-framework/modules/
	* Twig Templating Engine (by Symfony): integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl and mod-tpl-twig into smart-framework/modules/
	* TYPO3Flow Templating Engine: integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl and mod-tpl-typo3-fluid into smart-framework/modules/
	* Zend DBAL: PDO based connector for: PgSQL, MySQL and SQLite ; to start use just copy the mod-dbal-zend into smart-framework/modules/
	* Redbean ORM: an ORM based connector for: PgSQL, MySQL and SQLite ; to start use just copy the mod-db-orm-redbean into smart-framework/modules/
	* GeoIP: a GeoIP api for PHP using the geoiplookup / geoiplookup6 executables
	* MediaGallery: a media gallery api for Smart.Framework
	* Countries: json list of countries + svg flags for countries
	* SocialMedia Facebook: Js + PHP API
	* SocialMedia Twitter: Js + PHP API
	* OAuth2 Api: Manager for XOAUTH2 based authentications (can be used as an external provider for: SMTP, IMAP and other protocols)
	* SVN (manager): a web based SVN (subversion) manager
	* DB Admin (manager): a web based DataBase administrator (currently supports just MongoDB)

### Extra App Modules (GPLv3 licensed ; can be used with Smart.Framework licensed under GPLv3 license only):
	* Documentor: a PHP and JavaScript documentation generator
	* Cloud App: an advanced Cloud Api and app module: WebDAV, CalDAV / WebCal and CardDAV / WebAddressbook
	* Agile App: an document store API and app module for Smart.Framework
	* Workflow Components: a collection of smart Javascript components and utilities

### Extra Libs (BSD licensed):
	* PostgreSQL Extended connector: make life easier for the Smart.Framework PostgreSQL connector by providing an advanced functionality class
	* Solr: connector for Apache Solr 3.x / 4.x / 5.x / 6.x / 7.x / 8.x (or later versions)
	* CURL based HTTP Client Lib with proxy support
	* LangID.py client wrapper (a language detection utility based on external service)
	* Charts library for PHP
	* TPL wrapper Lib for the includded TPL engines (this make life easier with existing TPL engines in Smart.Framework: MarkersTPL and Smart.Framework.Modules: Dust, Twig and Typo3Fluid)

### Vendor Libs (BSD licensed):
	* CSS and JS Minify, Geo, Scrapbook: vendor/MatthiasMullie
	* PSR Cache, SimpleCache: vendor/Psr
	* HTMLToMarkdown: vendor/League
	* SVG Draw: vendor/MeyFaSvg
	* Math Parser: vendor/PHPMathParser
	* Lightweight DB Connector: vendor/Medoo

### Other App Modules:
	* Xtra Bizz (Business Widgets) (GPLv3 licensed ; can be used with Smart.Framework licensed under GPLv3 license only)
	* Vanilla Widgets (BSD licensed)

## Installation NOTES:
	* install first the Smart.Framework and choose the license: BSD license or GPLv3 license (see the file https://github.com/unix-world/Smart.Framework/LICENSE)
	* after, copy the desired modules from Smart.Framework.Modules into the Smart.Framework modules folder: (example) smart-framework/modules/
	* choose your license for the Smart.Framework.Modules (BSD license or GPLv3 license), depending of the modules you will use (see the above notes and the file https://github.com/unix-world/Smart.Framework.Modules/LICENSE)

## Installation HINTS:
	* all libs in modules are auto-loaded via built-in Autoloader (except: smart-extra-libs, vendor)
	* using the smart-extra-libs from Smart.Framework.Modules:
		uncomment or add the following line into: modules/app/app-custom-bootstrap.php
			require_once('modules/smart-extra-libs/autoload.php'); // the autoloader for Smart.Framework modules/extra-libs
	* using the vendor from Smart.Framework.Modules:
		uncomment or add the following line into: modules/app/app-custom-bootstrap.php
			require_once('modules/vendor/autoload.php'); // autoload for Smart.Framework.Modules / Vendor Libs

