/*
Language: YAML
Author: Stefan Wienert <stwienert@gmail.com>
Description: YAML (Yet Another Markdown Language)
Category: config
### modified by unixman: remove unused dependency to ruby, YAML is mostly used without it !
*/

// syntax/web/yaml.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('yaml',
function(hljs) {
	var LITERALS = 'true false yes no null';

	var keyPrefix = '^[ \\-]*';
	var keyName =  '[a-zA-Z_][\\w\\-]*';
	var KEY = {
		className: 'attr',
		variants: [
			{ begin: keyPrefix + keyName + ":"},
			{ begin: keyPrefix + '"' + keyName + '"' + ":"},
			{ begin: keyPrefix + "'" + keyName + "'" + ":"}
		]
	};

	var STRING = {
		className: 'string',
		relevance: 0,
		variants: [
			{begin: /'/, end: /'/},
			{begin: /"/, end: /"/},
			{begin: /\S+/}
		],
		contains: [
			hljs.BACKSLASH_ESCAPE
		]
	};

	return {
		case_insensitive: true,
		aliases: ['yml', 'YAML', 'yaml'],
		contains: [
			KEY,
			{
				className: 'meta',
				begin: '^---\s*$',
				relevance: 10
			},
			{ // multi line string
				className: 'string',
				begin: '[\\|>] *$',
				returnEnd: true,
				contains: STRING.contains,
				// very simple termination: next hash key
				end: KEY.variants[0].begin
			},
			{ // data type
				className: 'type',
				begin: '!!' + hljs.UNDERSCORE_IDENT_RE,
			},
			{ // fragment id &ref
				className: 'meta',
				begin: '&' + hljs.UNDERSCORE_IDENT_RE + '$',
			},
			{ // fragment reference *ref
				className: 'meta',
				begin: '\\*' + hljs.UNDERSCORE_IDENT_RE + '$'
			},
			{ // array listing
				className: 'bullet',
				begin: '^ *-',
				relevance: 0
			},
			hljs.HASH_COMMENT_MODE,
			{
				beginKeywords: LITERALS,
				keywords: {literal: LITERALS}
			},
			hljs.C_NUMBER_MODE,
			STRING
		]
	};
}
);

// #END
