
// (c) 2019-2021 unix-world.org
// License: GPLv3
// v.20210411
// modified by unixman

// Copyright 2013-2019 Alan Hong. and other contributors
// Copyright 2017 Diemen Design
// License: MIT

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		module.exports = factory(require('jquery'));
	} else {
		factory(window.jQuery);
	}
}(function ($) {
	$.extend(true,$.summernote.lang, {
		'en-US': { /* English */
			findnreplace: {
				tooltip:            'Find and Replace',
				findBtn:            'Find ',
				findPlaceholder:    'Text to Find...',
				findResult:         ' results found for ',
				findError:          'Nothing entered to Find...',
				replaceBtn:         'Replace',
				replacePlaceholder: 'Text to Replace the text from Find or Selected...',
				replaceResult:      ', replaced by ',
				replaceError:       'Nothing entered to Replace...',
				noneSelected:       'Nothing selected to Replace...'
			}
		}
	});
	$.extend($.summernote.options, {
		findnreplace: {
			highlight: 'border-bottom: 3px solid #fc0; text-decoration: none;',
			icon:      '<i class="sfi sfi-binoculars" data-toggle="findnreplace"></i>'
		}
	});
	$.extend($.summernote.plugins, {
		'findnreplace': function (context) {
			var ui       = $.summernote.ui;
			var $note    = context.layoutInfo.note;
			var $editor  = context.layoutInfo.editor;
			var $toolbar = context.layoutInfo.toolbar;
			var options  = context.options;
			var lang     = options.langInfo;
			context.memo('button.findnreplace', function() {
				var button = ui.button({
					contents: options.findnreplace.icon,
					tooltip:  lang.findnreplace.tooltip,
					container: options.container, // tooltip fix by unixman
					click: function (e) {
						e.preventDefault();
						$editor.find('.note-findnreplace').contents().unwrap('u');
						$('#findnreplaceToolbar').toggleClass('hidden');
						$('#findnreplace-info').text('');
						if ($note.summernote('createRange').toString()) {
							var selected = $note.summernote('createRange').toString();
							$('#note-findnreplace-find').val(selected);
						}
					}
				});
				return button.render();
			});
			this.initialize = function () {
				var fnrBody = '<div id="findnreplaceToolbar" class="note-toolbar-wrapper panel-heading hidden">' +
					'<hr>' +
					'<div class="form-group">' +
						'<div style="width:49%; display:inline-block; text-align:left;">' +
							'<button class="ux-button ux-button-small btn btn-primary note-btn note-btn-primary note-image-btn note-findnreplace-find-btn" style="width: 100px;">' + lang.findnreplace.findBtn + '</button>' +
							'<input id="note-findnreplace-find" type="text" class="ux-field note-findnreplace-find form-control input-sm" value="" placeholder="' + lang.findnreplace.findPlaceholder + '">' +
						'</div>' +
						'<div style="width:49%; display:inline-block; text-align:right;">' +
							'<input id="note-findnreplace-replace" type="text" class="ux-field note-findnreplace-replace form-control input-sm" value="" placeholder="' + lang.findnreplace.replacePlaceholder + '">' +
							'<button class="ux-button ux-button-small btn btn-primary note-btn note-btn-primary note-image-btn note-findnreplace-replace-btn" style="width: 100px;">' + lang.findnreplace.replaceBtn + '</button>' +
						'</div>' +
					'</div>' +
				'</div>';
				$('.note-toolbar').append(fnrBody);
				this.show();
			};
			this.findnreplace = function() {
				var $fnrFindBtn    = $toolbar.find('.note-findnreplace-find-btn');
				var $fnrReplaceBtn = $toolbar.find('.note-findnreplace-replace-btn');
				$fnrFindBtn.click(function (e) {
					e.preventDefault();
					$editor.find('.note-findnreplace').contents().unwrap('mark');
					var fnrCode    = context.invoke('code');
					var fnrFind    = smartJ$Utils.escape_html($('.note-findnreplace-find').val()); // bugfix (unixman): escape to HTML to avoid break the HTML code
					var fnrReplace = smartJ$Utils.escape_html($('.note-findnreplace-replace').val()); // bugfix (unixman): escape to HTML to avoid break the HTML code
					var fnrCount   = (fnrCode.match(new RegExp(fnrFind + "(?![^<>]*>)", "gi")) || []).length;
					if (fnrFind) {
						$('#findnreplace-info').text(fnrCount + lang.findnreplace.findResult + "`" + fnrFind + "`");
						var fnrReplaced = fnrCode.replace(new RegExp(fnrFind + "(?![^<>]*>)", "gi"), function(e){return '<mark class="note-findnreplace">' + e + '</mark>';});
						$note.summernote('code',fnrReplaced);
					} else
						$('#findnreplace-info').html('<span class="text-danger">' + lang.findnreplace.findError + '</span>');
				});
				$fnrReplaceBtn.click(function (e) {
					e.preventDefault();
					$editor.find('.note-findnreplace').contents().unwrap('mark');
					var fnrCode    = context.invoke('code');
					var fnrFind    = smartJ$Utils.escape_html($('.note-findnreplace-find').val()); // bugfix (unixman): escape to HTML to avoid break the HTML code
					var fnrReplace = smartJ$Utils.escape_html($('.note-findnreplace-replace').val()); // bugfix (unixman): escape to HTML to avoid break the HTML code
				//	var fnrCount   = (fnrCode.match(new RegExp(fnrFind, "gi")) || []).length;
					var fnrCount   = (fnrCode.match(new RegExp(fnrFind + "(?![^<>]*>)", "gi")) || []).length;
					if (fnrFind) {
						$('#findnreplace-info').text(fnrCount + lang.findnreplace.findResult + "`" + fnrFind + "`" + lang.findnreplace.replaceResult +"`" + fnrReplace + "`");
						var fnrReplaced = fnrCode.replace(new RegExp(fnrFind + "(?![^<>]*>)", "gi"), fnrReplace);
						$note.summernote('code', fnrReplaced);
					} else {
						if (fnrReplace) {
							if ($note.summernote('createRange').toString()) {
								$note.summernote('insertText',fnrReplace);
								$('#findnreplace-info').text('');
							} else
								$('#findnreplace-info').html('<span class="text-danger">' + lang.findnreplace.noneSelected + '</span>');
						} else
							$('#findnreplace-info').html('<span class="text-danger">' + lang.findnreplace.replaceError + '</span>');
					}
				});
			};
			this.show = function() {
				this.findnreplace();
			};
		}
	});
}));

// #END
