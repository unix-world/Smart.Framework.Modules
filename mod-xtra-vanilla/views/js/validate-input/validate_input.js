
// [LIB - SmartFramework / JS / Validate Input (Fields)]
// (c) 2006-2021 unix-world.org - all rights reserved
// r.20210411

// DEPENDS: smartJ$Utils

//==================================================================
//==================================================================

//================== [NO:evcode]

//=======================================
// CLASS :: Validate Input (Fields)
//=======================================

// added support for Integer Numbers
// added support for Number of Decimals to Place (0..4)

var SmartJS_FieldControl = new function() { // START CLASS :: v.181217

// :: static


// Validate Input Field as Integer Number
this.validate_Field_Integer = function(yObjInputField, yAllowNegatives) {
	//--
	var tmp_Value = '';
	tmp_Value = smartJ$Utils.format_number_int(yObjInputField.value, yAllowNegatives);
	tmp_Value = String(tmp_Value);
	//--
	yObjInputField.value = tmp_Value;
	//--
} //END FUNCTION


// Validate Input Field as Decimal(1..4) Number
this.validate_Field_Decimal = function(yObjInputField, yDecimalsDigits, yAllowNegatives, yAddThousandsSeparator) {
	//-- inits
	var tmp_Value = '';
	if(yObjInputField.value == '') {
		tmp_Value = '0';
	} else {
		tmp_Value = String(yObjInputField.value);
	} //end if
	//-- remove all spaces
	tmp_Value = String(smartJ$Utils.stringReplaceAll(' ', '', tmp_Value));
	//-- detect and trick the decimal and thousands separators
	var regex_dot = /\./g;
	var have_dot = regex_dot.test(tmp_Value);
	if(have_dot === true) {
		tmp_Value = smartJ$Utils.stringReplaceAll(',', '', tmp_Value); // remove thousands separator (comma) because there is already a dot there as decimal separator there (dot)
	} else {
		tmp_Value = smartJ$Utils.stringReplaceAll(',', '.', tmp_Value); // replace the wrong decimal separator (comma) with the real decimal separator (dot)
	} //end if
	//-- real format the value as decimal
	tmp_Value = smartJ$Utils.format_number_dec(tmp_Value, yDecimalsDigits, yAllowNegatives);
	//--
	if(yAddThousandsSeparator === true) {
		yObjInputField.value = String(addNumberThousandsCommaSeparator(String(tmp_Value)));
	} else {
		yObjInputField.value = String(tmp_Value);
	} //end if else
	//--
} //END FUNCTION


// Add the Thousands Separator (comma ,) to a number
// @param 	{Numeric} 	num 		The number to be formatted
// @return 	{String} 				The formatted number as string with comma as thousands separator if apply (will keep the . dot as decimal separator if apply)
var addNumberThousandsCommaSeparator = function(num) {
	//--
	num = String(num); // this is a special case
	//--
	var parts = num.split('.');
	parts[0] = parts[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,'); // add thousands separator
	//--
	return String(parts.join('.')); // fix to return empty string instead of null
	//--
} //END FUNCTION


} //END CLASS

//==================================================================
//==================================================================

// #END
