
// jquery.caret.js - Get or Set the the caret's position in a textArea or another input
// Developed by Copyright (c) 2009, Gideon Sireling, https://github.com/accursoft/caret
// License: BSD3
// v.head.20191123
// contains fixes by unixman

(function($) {
	function focus(target) {
		if (!document.activeElement || document.activeElement !== target) {
			target.focus();
		}
	}
	$.fn.caret = function(pos) {
		var target = this[0];
		var isContentEditable = target && target.contentEditable === 'true';
		if (arguments.length == 0) {
			//get
			if (target) {
				//HTML5
				if (window.getSelection) {
					//contenteditable
					if (isContentEditable) {
						focus(target);
						var selection = window.getSelection();
						// Opera 12 check
						if (!selection.rangeCount) {
							return 0;
						}
						var range1 = selection.getRangeAt(0),
								range2 = range1.cloneRange();
						range2.selectNodeContents(target);
						range2.setEnd(range1.endContainer, range1.endOffset);
						return range2.toString().length;
					}
					//textarea
					return target.selectionStart;
				}
				//IE<9
				if (document.selection) {
					focus(target);
					//contenteditable
					if (isContentEditable) {
							var range1 = document.selection.createRange(),
									range2 = document.body.createTextRange();
							range2.moveToElementText(target);
							range2.setEndPoint('EndToEnd', range1);
							return range2.text.length;
					}
					//textarea
					var pos = 0,
							range = target.createTextRange(),
							range2 = document.selection.createRange().duplicate(),
							bookmark = range2.getBookmark();
					range.moveToBookmark(bookmark);
					while (range.moveStart('character', -1) !== 0) pos++;
					return pos;
				}
				// Addition for jsdom support
				if (target.selectionStart)
					return target.selectionStart;
			}
			//not supported
			return;
		}
		//set
		if (target) {
			if (pos == -1)
				pos = this[isContentEditable? 'text' : 'val']().length;
			//HTML5
			if (window.getSelection) {
				//contenteditable
				if (isContentEditable) {
					focus(target);
					try { // fix by unixman, add try/catch
						window.getSelection().collapse(target.firstChild, pos);
					} catch(err){}
				} else { //textarea
					target.setSelectionRange(pos, pos);
				}
			} else if(document.body.createTextRange) { //IE<9
				if (isContentEditable) {
					var range = document.body.createTextRange();
					range.moveToElementText(target);
					range.moveStart('character', pos);
					range.collapse(true);
					range.select();
				} else {
					var range = target.createTextRange();
					range.move('character', pos);
					range.select();
				}
			}
			if (!isContentEditable)
				focus(target);
		}
		return this;
	}
})(jQuery);

// #END
