# Smart.Framework.Modules - a collection of modules for Smart.Framework
(c) 2009 - 2019 unix-world.org
License: BSD

## Extra Modules:
	* UI Fonts: Web fonts + Captcha fonts
	* UI Bootstrap: CSS + Javascript UI Toolkit
	* UI Uikit: CSS + Javascript UI Toolkit
	* UI jQueryUI: CSS + Javascript UI Toolkit
	* UI W3: CSS UI Toolkit
	* JS Components: a collection of Javascript components and utils
	* Workflow Components: another collection of Javascript components and utils
	* Language Detect: NGrams Language Detection library
	* Maps Cache: a caching API for Open Map types
	* Nette Latte Templating Engine: integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl-nette-latte into smart-framework/modules/
	* Twig Templating Engine: integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl-twig into smart-framework/modules/
	* TYPO3Flow Templating Engine: integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup, just copy the mod-tpl-typo3-fluid into smart-framework/modules/
	* Zend DBAL: PDO based connector for: PgSQL, MySQL and SQLite
	* MediaGallery: a media gallery api for Smart.Framework
	* SocialMedia Facebook: Js + PHP API
	* SocialMedia Twitter: Js + PHP API

## Extra App Modules:
	* SVN (manager): a SVN (subversion) web based manager
	* Cloud (manager): an advanced Cloud Api: WebDAV, CalDAV / WebCal and CardDAV / WebAddressbook
	* Agile (manager): an Agile project management API for Smart.Framework
	* PageBuilder: a content management API for Smart.Framework

## Extra Libs:
	* PostgreSQL Extended connector: make life easier for the Smart.Framework PostgreSQL connector by providing an advanced functionality class
	* Solr: connector for Apache Solr 3.x / 4.x / 5.x / 6.x / 7.x (or later versions)
	* CURL based HTTP Client Lib with proxy support
	* LangID.py client wrapper (a language detection utility based on external service)
	* Charting library for drawing charts with PHP
	* TPL wrapper Lib for the includded TPL engines (this make life easier with existing TPL engines in Smart.Framework: MarkersTPL and Smart.Framework.Modules: netteLatte, Twig and typo3Fluid)

## Vendor Libs:
	* CSS and JS Minify vendor/MatthiasMullie
	* PSR Cache: vendor/Psr

## Installation NOTES:
	* install first the Smart.Framework
	* after, copy the desired modules from Smart.Framework.Modules into the Smart.Framework modules folder: smart-framework/modules/
	* all libs in modules are auto-loaded via built-in Autoloader (except: smart-extra-libs, vendor)
	* using the smart-extra-libs from Smart.Framework.Modules:
		uncomment or add the following line into: modules/app/app-custom-bootstrap.php
			require_once('modules/smart-extra-libs/autoload.php'); // the autoloader for Smart.Framework modules/extra-libs
	* using the vendor from Smart.Framework.Modules:
		uncomment or add the following line into: modules/app/app-custom-bootstrap.php
			require_once('modules/vendor/autoload.php'); // autoload for Smart.Framework.Modules / Vendor Libs

