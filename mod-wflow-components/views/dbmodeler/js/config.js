
// wwwsqldesigner v.1.7: config.js
// (c) 2005-2018, Ondrej Zara
// License: BSD

// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

var CONFIG = {

	IS_READONLY: false,

	AVAILABLE_DBS: ['postgresql', 'sqlite', 'mysql'],
	DEFAULT_DB: 'sqlite',

	AVAILABLE_LOCALES: ['en'],
	DEFAULT_LOCALE: 'en',

	AVAILABLE_BACKENDS: [], // ['php-postgresql', 'php-sqlite', 'php-mysql'],
	DEFAULT_BACKEND: [], // ['php-postgresql'],

	RELATION_THICKNESS: 3,
	RELATION_SPACING: 15,
	RELATION_COLORS: ['#000000', '#003366', '#778899', '#666699', '#571845', '#900C3E', '#8C6954', '#0F5959', '#0B5E56'],

	STATIC_PATH: '',

	XHR_SAVE_FUNCTION: null,
	XHR_LOAD_FUNCTION: null,

	XML_SAVE_DATATYPES: false

};

// #END
