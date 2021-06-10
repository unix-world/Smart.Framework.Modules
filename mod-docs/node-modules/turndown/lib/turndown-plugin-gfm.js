
// turndown-plugin-gfm.js v.1.0.1 # https://github.com/domchristie/turndown-plugin-gfm
// License: MIT, (c) 2017 Dom Christie

// modified by unixman, (c) 2021 unix-world.org
// this is a hybrid js that works on both: browser and nodejs
// v.20210607 :: STABLE

var turndownPluginGfm = (function (exports) {
'use strict';

var highlightRegExp = /highlight-(?:text|source)-([a-z0-9]+)/;
var alignLine = '---';
var tblDef = '{!DEF!=AUTO-WIDTH;ALIGN-HEAD-CENTER;ALIGN-AUTO;.bordered;.stripped;.doc-table}';

function highlightedCodeBlock (turndownService) {
	turndownService.addRule('highlightedCodeBlock', {
		filter: function (node) {
			var firstChild = node.firstChild;
			return (
				node.nodeName === 'DIV' &&
				highlightRegExp.test(node.className) &&
				firstChild &&
				firstChild.nodeName === 'PRE'
			)
		},
		replacement: function (content, node, options) {
			var className = node.className || '';
			var language = (className.match(highlightRegExp) || [null, ''])[1];

			return (
				'\n\n' + options.fence + language + '\n' +
				node.firstChild.textContent +
				'\n' + options.fence + '\n\n'
			)
		}
	});
}

function strikethrough (turndownService) {
	turndownService.addRule('strikethrough', {
		filter: ['del', 's', 'strike'],
		replacement: function (content) {
			return '~' + content + '~'
		}
	});
}

var indexOf = Array.prototype.indexOf;
var every = Array.prototype.every;
var rules = {};

var rowNumber = -1;

rules.tableCell = {
	filter: ['th', 'td'],
	replacement: function (content, node) {
		return cell(content, node)
	}
};

rules.tableRow = {
//	filter: 'tr',
	filter: function (node) {
		var isRow = (node.nodeName === 'TR');
		if(isRow) {
			rowNumber++;
		}
		return isRow;
	},
	replacement: function (content, node) {
		var borderCells = '';
	//	var alignMap = { left: ':--', right: '--:', center: ':-:' }; // cannot use this, too complicated as now is using the `alignLine` as reference in other places ...
		if(isHeadingRow(node)) {
			for (var i = 0; i < node.childNodes.length; i++) {
				var border = alignLine;
			//	var align = ''; // (node.childNodes[i].getAttribute('align') || '').toLowerCase();
			//	if(align) {
			//		border = alignMap[align] || border;
			//	}
				borderCells += cell(border, node.childNodes[i], true);
			}
		}
		return '\n' + content + (borderCells ? '\n' + borderCells : '');
	}
};

rules.table = {
	// Only convert tables with a heading row.
	// Tables with no heading row are kept using `keep` (see below).
	filter: function (node) {
		if(node.nodeName === 'TABLE') {
			rowNumber = -1; // reset on each table
		}
		return node.nodeName === 'TABLE'; // && isHeadingRow(node.rows[0]) // fix by unixman: as there is already a fix to consider the first row as table heading, this condition is no more necessary
	},

	replacement: function (content) {
		// Ensure there are no blank lines
		content = content.replace('\n\n', '\n');
		return '\n\n' + content + '\n\n'
	}
};

rules.tableSection = {
	filter: ['thead', 'tbody', 'tfoot'],
	replacement: function (content) {
		return content
	}
};

// A tr is a heading row if:
// - the parent is a THEAD
// - or if its the first child of the TABLE or the first TBODY (possibly
//   following a blank THEAD)
// - and every cell is a TH
function isHeadingRow (tr) {
	return (rowNumber === 0); // fix by unixman: because on nodejs there is no rowIndex, and more, a table can have multiple headings, will use the first row as heading !!
	/*
	var parentNode = tr.parentNode;
	return (
		parentNode.nodeName === 'THEAD' ||
		parentNode.nodeName === 'TBODY' || // fix by unixman, was not processing tables missing thead ...
		(
			parentNode.firstChild === tr &&
			(parentNode.nodeName === 'TABLE' || isFirstTbody(parentNode)) &&
			every.call(tr.childNodes, function (n) { return n.nodeName === 'TH' })
		)
	)
	*/
}

/*
function isFirstTbody (element) {
	var previousSibling = element.previousSibling;
	return (
		element.nodeName === 'TBODY' && (
			!previousSibling ||
			(
				previousSibling.nodeName === 'THEAD' &&
				/^\s*$/i.test(previousSibling.textContent)
			)
		)
	)
}
*/

function cell (content, node, isRow) {
	var index = indexOf.call(node.parentNode.childNodes, node);
	var colspan = node.getAttribute('colspan') || '';
	var separator = '| ';
	if(
		(node.nodeName === 'TH') ||
		(node.nodeName === 'TD') // added by unixman for those tables that have no header at all
	) {
		if(!!isHeadingRow(node) && (index === 0)) {
			if(content !== alignLine) {
				separator = '\n:::\n:::\n' + '|' + tblDef + ' '; // fix by unixman ; add a div before table to avoid the situation to corrupt the table if the line above is not an empty line ; actually was enough a newline only but is too complicated, turndown trims the output somewhere ...
			}
		}
	}
	if(Math.round(colspan) > 0) {
		if(content !== alignLine) {
			content = content + ' {T: @colspan=' + Math.round(colspan) + '} ';
		}
	}
	var prefix = ' ';
	if (index === 0) {
		prefix = separator;
	}
	return prefix + (content.replace(/\\(\n)+/g, ' ').replace(/(\r\n|\r|\n|\t)+/g, ' ')) + ' |';
}

function tables (turndownService) {
	turndownService.keep(function (node) {
	//	return node.nodeName === 'TABLE' && !isHeadingRow(node.rows[0]);
	return node.nodeName === 'TABLE' && parentNode.nodeName !== 'THEAD'; // fix by unixman: as there is already a fix to consider the first row as table heading, this condition is no more necessary
	});
	for (var key in rules) turndownService.addRule(key, rules[key]);
}

function taskListItems (turndownService) {
	turndownService.addRule('taskListItems', {
		filter: function (node) {
			return node.type === 'checkbox' && node.parentNode.nodeName === 'LI'
		},
		replacement: function (content, node) {
			return (node.checked ? '[x]' : '[ ]') + ' '
		}
	});
}

function gfm (turndownService) {
	turndownService.use([
		highlightedCodeBlock,
		strikethrough,
		tables,
		taskListItems
	]);
}

exports.gfm = gfm;
exports.highlightedCodeBlock = highlightedCodeBlock;
exports.strikethrough = strikethrough;
exports.tables = tables;
exports.taskListItems = taskListItems;

return exports;

}({}));

if(typeof(module) !== 'undefined' && module.exports) { // nodejs
	module.exports = turndownPluginGfm;
} else {
	window.turndownPluginGfm = turndownPluginGfm;
}

// #END
