
// Excel Formula Parser
// (c) 2019 unix-world.org
// License: GPLv3
// v.20210411

/*
 * This code is ispired from: excel-formula.js
 * excelFormulaUtilitiesJS, License: MIT, (c) 2011, Josh Bennett # https://github.com/joshatjben/excelFormulaUtilitiesJS/
 * Some functionality based off of the jquery core lib, License: MIT, (c) 2011, John Resig # http://jquery.org
 * Based on Ewbi's Go Calc Prototype Excel Formula Parser # http://ewbi.blogs.com/develops/2004/12/excel_formula_p.html
 */

var excelFormulaParser = new function() { // START CLASS :: v.20190205

	// :: static


	this.parseFormula = function(formula) {
		//--
		formula = String(smartJ$Utils.stringTrim(formula));
		formula = smartJ$Utils.stringReplaceAll(' ', '', formula); // replace all spaces
		//--
		if(formula.substr(0,1) != '=') {
			return String(formula);
		} //end if
		//--
		var fixFormula = formula.substr(1);
		//console.log(fixFormula);
		if(!fixFormula) {
			return String(formula);
		} //end if
		var regexFormula = /[\=A-Z0-9\:\+\-\*\/\,\(\)]+/g;
		if(!fixFormula.match(regexFormula)) {
			return String(formula);
		} //end if
		//--
		var regexRanges = /[A-Z]+[0-9]+:[A-Z]+[0-9]+/g;
		var elements = smartJ$Utils.stringRegexMatchAll(fixFormula, regexRanges);
		if(elements.length) {
		//	console.log(JSON.stringify(elements));
			var fixedRange = '';
			for(var i=0; i<elements.length; i++) {
				fixedRange = breakOutRanges(elements[i], ',');
				fixFormula = fixFormula.replace(elements[i], fixedRange);
			} //end for
		} //end if
		//--
		//console.log(formula, '#####', fixFormula);
		return String(fixFormula);
		//--
	} //END FUNCTION


	// Pass a range such as A1:B2 along with a delimiter to get back a full list of ranges.
	// Example: breakOutRanges('A1:B2', '+'); // returns: A1+A2+B1+B2
	function breakOutRanges(rangeStr, delimStr) {

		if(!rangeStr) {
			return '';
		}
		if(!delimStr) {
			return String(rangeStr);
		} //end if

		rangeStr = String(rangeStr);

		//Quick Check to see if if rangeStr is a valid range
		if(!RegExp('[a-z]+[0-9]+:[a-z]+[0-9]+','gi').test(rangeStr)) {
			//console.error('breakOutRanges: This is not a valid range: ' + rangeStr);
			return String(rangeStr);
		} //end if

		//Make the rangeStr lowercase to deal with looping.
		var range = rangeStr.split(':'),

		startRow = parseInt(range[0].match(/[0-9]+/gi)[0]),
		startCol = range[0].match(/[A-Z]+/gi)[0],
		startColDec = fromBase26(startCol)

		endRow =  parseInt(range[1].match(/[0-9]+/gi)[0]),
		endCol = range[1].match(/[A-Z]+/gi)[0],
		endColDec = fromBase26(endCol),

		// Total rows and cols
		totalRows = endRow - startRow + 1,
		totalCols = fromBase26(endCol) - fromBase26(startCol) + 1,

		// Loop vars
		curCol = 0,
		curRow = 1 ,
		curCell = '',

		//Return String
		retStr = '';

		for(; curRow <= totalRows; curRow+=1) {
			for(; curCol < totalCols; curCol+=1) {
				// Get the current cell id
				curCell = toBase26(startColDec + curCol) + '' + (startRow + curRow - 1) ;
				retStr += curCell + (curRow === totalRows && curCol === totalCols-1 ? '' : delimStr);
			} //end for
			curCol = 0;
		} //end for

		return String(retStr);

	}; //END FUNCTION


	//Modified from function at http://en.wikipedia.org/wiki/Hexavigesimal
	var toBase26 = function(value) {
		//--
		value = Math.abs(value);
		//--
		var converted = '';
		var iteration = false;
		var remainder;
		// Repeatedly divide the numerb by 26 and convert the remainder into the appropriate letter.
		do {
			remainder = value % 26;
			// Compensate for the last letter of the series being corrected on 2 or more iterations.
			if(iteration && value < 25) {
				remainder--;
			} //end if
			converted = String.fromCharCode((remainder + 'A'.charCodeAt(0))) + converted;
			value = Math.floor((value - remainder) / 26);
			iteration = true;
		} while(value > 0);
		//--
		return String(converted);
		//--
	} //END FUNCTION


	// Pass in the base 26 string, get back integer # This was Modified from a function at http://en.wikipedia.org/wiki/Hexavigesimal
	var fromBase26 = function(number) {
		//--
		number = number.toUpperCase();
		//--
		var s=0, i=0, dec=0;
		if((number !== null) && (typeof number !== 'undefined') && (number.length > 0)) {
			for(; i < number.length; i++) {
				s = number.charCodeAt(number.length - i - 1) - 'A'.charCodeAt(0);
				dec += (Math.pow(26, i)) * (s+1);
			} //end for
		} //end if
		//--
		return dec - 1;
		//--
	} //END FUNCTION


} //END CLASS


// #END
