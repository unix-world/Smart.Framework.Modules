
// (c) 2019-2020 unix-world.org
// License: GPLv3
// v.20200502

// https://gist.github.com/linchpinstudios/61ed36f5ff42088753ba1bf0ea42fffa

fabric.Arrow = fabric.util.createClass(fabric.Line, {

	type: 'Arrow',

	initialize: function(element, options) {
		options || (options = {});
		this.callSuper('initialize', element, options);
	},

	toObject: function() {
		return fabric.util.object.extend(this.callSuper('toObject'));
	},

	_render: function(ctx){
		this.callSuper('_render', ctx);

		// do not render if width/height are zeros or object is not visible
		if (this.width === 0 && this.height === 0 || !this.visible) return;

		ctx.save();

		var xDiff = this.x2 - this.x1;
		var yDiff = this.y2 - this.y1;
		var angle = Math.atan2(yDiff, xDiff);
		ctx.translate((this.x2 - this.x1) / 2, (this.y2 - this.y1) / 2);
		ctx.rotate(angle);
		ctx.beginPath();
		//move 10px in front of line to start the arrow so it does not have the square line end showing in front (0,0)
		ctx.moveTo(10,0);
		ctx.lineTo(-10, 10);
		ctx.lineTo(-10, -10);
		ctx.closePath();
		ctx.fillStyle = this.stroke;
		ctx.fill();

		ctx.restore();
	},

	clipTo: function(ctx) {
		this._render(ctx);
	}

});

fabric.Arrow.fromObject = function (object, callback) {
	callback && callback(new fabric.Arrow([object.x1, object.y1, object.x2, object.y2],object));
};

fabric.Arrow.async = true;

// #END
