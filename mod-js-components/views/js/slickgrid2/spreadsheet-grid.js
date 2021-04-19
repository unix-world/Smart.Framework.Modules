
// basic Spreadsheet Implementation over SlickGrid
// Depends: jQuery ; smartJ$Utils
// (c) 2017-2021 unix-world.org


var SlickSpreadSheet = new function() { // START CLASS :: v.20210411

	// :: static

	var _class = this; // self referencing


	this.csvDataDownload = function(data) {
		//--
		var csv = _class.csvDataExport(data);
		//console.log(csv);
		var pom = document.createElement('a');
		var blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
		var wURL = window.URL || window.webkitURL;
		var xURL = wURL.createObjectURL(blob);
		pom.href = xURL;
		pom.setAttribute('download', 'data-table.csv');
		document.body.appendChild(pom);
		pom.click();
		setTimeout(function(){
			document.body.removeChild(pom);
			URL.revokeObjectURL(xURL);
		}, 100);
		//--
	} //END FUNCTION


	this.csvDataExport = function(data) {
		//--
		var i, j, row, ok, lastNonEmptyLtr;
		var expCsv = [];
		var lLine = 0;
		for(i=0; i<data.length; i++) {
			ok = 0;
			lastNonEmptyLtr = ''
			for(j in data[i]) {
				if(j != '#') {
					if(data[i][j]) {
						ok++;
						lastNonEmptyLtr = String(j); // get the last non-empty letter to stop after
					} //end if
				} //end if
			} //end for
			if(ok) { // if non-empty row
				lLine = i;
				row = [];
				for(j in data[i]) {
					if(j != '#') {
						row.push('"' + String(data[i][String(j)]).replace(/"/g,'""') + '"');
						if(String(lastNonEmptyLtr) === String(j)) {
							break; // stop after last non-empty column
						} //end if
					} //end if
				} //end for
				expCsv.push(String(row.join(',')));
			} else { // for empty row just put the first cell
				expCsv.push('""'); // empty line, so add just one field as ""
			} //end if else
		} //end for
		//--
		data = null; // free mem
		//--
		var expStrCsv = '';
		for(i=0; i<expCsv.length; i++) {
			expStrCsv = expStrCsv + String(expCsv[i]) + "\n";
			if(lLine == i) {
				break; // stop after last non-empty row
			} //end if
		} //end for
		expCsv = null; // free mem
		//--
		//console.log(expCsv);
		return String(expStrCsv);
		//--
	} //END FUNCTION


	this.xCellFormulaCalculator = function(cellVal, data) {
		//--
		var bkp = cellVal;
		//--
		if(!cellVal) {
			return String(bkp);
		} //end if
		//--
		if(String(cellVal).substr(0,1) != '=') {
			return String(bkp);
		} //end if
		//--
		//var formula = cellVal.substring(1);
		try {
			var formula = excelFormulaUtilities.formula2JavaScript(cellVal);
		} catch(err) {
			//console.log('XCell Formula Failed: ' + formula + ' with Error: ' + err);
			return String(bkp);
		} //end try catch
		//console.log(formula);
		if(!formula) {
			return String(bkp);
		} //end if
		var vrs = formula.match(/[A-Z0-9]+/g);
		if(!vrs) {
			return String(bkp);
		} //end if
		var calcFormulaResult = NaN;
		var expr = 'calcFormulaResult = (function(){' + "\n";
		//console.log(vrs);
		for(var i=0; i<vrs.length; i++) {
			var vrxi = String(vrs[i]);
			var ltt = vrxi.match(/[A-Z]+/g);
			var idx = vrxi.match(/[0-9]+/g);
			if(ltt && idx) {
				expr = expr + 'var ' + vrxi + ' = parseFloat(_class.xCellFormulaCalculator(String(data["' + (parseInt(idx[0])-1) + '"]["' + String(ltt[0]) + '"]' + '),data));' + "\n";
			} //end if
		} //end for
		expr = expr + 'return ' + formula + ';' + "\n";
		expr = expr + '})();';
		//console.log(expr);
		try {
			eval(expr);
		} catch(err) {
			//console.log('Eval the XCell Formula Failed: ' + formula + ' with Error: ' + err);
			return String(bkp);
		} //end try catch
		//console.log(calcFormulaResult);
		cellVal = calcFormulaResult;
		//--
		return String(cellVal);
		//--
	} //END FUNCTION


	this.getCharColumnNameFromNumber = function(columnNumber) {
		//--
		var dividend = columnNumber;
		var columnName = "";
		var modulo;
		//--
		while(dividend > 0) {
			modulo = (dividend - 1) % 26;
			columnName = String.fromCharCode(65 + modulo).toString() + columnName;
			dividend = parseInt((dividend - modulo) / 26);
		} //end while
		//--
		return String(columnName);
		//--
	} //END FUNCTION


	this.buildSpreadsheetColumns = function(maxNum) {
		//--
		// maxNum: A-Z is 26 ; A-BZ is 78 ; A-DZ is 130
		//--
		if(!maxNum) {
			maxNum = 0;
		} //end if
		maxNum = parseInt(maxNum);
		if(!smartJ$Utils.isFiniteNumber(maxNum)) {
			maxNum = 0;
		} //end if
		if(maxNum < 26) {
			maxNum = 26;
		} else if(maxNum > 130) {
			maxNum = 130;
		} //end if
		//--
		var headColumns = [];
		var gridColumns = [];
		var maxLttrs = maxNum;
		for(var i=0; i<(maxLttrs+1); i++) {
			var theColName = '';
			if(i === 0) {
				theColName = '#';
				gridColumns.push({
					id: theColName,
					name: theColName,
					field: theColName,
					width: 50,
					formatter: _class.displaySpreadsheetIdx
				});
			} else {
				theColName = String(_class.getCharColumnNameFromNumber(i));
				gridColumns.push({
					id: theColName,
					name: theColName,
					field: theColName,
					width: 75,
					formatter: _class.displaySpreadsheetCell,
					editor: FormulaEditor,
					behavior: 'select'
				});
			} //end if else
			headColumns[i] = theColName;
		} //end for
		//--
		return {
			headColumns: headColumns,	// header columns registry
			gridColumns: gridColumns 	// (slick) grid columns
		};
		//--
	} //END FUNCTION


	this.buildSpreadsheetDataRows = function(headColumns, importData) {
		//--
		data = [];
		//--
		for(var j=0; j<10000; j++) {
			//--
			var d = {};
			//--
			for(var i=0; i<headColumns.length; i++) {
				headColumns[i] = String(headColumns[i]);
				if(i === 0) {
					d[headColumns[i]] = j;
				} else {
					d[headColumns[i]] = '';
				} //end if else
			} //end for
			//--
			data[j] = d;
			//--
		} //end for
		//--
		if(importData) {
			//--
			//console.log(importData);
			if(!jQuery.csv) {
				console.error('SpreadSheet Build Data Rows / Import Data: jquery-csv library not found');
				return;
			} //end if
			//--
			var csv = jQuery.csv.toArrays(importData);
			//console.log(csv);
			var errs = 0;
			if(csv instanceof Array) {
				for(var i=0; i<csv.length; i++) {
					var line = csv[i];
					if(line instanceof Array) {
						for(var j=0; j<line.length; j++) {
							var colRealName = String(_class.getCharColumnNameFromNumber(j+1));
							if(colRealName) {
								if(line[j]) { // if non-empty data
									try {
										data[i][colRealName] = String(line[j]);
									} catch(e){
										errs++;
									} //end try catch
								} //end if
							} else {
								errs++;
							} //end if
						} //end for
					} else {
						errs++;
					} //end if
				} //end for
			} else {
				console.error('SpreadSheet Build Data Rows / Import Data: CSV Import Failed');
			} //end if else
			//--
			if(errs) {
				console.error('SpreadSheet Build Data Rows / Import Data: CSV / Some Errors Encountered: #' + errs);
			} //end if
			//--
		} //end if
		//--
		return data;
		//--
	} //END FUNCTION


	this.displaySpreadsheetIdx = function(row, cell, value, columnDef, dataContext) {
		//--
		return '<div style="background:#ECECEC;color:#333333;font-size:0.925em;font-weight:bold;text-align:center;cursor:context-menu;">' + smartJ$Utils.escape_html(String(value+1)) + '</div>';
		//--
	} //END FUNCTION


	this.displaySpreadsheetCell = function(row, cell, value, columnDef, dataContext) {
		//--
		var xval = '';
		var cssXtra = '';
		//--
		try {
			xval = _class.xCellFormulaCalculator(value, data);
		} catch(err) {
			cssXtra = 'color:#FF3300!important;';
		} //end catch
		//--
		if(jQuery.isNumeric(xval)) {
			cssXtra = 'text-align:right;';
		} //end if
		if(xval == value) {
			return '<div style="background:#FFFFFF;cursor:cell;' + cssXtra + '">' + smartJ$Utils.escape_html(String(xval)) + '</div>';
		} else {
			return '<div style="background:#DDEEFF;font-weight:bold;cursor:cell;' + cssXtra + '" title="' + smartJ$Utils.escape_html(String(value)) + '">' + smartJ$Utils.escape_html(String(xval)) + '</div>';
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS

// #END

