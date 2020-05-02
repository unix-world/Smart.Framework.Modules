
// (c) 2019-2020 unix-world.org
// License: GPLv3
// v.20200501
// modified by unixman

// License: MIT
// https://github.com/DiemenDesign/summernote-pagebreak

(function(factory) {
	if(typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		module.exports = factory(require('jquery'));
	} else {
		factory(window.jQuery);
	}
}
(function($) {
	$.extend(true,$.summernote.lang, {
		'en-US': {
			pagebreak: {
				tooltip: 'Page Break'
			}
		}
	});
	$.extend($.summernote.options, {
		pagebreak: {
			icon: '<i class="sfi sfi-pagebreak"></i>'
		}
	});
	$.extend($.summernote.plugins, {
		'pagebreak': function(context) {
			var ui      = $.summernote.ui;
			var options = context.options;
			var lang    = options.langInfo;
			$("head").append('<style>' + options.pagebreak.css + '</style>');
			context.memo('button.pagebreak',function() {
				var button = ui.button({
					contents: options.pagebreak.icon,
					tooltip:  lang.pagebreak.tooltip,
					container: 'body',
					click: function (e) {
						e.preventDefault();
						if (getSelection().rangeCount > 0) {
							var el = getSelection().getRangeAt(0).commonAncestorContainer.parentNode;
							if ($(el).hasClass('note-editable')) {
								el = getSelection().getRangeAt(0).commonAncestorContainer;
							}
							if (!$(el).hasClass('page-break')) {
								if ($(el).next('div.page-break').length < 1)
									$('<div class="page-break"></div>').insertAfter(el);
							}
						} else {
							if ($('.note-editable div').last().attr('class') !== 'page-break')
								$('.note-editable').append('<div class="page-break"></div>');
						}

						// Launching this method to force Summernote sync it's content with the bound textarea element
						context.invoke('editor.insertText','');
					}
				});
				return button.render();
			});
		}
	});
}));

// #END
