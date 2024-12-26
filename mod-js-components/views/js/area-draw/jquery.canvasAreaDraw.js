
// jQuery Canvas Area Draw

// based on: https://github.com/fahrenheit-marketing/jquery-canvas-area-draw
// (c) 2013 Fahrenheit Marketing, http://fahrenheitmarketing.com/

// (c) 2017 unix-world.org
// refactored and modified by unixman
// v.170507

(function ($) {

$.fn.canvasAreaDraw = function(options) {

	var canvas = $(this);
	var exportData = '';
	var doReset = function(){};

	// public
	var initialize = function(options) {

		var settings;

		var ctx = canvas[0].getContext('2d');

		var points, activePoint;
		var draw, mousedown, stopdrag, move, moveall, resize, rightclick, record;
		var dotLineLength;
		var dragpoint;
		var startpoint = false;

		settings = $.extend({
			polyData: ''
		}, options);

		points = [];
		if(settings.polyData) {
			var pts = JSON.parse(settings.polyData);
			if(pts.length) {
				for(var i=0; i<pts.length; i++) {
					var pt = pts[i];
					if(pt.hasOwnProperty('x') && pt.hasOwnProperty('y')) {
						pt.x = parseInt(pt.x);
						if(isNaN(pt.x) || (pt.x < 0)) {
							pt.x = 0;
						} //end if
						if(pt.x > 16384) {
							pt.x = 16384; // max canvas supported by browsers by now ...
						} //end if
						pt.y = parseInt(pt.y);
						if(isNaN(pt.y) || (pt.y < 0)) {
							pt.y = 0;
						} //end if
						if(pt.y > 16384) {
							pt.y = 16384; // max canvas supported by browsers by now ...
						} //end if
						points.push(pt.x);
						points.push(pt.y);
					}
				}
			}
		}

		doReset = function() {
			points = [];
			draw();
		};

		move = function (e) {
			if (!e.offsetX) {
				e.offsetX = (e.pageX - $(e.target).offset().left);
				e.offsetY = (e.pageY - $(e.target).offset().top);
			}
			points[activePoint] = Math.round(e.offsetX);
			points[activePoint + 1] = Math.round(e.offsetY);
			draw();
		};

		moveall = function (e) {
			if (!e.offsetX) {
				e.offsetX = (e.pageX - $(e.target).offset().left);
				e.offsetY = (e.pageY - $(e.target).offset().top);
			}
			if (!startpoint) {
				startpoint = {x: Math.round(e.offsetX), y: Math.round(e.offsetY)};
			}
			var sdvpoint = {x: Math.round(e.offsetX), y: Math.round(e.offsetY)};
			for (var i = 0; i < points.length; i++) {
				points[i] = (sdvpoint.x - startpoint.x) + points[i];
				points[++i] = (sdvpoint.y - startpoint.y) + points[i];
			}
			startpoint = sdvpoint;
			draw();
		};

		stopdrag = function () {
			$(this).off('mousemove');
			record();
			activePoint = null;
		};

		rightclick = function (e) {
			e.preventDefault();
			if (!e.offsetX) {
				e.offsetX = (e.pageX - $(e.target).offset().left);
				e.offsetY = (e.pageY - $(e.target).offset().top);
			}
			var x = e.offsetX, y = e.offsetY;
			for (var i = 0; i < points.length; i += 2) {
				dis = Math.sqrt(Math.pow(x - points[i], 2) + Math.pow(y - points[i + 1], 2));
				if (dis < 6) {
					points.splice(i, 2);
					draw();
					record();
					return false;
				}
			}
			return false;
		};

		mousedown = function (e) {
			var x, y, dis, lineDis, insertAt = points.length;

			if (e.which === 3) {
				return false;
			}

			e.preventDefault();
			if (!e.offsetX) {
				e.offsetX = (e.pageX - $(e.target).offset().left);
				e.offsetY = (e.pageY - $(e.target).offset().top);
			}
			x = e.offsetX;
			y = e.offsetY;

			if (points.length >= 6) {
				var c = getCenter();
				ctx.fillRect(c.x - 4, c.y - 4, 8, 8);
				dis = Math.sqrt(Math.pow(x - c.x, 2) + Math.pow(y - c.y, 2));
				if (dis < 6) {
					startpoint = false;
					$(this).on('mousemove', moveall);
					return false;
				}
			}

			for (var i = 0; i < points.length; i += 2) {
				dis = Math.sqrt(Math.pow(x - points[i], 2) + Math.pow(y - points[i + 1], 2));
				if (dis < 6) {
					activePoint = i;
					$(this).on('mousemove', move);
					return false;
				}
			}

			for (var i = 0; i < points.length; i += 2) {
				if (i > 1) {
					lineDis = dotLineLength(
						x, y,
						points[i], points[i + 1],
						points[i - 2], points[i - 1],
						true
					);
					if (lineDis < 6) {
						insertAt = i;
					}
				}
			}

			points.splice(insertAt, 0, Math.round(x), Math.round(y));
			activePoint = insertAt;
			$(this).on('mousemove', move);

			draw();
			record();

			return false;
		};

		draw = function () {
			ctx.canvas.width = ctx.canvas.width;

			record();
			if (points.length < 2) {
				return;
			}
			ctx.globalCompositeOperation = 'destination-over';
			ctx.fillStyle = 'rgb(255,255,255)';
			ctx.strokeStyle = 'rgb(255,20,20)';
			ctx.lineWidth = 1;
			if (points.length >= 6) {
				var c = getCenter();
				ctx.fillRect(c.x - 4, c.y - 4, 8, 8);
			}
			ctx.beginPath();
			ctx.moveTo(points[0], points[1]);
			for (var i = 0; i < points.length; i += 2) {
				ctx.fillRect(points[i] - 2, points[i + 1] - 2, 4, 4);
				ctx.strokeRect(points[i] - 2, points[i + 1] - 2, 4, 4);
				if (points.length > 2 && i > 1) {
					ctx.lineTo(points[i], points[i + 1]);
				}
			}
			ctx.closePath();
			ctx.fillStyle = 'rgba(255,0,0,0.3)';
			ctx.fill();
			ctx.stroke();

		};

		record = function () {
			//exportData = points.join(',');
			exportData = '{}';
			if(points.length) {
				var pts = [];
				for(var i=0; i<points.length; i++) {
					pts.push({
						x: points[i],
						y: points[i+1]
					});
					i += 1;
				} //end for
				exportData = JSON.stringify(pts);
			} //end if
			//console.log(exportData);
		};

		getCenter = function () {
			var ptc = [];
			for (i = 0; i < points.length; i++) {
				ptc.push({x: points[i], y: points[++i]});
			}
			var first = ptc[0], last = ptc[ptc.length - 1];
			if (first.x != last.x || first.y != last.y) ptc.push(first);
			var twicearea = 0,
				x = 0, y = 0,
				nptc = ptc.length,
				p1, p2, f;
			for (var i = 0, j = nptc - 1; i < nptc; j = i++) {
				p1 = ptc[i];
				p2 = ptc[j];
				f = p1.x * p2.y - p2.x * p1.y;
				twicearea += f;
				x += ( p1.x + p2.x ) * f;
				y += ( p1.y + p2.y ) * f;
			}
			f = twicearea * 3;
			return {x: x / f, y: y / f};
		};

		dotLineLength = function (x, y, x0, y0, x1, y1, o) {
			function lineLength(x, y, x0, y0) {
				return Math.sqrt((x -= x0) * x + (y -= y0) * y);
			}
			if (o && !(o = function (x, y, x0, y0, x1, y1) {
					if (!(x1 - x0)) return {x: x0, y: y};
					else if (!(y1 - y0)) return {x: x, y: y0};
					var left, tg = -1 / ((y1 - y0) / (x1 - x0));
					return {
						x: left = (x1 * (x * tg - y + y0) + x0 * (x * -tg + y - y1)) / (tg * (x1 - x0) + y0 - y1),
						y: tg * left - tg * x + y
					};
				}(x, y, x0, y0, x1, y1), o.x >= Math.min(x0, x1) && o.x <= Math.max(x0, x1) && o.y >= Math.min(y0, y1) && o.y <= Math.max(y0, y1))) {
				var l1 = lineLength(x, y, x0, y0), l2 = lineLength(x, y, x1, y1);
				return l1 > l2 ? l2 : l1;
			} else {
				var a = y0 - y1, b = x1 - x0, c = x0 * y1 - y0 * x1;
				return Math.abs(a * x + b * y + c) / Math.sqrt(a * a + b * b);
			}
		};

		//--
		canvas.on('mousedown', mousedown);
		canvas.on('contextmenu', rightclick);
		canvas.on('mouseup', stopdrag);
		//--
		draw();
		//--

	};

	initialize(options);

	function getAreaData() {
		return exportData || '{}';
	};

	function resetData() {
		doReset();
	}

	return $.extend({}, this, {
		'getAreaData': getAreaData,
		'resetData': resetData
	});

};

})(jQuery);

// #END
