/*
Language: Shell Session
Requires: bash.js
Author: TSUYUSATO Kitsune <make.just.on@gmail.com>
Category: common
*/

// syntax/lnx/shell.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('shell',
function(hljs) {
	return {
		aliases: ['console'],
		contains: [
			{
				className: 'meta',
				begin: '^\\s{0,3}[\\w\\d\\[\\]()@-]*[>%$#]',
				starts: {
					end: '$', subLanguage: 'bash'
				}
			}
		]
	}
}
);

// #END
