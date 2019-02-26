
// (c) 2019 unix-world.org
// License: GPLv3
// v.20190225

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
					contents: '<svg width="16" height="16" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M448 1536h896v-256h-896v256zm0-640h896v-384h-160q-40 0-68-28t-28-68v-160h-640v640zm1152 64q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm128 0v416q0 13-9.5 22.5t-22.5 9.5h-224v160q0 40-28 68t-68 28h-960q-40 0-68-28t-28-68v-160h-224q-13 0-22.5-9.5t-9.5-22.5v-416q0-79 56.5-135.5t135.5-56.5h64v-544q0-40 28-68t68-28h672q40 0 88 20t76 48l152 152q28 28 48 76t20 88v256h64q79 0 135.5 56.5t56.5 135.5z"/></svg>',
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
