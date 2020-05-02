
// (c) 2019-2020 unix-world.org
// License: GPLv3
// v.20200501
// modified by unixman

// License: MIT
// https://github.com/DiemenDesign/summernote-paper-size

function summertnotePaperSize_Screen() {
//	$('.note-frame').addClass('note-document');
	$('.note-editing-area').attr('data-papersize','Screen');
	$('.note-editable').css({'width':'100%','min-height':'500px'});
}
function summertnotePaperSize_A4() {
//	$('.note-frame').addClass('note-document');
	$('.note-editing-area').attr('data-papersize','A4').addClass('a4');
	$('.note-editable').css({'width':'210mm','min-height':'297mm'}); // 595px 842px
}
function summertnotePaperSize_A4L() {
//	$('.note-frame').addClass('note-document');
	$('.note-editing-area').attr('data-papersize','A4 Landscape').addClass('a4l');
	$('.note-editable').css({'width':'297mm','min-height':'210mm'}); // 842px 595px
}
/*
function summertnotePaperSize_A3() {
//	$('.note-frame').addClass('note-document');
	$('.note-editing-area').attr('data-papersize','A3').addClass('a3');
	$('.note-editable').css({'width':'297mm','min-height':'420mm'}); // 842px 1191px
}
function summertnotePaperSize_A5() {
//	$('.note-frame').addClass('note-document');
	$('.note-editing-area').attr('data-papersize','A5').addClass('a5');
	$('.note-editable').css({'width':'148mm','min-height':'210mm'}); // 420px 595px
}
*/

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery'],factory);
	} else if (typeof module === 'object' && module.exports) {
		module.exports = factory(require('jquery'));
	} else {
		factory(window.jQuery);
	}
}(function ($) {
	$.extend(true, $.summernote.lang, {
		'en-US': {
			paperSize: {
				tooltip: 'Paper Size'
			}
		}
	});
	$.extend($.summernote.options, {
		paperSize: {
			icon: '<i class="sfi sfi-file-text"></i> ',
			menu: [
				'Screen',
				'A4',
				'A4 Landscape'
			],
			css: '.note-editor.note-frame.note-document {display:block; height:auto;}' +
				 '.note-editor.note-frame.note-document .note-editing-area {background-color:#333333;overflow:auto;height:calc(100% - 57px)!important;padding:1px;}' +
				 '.note-editor.note-frame.note-document .note-editing-area .note-editable {display:block;overflow:visible;box-sizing:border-box;margin:2px auto 2px auto;border:25px solid #fcfcfc;min-height:calc100%;height:auto!important;}'
		}
	});
	$.extend($.summernote.plugins, {
		'paperSize': function(context) {
			var ui        = $.summernote.ui,
				$note     = context.layoutInfo.note,
				options   = context.options,
				lang      = options.langInfo;
			$('.note-frame').addClass('note-document');
			$('head').append('<style>' + options.paperSize.css + '</style>');
			context.memo('button.paperSize', function () {
				var button = ui.buttonGroup([
					ui.button({
						className: 'dropdown-toggle',
						contents:  options.paperSize.icon,
						tooltip:   lang.paperSize.tooltip,
						container: options.container, // tooltip fix by unixman
						data: {
							toggle: 'dropdown'
						}
					}),
					ui.dropdown({
						className: 'dropdown-template',
						items: options.paperSize.menu,
						click: function (e) {
							var $button = $(e.target);
							var value = $button.data('value');
							e.preventDefault();
						//	$('.note-frame').removeClass('note-document');
							$('.note-editing-area').attr('data-papersize','').removeClass('a4').removeClass('a4l');
							switch (value){
								case 'A4':
									summertnotePaperSize_A4();
									break;
								case 'A4 Landscape':
									summertnotePaperSize_A4L();
									break;
								case 'Screen':
								default:
									summertnotePaperSize_Screen();
							}
						}
					})
				]);
				return button.render();
			});
		}
	});
}));

// #END
