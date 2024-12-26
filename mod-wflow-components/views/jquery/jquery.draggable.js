
// Original Source: https://css-tricks.com/snippets/jquery/draggable-without-jquery-ui/
// License: PUBLIC DOMAIN, http://www.wtfpl.net/txt/copying/

// r.20190209
// (c) 2019 unix-world.org
// License: BSD

(function($) {

	$.fn.draggable = function(opt) {

		opt = $.extend({
			handle: '',
			drag: null, // or function
			axis: null, // null | x | y
			cursor: 'move',
			cssclass: 'ui-draggable-dragging',
			csshandleclass: 'ui-draggable-handle'
		}, opt);

		var allowX = true;
		var allowY = true;
		if(opt.axis == 'x') {
			allowY = false;
		} else if(opt.axis == 'y') {
			allowX = false;
		}

		if(!opt.handle) {
			var $el = this;
		} else if(typeof opt.handle == 'object') {
			var $el = opt.handle; // expects jquery object
			var $target = this;
		} else {
			var $el = this.find(opt.handle);
			var $target = this;
		}

		var isInViewport = function($theElem) {
			var elementTop = $theElem.offset().top;
			var elementBottom = elementTop + $theElem.outerHeight();
			var viewportTop = $(window).scrollTop();
			var viewportBottom = viewportTop + $(window).height();
			return elementBottom > viewportTop && elementTop < viewportBottom;
		};

		return $el.css('cursor', opt.cursor).on('mousedown', function(e) {
			if(!e || !e.pageX || !e.pageY) {
				return;
			}
			if(!opt.handle) {
				var $drag = $(this).addClass(opt.cssclass);
			} else {
				var $drag = $target.addClass(opt.cssclass);
				$(this).addClass(opt.csshandleclass);
			}
			var z_idx = $drag.css('z-index'),
				drg_h = $drag.outerHeight(),
				drg_w = $drag.outerWidth(),
				orig_x = $drag.offset().left,
				orig_y = $drag.offset().top,
				pos_y = orig_y + drg_h - e.pageY,
				pos_x = orig_x + drg_w - e.pageX;
			$drag.parents().on('mousemove', function(e) {
				if(isInViewport($drag)) {
					var moveCoords = {
						top:  e.pageY + pos_y - drg_h,
						left: e.pageX + pos_x - drg_w
					};
					if(!allowX) {
						moveCoords.left = orig_x;
					} else if(!allowY) {
						moveCoords.top = orig_y;
					}
					$('.' + opt.cssclass).offset(moveCoords).on('mouseup', function() {
						$(this).removeClass(opt.cssclass);
					});
				}
			});
			e.preventDefault(); // disable selection
		}).on('mouseup', function() {
			if(!opt.handle) {
				$(this).removeClass(opt.cssclass);
			} else {
				$(this).removeClass(opt.csshandleclass);
				$target.removeClass(opt.cssclass);
			}
		});

	}

})(jQuery);

// #END

