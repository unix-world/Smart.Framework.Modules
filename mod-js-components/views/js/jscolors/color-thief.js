
// https://github.com/lokesh/color-thief

/*
 * Color Thief v2.0
 * by Lokesh Dhakar - http://www.lokeshdhakar.com
 *
 * Thanks
 * ------
 * Nick Rabinowitz - For creating quantize.js.
 * John Schulz - For clean up and optimization. @JFSIII
 * Nathan Spady - For adding drag and drop support to the demo page.
 *
 * License
 * -------
 * Copyright 2011, 2015 Lokesh Dhakar
 * Released under the MIT license
 * https://raw.githubusercontent.com/lokesh/color-thief/master/LICENSE
 *
 */

// Bug Fixes by unixman:
//	* avoid return null[0] in getColor()

/*
  CanvasImage Class
  Class that wraps the html image element and canvas.
  It also simplifies some of the canvas context manipulation
  with a set of helper functions.
*/
var CanvasImage = function (image) {
	this.canvas  = document.createElement('canvas');
	this.context = this.canvas.getContext('2d');

	document.body.appendChild(this.canvas);

	this.width  = this.canvas.width  = image.width;
	this.height = this.canvas.height = image.height;

	this.context.drawImage(image, 0, 0, this.width, this.height);
};

CanvasImage.prototype.clear = function () {
	this.context.clearRect(0, 0, this.width, this.height);
};

CanvasImage.prototype.update = function (imageData) {
	this.context.putImageData(imageData, 0, 0);
};

CanvasImage.prototype.getPixelCount = function () {
	return this.width * this.height;
};

CanvasImage.prototype.getImageData = function () {
	return this.context.getImageData(0, 0, this.width, this.height);
};

CanvasImage.prototype.removeCanvas = function () {
	this.canvas.parentNode.removeChild(this.canvas);
};


var ColorThief = function () {};

/*
 * getColor(sourceImage[, quality])
 * returns {r: num, g: num, b: num}
 *
 * Use the median cut algorithm provided by quantize.js to cluster similar
 * colors and return the base color from the largest cluster.
 *
 * Quality is an optional argument. It needs to be an integer. 1 is the highest quality settings.
 * 10 is the default. There is a trade-off between quality and speed. The bigger the number, the
 * faster a color will be returned but the greater the likelihood that it will not be the visually
 * most dominant color.
 *
 * */
ColorThief.prototype.getColor = function(sourceImage, quality) {
	var palette       = this.getPalette(sourceImage, 5, quality);
	var dominantColor = null;
	if(palette) {
		dominantColor = palette[0]; // fix by unixman to avoid return null[0]
	}
	return dominantColor;
};


/*
 * getPalette(sourceImage[, colorCount, quality])
 * returns array[ {r: num, g: num, b: num}, {r: num, g: num, b: num}, ...]
 *
 * Use the median cut algorithm provided by quantize.js to cluster similar colors.
 *
 * colorCount determines the size of the palette; the number of colors returned. If not set, it
 * defaults to 10.
 *
 * BUGGY: Function does not always return the requested amount of colors. It can be +/- 2.
 *
 * quality is an optional argument. It needs to be an integer. 1 is the highest quality settings.
 * 10 is the default. There is a trade-off between quality and speed. The bigger the number, the
 * faster the palette generation but the greater the likelihood that colors will be missed.
 *
 *
 */
ColorThief.prototype.getPalette = function(sourceImage, colorCount, quality) {

	if (typeof colorCount === 'undefined' || colorCount < 2 || colorCount > 256) {
		colorCount = 10;
	}
	if (typeof quality === 'undefined' || quality < 1) {
		quality = 10;
	}

	// Create custom CanvasImage object
	var image      = new CanvasImage(sourceImage);
	var imageData  = image.getImageData();
	var pixels     = imageData.data;
	var pixelCount = image.getPixelCount();

	// Store the RGB values in an array format suitable for quantize function
	var pixelArray = [];
	for (var i = 0, offset, r, g, b, a; i < pixelCount; i = i + quality) {
		offset = i * 4;
		r = pixels[offset + 0];
		g = pixels[offset + 1];
		b = pixels[offset + 2];
		a = pixels[offset + 3];
		// If pixel is mostly opaque and not white
		if (a >= 125) {
			if (!(r > 250 && g > 250 && b > 250)) {
				pixelArray.push([r, g, b]);
			}
		}
	}

	// Send array to quantize function which clusters values
	// using median cut algorithm
	var cmap    = MMCQ.quantize(pixelArray, colorCount);
	var palette = cmap? cmap.palette() : null;

	// Clean up
	image.removeCanvas();

	return palette;
};

ColorThief.prototype.getColorFromUrl = function(imageUrl, callback, quality) {
	sourceImage = document.createElement("img");
	var thief = this;
	sourceImage.addEventListener('load' , function(){
		var palette = thief.getPalette(sourceImage, 5, quality);
		var dominantColor = palette[0];
		callback(dominantColor, imageUrl);
	});
	sourceImage.src = imageUrl
};


ColorThief.prototype.getImageData = function(imageUrl, callback) {
	xhr = new XMLHttpRequest();
	xhr.open('GET', imageUrl, true);
	xhr.responseType = 'arraybuffer'
	xhr.onload = function(e) {
		if (this.status == 200) {
			uInt8Array = new Uint8Array(this.response)
			i = uInt8Array.length
			binaryString = new Array(i);
			for (var i = 0; i < uInt8Array.length; i++){
				binaryString[i] = String.fromCharCode(uInt8Array[i])
			}
			data = binaryString.join('')
			base64 = window.btoa(data)
			callback ("data:image/png;base64,"+base64)
		}
	}
	xhr.send();
};

ColorThief.prototype.getColorAsync = function(imageUrl, callback, quality) {
	var thief = this;
	this.getImageData(imageUrl, function(imageData){
		sourceImage = document.createElement("img");
		sourceImage.addEventListener('load' , function(){
			var palette = thief.getPalette(sourceImage, 5, quality);
			var dominantColor = palette[0];
			callback(dominantColor, this);
		});
		sourceImage.src = imageData;
	});
};

// #END
