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
	var EXPRESSION_KEYWORDS = 'if eq ne lt lte gt gte select default math sep';
	return {
		aliases: ['dst'],
		case_insensitive: true,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\{\!/, /\!}/), // fix by unixman
			{
			//	className: 'template-tag',
				className: 'symbol',
				begin: /\{[\#\/@\:\<\>\?\^\+]/, end: /\}/, illegal: /;/,
				contains: [
					{
						className: 'name',
						begin: /[a-zA-Z\.\-_]+/,
						starts: {
							endsWithParent: true,
							relevance: 0,
							contains: [ hljs.QUOTE_STRING_MODE ]
						}
					}
				]
			},
			{
				className: 'template-variable',
				begin: /\{/, end: /\}/, illegal: /;/,
				keywords: EXPRESSION_KEYWORDS
			}
		]
	};
}
);

// #END
