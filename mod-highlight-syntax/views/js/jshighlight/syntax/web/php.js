/*
Language: PHP
Author: Victor Karamzin <Victor.Karamzin@enterra-inc.com>
Contributors: Evgeny Stepanischev <imbolk@gmail.com>, Ivan Sagalaev <maniac@softwaremaniacs.org>
Category: common
### modified by unixman: fix to better parse PHP code # r.20210603
*/

// syntax/web/php.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('php',
function(hljs) {
	var VARIABLE = {
		//begin: '\\$+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*' // Fix By Unixman
		begin: '\\$+[a-zA-Z_]+[a-zA-Z0-9_]*'
	};
	var PREPROCESSOR = {
		className: 'meta', begin: /<\?(php)?|\?>/
	};
	var STRING = {
		className: 'string',
		contains: [hljs.BACKSLASH_ESCAPE, PREPROCESSOR],
		variants: [
			{
				begin: 'b"', end: '"'
			},
			{
				begin: 'b\'', end: '\''
			},
			hljs.inherit(hljs.APOS_STRING_MODE, {illegal: null}),
			hljs.inherit(hljs.QUOTE_STRING_MODE, {illegal: null})
		]
	};
	var NUMBER = {variants: [hljs.BINARY_NUMBER_MODE, hljs.C_NUMBER_MODE]};
	return {
		aliases: ['php', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8'],
		case_insensitive: true,
		keywords:
			'include_once include require_once require ' +
			'and or xor list echo as ' +
			'switch case break endswitch if elseif else for foreach endforeach while endwhile do ' +
			'exit die empty isset try catch throw exception ' +
			'return print eval default ' +
			'enddeclare continue endfor endif declare unset ' +
			'goto insteadof yield finally ' +
			'class interface trait instanceof ' +
			'boolean bool int float binary string array object ' +
			'use abstract static private protected public final global var const self parent new clone ' +
			'true false null ' + // these are literal !
			'__DIR__ __FILE__ __LINE__ __NAMESPACE__ __CLASS__ __METHOD__ __FUNCTION__', // these are special literal !
		contains: [
			hljs.HASH_COMMENT_MODE,
			hljs.COMMENT('//', '$', {contains: [PREPROCESSOR]}),
			hljs.COMMENT(
				'/\\*',
				'\\*/',
				{
					contains: [
						{
							className: 'doctag',
							begin: '@[A-Za-z]+'
						}
					]
				}
			),
			hljs.COMMENT(
				'__halt_compiler.+?;',
				false,
				{
					endsWithParent: true,
					keywords: '__halt_compiler',
					lexemes: hljs.UNDERSCORE_IDENT_RE
				}
			),
			{
				className: 'string',
				begin: /<<<['"]?\w+['"]?$/, end: /^\w+;?$/,
				contains: [
					hljs.BACKSLASH_ESCAPE,
					{
						className: 'subst',
						variants: [
							{begin: /\$\w+/},
							{begin: /\{\$/, end: /\}/}
						]
					}
				]
			},
			PREPROCESSOR,
			{
			//	className: 'keyword', begin: /\$this\b/ // Fix By Unixman
				className: 'variable', begin: /\$[a-zA-Z0-9_]+\b/
			},
			VARIABLE,
			{
				// swallow composed identifiers to avoid parsing them as keywords
				begin: /(::|->)+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/
			},
			{
				className: 'function',
				beginKeywords: 'function', end: /[;{]/, excludeEnd: true,
				illegal: '\\$|\\[|%',
				contains: [
					hljs.UNDERSCORE_TITLE_MODE,
					{
						className: 'params',
						begin: '\\(', end: '\\)',
						contains: [
							'self',
							VARIABLE,
							hljs.C_BLOCK_COMMENT_MODE,
							STRING,
							NUMBER
						]
					}
				]
			},
			{
				className: 'class',
				beginKeywords: 'class interface trait', end: '{', excludeEnd: true,
				illegal: /[:\(\$"]/,
				contains: [
					{beginKeywords: 'extends implements'},
					hljs.UNDERSCORE_TITLE_MODE
				]
			},
			{
				className: 'operator',
				begin: /\buse|abstract|static|private|protected|public|final|global|var|const|self|parent|new|clone\b/
			},
			{
				className: 'literal',
				begin: /\btrue|false|null|__DIR__|__FILE__|__LINE__|__NAMESPACE__|__CLASS__|__METHOD__|__FUNCTION__\b/
			},
			{
				beginKeywords: 'namespace', end: ';',
				illegal: /[\.']/,
				contains: [hljs.UNDERSCORE_TITLE_MODE]
			},
			{
				beginKeywords: 'use', end: ';',
				contains: [hljs.UNDERSCORE_TITLE_MODE]
			},
			{
				begin: '=>' // No markup, just a relevance booster
			},
			STRING,
			NUMBER
		]
	};
}
);

// #END
