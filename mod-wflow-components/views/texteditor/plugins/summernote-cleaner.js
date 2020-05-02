
// (c) 2019-2020 unix-world.org
// License: GPLv3
// v.20200501
// contains fixes by by unixman

// License: MIT
// https://github.com/DiemenDesign/summernote-cleaner

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		module.exports = factory(require('jquery'));
	} else {
		factory(window.jQuery);
	}
}
(function ($) {
	$.extend(true, $.summernote.lang, {
		'en-US': {
			cleaner: {
				tooltip: 'Cleaner',
				not: 'Text has been Cleaned!!!',
				limitText: 'Text',
				limitHTML: 'HTML'
			}
		}
	});
	$.extend($.summernote.options, {
		cleaner: {
			action: 'both', // both|button|paste 'button' only cleans via toolbar button, 'paste' only clean when pasting content, both does both options.
			newline: '<br>', // Summernote's default is to use '<p><br></p>'
			notStyle: 'position:absolute;top:0;left:0;right:0',
			icon: '<i class="sfi sfi-command"></i>',
			keepHtml: true, //Remove all Html formats
			keepOnlyTags: [], // If keepHtml is true, remove all tags except these
			keepClasses: false, //Remove Classes
			badTags: ['style', 'script', 'applet', 'embed', 'noframes', 'noscript', 'html'], //Remove full tags with contents
			badAttributes: ['style', 'start'], //Remove attributes from remaining tags
			limitChars: 520, // 0|# 0 disables option
			limitDisplay: 'both', // none|text|html|both
			limitStop: false // true/false
		}
	});
	$.extend($.summernote.plugins, {
		'cleaner':function (context) {
			var self = this,
						ui = $.summernote.ui,
				 $note = context.layoutInfo.note,
			 $editor = context.layoutInfo.editor,
			 options = context.options,
					lang = options.langInfo;
			var cleanText = function (txt, nlO) {
				var out = txt;
				if (!options.cleaner.keepClasses) {
					var sS = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g;
						 out = txt.replace(sS, ' ');
				}
				var nL = /(\n)+/g;
					 out = out.replace(nL, nlO);
				if (options.cleaner.keepHtml) {
					var cS = new RegExp('<!--(.*?)-->', 'gi');
						 out = out.replace(cS, '');
					var tS = new RegExp('<(/)*(meta|link|\\?xml:|st1:|o:|font)(.*?)>', 'gi');
						 out = out.replace(tS, '');
					var bT = options.cleaner.badTags;
					for (var i = 0; i < bT.length; i++) {
						tS = new RegExp('<' + bT[i] + '\\b.*>.*</' + bT[i] + '>', 'gi');
						out = out.replace(tS, '');
					}
					var allowedTags = options.cleaner.keepOnlyTags;
					if (typeof(allowedTags) == "undefined") allowedTags = [];
					if (allowedTags.length > 0) {
						allowedTags = (((allowedTags||'') + '').toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
							 var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
										out = out.replace(tags, function($0, $1) {
							return allowedTags.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : ''
						});
					}
					var bA = options.cleaner.badAttributes;
					for (var ii = 0; ii < bA.length; ii++ ) {
						//var aS=new RegExp(' ('+bA[ii]+'="(.*?)")|('+bA[ii]+'=\'(.*?)\')', 'gi');
						var aS = new RegExp(' ' + bA[ii] + '=[\'|"](.*?)[\'|"]', 'gi');
							 out = out.replace(aS, '');
					}
				}
				return out;
			};
			if (options.cleaner.action == 'both' || options.cleaner.action == 'button') {
				context.memo('button.cleaner', function () {
					var button = ui.button({
						contents: options.cleaner.icon,
						tooltip: lang.cleaner.tooltip,
						container: 'body',
						click:function () {
							if ($note.summernote('createRange').toString())
								$note.summernote('pasteHTML', $note.summernote('createRange').toString());
							else
								$note.summernote('code', cleanText($note.summernote('code')));
							if ($editor.find('.note-status-output').length > 0)
								$editor.find('.note-status-output').html('<div class="alert alert-success">' + lang.cleaner.not + '</div>');
							else
								$editor.find('.note-editing-area').append('<div class="alert alert-success" style="' + options.cleaner.notStyle + '">' + lang.cleaner.not + '</div>');
						}
					});
					return button.render();
				});
			}
			this.events = {
				'summernote.init':function () {
					if ($editor.find('.note-status-output').length < 1) {
						$editor.find('.note-statusbar').prepend('<output class="note-status-output"></output>');
						$("head").append('<style>.note-statusbar .note-status-output{display:block;padding-top:7px;width:100%;font-size:14px;line-height:1.42857143;height:25px;color:#000}.note-statusbar .pull-right{float:right!important}.note-statusbar .note-status-output .text-muted{color:#777}.note-statusbar .note-status-output .text-primary{color:#286090}.note-statusbar .note-status-output .text-success{color:#3c763d}.note-statusbar .note-status-output .text-info{color:#31708f}.note-statusbar .note-status-output .text-warning{color:#8a6d3b}.note-statusbar .note-status-output .text-danger{color:#a94442}.note-statusbar .alert{margin:-7px 0 0 0;padding:2px 10px;border:1px solid transparent;border-radius:0}.note-statusbar .alert .note-icon{margin-right:5px}.note-statusbar .alert-success{color:#3c763d!important;background-color: #dff0d8 !important;border-color:#d6e9c6}.note-statusbar .alert-info{color:#31708f;background-color:#d9edf7;border-color:#bce8f1}.note-statusbar .alert-warning{color:#8a6d3b;background-color:#fcf8e3;border-color:#faebcc}.note-statusbar .alert-danger{color:#a94442;background-color:#f2dede;border-color:#ebccd1}</style>');
					}
					if (options.cleaner.limitChars != 0 || options.cleaner.limitDisplay != 'none') {
							var textLength = $editor.find(".note-editable").text(); //.replace(/(<([^>]+)>)/ig, "").replace(/( )/, " ");
							//console.log('`' + textLength + '`');
							var codeLength = $editor.find('.note-editable').html();
						var lengthStatus = '';
						if (textLength.length > options.cleaner.limitChars && options.cleaner.limitChars > 0)
							lengthStatus += 'text-danger">';
						else
							lengthStatus += '">';
						if (options.cleaner.limitDisplay == 'text' || options.cleaner.limitDisplay == 'both') lengthStatus += lang.cleaner.limitText + ': ' + textLength.length;
						if (options.cleaner.limitDisplay == 'both') lengthStatus += ' / ';
						if (options.cleaner.limitDisplay == 'html' || options.cleaner.limitDisplay == 'both') lengthStatus += lang.cleaner.limitHTML + ': ' + codeLength.length;
						$editor.find('.note-status-output').html('<small class="pull-right ' + lengthStatus + '&nbsp;</small>');
					}
				},
			//	'summernote.keydown':function (we, e) { // fixed by unixman
				'summernote.keyup':function (we, e) {
					if (options.cleaner.limitChars != 0 || options.cleaner.limitDisplay != 'none') {
							var textLength =  $editor.find(".note-editable").text(); //.replace(/(<([^>]+)>)/ig, "").replace(/( )/, " ");
							//console.log('@`' + textLength + '`');
							var codeLength =  $editor.find('.note-editable').html();
						var lengthStatus = '';
						if (options.cleaner.limitStop == true && textLength.length >= options.cleaner.limitChars) {
									 var key = e.keyCode;
							allowed_keys = [8, 37, 38, 39, 40, 46]
							if ($.inArray(key, allowed_keys) != -1) {
								$editor.find('.cleanerLimit').removeClass('text-danger');
								return true;
							} else {
								$editor.find('.cleanerLimit').addClass('text-danger');
								e.preventDefault();
								e.stopPropagation();
							}
						} else {
							if (textLength.length > options.cleaner.limitChars && options.cleaner.limitChars > 0)
								lengthStatus += 'text-danger">';
							else
								lengthStatus += '">';
							if (options.cleaner.limitDisplay == 'text' || options.cleaner.limitDisplay == 'both')
								lengthStatus += lang.cleaner.limitText + ': ' + textLength.length;
							if (options.cleaner.limitDisplay == 'both')
								lengthStatus += ' / ';
							if (options.cleaner.limitDisplay == 'html' || options.cleaner.limitDisplay == 'both')
								lengthStatus += lang.cleaner.limitHTML + ': ' + codeLength.length;
							$editor.find('.note-status-output').html('<small class="cleanerLimit pull-right ' + lengthStatus + '&nbsp;</small>');
						}
					}
				},
				'summernote.paste':function (we, e) {
					if (options.cleaner.action == 'both' || options.cleaner.action == 'paste') {
						e.preventDefault();
							var ua = window.navigator.userAgent;
						var msie = ua.indexOf("MSIE ");
								msie = msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./);
						var ffox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
						if (msie)
							var text = window.clipboardData.getData("Text");
						else
							var text = e.originalEvent.clipboardData.getData(options.cleaner.keepHtml ? 'Text' : 'text/plain');
						if (text) {
							if (msie || ffox)
								setTimeout(function(){$note.summernote('pasteHTML', cleanText(text, options.cleaner.newline));}, 1);
							else
								$note.summernote('pasteHTML', cleanText(text, options.cleaner.newline));
							if ($editor.find('.note-status-output').length > 0)
								$editor.find('.note-status-output').html('<div class="summernote-cleanerAlert alert alert-success">' + lang.cleaner.not + '</div>');
							else
								$editor.find('.note-resizebar').append('<div class="summernote-cleanerAlert alert alert-success" style="' + options.cleaner.notStyle + '">' + lang.cleaner.not + '</div>');
						}
					}
				}
			}
		}
	});
}));

// #END
