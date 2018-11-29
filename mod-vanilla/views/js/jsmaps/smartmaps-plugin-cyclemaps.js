
// Smart Maps / Open Maps :: OpenLayers Plugin: CycleMaps # v.2017.04.21
// (c) 2012-2017 unix-world.org
// License: Apache 2.0 License

OpenLayers.Layer.OSM.CycleMapLocalProxy = OpenLayers.Class(OpenLayers.Layer.OSM, {
	initialize: function(name, options) {
		var mode = '[D]';
		var url = [
			"http://a.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png",
			"http://b.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png",
			"http://c.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png"
		];
		options = OpenLayers.Util.extend({
			singleTile: false,
			numZoomLevels: 19,
			buffer: 0, // when loading extra surrounding tiles can be buffered
			attribution: '(c) opencyclemap.org'
		}, options);
		var newArguments = [name, url, options];
		OpenLayers.Layer.OSM.prototype.initialize.apply(this, newArguments);
	},
	CLASS_NAME: "OpenLayers.Layer.OSM.CycleMapLocalProxy"
});

OpenLayers.Layer.OSM.CycleMapTransportLocalProxy = OpenLayers.Class(OpenLayers.Layer.OSM, {
	initialize: function(name, options) {
		var mode = '[D]';
		var url = [
			"http://a.tile2.opencyclemap.org/transport/${z}/${x}/${y}.png",
			"http://b.tile2.opencyclemap.org/transport/${z}/${x}/${y}.png",
			"http://c.tile2.opencyclemap.org/transport/${z}/${x}/${y}.png"
		];
		options = OpenLayers.Util.extend({
			singleTile: false,
			numZoomLevels: 19,
			buffer: 0, // when loading extra surrounding tiles can be buffered
			attribution: '(c) opencyclemap.org'
		}, options);
		var newArguments = [name, url, options];
		OpenLayers.Layer.OSM.prototype.initialize.apply(this, newArguments);
	},
	CLASS_NAME: "OpenLayers.Layer.OSM.CycleMapTransportLocalProxy"
});

// #END
