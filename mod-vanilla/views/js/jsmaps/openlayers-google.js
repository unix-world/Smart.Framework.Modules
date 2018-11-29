
// [@[#[!JS-Compress!]#]@]
// Google for OpenLayers v.2.x
// v.2015.02.15 (2.14 dev + bugFixes by: unixman)

//======================================================================
//======================================================================
// OpenLayers/Layer/EventPane.js
//======================================================================
//======================================================================

/* Copyright (c) 2006-2013 by OpenLayers Contributors (see authors.txt for
 * full list of contributors). Published under the 2-clause BSD license.
 * See license.txt in the OpenLayers distribution or repository for the
 * full text of the license. */

/**
 * @requires OpenLayers/Layer.js
 * @requires OpenLayers/Util.js
 */

/**
 * Class: OpenLayers.Layer.EventPane
 * Base class for 3rd party layers, providing a DOM element which isolates
 * the 3rd-party layer from mouse events.
 * Only used by Google layers.
 *
 * Automatically instantiated by the Google constructor, and not usually instantiated directly.
 *
 * Create a new event pane layer with the
 * <OpenLayers.Layer.EventPane> constructor.
 *
 * Inherits from:
 *  - <OpenLayers.Layer>
 */
OpenLayers.Layer.EventPane = OpenLayers.Class(OpenLayers.Layer, {

	/**
	 * APIProperty: smoothDragPan
	 * {Boolean} smoothDragPan determines whether non-public/internal API
	 *     methods are used for better performance while dragging EventPane
	 *     layers. When not in sphericalMercator mode, the smoother dragging
	 *     doesn't actually move north/south directly with the number of
	 *     pixels moved, resulting in a slight offset when you drag your mouse
	 *     north south with this option on. If this visual disparity bothers
	 *     you, you should turn this option off, or use spherical mercator.
	 *     Default is on.
	 */
	smoothDragPan: true,

	/**
	 * Property: isBaseLayer
	 * {Boolean} EventPaned layers are always base layers, by necessity.
	 */
	isBaseLayer: true,

	/**
	 * APIProperty: isFixed
	 * {Boolean} EventPaned layers are fixed by default.
	 */
	isFixed: true,

	/**
	 * Property: pane
	 * {DOMElement} A reference to the element that controls the events.
	 */
	pane: null,


	/**
	 * Property: mapObject
	 * {Object} This is the object which will be used to load the 3rd party library
	 * in the case of the google layer, this will be of type GMap,
	 * in the case of the ve layer, this will be of type VEMap
	 */
	mapObject: null,


	/**
	 * Constructor: OpenLayers.Layer.EventPane
	 * Create a new event pane layer
	 *
	 * Parameters:
	 * name - {String}
	 * options - {Object} Hashtable of extra options to tag onto the layer
	 */
	initialize: function(name, options) {
		OpenLayers.Layer.prototype.initialize.apply(this, arguments);
		if (this.pane == null) {
			this.pane = OpenLayers.Util.createDiv(this.div.id + "_EventPane");
		}
	},

	/**
	 * APIMethod: destroy
	 * Deconstruct this layer.
	 */
	destroy: function() {
		this.mapObject = null;
		this.pane = null;
		OpenLayers.Layer.prototype.destroy.apply(this, arguments);
	},


	/**
	 * Method: setMap
	 * Set the map property for the layer. This is done through an accessor
	 * so that subclasses can override this and take special action once
	 * they have their map variable set.
	 *
	 * Parameters:
	 * map - {<OpenLayers.Map>}
	 */
	setMap: function(map) {
		OpenLayers.Layer.prototype.setMap.apply(this, arguments);

		this.pane.style.zIndex = parseInt(this.div.style.zIndex) + 1;
		this.pane.style.display = this.div.style.display;
		this.pane.style.width="100%";
		this.pane.style.height="100%";
		if (OpenLayers.BROWSER_NAME == "msie") {
			this.pane.style.background =
				"url(" + OpenLayers.Util.getImageLocation("blank.gif") + ")";
		}

		if (this.isFixed) {
			this.map.viewPortDiv.appendChild(this.pane);
		} else {
			this.map.layerContainerDiv.appendChild(this.pane);
		}

		// once our layer has been added to the map, we can load it
		this.loadMapObject();

		// if map didn't load, display warning
		if (this.mapObject == null) {
			this.loadWarningMessage();
		}

		this.map.events.register('zoomstart', this, this.onZoomStart);
	},

	/**
	 * APIMethod: removeMap
	 * On being removed from the map, we'll like to remove the invisible 'pane'
	 *     div that we added to it on creation.
	 *
	 * Parameters:
	 * map - {<OpenLayers.Map>}
	 */
	removeMap: function(map) {
		this.map.events.unregister('zoomstart', this, this.onZoomStart);

		if (this.pane && this.pane.parentNode) {
			this.pane.parentNode.removeChild(this.pane);
		}
		OpenLayers.Layer.prototype.removeMap.apply(this, arguments);
	},

	/**
	 * Method: onZoomStart
	 *
	 * Parameters:
	 * evt - zoomstart event object with center and zoom properties.
	 */
	onZoomStart: function(evt) {
		if (this.mapObject != null) {
			var center = this.getMapObjectLonLatFromOLLonLat(evt.center);
			var zoom = this.getMapObjectZoomFromOLZoom(evt.zoom);
			this.setMapObjectCenter(center, zoom, false);
		}
	},

	/**
	 * Method: loadWarningMessage
	 * If we can't load the map lib, then display an error message to the
	 *     user and tell them where to go for help.
	 *
	 *     This function sets up the layout for the warning message. Each 3rd
	 *     party layer must implement its own getWarningHTML() function to
	 *     provide the actual warning message.
	 */
	loadWarningMessage:function() {

		this.div.style.backgroundColor = "darkblue";

		var viewSize = this.map.getSize();

		var msgW = Math.min(viewSize.w, 300);
		var msgH = Math.min(viewSize.h, 200);
		var size = new OpenLayers.Size(msgW, msgH);

		var centerPx = new OpenLayers.Pixel(viewSize.w/2, viewSize.h/2);

		var topLeft = centerPx.add(-size.w/2, -size.h/2);

		var div = OpenLayers.Util.createDiv(this.name + "_warning",
											topLeft,
											size,
											null,
											null,
											null,
											"auto");

		div.style.padding = "7px";
		div.style.backgroundColor = "yellow";

		div.innerHTML = this.getWarningHTML();
		this.div.appendChild(div);
	},

	/**
	 * Method: getWarningHTML
	 * To be implemented by subclasses.
	 *
	 * Returns:
	 * {String} String with information on why layer is broken, how to get
	 *          it working.
	 */
	getWarningHTML:function() {
		//should be implemented by subclasses
		return "";
	},

	/**
	 * Method: display
	 * Set the display on the pane
	 *
	 * Parameters:
	 * display - {Boolean}
	 */
	display: function(display) {
		OpenLayers.Layer.prototype.display.apply(this, arguments);
		this.pane.style.display = this.div.style.display;
	},

	/**
	 * Method: setZIndex
	 * Set the z-index order for the pane.
	 *
	 * Parameters:
	 * zIndex - {int}
	 */
	setZIndex: function (zIndex) {
		OpenLayers.Layer.prototype.setZIndex.apply(this, arguments);
		this.pane.style.zIndex = parseInt(this.div.style.zIndex) + 1;
	},

	/**
	 * Method: moveByPx
	 * Move the layer based on pixel vector. To be implemented by subclasses.
	 *
	 * Parameters:
	 * dx - {Number} The x coord of the displacement vector.
	 * dy - {Number} The y coord of the displacement vector.
	 */
	moveByPx: function(dx, dy) {
		OpenLayers.Layer.prototype.moveByPx.apply(this, arguments);

		if (this.dragPanMapObject) {
			this.dragPanMapObject(dx, -dy);
		} else {
			this.moveTo(this.map.getCachedCenter());
		}
	},

	/**
	 * Method: moveTo
	 * Handle calls to move the layer.
	 *
	 * Parameters:
	 * bounds - {<OpenLayers.Bounds>}
	 * zoomChanged - {Boolean}
	 * dragging - {Boolean}
	 */
	moveTo:function(bounds, zoomChanged, dragging) {
		OpenLayers.Layer.prototype.moveTo.apply(this, arguments);

		if (this.mapObject != null) {

			var newCenter = this.map.getCenter();
			var newZoom = this.map.getZoom();

			if (newCenter != null) {

				var moOldCenter = this.getMapObjectCenter();
				var oldCenter = this.getOLLonLatFromMapObjectLonLat(moOldCenter);

				var moOldZoom = this.getMapObjectZoom();
				var oldZoom= this.getOLZoomFromMapObjectZoom(moOldZoom);

				if (!(newCenter.equals(oldCenter)) || newZoom != oldZoom) {

					if (!zoomChanged && oldCenter && this.dragPanMapObject &&
						this.smoothDragPan) {
						var oldPx = this.map.getViewPortPxFromLonLat(oldCenter);
						var newPx = this.map.getViewPortPxFromLonLat(newCenter);
						this.dragPanMapObject(newPx.x-oldPx.x, oldPx.y-newPx.y);
					} else {
						var center = this.getMapObjectLonLatFromOLLonLat(newCenter);
						var zoom = this.getMapObjectZoomFromOLZoom(newZoom);
						this.setMapObjectCenter(center, zoom, dragging);
					}
				}
			}
		}
	},


  /********************************************************/
  /*                                                      */
  /*                 Baselayer Functions                  */
  /*                                                      */
  /********************************************************/

	/**
	 * Method: getLonLatFromViewPortPx
	 * Get a map location from a pixel location
	 *
	 * Parameters:
	 * viewPortPx - {<OpenLayers.Pixel>}
	 *
	 * Returns:
	 *  {<OpenLayers.LonLat>} An OpenLayers.LonLat which is the passed-in view
	 *  port OpenLayers.Pixel, translated into lon/lat by map lib
	 *  If the map lib is not loaded or not centered, returns null
	 */
	getLonLatFromViewPortPx: function (viewPortPx) {
		var lonlat = null;
		if ( (this.mapObject != null) &&
			 (this.getMapObjectCenter() != null) ) {
			var moPixel = this.getMapObjectPixelFromOLPixel(viewPortPx);
			var moLonLat = this.getMapObjectLonLatFromMapObjectPixel(moPixel);
			lonlat = this.getOLLonLatFromMapObjectLonLat(moLonLat);
		}
		return lonlat;
	},


	/**
	 * Method: getViewPortPxFromLonLat
	 * Get a pixel location from a map location
	 *
	 * Parameters:
	 * lonlat - {<OpenLayers.LonLat>}
	 *
	 * Returns:
	 * {<OpenLayers.Pixel>} An OpenLayers.Pixel which is the passed-in
	 * OpenLayers.LonLat, translated into view port pixels by map lib
	 * If map lib is not loaded or not centered, returns null
	 */
	getViewPortPxFromLonLat: function (lonlat) {
		var viewPortPx = null;
		if ( (this.mapObject != null) &&
			 (this.getMapObjectCenter() != null) ) {

			var moLonLat = this.getMapObjectLonLatFromOLLonLat(lonlat);
			var moPixel = this.getMapObjectPixelFromMapObjectLonLat(moLonLat);

			viewPortPx = this.getOLPixelFromMapObjectPixel(moPixel);
		}
		return viewPortPx;
	},

  /********************************************************/
  /*                                                      */
  /*               Translation Functions                  */
  /*                                                      */
  /*   The following functions translate Map Object and   */
  /*            OL formats for Pixel, LonLat              */
  /*                                                      */
  /********************************************************/

  //
  // TRANSLATION: MapObject LatLng <-> OpenLayers.LonLat
  //

	/**
	 * Method: getOLLonLatFromMapObjectLonLat
	 * Get an OL style map location from a 3rd party style map location
	 *
	 * Parameters
	 * moLonLat - {Object}
	 *
	 * Returns:
	 * {<OpenLayers.LonLat>} An OpenLayers.LonLat, translated from the passed in
	 *          MapObject LonLat
	 *          Returns null if null value is passed in
	 */
	getOLLonLatFromMapObjectLonLat: function(moLonLat) {
		var olLonLat = null;
		if (moLonLat != null) {
			var lon = this.getLongitudeFromMapObjectLonLat(moLonLat);
			var lat = this.getLatitudeFromMapObjectLonLat(moLonLat);
			olLonLat = new OpenLayers.LonLat(lon, lat);
		}
		return olLonLat;
	},

	/**
	 * Method: getMapObjectLonLatFromOLLonLat
	 * Get a 3rd party map location from an OL map location.
	 *
	 * Parameters:
	 * olLonLat - {<OpenLayers.LonLat>}
	 *
	 * Returns:
	 * {Object} A MapObject LonLat, translated from the passed in
	 *          OpenLayers.LonLat
	 *          Returns null if null value is passed in
	 */
	getMapObjectLonLatFromOLLonLat: function(olLonLat) {
		var moLatLng = null;
		if (olLonLat != null) {
			moLatLng = this.getMapObjectLonLatFromLonLat(olLonLat.lon,
														 olLonLat.lat);
		}
		return moLatLng;
	},


  //
  // TRANSLATION: MapObject Pixel <-> OpenLayers.Pixel
  //

	/**
	 * Method: getOLPixelFromMapObjectPixel
	 * Get an OL pixel location from a 3rd party pixel location.
	 *
	 * Parameters:
	 * moPixel - {Object}
	 *
	 * Returns:
	 * {<OpenLayers.Pixel>} An OpenLayers.Pixel, translated from the passed in
	 *          MapObject Pixel
	 *          Returns null if null value is passed in
	 */
	getOLPixelFromMapObjectPixel: function(moPixel) {
		var olPixel = null;
		if (moPixel != null) {
			var x = this.getXFromMapObjectPixel(moPixel);
			var y = this.getYFromMapObjectPixel(moPixel);
			olPixel = new OpenLayers.Pixel(x, y);
		}
		return olPixel;
	},

	/**
	 * Method: getMapObjectPixelFromOLPixel
	 * Get a 3rd party pixel location from an OL pixel location
	 *
	 * Parameters:
	 * olPixel - {<OpenLayers.Pixel>}
	 *
	 * Returns:
	 * {Object} A MapObject Pixel, translated from the passed in
	 *          OpenLayers.Pixel
	 *          Returns null if null value is passed in
	 */
	getMapObjectPixelFromOLPixel: function(olPixel) {
		var moPixel = null;
		if (olPixel != null) {
			moPixel = this.getMapObjectPixelFromXY(olPixel.x, olPixel.y);
		}
		return moPixel;
	},

	CLASS_NAME: "OpenLayers.Layer.EventPane"
});


//======================================================================
//======================================================================
// OpenLayers/Layer/Google.js
//======================================================================
//======================================================================


/* Copyright (c) 2006-2013 by OpenLayers Contributors (see authors.txt for
 * full list of contributors). Published under the 2-clause BSD license.
 * See license.txt in the OpenLayers distribution or repository for the
 * full text of the license. */

/**
 * @requires OpenLayers/Layer/SphericalMercator.js
 * @requires OpenLayers/Layer/EventPane.js
 * @requires OpenLayers/Layer/FixedZoomLevels.js
 * @requires OpenLayers/Lang.js
 */

/**
 * Class: OpenLayers.Layer.Google
 *
 * Provides a wrapper for Google's Maps API
 * Normally the Terms of Use for this API do not allow wrapping, but Google
 * have provided written consent to OpenLayers for this - see email in
 * http://osgeo-org.1560.n6.nabble.com/Google-Maps-API-Terms-of-Use-changes-tp4910013p4911981.html
 *
 * Inherits from:
 *  - <OpenLayers.Layer.SphericalMercator>
 *  - <OpenLayers.Layer.EventPane>
 *  - <OpenLayers.Layer.FixedZoomLevels>
 */
OpenLayers.Layer.Google = OpenLayers.Class(
	OpenLayers.Layer.EventPane,
	OpenLayers.Layer.FixedZoomLevels, {

	/**
	 * Constant: MIN_ZOOM_LEVEL
	 * {Integer} 0
	 */
	MIN_ZOOM_LEVEL: 0,

	/**
	 * Constant: MAX_ZOOM_LEVEL
	 * {Integer} 21
	 */
	MAX_ZOOM_LEVEL: 21,

	/**
	 * Constant: RESOLUTIONS
	 * {Array(Float)} Hardcode these resolutions so that they are more closely
	 *                tied with the standard wms projection
	 */
	RESOLUTIONS: [
		1.40625,
		0.703125,
		0.3515625,
		0.17578125,
		0.087890625,
		0.0439453125,
		0.02197265625,
		0.010986328125,
		0.0054931640625,
		0.00274658203125,
		0.001373291015625,
		0.0006866455078125,
		0.00034332275390625,
		0.000171661376953125,
		0.0000858306884765625,
		0.00004291534423828125,
		0.00002145767211914062,
		0.00001072883605957031,
		0.00000536441802978515,
		0.00000268220901489257,
		0.0000013411045074462891,
		0.00000067055225372314453
	],

	/**
	 * APIProperty: type
	 * {GMapType}
	 */
	type: null,

	/**
	 * APIProperty: wrapDateLine
	 * {Boolean} Allow user to pan forever east/west.  Default is true.
	 *     Setting this to false only restricts panning if
	 *     <sphericalMercator> is true.
	 */
	wrapDateLine: true,

	/**
	 * APIProperty: sphericalMercator
	 * {Boolean} Should the map act as a mercator-projected map? This will
	 *     cause all interactions with the map to be in the actual map
	 *     projection, which allows support for vector drawing, overlaying
	 *     other maps, etc.
	 */
	sphericalMercator: false,

	/**
	 * Property: version
	 * {Number} The version of the Google Maps API
	 */
	version: null,

	/**
	 * Constructor: OpenLayers.Layer.Google
	 *
	 * Parameters:
	 * name - {String} A name for the layer.
	 * options - {Object} An optional object whose properties will be set
	 *     on the layer.
	 */
	initialize: function(name, options) {
		options = options || {};
		options.version = "3";
		var mixin = OpenLayers.Layer.Google["v" +
			options.version.replace(/\./g, "_")];
		if (mixin) {
			OpenLayers.Util.applyDefaults(options, mixin);
		} else {
			throw "Unsupported Google Maps API version: " + options.version;
		}

		OpenLayers.Util.applyDefaults(options, mixin.DEFAULTS);
		if (options.maxExtent) {
			options.maxExtent = options.maxExtent.clone();
		}

		OpenLayers.Layer.EventPane.prototype.initialize.apply(this,
			[name, options]);
		OpenLayers.Layer.FixedZoomLevels.prototype.initialize.apply(this,
			[name, options]);

		if (this.sphericalMercator) {
			OpenLayers.Util.extend(this, OpenLayers.Layer.SphericalMercator);
			this.initMercatorParameters();
		}
	},

	/**
	 * Method: clone
	 * Create a clone of this layer
	 *
	 * Returns:
	 * {<OpenLayers.Layer.Google>} An exact clone of this layer
	 */
	clone: function() {
		/**
		 * This method isn't intended to be called by a subclass and it
		 * doesn't call the same method on the superclass.  We don't call
		 * the super's clone because we don't want properties that are set
		 * on this layer after initialize (i.e. this.mapObject etc.).
		 */
		return new OpenLayers.Layer.Google(
			this.name, this.getOptions()
		);
	},

	/**
	 * APIMethod: setVisibility
	 * Set the visibility flag for the layer and hide/show & redraw
	 *     accordingly. Fire event unless otherwise specified
	 *
	 * Note that visibility is no longer simply whether or not the layer's
	 *     style.display is set to "block". Now we store a 'visibility' state
	 *     property on the layer class, this allows us to remember whether or
	 *     not we *desire* for a layer to be visible. In the case where the
	 *     map's resolution is out of the layer's range, this desire may be
	 *     subverted.
	 *
	 * Parameters:
	 * visible - {Boolean} Display the layer (if in range)
	 */
	setVisibility: function(visible) {
		// sharing a map container, opacity has to be set per layer
		var opacity = this.opacity == null ? 1 : this.opacity;
		OpenLayers.Layer.EventPane.prototype.setVisibility.apply(this, arguments);
		this.setOpacity(opacity);
	},

	/**
	 * APIMethod: display
	 * Hide or show the Layer
	 *
	 * Parameters:
	 * visible - {Boolean}
	 */
	display: function(visible) {
		if (!this._dragging) {
			this.setGMapVisibility(visible);
		}
		OpenLayers.Layer.EventPane.prototype.display.apply(this, arguments);
	},

	/**
	 * Method: moveTo
	 *
	 * Parameters:
	 * bounds - {<OpenLayers.Bounds>}
	 * zoomChanged - {Boolean} Tells when zoom has changed, as layers have to
	 *     do some init work in that case.
	 * dragging - {Boolean}
	 */
	moveTo: function(bounds, zoomChanged, dragging) {
		this._dragging = dragging;
		OpenLayers.Layer.EventPane.prototype.moveTo.apply(this, arguments);
		delete this._dragging;
	},

	/**
	 * APIMethod: setOpacity
	 * Sets the opacity for the entire layer (all images)
	 *
	 * Parameters:
	 * opacity - {Float}
	 */
	setOpacity: function(opacity) {
		if (opacity !== this.opacity) {
			if (this.map != null) {
				this.map.events.triggerEvent("changelayer", {
					layer: this,
					property: "opacity"
				});
			}
			this.opacity = opacity;
		}
		// Though this layer's opacity may not change, we're sharing a container
		// and need to update the opacity for the entire container.
		if (this.getVisibility()) {
			var container = this.getMapContainer();
			OpenLayers.Util.modifyDOMElement(
				container, null, null, null, null, null, null, opacity
			);
		}
	},

	/**
	 * APIMethod: destroy
	 * Clean up this layer.
	 */
	destroy: function() {
		/**
		 * We have to override this method because the event pane destroy
		 * deletes the mapObject reference before removing this layer from
		 * the map.
		 */
		if (this.map) {
			this.setGMapVisibility(false);
			var cache = OpenLayers.Layer.Google.cache[this.map.id];
			if (cache && cache.count <= 1) {
				this.removeGMapElements();
			}
		}
		OpenLayers.Layer.EventPane.prototype.destroy.apply(this, arguments);
	},

	/**
	 * Method: removeGMapElements
	 * Remove all elements added to the dom.  This should only be called if
	 * this is the last of the Google layers for the given map.
	 */
	removeGMapElements: function() {
		var cache = OpenLayers.Layer.Google.cache[this.map.id];
		if (cache) {
			// remove shared elements from dom
			var container = this.mapObject && this.getMapContainer();
			if (container && container.parentNode) {
				container.parentNode.removeChild(container);
			}
			var termsOfUse = cache.termsOfUse;
			if (termsOfUse && termsOfUse.parentNode) {
				termsOfUse.parentNode.removeChild(termsOfUse);
			}
			var poweredBy = cache.poweredBy;
			if (poweredBy && poweredBy.parentNode) {
				poweredBy.parentNode.removeChild(poweredBy);
			}
			if (this.mapObject && window.google && google.maps &&
					google.maps.event && google.maps.event.clearListeners) {
				google.maps.event.clearListeners(this.mapObject, 'tilesloaded');
			}
		}
	},

	/**
	 * APIMethod: removeMap
	 * On being removed from the map, also remove termsOfUse and poweredBy divs
	 *
	 * Parameters:
	 * map - {<OpenLayers.Map>}
	 */
	removeMap: function(map) {
		// hide layer before removing
		if (this.visibility && this.mapObject) {
			this.setGMapVisibility(false);
		}
		// check to see if last Google layer in this map
		var cache = OpenLayers.Layer.Google.cache[map.id];
		if (cache) {
			if (cache.count <= 1) {
				this.removeGMapElements();
				delete OpenLayers.Layer.Google.cache[map.id];
			} else {
				// decrement the layer count
				--cache.count;
			}
		}
		// remove references to gmap elements
		delete this.termsOfUse;
		delete this.poweredBy;
		delete this.mapObject;
		delete this.dragObject;
		OpenLayers.Layer.EventPane.prototype.removeMap.apply(this, arguments);
	},

  //
  // TRANSLATION: MapObject Bounds <-> OpenLayers.Bounds
  //

	/**
	 * APIMethod: getOLBoundsFromMapObjectBounds
	 *
	 * Parameters:
	 * moBounds - {Object}
	 *
	 * Returns:
	 * {<OpenLayers.Bounds>} An <OpenLayers.Bounds>, translated from the
	 *                       passed-in MapObject Bounds.
	 *                       Returns null if null value is passed in.
	 */
	getOLBoundsFromMapObjectBounds: function(moBounds) {
		var olBounds = null;
		if (moBounds != null) {
			var sw = moBounds.getSouthWest();
			var ne = moBounds.getNorthEast();
			if (this.sphericalMercator) {
				sw = this.forwardMercator(sw.lng(), sw.lat());
				ne = this.forwardMercator(ne.lng(), ne.lat());
			} else {
				sw = new OpenLayers.LonLat(sw.lng(), sw.lat());
				ne = new OpenLayers.LonLat(ne.lng(), ne.lat());
			}
			olBounds = new OpenLayers.Bounds(sw.lon,
											 sw.lat,
											 ne.lon,
											 ne.lat );
		}
		return olBounds;
	},

	/**
	 * APIMethod: getWarningHTML
	 *
	 * Returns:
	 * {String} String with information on why layer is broken, how to get
	 *          it working.
	 */
	getWarningHTML:function() {
		return OpenLayers.i18n("googleWarning");
	},


	/************************************
	 *                                  *
	 *   MapObject Interface Controls   *
	 *                                  *
	 ************************************/


  // Get&Set Center, Zoom

	/**
	 * APIMethod: getMapObjectCenter
	 *
	 * Returns:
	 * {Object} The mapObject's current center in Map Object format
	 */
	getMapObjectCenter: function() {
		return this.mapObject.getCenter();
	},

	/**
	 * APIMethod: getMapObjectZoom
	 *
	 * Returns:
	 * {Integer} The mapObject's current zoom, in Map Object format
	 */
	getMapObjectZoom: function() {
		return this.mapObject.getZoom();
	},


	/************************************
	 *                                  *
	 *       MapObject Primitives       *
	 *                                  *
	 ************************************/


  // LonLat

	/**
	 * APIMethod: getLongitudeFromMapObjectLonLat
	 *
	 * Parameters:
	 * moLonLat - {Object} MapObject LonLat format
	 *
	 * Returns:
	 * {Float} Longitude of the given MapObject LonLat
	 */
	getLongitudeFromMapObjectLonLat: function(moLonLat) {
		return this.sphericalMercator ?
		  this.forwardMercator(moLonLat.lng(), moLonLat.lat()).lon :
		  moLonLat.lng();
	},

	/**
	 * APIMethod: getLatitudeFromMapObjectLonLat
	 *
	 * Parameters:
	 * moLonLat - {Object} MapObject LonLat format
	 *
	 * Returns:
	 * {Float} Latitude of the given MapObject LonLat
	 */
	getLatitudeFromMapObjectLonLat: function(moLonLat) {
		var lat = this.sphericalMercator ?
		  this.forwardMercator(moLonLat.lng(), moLonLat.lat()).lat :
		  moLonLat.lat();
		return lat;
	},

  // Pixel

	/**
	 * APIMethod: getXFromMapObjectPixel
	 *
	 * Parameters:
	 * moPixel - {Object} MapObject Pixel format
	 *
	 * Returns:
	 * {Integer} X value of the MapObject Pixel
	 */
	getXFromMapObjectPixel: function(moPixel) {
		return moPixel.x;
	},

	/**
	 * APIMethod: getYFromMapObjectPixel
	 *
	 * Parameters:
	 * moPixel - {Object} MapObject Pixel format
	 *
	 * Returns:
	 * {Integer} Y value of the MapObject Pixel
	 */
	getYFromMapObjectPixel: function(moPixel) {
		return moPixel.y;
	},

	CLASS_NAME: "OpenLayers.Layer.Google"
});

/**
 * Property: OpenLayers.Layer.Google.cache
 * {Object} Cache for elements that should only be created once per map.
 */
OpenLayers.Layer.Google.cache = {};

//======================================================================
//======================================================================
// OpenLayers/Layer/Google/v3.js
//======================================================================
//======================================================================

/* Copyright (c) 2006-2013 by OpenLayers Contributors (see authors.txt for
 * full list of contributors). Published under the 2-clause BSD license.
 * See license.txt in the OpenLayers distribution or repository for the
 * full text of the license. */


/**
 * @requires OpenLayers/Layer/Google.js
 */

/**
 * Constant: OpenLayers.Layer.Google.v3
 *
 * Mixin providing functionality specific to the Google Maps API v3.
 *
 * To use this layer, you must include the GMaps v3 API in your html. To match
 * Google's zoom animation better with OpenLayers animated zooming, configure
 * your map with a zoomDuration of 10:
 *
 * (code)
 * new OpenLayers.Map('map', {zoomDuration: 10});
 * (end)
 *
 * Note that this layer configures the google.maps.map object with the
 * "disableDefaultUI" option set to true. Using UI controls that the Google
 * Maps API provides is not supported by the OpenLayers API.
 */
OpenLayers.Layer.Google.v3 = {

	/**
	 * Constant: DEFAULTS
	 * {Object} It is not recommended to change the properties set here. Note
	 * that Google.v3 layers only work when sphericalMercator is set to true.
	 *
	 * (code)
	 * {
	 *     sphericalMercator: true,
	 *     projection: "EPSG:900913"
	 * }
	 * (end)
	 */
	DEFAULTS: {
		sphericalMercator: true,
		projection: "EPSG:900913"
	},

	/**
	 * APIProperty: animationEnabled
	 * {Boolean} If set to true, the transition between zoom levels will be
	 *     animated (if supported by the GMaps API for the device used). Set to
	 *     false to match the zooming experience of other layer types. Default
	 *     is true. Note that the GMaps API does not give us control over zoom
	 *     animation, so if set to false, when zooming, this will make the
	 *     layer temporarily invisible, wait until GMaps reports the map being
	 *     idle, and make it visible again. The result will be a blank layer
	 *     for a few moments while zooming.
	 */
	animationEnabled: true,

	/**
	 * Method: loadMapObject
	 * Load the GMap and register appropriate event listeners.
	 */
	loadMapObject: function() {
		if (!this.type) {
			this.type = google.maps.MapTypeId.ROADMAP;
		}
		var mapObject;
		var cache = OpenLayers.Layer.Google.cache[this.map.id];
		if (cache) {
			// there are already Google layers added to this map
			mapObject = cache.mapObject;
			// increment the layer count
			++cache.count;
		} else {
			// this is the first Google layer for this map
			// create GMap
			var center = this.map.getCenter();
			var container = document.createElement('div');
			container.className = "olForeignContainer";
			container.style.width = '100%';
			container.style.height = '100%';
			mapObject = new google.maps.Map(container, {
				center: center ?
					new google.maps.LatLng(center.lat, center.lon) :
					new google.maps.LatLng(0, 0),
				zoom: this.map.getZoom() || 0,
				mapTypeId: this.type,
				disableDefaultUI: true,
				keyboardShortcuts: false,
				draggable: false,
				disableDoubleClickZoom: true,
				scrollwheel: false,
				streetViewControl: false
			});
			var googleControl = document.createElement('div');
			googleControl.style.width = '100%';
			googleControl.style.height = '100%';
			mapObject.controls[google.maps.ControlPosition.TOP_LEFT].push(googleControl);

			// cache elements for use by any other google layers added to
			// this same map
			cache = {
				googleControl: googleControl,
				mapObject: mapObject,
				count: 1
			};
			OpenLayers.Layer.Google.cache[this.map.id] = cache;
		}
		this.mapObject = mapObject;
		this.setGMapVisibility(this.visibility);
	},

	/**
	 * APIMethod: onMapResize
	 */
	onMapResize: function() {
		if (this.visibility) {
			google.maps.event.trigger(this.mapObject, "resize");
		}
	},

	/**
	 * Method: setGMapVisibility
	 * Display the GMap container and associated elements.
	 *
	 * Parameters:
	 * visible - {Boolean} Display the GMap elements.
	 */
	setGMapVisibility: function(visible) {
		var cache = OpenLayers.Layer.Google.cache[this.map.id];
		var map = this.map;
		if (cache) {
			var type = this.type;
			var layers = map.layers;
			var layer;
			for (var i=layers.length-1; i>=0; --i) {
				layer = layers[i];
				if (layer instanceof OpenLayers.Layer.Google &&
							layer.visibility === true && layer.inRange === true) {
					type = layer.type;
					visible = true;
					break;
				}
			}
			var container = this.mapObject.getDiv();
			if (visible === true) {
				if (container.parentNode !== map.div) {
					if (!cache.rendered) {
						var me = this;
						google.maps.event.addListenerOnce(this.mapObject, 'tilesloaded', function() {
							cache.rendered = true;
							me.setGMapVisibility(me.getVisibility());
							me.moveTo(me.map.getCenter());
						});
					//} else { // bugFix by unixman (https://github.com/openlayers/openlayers/issues/1450)
						map.div.appendChild(container);
						cache.googleControl.appendChild(map.viewPortDiv);
						google.maps.event.trigger(this.mapObject, 'resize');
					}
				}
				this.mapObject.setMapTypeId(type);
			} else if (cache.googleControl.hasChildNodes()) {
				map.div.appendChild(map.viewPortDiv);
				map.div.removeChild(container);
			}
		}
	},

	/**
	 * Method: getMapContainer
	 *
	 * Returns:
	 * {DOMElement} the GMap container's div
	 */
	getMapContainer: function() {
		return this.mapObject.getDiv();
	},

  //
  // TRANSLATION: MapObject Bounds <-> OpenLayers.Bounds
  //

	/**
	 * APIMethod: getMapObjectBoundsFromOLBounds
	 *
	 * Parameters:
	 * olBounds - {<OpenLayers.Bounds>}
	 *
	 * Returns:
	 * {Object} A MapObject Bounds, translated from olBounds
	 *          Returns null if null value is passed in
	 */
	getMapObjectBoundsFromOLBounds: function(olBounds) {
		var moBounds = null;
		if (olBounds != null) {
			var sw = this.sphericalMercator ?
			  this.inverseMercator(olBounds.bottom, olBounds.left) :
			  new OpenLayers.LonLat(olBounds.bottom, olBounds.left);
			var ne = this.sphericalMercator ?
			  this.inverseMercator(olBounds.top, olBounds.right) :
			  new OpenLayers.LonLat(olBounds.top, olBounds.right);
			moBounds = new google.maps.LatLngBounds(
				new google.maps.LatLng(sw.lat, sw.lon),
				new google.maps.LatLng(ne.lat, ne.lon)
			);
		}
		return moBounds;
	},


	/************************************
	 *                                  *
	 *   MapObject Interface Controls   *
	 *                                  *
	 ************************************/


  // LonLat - Pixel Translation

	/**
	 * APIMethod: getMapObjectLonLatFromMapObjectPixel
	 *
	 * Parameters:
	 * moPixel - {Object} MapObject Pixel format
	 *
	 * Returns:
	 * {Object} MapObject LonLat translated from MapObject Pixel
	 */
	getMapObjectLonLatFromMapObjectPixel: function(moPixel) {
		var size = this.map.getSize();
		var lon = this.getLongitudeFromMapObjectLonLat(this.mapObject.center);
		var lat = this.getLatitudeFromMapObjectLonLat(this.mapObject.center);
		var res = this.map.getResolution();

		var delta_x = moPixel.x - (size.w / 2);
		var delta_y = moPixel.y - (size.h / 2);

		var lonlat = new OpenLayers.LonLat(
			lon + delta_x * res,
			lat - delta_y * res
		);

		if (this.wrapDateLine) {
			lonlat = lonlat.wrapDateLine(this.maxExtent);
		}
		return this.getMapObjectLonLatFromLonLat(lonlat.lon, lonlat.lat);
	},

	/**
	 * APIMethod: getMapObjectPixelFromMapObjectLonLat
	 *
	 * Parameters:
	 * moLonLat - {Object} MapObject LonLat format
	 *
	 * Returns:
	 * {Object} MapObject Pixel transtlated from MapObject LonLat
	 */
	getMapObjectPixelFromMapObjectLonLat: function(moLonLat) {
		var lon = this.getLongitudeFromMapObjectLonLat(moLonLat);
		var lat = this.getLatitudeFromMapObjectLonLat(moLonLat);
		var res = this.map.getResolution();
		var extent = this.map.getExtent();
		return this.getMapObjectPixelFromXY((1/res * (lon - extent.left)),
											(1/res * (extent.top - lat)));
	},


	/**
	 * APIMethod: setMapObjectCenter
	 * Set the mapObject to the specified center and zoom
	 *
	 * Parameters:
	 * center - {Object} MapObject LonLat format
	 * zoom - {int} MapObject zoom format
	 */
	setMapObjectCenter: function(center, zoom) {
		if (this.animationEnabled === false && zoom != this.mapObject.zoom) {
			var mapContainer = this.getMapContainer();
			google.maps.event.addListenerOnce(
				this.mapObject,
				"idle",
				function() {
					mapContainer.style.visibility = "";
				}
			);
			mapContainer.style.visibility = "hidden";
		}
		this.mapObject.setOptions({
			center: center,
			zoom: zoom
		});
	},


  // Bounds

	/**
	 * APIMethod: getMapObjectZoomFromMapObjectBounds
	 *
	 * Parameters:
	 * moBounds - {Object} MapObject Bounds format
	 *
	 * Returns:
	 * {Object} MapObject Zoom for specified MapObject Bounds
	 */
	getMapObjectZoomFromMapObjectBounds: function(moBounds) {
		return this.mapObject.getBoundsZoomLevel(moBounds);
	},

	/************************************
	 *                                  *
	 *       MapObject Primitives       *
	 *                                  *
	 ************************************/


  // LonLat

	/**
	 * APIMethod: getMapObjectLonLatFromLonLat
	 *
	 * Parameters:
	 * lon - {Float}
	 * lat - {Float}
	 *
	 * Returns:
	 * {Object} MapObject LonLat built from lon and lat params
	 */
	getMapObjectLonLatFromLonLat: function(lon, lat) {
		var gLatLng;
		if(this.sphericalMercator) {
			var lonlat = this.inverseMercator(lon, lat);
			gLatLng = new google.maps.LatLng(lonlat.lat, lonlat.lon);
		} else {
			gLatLng = new google.maps.LatLng(lat, lon);
		}
		return gLatLng;
	},

  // Pixel

	/**
	 * APIMethod: getMapObjectPixelFromXY
	 *
	 * Parameters:
	 * x - {Integer}
	 * y - {Integer}
	 *
	 * Returns:
	 * {Object} MapObject Pixel from x and y parameters
	 */
	getMapObjectPixelFromXY: function(x, y) {
		return new google.maps.Point(x, y);
	}

};

// #END
