/*
Language: Dust
Requires: xml.js
Author: Michael Allen <michael.allen@benefitfocus.com>
Description: Matcher for dust templates.
Category: template
### modified by unixman
*/

// syntax/tpl/dust.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('dust',
function(hljs) {
	var EXPRESSION_KEYWORDS = 'if else'; // eq ne lt lte gt gte select default math sep
	return {
		aliases: ['dst'],
		case_insensitive: true,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\{\!/, /\!}/), // fix by unixman
			{ // sub-tpl
				className: 'title',
				begin: /\{[\>|\<]/,
				end: /\/?\}/,
				illegal: /\n/
			},
			{ // syntax
				className: 'symbol',
				begin: /\{[\#\/@\:\?\^\+]/,
				end: /\}/,
				illegal: /\n/,
				contains: [
					{
						className: 'symbol',
						begin: /[a-zA-Z\.\-_]+/,
						starts: {
							endsWithParent: true,
							relevance: 0,
							contains: [ hljs.QUOTE_STRING_MODE ]
						},
						keywords: EXPRESSION_KEYWORDS
					}
				],
			},
			{ // variable
				begin: /\{/, end: /\}/,
				illegal: /\n/,
				contains: [
					{
						className: 'keyword',
						begin: /[a-zA-Z0-9_\.]+/,
					//	returnEnd: true
					},
					{
						className: 'regexp',
						end: /(\|[a-z0-9]+)*/,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					}
				]
			}
		]
	};
}
);

// #END
