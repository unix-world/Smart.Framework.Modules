
// FabricJs Editor
// (c) 2019 unix-world.org
// License: GPLv3
// v.20190207

//== TODO:
// Add Crop Image @ https://mattketmo.github.io/darkroomjs/
// llok at http://fabricjs.com/kitchensink ...
//==


var Smart_FabricJs_EditCanvas = function(elemId, dimW, dimH, bgColor, fxSelElem, fxUnselectEl) { // START CLASS

	// ->

	//--
	var canvas = null; // the drawing canvas
	//--
	var element = null; // active element
	var eltype  = null; // element type
	var fcolour = null; // fill color
	var scolour = null; // stroke color
	//--

	//--
	dimW = dimW ? dimW : 800;
	dimH = dimH ? dimH : 600;
	bgColor = bgColor ? bgColor : '#FFFFFF';
	//--
	fabric.enableGLFiltering = false; // v2 only
	fabric.Object.prototype.originX = fabric.Object.prototype.originY = 'center';
	fabric.Object.prototype.transparentCorners = false;
	//--
	canvas = new fabric.Canvas(String(elemId), { isDrawingMode: false });
//	canvas.setActiveObject(canvas.item(0));
	canvas.setDimensions({
		width: dimW,
		height: dimH
	});
	canvas.setBackgroundColor(bgColor);
	canvas.requestRenderAll();
	//--
	canvas.on('object:selected', function() {
		element = canvas.getActiveObject();
		if(element) {
			eltype = element.type.toLowerCase();
			fcolour = element.get('fill') || null;
			scolour = element.get('stroke') || null;
			//console.log(fcolour, scolour);
			if(typeof fxSelElem === 'function') {
				fxSelElem(canvas, element, fcolour, scolour);
			} //end if
		} //end if
	});
	canvas.on('selection:updated', function() {
		element = canvas.getActiveObject();
		if(element) {
			eltype = element.type.toLowerCase();
			fcolour = element.get('fill') || null;
			scolour = element.get('stroke') || null;
			//console.log(fcolour, scolour);
			if(typeof fxSelElem === 'function') {
				fxSelElem(canvas, element, fcolour, scolour);
			} //end if
		} //end if
	});
	canvas.on('selection:cleared', function() {
		element = null;
		fcolour = null;
		scolour = null;
	})
	//--
	canvas.on('object:scaling', function(e) { // keep stroke width on scaled objects
		var o = e.target;
		if(!o.strokeWidthUnscaled && o.strokeWidth) {
			o.strokeWidthUnscaled = o.strokeWidth;
		} //end if
		if(o.strokeWidthUnscaled) {
			if(o.scaleX > 0 && o.scaleY > 0) {
				o.strokeWidth = Math.round(Math.min(o.strokeWidthUnscaled / o.scaleX, o.strokeWidthUnscaled / o.scaleY));
			} else if(o.scaleX > 0) {
				o.strokeWidth = Math.round(o.strokeWidthUnscaled / o.scaleX);
			} else if(o.scaleY > 0) {
				o.strokeWidth = Math.round(o.strokeWidthUnscaled / o.scaleY);
			} //end if else
		} //end if
	});
	//--


	this.getCanvas = function() {
		//--
		return canvas;
		//--
	} //END FUNCTION


	this.getSelectedElement = function() {
		//--
		return element;
		//--
	} //END FUNCTION


	this.getSelectedColor = function() {
		//--
		return fcolour;
		//--
	} //END FUNCTION


	this.canvasToggleDrawingMode = function(mode) {
		//--
		if(mode === true) {
			canvas.isDrawingMode = true;
		} else {
			canvas.isDrawingMode = false;
		} //end if else
		//--
	} //END FUNCTION


	this.clearCanvas = function() {
		//--
		canvas.clear();
		//--
		return true;
		//--
	} //END FUNCTION


	this.removeSelected = function() {
		//--
		if(!element) {
			return false;
		} //end if
		//--
		canvas.remove(element);
		//--
		return true;
		//--
	} //END FUNCTION


	this.bringSelectedToFront = function() {
		//--
		if(!element) {
			return false;
		} //end if
		//--
		element.bringToFront();
		//--
		return true;
		//--
	} //END FUNCTION


	this.sendSelectedToBack = function() {
		//--
		if(!element) {
			return false;
		} //end if
		//--
		element.sendToBack();
		//--
		return true;
		//--
	} //END FUNCTION


	this.setColorOnSelected = function(kind, hexClr) {
		//--
		kind = kind ? String(kind) : '';
		kind = kind.toLowerCase();
		switch(kind) {
			case 'fill':
			case 'stroke':
				break;
			default:
				return false;
		} //end switch
		//--
		if(!hexClr) {
			return false;
		} //end if
		//--
		hexClr = getSafeHexColor(hexClr, '#000000');
		//--
		if(!element) {
			return false;
		} //end if
		//--
		var haveColor = false;
		switch(eltype) {
			case 'image':
			case 'group': // svg image
				haveColor = false;
				break;
			default:
				haveColor = true;
		} //end switch
		if(kind == 'stroke') {
			if(!element.strokeWidth) {
				haveColor = false; // do not apply stroke color on elements without strokeWidth
			} //end if
		} //end if
		//--
		if(!haveColor) {
			return false;
		} //end if
		//--
		element.set(String(kind), String(hexClr));
		canvas.requestRenderAll();
		//--
		return true;
		//--
	} //END FUNCTION

	/* better use only textbox
	this.addText = function(str, hexColor, fontFamily) {
		//--
		hexColor = hexColor ? String(hexColor) : '#111111';
		fontFamily = fontFamily ? String(fontFamily) : 'sans-serif';
		//--
		var text = new fabric.IText(String(str), {
			left: canvas.width / 2,
			top:  canvas.height / 2,
			fill: String(hexColor),
			fontFamily: String(fontFamily),
		//	hasRotatingPoint: false,
			centerTransform: true,
			originX: 'center',
			originY: 'center'
		});
		//--
		canvas.add(text);
		//--
		return true;
		//--
	} //END FUNCTION
	*/


	this.addArrow = function(coords, hexBgColor, hexStrokeColor, strokeWidth) {
		//--
		coords = coords ? coords : [ 250, 125, 250, 175 ];
		hexBgColor = getSafeHexColor(hexBgColor, '#333333');
		hexStrokeColor = getSafeHexColor(hexStrokeColor, '#111111');
		strokeWidth = getSafeDimension(strokeWidth, 3);
		//--
		var arrow = new fabric.Arrow(coords, {
			left: canvas.width / 2,
			top: canvas.height / 2,
			fill: String(hexBgColor),
			stroke: String(hexStrokeColor),
			strokeWidth: strokeWidth,
			originX: 'center',
			originY: 'center'
		});
		//--
		canvas.add(arrow);
		//--
		return true;
		//--
	} //END FUNCTION


	this.addTextBox = function(str, hexColor, fontFamily) {
		//--
		hexColor = hexColor ? String(hexColor) : '#111111';
		fontFamily = fontFamily ? String(fontFamily) : 'sans-serif';
		//--
		var textbox = new fabric.Textbox(String(str), {
			left: canvas.width / 2,
			top: canvas.height / 2,
			fill: String(hexColor),
			fontFamily: String(fontFamily),
		//	hasRotatingPoint: false,
			centerTransform: true,
			originX: 'center',
			originY: 'center'
		});
		textbox.set('width', textbox.text.length * textbox.fontSize / 2);
		//--
		canvas.add(textbox);
		//--
		return true;
		//--
	} //END FUNCTION


	this.addPolygon = function(type, hexBgColor, hexStrokeColor, strokeWidth) {
		//--
		type = type ? String(type) : '';
		hexBgColor = getSafeHexColor(hexBgColor, '#333333');
		hexStrokeColor = getSafeHexColor(hexStrokeColor, '#111111');
		strokeWidth = getSafeDimension(strokeWidth, 3);
		//--
		var polyshape = null;
		switch(type.toLowerCase()) {
			case 'trapezoid':
				polyshape = [
					{x:-100,y:-50},
					{x:100,y:-50},
					{x:150,y:50},
					{x:-150,y:50}
				];
				break;
			case 'emerald':
				polyshape = [
					{x:850,y:75},
					{x:958,y:137.5},
					{x:958,y:262.5},
					{x:850,y:325},
					{x:742,y:262.5},
					{x:742,y:137.5}
				 ];
				break;
			case 'star4':
				polyshape = [
					{x:0,y:0},
					{x:100,y:50},
					{x:200,y:0},
					{x:150,y:100},
					{x:200,y:200},
					{x:100,y:150},
					{x:0,y:200},
					{x:50,y:100},
					{x:0,y:0}
				];
				break;
			case 'star5':
				polyshape = [
					{x:350,y:75},
					{x:380,y:160},
					{x:470,y:160},
					{x:400,y:215},
					{x:423,y:301},
					{x:350,y:250},
					{x:277,y:301},
					{x:303,y:215},
					{x:231,y:161},
					{x:321,y:161}
				];
				break;
			default:
				console.error('addPolygon: Invalid Object Type: ' + type);
		} //end switch
		//--
		if(!polyshape) {
			return false;
		} //end if
		//--
		var polygon = new fabric.Polygon(polyshape, {
			left: canvas.width / 2,
			top: canvas.height / 2,
			fill: String(hexBgColor),
			stroke: String(hexStrokeColor),
			strokeWidth: strokeWidth,
			width: dimW,
			height: dimH,
			originX: 'center',
			originY: 'center'
		});
		//--
		canvas.add(polygon);
		//--
		return true;
		//--
	} //END FUNCTION


	this.addRectangle = function(dimW, dimH, hexBgColor, hexStrokeColor, strokeWidth) {
		//--
		dimW = getSafeDimension(dimW, 100);
		dimH = getSafeDimension(dimH, 100);
		hexBgColor = getSafeHexColor(hexBgColor, '#333333');
		hexStrokeColor = getSafeHexColor(hexStrokeColor, '#111111');
		strokeWidth = getSafeDimension(strokeWidth, 3);
		//console.log(dimW, dimH, hexBgColor, hexStrokeColor, strokeWidth);
		//--
		var rect = new fabric.Rect({
			left: canvas.width / 2,
			top: canvas.height / 2,
			fill: String(hexBgColor),
			stroke: String(hexStrokeColor),
			strokeWidth: strokeWidth,
			width: dimW,
			height: dimH,
			originX: 'center',
			originY: 'center'
		});
		//--
		canvas.add(rect);
		//--
		return true;
		//--
	} //END FUNCTION


	this.addTriangle = function(dimW, dimH, hexBgColor, hexStrokeColor, strokeWidth) {
		//--
		dimW = getSafeDimension(dimW, 125);
		dimH = getSafeDimension(dimH, 125);
		hexBgColor = getSafeHexColor(hexBgColor, '#333333');
		hexStrokeColor = getSafeHexColor(hexStrokeColor, '#111111');
		strokeWidth = getSafeDimension(strokeWidth, 3);
		//console.log(dimW, dimH, hexBgColor, hexStrokeColor, strokeWidth);
		//--
		var triangle = new fabric.Triangle({
			left: canvas.width / 2,
			top: canvas.height / 2,
			fill: String(hexBgColor),
			stroke: String(hexStrokeColor),
			strokeWidth: strokeWidth,
			width: dimW,
			height: dimH,
			originX: 'center',
			originY: 'center'
		});
		//--
		canvas.add(triangle);
		//--
		return true;
		//--
	} //END FUNCTION


	this.addCircle = function(radius, hexBgColor, hexStrokeColor, strokeWidth) {
		//--
		radius = getSafeDimension(radius, 75);
		hexBgColor = getSafeHexColor(hexBgColor, '#333333');
		hexStrokeColor = getSafeHexColor(hexStrokeColor, '#111111');
		strokeWidth = getSafeDimension(strokeWidth, 3);
		//console.log(radius, hexBgColor, hexStrokeColor, strokeWidth);
		//--
		var circle = new fabric.Circle({
			left: canvas.width / 2,
			top: canvas.height / 2,
			fill: String(hexBgColor),
			stroke: String(hexStrokeColor),
			strokeWidth: strokeWidth,
			radius: radius,
			originX: 'center',
			originY: 'center'
		});
		//--
		canvas.add(circle);
		//--
		return true;
		//--
	} //END FUNCTION


	this.addEllipse = function(radiusW, radiusH, hexBgColor, hexStrokeColor, strokeWidth) {
		//--
		radiusW = getSafeDimension(radiusW, 100);
		radiusH = getSafeDimension(radiusH, 50);
		hexBgColor = getSafeHexColor(hexBgColor, '#333333');
		hexStrokeColor = getSafeHexColor(hexStrokeColor, '#111111');
		strokeWidth = getSafeDimension(strokeWidth, 3);
		//console.log(radius, hexBgColor, hexStrokeColor, strokeWidth);
		//--
		var ellipse = new fabric.Ellipse({
			left: canvas.width / 2,
			top: canvas.height / 2,
			fill: String(hexBgColor),
			stroke: String(hexStrokeColor),
			strokeWidth: strokeWidth,
			rx: radiusW,
			ry: radiusH,
			originX: 'center',
			originY: 'center'
		});
		//--
		canvas.add(ellipse);
		//--
		return true;
		//--
	} //END FUNCTION


	this.addImage = function(urlOrData, dimW, dimH, isSvg) {
		//--
		if(!urlOrData) {
			return false;
		} //end if
		//--
		var opts = {
			left: canvas.width / 2,
			top: canvas.height / 2,
		//	scaleX: 1,
		//	scaleY: 1,
		//	angle: 0,
			originX: 'center',
			originY: 'center'
		};
		if(isSvg) {
			fabric.loadSVGFromURL(String(urlOrData), function(objects, options){
				var svg = fabric.util.groupSVGElements(objects, options);
				svg.set(opts);
				svg.scaleToWidth(dimW);
				svg.scaleToHeight(dimH);
				canvas.add(svg);
			});
		} else {
			fabric.Image.fromURL(String(urlOrData), function(image){
				image.set(opts);
				image.scaleToWidth(dimW);
				image.scaleToHeight(dimH);
				canvas.add(image);
			});
		} //end if else
		//--
		return true;
		//--
	} //END FUNCTION


	//#####


	var getSafeDimension = function(dim, initDim) {
		//--
		initDim = parseInt(initDim);
		if(isNaN(initDim) || (initDim < 1)) {
			initDim = 1;
		} //end if
		//--
		dim = parseInt(dim);
		if((isNaN(dim)) || (dim < 1)) {
			dim = initDim;
		} //end if
		//--
		return dim;
		//--
	} //END FUNCTION


	var getSafeHexColor = function(hexClr, initHexClr) {
		//--
		var regex = /^#[0-9A-F]{6}$/i;
		//--
		var isInitOk = false;
		if(initHexClr) {
			isInitOk = regex.test(String(initHexClr));
		} //end if
		if(!isInitOk) {
			initHexClr = '#000000';
		} //end if
		//--
		var isOk  = false;
		if(hexClr) {
			isOk = regex.test(String(hexClr));
		} //end if
		if(!isOk) {
			hexClr = String(initHexClr);
		} //end if
		//--
		return String(hexClr);
		//--
	} //END FUNCTION


} //END CLASS


// #END
