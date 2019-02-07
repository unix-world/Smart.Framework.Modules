
// Area Calculator
// (c) 2019 unix-world.org
// License: GPLv3
// v.20190207

// DEPENDS: bc-math.js

//==================================================================
//==================================================================


var GeometryAreaCalculator = new function() { // START CLASS :: v.171002

	// :: static

	var _class = this; // self referencing


	var theNumAreas = 0;
	var theGrandTotal = 0;


	var updateGrandTotal = function(result) {
	//	theGrandTotal += parseFloat(result);
	//	theGrandTotal = Math.round(100 * theGrandTotal) / 100; // format with 2 decimals
		theGrandTotal = BC_Math.bcadd(theGrandTotal, result, 2);
	//	document.getElementById('result-total').innerHTML = '<h4>' + 'Total: ' + parseFloat(theGrandTotal) + '</h4>';
		document.getElementById('result-total').innerHTML = '<h4 style="font-weight:bold;">' + 'Total: ' + theGrandTotal + '</h4>';
	} //END FUNCTION


	this.calculateTriangleAreaBH = function(elemIdResult, elemIdBase, elemIdHeight) {
		//-- bind fields
		var fld1 = document.getElementById(String(elemIdBase));
		var fld2 = document.getElementById(String(elemIdHeight));
		//-- read values from form fields
		var base = fld1.value;
		var height = fld2.value;
		//-- calculate
		var calc = _class.calculateGeometryArea('triangle-bs', {base:base, height:height});
		//-- display
		if(calc.status == 'OK') {
			theNumAreas++;
			document.getElementById(String(elemIdResult)).innerHTML += '' + theNumAreas + '.&nbsp;' + String(calc.html);
			updateGrandTotal(calc.result);
			fld1.value = '';
			fld2.value = '';
		} else {
			alert('Failed: ' + calc.status);
		} //end if else
		//--
	} //END FUNCTION


	this.calculateTriangleAreaHeron = function(elemIdResult, elemIdS1, elemIdS2, elemIdS3) {
		//-- bind fields
		var fld1 = document.getElementById(String(elemIdS1));
		var fld2 = document.getElementById(String(elemIdS2));
		var fld3 = document.getElementById(String(elemIdS3));
		//-- read values from form fields
		var s1 = fld1.value;
		var s2 = fld2.value;
		var s3 = fld3.value;
		//-- calculate
		var calc = _class.calculateGeometryArea('triangle-heron', {s1:s1, s2:s2, s3:s3});
		//-- display
		if(calc.status == 'OK') {
			theNumAreas++;
			document.getElementById(String(elemIdResult)).innerHTML += '' + theNumAreas + '.&nbsp;' + String(calc.html);
			updateGrandTotal(calc.result);
			fld1.value = '';
			fld2.value = '';
			fld3.value = '';
		} else {
			alert('Failed: ' + calc.status);
		} //end if else
		//--
	} //END FUNCTION


	this.calculateRectangleOrSquareArea = function(elemIdResult, elemIdBase, elemIdHeight) {
		//-- bind fields
		var fld1 = document.getElementById(String(elemIdBase));
		var fld2 = document.getElementById(String(elemIdHeight));
		//-- read values from form fields
		var base = fld1.value;
		var height = fld2.value;
		//-- calculate
		var calc = _class.calculateGeometryArea('rectangle-or-square', {base:base, height:height});
		//-- display
		if(calc.status == 'OK') {
			theNumAreas++;
			document.getElementById(String(elemIdResult)).innerHTML += '' + theNumAreas + '.&nbsp;' + String(calc.html);
			updateGrandTotal(calc.result);
			fld1.value = '';
			fld2.value = '';
		} else {
			alert('Failed: ' + calc.status);
		} //end if else
		//--
	} //END FUNCTION


	this.calculateRhombusOrSquareArea = function(elemIdResult, elemIdDiagonal1, elemIdDiagonal2) {
		//-- bind fields
		var fld1 = document.getElementById(String(elemIdDiagonal1));
		var fld2 = document.getElementById(String(elemIdDiagonal2));
		//-- read values from form fields
		var d1 = fld1.value;
		var d2 = fld2.value;
		//-- calculate
		var calc = _class.calculateGeometryArea('rhombus-or-square', {diagonal1:d1, diagonal2:d2});
		//-- display
		if(calc.status == 'OK') {
			theNumAreas++;
			document.getElementById(String(elemIdResult)).innerHTML += '' + theNumAreas + '.&nbsp;' + String(calc.html);
			updateGrandTotal(calc.result);
			fld1.value = '';
			fld2.value = '';
		} else {
			alert('Failed: ' + calc.status);
		} //end if else
		//--
	} //END FUNCTION


	this.calculateParallelogramArea = function(elemIdResult, elemIdBase, elemIdHeight) {
		//-- bind fields
		var fld1 = document.getElementById(String(elemIdBase));
		var fld2 = document.getElementById(String(elemIdHeight));
		//-- read values from form fields
		var base = fld1.value;
		var height = fld2.value;
		//-- calculate
		var calc = _class.calculateGeometryArea('parallelogram', {base:base, height:height});
		//-- display
		if(calc.status == 'OK') {
			theNumAreas++;
			document.getElementById(String(elemIdResult)).innerHTML += '' + theNumAreas + '.&nbsp;' + String(calc.html);
			updateGrandTotal(calc.result);
			fld1.value = '';
			fld2.value = '';
		} else {
			alert('Failed: ' + calc.status);
		} //end if else
		//--
	} //END FUNCTION


	this.calculateTrapezoidArea = function(elemIdResult, elemIdBase1, elemIdBase2, elemIdHeight) {
		//-- bind fields
		var fld1 = document.getElementById(String(elemIdBase1));
		var fld2 = document.getElementById(String(elemIdBase2));
		var fld3 = document.getElementById(String(elemIdHeight));
		//-- read values from form fields
		var base1 = fld1.value;
		var base2 = fld2.value;
		var height = fld3.value;
		//-- calculate
		var calc = _class.calculateGeometryArea('trapezoid', {base1:base1, base2:base2, height:height});
		//-- display
		if(calc.status == 'OK') {
			theNumAreas++;
			document.getElementById(String(elemIdResult)).innerHTML += '' + theNumAreas + '.&nbsp;' + String(calc.html);
			updateGrandTotal(calc.result);
			fld1.value = '';
			fld2.value = '';
			fld3.value = '';
		} else {
			alert('Failed: ' + calc.status);
		} //end if else
		//--
	} //END FUNCTION


	this.calculateEllipseCircleArea = function(elemIdResult, elemIdRadius1, elemIdRadius2) {
		//-- bind fields
		var fld1 = document.getElementById(String(elemIdRadius1));
		var fld2 = document.getElementById(String(elemIdRadius2));
		//-- read values from form fields
		var r1 = fld1.value;
		var r2 = fld2.value;
		//-- calculate
		var calc = _class.calculateGeometryArea('ellipse-circle', {radius1:r1, radius2:r2});
		//-- display
		if(calc.status == 'OK') {
			theNumAreas++;
			document.getElementById(String(elemIdResult)).innerHTML += '' + theNumAreas + '.&nbsp;' + String(calc.html);
			updateGrandTotal(calc.result);
			fld1.value = '';
			fld2.value = '';
		} else {
			alert('Failed: ' + calc.status);
		} //end if else
		//--
	} //END FUNCTION

	//=====


	var testNumbersAndDecimal = function(input) {
		//--
		var regexp = /^[0-9]+([.][0-9]+)?$/g;
		//--
		return regexp.test(String(input));
		//--
	} //END FUNCTION


	this.calculateGeometryArea = function(figure, obj) {

		var objResult = {
			status: 'ERROR: No Data ...',
			result: 0,
			html: ''
		};

		switch(String(figure)) {
			case 'triangle-bs': // triangle area by base and height: obj.base ; obj.height
			//	var base = parseFloat(obj.base);
			//	var height = parseFloat(obj.height);
			//	var result = Math.round(100 * ((height * base) / 2)) / 100;
				var base = String(obj.base);
				var height = String(obj.height);
				if(!testNumbersAndDecimal(base)) {
					alert('Invalid Triangle Base (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(height)) {
					alert('Invalid Triangle Height (numeric / decimals)');
					return objResult;
				} //end if
				var result = 0;
				result = BC_Math.bcmul(height, base, 2);
				result = BC_Math.bcdiv(result, 2, 2);
				var html = '';
				var metainfo = '[H='+height+' ; B='+base+']';
				var formula = 'Formula = (H * B) / 2';
				if((result <= 0) || isNaN(result)) {
					result = 0;
					html = '<div align="right" title="'+formula+'"><font color="#FF3300"><b>TRIANGLE Area is Zero or Impossible:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font></div><hr>';
				} else {
					html =  '<div align="right" title="'+formula+'"><font color="#003399"><b>Area of TRIANGLE:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font> = <font color="#FF6600"><b>'+result+'</b></font></div><hr>';
				} //end if else
				objResult.status = 'OK';
				objResult.result = result;
				objResult.html = String(html);
				break;
			case 'triangle-heron': // triangle area by heron: obj.s1 ; obj.s2 ; obj.s3
			//	var s1 = parseFloat(obj.s1);
			//	var s2 = parseFloat(obj.s2);
			//	var s3 = parseFloat(obj.s3);
			//	var heron = ((s1 + s2 + s3) / 2);
			//	var result = Math.round(100 * Math.sqrt(heron * (heron - s1) * (heron - s2) * (heron - s3))) / 100;
				var s1 = String(obj.s1);
				var s2 = String(obj.s2);
				var s3 = String(obj.s3);
				if(!testNumbersAndDecimal(s1)) {
					alert('Invalid Triangle S1 (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(s2)) {
					alert('Invalid Triangle S2 (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(s3)) {
					alert('Invalid Triangle S3 (numeric / decimals)');
					return objResult;
				} //end if
				var heron = 0
				heron = BC_Math.bcadd(s1, s2, 2);
				heron = BC_Math.bcadd(heron, s3, 2);
				heron = BC_Math.bcdiv(heron, 2, 2);
				var result = 0;
				result = BC_Math.bcmul(heron, BC_Math.bcsub(heron, s1, 2));
				result = BC_Math.bcmul(result, BC_Math.bcsub(heron, s2, 2));
				result = BC_Math.bcmul(result, BC_Math.bcsub(heron, s3, 2));
				result = BC_Math.bcsqrt(result, 2);
				var html = '';
				var metainfo = '<span style="cursor:help;" title="Calculated Heron Semi-Perimeter *HS='+heron+'">' + '[S1='+s1+' ; S2='+s2+' ; S3='+s3+']' + '</span>';
				var formula = 'HS = (S1 + S2 + S3) / 2 ; Formula = HS * (HS - S1) * (HS - S2) * (HS - S3)';
				if((result <= 0) || isNaN(result)) {
					result = 0;
					html = '<div align="right" title="'+formula+'"><font color="#FF3300"><b>TRIANGLE (Heron) Area is Zero or Impossible:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font></div><hr>';
				} else {
					html = '<div align="right" title="'+formula+'"><font color="#003399"><b>Area of TRIANGLE (Heron):</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font> = <font color="#FF6600"><b>'+result+'</b></font></div><hr>';
				} //end if else
				objResult.status = 'OK';
				objResult.result = result;
				objResult.html = String(html);
				break;
			case 'rectangle-or-square': // rectangle/square area by base and height: obj.base ; obj.height
			//	var base = parseFloat(obj.base);
			//	var height = parseFloat(obj.height);
				var base = String(obj.base);
				var height = String(obj.height);
				if(!testNumbersAndDecimal(base)) {
					alert('Invalid Rectangle / Square Base (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(height)) {
					alert('Invalid Rectangle / Square Height (numeric / decimals)');
					return objResult;
				} //end if
				var figType = 'RECTANGLE';
				if(base === height) {
					figType = 'SQUARE';
				} //end if
			//	var result = Math.round(100 * (height * base)) / 100;
				var result = 0;
				result = BC_Math.bcmul(height, base, 2);
				var html = '';
				var metainfo = '[H='+height+' ; B='+base+']';
				var formula = 'Formula = H * B';
				if((result <= 0) || isNaN(result)) {
					result = 0;
					html = '<div align="right" title="'+formula+'"><font color="#FF3300"><b>RECTANGLE/SQUARE Area is Zero or Impossible:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font></div><hr>';
				} else {
					html =  '<div align="right" title="'+formula+'"><font color="#003399"><b>Area of '+figType+':</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font> = <font color="#FF6600"><b>'+result+'</b></font></div><hr>';
				} //end if else
				objResult.status = 'OK';
				objResult.result = result;
				objResult.html = String(html);
				break;
			case 'rhombus-or-square': // rhombus or square area by the diagonals: obj.diagonal1 ; obj.diagonal2
			//	var diagonal1 = parseFloat(obj.diagonal1);
			//	var diagonal2 = parseFloat(obj.diagonal2);
				var diagonal1 = String(obj.diagonal1);
				var diagonal2 = String(obj.diagonal2);
				if(!testNumbersAndDecimal(diagonal1)) {
					alert('Invalid Rhombus / Square Diagonal1 (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(diagonal2)) {
					alert('Invalid Rhombus / Square Diagonal2 (numeric / decimals)');
					return objResult;
				} //end if
				var figType = 'RHOMBUS';
				if(diagonal1 === diagonal2) {
					figType = 'SQUARE';
				} //end if
			//	var result = Math.round(100 * ((parseFloat(diagonal1) * parseFloat(diagonal2)) / 2)) / 100;
				var result = 0;
				result = BC_Math.bcmul(diagonal1, diagonal2, 2);
				result = BC_Math.bcdiv(result, 2, 2);
				var html = '';
				var metainfo = '[D1='+diagonal1+' ; D2='+diagonal2+']';
				var formula = 'Formula = (D1 * D2) / 2';
				if((result <= 0) || isNaN(result)) {
					result = 0;
					html = '<div align="right" title="'+formula+'"><font color="#FF3300"><b>RHOMBUS/SQUARE Area is Zero or Impossible:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font></div><hr>';
				} else {
					html =  '<div align="right" title="'+formula+'"><font color="#003399"><b>Area of '+figType+':</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font> = <font color="#FF6600"><b>'+result+'</b></font></div><hr>';
				} //end if else
				objResult.status = 'OK';
				objResult.result = result;
				objResult.html = String(html);
				break;
			case 'parallelogram': // parallelogram area by base and height: obj.base ; obj.height
			//	var base = parseFloat(obj.base);
			//	var height = parseFloat(obj.height);
			//	var result = Math.round(100 * (parseFloat(base) * parseFloat(height))) / 100;
				var base = String(obj.base);
				var height = String(obj.height);
				if(!testNumbersAndDecimal(base)) {
					alert('Invalid Parallelogram Base (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(height)) {
					alert('Invalid Parallelogram Height (numeric / decimals)');
					return objResult;
				} //end if
				var result = 0;
				result = BC_Math.bcmul(base, height, 2);
				var html = '';
				var metainfo = '[H='+height+' ; B='+base+']';
				var formula = 'Formula = H * B';
				if((result <= 0) || isNaN(result)) {
					result = 0;
					html = '<div align="right" title="'+formula+'"><font color="#FF3300"><b>PARALLELOGRAM Area is Zero or Impossible:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font></div><hr>';
				} else {
					html =  '<div align="right" title="'+formula+'"><font color="#003399"><b>Area of PARALLELOGRAM:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font> = <font color="#FF6600"><b>'+result+'</b></font></div><hr>';
				} //end if else
				objResult.status = 'OK';
				objResult.result = result;
				objResult.html = String(html);
				break;
			case 'trapezoid': // trapezoid area by base1, base2 and height: obj.base1 ; obj.base2 ; obj.height
			//	var base1 = parseFloat(obj.base1);
			//	var base2 = parseFloat(obj.base2);
			//	var height = parseFloat(obj.height);
			//	var result = Math.round(100 * (((parseFloat(base1) + parseFloat(base2)) * parseFloat(height)) / 2)) / 100;
				var base1 = String(obj.base1);
				var base2 = String(obj.base2);
				var height = String(obj.height);
				if(!testNumbersAndDecimal(base1)) {
					alert('Invalid Trapezoid Base1 (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(base2)) {
					alert('Invalid Trapezoid Base2 (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(height)) {
					alert('Invalid Trapezoid Height (numeric / decimals)');
					return objResult;
				} //end if
				var result = 0;
				result = BC_Math.bcadd(base1, base2, 2);
				result = BC_Math.bcmul(result, height, 2);
				result = BC_Math.bcdiv(result, 2, 2);
				var html = '';
				var metainfo = '[B1='+base1+' ; B2='+base2+' ; H='+height+']';
				var formula = 'Formula = ((B1 + B2) * H) / 2';
				if((result <= 0) || isNaN(result)) {
					result = 0;
					html = '<div align="right" title="'+formula+'"><font color="#FF3300"><b>TRAPEZOID Area is Zero or Impossible:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font></div><hr>';
				} else {
					html = '<div align="right" title="'+formula+'"><font color="#003399"><b>Area of TRAPEZOID:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font> = <font color="#FF6600"><b>'+result+'</b></font></div><hr>';
				} //end if else
				objResult.status = 'OK';
				objResult.result = result;
				objResult.html = String(html);
				break;
			case 'ellipse-circle':
			//	var r1 = parseFloat(obj.radius1);
			//	var r2 = parseFloat(obj.radius2);
				var r1 = String(obj.radius1);
				var r2 = String(obj.radius2);
				if(!testNumbersAndDecimal(r1)) {
					alert('Invalid Ellipse/Circle R1 (numeric / decimals)');
					return objResult;
				} //end if
				if(!testNumbersAndDecimal(r2)) {
					alert('Invalid Ellipse/Circle R2 (numeric / decimals)');
					return objResult;
				} //end if
				var figType = 'ELLIPSE';
				if(r1 === r2) {
					figType = 'CIRCLE';
				} //end if
			//	var result = Math.round(100 * (Math.PI * parseFloat(r1) * parseFloat(r2))) / 100;
				var result = 0;
				result = BC_Math.bcmul(r1, r2, 2);
				result = BC_Math.bcmul(result, Math.PI, 2);
				var html = '';
				var metainfo = '[R1='+r1+' ; R2='+r2+']';
				var formula = 'Pi = 3.141592653589793238; Formula = Pi * (R1 * R2)';
				if((result <= 0) || isNaN(result)) {
					result = 0;
					html = '<div align="right" title="'+formula+'"><font color="#FF3300"><b>ELLIPSE/CIRCLE Area is Zero or Impossible:</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font></div><hr>';
				} else {
					html =  '<div align="right" title="'+formula+'"><font color="#003399"><b>Area of '+figType+':</b></font> <font color="#778899"><b><i>'+metainfo+'</i></b></font> = <font color="#FF6600"><b>'+result+'</b></font></div><hr>';
				} //end if else
				objResult.status = 'OK';
				objResult.result = result;
				objResult.html = String(html);
				break;

/*
case 'sphere':
	var radius = prompt("RADIUS of the SPHERE:", "");
	jx_result = Math.round(100 * (4 * Math.PI * (Math.pow(parseFloat(radius), 2)))) / 100;
	jx_area = '<div align="right" class="jx_active_div"><font color="#003399"><b>Area of SPHERE:</b></font> <font color="#778899"><b><i>[R='+radius+']</i></b></font> = <font color="#FF6600"><b>'+jx_result+'</b></font></div>';
	break;
case 'rcilinder_lateral':
	var radius = prompt('RADIUS of the Right CILINDER:', '');
	var height = prompt('HEIGHT of the Right CILINDER:', '');
	jx_result = Math.round(100 * (2 * Math.PI * parseFloat(radius) * parseFloat(height))) / 100;
	jx_area = '<div align="right" class="jx_active_div"><font color="#003399"><b>LATERAL Area of Right CILINDER:</b></font> <font color="#778899"><b><i>[R='+radius+' ; H='+height+']</i></b></font> = <font color="#FF6600"><b>'+jx_result+'</b></font></div>';
	break;
case 'rcircle_cone_lateral':
	var radius = prompt('RADIUS of the Right/Circle CONE (from the Base Circle):', '');
	var height = prompt('HEIGHT of the Right/Circle CONE (perpendicular from Top to Base Circle center):', '');
	var slant =  Math.sqrt((Math.pow(parseFloat(radius), 2)) + (Math.pow(parseFloat(height), 2)));
	jx_result = Math.round(100 * (Math.PI * parseFloat(radius) * slant)) / 100;
	jx_area = '<div align="right" class="jx_active_div"><font color="#003399"><b>LATERAL Area of Right/Circle CONE:</b></font> <font color="#778899"><b><i>[R='+radius+' ; H='+height+' ; <span title="Calculated Value: Slant">*SL='+slant+'</span>]</i></b></font> = <font color="#FF6600"><b>'+jx_result+'</b></font></div>';
	break;
case 'square_pyramide_lateral':
	var length = prompt('LENGTH of one Side of the Base Square of the PYRAMIDE:', '');
	var height = prompt('HEIGHT of the Rectangle PYRAMIDE (perpendicular from Top to Base Square center):', '');
	var slant =  Math.sqrt((Math.pow((parseFloat(length) / 2), 2)) + (Math.pow(parseFloat(height), 2)));
	jx_result = Math.round(100 * ((4 * parseFloat(length)) * slant / 2)) / 100;
	jx_area = '<div align="right" class="jx_active_div"><font color="#003399"><b>LATERAL Area of Square PYRAMIDE:</b></font> <font color="#778899"><b><i>[L='+length+' ; H='+height+' ; <span title="Calculated Value: Slant">*SL='+slant+'</span>]</i></b></font> = <font color="#FF6600"><b>'+jx_result+'</b></font></div>';
	break;
*/

			default:
				objResult.status = 'ERROR: Invalid Geometry Figure !';
		} //end switch

		return objResult;

	} //END FUNCTION


} //END CLASS


//==================================================================
//==================================================================


// #END
