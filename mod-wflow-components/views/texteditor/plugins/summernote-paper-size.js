
// (c) 2019 unix-world.org
// License: GPLv3
// v.20190207
// modified by unixman

// License: MIT
// https://github.com/DiemenDesign/summernote-paper-size

function summertnotePaperSize_A3() {
	$('.note-frame').addClass('note-document');
	$('.note-editing-area').addClass('a3');
	$('.note-editable').css({'width':'297mm','min-height':'420mm'}); // 842px 1191px
}
function summertnotePaperSize_A4() {
	$('.note-frame').addClass('note-document');
	$('.note-editing-area').addClass('a4');
	$('.note-editable').css({'width':'210mm','min-height':'297mm'}); // 595px 842px
}
function summertnotePaperSize_A5() {
	$('.note-frame').addClass('note-document');
	$('.note-editing-area').addClass('a5');
	$('.note-editable').css({'width':'148mm','min-height':'210mm'}); // 420px 595px
}

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
			icon: '<i class="note-icon"><svg role="img" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" width="14" height="14"><path d="M 7.5194922,8.03901 5.9609687,6.48048 3.8757344,8.56572 2.4693906,7.15937 l 0,4.37124 4.3712344,0 -1.4063437,-1.40637 2.0852109,-2.08523 z m -4.3154063,2.7569 0,-1.86283 0.6716719,0.67167 2.0852344,-2.08523 0.5194922,0.51949 -2.0852344,2.08523 0.6716484,0.67165 -1.8628125,0 z M 7.159375,2.46939 8.5657187,3.87576 6.4804844,5.96099 l 1.5585,1.5585 2.0852346,-2.08523 1.406367,1.40634 0,-4.37121 -4.371211,0 z M 10.795914,5.06692 10.124242,4.39525 8.0390078,6.48048 7.5195156,5.96099 9.60475,3.87576 8.9331016,3.20411 l 1.8628354,0 0,1.86281 z M 1,1 1,13 13,13 13,1 1,1 Z m 11.265305,11.2653 -10.5306097,0 0,-10.5306 10.5306097,0 0,10.5306 z"/></svg></i> ',
			menu: [
				'Screen',
				'A3 Paper',
				'A4 Paper',
				'A5 Paper'
			],
/*			css: '.note-editor.note-frame.note-document{display:block;overflow:none}' +
					 '.note-editor.note-frame.note-document .note-editing-area{background-color:#333333;overflow:auto}' +
					 '.note-editor.note-frame .note-editing-area .note-editable{overflow:auto;border-radius:0;box-shadow:0;width:100%}' +
					 '.note-editor.note-frame.note-document .note-editing-area .note-editable{display:block;overflow:visible;box-sizing:border-box;margin:2px auto 2px auto;border:25px solid #fcfcfc;border-radius:5px;box-shadow:0 0 5px rgba(0,0,0,.1)}' + // margin:40px auto 2px auto; overflow:hidden;overflow-y:auto
					 '.note-editor.note-frame.note-document .note-editing-area.a3:before,' +
					 '.note-editor.note-frame.note-document .note-editing-area.a3:after,' +
					 '.note-editor.note-frame.note-document .note-editing-area.a4:before,' +
					 '.note-editor.note-frame.note-document .note-editing-area.a4:after,' +
					 '.note-editor.note-frame.note-document .note-editing-area.a5:before,' +
					 '}' +
					 '.note-editor.note-frame.note-document .note-editing-area.a3:before{content:"A3";font-size:32px;font-weight:700;color:#ddd;position:absolute;top:0;right:10px}' +
					 '.note-editor.note-frame.note-document .note-editing-area.a3:after{content:"842 x 1191";font-size:14px;top:18px;right:55px;}' +
					 '.note-editor.note-frame.note-document .note-editing-area.a4:before{content:"A4";font-size:32px;font-weight:700;color:#ddd;position:absolute;top:0;right:10px}' +
					 '.note-editor.note-frame.note-document .note-editing-area.a4:after{content:"595 x 842";font-size:14px;top:18px;right:55px;}' +
					 '.note-editor.note-frame.note-document .note-editing-area.a5:before{content:"A5";font-size:32px;font-weight:700;color:#ddd;position:absolute;top:0;right:10px}' +
					 '.note-editor.note-frame.note-document .note-editing-area.a5:after{content:"420 x 595";font-size:14px;top:18px;right:55px;}',
*/
			css: '.note-editor.note-frame.note-document {display:block; height:' + 'calc(100vh - 50px);' + '}' +
				 '.note-editor.note-frame.note-document .note-editing-area {background-color:#333333;overflow:auto;height:calc(100% - 57px)!important;}' +
				 '.note-editor.note-frame.note-document .note-editing-area .note-editable {display:block;overflow:visible;box-sizing:border-box;margin:2px auto 2px auto;border:25px solid #fcfcfc;min-height:calc100%;height:auto!important;}'
		}
	});
	$.extend($.summernote.plugins, {
		'paperSize': function(context) {
			var ui        = $.summernote.ui,
					$note     = context.layoutInfo.note,
					options   = context.options,
					lang      = options.langInfo;
			$("head").append('<style>' + options.paperSize.css + '</style>');
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
							$('.note-frame').removeClass('note-document');
							$('.note-editing-area').removeClass('a3').removeClass('a4').removeClass('a5');
							switch (value){
								case 'A3 Paper':
									summertnotePaperSize_A3();
									break;
								case 'A4 Paper':
									summertnotePaperSize_A4();
									break;
								case 'A5 Paper':
									summertnotePaperSize_A5();
									break;
								case 'Screen':
								default:
									$('.note-frame').removeClass('note-document');
									$('.note-editing-area').removeClass('a3').removeClass('a4').removeClass('a5');
									$('.note-editable').css({'width':'100%','height':'100%'});
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
