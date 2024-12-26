
// [LIB - SmartFramework / JS / Browser UI Utils - jQueryUI]
// (c) 2006-2023 unix-world.org - all rights reserved
// r.8.7 / smart.framework.v.8.7

// DEPENDS: jQuery, smartJ$Utils, smartJ$Date, smartJ$Browser, jQueryUI, jQuery.UI.ListSelect, jQuery.UI.TimePicker, jQuery.DataTable

// ! To use jQueryUI bindings for Smart.Framework load this instead of default jquery.smartframework.ui.js ; they are a drop-in replacements for LighJS-UI !

//==================================================================
//==================================================================

// Fix: add HTML support for dialog title
$.widget('ui.dialog', $.extend({}, $.ui.dialog.prototype, {
	_title: function(title) {
		var fixTitle = '';
		if(!this.options.title) {
			fixTitle = '';
		} else {
			fixTitle = smartJ$Utils.stringTrim(this.options.title);
		} //end if else
		if(!fixTitle) {
			fixTitle = '&nbsp;';
		} //end if
		title.html(fixTitle).css({ width: '100%' });
	} //end function
}));

var smartJ$UI = new function() { // START CLASS :: ES5 :: v.20230123

	this.overlayCssClass = 'ui-widget-overlay'; // optional: overlay integration

	//=======================================

	// Dependencies:
	//	jQueryUI
	this.ToolTip = function(selector) {
		//--
		var HtmlElement = $(selector);
		var dataTooltipOk = 'tooltip-ok';
		//--
		$('body').on('mousemove', selector, function(el) {
			$(selector).each(function(index, el) {
				var $el = $(this);
				if($el.data(dataTooltipOk)) {
					return;
				} //end if
				$el.data(dataTooltipOk, '1').tooltip({
					track: true,
					show: {
						delay: 10,
						duration: 0
					},
					hide: {
						delay: 10,
						duration: 0
					},
					classes: {
						'ui-tooltip': 'ui-state-active'
					}
				});
				var trigered = false;
				$el.on('mousemove', function() {
					if(trigered) {
						return;
					} //end if
					trigered = true;
					$el.trigger('mouseenter');
				}).on('focus blur click', function(){
					try {
						$el.tooltip('close');
					} catch(err){} // fix: Uncaught Error: cannot call methods on tooltip prior to initialization; attempted to call method 'close'
				});
			});
		});
		//--
		return HtmlElement;
		//--
	} //END FUNCTION

	//=======================================

	// SYNC WITH: smartJ$Browser.AlertDialog()
	// Dependencies:
	//	jQueryUI
	this.DialogAlert = function(y_message_html, evcode, y_title, y_width, y_height) {
		//--
		// evcode params: -
		//--
		y_title = smartJ$Utils.stringPureVal(y_title); // cast to string, don't trim ! need to preserve the value
		//--
		y_width = smartJ$Utils.format_number_int(y_width);
		if((y_width < 100) || (y_width > 1920)) {
			y_width = 550;
		} //end if
		//--
		y_height = smartJ$Utils.format_number_int(y_height);
		if((y_height < 50) || (y_height > 1080)) {
			y_height = 250;
		} //end if
		//--
		var HtmlElement = $('<div></div>').html(y_message_html);
		var TheMsgDialog = HtmlElement.dialog({autoOpen:false});
		//--
		TheMsgDialog.dialog({
			title: y_title,
			resizable: false,
			width: y_width,
			height: y_height + 25, // fix: because of jQueryUI styles it requires a higher height than default with 25px
			position: { my: 'center top+70', at: 'center top', of: window },
			modal: true,
			closeOnEscape: false,
			open: function(event, ui){ $(this).parent().find('.ui-dialog-titlebar-close').hide(); },
			buttons: {
				'OK': {
					text: 'OK',
					icon: 'ui-icon-check',
					click: function() {
						//--
						$(this).dialog('close');
						//--
						if((evcode != undefined) && (evcode != 'undefined') && (evcode != '')) { // undef tests also for null
							try {
								if(typeof(evcode) === 'function') {
									evcode(); // call :: sync params dialog-alert
								} else {
									eval('(function(){ ' + String(evcode) + ' })();'); // sandbox
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

	// SYNC WITH: smartJ$Browser.ConfirmDialog()
	// Dependencies:
	//	jQueryUI
	this.DialogConfirm = function(y_question_html, evcode, y_title, y_width, y_height) {
		//--
		// evcode params: -
		//--
		y_title = smartJ$Utils.stringPureVal(y_title); // cast to string, don't trim ! need to preserve the value
		//--
		y_width = smartJ$Utils.format_number_int(y_width);
		if((y_width < 100) || (y_width > 1920)) {
			y_width = 550;
		} //end if
		//--
		y_height = smartJ$Utils.format_number_int(y_height);
		if((y_height < 50) || (y_height > 1080)) {
			y_height = 250;
		} //end if
		//--
		var HtmlElement = $('<div></div>').html(y_question_html);
		var TheMsgDialog = HtmlElement.dialog({autoOpen:false});
		//--
		TheMsgDialog.dialog({
			title: y_title,
			resizable: false,
			width: y_width,
			height: y_height + 25, // fix: because of jQueryUI styles it requires a higher height than default with 25px
			position: { my: 'center top+70', at: 'center top', of: window },
			modal: true,
			closeOnEscape: false,
			open: function(event, ui){ $(this).parent().find('.ui-dialog-titlebar-close').hide(); },
			buttons: {
				'Cancel': {
					text: 'Cancel',
					icon: 'ui-icon-closethick',
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
					icon: 'ui-icon-check',
					click: function() {
						//--
						$(this).dialog('close');
						//--
						if((evcode != undefined) && (evcode != 'undefined') && (evcode != '')) { // undef tests also for null
							try {
								if(typeof(evcode) === 'function') {
									evcode(); // call :: sync params dialog-confirm
								} else {
									eval('(function(){ ' + String(evcode) + ' })();'); // sandbox
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
	//	toolkit/listselect/jquery.multiselect.css
	//	toolkit/listselect/jquery.multiselect.filter.css
	//	toolkit/listselect/jquery.multiselect.js
	//	toolkit/listselect/i18n/jquery.multiselect.{lang}.js
	//	toolkit/listselect/jquery.multiselect.filter.js
	//	toolkit/listselect/i18n/jquery.multiselect.filter.{lang}.js
	this.SelectList = function(elemID, dimW, dimH, isMulti, useFilter) {
		//--
		// evcode is taken from onBlur, mostly used by multi-select lists ; single select lists can handle onChange
		// evcode params: elemID, useFilter, isMulti
		//--
		var HtmlElement = $('#' + elemID);
		//--
		if(isMulti) {
			dimH = dimH + 75; // correction factor
		} //end if
		//--
		HtmlElement.multiselect({
			header: true,
			multiple: !! isMulti,
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
				if((evcode != undefined) && (evcode != 'undefined') && (evcode != '')) { // undef tests also for null
					try {
						if(typeof(evcode) === 'function') {
							evcode(elemID, useFilter, isMulti); // call :: sync params ui-selectlist
						} else { // sync :: eliminate javascript:
							evcode = smartJ$Utils.stringTrim(evcode);
							evcode = evcode.replace('javascript:', '');
							evcode = smartJ$Utils.stringTrim(evcode);
							if((evcode != null) && (evcode != '')) {
								eval('(function(){ ' + String(evcode) + ' })();'); // sandbox
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
	//	toolkit/i18n/jquery.ui.datepicker-{lang}.js
	this.DatePickerInit = function(elemID, dateFmt, selDate, calStart, calMinDate, calMaxDate, noOfMonths, evcode) {
		//--
		// evcode params: date, altdate, inst, elemID
		//--
		var the_initial_date = String(selDate);
		//--
		var the_initial_altdate = '';
		if(the_initial_date != '') {
			the_initial_altdate = smartJ$Date.formatDate(String(dateFmt), new Date(the_initial_date));
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
				var altdate = date;
				try {
					altdate = smartJ$Date.formatDate(String(dateFmt), new Date(date));
					if(/Invalid|NaN|Infinity/.test(altdate)) {
						altdate = date;
					} //end if
				} catch(err) {
					console.log('Date conversion is not supported by the browser. Using ISO Date');
				} //end try catch
				$('#date-entry-' + elemID).val(altdate);
				//--
				if((evcode != undefined) && (evcode != 'undefined') && (evcode != '')) { // undef tests also for null
					try {
						if(typeof(evcode) === 'function') {
							evcode(date, altdate, inst, elemID); // call :: sync params ui-datepicker
						} else {
							eval('(function(){ ' + String(evcode) + ' })();'); // sandbox
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
	//	toolkit/i18n/jquery.ui.datepicker-{lang}.js
	this.DatePickerDisplay = function(datepicker_id) {
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
	//	toolkit/timepicker/jquery.ui.timepicker.css
	//	toolkit/timepicker/jquery.ui.timepicker.js
	//	toolkit/timepicker/i18n/jquery.ui.timepicker-{lang}.js
	this.TimePickerInit = function(elemID, hStart, hEnd, mStart, mEnd, mInterval, tmRows, evcode) {
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
					if((evcode != undefined) && (evcode != 'undefined') && (evcode != '')) { // undef tests also for null
						try {
							if(typeof(evcode) === 'function') {
								evcode(time, inst, elemID); // call :: sync params ui-timepicker
							} else {
								eval('(function(){ ' + String(evcode) + ' })();'); // sandbox
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
	//	toolkit/timepicker/jquery.ui.timepicker.css
	//	toolkit/timepicker/jquery.ui.timepicker.js
	//	toolkit/timepicker/i18n/jquery.ui.timepicker-{lang}.js
	this.TimePickerDisplay = function(timepicker_id) {
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
	this.TabsInit = function(tabs_id, tab_selected, prevent_reload) {
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
				if(prevent_reload !== false) {
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
					var imgLoader = '';
					if(smartJ$Browser.param_LoaderImg) {
						imgLoader = '<img src="' + smartJ$Utils.escape_html(smartJ$Browser.param_LoaderImg) + '" alt="... loading Tab data ...">';
					} //end if
					$('<div id="smartframeworkcomponents_jquery_tabs_loader" style="width:250px; position:absolute; top:37px; right:0px; text-align:center;">' + imgLoader + '</div>').appendTo('#' + tabs_id);
					//ui.ajaxSettings.type = 'GET';
					//ui.ajaxSettings.async = true;
					//ui.ajaxSettings.cache = true;
					//ui.ajaxSettings.timeout = 0;
					//ui.jqXHR.error(function() { // .error() is deprecated in the favour of .fail()
					ui.jqXHR.fail(function() {
						smartJ$Browser.AlertDialog('<h1>WARNING: Asyncronous Load Timeout or URL is broken !</h1>', '$(\'#smartframeworkcomponents_jquery_tabs_loader\').remove();', 'TAB #' + (parseInt($(ui.tab).index()) + 1) + ' :: ' + $(ui.tab).text());
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
	this.TabsActivate = function(tabs_id, activation) {
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
		var_term = smartJ$Utils.stringPureVal(var_term, true); // cast to string, trim
		if((var_term == null) || (var_term == '')) {
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
				var ajax = smartJ$Browser.AjaxRequestFromURL(
					''+data_url,
					'POST',
					'json',
					'&'+var_term+'='+encodeURIComponent(smartJ$Utils.arrayGetLast(smartJ$Utils.stringSplitbyComma(request.term)))
				);
				ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
					response(msg); // this will bind json to the autocomplete
				}).fail(function(msg) {
					console.error('UI.AutoCompleteField: FAILED to fetch results for Element: ' + elem_id);
				});
			},
			search: function() {
				// custom minLength
				var term = smartJ$Utils.arrayGetLast(smartJ$Utils.stringSplitbyComma(HtmlElement.val()));
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
						HtmlElement.val(smartJ$Utils.addToList(value, HtmlElement.val(), ','));
					} else {
						HtmlElement.val(value); // on select replace element value with the selected item
					} //end if else
				} catch(err) {
					console.error('UI.AutoCompleteField: ERROR ... could not bind value to Element: ' + elem_id);
				}
				if((evcode != undefined) && (evcode != 'undefined') && (evcode != '')) { // undef tests also for null
					try {
						if(typeof(evcode) === 'function') {
							evcode(id, value, label, data); // call :: sync params ui-autosuggest
						} else {
							eval('(function(){ ' + String(evcode) + ' })();'); // sandbox
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
	//	datatables/datatables-responsive.css
	//	datatables/datatables-responsive.js
	//	datatables/smart-datatables.js
	this.DataTableInit = function(elem_id, options) {
		//--
		if(typeof(SmartDataTables) == undefined) {
			console.error('smartJ$UI', 'DataTableInit', 'SmartDataTables is not loaded ...');
			return;
		} //end if
		//--
		if(!options || (typeof(options) !== 'object')) {
			options = {};
		} //end if
		if(!options.hasOwnProperty('classField')) {
			options['classField'] = 'ui-widget'; // default class
		} //end if
		if(!options.hasOwnProperty('classButton')) {
			options['classButton'] = 'ui-button ui-corner-all ui-widget'; // default class
		} //end if
		if(!options.hasOwnProperty('classActiveButton')) {
			options['classActiveButton'] = 'ui-state-active'; // default class
		} //end if
		//--
		return SmartDataTables.DataTableInit(elem_id, options);
		//--
	} //END FUNCTION

	//=======================================

	// Dependencies:
	//	jQuery, smartJ$Utils
	//	datatables/datatables-responsive.css
	//	datatables/datatables-responsive.js
	//	datatables/smart-datatables.js
	this.DataTableColumnsFilter = function(elem_id, filterColNumber, regexStr) {
		//--
		if(typeof(SmartDataTables) == undefined) {
			console.error('smartJ$UI', 'DataTableColumnsFilter', 'SmartDataTables is not loaded ...');
			return;
		} //end if
		//--
		return SmartDataTables.DataTableColumnsFilter(elem_id, filterColNumber, regexStr);
		//--
	} //END FUNCTION

	//=======================================

} //END CLASS

//==================================================================
//==================================================================


// #END
