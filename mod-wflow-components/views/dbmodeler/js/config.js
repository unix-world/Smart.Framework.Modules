
// wwwsqldesigner: config.js

var CONFIG = {

	IS_READONLY: false,

	AVAILABLE_DBS: ['postgresql', 'sqlite', 'mysql'],
	DEFAULT_DB: 'postgresql',

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

// END
