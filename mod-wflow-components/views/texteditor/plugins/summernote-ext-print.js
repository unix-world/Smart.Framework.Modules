
// (c) 2019-2020 unix-world.org
// License: GPLv3
// v.20200501
// contains fixes by by unixman

// Copyright 2013-2019 Alan Hong. and other contributors
// License: MIT

(function (factory) {
	/* global define */
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		// Node/CommonJS
		module.exports = factory(require('jquery'));
	} else {
		// Browser globals
		factory(window.jQuery);
	}
}(function ($) {

	// Extends lang for print plugin.
	$.extend(true, $.summernote.lang, {
		'en-US': {
			print: {
				print: 'Print'
			}
		},
		'ro-RO': {
			print: {
				print: 'Printează'
			}
		}
	});

	// Extends plugins for print plugin.
	$.extend($.summernote.plugins, {
		/**
		 * @param {Object} context - context object has status of editor.
		 */
		'print': function (context) {
			var self = this;

			// ui has renders to build ui elements.
			//  - you can create a button with `ui.button`
			var ui = $.summernote.ui;
			var $editor = context.layoutInfo.editor;
			var options = context.options;
			var lang = options.langInfo;

			var isFF = function () {
				var userAgent = navigator.userAgent;
				var isEdge = /Edge\/\d+/.test(userAgent);
				return !isEdge && /firefox/i.test(userAgent)
			}

			var fillContentAndPrint = function($frame, content) {

				var $head = $frame.contents().find('head');
				$head.append('<meta charset="UTF-8"><title>Document</title>');
				if(options.print && options.print.stylesheetUrl) {
					var css = null;
					for(var i=0; i<options.print.stylesheetUrl.length; i++) {
						// Use dedicated styles
						if(options.print.stylesheetUrl[i]) {
							css = document.createElement('link');
							css.href = String(options.print.stylesheetUrl[i]);
							css.rel = 'stylesheet';
							css.type = 'text/css';
							css.media = 'print';
							$head.append(css);
						//	console.log(css.href);
						}
					}
					css = null;
				}
			/*	else {
					// Inherit styles from document
					$('style, link[rel=stylesheet]', document).each(function () {
						$head.append($(this).clone());
					});
				} */

				$frame.contents().find('body').addClass('note-printable').append(content);

				setTimeout(function () {
					$frame[0].contentWindow.focus();
					$frame[0].contentWindow.print();
					$frame.remove();
					$frame = null;
				}, 250);
			}

			var getPrintframe = function ($container) {
				var $frame = $('<iframe name="summernotePrintFrame" width="0" height="0" frameborder="0" src="about:blank" style="visibility:hidden"></iframe>');
				$frame.appendTo($editor.parent());
				return $frame;
			};

			// add print button
			context.memo('button.print', function () {
				// create button
				var button = ui.button({
					contents: '<i class="sfi sfi-printer"></i>',
					tooltip: lang.print.print,
					container: options.container,
					click: function () {
						var $frame = getPrintframe();
						var content = context.invoke('code');

						if (isFF()) {
							$frame[0].onload = function () {
								fillContentAndPrint($frame, content);
							};
						} else {
							fillContentAndPrint($frame, content);
						}
					}
				});
				return button.render();
			});
		}
	});
}));

// #END
