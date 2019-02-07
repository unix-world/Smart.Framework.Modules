
// JX Calculator
// (c) 2019 unix-world.org
// License: GPLv3
// v.20190207

var JXCalculator = new function() { // START CLASS

// :: static

// inits
var tmp_Value__Result=0, tmp_Value__Operand=0, tmp_Value__Second=0, tmp_Value__Ready=0, tmp_Value__Done=1, tmp_Value__Complete=0, tmp_Value__Integer, tmp_Value__CurrentValue;

// private
this.calculator_initval = function(value) {

	value = parseFloat(value);
	value = value.toFixed(4);
	document.calculator.LED.value = value;
	tmp_Value__Result = value, tmp_Value__Operand = 0, tmp_Value__Second = 0, tmp_Value__Ready = 0; tmp_Value__Done = 1; tmp_Value__Complete = 0;

} //END FUNCTION

// public
this.calculator_click_key = function(Caption) {

	tmp_Value__CurrentValue = document.calculator.LED.value;

	if(Caption=='.') {
		calculator_set_value('0');
		if(tmp_Value__Integer) {
			tmp_Value__CurrentValue += Caption;
			document.calculator.LED.value = tmp_Value__CurrentValue;
			tmp_Value__Complete = 0;
		} //end if
	} //end if

	if (Caption.length == 1 && Caption>='0' && Caption<='9') {
		calculator_set_value('');
		if(tmp_Value__CurrentValue=='0') {
			tmp_Value__CurrentValue='';
		} //end if
		tmp_Value__CurrentValue += Caption;
		document.calculator.LED.value = tmp_Value__CurrentValue;
		tmp_Value__Complete = 1;
	} //end if

	if (Caption=='pi') {
		tmp_Value__CurrentValue = Math.PI;
		document.calculator.LED.value = tmp_Value__CurrentValue;
		tmp_Value__Complete = 1;
	} //end if

	if (Caption=='e') {
		tmp_Value__CurrentValue = Math.E;
		document.calculator.LED.value = tmp_Value__CurrentValue;
		tmp_Value__Complete = 1;
	} //end if

	if(Caption=='-' || Caption=='+' || Caption=='/' || Caption=='*' || Caption=='^') {
		if(tmp_Value__Second) {
			tmp_Value__Operand = Caption
		} else {
			if(!tmp_Value__Ready) {
				tmp_Value__Operand = Caption;
				tmp_Value__Result = tmp_Value__CurrentValue;
				tmp_Value__Ready = 1;
			} else {
				if (tmp_Value__Operand=='^') {
					tmp_Value__Result = Math.pow(tmp_Value__Result, tmp_Value__CurrentValue);
				} else {
					tmp_Value__Result = eval(tmp_Value__Result + tmp_Value__Operand + tmp_Value__CurrentValue);
				} //end if else
				tmp_Value__Operand = Caption; document.calculator.LED.value = tmp_Value__Result;
			} //end if else
			tmp_Value__Complete = 0;
			tmp_Value__Second = 1;
		} //end if else
	} //end if

	if(Caption=='1/x' ) {
		tmp_Value__Result = eval('1/' + tmp_Value__CurrentValue);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='sqrt') {
		tmp_Value__Result = Math.sqrt(tmp_Value__CurrentValue);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='exp') {
		tmp_Value__Result = Math.exp(tmp_Value__CurrentValue);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='log') {
		tmp_Value__Result = Math.log(tmp_Value__CurrentValue) / Math.LN10;
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='ln') {
		tmp_Value__Result = Math.log(tmp_Value__CurrentValue);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='sin') {
		tmp_Value__Result = tmp_Value__CurrentValue;
		if (document.calculator.angle[0].checked) {
			tmp_Value__Result = tmp_Value__Result * Math.PI / 180;
		} //end if
		if (document.calculator.angle[2].checked) {
			tmp_Value__Result = tmp_Value__Result * Math.PI / 200;
		} //end if
		tmp_Value__Result = Math.sin(tmp_Value__Result);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='cos') {
		tmp_Value__Result = tmp_Value__CurrentValue;
		if (document.calculator.angle[0].checked) {
			tmp_Value__Result = tmp_Value__Result * Math.PI / 180;
		} //end if
		if (document.calculator.angle[2].checked) {
			tmp_Value__Result = tmp_Value__Result * Math.PI / 200;
		} //end if
		tmp_Value__Result = Math.cos(tmp_Value__Result);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='tan') {
		tmp_Value__Result = tmp_Value__CurrentValue;
		if (document.calculator.angle[0].checked) {
			tmp_Value__Result = tmp_Value__Result * Math.PI / 180;
		} //end if
		if (document.calculator.angle[2].checked) {
			tmp_Value__Result = tmp_Value__Result * Math.PI / 200;
		} //end if
		tmp_Value__Result = Math.tan(tmp_Value__Result);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='sinh') {
		tmp_Value__Result = Math.exp(tmp_Value__CurrentValue);
		tmp_Value__Result = (tmp_Value__Result - 1 / tmp_Value__Result) / 2;
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='cosh') {
		tmp_Value__Result = Math.exp(tmp_Value__CurrentValue);
		tmp_Value__Result = (tmp_Value__Result + 1 / tmp_Value__Result) / 2;
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='tanh') {
		tmp_Value__Result = Math.exp(tmp_Value__CurrentValue);
		tmp_Value__Result = (tmp_Value__Result - 1 / tmp_Value__Result) / (tmp_Value__Result + 1 / tmp_Value__Result);
		calculator_reset(tmp_Value__Result);
	} //end if

	if(Caption=='+/-') {
		document.calculator.LED.value = eval(-tmp_Value__CurrentValue);
	} //end if

	if(Caption=='=' && tmp_Value__Complete && tmp_Value__Operand!='0') {
		if (tmp_Value__Operand=='^') {
			tmp_Value__Result = Math.pow(tmp_Value__Result, tmp_Value__CurrentValue);
			calculator_reset(tmp_Value__Result);
		} else {
			tmp_Value__Result = eval(tmp_Value__Result + tmp_Value__Operand + tmp_Value__CurrentValue);
			calculator_reset(tmp_Value__Result);
		} //end if else
	} //end if

	if (Caption=='C') {
		calculator_reset(0);
	} //end if

	if(document.calculator.LED.value[0] == '.') {
		document.calculator.LED.value = '0' + document.calculator.LED.value;
	} //end if

} //END FUNCTION

// private
var calculator_reset = function(value) {

	SmartJS_BrowserUtils.setCookie('WIDGET_NETVISION_CALCULATOR_RESULT', value.toFixed(4), 0, '/');
	document.calculator.LED.value = value.toFixed(4);
	tmp_Value__Result = 0, tmp_Value__Operand = 0, tmp_Value__Second = 0, tmp_Value__Ready = 0; tmp_Value__Done = 1; tmp_Value__Complete = 0;

} //END FUNCTION

// private
var calculator_set_value = function(NewValue) {

	tmp_Value__Integer = 1;

	if(tmp_Value__Second || tmp_Value__Done) {
		tmp_Value__Second = 0;
		tmp_Value__Done = 0;
		tmp_Value__CurrentValue = NewValue;
	} //end if

	for(var i=0; i<tmp_Value__CurrentValue.length; i++) {
		if (tmp_Value__CurrentValue[i]=='.') {
			tmp_Value__Integer=0;
		} //end if
	} //end for

} //END FUNCTION

} //END CLASS

// #END
