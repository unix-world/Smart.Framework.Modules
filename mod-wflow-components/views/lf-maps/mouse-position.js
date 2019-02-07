
// (c) 2017-2019 unix-world.org
// License: GPLv3
// v.20190207

// file: L.Control.MousePosition.js
// LeafletJS Plugin: Mouse Position
// https://github.com/ardhi/Leaflet.MousePosition
// modified by unixman:
// 		* added several default customizations
// 		* added zoom info

L.Control.MousePosition = L.Control.extend({

	options: {
		position: 'bottomleft',
		separator: ' ; ',
		emptyString: 'Info: n/a',
		lngFirst: false,
		numDigits: 7,
		lngFormatter: undefined,
		latFormatter: undefined,
		prefix: ""
	},

	onAdd: function(map){
		this._container = L.DomUtil.create('div', 'leaflet-control-mouseposition');
		L.DomEvent.disableClickPropagation(this._container);
		map.on('mousemove', this._onMouseMove, this);
		this._container.innerHTML=this.options.emptyString;
		return this._container;
	},

	onRemove: function(map){
		map.off('mousemove', this._onMouseMove)
	},

	_onMouseMove: function(e){
		var lng = this.options.lngFormatter ? this.options.lngFormatter(e.latlng.lng) : L.Util.formatNum(e.latlng.lng, this.options.numDigits);
		var lat = this.options.latFormatter ? this.options.latFormatter(e.latlng.lat) : L.Util.formatNum(e.latlng.lat, this.options.numDigits);
		lng = 'Lon: ' + lng;
		lat = 'Lat: ' + lat;
		var value = this.options.lngFirst ? lng + this.options.separator + lat : lat + this.options.separator + lng;
		var prefixAndValue = this.options.prefix + ' ' + value;
		this._container.innerHTML = 'Zoom: ' + this._map.getZoom() + ' ; ' + prefixAndValue;
	}

});


L.Map.mergeOptions({

	positionControl: false

});


L.Map.addInitHook(function(){

	if(this.options.positionControl) {
		this.positionControl = new L.Control.MousePosition();
		this.addControl(this.positionControl);
	}

});


L.control.mousePosition = function(options){

	return new L.Control.MousePosition(options);

};

// #END