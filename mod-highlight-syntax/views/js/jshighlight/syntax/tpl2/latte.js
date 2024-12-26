/*
Language: Latte (nette)
Requires: xml.js
Author: Radu Ovidiu I. <iradu@unix-world.org>
Description: Latte is a templating language for PHP (from Nette framework)
Category: template
### created by unixman
*/

// syntax/tpl/latte.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('latte',
function(hljs) {
	var PARAMS = {
		className: 'params',
		begin: '\\(', end: '\\)'
	};

	var FUNCTION_NAMES = 'dump expand cache spaceless include php syntax l r contentType debugbreak block define import layout extends ifset';

	var FUNCTIONS = {
		beginKeywords: FUNCTION_NAMES,
		keywords: {name: FUNCTION_NAMES},
		relevance: 0,
		contains: [
			PARAMS
		]
	};

	var FILTER = {
		begin: /\|[A-Za-z_]+:?/,
		keywords:
			'noescape breaklines bytes capitalize datastream date escapecss escapehtml escapehtmlcomment escapeical escapejs escapeurl escapexml ' +
			'firstupper checkurl implode indent length lower nl2br number padleft padright repeat replace replacere reverse safeurl strip ' +
			'striphtml striptags substr trim truncate upper', // 'webalize' is disabled
		contains: [
			FUNCTIONS
		]
	};

	var TAGS = 	'if else ifset elseifset switch case default ' + // conditions
				'for foreach while continueIf breakIf first last sep ' + // loops
				'var capture'; // variables ('default' is already defined above)


	TAGS = TAGS + ' ' + TAGS.split(' ').map(function(t){return 'end' + t}).join(' ');

	return {
		aliases: ['nettetpl'],
		case_insensitive: true,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\{\*/, /\*}/),
			{
				className: 'template-variable',
				begin: /\{\$([a-zA-Z0-9])+/, end: /}/,
				contains: ['self', FILTER, FUNCTIONS]
			},
			{
				//className: 'template-tag',
				className: 'symbol',
				begin: /\{\/?/, end: /}/,
				contains: [
					{
						className: 'name',
						begin: /([a-zA-Z0-9])+/,
						keywords: TAGS,
						starts: {
							endsWithParent: true,
							contains: [FILTER, FUNCTIONS],
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
