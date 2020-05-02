/***
 * Contains basic SlickGrid editors.
 * @module Editors
 * @namespace Slick
 */

// (c) 2017-2020 unix-world.org
// SlickGrid v2.3.uxm20200502
// Fixes by unixman:
// 	- jQuery 3.5.0 ready (fixed XHTML Tags)

(function ($) {
	// register namespace
	$.extend(true, window, {
		"Slick": {
			"Editors": {
				"Text": TextEditor,
				"Integer": IntegerEditor,
				"Float": FloatEditor,
				"Date": DateEditor,
				"YesNoSelect": YesNoSelectEditor,
				"Checkbox": CheckboxEditor,
				"PercentComplete": PercentCompleteEditor,
				"LongText": LongTextEditor
			}
		}
	});

	function TextEditor(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function () {
			$input = $('<input type="text" class="ux-field editor-text" style="width:100%!important; height:100%!important; margin:0!important;">')
					.appendTo(args.container)
					.on("keydown.nav", function (e) {
						if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
							e.stopImmediatePropagation();
						}
					})
					.focus()
					.select();
		};

		this.destroy = function () {
			$input.remove();
		};

		this.focus = function () {
			$input.focus();
		};

		this.getValue = function () {
			return $input.val();
		};

		this.setValue = function (val) {
			$input.val(val);
		};

		this.loadValue = function (item) {
			defaultValue = item[args.column.field] || "";
			$input.val(defaultValue);
			$input[0].defaultValue = defaultValue;
			$input.select();
		};

		this.serializeValue = function () {
			return $input.val();
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
		};

		this.validate = function () {
			if (args.column.validator) {
				var validationResults = args.column.validator($input.val());
				if (!validationResults.valid) {
					return validationResults;
				}
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

	function IntegerEditor(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function () {
			$input = $('<input type="number" class="ux-field editor-text" style="width:100%!important; height:100%!important; margin:0!important;">');

			$input.on("keydown.nav", function (e) {
				if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
					e.stopImmediatePropagation();
				}
			});

			$input.appendTo(args.container);
			$input.focus().select();
		};

		this.destroy = function () {
			$input.remove();
		};

		this.focus = function () {
			$input.focus();
		};

		this.loadValue = function (item) {
			defaultValue = item[args.column.field];
			$input.val(defaultValue);
			$input[0].defaultValue = defaultValue;
			$input.select();
		};

		this.serializeValue = function () {
			return parseInt($input.val(), 10) || 0;
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
		};

		this.validate = function () {
			if (isNaN($input.val())) {
				return {
					valid: false,
					msg: "Please enter a valid integer"
				};
			}

			if (args.column.validator) {
				var validationResults = args.column.validator($input.val());
				if (!validationResults.valid) {
					return validationResults;
				}
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

	function FloatEditor(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function () {
			$input = $('<input type="number" class="ux-field editor-text" style="width:100%!important; height:100%!important; margin:0!important;">');

			$input.on("keydown.nav", function (e) {
				if (e.keyCode === $.ui.keyCode.LEFT || e.keyCode === $.ui.keyCode.RIGHT) {
					e.stopImmediatePropagation();
				}
			});

			$input.appendTo(args.container);
			$input.focus().select();
		};

		this.destroy = function () {
			$input.remove();
		};

		this.focus = function () {
			$input.focus();
		};

		function getDecimalPlaces() {
				// returns the number of fixed decimal places or null
				var rtn = args.column.editorFixedDecimalPlaces;
				if (typeof rtn == 'undefined') {
						rtn = FloatEditor.DefaultDecimalPlaces;
				}
				return (!rtn && rtn!==0 ? null : rtn);
		}

		this.loadValue = function (item) {
			defaultValue = item[args.column.field];

			var decPlaces = getDecimalPlaces();
			if (decPlaces !== null
			&& (defaultValue || defaultValue===0)
			&& defaultValue.toFixed) {
				defaultValue = defaultValue.toFixed(decPlaces);
			}

			$input.val(defaultValue);
			$input[0].defaultValue = defaultValue;
			$input.select();
		};

		this.serializeValue = function () {
			var rtn = parseFloat($input.val());
			if (FloatEditor.AllowEmptyValue) {
				if (!rtn && rtn !==0) { rtn = ''; }
			} else {
				rtn |= 0;
			}

			var decPlaces = getDecimalPlaces();
			if (decPlaces !== null
			&& (rtn || rtn===0)
			&& rtn.toFixed) {
				rtn = parseFloat(rtn.toFixed(decPlaces));
			}

			return rtn;
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
		};

		this.validate = function () {
			if (isNaN($input.val())) {
				return {
					valid: false,
					msg: "Please enter a valid number"
				};
			}

			if (args.column.validator) {
				var validationResults = args.column.validator($input.val());
				if (!validationResults.valid) {
					return validationResults;
				}
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

	FloatEditor.DefaultDecimalPlaces = null;
	FloatEditor.AllowEmptyValue = false;

	function DateEditor(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var calendarOpen = false;

		this.init = function () {
			$input = $('<input type="text" class="ux-field editor-text" style="width:100%!important; height:100%!important; margin:0!important;">');
			$input.appendTo(args.container);
			$input.focus().select();
			$input.datepicker({
				showOn: "button",
				buttonImageOnly: true,
				 beforeShow: function () {
					calendarOpen = true
				},
				onClose: function () {
					calendarOpen = false
				}
			});
			$input.width($input.width() - 18);
		};

		this.destroy = function () {
			$.datepicker.dpDiv.stop(true, true);
			$input.datepicker("hide");
			$input.datepicker("destroy");
			$input.remove();
		};

		this.show = function () {
			if (calendarOpen) {
				$.datepicker.dpDiv.stop(true, true).show();
			}
		};

		this.hide = function () {
			if (calendarOpen) {
				$.datepicker.dpDiv.stop(true, true).hide();
			}
		};

		this.position = function (position) {
			if (!calendarOpen) {
				return;
			}
			$.datepicker.dpDiv
					.css("top", position.top + 30)
					.css("left", position.left);
		};

		this.focus = function () {
			$input.focus();
		};

		this.loadValue = function (item) {
			defaultValue = item[args.column.field];
			$input.val(defaultValue);
			$input[0].defaultValue = defaultValue;
			$input.select();
		};

		this.serializeValue = function () {
			return $input.val();
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
		};

		this.validate = function () {
			if (args.column.validator) {
				var validationResults = args.column.validator($input.val());
				if (!validationResults.valid) {
					return validationResults;
				}
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

	function YesNoSelectEditor(args) {
		var $select;
		var defaultValue;
		var scope = this;

		this.init = function () {
			$select = $('<select tabIndex="0" class="editor-yesno"><option value="yes">Yes</option><option value="no">No</option></select>');
			$select.appendTo(args.container);
			$select.focus();
		};

		this.destroy = function () {
			$select.remove();
		};

		this.focus = function () {
			$select.focus();
		};

		this.loadValue = function (item) {
			$select.val((defaultValue = item[args.column.field]) ? "yes" : "no");
			$select.select();
		};

		this.serializeValue = function () {
			return ($select.val() == "yes");
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return ($select.val() != defaultValue);
		};

		this.validate = function () {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

	function CheckboxEditor(args) {
		var $select;
		var defaultValue;
		var scope = this;

		this.init = function () {
			$select = $('<input type="checkbox" value="true" class="editor-checkbox" hideFocus>');
			$select.appendTo(args.container);
			$select.focus();
		};

		this.destroy = function () {
			$select.remove();
		};

		this.focus = function () {
			$select.focus();
		};

		this.loadValue = function (item) {
			defaultValue = !!item[args.column.field];
			if (defaultValue) {
				$select.prop('checked', true);
			} else {
				$select.prop('checked', false);
			}
		};

		this.preClick = function () {
				$select.prop('checked', !$select.prop('checked'));
		};

		this.serializeValue = function () {
			return $select.prop('checked');
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return (this.serializeValue() !== defaultValue);
		};

		this.validate = function () {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

	function PercentCompleteEditor(args) {
		var $input, $picker;
		var defaultValue;
		var scope = this;

		this.init = function () {
			$input = $('<input type="text" class="editor-percentcomplete">');
			$input.width($(args.container).innerWidth() - 25);
			$input.appendTo(args.container);

			$picker = $("<div class='editor-percentcomplete-picker'></div>").appendTo(args.container);
			$picker.append("<div class='editor-percentcomplete-helper'><div class='editor-percentcomplete-wrapper'><div class='editor-percentcomplete-slider'></div><div class='editor-percentcomplete-buttons'></div></div></div>");

			$picker.find(".editor-percentcomplete-buttons").append("<button val=0>Not started</button><br><button val=50>In Progress</button><br><button val=100>Complete</button>");

			$input.focus().select();

			$picker.find(".editor-percentcomplete-slider").slider({
				orientation: "vertical",
				range: "min",
				value: defaultValue,
				slide: function (event, ui) {
					$input.val(ui.value)
				}
			});

			$picker.find(".editor-percentcomplete-buttons button").on("click", function (e) {
				$input.val($(this).attr("val"));
				$picker.find(".editor-percentcomplete-slider").slider("value", $(this).attr("val"));
			})
		};

		this.destroy = function () {
			$input.remove();
			$picker.remove();
		};

		this.focus = function () {
			$input.focus();
		};

		this.loadValue = function (item) {
			$input.val(defaultValue = item[args.column.field]);
			$input.select();
		};

		this.serializeValue = function () {
			return parseInt($input.val(), 10) || 0;
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return (!($input.val() == "" && defaultValue == null)) && ((parseInt($input.val(), 10) || 0) != defaultValue);
		};

		this.validate = function () {
			if (isNaN(parseInt($input.val(), 10))) {
				return {
					valid: false,
					msg: "Please enter a valid positive number"
				};
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

	/*
	 * An example of a "detached" editor.
	 * The UI is added onto document BODY and .position(), .show() and .hide() are implemented.
	 * KeyDown events are also handled to provide handling for Tab, Shift-Tab, Esc and Ctrl-Enter.
	 */
	function LongTextEditor(args) {
		var $input, $wrapper;
		var defaultValue;
		var scope = this;

		this.init = function () {
			var $container = $("body");

			$wrapper = $("<div style='z-index:10000;position:absolute;background:white;padding:5px;border:3px solid gray; -moz-border-radius:10px; border-radius:10px;'></div>")
					.appendTo($container);

			$input = $('<textarea hidefocus rows="5" style="background:white;width:250px;height:80px;border:0;outline:0"></textarea>')
					.appendTo($wrapper);

			$("<div style='text-align:right'><button>Save</button><button>Cancel</button></div>")
					.appendTo($wrapper);

			$wrapper.find("button:first").on("click", this.save);
			$wrapper.find("button:last").on("click", this.cancel);
			$input.on("keydown", this.handleKeyDown);

			scope.position(args.position);
			$input.focus().select();
		};

		this.handleKeyDown = function (e) {
			if (e.which == $.ui.keyCode.ENTER && e.ctrlKey) {
				scope.save();
			} else if (e.which == $.ui.keyCode.ESCAPE) {
				e.preventDefault();
				scope.cancel();
			} else if (e.which == $.ui.keyCode.TAB && e.shiftKey) {
				e.preventDefault();
				args.grid.navigatePrev();
			} else if (e.which == $.ui.keyCode.TAB) {
				e.preventDefault();
				args.grid.navigateNext();
			}
		};

		this.save = function () {
			args.commitChanges();
		};

		this.cancel = function () {
			$input.val(defaultValue);
			args.cancelChanges();
		};

		this.hide = function () {
			$wrapper.hide();
		};

		this.show = function () {
			$wrapper.show();
		};

		this.position = function (position) {
			$wrapper
					.css("top", position.top - 5)
					.css("left", position.left - 5)
		};

		this.destroy = function () {
			$wrapper.remove();
		};

		this.focus = function () {
			$input.focus();
		};

		this.loadValue = function (item) {
			$input.val(defaultValue = item[args.column.field]);
			$input.select();
		};

		this.serializeValue = function () {
			return $input.val();
		};

		this.applyValue = function (item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function () {
			return (!($input.val() == "" && defaultValue == null)) && ($input.val() != defaultValue);
		};

		this.validate = function () {
			if (args.column.validator) {
				var validationResults = args.column.validator($input.val());
				if (!validationResults.valid) {
					return validationResults;
				}
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}
})(jQuery);
