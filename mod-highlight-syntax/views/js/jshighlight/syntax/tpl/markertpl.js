/*
Language: Marker-TPL (Smart.Framework) v.20210610.1720
Requires: xml.js
Author: unix-world.org
Description: Marker-TPL is a templating language for PHP and Javascript built into Smart.Framework
Category: template
*/

// syntax/tpl/markertpl.js
// HighlightJs: v.9.18.5

hljs.registerLanguage('markertpl',
function(hljs) {

	var SYNTAX = 'IF LOOP ELSE';

	return {
		aliases: ['markertpl','markerstpl','smartframeworktpl','markup'],
		case_insensitive: false,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\[%%%COMMENT%%%\]/, /\[%%%\/COMMENT%%%\]/),
			{ // syntax: if, loop, specials (space, tab, new line, carriage return, left/right square bracket)
				className: 'meta',
				begin: /\[%%%/, end: /%%%\]/,
				contains: [
					{
						className: 'variable',
						begin: /([\/a-zA-Z0-9_\-\.]+)/, // {{{SYNC-TPL-EXPR-SPECIALS}}}
					},
					{ // {{{SYNC-TPL-EXPR-IF}}} {{{SYNC-TPL-EXPR-LOOP}}}
						className: 'string',
						begin: /[\|a-zA-Z0-9_\-\.\:]+/,
						contains: [
							{ // {{{SYNC-TPL-EXPR-IF}}}
								className: 'operator', // tag
								begin: /\:(@\=\=|@\!\=|@\<\=|@\<|@\>\=|@\>|\=\=|\!\=|\<\=|\<|\>\=|\>|\!%|%|\!\?|\?|\^~|\^\*|&~|&\*|\$~|\$\*){1,3}/,
							},
							{ // {{{SYNC-TPL-EXPR-IF}}}
								className: 'string',
								begin: /([^\[\]]*);/,
							},
						],
					},
					{
						className: 'meta',
						begin: /(\([0-9]+\))/,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					},
				]
			},
			{ // sub-template
				className: 'meta',
				begin: /\[@@@/, end: /@@@\]/,
				contains: [
					{
						className: 'section',
						begin: /([A-Z\-]+\:){1}/,
					},
					{ // {{{SYNC-TPL-EXPR-SUBTPL}}}
						className: 'string',
						begin: /([a-zA-Z0-9_\-\.\/\!\?%]+)/,
						contains: [
							{
								className: 'symbol',
								begin: /(\|[a-z0-9\-]+)+/,
								starts: {
									endsWithParent: true,
									relevance: 0
								},
							}
						],
						starts: {
							endsWithParent: true,
							relevance: 0
						},
					},
				]
			},
			{ // markers
				className: 'meta',
				begin: /\[###/, end: /###\]/,
				contains: [
					{
						className: 'keyword',
						begin: /([A-Z0-9_\-\.]+)/,
						contains: [
							{
								className: 'symbol',
								begin: /(\|[a-z0-9]+)+/,
								starts: {
									endsWithParent: true,
									relevance: 0
								},
							}
						],
						starts: {
							endsWithParent: true,
							relevance: 0
						},
					},
				]
			},
			{ // placeholders
				className: 'title',
				begin: /\[\:\:\:/, end: /\:\:\:\]/,
				contains: [
					{
						className: 'keyword',
						begin: /[A-Z0-9_\-\.]+/,
						returnEnd: true
					}
				]
			}
		]
	};
}
);

// #END
