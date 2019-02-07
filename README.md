# Smart.Framework.Modules - a collection of modules for Smart.Framework
## Dual-licensed: under BSD license or GPLv3 license (at your choice)
### This software project is open source. You must choose which license to use depending on your use case: BSD license or GPLv3 license (see LICENSE file)
<b>(c) 2009 - 2019 unix-world.org</b> / <i>support&#64;unix-world.org</i>

This software framework is compatible, stable and actively tested with PHP 5.6 / 7.0 / 7.1 / 7.2 / 7.3 versions.
Prefered PHP versions are: 7.1 / 7.2 which are currently LTS.

## List of available Modules for Smart.Framework:

### Extra Modules (BSD licensed):
	* UI Fonts: Web fonts + Captcha fonts
	* UI Bootstrap: CSS + Javascript UI Toolkit
	* UI Uikit: CSS + Javascript UI Toolkit
	* UI jQueryUI: CSS + Javascript UI Toolkit
	* UI W3: CSS UI Toolkit
	* JS Components: a collection of Javascript components and utils
	* Maps Cache: a caching API for Open Map types
	* Language Detect: NGrams Language Detection library
	* Nette Latte Templating Engine: integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl-nette-latte into smart-framework/modules/
	* Twig Templating Engine: integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl-twig into smart-framework/modules/
	* TYPO3Flow Templating Engine: integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl-typo3-fluid into smart-framework/modules/
	* Zend DBAL: PDO based connector for: PgSQL, MySQL and SQLite
	* MediaGallery: a media gallery api for Smart.Framework
	* SocialMedia Facebook: Js + PHP API
	* SocialMedia Twitter: Js + PHP API
	* PageBuilder: a content management API for Smart.Framework
	* SVN (manager): a web based SVN (subversion) manager

### Extra App Modules (GPLv3 licensed ; can be used with Smart.Framework licensed under GPLv3 license only):
	* Cloud App: an advanced Cloud Api and app module: WebDAV, CalDAV / WebCal and CardDAV / WebAddressbook
	* Agile App: an document store API and app module for Smart.Framework
	* Workflow Components: a collection of smart Javascript components and utilities

### Extra Libs (BSD licensed):
	* PostgreSQL Extended connector: make life easier for the Smart.Framework PostgreSQL connector by providing an advanced functionality class
	* Solr: connector for Apache Solr 3.x / 4.x / 5.x / 6.x / 7.x (or later versions)
	* CURL based HTTP Client Lib with proxy support
	* LangID.py client wrapper (a language detection utility based on external service)
	* Charting library for drawing charts with PHP
	* TPL wrapper Lib for the includded TPL engines (this make life easier with existing TPL engines in Smart.Framework: MarkersTPL and Smart.Framework.Modules: netteLatte, Twig and typo3Fluid)

### Vendor Libs (BSD licensed):
	* CSS and JS Minify vendor/MatthiasMullie
	* PSR Cache: vendor/Psr

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
