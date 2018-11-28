
// [LIB - SmartFramework / JS / Browser UI Utils - jQueryUI]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

// DEPENDS: jQuery, SmartJS_CoreUtils, SmartJS_BrowserUtils, jQueryUI, jQuery.UI.ListSelect, jQuery.UI.TimePicker, jQuery.DataTable

// ! To use jQueryUI bindings for Smart.Framework load this instead of lib/js/jquery/jquery.smartframework.ui.js ; they are a drop-in replacements for LighJS-UI !

//==================================================================
//==================================================================

// Fix: add HTML support for dialog title
$.widget('ui.dialog', $.extend({}, $.ui.dialog.prototype, {
	_title: function(title) {
		var fixTitle = '';
		if(!this.options.title) {
			fixTitle = '';
		} else {
			fixTitle = SmartJS_CoreUtils.stringTrim(this.options.title);
		} //end if else
		if(!fixTitle) {
			fixTitle = '&nbsp;';
		} //end if
		title.html(fixTitle).css({ width: '100%' });
	} //end function
}));

var SmartJS_BrowserUIUtils = new function() { // START CLASS :: v.181128.r5

this.overlayCssClass = 'ui-widget-overlay'; // optional: overlay integration

//=======================================

// SYNC WITH: SmartJS_BrowserUtils.alert_Dialog()
// Dependencies:
//	jQueryUI
this.DialogAlert = function(y_message_html, evcode, y_title, y_width, y_height) {
	//--
	// evcode params: -
	//--
	if((typeof y_title == 'undefined') || (y_title == null) || (y_title == '')) {
		y_title = '';
	} //end if
	//--
	if((typeof y_width == 'undefined') || (y_width == null) || (y_width == '')) {
		y_width = 550;
	} //end if
	y_width = parseInt(y_width);
	if(isNaN(y_width) || (y_width < 100) || (y_width > 920)) {
		y_width = 550;
	} //end if
	//--
	if((typeof y_height == 'undefined') || (y_height == null) || (y_height == '')) {
		y_height = 225;
	} //end if
	y_height = parseInt(y_height);
	if(isNaN(y_height) || (y_height < 50) || (y_height > 700)) {
		y_height = 225;
	} //end if
	//--
	var HtmlElement = $('<div></div>').html(y_message_html);
	var TheMsgDialog = HtmlElement.dialog({autoOpen:false});
	//--
	TheMsgDialog.dialog({
		title: y_title,
		resizable: false,
		width: y_width,
		height: y_height,
		position: { my: 'center top+70', at: 'center top', of: window },
		modal: true,
		closeOnEscape: false,
		open: function(event, ui){ $(this).parent().find('.ui-dialog-titlebar-close').hide(); },
		buttons: {
			'OK': {
				text: 'OK',
				//icons: { primary: 'ui-icon-check' },
				icon: 'ui-icon-check', // fix for jQueryUI 1.12
				click: function() {
					//--
					$(this).dialog('close');
					//--
					if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
						try {
							if(typeof evcode === 'function') {
								evcode(); // call :: sync params dialog-alert
							} else {
								eval('(function(){ ' + evcode + ' })();'); // sandbox
							} //end if else
						} catch(err) {
							console.error('ERROR: JS-Eval Error on BrowserUI DialogAlert Function' + '\nDetails: ' + err);
						} //end try catch
					} //end if
					//--
					$(this).dialog('destroy');
					$(this).remove();
					//--
				}
			}
		}
	});
	//--
	TheMsgDialog.dialog('open');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// SYNC WITH: SmartJS_BrowserUtils.confirm_Dialog()
// Dependencies:
//	jQueryUI
this.DialogConfirm = function(y_question_html, evcode, y_title, y_width, y_height) {
	//--
	// evcode params: -
	//--
	if((typeof y_title == 'undefined') || (y_title == null) || (y_title == '')) {
		y_title = '';
	} //end if
	//--
	if((typeof y_width == 'undefined') || (y_width == null) || (y_width == '')) {
		y_width = 550;
	} //end if
	y_width = parseInt(y_width);
	if(isNaN(y_width) || (y_width < 100) || (y_width > 920)) {
		y_width = 550;
	} //end if
	//--
	if((typeof y_height == 'undefined') || (y_height == null) || (y_height == '')) {
		y_height = 225;
	} //end if
	y_height = parseInt(y_height);
	if(isNaN(y_height) || (y_height < 50) || (y_height > 700)) {
		y_height = 225;
	} //end if
	//--
	var HtmlElement = $('<div></div>').html(y_question_html);
	var TheMsgDialog = HtmlElement.dialog({autoOpen:false});
	//--
	TheMsgDialog.dialog({
		title: y_title,
		resizable: false,
		width: y_width,
		height: y_height,
		position: { my: 'center top+70', at: 'center top', of: window },
		modal: true,
		closeOnEscape: false,
		open: function(event, ui){ $(this).parent().find('.ui-dialog-titlebar-close').hide(); },
		buttons: {
			'Cancel': {
				text: 'Cancel',
				//icons: { primary: 'ui-icon-closethick' },
				icon: 'ui-icon-closethick', // fix for jQueryUI 1.12
				click: function() {
					//--
					$(this).dialog('close');
					$(this).dialog('destroy');
					$(this).remove();
					//--
				}
			},
			'OK': {
				text: 'OK',
				//icons: { primary: 'ui-icon-check' },
				icon: 'ui-icon-check', // fix for jQueryUI 1.12
				click: function() {
					//--
					$(this).dialog('close');
					//--
					if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
						try {
							if(typeof evcode === 'function') {
								evcode(); // call :: sync params dialog-confirm
							} else {
								eval('(function(){ ' + evcode + ' })();'); // sandbox
							} //end if else
						} catch(err) {
							console.error('ERROR: JS-Eval Error on BrowserUI DialogConfirm Function' + '\nDetails: ' + err);
						} //end try catch
					} //end if
					//--
					$(this).dialog('destroy');
					$(this).remove();
					//--
				}
			}
		}
	});
	//--
	TheMsgDialog.dialog('open');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
//	modules/mod-ui-jqueryui/toolkit/listselect/jquery.multiselect.css
//	modules/mod-ui-jqueryui/toolkit/listselect/jquery.multiselect.filter.css
//	modules/mod-ui-jqueryui/toolkit/listselect/jquery.multiselect.js
//	modules/mod-ui-jqueryui/toolkit/listselect/i18n/jquery.multiselect.{lang}.js
//	modules/mod-ui-jqueryui/toolkit/listselect/jquery.multiselect.filter.js
//	modules/mod-ui-jqueryui/toolkit/listselect/i18n/jquery.multiselect.filter.{lang}.js
this.Smart_SelectList = function(elemID, dimW, dimH, allowMulti, useFilter) {
	//--
	// evcode is taken from onBlur ; evcode params: elemID
	//--
	var HtmlElement = $('#' + elemID);
	//--
	HtmlElement.multiselect({
		header: true,
		multiple: allowMulti,
		selectedList: 1,
		minWidth: dimW,
		height: dimH,
		position: {
			my: 'left top',
			at: 'left bottom',
			collision: 'flipfit'
		},
		close: function() {
			//--
			var evcode = HtmlElement.attr('onBlur'); // onChange is always triggered, but useless on Multi-Select Lists on which we substitute it with the onBlur which is not triggered here but we catch and execute here
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					if(typeof evcode === 'function') {
						evcode(elemID); // call :: sync params ui-selectlist
					} else { // sync :: eliminate javascript:
						evcode = SmartJS_CoreUtils.stringTrim(evcode);
						evcode = evcode.replace('javascript:', '');
						evcode = SmartJS_CoreUtils.stringTrim(evcode);
						if((evcode != null) && (evcode != '')) {
							eval('(function(){ ' + evcode + ' })();'); // sandbox
						} //end if
					} //end if else
				} catch(err) {
					console.error('ERROR: JS-Eval Error on Smart-SelectList: ' + elemID + '\nDetails: ' + err);
				} //end try catch
			} //end if
			//--
		} //end function
	});
	//--
	if(useFilter === true) {
		HtmlElement.multiselectfilter({
			autoReset: true,
			placeholder: '...',
			//label: ''
		});
	} //end if
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
//	modules/mod-ui-jqueryui/toolkit/i18n/jquery.ui.datepicker-{lang}.js
this.Date_Picker_Init = function(elemID, dateFmt, selDate, calStart, calMinDate, calMaxDate, noOfMonths, evcode) {
	//--
	// evcode params: date, altdate, inst, elemID
	//--
	var the_initial_date = String(selDate);
	//--
	var the_initial_altdate = '';
	if(the_initial_date != '') {
		$('#date-bttn-' + elemID).attr('title', String(selDate));
		the_initial_altdate = SmartJS_CoreUtils.formatDate(String(dateFmt), new Date(the_initial_date));
		$('#date-entry-' + elemID).val(the_initial_altdate);
	} //end if
	//--
	var HtmlElement = $('#' + elemID);
	//--
	HtmlElement.datepicker({
		showAnim: null, duration: null,
		numberOfMonths: 1, stepMonths: 1, // noOfMonths is ignored (set to 1) to be compatible with LightJSComponents
		showButtonPanel: true, showWeek: true, weekHeader: '#',
		prevText: '&lt;&lt;', nextText: '&gt;&gt;',
		changeYear: true, changeMonth: true,
		showOtherMonths: true, selectOtherMonths: false,
		firstDay: calStart,
		dateFormat: 'yy-mm-dd',
		altFormat: String(dateFmt),
		altField: '#date-entry-' + elemID,
		minDate: calMinDate, maxDate: calMaxDate,
		onSelect: function(date, inst) {
			//--
			$('#date-bttn-' + elemID).attr('title', date);
			var altdate = date;
			try {
				altdate = SmartJS_CoreUtils.formatDate(String(dateFmt), new Date(date));
				if(/Invalid|NaN/.test(altdate)) {
					altdate = date;
				} //end if
			} catch(err) {
				console.log('Date conversion is not supported by the browser. Using ISO Date');
			} //end try catch
			$('#date-entry-' + elemID).val(altdate);
			//--
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					if(typeof evcode === 'function') {
						evcode(date, altdate, inst, elemID); // call :: sync params ui-datepicker
					} else {
						eval('(function(){ ' + evcode + ' })();'); // sandbox
					} //end if else
				} catch(err) {
					console.error('ERROR: JS-Eval Error on DatePicker: ' + elemID + '\nDetails: ' + err);
				} //end try catch
			} //end if
			//--
		} //end function
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
//	modules/mod-ui-jqueryui/toolkit/i18n/jquery.ui.datepicker-{lang}.js
this.Date_Picker_Display = function(datepicker_id) {
	//--
	var HtmlElement = $('#' + datepicker_id);
	//--
	HtmlElement.datepicker('show');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
//	modules/mod-ui-jqueryui/toolkit/timepicker/jquery.ui.timepicker.css
//	modules/mod-ui-jqueryui/toolkit/timepicker/jquery.ui.timepicker.js
//	modules/mod-ui-jqueryui/toolkit/timepicker/i18n/jquery.ui.timepicker-{lang}.js
this.Time_Picker_Init = function(elemID, hStart, hEnd, mStart, mEnd, mInterval, tmRows, evcode) {
	//--
	// evcode params: time, inst, elemID
	//--
	var HtmlElement = $('#' + elemID);
	//--
	HtmlElement.timepicker({
		defaultTime: '', // this must superset the default now() when now() is not in allowed h/m
		showOn: 'button',
		showCloseButton: false,
		showAnim: null, duration: null,
		timeSeparator: ':',
		showPeriodLabels: false,
		showPeriod: false,
		amPmText:['',''],
		rows: tmRows,
		hours: {
			starts: hStart,
			ends: hEnd
		},
		minutes: {
			starts: mStart,
			ends: mEnd,
			interval: mInterval
		},
		onSelect: function(time, inst) {
			//--
			if(time != '') { //emulate on select because onSelect trigger twice (1 select hour + 2 select minutes), so if no time selected even if onClose means no onSelect !
				if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
					try {
						if(typeof evcode === 'function') {
							evcode(time, inst, elemID); // call :: sync params ui-timepicker
						} else {
							eval('(function(){ ' + evcode + ' })();'); // sandbox
						} //end if else
					} catch(err) {
						console.error('ERROR: JS-Eval Error on TimePicker: ' + elemID + '\nDetails: ' + err);
					} //end try catch
				} //end if
			} //end if
			//--
		} //end function
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
//	modules/mod-ui-jqueryui/toolkit/timepicker/jquery.ui.timepicker.css
//	modules/mod-ui-jqueryui/toolkit/timepicker/jquery.ui.timepicker.js
//	modules/mod-ui-jqueryui/toolkit/timepicker/i18n/jquery.ui.timepicker-{lang}.js
this.Time_Picker_Display = function(timepicker_id) {
	//--
	var HtmlElement = $('#' + timepicker_id);
	//--
	HtmlElement.timepicker('show');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
this.Tabs_Init = function(tabs_id, tab_selected, prevent_reload) {
	//--
	tab_selected = parseInt(tab_selected);
	if(tab_selected < 0) {
		tab_selected = 0;
	} //end if
	//--
	var HtmlElement = $('#' + tabs_id);
	//--
	HtmlElement.tabs({
		active: tab_selected,
		select: function(event, ui) {},
		beforeLoad: function(event, ui) {
			if(prevent_reload === true) {
				if(ui.tab.data('loaded')) {
					event.preventDefault();
					return;
				}
				ui.jqXHR.done(function() { // {{{JQUERY-AJAX}}} :: instead of .success is deprecated
					ui.tab.data('loaded', true);
				});
			} //end if
			if(!ui.tab.data('loaded')) {
				$('#smartframeworkcomponents_jquery_tabs_loader').remove();
				$('<div id="smartframeworkcomponents_jquery_tabs_loader" style="width:250px; position:absolute; top:37px; right:0px; text-align:center;"><img src="' + SmartJS_BrowserUtils_LoaderImg + '" alt="... loading Tab data ..."></div>').appendTo('#' + tabs_id);
				//ui.ajaxSettings.type = 'GET';
				//ui.ajaxSettings.async = true;
				//ui.ajaxSettings.cache = true;
				//ui.ajaxSettings.timeout = 0;
				//ui.jqXHR.error(function() { // .error() is deprecated in the favour of .fail()
				ui.jqXHR.fail(function() {
					SmartJS_BrowserUtils.alert_Dialog('<h1>WARNING: Asyncronous Load Timeout or URL is broken !</h1>', '$(\'#smartframeworkcomponents_jquery_tabs_loader\').remove();', 'TAB #' + (parseInt($(ui.tab).index()) + 1) + ' :: ' + $(ui.tab).text());
				});
			} //end if
		},
		load: function(event, ui) {
			$('#smartframeworkcomponents_jquery_tabs_loader').remove();
		}
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
this.Tabs_Activate = function(tabs_id, activation) {
	//--
	var HtmlElement = $('#' + tabs_id);
	//--
	if(activation === false) {
		HtmlElement.tabs('disable');
	} else {
		HtmlElement.tabs('enable');
	} //end if else
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQueryUI
this.AutoCompleteField = function(single_or_multi, elem_id, data_url, var_term, min_term_len, evcode) {
	//--
	// evcode params: id, value, label, data
	//--
	var HtmlElement = $('#' + elem_id);
	HtmlElement.dblclick(function() {
		$(this).val('');
	});
	//--
	min_term_len = parseInt(min_term_len);
	if(min_term_len < 1) {
		min_term_len = 1;
	} //end if
	if(min_term_len > 255) {
		min_term_len = 255;
	} //end if
	//--
	if((typeof var_term == 'undefined') || (var_term == 'undefined') || (var_term == null) || (var_term == '')) {
		var_term = 'undefined_search_term_url_variable';
	} //end if
	//--
	HtmlElement.bind('keydown', function(event) {
		if(event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
			event.preventDefault(); // don't navigate away from the field on tab when selecting an item
		} //end if
		if(event.keyCode === $.ui.keyCode.ENTER) {
			event.preventDefault(); // catch ENTER key
		} //end if
	}).autocomplete({
		timeout: 0,
		delay: 500,
		source: function(request, response) {
			var ajax = SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL(
				''+data_url,
				'POST',
				'json',
				'&'+var_term+'='+encodeURIComponent(SmartJS_CoreUtils.arrayGetLast(SmartJS_CoreUtils.stringSplitbyComma(request.term)))
			);
			ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
				response(msg); // this will bind json to the autocomplete
			}).fail(function(msg) {
				console.error('UI.AutoCompleteField: FAILED to fetch results for Element: ' + elem_id);
			});
		},
		search: function() {
			// custom minLength
			var term = SmartJS_CoreUtils.arrayGetLast(SmartJS_CoreUtils.stringSplitbyComma(HtmlElement.val()));
			if(term.length < min_term_len) {
				return false;
			}
		},
		focus: function() {
			// prevent value inserted on focus
			return false;
		},
		select: function(event, ui) {
			var id = String(ui.item.id);
			var value = String(ui.item.value);
			var label = String(ui.item.label);
			var data = String(ui.item.data); // can be a json to be used with JSON.parse(data) to pass extra properties
			try {
				if(single_or_multi === 'multilist') {
					HtmlElement.val(SmartJS_CoreUtils.addToList(value, HtmlElement.val(), ','));
				} else {
					HtmlElement.val(value); // on select replace element value with the selected item
				} //end if else
			} catch(err) {
				console.error('UI.AutoCompleteField: ERROR ... could not bind value to Element: ' + elem_id);
			}
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					if(typeof evcode === 'function') {
						evcode(id, value, label, data); // call :: sync params ui-autosuggest
					} else {
						eval('(function(){ ' + evcode + ' })();'); // sandbox
					} //end if else
				} catch(err) {
					console.error('UI.AutoCompleteField ERROR: JS-Eval Error on Element: ' + elem_id + '\nDetails: ' + err);
				} //end try catch
			}
			return false;
		}
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/datatables/datatables-responsive.css
//	lib/js/jquery/datatables/datatables-responsive.js
this.Smart_DataTable_Init = function(elem_id, options) {
	//--
	if(!options || typeof options !== 'object') {
		options = {};
	} //end if
	//--
	if(!options.hasOwnProperty('filter')) {
		options['filter'] = true;
	} else {
		options['filter'] = !(!options['filter']); // force boolean
	} //end if
	//--
	if(!options.hasOwnProperty('sort')) {
		options['sort'] = true;
	} else {
		options['sort'] = !(!options['sort']); // force boolean
	} //end if
	//--
	if(!options.hasOwnProperty('paginate')) {
		options['paginate'] = true;
	} else {
		options['paginate'] = !(!options['paginate']); // force boolean
	} //end if
	//--
	if(!options.hasOwnProperty('pagesize')) {
		options['pagesize'] = 10;
	} else {
		options['pagesize'] = parseInt(options['pagesize']); // force integer
		if(options['pagesize'] < 1) {
			options['pagesize'] = 1;
		} //end if
	} //end if
	//--
	var defPageSizes = [ 10, 25, 50, 100 ]; // default array
	if(!options.hasOwnProperty('pagesizes')) {
		options['pagesizes'] = defPageSizes;
	} else if(!Array.isArray(options['pagesizes'])) {
		options['pagesizes'] = defPageSizes;
	} //end if else
	//--
	if(!(!!options.paginate)) {
		options['pagesize'] = Number.MAX_SAFE_INTEGER;
		options['pagesizes'] = [ Number.MAX_SAFE_INTEGER ];
	} //end if
	//--
	if(!options.hasOwnProperty('classField')) {
		options['classField'] = 'ui-widget'; // default class
	} //end if
	//--
	if(!options.hasOwnProperty('classButton')) {
		options['classButton'] = 'ui-button ui-corner-all ui-widget'; // default class
	} //end if
	//--
	if(!options.hasOwnProperty('classActiveButton')) {
		options['classActiveButton'] = 'ui-state-active'; // default class
	} //end if
	//--
	var ordCols = []; // default array
	if(!options.hasOwnProperty('colorder')) {
		options['colorder'] = ordCols;
	} else if(!Array.isArray(options['colorder'])) {
		options['colorder'] = ordCols;
	} //end if else
	//--
	var defCols = [{}]; // default array
	if(!options.hasOwnProperty('coldefs')) {
		options['coldefs'] = defCols;
	} else if(!Array.isArray(options['coldefs'])) {
		options['coldefs'] = defCols;
	} //end if else
	//--
	var opts = {
		bFilter: 		!!options.filter,
		bSort: 			!!options.sort,
		bSortMulti: 	!!options.sort,
		order: 			Array.from(options.colorder),
		bPaginate: 		!!options.paginate,
		iDisplayLength: parseInt(options.pagesize),
		aLengthMenu: 	Array.from(options.pagesizes, x => parseInt(x)),
		uxmHidePagingIfNoMultiPages: 	true,
		uxmCssClassLengthField: 		String(options.classField),
		uxmCssClassFilterField: 		String(options.classField),
		classes: {
			sPageButton: 		String(options.classButton),
			sPageButtonActive: 	String(options.classActiveButton)
		},
		columnDefs: 	Array.from(options.coldefs)
	};
	//--
	var HtmlElement = jQuery('table#' + elem_id);
	//--
	HtmlElement.DataTable(opts);
	HtmlElement.data('smart-ui-elem-type', 'DataTable');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery, SmartJS_CoreUtils
//	lib/js/jquery/datatables/datatables-responsive.css
//	lib/js/jquery/datatables/datatables-responsive.js
this.Smart_DataTable_FilterColumns = function(elem_id, filterColNumber, regexStr) {
	//--
	var HtmlElement = jQuery('table#' + elem_id);
	//--
	if(HtmlElement.data('smart-ui-elem-type') !== 'DataTable') {
		return null;
	} //end if
	//--
	var obj = HtmlElement.DataTable();
	//--
	var col = parseInt(filterColNumber);
	if((col < 0) || !SmartJS_CoreUtils.isFiniteNumber(col)) {
		col = 0;
	} //end if
	if(regexStr) { // ex: '^(val1|val\-2)$'
		obj.columns(col).search(String(regexStr), true, false, true).draw();
	} else {
		obj.columns(col).search('').draw();
	} //end if else
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

} //END CLASS

//==================================================================
//==================================================================


// #END
