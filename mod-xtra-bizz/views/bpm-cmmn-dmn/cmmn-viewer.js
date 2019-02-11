
// Case Management Model and Notation (CMMN) - Viewer

/*
(c) 2019 unix-world.org
License: GPLv3
Version: 20190211
IMPORTANT: DO NOT MODIFY OR REMOVE THE ORIGINAL BPMN.IO CODE THAT DISPLAY THE BPMN.IO LOGO (see original bpmn.io LICENSE)
*/

/*
 * file: cmmn-viewer.js (viewer without pan and zoom)
 * version: v0.17.0
 * (c) 2014-2019, camunda Services GmbH @ see LICENSE
 * https://github.com/bpmn-io/cmmn-js # https://unpkg.com/cmmn-js@0.17.0/dist/
 */

(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.CmmnJS = f()}})(function(){var define,module,exports;return (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(_dereq_,module,exports){
/**
 * The code in the <project-logo></project-logo> area
 * must not be changed.
 *
 * @see http://bpmn.io/license for more information.
 */
'use strict';

var assign = _dereq_(59).assign,
	omit = _dereq_(59).omit,
	isNumber = _dereq_(59).isNumber;

var inherits = _dereq_(58);

var domify = _dereq_(60).domify,
	domQuery = _dereq_(60).query,
	domRemove = _dereq_(60).remove;

var innerSVG = _dereq_(79).innerSVG;

var Diagram = _dereq_(24).default,
	CmmnModdle = _dereq_(17).default;

var Importer = _dereq_(10);

function checkValidationError(err) {

	// check if we can help the user by indicating wrong CMMN 1.1 xml
	// (in case he or the exporting tool did not get that right)

	var pattern = /unparsable content <([^>]+)> detected([\s\S]*)$/;
	var match = pattern.exec(err.message);

	if (match) {
		err.message = 'unparsable content <' + match[1] + '> detected; ' + 'this may indicate an invalid CMMN 1.1 diagram file' + match[2];
	}

	return err;
}

var DEFAULT_OPTIONS = {
	width: '100%',
	height: '100%',
	position: 'relative'
};

/**
 * Ensure the passed argument is a proper unit (defaulting to px)
 */
function ensureUnit(val) {
	return val + (isNumber(val) ? 'px' : '');
}

/**
 * A viewer for CMMN 1.1 diagrams.
 *
 * Have a look at {@link NavigatedViewer} or {@link Modeler} for bundles that include
 * additional features.
 *
 *
 * ## Extending the Viewer
 *
 * In order to extend the viewer pass extension modules to bootstrap via the
 * `additionalModules` option. An extension module is an object that exposes
 * named services.
 *
 * The following example depicts the integration of a simple
 * logging component that integrates with interaction events:
 *
 *
 * ```javascript
 *
 * // logging component
 * function InteractionLogger(eventBus) {
 *   eventBus.on('element.hover', function(event) {
 *     console.log()
 *   })
 * }
 *
 * InteractionLogger.$inject = [ 'eventBus' ]; // minification save
 *
 * // extension module
 * var extensionModule = {
 *   __init__: [ 'interactionLogger' ],
 *   interactionLogger: [ 'type', InteractionLogger ]
 * };
 *
 * // extend the viewer
 * var cmmnViewer = new Viewer({ additionalModules: [ extensionModule ] });
 * cmmnViewer.importXML(...);
 * ```
 *
 * @param {Object} [options] configuration options to pass to the viewer
 * @param {DOMElement} [options.container] the container to attach to
 * @param {String|Number} [options.width] the width of the viewer
 * @param {String|Number} [options.height] the height of the viewer
 * @param {Object} [options.moddleExtensions] extension packages to provide
 * @param {Array<didi.Module>} [options.modules] a list of modules to override the default modules
 * @param {Array<didi.Module>} [options.additionalModules] a list of modules to use with the default modules
 */
function Viewer(options) {
	options = assign({}, DEFAULT_OPTIONS, options);
	this._moddle = this._createModdle(options);
	this._container = this._createContainer(options);
	addProjectLogo(this._container);
	this._init(this._container, this._moddle, options);
}

inherits(Viewer, Diagram);

module.exports = Viewer;

/**
 * Parse and render a CMMN 1.1 diagram.
 *
 * Once finished the viewer reports back the result to the
 * provided callback function with (err, warnings).
 *
 * ## Life-Cycle Events
 *
 * During import the viewer will fire life-cycle events:
 *
 *   * import.parse.start (about to read model from xml)
 *   * import.parse.complete (model read; may have worked or not)
 *   * import.render.start (graphical import start)
 *   * import.render.complete (graphical import finished)
 *   * import.done (everything done)
 *
 * You can use these events to hook into the life-cycle.
 *
 * @param {String} xml the CMMN 1.1 xml
 * @param {Function} [done] invoked with (err, warnings=[])
 */
Viewer.prototype.importXML = function (xml, done) {

	// done is optional
	done = done || function () {};

	var self = this;

	// hook in pre-parse listeners +
	// allow xml manipulation
	xml = this._emit('import.parse.start', { xml: xml }) || xml;

	this._moddle.fromXML(xml, 'cmmn:Definitions', function (err, definitions, context) {

		// hook in post parse listeners +
		// allow definitions manipulation
		definitions = self._emit('import.parse.complete', {
			error: err,
			definitions: definitions,
			context: context
		}) || definitions;

		var parseWarnings = context.warnings;

		if (err) {
			err = checkValidationError(err);

			self._emit('import.done', { error: err, warnings: parseWarnings });

			return done(err, parseWarnings);
		}

		self.importDefinitions(definitions, function (err, importWarnings) {
			var allWarnings = [].concat(parseWarnings, importWarnings || []);

			self._emit('import.done', { error: err, warnings: allWarnings });

			done(err, allWarnings);
		});
	});
};

/**
 * Export the currently displayed CMMN 1.1 diagram as
 * a CMMN 1.1 XML document.
 *
 * ## Life-Cycle Events
 *
 * During XML saving the viewer will fire life-cycle events:
 *
 *   * saveXML.start (before serialization)
 *   * saveXML.serialized (after xml generation)
 *   * saveXML.done (everything done)
 *
 * You can use these events to hook into the life-cycle.
 *
 * @param {Object} [options] export options
 * @param {Boolean} [options.format=false] output formated XML
 * @param {Boolean} [options.preamble=true] output preamble
 *
 * @param {Function} done invoked with (err, xml)
 */
Viewer.prototype.saveXML = function (options, done) {

	if (!done) {
		done = options;
		options = {};
	}

	var self = this;

	var definitions = this._definitions;

	if (!definitions) {
		return done(new Error('no definitions loaded'));
	}

	// allow to fiddle around with definitions
	definitions = this._emit('saveXML.start', {
		definitions: definitions
	}) || definitions;

	this._moddle.toXML(definitions, options, function (err, xml) {

		try {
			xml = self._emit('saveXML.serialized', {
				error: err,
				xml: xml
			}) || xml;

			self._emit('saveXML.done', {
				error: err,
				xml: xml
			});
		} catch (e) {
			console.error('error in saveXML life-cycle listener', e);
		}

		done(err, xml);
	});
};

Viewer.prototype.saveSVG = function (options, done) {

	if (!done) {
		done = options;
		options = {};
	}

	var canvas = this.get('canvas');

	var contentNode = canvas.getDefaultLayer(),
			defsNode = domQuery('defs', canvas._svg);

	var contents = innerSVG(contentNode),
			defs = defsNode && defsNode.outerHTML || '';

	var bbox = contentNode.getBBox();

	var svg = '<?xml version="1.0" encoding="utf-8"?>\n' + '<!-- created with cmmn-js / http://bpmn.io -->\n' + '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">\n' + '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ' + 'width="' + bbox.width + '" height="' + bbox.height + '" ' + 'viewBox="' + bbox.x + ' ' + bbox.y + ' ' + bbox.width + ' ' + bbox.height + '" version="1.1">' + defs + contents + '</svg>';

	done(null, svg);
};

Viewer.prototype.importDefinitions = function (definitions, done) {

	// use try/catch to not swallow synchronous exceptions
	// that may be raised during model parsing
	try {

		if (this._definitions) {
			// clear existing rendered diagram
			this.clear();
		}

		// update definitions
		this._definitions = definitions;

		// perform graphical import
		Importer.importCmmnDiagram(this, definitions, done);
	} catch (e) {

		// handle synchronous errors
		done(e);
	}
};

Viewer.prototype.attachTo = function (parentNode) {

	if (!parentNode) {
		throw new Error('parentNode required');
	}

	// ensure we detach from the
	// previous, old parent
	this.detach();

	// unwrap jQuery if provided
	if (parentNode.get && parentNode.constructor.prototype.jquery) {
		parentNode = parentNode.get(0);
	}

	if (typeof parentNode === 'string') {
		parentNode = domQuery(parentNode);
	}

	parentNode.appendChild(this._container);

	this._emit('attach', {});

	this.get('canvas').resized();
};

Viewer.prototype.getDefinitions = function () {
	return this._definitions;
};

Viewer.prototype.detach = function () {

	var container = this._container,
			parentNode = container.parentNode;

	if (!parentNode) {
		return;
	}

	this._emit('detach', {});

	parentNode.removeChild(container);
};

Viewer.prototype.getModules = function () {
	return this._modules;
};

/**
 * Destroy the viewer instance and remove all its
 * remainders from the document tree.
 */
Viewer.prototype.destroy = function () {

	// diagram destroy
	Diagram.prototype.destroy.call(this);

	// dom detach
	domRemove(this._container);
};

/**
 * Register an event listener
 *
 * Remove a previously added listener via {@link #off(event, callback)}.
 *
 * @param {String} event
 * @param {Number} [priority]
 * @param {Function} callback
 * @param {Object} [that]
 */
Viewer.prototype.on = function (event, priority, callback, target) {
	return this.get('eventBus').on(event, priority, callback, target);
};

/**
 * De-register an event listener
 *
 * @param {String} event
 * @param {Function} callback
 */
Viewer.prototype.off = function (event, callback) {
	this.get('eventBus').off(event, callback);
};

Viewer.prototype._init = function (container, moddle, options) {

	var baseModules = options.modules || this.getModules(),
			additionalModules = options.additionalModules || [],
			staticModules = [{
		cmmnjs: ['value', this],
		moddle: ['value', moddle]
	}];

	var diagramModules = [].concat(staticModules, baseModules, additionalModules);

	var diagramOptions = assign(omit(options, 'additionalModules'), {
		canvas: assign({}, options.canvas, { container: container }),
		modules: diagramModules
	});

	// invoke diagram constructor
	Diagram.call(this, diagramOptions);

	if (options && options.container) {
		this.attachTo(options.container);
	}
};

/**
 * Emit an event on the underlying {@link EventBus}
 *
 * @param  {String} type
 * @param  {Object} event
 *
 * @return {Object} event processing result (if any)
 */
Viewer.prototype._emit = function (type, event) {
	return this.get('eventBus').fire(type, event);
};

Viewer.prototype._createContainer = function (options) {

	var container = domify('<div class="cjs-container"></div>');

	assign(container.style, {
		width: ensureUnit(options.width),
		height: ensureUnit(options.height),
		position: options.position
	});

	return container;
};

Viewer.prototype._createModdle = function (options) {
	var moddleOptions = assign({}, this._moddleExtensions, options.moddleExtensions);

	return new CmmnModdle(moddleOptions);
};

// modules the viewer is composed of
Viewer.prototype._modules = [_dereq_(3), _dereq_(45).default, _dereq_(41).default];

/* <project-logo> */

var PoweredBy = _dereq_(16),
		domEvent = _dereq_(60).event;

/**
 * Adds the project logo to the diagram container as
 * required by the bpmn.io license.
 *
 * @see http://bpmn.io/license
 *
 * @param {Element} container
 */
function addProjectLogo(container) {
	var img = PoweredBy.BPMNIO_IMG;

	var linkMarkup =
		'<a href="#" ' +
			'class="cjs-powered-by" ' +
			'title="Powered by bpmn.io" ' +
			'style="position: absolute; bottom: 15px; right: 15px; z-index: 100;">' +
			img +
		'</a>';

	var linkElement = domify(linkMarkup);

	container.appendChild(linkElement);

	domEvent.bind(linkElement, 'click', function (event) {
		PoweredBy.open();
		event.preventDefault();
	});
}

/* </project-logo> */

},{"10":10,"16":16,"17":17,"24":24,"3":3,"41":41,"45":45,"58":58,"59":59,"60":60,"79":79}],2:[function(_dereq_,module,exports){
'use strict';

var ModelUtil = _dereq_(15),
		getDefinition = ModelUtil.getDefinition,
		getSentry = ModelUtil.getSentry;

var isAny = _dereq_(7).isAny;

var forEach = _dereq_(59).forEach,
		isArray = _dereq_(59).isArray;

/**
 * @class
 *
 * A registry that keeps track of all items in the model.
 */
function ItemRegistry(elementRegistry, eventBus) {
	this._items = {};
	this._referencedBy = {};

	this._elementRegistry = elementRegistry;
	this._eventBus = eventBus;

	this._init();
}

ItemRegistry.$inject = ['elementRegistry', 'eventBus'];

module.exports = ItemRegistry;

ItemRegistry.prototype._init = function (config) {

	var eventBus = this._eventBus;

	eventBus.on('diagram.destroy', 500, this._clear, this);
	eventBus.on('diagram.clear', 500, this._clear, this);
};

ItemRegistry.prototype._clear = function () {
	this._items = {};
	this._referencedBy = {};
};

/**
 * Register a given item.
 *
 * @param {ModdleElement} item
 */
ItemRegistry.prototype.add = function (item) {

	var items = this._items,
			id = item.id,
			definitions = this._referencedBy,
			definition = getReference(item),
			definitionId = definition && definition.id;

	items[id] = item;

	if (definition) {
		definitions[definitionId] = definitions[definitionId] || [];

		if (definitions[definitionId].indexOf(item) === -1) {
			definitions[definitionId].push(item);
		}
	}
};

/**
 * Removes an item from the registry.
 *
 * @param {ModdleElement} item
 */
ItemRegistry.prototype.remove = function (item) {

	var items = this._items,
			id = item.id,
			definitions = this._referencedBy,
			definition = getReference(item),
			definitionId = definition && definition.id;

	delete items[id];

	if (definition) {

		var referencingItems = definitions[definitionId] || [],
				idx = referencingItems.indexOf(item);

		if (idx !== -1) {
			referencingItems.splice(idx, 1);
		}

		if (!referencingItems.length) {
			delete definitions[definitionId];
		}
	}
};

/**
 * Update the registration with the new id.
 *
 * @param {ModdleElement} item
 * @param {String} newId
 */
ItemRegistry.prototype.updateId = function (element, newId) {

	var items, item;

	if (typeof element === 'string') {
		element = this.get(element);
	}

	if (isDefinition(element)) {
		items = this._referencedBy;
	} else {
		items = this._items;
	}

	if (element) {

		item = items[element.id];

		delete items[element.id];

		items[newId] = item;
	}
};

/**
 * Update the registration.
 *
 * @param {ModdleElement} item
 * @param {ModdleElement} newReference
 */
ItemRegistry.prototype.updateReference = function (item, newReference) {

	var definitions = this._referencedBy,
			oldDefinition = getReference(item),
			oldDefinitionId = oldDefinition && oldDefinition.id;

	if (oldDefinition) {

		var referencingItems = definitions[oldDefinitionId] || [],
				idx = referencingItems.indexOf(item);

		if (idx !== -1) {
			referencingItems.splice(idx, 1);
		}

		if (!referencingItems.length) {
			delete definitions[oldDefinitionId];
		}
	}

	if (newReference) {

		var newReferenceId = newReference.id;
		if (newReferenceId) {

			definitions[newReferenceId] = definitions[newReferenceId] || [];

			if (definitions[newReferenceId].indexOf(item) === -1) {
				definitions[newReferenceId].push(item);
			}
		}
	}
};

/**
 * Return the item for a given id.
 *
 * @param {String} id for selecting the item
 *
 * @return {ModdleElement}
 */
ItemRegistry.prototype.get = function (id) {
	return this._items[id];
};

/**
 * Return all items that match a given filter function.
 *
 * @param {Function} fn
 *
 * @return {Array<ModdleElement>}
 */
ItemRegistry.prototype.filter = function (fn) {

	var filtered = [];

	this.forEach(function (element, definition) {
		if (fn(element, definition)) {
			filtered.push(element);
		}
	});

	return filtered;
};

/**
 * Return all items.
 *
 * @return {Array<ModdleElement>}
 */
ItemRegistry.prototype.getAll = function () {
	return this.filter(function (e) {
		return e;
	});
};

/**
 * Iterate over all items.
 *
 * @param {Function} fn
 */
ItemRegistry.prototype.forEach = function (fn) {

	var items = this._items;

	forEach(items, function (item) {
		return fn(item, getReference(item));
	});
};

/**
 * Return for given definition all referenced items.
 *
 * @param {String|ModdleElement} filter
 */
ItemRegistry.prototype.getReferences = function (filter) {
	var id = filter.id || filter;
	return (this._referencedBy[id] || []).slice();
};

/**
 * Return for a given item id the shape element.
 *
 * @param {String|ModdleElement} filter
 */
ItemRegistry.prototype.getShape = function (filter) {
	var id = filter.id || filter;
	return this._elementRegistry && this._elementRegistry.get(id);
};

/**
 * Return for a given filter all shapes.
 *
 * @param {Array<String>|String|ModdleElement} filter
 */
ItemRegistry.prototype.getShapes = function (filter) {

	var shapes = [],
			self = this;

	function add(shape) {
		shape && shapes.push(shape);
	}

	if (isArray(filter)) {

		forEach(filter, function (f) {
			add(self.getShape(f));
		});
	} else if (isDefinition(filter)) {

		var referencedBy = self.getReferences(filter);
		forEach(referencedBy, function (reference) {
			add(self.getShape(reference));
		});
	} else {
		add(self.getShape(filter));
	}

	return shapes;
};

function getReference(item) {
	return getDefinition(item) || getSentry(item);
}

function isDefinition(item) {
	return isAny(item, ['cmmn:PlanItemDefinition', 'cmmn:Sentry', 'cmmn:CaseFileItemDefinition']);
}

},{"15":15,"59":59,"7":7}],3:[function(_dereq_,module,exports){
'use strict';

module.exports = {
	__depends__: [_dereq_(6), _dereq_(12)],
	itemRegistry: ['type', _dereq_(2)]
};

},{"12":12,"2":2,"6":6}],4:[function(_dereq_,module,exports){
'use strict';

var inherits = _dereq_(58),
		isArray = _dereq_(59).isArray,
		isObject = _dereq_(59).isObject,
		assign = _dereq_(59).assign;

var BaseRenderer = _dereq_(32).default,
		TextUtil = _dereq_(56).default,
		DiUtil = _dereq_(13),
		ModelUtil = _dereq_(15);

var isStandardEventVisible = DiUtil.isStandardEventVisible;
var isPlanningTableCollapsed = DiUtil.isPlanningTableCollapsed;
var isCollapsed = DiUtil.isCollapsed;

var isCasePlanModel = ModelUtil.isCasePlanModel;
var getBusinessObject = ModelUtil.getBusinessObject;
var getDefinition = ModelUtil.getDefinition;
var isRequired = ModelUtil.isRequired;
var isRepeatable = ModelUtil.isRepeatable;
var isManualActivation = ModelUtil.isManualActivation;
var isAutoComplete = ModelUtil.isAutoComplete;
var hasPlanningTable = ModelUtil.hasPlanningTable;
var getName = ModelUtil.getName;
var is = ModelUtil.is;
var getStandardEvent = ModelUtil.getStandardEvent;

var domQuery = _dereq_(60).query;

var svgAppend = _dereq_(79).append,
		svgAttr = _dereq_(79).attr,
		svgClasses = _dereq_(79).classes,
		svgCreate = _dereq_(79).create;

var translate = _dereq_(55).translate;

var createLine = _dereq_(54).createLine;

function CmmnRenderer(eventBus, styles, pathMap) {

	BaseRenderer.call(this, eventBus);

	var TASK_BORDER_RADIUS = 10;
	var MILESTONE_BORDER_RADIUS = 24;
	var STAGE_EDGE_OFFSET = 20;

	var LABEL_STYLE = {
		fontFamily: 'Arial, sans-serif',
		fontSize: '12px'
	};

	var textUtil = new TextUtil({
		style: LABEL_STYLE,
		size: { width: 100 }
	});

	var markers = {};

	function addMarker(id, element) {
		markers[id] = element;
	}

	function marker(id) {
		return markers[id];
	}

	function initMarkers(svg) {

		function createMarker(id, options) {
			var attrs = assign({
				fill: 'black',
				strokeWidth: 1,
				strokeLinecap: 'round',
				strokeDasharray: 'none'
			}, options.attrs);

			var ref = options.ref || { x: 0, y: 0 };

			var scale = options.scale || 1;

			// fix for safari / chrome / firefox bug not correctly
			// resetting stroke dash array
			if (attrs.strokeDasharray === 'none') {
				attrs.strokeDasharray = [10000, 1];
			}

			var marker = svgCreate('marker');

			svgAttr(options.element, attrs);

			svgAppend(marker, options.element);

			svgAttr(marker, {
				id: id,
				viewBox: '0 0 20 20',
				refX: ref.x,
				refY: ref.y,
				markerWidth: 20 * scale,
				markerHeight: 20 * scale,
				orient: 'auto'
			});

			var defs = domQuery('defs', svg);

			if (!defs) {
				defs = svgCreate('defs');

				svgAppend(svg, defs);
			}

			svgAppend(defs, marker);

			return addMarker(id, marker);
		}

		var associationStart = svgCreate('path');
		svgAttr(associationStart, { d: 'M 11 5 L 1 10 L 11 15' });

		createMarker('association-start', {
			element: associationStart,
			attrs: {
				fill: 'none',
				stroke: 'black',
				strokeWidth: 1.5
			},
			ref: { x: 1, y: 10 },
			scale: 0.5
		});

		var associationEnd = svgCreate('path');
		svgAttr(associationEnd, { d: 'M 1 5 L 11 10 L 1 15' });

		createMarker('association-end', {
			element: associationEnd,
			attrs: {
				fill: 'none',
				stroke: 'black',
				strokeWidth: 1.5
			},
			ref: { x: 12, y: 10 },
			scale: 0.5
		});
	}

	// draw shape //////////////////////////////////////////////////////////////

	function computeStyle(custom, traits, defaultStyles) {
		if (!isArray(traits)) {
			defaultStyles = traits;
			traits = [];
		}

		return styles.style(traits || [], assign(defaultStyles, custom || {}));
	}

	function drawCircle(parentGfx, width, height, offset, attrs) {

		if (isObject(offset)) {
			attrs = offset;
			offset = 0;
		}

		offset = offset || 0;

		attrs = computeStyle(attrs, {
			stroke: 'black',
			strokeWidth: 2,
			fill: 'white'
		});

		var cx = width / 2,
				cy = height / 2;

		var circle = svgCreate('circle');
		svgAttr(circle, {
			cx: cx,
			cy: cy,
			r: Math.round((width + height) / 4 - offset)
		});
		svgAttr(circle, attrs);

		svgAppend(parentGfx, circle);

		return circle;
	}

	function drawRect(parentGfx, width, height, r, offset, attrs) {

		if (isObject(offset)) {
			attrs = offset;
			offset = 0;
		}

		offset = offset || 0;

		attrs = computeStyle(attrs, {
			stroke: 'black',
			strokeWidth: 2,
			fill: 'white'
		});

		var rect = svgCreate('rect');
		svgAttr(rect, {
			x: offset,
			y: offset,
			width: width - offset * 2,
			height: height - offset * 2,
			rx: r,
			ry: r
		});
		svgAttr(rect, attrs);

		svgAppend(parentGfx, rect);

		return rect;
	}

	function drawDiamond(parentGfx, width, height, attrs) {

		var x_2 = width / 2;
		var y_2 = height / 2;

		var points = [{ x: x_2, y: 0 }, { x: width, y: y_2 }, { x: x_2, y: height }, { x: 0, y: y_2 }];

		var pointsString = points.map(function (point) {
			return point.x + ',' + point.y;
		}).join(' ');

		attrs = computeStyle(attrs, {
			stroke: 'black',
			strokeWidth: 2,
			fill: 'white'
		});

		var polygon = svgCreate('polygon');
		svgAttr(polygon, {
			points: pointsString
		});
		svgAttr(polygon, attrs);

		svgAppend(parentGfx, polygon);

		return polygon;
	}

	function drawPath(parentGfx, d, attrs) {

		attrs = computeStyle(attrs, ['no-fill'], {
			strokeWidth: 2,
			stroke: 'black'
		});

		var path = svgCreate('path');
		svgAttr(path, { d: d });
		svgAttr(path, attrs);

		svgAppend(parentGfx, path);

		return path;
	}

	function drawOctagon(parentGfx, width, height, offset, attrs) {
		offset = offset || 20;

		var x1 = offset;
		var y1 = height;

		var x2 = 0;
		var y2 = height - offset;

		var x3 = 0;
		var y3 = offset;

		var x4 = offset;
		var y4 = 0;

		var x5 = width - offset;
		var y5 = 0;

		var x6 = width;
		var y6 = offset;

		var x7 = width;
		var y7 = height - offset;

		var x8 = width - offset;
		var y8 = height;

		var points = [{ x: x1, y: y1 }, { x: x2, y: y2 }, { x: x3, y: y3 }, { x: x4, y: y4 }, { x: x5, y: y5 }, { x: x6, y: y6 }, { x: x7, y: y7 }, { x: x8, y: y8 }];

		attrs = attrs || {};
		attrs.fill = 'white';
		attrs.stroke = 'black';
		attrs.strokeWidth = 2;

		return drawPolygon(parentGfx, points, attrs);
	}

	function drawPolygon(parentGfx, points, attrs) {
		var pointsString = points.map(function (point) {
			return point.x + ',' + point.y;
		}).join(' ');

		var polygon = svgCreate('polygon');

		svgAttr(polygon, {
			points: pointsString
		});
		svgAttr(polygon, attrs);

		svgAppend(parentGfx, polygon);

		return polygon;
	}

	// draw connection ////////////////////////////////////////////

	function drawLine(parentGfx, waypoints, attrs) {
		attrs = computeStyle(attrs, ['no-fill'], {
			stroke: 'black',
			strokeWidth: 2,
			fill: 'none'
		});

		var line = createLine(waypoints, attrs);

		svgAppend(parentGfx, line);

		return line;
	}

	function createPathFromConnection(connection) {
		var waypoints = connection.waypoints;

		var pathData = 'm  ' + waypoints[0].x + ',' + waypoints[0].y;
		for (var i = 1; i < waypoints.length; i++) {
			pathData += 'L' + waypoints[i].x + ',' + waypoints[i].y + ' ';
		}
		return pathData;
	}

	// render label //////////////////////////////////////////////

	function renderLabel(parentGfx, label, options) {
		var text = textUtil.createText(label || '', options);
		svgClasses(text).add('djs-label');
		svgAppend(parentGfx, text);

		return text;
	}

	function renderEmbeddedLabel(p, element, align) {
		var name = getName(element);
		return renderLabel(p, name, {
			box: element,
			align: align,
			padding: 5
		});
	}

	function renderExpandedStageLabel(p, element, align) {
		var name = getName(element);
		var textbox = renderLabel(p, name, { box: element, align: align, padding: 5 });

		// reset the position of the text box
		translate(textbox, STAGE_EDGE_OFFSET, 0);

		return textbox;
	}

	function renderCasePlanModelLabel(p, element) {
		var bo = getBusinessObject(element);

		// default maximum textbox dimensions
		var height = 18;
		var width = element.width / 2 - 60;

		var label = bo.name;

		// create text box
		var textBox = renderLabel(p, label, {
			box: { height: height, width: width },
			align: 'left-top'
		});

		var minWidth = 60,
				padding = 40,
				textBoxWidth = textBox.getBBox().width;

		// set polygon width based on actual textbox size
		var polygonWidth = textBoxWidth + padding;

		if (textBoxWidth < minWidth) {
			polygonWidth = minWidth + padding;
		}

		var polygonPoints = [{ x: 10, y: 0 }, { x: 20, y: -height }, { x: polygonWidth, y: -height }, { x: polygonWidth + 10, y: 0 }];

		// The pointer-events attribute is needed to allow clicks on the polygon
		// which otherwise would be prevented by the parent node ('djs-visual').
		var polygon = drawPolygon(p, polygonPoints, {
			fill: 'white',
			stroke: 'black',
			strokeWidth: 2,
			fillOpacity: 0.95,
			'pointer-events': 'all'
		});

		// make sure the textbox is visually on top of the polygon
		textBox.parentNode.insertBefore(polygon, textBox);

		// reset the position of the text box
		translate(textBox, 25, -height + 5);

		return textBox;
	}

	function renderExternalLabel(parentGfx, element) {
		var name = getName(element),
				hide = false;

		var standardEvent = getStandardEvent(element);

		if (standardEvent) {

			var standardEventVisible = isStandardEventVisible(element);
			standardEvent = '[' + standardEvent + ']';

			if (!name) {
				name = standardEvent;
				element.hidden = hide = !standardEventVisible;
			} else {
				if (standardEventVisible) {
					name = name + ' ' + standardEvent;
				}
			}
		}

		var box = {
			width: 90,
			height: 30,
			x: element.width / 2 + element.x,
			y: element.height / 2 + element.y
		};

		element.hidden = element.labelTarget.hidden || hide || !name;

		return renderLabel(parentGfx, name, { box: box, style: { fontSize: '11px' } });
	}

	// render elements //////////////////////////////////////////

	function renderer(type) {
		return handlers[type];
	}

	var handlers = {
		'cmmn:PlanItem': function cmmnPlanItem(p, element) {
			var definition = getDefinition(element);
			return renderer(definition.$type)(p, element);
		},

		'cmmn:DiscretionaryItem': function cmmnDiscretionaryItem(p, element) {
			var definition = getDefinition(element);

			var attrs = {
				strokeDasharray: '10, 12'
			};

			if (is(definition, 'cmmn:Task')) {
				assign(attrs, {
					strokeDasharray: '12, 12.4',
					strokeDashoffset: 13.6
				});
			}

			return renderer(definition.$type)(p, element, attrs);
		},

		// STAGE
		'cmmn:Stage': function cmmnStage(p, element, attrs) {

			attrs = assign({ fillOpacity: 0.95 }, attrs);

			var rect;
			if (isCasePlanModel(element)) {
				return handlers['cmmn:CasePlanModel'](p, element);
			}

			rect = drawOctagon(p, element.width, element.height, STAGE_EDGE_OFFSET, attrs);

			if (!isCollapsed(element)) {
				renderExpandedStageLabel(p, element, 'left-top');
			} else {
				renderEmbeddedLabel(p, element, 'center-middle');
			}

			attachPlanningTableMarker(p, element);
			attachStageMarkers(p, element);
			return rect;
		},

		// STAGE
		'cmmn:PlanFragment': function cmmnPlanFragment(p, element, attrs) {

			var rect = drawRect(p, element.width, element.height, TASK_BORDER_RADIUS, {
				strokeDasharray: '10, 12',
				fillOpacity: 0.95
			});

			renderEmbeddedLabel(p, element, isCollapsed(element) ? 'center-middle' : 'left-top');

			attachStageMarkers(p, element);
			return rect;
		},

		'cmmn:CasePlanModel': function cmmnCasePlanModel(p, element) {
			var rect = drawRect(p, element.width, element.height, 0, {
				fillOpacity: 0.95
			});
			renderCasePlanModelLabel(p, element);
			attachPlanningTableMarker(p, element);
			attachCasePlanModelMarkers(p, element);
			return rect;
		},

		// MILESTONE
		'cmmn:Milestone': function cmmnMilestone(p, element, attrs) {
			var rect = drawRect(p, element.width, element.height, MILESTONE_BORDER_RADIUS, attrs);
			renderEmbeddedLabel(p, element, 'center-middle');
			attachTaskMarkers(p, element);
			return rect;
		},

		// EVENT LISTENER
		'cmmn:EventListener': function cmmnEventListener(p, element, attrs) {
			var outerCircle = drawCircle(p, element.width, element.height, attrs);

			attrs = attrs || {};
			attrs.strokeWidth = 2;

			drawCircle(p, element.width, element.height, 0.1 * element.height, attrs);
			return outerCircle;
		},

		'cmmn:TimerEventListener': function cmmnTimerEventListener(p, element, attrs) {
			var circle = renderer('cmmn:EventListener')(p, element, attrs);

			var pathData = pathMap.getScaledPath('EVENT_TIMER_WH', {
				xScaleFactor: 0.75,
				yScaleFactor: 0.75,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: 0.5,
					my: 0.5
				}
			});

			drawPath(p, pathData, {
				strokeWidth: 2,
				strokeLinecap: 'square'
			});

			for (var i = 0; i < 12; i++) {

				var linePathData = pathMap.getScaledPath('EVENT_TIMER_LINE', {
					xScaleFactor: 0.75,
					yScaleFactor: 0.75,
					containerWidth: element.width,
					containerHeight: element.height,
					position: {
						mx: 0.5,
						my: 0.5
					}
				});

				var width = element.width / 2;
				var height = element.height / 2;

				drawPath(p, linePathData, {
					strokeWidth: 1,
					strokeLinecap: 'square',
					transform: 'rotate(' + i * 30 + ',' + height + ',' + width + ')'
				});
			}

			return circle;
		},

		'cmmn:UserEventListener': function cmmnUserEventListener(p, element, attrs) {
			var circle = renderer('cmmn:EventListener')(p, element, attrs);

			// TODO: The user event decorator has to be
			// scaled correctly!
			var x = 20;
			var y = 15;

			var pathData = pathMap.getScaledPath('TASK_TYPE_USER_1', {
				abspos: {
					x: x,
					y: y
				}
			});

			/* user path */drawPath(p, pathData, {
				strokeWidth: 0.5,
				fill: 'none'
			});

			var pathData2 = pathMap.getScaledPath('TASK_TYPE_USER_2', {
				abspos: {
					x: x,
					y: y
				}
			});

			/* user2 path */drawPath(p, pathData2, {
				strokeWidth: 0.5,
				fill: 'none'
			});

			var pathData3 = pathMap.getScaledPath('TASK_TYPE_USER_3', {
				abspos: {
					x: x,
					y: y
				}
			});

			/* user3 path */drawPath(p, pathData3, {
				strokeWidth: 0.5,
				fill: 'black'
			});

			return circle;
		},

		// TASK
		'cmmn:Task': function cmmnTask(p, element, attrs) {
			var rect = drawRect(p, element.width, element.height, TASK_BORDER_RADIUS, attrs);
			renderEmbeddedLabel(p, element, 'center-middle');
			attachTaskMarkers(p, element);
			return rect;
		},

		'cmmn:HumanTask': function cmmnHumanTask(p, element, attrs) {
			var task = renderer('cmmn:Task')(p, element, attrs);

			var bo = element.businessObject;
			var definition = bo.definitionRef;

			if (definition.isBlocking) {
				var x = 15;
				var y = 12;

				var pathData1 = pathMap.getScaledPath('TASK_TYPE_USER_1', {
					abspos: {
						x: x,
						y: y
					}
				});

				/* user path */drawPath(p, pathData1, {
					strokeWidth: 0.5,
					fill: 'none'
				});

				var pathData2 = pathMap.getScaledPath('TASK_TYPE_USER_2', {
					abspos: {
						x: x,
						y: y
					}
				});

				/* user2 path */drawPath(p, pathData2, {
					strokeWidth: 0.5,
					fill: 'none'
				});

				var pathData3 = pathMap.getScaledPath('TASK_TYPE_USER_3', {
					abspos: {
						x: x,
						y: y
					}
				});

				/* user3 path */drawPath(p, pathData3, {
					strokeWidth: 0.5,
					fill: 'black'
				});
			} else {
				var pathData = pathMap.getScaledPath('TASK_TYPE_MANUAL', {
					abspos: {
						x: 17,
						y: 15
					}
				});

				/* manual path */drawPath(p, pathData, {
					strokeWidth: 1.25,
					fill: 'white',
					stroke: 'black'
				});
			}

			attachPlanningTableMarker(p, element);

			return task;
		},

		'cmmn:CaseTask': function cmmnCaseTask(p, element, attrs) {
			var task = renderer('cmmn:Task')(p, element, attrs);

			var pathData = pathMap.getScaledPath('TASK_TYPE_FOLDER', {
				abspos: {
					x: 7,
					y: 7
				}
			});

			/* manual path */drawPath(p, pathData, {
				strokeWidth: 1.25,
				fill: 'white',
				stroke: 'black'
			});

			return task;
		},

		'cmmn:ProcessTask': function cmmnProcessTask(p, element, attrs) {
			var task = renderer('cmmn:Task')(p, element, attrs);

			var pathData = pathMap.getScaledPath('TASK_TYPE_CHEVRON', {
				abspos: {
					x: 5,
					y: 5
				}
			});

			/* manual path */drawPath(p, pathData, {
				strokeWidth: 1.25,
				fill: 'white',
				stroke: 'black'
			});

			return task;
		},

		'cmmn:DecisionTask': function cmmnDecisionTask(p, element, attrs) {
			var task = renderer('cmmn:Task')(p, element, attrs);

			var headerPathData = pathMap.getScaledPath('TASK_TYPE_BUSINESS_RULE_HEADER', {
				abspos: {
					x: 8,
					y: 8
				}
			});

			drawPath(p, headerPathData, {
				strokeWidth: 1,
				fill: '000'
			});

			var headerData = pathMap.getScaledPath('TASK_TYPE_BUSINESS_RULE_MAIN', {
				abspos: {
					x: 8,
					y: 8
				}
			});

			drawPath(p, headerData, {
				strokeWidth: 1
			});

			return task;
		},

		'cmmn:CaseFileItem': function cmmnCaseFileItem(p, element, attrs) {
			var pathData = pathMap.getScaledPath('DATA_OBJECT_PATH', {
				xScaleFactor: 1,
				yScaleFactor: 1,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: 0.474,
					my: 0.296
				}
			});

			return drawPath(p, pathData, { fill: 'white' });
		},

		// ARTIFACTS
		'cmmn:TextAnnotation': function cmmnTextAnnotation(p, element) {
			var style = {
				'fill': 'none',
				'stroke': 'none'
			};
			var textElement = drawRect(p, element.width, element.height, 0, 0, style);
			var textPathData = pathMap.getScaledPath('TEXT_ANNOTATION', {
				xScaleFactor: 1,
				yScaleFactor: 1,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: 0.0,
					my: 0.0
				}
			});
			drawPath(p, textPathData);

			var text = getBusinessObject(element).text || '';
			renderLabel(p, text, { box: element, align: 'left-middle', padding: 5 });

			return textElement;
		},

		'cmmn:Association': function cmmnAssociation(p, element, attrs) {

			var semantic = getBusinessObject(element);

			attrs = assign({
				strokeDasharray: '0.5, 5',
				strokeLinecap: 'round',
				strokeLinejoin: 'round'
			}, attrs || {});

			if (semantic.associationDirection === 'One' || semantic.associationDirection === 'Both') {
				attrs.markerEnd = marker('association-end');
			}

			if (semantic.associationDirection === 'Both') {
				attrs.markerStart = marker('association-start');
			}

			return drawLine(p, element.waypoints, attrs);
		},

		// MARKERS
		'StageMarker': function StageMarker(p, element) {
			var markerRect = drawRect(p, 14, 14, 0, {
				strokeWidth: 1,
				stroke: 'black'
			});

			translate(markerRect, element.width / 2 - 7, element.height - 17);

			var path = isCollapsed(element) ? 'MARKER_STAGE_COLLAPSED' : 'MARKER_STAGE_EXPANDED';

			var stagePath = pathMap.getScaledPath(path, {
				xScaleFactor: 1.5,
				yScaleFactor: 1.5,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: (element.width / 2 - 7) / element.width,
					my: (element.height - 17) / element.height
				}
			});

			drawPath(p, stagePath);
		},

		'RequiredMarker': function RequiredMarker(p, element, position) {
			var path = pathMap.getScaledPath('MARKER_REQUIRED', {
				xScaleFactor: 1,
				yScaleFactor: 1,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: (element.width / 2 + position) / element.width,
					my: (element.height - 17) / element.height
				}
			});

			drawPath(p, path, { strokeWidth: 3 });
		},

		'AutoCompleteMarker': function AutoCompleteMarker(p, element, position) {
			var markerRect = drawRect(p, 11, 14, 0, {
				strokeWidth: 1,
				stroke: 'black',
				fill: 'black'
			});

			translate(markerRect, element.width / 2 + position + 2, element.height - 17);
		},

		'ManualActivationMarker': function ManualActivationMarker(p, element, position) {
			var path = pathMap.getScaledPath('MARKER_MANUAL_ACTIVATION', {
				xScaleFactor: 1,
				yScaleFactor: 1,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: (element.width / 2 + position) / element.width,
					my: (element.height - 17) / element.height
				}
			});

			drawPath(p, path, { strokeWidth: 1 });
		},

		'RepetitionMarker': function RepetitionMarker(p, element, position) {
			var path = pathMap.getScaledPath('MARKER_REPEATABLE', {
				xScaleFactor: 1,
				yScaleFactor: 1,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: (element.width / 2 + position) / element.width,
					my: (element.height - 17) / element.height
				}
			});

			drawPath(p, path);
		},

		'PlanningTableMarker': function PlanningTableMarker(p, element, position) {
			var planningTableRect = drawRect(p, 30, 24, 0, {
				strokeWidth: 1.5,
				stroke: 'black'
			});

			translate(planningTableRect, element.width / 2 - 15, -17);

			var isCollapsed = isPlanningTableCollapsed(element);

			var marker = isCollapsed ? 'MARKER_PLANNING_TABLE_COLLAPSED' : 'MARKER_PLANNING_TABLE_EXPANDED';

			var stagePath = pathMap.getScaledPath(marker, {
				xScaleFactor: 1.5,
				yScaleFactor: 1.5,
				containerWidth: element.width,
				containerHeight: element.height,
				position: {
					mx: (element.width / 2 - 15) / element.width,
					my: -17 / element.height
				}
			});

			drawPath(p, stagePath, {
				strokeWidth: 1.5
			});
		},

		'cmmn:OnPart': function cmmnOnPart(p, element) {
			var pathData = createPathFromConnection(element);

			var path = drawPath(p, pathData, {
				strokeDasharray: '10, 5, 2, 5, 2, 5',
				strokeWidth: 1.5
			});

			return path;
		},
		'cmmn:PlanItemOnPart': function cmmnPlanItemOnPart(p, element) {
			return renderer('cmmn:OnPart')(p, element);
		},
		'cmmn:CaseFileItemOnPart': function cmmnCaseFileItemOnPart(p, element) {
			return renderer('cmmn:OnPart')(p, element);
		},
		'cmmn:EntryCriterion': function cmmnEntryCriterion(p, element) {
			return drawDiamond(p, element.width, element.height, {
				fill: 'white'
			});
		},
		'cmmn:ExitCriterion': function cmmnExitCriterion(p, element) {
			return drawDiamond(p, element.width, element.height, {
				fill: 'black'
			});
		},

		'cmmndi:CMMNEdge': function cmmndiCMMNEdge(p, element) {

			var bo = getBusinessObject(element);

			if (bo.cmmnElementRef) {
				return renderer(bo.cmmnElementRef.$type)(p, element);
			}

			var pathData = createPathFromConnection(element);

			var path = drawPath(p, pathData, {
				strokeDasharray: '3, 5',
				strokeWidth: 1
			});

			return path;
		},

		'label': function label(parentGfx, element) {
			// Update external label size and bounds during rendering when
			// we have the actual rendered bounds anyway.

			var textElement = renderExternalLabel(parentGfx, element);

			var textBBox;

			try {
				textBBox = textElement.getBBox();
			} catch (e) {
				textBBox = { width: 0, height: 0, x: 0 };
			}

			// update element.x so that the layouted text is still
			// center alligned (newX = oldMidX - newWidth / 2)
			element.x = Math.ceil(element.x + element.width / 2) - Math.ceil(textBBox.width / 2);

			// take element width, height from actual bounds
			element.width = Math.ceil(textBBox.width);
			element.height = Math.ceil(textBBox.height);

			// compensate bounding box x
			svgAttr(textElement, {
				transform: 'translate(' + -1 * textBBox.x + ',0)'
			});

			return textElement;
		}
	};

	// attach markers /////////////////////////

	function attachTaskMarkers(p, element) {
		var obj = getBusinessObject(element);
		var padding = 6;

		var markers = [];

		if (isRequired(obj)) {
			markers.push({ marker: 'RequiredMarker', width: 1 });
		}

		if (isManualActivation(obj)) {
			markers.push({ marker: 'ManualActivationMarker', width: 14 });
		}

		if (isRepeatable(obj)) {
			markers.push({ marker: 'RepetitionMarker', width: 14 });
		}

		if (markers.length) {

			if (markers.length === 1) {
				// align marker in the middle of the element
				drawMarker(markers[0].marker, p, element, markers[0].width / 2 * -1);
			} else if (markers.length === 2) {
				/* align marker:
				 *
				 *      |             |
				 *      +-------------+
				 *             ^
				 *             |
				 *         +-+   +-+
				 *         |0|   |1| <-- markers
				 *         +-+   +-+
				 * (leftMarker)  (rightMarker)
				 */
				drawMarker(markers[0].marker, p, element, markers[0].width * -1 - padding / 2);
				drawMarker(markers[1].marker, p, element, padding / 2);
			} else if (markers.length === 3) {
				/* align marker:
				 *
				 *      |             |
				 *      +-------------+
				 *             ^
				 *             |
				 *      +-+   +-+   +-+
				 *      |0|   |1|   |2| <-- markers
				 *      +-+   +-+   +-+
				 */

				/* 1 */drawMarker(markers[1].marker, p, element, markers[1].width / 2 * -1);
				/* 0 */drawMarker(markers[0].marker, p, element, markers[1].width / 2 * -1 - padding - markers[0].width);
				/* 2 */drawMarker(markers[2].marker, p, element, markers[1].width / 2 + padding);
			}
		}
	}

	function attachCasePlanModelMarkers(p, element) {
		var obj = getBusinessObject(element);

		if (isAutoComplete(obj)) {
			drawMarker('AutoCompleteMarker', p, element, -7);
		}
	}

	function attachStageMarkers(p, element, stage) {
		var obj = getBusinessObject(element);
		var padding = 6;

		drawMarker('StageMarker', p, element, -7);

		var leftMarkers = [];

		if (isRequired(obj)) {
			leftMarkers.push({ marker: 'RequiredMarker', width: 1 });
		}

		if (isAutoComplete(obj)) {
			leftMarkers.push({ marker: 'AutoCompleteMarker', width: 14 });
		}

		if (leftMarkers.length) {

			if (leftMarkers.length === 1) {
				drawMarker(leftMarkers[0].marker, p, element, leftMarkers[0].width * -1 - 7 - padding);
			} else if (leftMarkers.length === 2) {
				drawMarker(leftMarkers[0].marker, p, element, leftMarkers[1].width * -1 - 7 - padding - leftMarkers[0].width * -1 - padding);

				drawMarker(leftMarkers[1].marker, p, element, leftMarkers[1].width * -1 - 7 - padding);
			}
		}

		var rightMarkers = [];

		if (isManualActivation(obj)) {
			rightMarkers.push({ marker: 'ManualActivationMarker', width: 14 });
		}

		if (isRepeatable(obj)) {
			rightMarkers.push({ marker: 'RepetitionMarker', width: 14 });
		}

		if (rightMarkers.length) {

			if (rightMarkers.length === 1) {
				drawMarker(rightMarkers[0].marker, p, element, 7 + padding);
			} else if (rightMarkers.length === 2) {
				drawMarker(rightMarkers[0].marker, p, element, 7 + padding);
				drawMarker(rightMarkers[1].marker, p, element, 7 + padding + rightMarkers[0].width + padding);
			}
		}
	}

	function attachPlanningTableMarker(p, element) {
		if (hasPlanningTable(element)) {
			drawMarker('PlanningTableMarker', p, element);
		}
	}

	function drawMarker(marker, parent, element, position) {
		renderer(marker)(parent, element, position);
	}

	// draw shape and connection ////////////////////////////////////

	function drawShape(parent, element) {
		var h = handlers[element.type];

		/* jshint -W040 */
		if (!h) {
			return BaseRenderer.prototype.drawShape.apply(this, [parent, element]);
		} else {
			return h(parent, element);
		}
	}

	function drawConnection(parent, element) {
		var type = element.type;
		var h = handlers[type];

		/* jshint -W040 */
		if (!h) {
			return BaseRenderer.prototype.drawConnection.apply(this, [parent, element]);
		} else {
			return h(parent, element);
		}
	}

	this.canRender = function (element) {
		return is(element, 'cmmn:CMMNElement') || is(element, 'cmmndi:CMMNEdge');
	};

	this.drawShape = drawShape;
	this.drawConnection = drawConnection;

	// hook onto canvas init event to initialize
	// connection start/end markers on svg
	eventBus.on('canvas.init', function (event) {
		initMarkers(event.svg);
	});
}

inherits(CmmnRenderer, BaseRenderer);

CmmnRenderer.$inject = ['eventBus', 'styles', 'pathMap'];

module.exports = CmmnRenderer;

},{"13":13,"15":15,"32":32,"54":54,"55":55,"56":56,"58":58,"59":59,"60":60,"79":79}],5:[function(_dereq_,module,exports){
'use strict';

/**
 * Map containing SVG paths needed by CmmnRenderer.
 */

function PathMap() {

	var PATH_USER_TYPE_1 = 'm {mx},{my} c 0.909,-0.845 1.594,-2.049 1.594,-3.385 0,-2.554 -1.805,-4.62199999 ' + '-4.357,-4.62199999 -2.55199998,0 -4.28799998,2.06799999 -4.28799998,4.62199999 0,1.348 ' + '0.974,2.562 1.89599998,3.405 -0.52899998,0.187 -5.669,2.097 -5.794,4.7560005 v 6.718 ' + 'h 17 v -6.718 c 0,-2.2980005 -5.5279996,-4.5950005 -6.0509996,-4.7760005 z' + 'm -8,6 l 0,5.5 m 11,0 l 0,-5';

	var PATH_USER_TYPE_2 = 'm {mx},{my} m 2.162,1.009 c 0,2.4470005 -2.158,4.4310005 -4.821,4.4310005 ' + '-2.66499998,0 -4.822,-1.981 -4.822,-4.4310005';

	var PATH_USER_TYPE_3 = 'm {mx},{my} m -6.9,-3.80 c 0,0 2.25099998,-2.358 4.27399998,-1.177 2.024,1.181 4.221,1.537 ' + '4.124,0.965 -0.098,-0.57 -0.117,-3.79099999 -4.191,-4.13599999 -3.57499998,0.001 ' + '-4.20799998,3.36699999 -4.20699998,4.34799999 z';

	/**
	 * Contains a map of path elements
	 *
	 * <h1>Path definition</h1>
	 * A parameterized path is defined like this:
	 * <pre>
	 * 'EVENT_TIMER_WH': {
	 *   d: 'M {mx},{my} l {e.x0},-{e.y0} m -{e.x0},{e.y0} l {e.x1},{e.y1} ',
	 *   height: 17.5,
	 *   width:  17.5,
	 *   heightElements: [2.5, 7.5],
	 *   widthElements: [2.5, 7.5]
	 * }
	 * </pre>
	 * <p>It's important to specify a correct <b>height and width</b> for the path as the scaling
	 * is based on the ratio between the specified height and width in this object and the
	 * height and width that is set as scale target (Note x,y coordinates will be scaled with
	 * individual ratios).</p>
	 * <p>The '<b>heightElements</b>' and '<b>widthElements</b>' array must contain the values that will be scaled.
	 * The scaling is based on the computed ratios.
	 * Coordinates on the y axis should be in the <b>heightElement</b>'s array, they will be scaled using
	 * the computed ratio coefficient.
	 * In the parameterized path the scaled values can be accessed through the 'e' object in {} brackets.
	 *   <ul>
	 *    <li>The values for the y axis can be accessed in the path string using {e.y0}, {e.y1}, ....</li>
	 *    <li>The values for the x axis can be accessed in the path string using {e.x0}, {e.x1}, ....</li>
	 *   </ul>
	 *   The numbers x0, x1 respectively y0, y1, ... map to the corresponding array index.
	 * </p>
	 */
	this.pathMap = {

		// TASK DECORATOR

		'TASK_TYPE_FOLDER': {
			d: 'm {mx},{my} l18,0 l0,12 l-18,0 l0,-12 m 2,0 l3,-4 l5,0 l3,4'
		},
		'TASK_TYPE_CHEVRON': {
			d: 'm {mx},{my} l15,0 l6,6 l-6,6 l-15,0 l6,-6 l-6,-6'
		},
		'TASK_TYPE_USER_1': {
			d: PATH_USER_TYPE_1
		},
		'TASK_TYPE_USER_2': {
			d: PATH_USER_TYPE_2
		},
		'TASK_TYPE_USER_3': {
			d: PATH_USER_TYPE_3
		},
		'TASK_TYPE_MANUAL': {
			d: 'm {mx},{my} c 0.234,-0.01 5.604,0.008 8.029,0.004 0.808,0 1.271,-0.172 1.417,-0.752 0.227,-0.898 ' + '-0.334,-1.314 -1.338,-1.316 -2.467,-0.01 -7.886,-0.004 -8.108,-0.004 -0.014,-0.079 0.016,-0.533 0,-0.61 ' + '0.195,-0.042 8.507,0.006 9.616,0.002 0.877,-0.007 1.35,-0.438 1.353,-1.208 0.003,-0.768 -0.479,-1.09 ' + '-1.35,-1.091 -2.968,-0.002 -9.619,-0.013 -9.619,-0.013 v -0.591 c 0,0 5.052,-0.016 7.225,-0.016 ' + '0.888,-0.002 1.354,-0.416 1.351,-1.193 -0.006,-0.761 -0.492,-1.196 -1.361,-1.196 -3.473,-0.005 ' + '-10.86,-0.003 -11.0829995,-0.003 -0.022,-0.047 -0.045,-0.094 -0.069,-0.139 0.3939995,-0.319 ' + '2.0409995,-1.626 2.4149995,-2.017 0.469,-0.4870005 0.519,-1.1650005 0.162,-1.6040005 -0.414,-0.511 ' + '-0.973,-0.5 -1.48,-0.236 -1.4609995,0.764 -6.5999995,3.6430005 -7.7329995,4.2710005 -0.9,0.499 ' + '-1.516,1.253 -1.882,2.19 -0.37000002,0.95 -0.17,2.01 -0.166,2.979 0.004,0.718 -0.27300002,1.345 ' + '-0.055,2.063 0.629,2.087 2.425,3.312 4.859,3.318 4.6179995,0.014 9.2379995,-0.139 13.8569995,-0.158 ' + '0.755,-0.004 1.171,-0.301 1.182,-1.033 0.012,-0.754 -0.423,-0.969 -1.183,-0.973 -1.778,-0.01 ' + '-5.824,-0.004 -6.04,-0.004 10e-4,-0.084 0.003,-0.586 10e-4,-0.67 z'
		},

		'TASK_TYPE_BUSINESS_RULE_HEADER': {
			d: 'm {mx},{my} 0,4 20,0 0,-4 z'
		},
		'TASK_TYPE_BUSINESS_RULE_MAIN': {
			d: 'm {mx},{my} 0,12 20,0 0,-12 z' + 'm 0,8 l 20,0 ' + 'm -13,-4 l 0,8'
		},

		// EVENT LISTENER DECORATOR

		'EVENT_TIMER_WH': {
			d: 'M {mx},{my} l {e.x0},-{e.y0} m -{e.x0},{e.y0} l {e.x1},{e.y1} ',
			height: 36,
			width: 36,
			heightElements: [10, 2],
			widthElements: [3, 7]
		},
		'EVENT_TIMER_LINE': {
			d: 'M {mx},{my} ' + 'm {e.x0},{e.y0} l -{e.x1},{e.y1} ',
			height: 36,
			width: 36,
			heightElements: [10, 3],
			widthElements: [0, 0]
		},

		'EVENT_USER_1': {
			d: PATH_USER_TYPE_1,
			height: 36,
			width: 36,
			heightElements: [],
			widthElements: []

		},
		'EVENT_USER_2': {
			d: PATH_USER_TYPE_2,
			height: 36,
			width: 36,
			heightElements: [],
			widthElements: []
		},
		'EVENT_USER_3': {
			d: PATH_USER_TYPE_3,
			height: 36,
			width: 36,
			heightElements: [],
			widthElements: []
		},

		// MARKERS

		'MARKER_STAGE_EXPANDED': {
			d: 'm{mx},{my} m 2,7 l 10,0',
			height: 10,
			width: 10,
			heightElements: [],
			widthElements: []
		},
		'MARKER_STAGE_COLLAPSED': {
			d: 'm{mx},{my} m 2,7 l 10,0 m -5,-5 l 0,10',
			height: 10,
			width: 10,
			heightElements: [],
			widthElements: []
		},
		'MARKER_REQUIRED': {
			d: 'm{mx},{my} l 0,9 m 0,2 l0,3',
			height: 14,
			width: 3,
			heightElements: [],
			widthElements: []
		},

		'MARKER_MANUAL_ACTIVATION': {
			d: 'm{mx}, {my} l 14,7 l -14,7 l 0,-14 z',
			height: 14,
			width: 14,
			heightElements: [],
			widthElements: []
		},

		'MARKER_REPEATABLE': {
			d: 'm{mx},{my} m 3,0 l 0,14 m 6,-14 l 0,14 m -10,-10 l 14,0 m -14,6 l 14,0',
			height: 14,
			width: 14,
			heightElements: [],
			widthElements: []
		},

		'MARKER_PLANNING_TABLE_EXPANDED': {
			d: 'm{mx},{my} m 0,12 l 30,0 m -20,-12 l 0, 24 m 10, -24 l 0, 24 m -10, -6 l 10,0',
			height: 24,
			width: 30,
			heightElements: [],
			widthElements: []
		},
		'MARKER_PLANNING_TABLE_COLLAPSED': {
			d: 'm{mx},{my} m 0,12 l 30,0 m -20,-12 l 0, 24 m 10, -24 l 0, 24 m -10, -6 l 10,0 m -5, -6 l 0,12',
			height: 10,
			width: 10,
			heightElements: [],
			widthElements: []
		},

		'DATA_OBJECT_PATH': {
			d: 'm 0,0 {e.x1},0 {e.x0},{e.y0} 0,{e.y1} -{e.x2},0 0,-{e.y2} {e.x1},0 0,{e.y0} {e.x0},0',
			height: 61,
			width: 51,
			heightElements: [10, 50, 60],
			widthElements: [10, 40, 50, 60]
		},

		'TEXT_ANNOTATION': {
			d: 'm {mx}, {my} m 10,0 l -10,0 l 0,{e.y0} l 10,0',
			height: 30,
			width: 10,
			heightElements: [30],
			widthElements: [10]
		}
	};

	this.getRawPath = function getRawPath(pathId) {
		return this.pathMap[pathId].d;
	};

	/**
	 * Scales the path to the given height and width.
	 * <h1>Use case</h1>
	 * <p>Use case is to scale the content of elements (event) based
	 * on the element bounding box's size.
	 * </p>
	 * <h1>Why not transform</h1>
	 * <p>Scaling a path with transform() will also scale the stroke and IE does not support
	 * the option 'non-scaling-stroke' to prevent this.
	 * Also there are use cases where only some parts of a path should be
	 * scaled.</p>
	 *
	 * @param {String} pathId The ID of the path.
	 * @param {Object} param <p>
	 *   Example param object scales the path to 60% size of the container (data.width, data.height).
	 *   <pre>
	 *   {
	 *     xScaleFactor: 0.6,
	 *     yScaleFactor:0.6,
	 *     containerWidth: data.width,
	 *     containerHeight: data.height,
	 *     position: {
	 *       mx: 0.46,
	 *       my: 0.2,
	 *     }
	 *   }
	 *   </pre>
	 *   <ul>
	 *    <li>targetpathwidth = xScaleFactor * containerWidth</li>
	 *    <li>targetpathheight = yScaleFactor * containerHeight</li>
	 *    <li>Position is used to set the starting coordinate of the path. M is computed:
		*    <ul>
		*      <li>position.x * containerWidth</li>
		*      <li>position.y * containerHeight</li>
		*    </ul>
		*    Center of the container <pre> position: {
	 *       mx: 0.5,
	 *       my: 0.5,
	 *     }</pre>
	 *     Upper left corner of the container
	 *     <pre> position: {
	 *       mx: 0.0,
	 *       my: 0.0,
	 *     }</pre>
	 *    </li>
	 *   </ul>
	 * </p>
	 *
	 */
	this.getScaledPath = function getScaledPath(pathId, param) {
		var rawPath = this.pathMap[pathId];

		// positioning
		// compute the start point of the path
		var mx, my;

		if (param.abspos) {
			mx = param.abspos.x;
			my = param.abspos.y;
		} else {
			mx = param.containerWidth * param.position.mx;
			my = param.containerHeight * param.position.my;
		}

		var coordinates = {}; // map for the scaled coordinates
		if (param.position) {

			// path
			var heightRatio = param.containerHeight / rawPath.height * param.yScaleFactor;
			var widthRatio = param.containerWidth / rawPath.width * param.xScaleFactor;

			// Apply height ratio
			for (var heightIndex = 0; heightIndex < rawPath.heightElements.length; heightIndex++) {
				coordinates['y' + heightIndex] = rawPath.heightElements[heightIndex] * heightRatio;
			}

			// Apply width ratio
			for (var widthIndex = 0; widthIndex < rawPath.widthElements.length; widthIndex++) {
				coordinates['x' + widthIndex] = rawPath.widthElements[widthIndex] * widthRatio;
			}
		}

		// Apply value to raw path
		var path = format(rawPath.d, {
			mx: mx,
			my: my,
			e: coordinates
		});
		return path;
	};
}

module.exports = PathMap;

// helpers /////////////////

// copied from https://github.com/adobe-webplatform/Snap.svg/blob/master/src/svg.js
var tokenRegex = /\{([^}]+)\}/g,
		objNotationRegex = /(?:(?:^|\.)(.+?)(?=\[|\.|$|\()|\[('|")(.+?)\2\])(\(\))?/g; // matches .xxxxx or ["xxxxx"] to run over object properties

function replacer(all, key, obj) {
	var res = obj;
	key.replace(objNotationRegex, function (all, name, quote, quotedName, isFunc) {
		name = name || quotedName;
		if (res) {
			if (name in res) {
				res = res[name];
			}
			typeof res == 'function' && isFunc && (res = res());
		}
	});
	res = (res == null || res == obj ? all : res) + '';

	return res;
}

function format(str, obj) {
	return String(str).replace(tokenRegex, function (all, key) {
		return replacer(all, key, obj);
	});
}

},{}],6:[function(_dereq_,module,exports){
'use strict';

module.exports = {
	__init__: ['cmmnRenderer'],
	cmmnRenderer: ['type', _dereq_(4)],
	pathMap: ['type', _dereq_(5)]
};

},{"4":4,"5":5}],7:[function(_dereq_,module,exports){
'use strict';

var some = _dereq_(59).some;

var ModelUtil = _dereq_(15),
		is = ModelUtil.is,
		getBusinessObject = ModelUtil.getBusinessObject;

var Model = _dereq_(46);

/**
 * Return true if given elements are the same.
 *
 * @param {Object} a
 * @param {Object} b
 *
 * @return {boolean}
 */
function isSame(a, b) {
	return a === b;
}

module.exports.isSame = isSame;

/**
 * Return true if given cases are the same.
 *
 * @param {ModdleElement} a
 * @param {ModdleElement} b
 *
 * @return {boolean}
 */
function isSameCase(a, b) {
	return isSame(getCase(a), getCase(b));
}

module.exports.isSameCase = isSameCase;

function getCase(element) {
	return getParent(getBusinessObject(element), 'cmmn:Case');
}

module.exports.getCase = getCase;

/**
 * Return the parents of the element with any of the given types.
 *
 * @param {ModdleElement} element
 * @param {String|Array<String>} anyType
 *
 * @return {Array<ModdleElement>}
 */
function getParents(element, anyType) {

	var parents = [];

	if (typeof anyType === 'string') {
		anyType = [anyType];
	}

	while (element) {
		element = element.$parent || element.parent;

		if (element) {

			if (anyType) {
				if (isAny(element, anyType)) {
					parents.push(element);
				}
			} else {
				parents.push(element);
			}
		}
	}

	return parents;
}

module.exports.getParents = getParents;

/**
 * Return the parent of the element with any of the given types.
 *
 * @param {ModdleElement} element
 * @param {String|Array<String>} anyType
 *
 * @return {ModdleElement}
 */
function getParent(element, anyType) {

	if (typeof anyType === 'string') {
		anyType = [anyType];
	}

	while (element = element.$parent || element.parent) {
		if (anyType) {
			if (isAny(element, anyType)) {
				return element;
			}
		} else {
			return element;
		}
	}

	return null;
}

module.exports.getParent = getParent;

/**
 * Return true if element has any of the given types.
 *
 * @param {djs.model.Base} element
 * @param {Array<String>} types
 *
 * @return {Boolean}
 */
function isAny(element, types) {
	return some(types, function (t) {
		return is(element, t);
	});
}

module.exports.isAny = isAny;

function isLabel(element) {
	return element instanceof Model.Label;
}

module.exports.isLabel = isLabel;

},{"15":15,"46":46,"59":59}],8:[function(_dereq_,module,exports){
'use strict';

var assign = _dereq_(59).assign,
		map = _dereq_(59).map;

var LabelUtil = _dereq_(14);

var is = _dereq_(15).is;

var hasExternalLabel = LabelUtil.hasExternalLabel,
		getExternalLabelBounds = LabelUtil.getExternalLabelBounds,
		isCollapsed = _dereq_(13).isCollapsed,
		elementToString = _dereq_(11).elementToString;

function elementData(semantic, attrs) {
	return assign({
		id: semantic.id,
		type: semantic.$type,
		businessObject: semantic
	}, attrs);
}

function collectWaypoints(waypoints) {
	return map(waypoints, function (p) {
		return { x: p.x, y: p.y };
	});
}

function notYetDrawn(semantic, refSemantic, property) {
	return new Error('element ' + elementToString(refSemantic) + ' referenced by ' + elementToString(semantic) + '#' + property + ' not yet drawn');
}

/**
 * An importer that adds cmmn elements to the canvas
 *
 * @param {EventBus} eventBus
 * @param {Canvas} canvas
 * @param {ElementFactory} elementFactory
 * @param {ElementRegistry} elementRegistry
 */
function CmmnImporter(eventBus, canvas, elementFactory, elementRegistry) {
	this._eventBus = eventBus;
	this._canvas = canvas;

	this._elementFactory = elementFactory;
	this._elementRegistry = elementRegistry;
}

CmmnImporter.$inject = ['eventBus', 'canvas', 'elementFactory', 'elementRegistry'];

module.exports = CmmnImporter;

/**
 * Set the diagram as root element
 */
CmmnImporter.prototype.root = function (diagram) {
	var element = this._elementFactory.createRoot(elementData(diagram));

	this._canvas.setRootElement(element);

	return element;
};

/**
 * Add cmmn element (semantic) to the canvas onto the
 * specified parent shape.
 */
CmmnImporter.prototype.add = function (semantic, parentElement) {

	var di = semantic.di,
			element,
			hidden;

	// SHAPE
	if (di && is(di, 'cmmndi:CMMNShape') && !this._getElement(semantic)) {

		var collapsed = isCollapsed(semantic);

		hidden = parentElement && (parentElement.hidden || parentElement.collapsed);

		var bounds = semantic.di.bounds;

		element = this._elementFactory.createShape(elementData(semantic, {
			collapsed: collapsed,
			hidden: hidden,
			x: Math.round(bounds.x),
			y: Math.round(bounds.y),
			width: Math.round(bounds.width),
			height: Math.round(bounds.height)
		}));

		if (is(semantic, 'cmmn:Criterion')) {
			this._attachCriterion(semantic, element);
		}

		this._canvas.addShape(element, parentElement);
	}

	// CONNECTION
	else if (di && is(di, 'cmmndi:CMMNEdge') || is(semantic, 'cmmndi:CMMNEdge')) {

			var source = this._getSource(semantic),
					target = this._getTarget(semantic);

			hidden = parentElement && (parentElement.hidden || parentElement.collapsed) || source && source.hidden || target && target.hidden;

			var waypoint = (semantic.di || {}).waypoint || semantic.waypoint;

			element = this._elementFactory.createConnection(elementData(semantic, {
				hidden: hidden,
				source: source,
				target: target,
				waypoints: collectWaypoints(waypoint)
			}));

			this._canvas.addConnection(element, parentElement);
		} else {
			throw new Error('unknown di ' + elementToString(di) + ' for element ' + elementToString(semantic));
		}

	// (optional) LABEL
	if (hasExternalLabel(semantic)) {
		this.addLabel(semantic, element);
	}

	this._eventBus.fire('cmmnElement.added', { element: element });

	return element;
};

/**
 * Attach the criterion element to the given host
 *
 * @param {ModdleElement} criterionSemantic
 * @param {djs.model.Base} criterionElement
 */
CmmnImporter.prototype._attachCriterion = function (criterionSemantic, criterionElement) {
	var hostSemantic = criterionSemantic.$parent;

	if (!hostSemantic) {
		throw new Error('missing ' + elementToString(criterionSemantic) + '$parent');
	}

	var host = this._elementRegistry.get(hostSemantic.id),
			attachers = host && host.attachers;

	if (!host) {
		throw notYetDrawn(criterionSemantic, hostSemantic, 'criterion');
	}

	// wire element.host <> host.attachers
	criterionElement.host = host;

	if (!attachers) {
		host.attachers = attachers = [];
	}

	if (attachers.indexOf(criterionElement) === -1) {
		attachers.push(criterionElement);
	}
};

/**
 * add label for an element
 */
CmmnImporter.prototype.addLabel = function (semantic, element) {
	var bounds = getExternalLabelBounds(semantic, element);

	var label = this._elementFactory.createLabel(elementData(semantic, {
		id: semantic.id + '_label',
		labelTarget: element,
		type: 'label',
		hidden: element.hidden,
		x: Math.round(bounds.x),
		y: Math.round(bounds.y),
		width: Math.round(bounds.width),
		height: Math.round(bounds.height)
	}));

	return this._canvas.addShape(label, element.parent);
};

CmmnImporter.prototype._getSource = function (semantic) {
	var cmmnElement = semantic.cmmnElementRef;

	if (cmmnElement) {

		if (is(cmmnElement, 'cmmn:OnPart')) {

			if (cmmnElement.exitCriterionRef) {
				return this._getEnd(cmmnElement, 'exitCriterionRef');
			}

			return this._getEnd(cmmnElement, 'sourceRef');
		}

		if (is(cmmnElement, 'cmmn:Association')) {
			return this._getEnd(cmmnElement, 'sourceRef');
		}
	}

	if (is(semantic, 'cmmndi:CMMNEdge')) {
		return this._getEnd(semantic, 'sourceCMMNElementRef');
	}
};

CmmnImporter.prototype._getTarget = function (semantic) {
	var cmmnElement = semantic.cmmnElementRef;

	if (cmmnElement) {

		if (is(cmmnElement, 'cmmn:Association')) {
			return this._getEnd(cmmnElement, 'targetRef');
		}
	}

	return this._getEnd(semantic, 'targetCMMNElementRef');
};

CmmnImporter.prototype._getEnd = function (semantic, side) {
	var refSemantic = semantic[side];
	var element = refSemantic && this._getElement(refSemantic);

	if (element) {
		return element;
	}

	if (refSemantic) {
		throw notYetDrawn(semantic, refSemantic, side);
	} else {
		throw new Error(elementToString(semantic) + '#' + side + 'Ref not specified');
	}
};

CmmnImporter.prototype._getElement = function (semantic) {
	return this._elementRegistry.get(semantic.id);
};

},{"11":11,"13":13,"14":14,"15":15,"59":59}],9:[function(_dereq_,module,exports){
'use strict';

var forEach = _dereq_(59).forEach,
		filter = _dereq_(59).filter;

var Refs = _dereq_(74);

var elementToString = _dereq_(11).elementToString;
var is = _dereq_(15).is;

var Collections = _dereq_(47);

var diRefs = new Refs({ name: 'cmmnElement', enumerable: true }, { name: 'di' });

function CmmnTreeWalker(handler) {

	// list of elements to handle deferred to ensure
	// prerequisites are drawn
	var deferred = [];

	// list of CMMNEdges which cmmnElementRef is equals null:
	// - it is a connection which does not have a representation
	//   in case plan model
	// - it is a connection between a human (plan item) task and a
	//   discretionary item
	var connections = [];

	var discretionaryConnections = {};

	// list of cases to draw
	var cases = [];

	var handledDiscretionaryItems = {};

	// list of elements (textAnnotations and caseFileItems)
	var elementsWithoutParent = [];

	var associations = [];

	// Helpers /////////////////

	function isDiscretionaryItemHandled(item) {
		return !!handledDiscretionaryItems[item.id];
	}

	function handledDiscretionaryItem(item) {
		handledDiscretionaryItems[item.id] = item;
	}

	/**
	 * Returns the surrounding 'cmmn:Case' element
	 *
	 * @param {ModdleElement} element
	 *
	 * @return {ModdleElement} the surrounding case
	 */
	function getCase(element) {
		while (element && !is(element, 'cmmn:Case')) {
			element = element.$parent;
		}
		return element;
	}

	function visit(element, ctx) {
		// call handler
		return handler.element(element, ctx);
	}

	function visitRoot(element, diagram) {
		return handler.root(element, diagram);
	}

	function visitIfDi(element, ctx) {
		try {
			handler.addItem(element);
			return element.di && visit(element, ctx);
		} catch (e) {
			logError(e.message, { element: element, error: e });

			console.error('failed to import ' + elementToString(element));
			console.error(e);
		}
	}

	function logError(message, context) {
		handler.error(message, context);
	}

	function contextual(fn, ctx) {
		return function (e) {
			fn(e, ctx);
		};
	}

	// DI handling /////////////////

	function registerDi(di) {

		var cmmnElement = di.cmmnElementRef;

		if (cmmnElement && !is(di, 'cmmndi:CMMNEdge')) {

			if (cmmnElement.di) {
				logError('multiple DI elements defined for ' + elementToString(cmmnElement), { element: cmmnElement });
			} else {

				var _case = getCase(cmmnElement);
				if (_case && cases.indexOf(_case) === -1) {
					// add _case to the list of cases
					// that should be drawn
					cases.push(_case);
				}

				if (is(cmmnElement, 'cmmn:TextAnnotation') || is(cmmnElement, 'cmmn:CaseFileItem')) {
					elementsWithoutParent.push(cmmnElement);
				}

				diRefs.bind(cmmnElement, 'di');
				cmmnElement.di = di;
			}
		} else if (is(di, 'cmmndi:CMMNEdge')) {
			var shouldHandle = true;

			if (!isReferencingTarget(di)) {
				shouldHandle = false;
				logError('no target referenced in ' + elementToString(di), { element: di });
			}

			if (!isReferencingSource(di)) {
				shouldHandle = false;
				logError('no source referenced in ' + elementToString(di), { element: di });
			}

			if (shouldHandle) {

				if (is(cmmnElement, 'cmmn:Association')) {
					associations.push(di);
				} else if (!cmmnElement) {
					var source = di.sourceCMMNElementRef;
					discretionaryConnections[source.id] = discretionaryConnections[source.id] || [];
					discretionaryConnections[source.id].push(di);
				} else {
					connections.push(function (ctx) {
						handleConnection(di, ctx);
					});
				}
			}
		} else {
			logError('no cmmnElement referenced in ' + elementToString(di), { element: di });
		}
	}

	function isReferencingTarget(edge) {
		if (is(edge.cmmnElementRef, 'cmmn:Association')) {
			return !!edge.cmmnElementRef.targetRef;
		}

		return !!edge.targetCMMNElementRef;
	}

	function isReferencingSource(edge) {
		if (is(edge.cmmnElementRef, 'cmmn:OnPart')) {
			return !!(edge.cmmnElementRef.exitCriterionRef || edge.cmmnElementRef.sourceRef);
		}

		if (is(edge.cmmnElementRef, 'cmmn:Association')) {
			return !!edge.cmmnElementRef.sourceRef;
		}

		return !!edge.sourceCMMNElementRef;
	}

	function handleConnection(connection, context) {
		visit(connection, context);
	}

	function handleDiagram(diagram) {
		handleDiagramElements(diagram.diagramElements);
	}

	function handleDiagramElements(diagramElements) {
		forEach(diagramElements, handleDiagramElement);
	}

	function handleDiagramElement(diagramElement) {
		registerDi(diagramElement);
	}

	// Semantic handling /////////////////

	function handleDefinitions(definitions, diagram) {
		// make sure we walk the correct cmmnElement

		var cmmndi = definitions.CMMNDI;

		// no di -> nothing to import
		if (!cmmndi) {
			return;
		}

		var diagrams = cmmndi.diagrams;

		if (diagram && diagrams.indexOf(diagram) === -1) {
			throw new Error('diagram not part of cmmn:Definitions');
		}

		if (!diagram && diagrams && diagrams.length) {
			diagram = diagrams[0];
		}

		// handle only the first diagram and ignore others
		handleDiagram(diagram);

		var context = visitRoot(diagram, diagram);

		handleCases(cases, context);

		forEach(elementsWithoutParent, contextual(handleElementWithoutParent));
		forEach(associations, contextual(handleAssociation));
	}

	function handleCases(cases, context) {
		forEach(cases, function (_case) {
			handleCase(_case, context);

			// clear collections for the next iteration
			deferred = [];
			connections = [];
		});
	}

	function handleCase(_case, context) {
		var casePlanModel = _case.casePlanModel;

		var casePlanModelContext;
		if (casePlanModel) {
			casePlanModelContext = handleCasePlanModel(casePlanModel, context);
		}

		handleDeferred(deferred);

		forEach(connections, function (d) {
			d(casePlanModelContext);
		});
	}

	function handleCasePlanModel(casePlanModel, context) {
		var newCtx = visitIfDi(casePlanModel, context);

		forEach(casePlanModel.exitCriteria, contextual(handleCriterion, newCtx));

		handlePlanFragment(casePlanModel, newCtx);
		handleElementsWithoutParent(casePlanModel, newCtx);

		return newCtx;
	}

	function handleDeferred(deferred) {
		forEach(deferred, function (d) {
			d();
		});
	}

	function handlePlanFragment(planFragment, context) {
		handlePlanItems(planFragment.planItems, context);

		if (is(planFragment, 'cmmn:Stage')) {
			handleStage(planFragment, context);
		}
	}

	function handleStage(stage, context) {
		handlePlanningTable(stage.planningTable, context);
	}

	function handlePlanningTable(planningTable, context) {
		if (planningTable) {
			forEach(planningTable.tableItems, function (tableItem) {
				if (is(tableItem, 'cmmn:DiscretionaryItem')) {
					handleDiscretionaryItem(tableItem, context);
				} else if (is(tableItem, 'cmmn:PlanningTable')) {
					handlePlanningTable(tableItem, context);
				}
			});
		}
	}

	function handleDiscretionaryItem(discretionayItem, context) {
		if (isDiscretionaryItemHandled(discretionayItem)) {
			return;
		}

		handledDiscretionaryItem(discretionayItem);
		handleItem(discretionayItem, context);
	}

	function handlePlanItems(planItems, context) {
		forEach(planItems, contextual(handleItem, context));
	}

	function handleItem(item, context) {
		var newCtx = visitIfDi(item, context);

		forEach(item.exitCriteria, contextual(handleCriterion, context));
		forEach(item.entryCriteria, contextual(handleCriterion, context));

		var definitionRef = item.definitionRef;
		if (is(definitionRef, 'cmmn:PlanFragment')) {
			handlePlanFragment(definitionRef, newCtx);
			handleElementsWithoutParent(item, newCtx);
		} else if (is(definitionRef, 'cmmn:HumanTask')) {
			handlePlanningTable(definitionRef.planningTable, context);

			var edges = discretionaryConnections[item.id];
			forEach(edges, contextual(handleDiscretionaryConnection, context));
			delete discretionaryConnections[item.id];
		}
	}

	function handleCriterion(criterion, context) {
		deferred.unshift(function () {
			visitIfDi(criterion, context);
		});
	}

	function handleElementsWithoutParent(container, context) {

		if (container.di && container.di.bounds) {
			var elements = getEnclosedElements(elementsWithoutParent, container);

			forEach(elements, function (e) {
				Collections.remove(elementsWithoutParent, e);
				handleElementWithoutParent(e, context);
			});
		}
	}

	function handleElementWithoutParent(element, context) {
		if (is(element, 'cmmn:TextAnnotation')) {
			handleTextAnnotation(element, context);
		} else if (is(element, 'cmmn:CaseFileItem')) {
			handleCaseFileItem(element, context);
		}
	}

	function handleCaseFileItem(caseFileItem, context) {
		visitIfDi(caseFileItem, context);
	}

	function handleTextAnnotation(annotation, context) {
		visitIfDi(annotation, context);
	}

	function handleAssociation(association, context) {
		visit(association, context);
	}

	function handleDiscretionaryConnection(connection, context) {
		deferred.push(function () {
			visit(connection, context);
		});
	}

	function getEnclosedElements(elements, container) {
		var bounds = container.di.bounds;
		return filter(elements, function (e) {
			return e.di.bounds.x > bounds.x && e.di.bounds.x < bounds.x + bounds.width && e.di.bounds.y > bounds.y && e.di.bounds.y < bounds.y + bounds.height;
		});
	}

	// API /////////////////

	return {
		handleDefinitions: handleDefinitions
	};
}

module.exports = CmmnTreeWalker;

},{"11":11,"15":15,"47":47,"59":59,"74":74}],10:[function(_dereq_,module,exports){
'use strict';

var CmmnTreeWalker = _dereq_(9);

/**
 * Import the definitions into a diagram.
 *
 * Errors and warnings are reported through the specified callback.
 *
 * @param  {Diagram} diagram
 * @param  {ModdleElement} definitions
 * @param  {Function} done the callback, invoked with (err, [ warning ]) once the import is done
 */
function importCmmnDiagram(diagram, definitions, done) {

	var importer = diagram.get('cmmnImporter'),
			eventBus = diagram.get('eventBus'),
			itemRegistry = diagram.get('itemRegistry');

	var error,
			warnings = [];

	/**
	 * Walk the diagram semantically, importing (=drawing)
	 * all elements you encounter.
	 *
	 * @param {ModdleElement} definitions
	 */
	function render(definitions) {

		var visitor = {

			root: function root(element) {
				return importer.root(element);
			},

			element: function element(_element, parentShape) {
				return importer.add(_element, parentShape);
			},

			error: function error(message, context) {
				warnings.push({ message: message, context: context });
			},

			addItem: function addItem(item) {
				itemRegistry.add(item);
			}
		};

		var walker = new CmmnTreeWalker(visitor);

		// import
		walker.handleDefinitions(definitions);
	}

	eventBus.fire('import.render.start', { definitions: definitions });

	try {
		render(definitions);
	} catch (e) {
		error = e;
	}

	eventBus.fire('import.render.complete', {
		error: error,
		warnings: warnings
	});

	done(error, warnings);
}

module.exports.importCmmnDiagram = importCmmnDiagram;

},{"9":9}],11:[function(_dereq_,module,exports){
'use strict';

module.exports.elementToString = function (e) {
	if (!e) {
		return '<null>';
	}

	return '<' + e.$type + (e.id ? ' id="' + e.id : '') + '" />';
};

},{}],12:[function(_dereq_,module,exports){
'use strict';

module.exports = {
	cmmnImporter: ['type', _dereq_(8)]
};

},{"8":8}],13:[function(_dereq_,module,exports){
'use strict';

var is = _dereq_(15).is,
		getBusinessObject = _dereq_(15).getBusinessObject,
		isCasePlanModel = _dereq_(15).isCasePlanModel;

module.exports.isCollapsed = function (element) {

	if (!isCasePlanModel(element)) {

		element = getBusinessObject(element);

		var definition = element.definitionRef;
		if (is(definition, 'cmmn:PlanFragment')) {
			return !!(element && element.di && element.di.isCollapsed);
		}
	}

	return false;
};

module.exports.isPlanningTableCollapsed = function (element) {

	element = getBusinessObject(element);

	if (is(element, 'cmmn:Stage') || element.definitionRef && (is(element.definitionRef, 'cmmn:Stage') || is(element.definitionRef, 'cmmn:HumanTask'))) {
		return element.di && element.di.isPlanningTableCollapsed;
	}

	return false;
};

module.exports.isStandardEventVisible = function (element) {
	element = getBusinessObject(element);
	var cmmnElement = element.cmmnElementRef;
	return !!(is(cmmnElement, 'cmmn:OnPart') && element.isStandardEventVisible);
};

},{"15":15}],14:[function(_dereq_,module,exports){
'use strict';

var is = _dereq_(15).is;
var assign = _dereq_(59).assign;

var DEFAULT_LABEL_SIZE = module.exports.DEFAULT_LABEL_SIZE = {
	width: 90,
	height: 20
};

/**
 * Returns true if the given semantic has an external label
 *
 * @param {CmmnElement} semantic
 * @return {Boolean} true if has label
 */
module.exports.hasExternalLabel = function (semantic) {

	if (is(semantic, 'cmmn:PlanItem') || is(semantic, 'cmmn:DiscretionaryItem')) {
		semantic = semantic.definitionRef;
	}

	if (is(semantic, 'cmmndi:CMMNEdge') && semantic.cmmnElementRef) {

		if (is(semantic.cmmnElementRef, 'cmmn:OnPart')) {
			semantic = semantic.cmmnElementRef;
		}
	}

	return is(semantic, 'cmmn:EventListener') || is(semantic, 'cmmn:OnPart') || is(semantic, 'cmmn:CaseFileItem');
};

/**
 * Get the middle of a number of waypoints
 *
 * @param  {Array<Point>} waypoints
 * @return {Point} the mid point
 */
var getWaypointsMid = module.exports.getWaypointsMid = function (waypoints) {

	var mid = waypoints.length / 2 - 1;

	var first = waypoints[Math.floor(mid)];
	var second = waypoints[Math.ceil(mid + 0.01)];

	return {
		x: first.x + (second.x - first.x) / 2,
		y: first.y + (second.y - first.y) / 2
	};
};

var getExternalLabelMid = module.exports.getExternalLabelMid = function (element) {

	var bo = element.businessObject,
			di = bo.di;

	if (!di && is(bo, 'cmmndi:CMMNEdge') && bo.cmmnElementRef) {
		di = bo;
	}

	if (bo && di && di.waypoint) {
		return getWaypointsMid(di.waypoint);
	} else {
		return {
			x: element.x + element.width / 2,
			y: element.y + element.height + DEFAULT_LABEL_SIZE.height / 2
		};
	}
};

/**
 * Returns the bounds of an elements label, parsed from the elements DI or
 * generated from its bounds.
 *
 * @param {CmmnElement} semantic
 * @param {djs.model.Base} element
 */
module.exports.getExternalLabelBounds = function (semantic, element) {

	var mid,
			size,
			bounds,
			di = semantic.di || semantic,
			label = di.label;

	if (label && label.bounds) {
		bounds = label.bounds;

		size = {
			width: Math.max(DEFAULT_LABEL_SIZE.width, bounds.width),
			height: bounds.height
		};

		mid = {
			x: bounds.x + bounds.width / 2,
			y: bounds.y + bounds.height / 2
		};
	} else {

		mid = getExternalLabelMid(element);

		size = DEFAULT_LABEL_SIZE;
	}

	return assign({
		x: mid.x - size.width / 2,
		y: mid.y - size.height / 2
	}, size);
};

var hasLabelBounds = module.exports.hasLabelBounds = function (semantic) {
	return semantic && semantic.di && semantic.di.label && semantic.di.label.bounds;
};

module.exports.getLabelBounds = function (semantic) {
	if (hasLabelBounds(semantic)) {
		return semantic.di.label.bounds;
	}
};

},{"15":15,"59":59}],15:[function(_dereq_,module,exports){
'use strict';

function isInstanceOf(bo, type) {
	return !!(bo && typeof bo.$instanceOf === 'function' && bo.$instanceOf(type));
}

/**
 * Is an element of the given CMMN type?
 *
 * @param  {djs.model.Base|ModdleElement} element
 * @param  {String} type
 *
 * @return {Boolean}
 */
function is(element, type) {
	return isInstanceOf(getBusinessObject(element), type);
}

module.exports.is = is;

/**
 * Return the business object for a given element.
 *
 * @param  {djs.model.Base|ModdleElement} element
 *
 * @return {ModdleElement}
 */
function getBusinessObject(element) {
	return element && element.businessObject ? element.businessObject : element;
}

module.exports.getBusinessObject = getBusinessObject;

function isCasePlanModel(element) {
	element = getBusinessObject(element);
	return is(element, 'cmmn:Stage') && element.$parent && is(element.$parent, 'cmmn:Case');
}

module.exports.isCasePlanModel = isCasePlanModel;

function getDefinition(element) {
	var bo = getBusinessObject(element);

	if (is(element, 'cmmn:PlanItemDefinition') || is(element, 'cmmn:CaseFileItemDefinition')) {
		return bo;
	}

	return bo && bo.definitionRef;
}

module.exports.getDefinition = getDefinition;

function getDefaultControl(element) {
	var definition = getDefinition(element);
	return definition && definition.defaultControl;
}

module.exports.getDefaultControl = getDefaultControl;

function getItemControl(element) {
	element = getBusinessObject(element);
	return element && element.itemControl;
}

module.exports.getItemControl = getItemControl;

function getRule(element, rule) {
	var itemControl = getItemControl(element),
			defaultControl = getDefaultControl(element);

	if (itemControl && itemControl[rule]) {
		return itemControl[rule];
	}

	return defaultControl && defaultControl[rule];
}

function isRequired(element) {
	return !!getRule(element, 'requiredRule');
}

module.exports.isRequired = isRequired;

function isRepeatable(element) {
	return !!getRule(element, 'repetitionRule');
}

module.exports.isRepeatable = isRepeatable;

function isManualActivation(element) {
	return !!getRule(element, 'manualActivationRule');
}

module.exports.isManualActivation = isManualActivation;

function isAutoComplete(element) {
	element = getBusinessObject(element);
	var definition = getDefinition(element);
	return element.autoComplete || definition && definition.autoComplete;
}

module.exports.isAutoComplete = isAutoComplete;

function hasPlanningTable(element) {
	element = getBusinessObject(element);
	return element.planningTable || getDefinition(element) && getDefinition(element).planningTable;
}

module.exports.hasPlanningTable = hasPlanningTable;

function getName(element) {
	element = getBusinessObject(element);

	if (is(element, 'cmmndi:CMMNEdge') && element.cmmnElementRef) {
		element = element.cmmnElementRef;
	}

	var name = element.name;
	if (!name) {

		if (element.definitionRef) {
			name = element.definitionRef.name;
		}
	}

	return name;
}

module.exports.getName = getName;

/**
 * Returns the referenced sentry, if present.
 *
 * @param {djs.model.Base} criterion
 *
 * @result {ModdleElement} referenced sentry
 */
function getSentry(element) {
	var bo = getBusinessObject(element);

	if (is(bo, 'cmmn:Sentry')) {
		return bo;
	}

	return bo && bo.sentryRef;
}

module.exports.getSentry = getSentry;

function getStandardEvent(element) {
	element = getBusinessObject(element);
	return element.cmmnElementRef && element.cmmnElementRef.standardEvent;
}

module.exports.getStandardEvent = getStandardEvent;

function getStandardEvents(element) {

	if (is(element, 'cmmndi:CMMNEdge')) {
		element = getBusinessObject(element).cmmnElementRef;
	}

	if (is(element, 'cmmn:OnPart')) {

		if (is(element.exitCriterionRef, 'cmmn:ExitCriterion')) {
			return ['exit'];
		}

		return getTransitions(element.sourceRef);
	}

	return [];
}

module.exports.getStandardEvents = getStandardEvents;

function getTransitions(element) {

	element = getBusinessObject(element);

	if (is(element, 'cmmn:CaseFileItem')) {

		return ['addChild', 'addReference', 'create', 'delete', 'removeChild', 'removeReference', 'replace', 'update'];
	}

	if (is(element, 'cmmn:PlanItem') || is(element, 'cmmn:DiscretionaryItem')) {

		var definition = getDefinition(element);

		if (is(definition, 'cmmn:EventListener') || is(definition, 'cmmn:Milestone')) {

			return ['create', 'occur', 'resume', 'suspend', 'terminate'];
		}

		return ['complete', 'create', 'disable', 'enable', 'exit', 'fault', 'manualStart', 'parentResume', 'parentSuspend', 'reactivate', 'reenable', 'resume', 'start', 'suspend', 'terminate'];
	}

	if (isCasePlanModel(element)) {

		return ['close', 'complete', 'create', 'fault', 'reactivate', 'suspend', 'terminate'];
	}

	return [];
}

module.exports.getTransitions = getTransitions;

},{}],16:[function(_dereq_,module,exports){
/**
 * This file must not be changed or exchanged.
 *
 * @see http://bpmn.io/license for more information.
 */

'use strict';

var domify = _dereq_(60).domify;

var domDelegate = _dereq_(60).delegate;

// inlined ../../resources/logo.svg
var BPMNIO_LOGO_SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 960"><path fill="#fff" d="M960 60v839c0 33-27 61-60 61H60c-33 0-60-27-60-60V60C0 27 27 0 60 0h839c34 0 61 27 61 60z"/><path fill="#52b415" d="M217 548a205 205 0 0 0-144 58 202 202 0 0 0-4 286 202 202 0 0 0 285 3 200 200 0 0 0 48-219 203 203 0 0 0-185-128zM752 6a206 206 0 0 0-192 285 206 206 0 0 0 269 111 207 207 0 0 0 111-260A204 204 0 0 0 752 6zM62 0A62 62 0 0 0 0 62v398l60 46a259 259 0 0 1 89-36c5-28 10-57 14-85l99 2 12 85a246 246 0 0 1 88 38l70-52 69 71-52 68c17 30 29 58 35 90l86 14-2 100-86 12a240 240 0 0 1-38 89l43 58h413c37 0 60-27 60-61V407a220 220 0 0 1-44 40l21 85-93 39-45-76a258 258 0 0 1-98 1l-45 76-94-39 22-85a298 298 0 0 1-70-69l-86 22-38-94 76-45a258 258 0 0 1-1-98l-76-45 40-94 85 22a271 271 0 0 1 41-47z"/></svg>';

var BPMNIO_LOGO_URL = 'data:image/svg+xml,' + encodeURIComponent(BPMNIO_LOGO_SVG);

var BPMNIO_IMG = '<img width="52" height="52" src="' + BPMNIO_LOGO_URL + '" />';

function css(attrs) {
	return attrs.join(';');
}

var LIGHTBOX_STYLES = css([
	'z-index: 1001',
	'position: fixed',
	'top: 0',
	'left: 0',
	'right: 0',
	'bottom: 0'
]);

var BACKDROP_STYLES = css([
	'width: 100%',
	'height: 100%',
	'background: rgba(0,0,0,0.2)'
]);

var NOTICE_STYLES = css([
	'position: absolute',
	'left: 50%',
	'top: 40%',
	'margin: 0 -130px',
	'width: 260px',
	'padding: 10px',
	'background: #FFFFFF',
	'border: solid 1px #AAAAAA',
	'border-radius: 3px',
	'font-size: 14px',
	'line-height: 16px'
]);

var LIGHTBOX_MARKUP =
	'<div class="bjs-powered-by-lightbox" style="' + LIGHTBOX_STYLES + '">' +
		'<div class="backdrop" style="' + BACKDROP_STYLES + '"></div>' +
		'<div class="notice" style="' + NOTICE_STYLES + '">' +
			'<span style="float: left; margin-right: 10px">' +
				BPMNIO_IMG +
			'</span>' +
			'Web-based tooling for BPMN, DMN and CMMN diagrams ' +
			'powered by <a href="http://bpmn.io" target="_blank">bpmn.io</a>.' +
		'</div>' +
	'</div>';

var lightbox;

function open() {

	if (!lightbox) {
		lightbox = domify(LIGHTBOX_MARKUP);

		domDelegate.bind(lightbox, '.backdrop', 'click', function (event) {
			document.body.removeChild(lightbox);
		});
	}

	document.body.appendChild(lightbox);
}

module.exports.open = open;

module.exports.BPMNIO_IMG = BPMNIO_IMG;

},{"60":60}],17:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _simple = _dereq_(19);

Object.defineProperty(exports, 'default', {
	enumerable: true,
	get: function get() {
		return _interopRequireDefault(_simple).default;
	}
});

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

},{"19":19}],18:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = CmmnModdle;

var _minDash = _dereq_(59);

var _moddle = _dereq_(65);

var _moddle2 = _interopRequireDefault(_moddle);

var _moddleXml = _dereq_(61);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * A sub class of {@link Moddle} with support for import and export of CMMN 1.1 xml files.
 *
 * @class CmmnModdle
 * @extends Moddle
 *
 * @param {Object|Array} packages to use for instantiating the model
 * @param {Object} [options] additional options to pass over
 */
function CmmnModdle(packages, options) {
	_moddle2.default.call(this, packages, options);
}

CmmnModdle.prototype = Object.create(_moddle2.default.prototype);

/**
 * Instantiates a CMMN model tree from a given xml string.
 *
 * @param {String}   xmlStr
 * @param {String}   [typeName='cmmn:Definitions'] name of the root element
 * @param {Object}   [options]  options to pass to the underlying reader
 * @param {Function} done       callback that is invoked with (err, result, parseContext)
 *                              once the import completes
 */
CmmnModdle.prototype.fromXML = function (xmlStr, typeName, options, done) {

	if (!(0, _minDash.isString)(typeName)) {
		done = options;
		options = typeName;
		typeName = 'cmmn:Definitions';
	}

	if ((0, _minDash.isFunction)(options)) {
		done = options;
		options = {};
	}

	var reader = new _moddleXml.Reader((0, _minDash.assign)({ model: this, lax: true }, options));
	var rootHandler = reader.handler(typeName);

	reader.fromXML(xmlStr, rootHandler, done);
};

/**
 * Serializes a CMMN 1.1 object tree to XML.
 *
 * @param {String}   element    the root element, typically an instance of `cmmn:Definitions`
 * @param {Object}   [options]  to pass to the underlying writer
 * @param {Function} done       callback invoked with (err, xmlStr) once the import completes
 */
CmmnModdle.prototype.toXML = function (element, options, done) {

	if ((0, _minDash.isFunction)(options)) {
		done = options;
		options = {};
	}

	var writer = new _moddleXml.Writer(options);

	var result;
	var err;

	try {
		result = writer.toXML(element);
	} catch (e) {
		err = e;
	}

	return done(err, result);
};

},{"59":59,"61":61,"65":65}],19:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

exports.default = function (additionalPackages, options) {
	var pks = (0, _minDash.assign)({}, packages, additionalPackages);

	return new _cmmnModdle2.default(pks, options);
};

var _minDash = _dereq_(59);

var _cmmnModdle = _dereq_(18);

var _cmmnModdle2 = _interopRequireDefault(_cmmnModdle);

var _cmmn = _dereq_(20);

var _cmmn2 = _interopRequireDefault(_cmmn);

var _cmmndi = _dereq_(21);

var _cmmndi2 = _interopRequireDefault(_cmmndi);

var _dc = _dereq_(22);

var _dc2 = _interopRequireDefault(_dc);

var _di = _dereq_(23);

var _di2 = _interopRequireDefault(_di);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var packages = {
	cmmn: _cmmn2.default,
	cmmndi: _cmmndi2.default,
	dc: _dc2.default,
	di: _di2.default
};

},{"18":18,"20":20,"21":21,"22":22,"23":23,"59":59}],20:[function(_dereq_,module,exports){
module.exports={
	"name": "CMMN",
	"uri": "http://www.omg.org/spec/CMMN/20151109/MODEL",
	"types": [
		{
			"name": "ApplicabilityRule",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "condition",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "contextRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "Artifact",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			]
		},
		{
			"name": "Association",
			"superClass": [
				"Artifact"
			],
			"properties": [
				{
					"name": "associationDirection",
					"type": "AssociationDirection",
					"isAttr": true
				},
				{
					"name": "sourceRef",
					"type": "CMMNElement",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "targetRef",
					"type": "CMMNElement",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "TextAnnotation",
			"superClass": [
				"Artifact"
			],
			"properties": [
				{
					"name": "text",
					"type": "String"
				},
				{
					"name": "textFormat",
					"default": "text/plain",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "ManualActivationRule",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "condition",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "contextRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "Case",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "caseFileModel",
					"type": "CaseFile",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "casePlanModel",
					"type": "Stage",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "caseRoles",
					"type": "CaseRoles"
				},
				{
					"name": "input",
					"type": "CaseParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "output",
					"type": "CaseParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "CaseFile",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "caseFileItems",
					"type": "CaseFileItem",
					"isMany": true
				}
			]
		},
		{
			"name": "CaseFileItem",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "multiplicity",
					"type": "String",
					"isAttr": true,
					"default": "Unspecified"
				},
				{
					"name": "definitionRef",
					"type": "CaseFileItemDefinition",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "sourceRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "targetRefs",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true,
					"isMany": true
				},
				{
					"name": "children",
					"type": "Children",
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "CaseFileItemDefinition",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "definitionType",
					"type": "String",
					"default": "http://www.omg.org/spec/CMMN/DefinitionType/Unspecified",
					"isAttr": true
				},
				{
					"name": "properties",
					"type": "Property",
					"isMany": true
				},
				{
					"name": "structureRef",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "importRef",
					"type": "Import",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "CaseFileItemOnPart",
			"superClass": [
				"OnPart"
			],
			"properties": [
				{
					"name": "standardEvent",
					"type": "String"
				},
				{
					"name": "sourceRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "CaseParameter",
			"superClass": [
				"Parameter"
			],
			"properties": [
				{
					"name": "bindingRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "bindingRefinement",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "CaseRoles",
			"superClass": [
				"Parameter"
			],
			"properties": [
				{
					"name": "role",
					"type": "Role",
					"isMany": true
				}
			]
		},
		{
			"name": "CaseTask",
			"superClass": [
				"Task"
			],
			"properties": [
				{
					"name": "parameterMapping",
					"type": "ParameterMapping",
					"isMany": true
				},
				{
					"name": "caseRefExpression",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "caseRef",
					"type": "String",
					"isAttr": true
				}
			]
		},
		{
			"name": "Children",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "caseFileItems",
					"type": "CaseFileItem",
					"isMany": true
				}
			]
		},
		{
			"name": "CMMNElement",
			"isAbstract": true,
			"properties": [
				{
					"name": "id",
					"isAttr": true,
					"type": "String",
					"isId": true
				},
				{
					"name": "documentation",
					"type": "Documentation",
					"isMany": true
				},
				{
					"name": "extensionElements",
					"type": "ExtensionElements"
				},
				{
					"name": "extensionDefinitions",
					"type": "ExtensionDefinition",
					"isReference": true,
					"isMany": true
				}
			]
		},
		{
			"name": "Definitions",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "targetNamespace",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "expressionLanguage",
					"type": "String",
					"isAttr": true,
					"default": "http://www.w3.org/1999/XPath"
				},
				{
					"name": "exporter",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "exporterVersion",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "author",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "creationDate",
					"type": "DateTime",
					"isAttr": true
				},
				{
					"name": "imports",
					"type": "Import",
					"isMany": true
				},
				{
					"name": "caseFileItemDefinitions",
					"type": "CaseFileItemDefinition",
					"isMany": true
				},
				{
					"name": "cases",
					"type": "Case",
					"isMany": true
				},
				{
					"name": "processes",
					"type": "Process",
					"isMany": true
				},
				{
					"name": "decisions",
					"type": "Decision",
					"isMany": true
				},
				{
					"name": "extensions",
					"type": "Extension",
					"isMany": true
				},
				{
					"name": "relationships",
					"type": "Relationship",
					"isMany": true
				},
				{
					"name": "artifacts",
					"type": "Artifact",
					"isMany": true
				},
				{
					"name": "CMMNDI",
					"type": "cmmndi:CMMNDI"
				}
			]
		},
		{
			"name": "DiscretionaryItem",
			"superClass": [
				"TableItem"
			],
			"properties": [
				{
					"name": "itemControl",
					"type": "PlanItemControl",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "definitionRef",
					"type": "PlanItemDefinition",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "entryCriteria",
					"type": "EntryCriterion",
					"isMany": true
				},
				{
					"name": "exitCriteria",
					"type": "ExitCriterion",
					"isMany": true
				}
			]
		},
		{
			"name": "EventListener",
			"superClass": [
				"PlanItemDefinition"
			]
		},
		{
			"name": "Expression",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "language",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "body",
					"isBody": true,
					"type": "String"
				}
			]
		},
		{
			"name": "HumanTask",
			"superClass": [
				"Task"
			],
			"properties": [
				{
					"name": "planningTable",
					"type": "PlanningTable"
				},
				{
					"name": "performerRef",
					"type": "Role",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "IfPart",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "contextRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "condition",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "Import",
			"properties": [
				{
					"name": "location",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "namespace",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "importType",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "Milestone",
			"superClass": [
				"PlanItemDefinition"
			]
		},
		{
			"name": "On",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			]
		},
		{
			"name": "OnPart",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "Parameter",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "ParameterMapping",
			"properties": [
				{
					"name": "sourceRef",
					"type": "Parameter",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "targetRef",
					"type": "Parameter",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "transformation",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "PlanFragment",
			"superClass": [
				"PlanItemDefinition"
			],
			"properties": [
				{
					"name": "planItems",
					"type": "PlanItem",
					"isMany": true
				},
				{
					"name": "sentries",
					"type": "Sentry",
					"isMany": true
				}
			]
		},
		{
			"name": "PlanItem",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "definitionRef",
					"type": "PlanItemDefinition",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "itemControl",
					"type": "PlanItemControl",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "entryCriteria",
					"type": "EntryCriterion",
					"isMany": true
				},
				{
					"name": "exitCriteria",
					"type": "ExitCriterion",
					"isMany": true
				}
			]
		},
		{
			"name": "PlanItemControl",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "repetitionRule",
					"type": "RepetitionRule"
				},
				{
					"name": "requiredRule",
					"type": "RequiredRule"
				},
				{
					"name": "manualActivationRule",
					"type": "ManualActivationRule"
				}
			]
		},
		{
			"name": "PlanItemDefinition",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "defaultControl",
					"type": "PlanItemControl",
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "PlanItemOnPart",
			"superClass": [
				"OnPart"
			],
			"properties": [
				{
					"name": "standardEvent",
					"type": "String"
				},
				{
					"name": "sourceRef",
					"type": "PlanItem",
					"isAttr": true,
					"isReference": true
				},
				{
					"name": "exitCriterionRef",
					"type": "ExitCriterion",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "PlanningTable",
			"superClass": [
				"TableItem"
			],
			"properties": [
				{
					"name": "tableItems",
					"type": "TableItem",
					"isMany": true
				},
				{
					"name": "applicabilityRules",
					"type": "ApplicabilityRule",
					"isMany": true
				}
			]
		},
		{
			"name": "Process",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "implementationType",
					"type": "String",
					"isAttr": true,
					"default": "http://www.omg.org/spec/CMMN/ProcessType/Unspecified"
				},
				{
					"name": "externalRef",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "input",
					"type": "ProcessParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "output",
					"type": "ProcessParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "ProcessParameter",
			"superClass": [
				"Parameter"
			]
		},
		{
			"name": "ProcessTask",
			"superClass": [
				"Task"
			],
			"properties": [
				{
					"name": "parameterMapping",
					"type": "ParameterMapping",
					"isMany": true
				},
				{
					"name": "processRefExpression",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "processRef",
					"type": "String",
					"isAttr": true
				}
			]
		},
		{
			"name": "Property",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "type",
					"type": "String",
					"isAttr": true,
					"default": "http://www.omg.org/spec/CMMN/PropertyType/Unspecified"
				}
			]
		},
		{
			"name": "RepetitionRule",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "condition",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "contextRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "RequiredRule",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "condition",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "contextRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "Role",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "Sentry",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "onParts",
					"type": "OnPart",
					"isMany": true
				},
				{
					"name": "ifPart",
					"type": "IfPart"
				},
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "Stage",
			"superClass": [
				"PlanFragment"
			],
			"properties": [
				{
					"name": "planningTable",
					"type": "PlanningTable"
				},
				{
					"name": "planItemDefinitions",
					"type": "PlanItemDefinition",
					"isMany": true
				},
				{
					"name": "autoComplete",
					"isAttr": true,
					"type": "Boolean"
				},
				{
					"name": "exitCriteria",
					"type": "ExitCriterion",
					"isMany": true
				}
			]
		},
		{
			"name": "TableItem",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "authorizedRoleRefs",
					"type": "Role",
					"isAttr": true,
					"isReference": true,
					"isMany": true
				},
				{
					"name": "applicabilityRuleRefs",
					"type": "ApplicabilityRule",
					"isAttr": true,
					"isReference": true,
					"isMany": true
				}
			]
		},
		{
			"name": "Task",
			"superClass": [
				"PlanItemDefinition"
			],
			"properties": [
				{
					"name": "input",
					"type": "CaseParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "output",
					"type": "CaseParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "isBlocking",
					"isAttr": true,
					"default": true,
					"type": "Boolean"
				}
			]
		},
		{
			"name": "TimerEventListener",
			"superClass": [
				"EventListener"
			],
			"properties": [
				{
					"name": "timerExpression",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "timerStart",
					"type": "StartTrigger"
				}
			]
		},
		{
			"name": "UserEventListener",
			"superClass": [
				"EventListener"
			],
			"properties": [
				{
					"name": "authorizedRoleRefs",
					"type": "Role",
					"isMany": true,
					"isAttr": true
				}
			]
		},
		{
			"name": "DateTime",
			"superClass": []
		},
		{
			"name": "StartTrigger",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			]
		},
		{
			"name": "PlanItemStartTrigger",
			"superClass": [
				"StartTrigger"
			],
			"properties": [
				{
					"name": "standardEvent",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "sourceRef",
					"type": "PlanItem",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "CaseFileItemStartTrigger",
			"superClass": [
				"StartTrigger"
			],
			"properties": [
				{
					"name": "standardEvent",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "sourceRef",
					"type": "CaseFileItem",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "Extension",
			"properties": [
				{
					"name": "mustUnderstand",
					"default": false,
					"isAttr": true,
					"type": "Boolean"
				},
				{
					"name": "definition",
					"type": "ExtensionDefinition"
				}
			]
		},
		{
			"name": "ExtensionDefinition",
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "extensionAttributeDefinitions",
					"type": "ExtensionAttributeDefinition",
					"isMany": true
				}
			]
		},
		{
			"name": "ExtensionAttributeDefinition",
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "type",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "isReference",
					"isAttr": true,
					"default": false,
					"type": "Boolean"
				}
			]
		},
		{
			"name": "ExtensionElements",
			"properties": [
				{
					"name": "valueRef",
					"isAttr": true,
					"isReference": true,
					"type": "Element"
				},
				{
					"name": "values",
					"type": "Element",
					"isMany": true
				},
				{
					"name": "extensionAttributeDefinition",
					"type": "ExtensionAttributeDefinition",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "Relationship",
			"properties": [
				{
					"name": "type",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "direction",
					"type": "RelationshipDirection",
					"isAttr": true
				},
				{
					"name": "source",
					"isMany": true,
					"type": "Element"
				},
				{
					"name": "target",
					"isMany": true,
					"type": "Element"
				}
			]
		},
		{
			"name": "Documentation",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "text",
					"type": "String",
					"isBody": true
				},
				{
					"name": "textFormat",
					"default": "text/plain",
					"isAttr": true,
					"type": "String"
				}
			]
		},
		{
			"name": "DecisionTask",
			"superClass": [
				"Task"
			],
			"properties": [
				{
					"name": "mappings",
					"type": "ParameterMapping",
					"isMany": true
				},
				{
					"name": "decisionRef",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "decisionRefExpression",
					"type": "Expression",
					"xml": {
						"serialize": "property"
					}
				}
			]
		},
		{
			"name": "Decision",
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "implementationType",
					"type": "String",
					"isAttr": true,
					"default": "http://www.omg.org/spec/CMMN/DecisionType/Unspecified"
				},
				{
					"name": "input",
					"type": "DecisionParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "output",
					"type": "DecisionParameter",
					"isMany": true,
					"xml": {
						"serialize": "property"
					}
				},
				{
					"name": "externalRef",
					"type": "String",
					"isAttr": true
				}
			]
		},
		{
			"name": "DecisionParameter",
			"superClass": [
				"Parameter"
			]
		},
		{
			"name": "Criterion",
			"isAbstract": true,
			"superClass": [
				"CMMNElement"
			],
			"properties": [
				{
					"name": "name",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "sentryRef",
					"type": "Sentry",
					"isAttr": true,
					"isReference": true
				}
			]
		},
		{
			"name": "EntryCriterion",
			"superClass": [
				"Criterion"
			]
		},
		{
			"name": "ExitCriterion",
			"superClass": [
				"Criterion"
			]
		}
	],
	"emumerations": [
		{
			"name": "AssociationDirection",
			"literalValues": [
				{
					"name": "None"
				},
				{
					"name": "One"
				},
				{
					"name": "Both"
				}
			]
		},
		{
			"name": "CaseFileItemTransition",
			"literalValues": [
				{
					"name": "addChild"
				},
				{
					"name": "addReference"
				},
				{
					"name": "create"
				},
				{
					"name": "delete"
				},
				{
					"name": "removeChild"
				},
				{
					"name": "removeReference"
				},
				{
					"name": "replace"
				},
				{
					"name": "update"
				}
			]
		},
		{
			"name": "MultiplicityEnum",
			"literalValues": [
				{
					"name": "ZeroOrOne"
				},
				{
					"name": "ZeroOrMore"
				},
				{
					"name": "ExactlyOne"
				},
				{
					"name": "OneOrMore"
				},
				{
					"name": "Unspecified"
				},
				{
					"name": "Unknown"
				}
			]
		},
		{
			"name": "PlanItemTransition",
			"literalValues": [
				{
					"name": "close"
				},
				{
					"name": "complete"
				},
				{
					"name": "create"
				},
				{
					"name": "disable"
				},
				{
					"name": "enable"
				},
				{
					"name": "exit"
				},
				{
					"name": "fault"
				},
				{
					"name": "manualStart"
				},
				{
					"name": "occur"
				},
				{
					"name": "parentResume"
				},
				{
					"name": "parentSuspend"
				},
				{
					"name": "reactivate"
				},
				{
					"name": "reenable"
				},
				{
					"name": "resume"
				},
				{
					"name": "start"
				},
				{
					"name": "suspend"
				},
				{
					"name": "terminate"
				}
			]
		},
		{
			"name": "RelationshipDirection",
			"literalValues": [
				{
					"name": "None"
				},
				{
					"name": "Forward"
				},
				{
					"name": "Backward"
				},
				{
					"name": "Both"
				}
			]
		}
	],
	"associations": [],
	"xml": {
		"tagAlias": "lowerCase",
		"typePrefix": "t"
	},
	"prefix": "cmmn"
}
},{}],21:[function(_dereq_,module,exports){
module.exports={
	"name": "CMMNDI",
	"uri": "http://www.omg.org/spec/CMMN/20151109/CMMNDI",
	"types": [
		{
			"name": "CMMNDI",
			"properties": [
				{
					"name": "diagrams",
					"type": "CMMNDiagram",
					"isMany": true
				},
				{
					"name": "styles",
					"type": "CMMNStyle",
					"isMany": true
				}
			]
		},

		{
			"name": "CMMNDiagram",
			"properties": [
				{
					"name": "cmmnElementRef",
					"isAttr": true,
					"type": "cmmn:CMMNElement",
					"isReference": true
				},
				{
					"name": "Size",
					"type": "dc:Dimension",
					"xml": {
						"serialize": "xsi:type"
					}
				},
				{
					"name": "diagramElements",
					"type": "CMMNDiagramElement",
					"isMany": true
				}
			],
			"superClass": [
				"di:Diagram"
			]
		},

		{
			"name": "CMMNDiagramElement",
			"superClass": [
				"di:DiagramElement"
			]
		},

		{
			"name": "CMMNShape",
			"properties": [
				{
					"name": "cmmnElementRef",
					"isAttr": true,
					"isReference": true,
					"type": "cmmn:CMMNElement"
				},
				{
					"name": "label",
					"type": "CMMNLabel"
				},
				{
					"name": "isCollapsed",
					"isAttr": true,
					"type": "Boolean"
				},
				{
					"name": "isPlanningTableCollapsed",
					"isAttr": true,
					"type": "Boolean"
				}
			],
			"superClass": [
				"CMMNDiagramElement", "di:Shape"
			]
		},
		{
			"name": "CMMNEdge",
			"properties": [
				{
					"name": "label",
					"type": "CMMNLabel"
				},
				{
					"name": "cmmnElementRef",
					"isAttr": true,
					"isReference": true,
					"type": "cmmn:CMMNElement"
				},
				{
					"name": "sourceCMMNElementRef",
					"isAttr": true,
					"isReference": true,
					"type": "cmmn:CMMNElement"
				},
				{
					"name": "targetCMMNElementRef",
					"isAttr": true,
					"isReference": true,
					"type": "cmmn:CMMNElement"
				},
				{
					"name": "isStandardEventVisible",
					"type": "Boolean",
					"isAttr": true
				}
			],
			"superClass": [
				"CMMNDiagramElement", "di:Edge"
			]
		},
		{
			"name": "CMMNLabel",
			"superClass": [
				"di:Shape"
			]
		},
		{
			"name": "CMMNStyle",
			"properties": [
				{
					"name": "FillColor",
					"type": "dc:Color",
					"xml": {
						"serialize": "xsi:type"
					}
				},
				{
					"name": "StrokeColor",
					"type": "dc:Color",
					"xml": {
						"serialize": "xsi:type"
					}
				},
				{
					"name": "FontColor",
					"type": "dc:Color",
					"xml": {
						"serialize": "xsi:type"
					}
				},
				{
					"name": "fontFamily",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "fontSize",
					"type": "Real",
					"isAttr": true
				},
				{
					"name": "fontItalic",
					"type": "Boolean",
					"isAttr": true
				},
				{
					"name": "fontBold",
					"type": "Boolean",
					"isAttr": true
				},
				{
					"name": "fontUnderline",
					"type": "Boolean",
					"isAttr": true
				},
				{
					"name": "fontStrikeThrough",
					"type": "Boolean",
					"isAttr": true
				}
			],
			"superClass": [
				"di:Style"
			]
		}
	],
	"associations": [],
	"prefix": "cmmndi"
}
},{}],22:[function(_dereq_,module,exports){
module.exports={
	"name": "DC",
	"uri": "http://www.omg.org/spec/CMMN/20151109/DC",
	"types": [
		{
			"name": "rgb"
		},
		{
			"name": "Real"
		},
		{
			"name": "Color",
			"properties" : [
				{
					"name": "red",
					"type": "rgb",
					"isAttr": true
				},
				{
					"name": "green",
					"type": "rgb",
					"isAttr": true
				},
				{
					"name": "blue",
					"type": "rgb",
					"isAttr": true
				}
			]
		},

		{
			"name": "Point",
			"properties" : [
				{
					"name": "x",
					"type": "Real",
					"isAttr": true
				},
				{
					"name": "y",
					"type": "Real",
					"isAttr": true
				}
			]
		},

		{
			"name": "Dimension",
			"properties" : [
				{
					"name": "width",
					"type": "Real",
					"isAttr": true
				},
				{
					"name": "height",
					"type": "Real",
					"isAttr": true
				}
			]
		},

		{
			"name": "Bounds",
			"properties" : [
				{
					"name": "x",
					"type": "Real",
					"isAttr": true
				},
				{
					"name": "y",
					"type": "Real",
					"isAttr": true
				},
				{
					"name": "width",
					"type": "Real",
					"isAttr": true
				},
				{
					"name": "height",
					"type": "Real",
					"isAttr": true
				}
			]
		}
	],
	"prefix": "dc",
	"associations": []
}
},{}],23:[function(_dereq_,module,exports){
module.exports={
	"name": "DI",
	"uri": "http://www.omg.org/spec/CMMN/20151109/DI",
	"types": [
		{
			"name": "DiagramElement",
			"isAbstract": true,
			"properties": [
				{
					"name": "extension",
					"type": "Extension"
				},
				{
					"name": "style",
					"type": "Style"
				},
				{
					"name": "sharedStyle",
					"type": "Style",
					"isReference": true,
					"isAttr": true
				},
				{
					"name": "id",
					"type": "String",
					"isAttr": true,
					"isId": true
				}
			]
		},

		{
			"name": "Edge",
			"isAbstract": true,
			"superClass": [
				"DiagramElement"
			],
			"properties": [
				{
					"name": "waypoint",
					"isMany": true,
					"type": "dc:Point",
					"xml": {
						"serialize": "xsi:type"
					}
				}
			]
		},
		{
			"name": "Diagram",
			"isAbstract": true,
			"superClass": [
				"DiagramElement"
			],
			"properties": [
				{
					"name": "name",
					"type": "String",
					"isAttr": true
				},
				{
					"name": "documentation",
					"isAttr": true,
					"type": "String"
				},
				{
					"name": "resolution",
					"isAttr": true,
					"default": 300,
					"type": "double"
				}
			]
		},
		{
			"name": "Shape",
			"isAbstract": true,
			"superClass": [
				"DiagramElement"
			],
			"properties": [
				{
					"name": "bounds",
					"type": "dc:Bounds"
				}
			]
		},

		{
			"name": "Style",
			"isAbstract": true,
			"properties": [
				{
					"name": "extension",
					"type": "Extension"
				},
				{
					"name": "id",
					"type": "String",
					"isAttr": true,
					"isId": true
				}
			]
		},

		{
			"name": "Extension",
			"properties": [
				{
					"name": "values",
					"type": "Element",
					"isMany": true
				}
			]
		}
	],
	"associations": [],
	"prefix": "di",
	"xml": {
		"tagAlias": "lowerCase"
	}
}
},{}],24:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _Diagram = _dereq_(25);

Object.defineProperty(exports, 'default', {
	enumerable: true,
	get: function get() {
		return _interopRequireDefault(_Diagram).default;
	}
});

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

},{"25":25}],25:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Diagram;

var _didi = _dereq_(57);

var _core = _dereq_(31);

var _core2 = _interopRequireDefault(_core);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Bootstrap an injector from a list of modules, instantiating a number of default components
 *
 * @ignore
 * @param {Array<didi.Module>} bootstrapModules
 *
 * @return {didi.Injector} a injector to use to access the components
 */
function bootstrap(bootstrapModules) {

	var modules = [],
			components = [];

	function hasModule(m) {
		return modules.indexOf(m) >= 0;
	}

	function addModule(m) {
		modules.push(m);
	}

	function visit(m) {
		if (hasModule(m)) {
			return;
		}

		(m.__depends__ || []).forEach(visit);

		if (hasModule(m)) {
			return;
		}

		addModule(m);

		(m.__init__ || []).forEach(function (c) {
			components.push(c);
		});
	}

	bootstrapModules.forEach(visit);

	var injector = new _didi.Injector(modules);

	components.forEach(function (c) {

		try {
			// eagerly resolve component (fn or string)
			injector[typeof c === 'string' ? 'get' : 'invoke'](c);
		} catch (e) {
			console.error('Failed to instantiate component');
			console.error(e.stack);

			throw e;
		}
	});

	return injector;
}

/**
 * Creates an injector from passed options.
 *
 * @ignore
 * @param  {Object} options
 * @return {didi.Injector}
 */
function createInjector(options) {

	options = options || {};

	var configModule = {
		'config': ['value', options]
	};

	var modules = [configModule, _core2.default].concat(options.modules || []);

	return bootstrap(modules);
}

/**
 * The main diagram-js entry point that bootstraps the diagram with the given
 * configuration.
 *
 * To register extensions with the diagram, pass them as Array<didi.Module> to the constructor.
 *
 * @class djs.Diagram
 * @memberOf djs
 * @constructor
 *
 * @example
 *
 * <caption>Creating a plug-in that logs whenever a shape is added to the canvas.</caption>
 *
 * // plug-in implemenentation
 * function MyLoggingPlugin(eventBus) {
 *   eventBus.on('shape.added', function(event) {
 *     console.log('shape ', event.shape, ' was added to the diagram');
 *   });
 * }
 *
 * // export as module
 * export default {
 *   __init__: [ 'myLoggingPlugin' ],
 *     myLoggingPlugin: [ 'type', MyLoggingPlugin ]
 * };
 *
 *
 * // instantiate the diagram with the new plug-in
 *
 * import MyLoggingModule from 'path-to-my-logging-plugin';
 *
 * var diagram = new Diagram({
 *   modules: [
 *     MyLoggingModule
 *   ]
 * });
 *
 * diagram.invoke([ 'canvas', function(canvas) {
 *   // add shape to drawing canvas
 *   canvas.addShape({ x: 10, y: 10 });
 * });
 *
 * // 'shape ... was added to the diagram' logged to console
 *
 * @param {Object} options
 * @param {Array<didi.Module>} [options.modules] external modules to instantiate with the diagram
 * @param {didi.Injector} [injector] an (optional) injector to bootstrap the diagram with
 */
function Diagram(options, injector) {

	// create injector unless explicitly specified
	this.injector = injector = injector || createInjector(options);

	// API

	/**
	 * Resolves a diagram service
	 *
	 * @method Diagram#get
	 *
	 * @param {String} name the name of the diagram service to be retrieved
	 * @param {Boolean} [strict=true] if false, resolve missing services to null
	 */
	this.get = injector.get;

	/**
	 * Executes a function into which diagram services are injected
	 *
	 * @method Diagram#invoke
	 *
	 * @param {Function|Object[]} fn the function to resolve
	 * @param {Object} locals a number of locals to use to resolve certain dependencies
	 */
	this.invoke = injector.invoke;

	// init

	// indicate via event


	/**
	 * An event indicating that all plug-ins are loaded.
	 *
	 * Use this event to fire other events to interested plug-ins
	 *
	 * @memberOf Diagram
	 *
	 * @event diagram.init
	 *
	 * @example
	 *
	 * eventBus.on('diagram.init', function() {
	 *   eventBus.fire('my-custom-event', { foo: 'BAR' });
	 * });
	 *
	 * @type {Object}
	 */
	this.get('eventBus').fire('diagram.init');
}

/**
 * Destroys the diagram
 *
 * @method  Diagram#destroy
 */
Diagram.prototype.destroy = function () {
	this.get('eventBus').fire('diagram.destroy');
};

/**
 * Clear the diagram, removing all contents.
 */
Diagram.prototype.clear = function () {
	this.get('eventBus').fire('diagram.clear');
};

},{"31":31,"57":57}],26:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

exports.default = Canvas;

var _minDash = _dereq_(59);

var _Collections = _dereq_(47);

var _Elements = _dereq_(48);

var _tinySvg = _dereq_(79);

function round(number, resolution) {
	return Math.round(number * resolution) / resolution;
}

function ensurePx(number) {
	return (0, _minDash.isNumber)(number) ? number + 'px' : number;
}

/**
 * Creates a HTML container element for a SVG element with
 * the given configuration
 *
 * @param  {Object} options
 * @return {HTMLElement} the container element
 */
function createContainer(options) {

	options = (0, _minDash.assign)({}, { width: '100%', height: '100%' }, options);

	var container = options.container || document.body;

	// create a <div> around the svg element with the respective size
	// this way we can always get the correct container size
	// (this is impossible for <svg> elements at the moment)
	var parent = document.createElement('div');
	parent.setAttribute('class', 'djs-container');

	(0, _minDash.assign)(parent.style, {
		position: 'relative',
		overflow: 'hidden',
		width: ensurePx(options.width),
		height: ensurePx(options.height)
	});

	container.appendChild(parent);

	return parent;
}

function createGroup(parent, cls, childIndex) {
	var group = (0, _tinySvg.create)('g');
	(0, _tinySvg.classes)(group).add(cls);

	var index = childIndex !== undefined ? childIndex : parent.childNodes.length - 1;

	// must ensure second argument is node or _null_
	// cf. https://developer.mozilla.org/en-US/docs/Web/API/Node/insertBefore
	parent.insertBefore(group, parent.childNodes[index] || null);

	return group;
}

var BASE_LAYER = 'base';

var REQUIRED_MODEL_ATTRS = {
	shape: ['x', 'y', 'width', 'height'],
	connection: ['waypoints']
};

/**
 * The main drawing canvas.
 *
 * @class
 * @constructor
 *
 * @emits Canvas#canvas.init
 *
 * @param {Object} config
 * @param {EventBus} eventBus
 * @param {GraphicsFactory} graphicsFactory
 * @param {ElementRegistry} elementRegistry
 */
function Canvas(config, eventBus, graphicsFactory, elementRegistry) {

	this._eventBus = eventBus;
	this._elementRegistry = elementRegistry;
	this._graphicsFactory = graphicsFactory;

	this._init(config || {});
}

Canvas.$inject = ['config.canvas', 'eventBus', 'graphicsFactory', 'elementRegistry'];

Canvas.prototype._init = function (config) {

	var eventBus = this._eventBus;

	// Creates a <svg> element that is wrapped into a <div>.
	// This way we are always able to correctly figure out the size of the svg element
	// by querying the parent node.
	//
	// (It is not possible to get the size of a svg element cross browser @ 2014-04-01)
	//
	// <div class="djs-container" style="width: {desired-width}, height: {desired-height}">
	//   <svg width="100%" height="100%">
	//    ...
	//   </svg>
	// </div>

	// html container
	var container = this._container = createContainer(config);

	var svg = this._svg = (0, _tinySvg.create)('svg');
	(0, _tinySvg.attr)(svg, { width: '100%', height: '100%' });

	(0, _tinySvg.append)(container, svg);

	var viewport = this._viewport = createGroup(svg, 'viewport');

	this._layers = {};

	// debounce canvas.viewbox.changed events
	// for smoother diagram interaction
	if (config.deferUpdate !== false) {
		this._viewboxChanged = (0, _minDash.debounce)((0, _minDash.bind)(this._viewboxChanged, this), 300);
	}

	eventBus.on('diagram.init', function () {

		/**
		 * An event indicating that the canvas is ready to be drawn on.
		 *
		 * @memberOf Canvas
		 *
		 * @event canvas.init
		 *
		 * @type {Object}
		 * @property {SVGElement} svg the created svg element
		 * @property {SVGElement} viewport the direct parent of diagram elements and shapes
		 */
		eventBus.fire('canvas.init', {
			svg: svg,
			viewport: viewport
		});
	}, this);

	// reset viewbox on shape changes to
	// recompute the viewbox
	eventBus.on(['shape.added', 'connection.added', 'shape.removed', 'connection.removed', 'elements.changed'], function () {
		delete this._cachedViewbox;
	}, this);

	eventBus.on('diagram.destroy', 500, this._destroy, this);
	eventBus.on('diagram.clear', 500, this._clear, this);
};

Canvas.prototype._destroy = function (emit) {
	this._eventBus.fire('canvas.destroy', {
		svg: this._svg,
		viewport: this._viewport
	});

	var parent = this._container.parentNode;

	if (parent) {
		parent.removeChild(this._container);
	}

	delete this._svg;
	delete this._container;
	delete this._layers;
	delete this._rootElement;
	delete this._viewport;
};

Canvas.prototype._clear = function () {

	var self = this;

	var allElements = this._elementRegistry.getAll();

	// remove all elements
	allElements.forEach(function (element) {
		var type = (0, _Elements.getType)(element);

		if (type === 'root') {
			self.setRootElement(null, true);
		} else {
			self._removeElement(element, type);
		}
	});

	// force recomputation of view box
	delete this._cachedViewbox;
};

/**
 * Returns the default layer on which
 * all elements are drawn.
 *
 * @returns {SVGElement}
 */
Canvas.prototype.getDefaultLayer = function () {
	return this.getLayer(BASE_LAYER, 0);
};

/**
 * Returns a layer that is used to draw elements
 * or annotations on it.
 *
 * Non-existing layers retrieved through this method
 * will be created. During creation, the optional index
 * may be used to create layers below or above existing layers.
 * A layer with a certain index is always created above all
 * existing layers with the same index.
 *
 * @param {String} name
 * @param {Number} index
 *
 * @returns {SVGElement}
 */
Canvas.prototype.getLayer = function (name, index) {

	if (!name) {
		throw new Error('must specify a name');
	}

	var layer = this._layers[name];

	if (!layer) {
		layer = this._layers[name] = this._createLayer(name, index);
	}

	// throw an error if layer creation / retrival is
	// requested on different index
	if (typeof index !== 'undefined' && layer.index !== index) {
		throw new Error('layer <' + name + '> already created at index <' + index + '>');
	}

	return layer.group;
};

/**
 * Creates a given layer and returns it.
 *
 * @param {String} name
 * @param {Number} [index=0]
 *
 * @return {Object} layer descriptor with { index, group: SVGGroup }
 */
Canvas.prototype._createLayer = function (name, index) {

	if (!index) {
		index = 0;
	}

	var childIndex = (0, _minDash.reduce)(this._layers, function (childIndex, layer) {
		if (index >= layer.index) {
			childIndex++;
		}

		return childIndex;
	}, 0);

	return {
		group: createGroup(this._viewport, 'layer-' + name, childIndex),
		index: index
	};
};

/**
 * Returns the html element that encloses the
 * drawing canvas.
 *
 * @return {DOMNode}
 */
Canvas.prototype.getContainer = function () {
	return this._container;
};

// markers //////////////////////

Canvas.prototype._updateMarker = function (element, marker, add) {
	var container;

	if (!element.id) {
		element = this._elementRegistry.get(element);
	}

	// we need to access all
	container = this._elementRegistry._elements[element.id];

	if (!container) {
		return;
	}

	(0, _minDash.forEach)([container.gfx, container.secondaryGfx], function (gfx) {
		if (gfx) {
			// invoke either addClass or removeClass based on mode
			if (add) {
				(0, _tinySvg.classes)(gfx).add(marker);
			} else {
				(0, _tinySvg.classes)(gfx).remove(marker);
			}
		}
	});

	/**
	 * An event indicating that a marker has been updated for an element
	 *
	 * @event element.marker.update
	 * @type {Object}
	 * @property {djs.model.Element} element the shape
	 * @property {Object} gfx the graphical representation of the shape
	 * @property {String} marker
	 * @property {Boolean} add true if the marker was added, false if it got removed
	 */
	this._eventBus.fire('element.marker.update', { element: element, gfx: container.gfx, marker: marker, add: !!add });
};

/**
 * Adds a marker to an element (basically a css class).
 *
 * Fires the element.marker.update event, making it possible to
 * integrate extension into the marker life-cycle, too.
 *
 * @example
 * canvas.addMarker('foo', 'some-marker');
 *
 * var fooGfx = canvas.getGraphics('foo');
 *
 * fooGfx; // <g class="... some-marker"> ... </g>
 *
 * @param {String|djs.model.Base} element
 * @param {String} marker
 */
Canvas.prototype.addMarker = function (element, marker) {
	this._updateMarker(element, marker, true);
};

/**
 * Remove a marker from an element.
 *
 * Fires the element.marker.update event, making it possible to
 * integrate extension into the marker life-cycle, too.
 *
 * @param  {String|djs.model.Base} element
 * @param  {String} marker
 */
Canvas.prototype.removeMarker = function (element, marker) {
	this._updateMarker(element, marker, false);
};

/**
 * Check the existence of a marker on element.
 *
 * @param  {String|djs.model.Base} element
 * @param  {String} marker
 */
Canvas.prototype.hasMarker = function (element, marker) {
	if (!element.id) {
		element = this._elementRegistry.get(element);
	}

	var gfx = this.getGraphics(element);

	return (0, _tinySvg.classes)(gfx).has(marker);
};

/**
 * Toggles a marker on an element.
 *
 * Fires the element.marker.update event, making it possible to
 * integrate extension into the marker life-cycle, too.
 *
 * @param  {String|djs.model.Base} element
 * @param  {String} marker
 */
Canvas.prototype.toggleMarker = function (element, marker) {
	if (this.hasMarker(element, marker)) {
		this.removeMarker(element, marker);
	} else {
		this.addMarker(element, marker);
	}
};

Canvas.prototype.getRootElement = function () {
	if (!this._rootElement) {
		this.setRootElement({ id: '__implicitroot', children: [] });
	}

	return this._rootElement;
};

// root element handling //////////////////////

/**
 * Sets a given element as the new root element for the canvas
 * and returns the new root element.
 *
 * @param {Object|djs.model.Root} element
 * @param {Boolean} [override] whether to override the current root element, if any
 *
 * @return {Object|djs.model.Root} new root element
 */
Canvas.prototype.setRootElement = function (element, override) {

	if (element) {
		this._ensureValid('root', element);
	}

	var currentRoot = this._rootElement,
			elementRegistry = this._elementRegistry,
			eventBus = this._eventBus;

	if (currentRoot) {
		if (!override) {
			throw new Error('rootElement already set, need to specify override');
		}

		// simulate element remove event sequence
		eventBus.fire('root.remove', { element: currentRoot });
		eventBus.fire('root.removed', { element: currentRoot });

		elementRegistry.remove(currentRoot);
	}

	if (element) {
		var gfx = this.getDefaultLayer();

		// resemble element add event sequence
		eventBus.fire('root.add', { element: element });

		elementRegistry.add(element, gfx, this._svg);

		eventBus.fire('root.added', { element: element, gfx: gfx });
	}

	this._rootElement = element;

	return element;
};

// add functionality //////////////////////

Canvas.prototype._ensureValid = function (type, element) {
	if (!element.id) {
		throw new Error('element must have an id');
	}

	if (this._elementRegistry.get(element.id)) {
		throw new Error('element with id ' + element.id + ' already exists');
	}

	var requiredAttrs = REQUIRED_MODEL_ATTRS[type];

	var valid = (0, _minDash.every)(requiredAttrs, function (attr) {
		return typeof element[attr] !== 'undefined';
	});

	if (!valid) {
		throw new Error('must supply { ' + requiredAttrs.join(', ') + ' } with ' + type);
	}
};

Canvas.prototype._setParent = function (element, parent, parentIndex) {
	(0, _Collections.add)(parent.children, element, parentIndex);
	element.parent = parent;
};

/**
 * Adds an element to the canvas.
 *
 * This wires the parent <-> child relationship between the element and
 * a explicitly specified parent or an implicit root element.
 *
 * During add it emits the events
 *
 *  * <{type}.add> (element, parent)
 *  * <{type}.added> (element, gfx)
 *
 * Extensions may hook into these events to perform their magic.
 *
 * @param {String} type
 * @param {Object|djs.model.Base} element
 * @param {Object|djs.model.Base} [parent]
 * @param {Number} [parentIndex]
 *
 * @return {Object|djs.model.Base} the added element
 */
Canvas.prototype._addElement = function (type, element, parent, parentIndex) {

	parent = parent || this.getRootElement();

	var eventBus = this._eventBus,
			graphicsFactory = this._graphicsFactory;

	this._ensureValid(type, element);

	eventBus.fire(type + '.add', { element: element, parent: parent });

	this._setParent(element, parent, parentIndex);

	// create graphics
	var gfx = graphicsFactory.create(type, element, parentIndex);

	this._elementRegistry.add(element, gfx);

	// update its visual
	graphicsFactory.update(type, element, gfx);

	eventBus.fire(type + '.added', { element: element, gfx: gfx });

	return element;
};

/**
 * Adds a shape to the canvas
 *
 * @param {Object|djs.model.Shape} shape to add to the diagram
 * @param {djs.model.Base} [parent]
 * @param {Number} [parentIndex]
 *
 * @return {djs.model.Shape} the added shape
 */
Canvas.prototype.addShape = function (shape, parent, parentIndex) {
	return this._addElement('shape', shape, parent, parentIndex);
};

/**
 * Adds a connection to the canvas
 *
 * @param {Object|djs.model.Connection} connection to add to the diagram
 * @param {djs.model.Base} [parent]
 * @param {Number} [parentIndex]
 *
 * @return {djs.model.Connection} the added connection
 */
Canvas.prototype.addConnection = function (connection, parent, parentIndex) {
	return this._addElement('connection', connection, parent, parentIndex);
};

/**
 * Internal remove element
 */
Canvas.prototype._removeElement = function (element, type) {

	var elementRegistry = this._elementRegistry,
			graphicsFactory = this._graphicsFactory,
			eventBus = this._eventBus;

	element = elementRegistry.get(element.id || element);

	if (!element) {
		// element was removed already
		return;
	}

	eventBus.fire(type + '.remove', { element: element });

	graphicsFactory.remove(element);

	// unset parent <-> child relationship
	(0, _Collections.remove)(element.parent && element.parent.children, element);
	element.parent = null;

	eventBus.fire(type + '.removed', { element: element });

	elementRegistry.remove(element);

	return element;
};

/**
 * Removes a shape from the canvas
 *
 * @param {String|djs.model.Shape} shape or shape id to be removed
 *
 * @return {djs.model.Shape} the removed shape
 */
Canvas.prototype.removeShape = function (shape) {

	/**
	 * An event indicating that a shape is about to be removed from the canvas.
	 *
	 * @memberOf Canvas
	 *
	 * @event shape.remove
	 * @type {Object}
	 * @property {djs.model.Shape} element the shape descriptor
	 * @property {Object} gfx the graphical representation of the shape
	 */

	/**
	 * An event indicating that a shape has been removed from the canvas.
	 *
	 * @memberOf Canvas
	 *
	 * @event shape.removed
	 * @type {Object}
	 * @property {djs.model.Shape} element the shape descriptor
	 * @property {Object} gfx the graphical representation of the shape
	 */
	return this._removeElement(shape, 'shape');
};

/**
 * Removes a connection from the canvas
 *
 * @param {String|djs.model.Connection} connection or connection id to be removed
 *
 * @return {djs.model.Connection} the removed connection
 */
Canvas.prototype.removeConnection = function (connection) {

	/**
	 * An event indicating that a connection is about to be removed from the canvas.
	 *
	 * @memberOf Canvas
	 *
	 * @event connection.remove
	 * @type {Object}
	 * @property {djs.model.Connection} element the connection descriptor
	 * @property {Object} gfx the graphical representation of the connection
	 */

	/**
	 * An event indicating that a connection has been removed from the canvas.
	 *
	 * @memberOf Canvas
	 *
	 * @event connection.removed
	 * @type {Object}
	 * @property {djs.model.Connection} element the connection descriptor
	 * @property {Object} gfx the graphical representation of the connection
	 */
	return this._removeElement(connection, 'connection');
};

/**
 * Return the graphical object underlaying a certain diagram element
 *
 * @param {String|djs.model.Base} element descriptor of the element
 * @param {Boolean} [secondary=false] whether to return the secondary connected element
 *
 * @return {SVGElement}
 */
Canvas.prototype.getGraphics = function (element, secondary) {
	return this._elementRegistry.getGraphics(element, secondary);
};

/**
 * Perform a viewbox update via a given change function.
 *
 * @param {Function} changeFn
 */
Canvas.prototype._changeViewbox = function (changeFn) {

	// notify others of the upcoming viewbox change
	this._eventBus.fire('canvas.viewbox.changing');

	// perform actual change
	changeFn.apply(this);

	// reset the cached viewbox so that
	// a new get operation on viewbox or zoom
	// triggers a viewbox re-computation
	this._cachedViewbox = null;

	// notify others of the change; this step
	// may or may not be debounced
	this._viewboxChanged();
};

Canvas.prototype._viewboxChanged = function () {
	this._eventBus.fire('canvas.viewbox.changed', { viewbox: this.viewbox() });
};

/**
 * Gets or sets the view box of the canvas, i.e. the
 * area that is currently displayed.
 *
 * The getter may return a cached viewbox (if it is currently
 * changing). To force a recomputation, pass `false` as the first argument.
 *
 * @example
 *
 * canvas.viewbox({ x: 100, y: 100, width: 500, height: 500 })
 *
 * // sets the visible area of the diagram to (100|100) -> (600|100)
 * // and and scales it according to the diagram width
 *
 * var viewbox = canvas.viewbox(); // pass `false` to force recomputing the box.
 *
 * console.log(viewbox);
 * // {
 * //   inner: Dimensions,
 * //   outer: Dimensions,
 * //   scale,
 * //   x, y,
 * //   width, height
 * // }
 *
 * // if the current diagram is zoomed and scrolled, you may reset it to the
 * // default zoom via this method, too:
 *
 * var zoomedAndScrolledViewbox = canvas.viewbox();
 *
 * canvas.viewbox({
 *   x: 0,
 *   y: 0,
 *   width: zoomedAndScrolledViewbox.outer.width,
 *   height: zoomedAndScrolledViewbox.outer.height
 * });
 *
 * @param  {Object} [box] the new view box to set
 * @param  {Number} box.x the top left X coordinate of the canvas visible in view box
 * @param  {Number} box.y the top left Y coordinate of the canvas visible in view box
 * @param  {Number} box.width the visible width
 * @param  {Number} box.height
 *
 * @return {Object} the current view box
 */
Canvas.prototype.viewbox = function (box) {

	if (box === undefined && this._cachedViewbox) {
		return this._cachedViewbox;
	}

	var viewport = this._viewport,
			innerBox,
			outerBox = this.getSize(),
			matrix,
			transform,
			scale,
			x,
			y;

	if (!box) {
		// compute the inner box based on the
		// diagrams default layer. This allows us to exclude
		// external components, such as overlays
		innerBox = this.getDefaultLayer().getBBox();

		transform = (0, _tinySvg.transform)(viewport);
		matrix = transform ? transform.matrix : (0, _tinySvg.createMatrix)();
		scale = round(matrix.a, 1000);

		x = round(-matrix.e || 0, 1000);
		y = round(-matrix.f || 0, 1000);

		box = this._cachedViewbox = {
			x: x ? x / scale : 0,
			y: y ? y / scale : 0,
			width: outerBox.width / scale,
			height: outerBox.height / scale,
			scale: scale,
			inner: {
				width: innerBox.width,
				height: innerBox.height,
				x: innerBox.x,
				y: innerBox.y
			},
			outer: outerBox
		};

		return box;
	} else {

		this._changeViewbox(function () {
			scale = Math.min(outerBox.width / box.width, outerBox.height / box.height);

			var matrix = this._svg.createSVGMatrix().scale(scale).translate(-box.x, -box.y);

			(0, _tinySvg.transform)(viewport, matrix);
		});
	}

	return box;
};

/**
 * Gets or sets the scroll of the canvas.
 *
 * @param {Object} [delta] the new scroll to apply.
 *
 * @param {Number} [delta.dx]
 * @param {Number} [delta.dy]
 */
Canvas.prototype.scroll = function (delta) {

	var node = this._viewport;
	var matrix = node.getCTM();

	if (delta) {
		this._changeViewbox(function () {
			delta = (0, _minDash.assign)({ dx: 0, dy: 0 }, delta || {});

			matrix = this._svg.createSVGMatrix().translate(delta.dx, delta.dy).multiply(matrix);

			setCTM(node, matrix);
		});
	}

	return { x: matrix.e, y: matrix.f };
};

/**
 * Gets or sets the current zoom of the canvas, optionally zooming
 * to the specified position.
 *
 * The getter may return a cached zoom level. Call it with `false` as
 * the first argument to force recomputation of the current level.
 *
 * @param {String|Number} [newScale] the new zoom level, either a number, i.e. 0.9,
 *                                   or `fit-viewport` to adjust the size to fit the current viewport
 * @param {String|Point} [center] the reference point { x: .., y: ..} to zoom to, 'auto' to zoom into mid or null
 *
 * @return {Number} the current scale
 */
Canvas.prototype.zoom = function (newScale, center) {

	if (!newScale) {
		return this.viewbox(newScale).scale;
	}

	if (newScale === 'fit-viewport') {
		return this._fitViewport(center);
	}

	var outer, matrix;

	this._changeViewbox(function () {

		if ((typeof center === 'undefined' ? 'undefined' : _typeof(center)) !== 'object') {
			outer = this.viewbox().outer;

			center = {
				x: outer.width / 2,
				y: outer.height / 2
			};
		}

		matrix = this._setZoom(newScale, center);
	});

	return round(matrix.a, 1000);
};

function setCTM(node, m) {
	var mstr = 'matrix(' + m.a + ',' + m.b + ',' + m.c + ',' + m.d + ',' + m.e + ',' + m.f + ')';
	node.setAttribute('transform', mstr);
}

Canvas.prototype._fitViewport = function (center) {

	var vbox = this.viewbox(),
			outer = vbox.outer,
			inner = vbox.inner,
			newScale,
			newViewbox;

	// display the complete diagram without zooming in.
	// instead of relying on internal zoom, we perform a
	// hard reset on the canvas viewbox to realize this
	//
	// if diagram does not need to be zoomed in, we focus it around
	// the diagram origin instead

	if (inner.x >= 0 && inner.y >= 0 && inner.x + inner.width <= outer.width && inner.y + inner.height <= outer.height && !center) {

		newViewbox = {
			x: 0,
			y: 0,
			width: Math.max(inner.width + inner.x, outer.width),
			height: Math.max(inner.height + inner.y, outer.height)
		};
	} else {

		newScale = Math.min(1, outer.width / inner.width, outer.height / inner.height);
		newViewbox = {
			x: inner.x + (center ? inner.width / 2 - outer.width / newScale / 2 : 0),
			y: inner.y + (center ? inner.height / 2 - outer.height / newScale / 2 : 0),
			width: outer.width / newScale,
			height: outer.height / newScale
		};
	}

	this.viewbox(newViewbox);

	return this.viewbox(false).scale;
};

Canvas.prototype._setZoom = function (scale, center) {

	var svg = this._svg,
			viewport = this._viewport;

	var matrix = svg.createSVGMatrix();
	var point = svg.createSVGPoint();

	var centerPoint, originalPoint, currentMatrix, scaleMatrix, newMatrix;

	currentMatrix = viewport.getCTM();

	var currentScale = currentMatrix.a;

	if (center) {
		centerPoint = (0, _minDash.assign)(point, center);

		// revert applied viewport transformations
		originalPoint = centerPoint.matrixTransform(currentMatrix.inverse());

		// create scale matrix
		scaleMatrix = matrix.translate(originalPoint.x, originalPoint.y).scale(1 / currentScale * scale).translate(-originalPoint.x, -originalPoint.y);

		newMatrix = currentMatrix.multiply(scaleMatrix);
	} else {
		newMatrix = matrix.scale(scale);
	}

	setCTM(this._viewport, newMatrix);

	return newMatrix;
};

/**
 * Returns the size of the canvas
 *
 * @return {Dimensions}
 */
Canvas.prototype.getSize = function () {
	return {
		width: this._container.clientWidth,
		height: this._container.clientHeight
	};
};

/**
 * Return the absolute bounding box for the given element
 *
 * The absolute bounding box may be used to display overlays in the
 * callers (browser) coordinate system rather than the zoomed in/out
 * canvas coordinates.
 *
 * @param  {ElementDescriptor} element
 * @return {Bounds} the absolute bounding box
 */
Canvas.prototype.getAbsoluteBBox = function (element) {
	var vbox = this.viewbox();
	var bbox;

	// connection
	// use svg bbox
	if (element.waypoints) {
		var gfx = this.getGraphics(element);

		bbox = gfx.getBBox();
	}
	// shapes
	// use data
	else {
			bbox = element;
		}

	var x = bbox.x * vbox.scale - vbox.x * vbox.scale;
	var y = bbox.y * vbox.scale - vbox.y * vbox.scale;

	var width = bbox.width * vbox.scale;
	var height = bbox.height * vbox.scale;

	return {
		x: x,
		y: y,
		width: width,
		height: height
	};
};

/**
 * Fires an event in order other modules can react to the
 * canvas resizing
 */
Canvas.prototype.resized = function () {

	// force recomputation of view box
	delete this._cachedViewbox;

	this._eventBus.fire('canvas.resized');
};

},{"47":47,"48":48,"59":59,"79":79}],27:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = ElementFactory;

var _model = _dereq_(46);

var _minDash = _dereq_(59);

/**
 * A factory for diagram-js shapes
 */
function ElementFactory() {
	this._uid = 12;
}

ElementFactory.prototype.createRoot = function (attrs) {
	return this.create('root', attrs);
};

ElementFactory.prototype.createLabel = function (attrs) {
	return this.create('label', attrs);
};

ElementFactory.prototype.createShape = function (attrs) {
	return this.create('shape', attrs);
};

ElementFactory.prototype.createConnection = function (attrs) {
	return this.create('connection', attrs);
};

/**
 * Create a model element with the given type and
 * a number of pre-set attributes.
 *
 * @param  {String} type
 * @param  {Object} attrs
 * @return {djs.model.Base} the newly created model instance
 */
ElementFactory.prototype.create = function (type, attrs) {

	attrs = (0, _minDash.assign)({}, attrs || {});

	if (!attrs.id) {
		attrs.id = type + '_' + this._uid++;
	}

	return (0, _model.create)(type, attrs);
};

},{"46":46,"59":59}],28:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = ElementRegistry;

var _tinySvg = _dereq_(79);

var ELEMENT_ID = 'data-element-id';

/**
 * @class
 *
 * A registry that keeps track of all shapes in the diagram.
 */
function ElementRegistry(eventBus) {
	this._elements = {};

	this._eventBus = eventBus;
}

ElementRegistry.$inject = ['eventBus'];

/**
 * Register a pair of (element, gfx, (secondaryGfx)).
 *
 * @param {djs.model.Base} element
 * @param {SVGElement} gfx
 * @param {SVGElement} [secondaryGfx] optional other element to register, too
 */
ElementRegistry.prototype.add = function (element, gfx, secondaryGfx) {

	var id = element.id;

	this._validateId(id);

	// associate dom node with element
	(0, _tinySvg.attr)(gfx, ELEMENT_ID, id);

	if (secondaryGfx) {
		(0, _tinySvg.attr)(secondaryGfx, ELEMENT_ID, id);
	}

	this._elements[id] = { element: element, gfx: gfx, secondaryGfx: secondaryGfx };
};

/**
 * Removes an element from the registry.
 *
 * @param {djs.model.Base} element
 */
ElementRegistry.prototype.remove = function (element) {
	var elements = this._elements,
			id = element.id || element,
			container = id && elements[id];

	if (container) {

		// unset element id on gfx
		(0, _tinySvg.attr)(container.gfx, ELEMENT_ID, '');

		if (container.secondaryGfx) {
			(0, _tinySvg.attr)(container.secondaryGfx, ELEMENT_ID, '');
		}

		delete elements[id];
	}
};

/**
 * Update the id of an element
 *
 * @param {djs.model.Base} element
 * @param {String} newId
 */
ElementRegistry.prototype.updateId = function (element, newId) {

	this._validateId(newId);

	if (typeof element === 'string') {
		element = this.get(element);
	}

	this._eventBus.fire('element.updateId', {
		element: element,
		newId: newId
	});

	var gfx = this.getGraphics(element),
			secondaryGfx = this.getGraphics(element, true);

	this.remove(element);

	element.id = newId;

	this.add(element, gfx, secondaryGfx);
};

/**
 * Return the model element for a given id or graphics.
 *
 * @example
 *
 * elementRegistry.get('SomeElementId_1');
 * elementRegistry.get(gfx);
 *
 *
 * @param {String|SVGElement} filter for selecting the element
 *
 * @return {djs.model.Base}
 */
ElementRegistry.prototype.get = function (filter) {
	var id;

	if (typeof filter === 'string') {
		id = filter;
	} else {
		id = filter && (0, _tinySvg.attr)(filter, ELEMENT_ID);
	}

	var container = this._elements[id];
	return container && container.element;
};

/**
 * Return all elements that match a given filter function.
 *
 * @param {Function} fn
 *
 * @return {Array<djs.model.Base>}
 */
ElementRegistry.prototype.filter = function (fn) {

	var filtered = [];

	this.forEach(function (element, gfx) {
		if (fn(element, gfx)) {
			filtered.push(element);
		}
	});

	return filtered;
};

/**
 * Return all rendered model elements.
 *
 * @return {Array<djs.model.Base>}
 */
ElementRegistry.prototype.getAll = function () {
	return this.filter(function (e) {
		return e;
	});
};

/**
 * Iterate over all diagram elements.
 *
 * @param {Function} fn
 */
ElementRegistry.prototype.forEach = function (fn) {

	var map = this._elements;

	Object.keys(map).forEach(function (id) {
		var container = map[id],
				element = container.element,
				gfx = container.gfx;

		return fn(element, gfx);
	});
};

/**
 * Return the graphical representation of an element or its id.
 *
 * @example
 * elementRegistry.getGraphics('SomeElementId_1');
 * elementRegistry.getGraphics(rootElement); // <g ...>
 *
 * elementRegistry.getGraphics(rootElement, true); // <svg ...>
 *
 *
 * @param {String|djs.model.Base} filter
 * @param {Boolean} [secondary=false] whether to return the secondary connected element
 *
 * @return {SVGElement}
 */
ElementRegistry.prototype.getGraphics = function (filter, secondary) {
	var id = filter.id || filter;

	var container = this._elements[id];
	return container && (secondary ? container.secondaryGfx : container.gfx);
};

/**
 * Validate the suitability of the given id and signals a problem
 * with an exception.
 *
 * @param {String} id
 *
 * @throws {Error} if id is empty or already assigned
 */
ElementRegistry.prototype._validateId = function (id) {
	if (!id) {
		throw new Error('element must have an id');
	}

	if (this._elements[id]) {
		throw new Error('element with id ' + id + ' already added');
	}
};

},{"79":79}],29:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

exports.default = EventBus;

var _minDash = _dereq_(59);

var FN_REF = '__fn';

var DEFAULT_PRIORITY = 1000;

var slice = Array.prototype.slice;

/**
 * A general purpose event bus.
 *
 * This component is used to communicate across a diagram instance.
 * Other parts of a diagram can use it to listen to and broadcast events.
 *
 *
 * ## Registering for Events
 *
 * The event bus provides the {@link EventBus#on} and {@link EventBus#once}
 * methods to register for events. {@link EventBus#off} can be used to
 * remove event registrations. Listeners receive an instance of {@link Event}
 * as the first argument. It allows them to hook into the event execution.
 *
 * ```javascript
 *
 * // listen for event
 * eventBus.on('foo', function(event) {
 *
 *   // access event type
 *   event.type; // 'foo'
 *
 *   // stop propagation to other listeners
 *   event.stopPropagation();
 *
 *   // prevent event default
 *   event.preventDefault();
 * });
 *
 * // listen for event with custom payload
 * eventBus.on('bar', function(event, payload) {
 *   console.log(payload);
 * });
 *
 * // listen for event returning value
 * eventBus.on('foobar', function(event) {
 *
 *   // stop event propagation + prevent default
 *   return false;
 *
 *   // stop event propagation + return custom result
 *   return {
 *     complex: 'listening result'
 *   };
 * });
 *
 *
 * // listen with custom priority (default=1000, higher is better)
 * eventBus.on('priorityfoo', 1500, function(event) {
 *   console.log('invoked first!');
 * });
 *
 *
 * // listen for event and pass the context (`this`)
 * eventBus.on('foobar', function(event) {
 *   this.foo();
 * }, this);
 * ```
 *
 *
 * ## Emitting Events
 *
 * Events can be emitted via the event bus using {@link EventBus#fire}.
 *
 * ```javascript
 *
 * // false indicates that the default action
 * // was prevented by listeners
 * if (eventBus.fire('foo') === false) {
 *   console.log('default has been prevented!');
 * };
 *
 *
 * // custom args + return value listener
 * eventBus.on('sum', function(event, a, b) {
 *   return a + b;
 * });
 *
 * // you can pass custom arguments + retrieve result values.
 * var sum = eventBus.fire('sum', 1, 2);
 * console.log(sum); // 3
 * ```
 */
function EventBus() {
	this._listeners = {};

	// cleanup on destroy on lowest priority to allow
	// message passing until the bitter end
	this.on('diagram.destroy', 1, this._destroy, this);
}

/**
 * Register an event listener for events with the given name.
 *
 * The callback will be invoked with `event, ...additionalArguments`
 * that have been passed to {@link EventBus#fire}.
 *
 * Returning false from a listener will prevent the events default action
 * (if any is specified). To stop an event from being processed further in
 * other listeners execute {@link Event#stopPropagation}.
 *
 * Returning anything but `undefined` from a listener will stop the listener propagation.
 *
 * @param {String|Array<String>} events
 * @param {Number} [priority=1000] the priority in which this listener is called, larger is higher
 * @param {Function} callback
 * @param {Object} [that] Pass context (`this`) to the callback
 */
EventBus.prototype.on = function (events, priority, callback, that) {

	events = (0, _minDash.isArray)(events) ? events : [events];

	if ((0, _minDash.isFunction)(priority)) {
		that = callback;
		callback = priority;
		priority = DEFAULT_PRIORITY;
	}

	if (!(0, _minDash.isNumber)(priority)) {
		throw new Error('priority must be a number');
	}

	var actualCallback = callback;

	if (that) {
		actualCallback = (0, _minDash.bind)(callback, that);

		// make sure we remember and are able to remove
		// bound callbacks via {@link #off} using the original
		// callback
		actualCallback[FN_REF] = callback[FN_REF] || callback;
	}

	var self = this;

	events.forEach(function (e) {
		self._addListener(e, {
			priority: priority,
			callback: actualCallback,
			next: null
		});
	});
};

/**
 * Register an event listener that is executed only once.
 *
 * @param {String} event the event name to register for
 * @param {Function} callback the callback to execute
 * @param {Object} [that] Pass context (`this`) to the callback
 */
EventBus.prototype.once = function (event, priority, callback, that) {
	var self = this;

	if ((0, _minDash.isFunction)(priority)) {
		that = callback;
		callback = priority;
		priority = DEFAULT_PRIORITY;
	}

	if (!(0, _minDash.isNumber)(priority)) {
		throw new Error('priority must be a number');
	}

	function wrappedCallback() {
		var result = callback.apply(that, arguments);

		self.off(event, wrappedCallback);

		return result;
	}

	// make sure we remember and are able to remove
	// bound callbacks via {@link #off} using the original
	// callback
	wrappedCallback[FN_REF] = callback;

	this.on(event, priority, wrappedCallback);
};

/**
 * Removes event listeners by event and callback.
 *
 * If no callback is given, all listeners for a given event name are being removed.
 *
 * @param {String|Array<String>} events
 * @param {Function} [callback]
 */
EventBus.prototype.off = function (events, callback) {

	events = (0, _minDash.isArray)(events) ? events : [events];

	var self = this;

	events.forEach(function (event) {
		self._removeListener(event, callback);
	});
};

/**
 * Create an EventBus event.
 *
 * @param {Object} data
 *
 * @return {Object} event, recognized by the eventBus
 */
EventBus.prototype.createEvent = function (data) {
	var event = new InternalEvent();

	event.init(data);

	return event;
};

/**
 * Fires a named event.
 *
 * @example
 *
 * // fire event by name
 * events.fire('foo');
 *
 * // fire event object with nested type
 * var event = { type: 'foo' };
 * events.fire(event);
 *
 * // fire event with explicit type
 * var event = { x: 10, y: 20 };
 * events.fire('element.moved', event);
 *
 * // pass additional arguments to the event
 * events.on('foo', function(event, bar) {
 *   alert(bar);
 * });
 *
 * events.fire({ type: 'foo' }, 'I am bar!');
 *
 * @param {String} [name] the optional event name
 * @param {Object} [event] the event object
 * @param {...Object} additional arguments to be passed to the callback functions
 *
 * @return {Boolean} the events return value, if specified or false if the
 *                   default action was prevented by listeners
 */
EventBus.prototype.fire = function (type, data) {

	var event, firstListener, returnValue, args;

	args = slice.call(arguments);

	if ((typeof type === 'undefined' ? 'undefined' : _typeof(type)) === 'object') {
		event = type;
		type = event.type;
	}

	if (!type) {
		throw new Error('no event type specified');
	}

	firstListener = this._listeners[type];

	if (!firstListener) {
		return;
	}

	// we make sure we fire instances of our home made
	// events here. We wrap them only once, though
	if (data instanceof InternalEvent) {
		// we are fine, we alread have an event
		event = data;
	} else {
		event = this.createEvent(data);
	}

	// ensure we pass the event as the first parameter
	args[0] = event;

	// original event type (in case we delegate)
	var originalType = event.type;

	// update event type before delegation
	if (type !== originalType) {
		event.type = type;
	}

	try {
		returnValue = this._invokeListeners(event, args, firstListener);
	} finally {
		// reset event type after delegation
		if (type !== originalType) {
			event.type = originalType;
		}
	}

	// set the return value to false if the event default
	// got prevented and no other return value exists
	if (returnValue === undefined && event.defaultPrevented) {
		returnValue = false;
	}

	return returnValue;
};

EventBus.prototype.handleError = function (error) {
	return this.fire('error', { error: error }) === false;
};

EventBus.prototype._destroy = function () {
	this._listeners = {};
};

EventBus.prototype._invokeListeners = function (event, args, listener) {

	var returnValue;

	while (listener) {

		// handle stopped propagation
		if (event.cancelBubble) {
			break;
		}

		returnValue = this._invokeListener(event, args, listener);

		listener = listener.next;
	}

	return returnValue;
};

EventBus.prototype._invokeListener = function (event, args, listener) {

	var returnValue;

	try {
		// returning false prevents the default action
		returnValue = invokeFunction(listener.callback, args);

		// stop propagation on return value
		if (returnValue !== undefined) {
			event.returnValue = returnValue;
			event.stopPropagation();
		}

		// prevent default on return false
		if (returnValue === false) {
			event.preventDefault();
		}
	} catch (e) {
		if (!this.handleError(e)) {
			console.error('unhandled error in event listener');
			console.error(e.stack);

			throw e;
		}
	}

	return returnValue;
};

/*
 * Add new listener with a certain priority to the list
 * of listeners (for the given event).
 *
 * The semantics of listener registration / listener execution are
 * first register, first serve: New listeners will always be inserted
 * after existing listeners with the same priority.
 *
 * Example: Inserting two listeners with priority 1000 and 1300
 *
 *    * before: [ 1500, 1500, 1000, 1000 ]
 *    * after: [ 1500, 1500, (new=1300), 1000, 1000, (new=1000) ]
 *
 * @param {String} event
 * @param {Object} listener { priority, callback }
 */
EventBus.prototype._addListener = function (event, newListener) {

	var listener = this._getListeners(event),
			previousListener;

	// no prior listeners
	if (!listener) {
		this._setListeners(event, newListener);

		return;
	}

	// ensure we order listeners by priority from
	// 0 (high) to n > 0 (low)
	while (listener) {

		if (listener.priority < newListener.priority) {

			newListener.next = listener;

			if (previousListener) {
				previousListener.next = newListener;
			} else {
				this._setListeners(event, newListener);
			}

			return;
		}

		previousListener = listener;
		listener = listener.next;
	}

	// add new listener to back
	previousListener.next = newListener;
};

EventBus.prototype._getListeners = function (name) {
	return this._listeners[name];
};

EventBus.prototype._setListeners = function (name, listener) {
	this._listeners[name] = listener;
};

EventBus.prototype._removeListener = function (event, callback) {

	var listener = this._getListeners(event),
			nextListener,
			previousListener,
			listenerCallback;

	if (!callback) {
		// clear listeners
		this._setListeners(event, null);

		return;
	}

	while (listener) {

		nextListener = listener.next;

		listenerCallback = listener.callback;

		if (listenerCallback === callback || listenerCallback[FN_REF] === callback) {
			if (previousListener) {
				previousListener.next = nextListener;
			} else {
				// new first listener
				this._setListeners(event, nextListener);
			}
		}

		previousListener = listener;
		listener = nextListener;
	}
};

/**
 * A event that is emitted via the event bus.
 */
function InternalEvent() {}

InternalEvent.prototype.stopPropagation = function () {
	this.cancelBubble = true;
};

InternalEvent.prototype.preventDefault = function () {
	this.defaultPrevented = true;
};

InternalEvent.prototype.init = function (data) {
	(0, _minDash.assign)(this, data || {});
};

/**
 * Invoke function. Be fast...
 *
 * @param {Function} fn
 * @param {Array<Object>} args
 *
 * @return {Any}
 */
function invokeFunction(fn, args) {
	return fn.apply(null, args);
}

},{"59":59}],30:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = GraphicsFactory;

var _minDash = _dereq_(59);

var _GraphicsUtil = _dereq_(50);

var _SvgTransformUtil = _dereq_(55);

var _minDom = _dereq_(60);

var _tinySvg = _dereq_(79);

/**
 * A factory that creates graphical elements
 *
 * @param {EventBus} eventBus
 * @param {ElementRegistry} elementRegistry
 */
function GraphicsFactory(eventBus, elementRegistry) {
	this._eventBus = eventBus;
	this._elementRegistry = elementRegistry;
}

GraphicsFactory.$inject = ['eventBus', 'elementRegistry'];

GraphicsFactory.prototype._getChildren = function (element) {

	var gfx = this._elementRegistry.getGraphics(element);

	var childrenGfx;

	// root element
	if (!element.parent) {
		childrenGfx = gfx;
	} else {
		childrenGfx = (0, _GraphicsUtil.getChildren)(gfx);
		if (!childrenGfx) {
			childrenGfx = (0, _tinySvg.create)('g');
			(0, _tinySvg.classes)(childrenGfx).add('djs-children');

			(0, _tinySvg.append)(gfx.parentNode, childrenGfx);
		}
	}

	return childrenGfx;
};

/**
 * Clears the graphical representation of the element and returns the
 * cleared visual (the <g class="djs-visual" /> element).
 */
GraphicsFactory.prototype._clear = function (gfx) {
	var visual = (0, _GraphicsUtil.getVisual)(gfx);

	(0, _minDom.clear)(visual);

	return visual;
};

/**
 * Creates a gfx container for shapes and connections
 *
 * The layout is as follows:
 *
 * <g class="djs-group">
 *
 *   <!-- the gfx -->
 *   <g class="djs-element djs-(shape|connection)">
 *     <g class="djs-visual">
 *       <!-- the renderer draws in here -->
 *     </g>
 *
 *     <!-- extensions (overlays, click box, ...) goes here
 *   </g>
 *
 *   <!-- the gfx child nodes -->
 *   <g class="djs-children"></g>
 * </g>
 *
 * @param {Object} parent
 * @param {String} type the type of the element, i.e. shape | connection
 * @param {Number} [parentIndex] position to create container in parent
 */
GraphicsFactory.prototype._createContainer = function (type, childrenGfx, parentIndex) {
	var outerGfx = (0, _tinySvg.create)('g');
	(0, _tinySvg.classes)(outerGfx).add('djs-group');

	// insert node at position
	if (typeof parentIndex !== 'undefined') {
		prependTo(outerGfx, childrenGfx, childrenGfx.childNodes[parentIndex]);
	} else {
		(0, _tinySvg.append)(childrenGfx, outerGfx);
	}

	var gfx = (0, _tinySvg.create)('g');
	(0, _tinySvg.classes)(gfx).add('djs-element');
	(0, _tinySvg.classes)(gfx).add('djs-' + type);

	(0, _tinySvg.append)(outerGfx, gfx);

	// create visual
	var visual = (0, _tinySvg.create)('g');
	(0, _tinySvg.classes)(visual).add('djs-visual');

	(0, _tinySvg.append)(gfx, visual);

	return gfx;
};

GraphicsFactory.prototype.create = function (type, element, parentIndex) {
	var childrenGfx = this._getChildren(element.parent);
	return this._createContainer(type, childrenGfx, parentIndex);
};

GraphicsFactory.prototype.updateContainments = function (elements) {

	var self = this,
			elementRegistry = this._elementRegistry,
			parents;

	parents = (0, _minDash.reduce)(elements, function (map, e) {

		if (e.parent) {
			map[e.parent.id] = e.parent;
		}

		return map;
	}, {});

	// update all parents of changed and reorganized their children
	// in the correct order (as indicated in our model)
	(0, _minDash.forEach)(parents, function (parent) {

		var children = parent.children;

		if (!children) {
			return;
		}

		var childGfx = self._getChildren(parent);

		(0, _minDash.forEach)(children.slice().reverse(), function (c) {
			var gfx = elementRegistry.getGraphics(c);

			prependTo(gfx.parentNode, childGfx);
		});
	});
};

GraphicsFactory.prototype.drawShape = function (visual, element) {
	var eventBus = this._eventBus;

	return eventBus.fire('render.shape', { gfx: visual, element: element });
};

GraphicsFactory.prototype.getShapePath = function (element) {
	var eventBus = this._eventBus;

	return eventBus.fire('render.getShapePath', element);
};

GraphicsFactory.prototype.drawConnection = function (visual, element) {
	var eventBus = this._eventBus;

	return eventBus.fire('render.connection', { gfx: visual, element: element });
};

GraphicsFactory.prototype.getConnectionPath = function (waypoints) {
	var eventBus = this._eventBus;

	return eventBus.fire('render.getConnectionPath', waypoints);
};

GraphicsFactory.prototype.update = function (type, element, gfx) {
	// Do not update root element
	if (!element.parent) {
		return;
	}

	var visual = this._clear(gfx);

	// redraw
	if (type === 'shape') {
		this.drawShape(visual, element);

		// update positioning
		(0, _SvgTransformUtil.translate)(gfx, element.x, element.y);
	} else if (type === 'connection') {
		this.drawConnection(visual, element);
	} else {
		throw new Error('unknown type: ' + type);
	}

	if (element.hidden) {
		(0, _tinySvg.attr)(gfx, 'display', 'none');
	} else {
		(0, _tinySvg.attr)(gfx, 'display', 'block');
	}
};

GraphicsFactory.prototype.remove = function (element) {
	var gfx = this._elementRegistry.getGraphics(element);

	// remove
	(0, _tinySvg.remove)(gfx.parentNode);
};

// helpers //////////////////////

function prependTo(newNode, parentNode, siblingNode) {
	parentNode.insertBefore(newNode, siblingNode || parentNode.firstChild);
}

},{"50":50,"55":55,"59":59,"60":60,"79":79}],31:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _draw = _dereq_(35);

var _draw2 = _interopRequireDefault(_draw);

var _Canvas = _dereq_(26);

var _Canvas2 = _interopRequireDefault(_Canvas);

var _ElementRegistry = _dereq_(28);

var _ElementRegistry2 = _interopRequireDefault(_ElementRegistry);

var _ElementFactory = _dereq_(27);

var _ElementFactory2 = _interopRequireDefault(_ElementFactory);

var _EventBus = _dereq_(29);

var _EventBus2 = _interopRequireDefault(_EventBus);

var _GraphicsFactory = _dereq_(30);

var _GraphicsFactory2 = _interopRequireDefault(_GraphicsFactory);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	__depends__: [_draw2.default],
	__init__: ['canvas'],
	canvas: ['type', _Canvas2.default],
	elementRegistry: ['type', _ElementRegistry2.default],
	elementFactory: ['type', _ElementFactory2.default],
	eventBus: ['type', _EventBus2.default],
	graphicsFactory: ['type', _GraphicsFactory2.default]
};

},{"26":26,"27":27,"28":28,"29":29,"30":30,"35":35}],32:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = BaseRenderer;
var DEFAULT_RENDER_PRIORITY = 1000;

/**
 * The base implementation of shape and connection renderers.
 *
 * @param {EventBus} eventBus
 * @param {Number} [renderPriority=1000]
 */
function BaseRenderer(eventBus, renderPriority) {
	var self = this;

	renderPriority = renderPriority || DEFAULT_RENDER_PRIORITY;

	eventBus.on(['render.shape', 'render.connection'], renderPriority, function (evt, context) {
		var type = evt.type,
				element = context.element,
				visuals = context.gfx;

		if (self.canRender(element)) {
			if (type === 'render.shape') {
				return self.drawShape(visuals, element);
			} else {
				return self.drawConnection(visuals, element);
			}
		}
	});

	eventBus.on(['render.getShapePath', 'render.getConnectionPath'], renderPriority, function (evt, element) {
		if (self.canRender(element)) {
			if (evt.type === 'render.getShapePath') {
				return self.getShapePath(element);
			} else {
				return self.getConnectionPath(element);
			}
		}
	});
}

/**
 * Should check whether *this* renderer can render
 * the element/connection.
 *
 * @param {element} element
 *
 * @returns {Boolean}
 */
BaseRenderer.prototype.canRender = function () {};

/**
 * Provides the shape's snap svg element to be drawn on the `canvas`.
 *
 * @param {djs.Graphics} visuals
 * @param {Shape} shape
 *
 * @returns {Snap.svg} [returns a Snap.svg paper element ]
 */
BaseRenderer.prototype.drawShape = function () {};

/**
 * Provides the shape's snap svg element to be drawn on the `canvas`.
 *
 * @param {djs.Graphics} visuals
 * @param {Connection} connection
 *
 * @returns {Snap.svg} [returns a Snap.svg paper element ]
 */
BaseRenderer.prototype.drawConnection = function () {};

/**
 * Gets the SVG path of a shape that represents it's visual bounds.
 *
 * @param {Shape} shape
 *
 * @return {string} svg path
 */
BaseRenderer.prototype.getShapePath = function () {};

/**
 * Gets the SVG path of a connection that represents it's visual bounds.
 *
 * @param {Connection} connection
 *
 * @return {string} svg path
 */
BaseRenderer.prototype.getConnectionPath = function () {};

},{}],33:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = DefaultRenderer;

var _inherits = _dereq_(58);

var _inherits2 = _interopRequireDefault(_inherits);

var _BaseRenderer = _dereq_(32);

var _BaseRenderer2 = _interopRequireDefault(_BaseRenderer);

var _RenderUtil = _dereq_(54);

var _tinySvg = _dereq_(79);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// apply default renderer with lowest possible priority
// so that it only kicks in if noone else could render
var DEFAULT_RENDER_PRIORITY = 1;

/**
 * The default renderer used for shapes and connections.
 *
 * @param {EventBus} eventBus
 * @param {Styles} styles
 */
function DefaultRenderer(eventBus, styles) {
	//
	_BaseRenderer2.default.call(this, eventBus, DEFAULT_RENDER_PRIORITY);

	this.CONNECTION_STYLE = styles.style(['no-fill'], { strokeWidth: 5, stroke: 'fuchsia' });
	this.SHAPE_STYLE = styles.style({ fill: 'white', stroke: 'fuchsia', strokeWidth: 2 });
}

(0, _inherits2.default)(DefaultRenderer, _BaseRenderer2.default);

DefaultRenderer.prototype.canRender = function () {
	return true;
};

DefaultRenderer.prototype.drawShape = function drawShape(visuals, element) {

	var rect = (0, _tinySvg.create)('rect');
	(0, _tinySvg.attr)(rect, {
		x: 0,
		y: 0,
		width: element.width || 0,
		height: element.height || 0
	});
	(0, _tinySvg.attr)(rect, this.SHAPE_STYLE);

	(0, _tinySvg.append)(visuals, rect);

	return rect;
};

DefaultRenderer.prototype.drawConnection = function drawConnection(visuals, connection) {

	var line = (0, _RenderUtil.createLine)(connection.waypoints, this.CONNECTION_STYLE);
	(0, _tinySvg.append)(visuals, line);

	return line;
};

DefaultRenderer.prototype.getShapePath = function getShapePath(shape) {

	var x = shape.x,
			y = shape.y,
			width = shape.width,
			height = shape.height;

	var shapePath = [['M', x, y], ['l', width, 0], ['l', 0, height], ['l', -width, 0], ['z']];

	return (0, _RenderUtil.componentsToPath)(shapePath);
};

DefaultRenderer.prototype.getConnectionPath = function getConnectionPath(connection) {
	var waypoints = connection.waypoints;

	var idx,
			point,
			connectionPath = [];

	for (idx = 0; point = waypoints[idx]; idx++) {

		// take invisible docking into account
		// when creating the path
		point = point.original || point;

		connectionPath.push([idx === 0 ? 'M' : 'L', point.x, point.y]);
	}

	return (0, _RenderUtil.componentsToPath)(connectionPath);
};

DefaultRenderer.$inject = ['eventBus', 'styles'];

},{"32":32,"54":54,"58":58,"79":79}],34:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Styles;

var _minDash = _dereq_(59);

/**
 * A component that manages shape styles
 */
function Styles() {

	var defaultTraits = {

		'no-fill': {
			fill: 'none'
		},
		'no-border': {
			strokeOpacity: 0.0
		},
		'no-events': {
			pointerEvents: 'none'
		}
	};

	var self = this;

	/**
	 * Builds a style definition from a className, a list of traits and an object of additional attributes.
	 *
	 * @param  {String} className
	 * @param  {Array<String>} traits
	 * @param  {Object} additionalAttrs
	 *
	 * @return {Object} the style defintion
	 */
	this.cls = function (className, traits, additionalAttrs) {
		var attrs = this.style(traits, additionalAttrs);

		return (0, _minDash.assign)(attrs, { 'class': className });
	};

	/**
	 * Builds a style definition from a list of traits and an object of additional attributes.
	 *
	 * @param  {Array<String>} traits
	 * @param  {Object} additionalAttrs
	 *
	 * @return {Object} the style defintion
	 */
	this.style = function (traits, additionalAttrs) {

		if (!(0, _minDash.isArray)(traits) && !additionalAttrs) {
			additionalAttrs = traits;
			traits = [];
		}

		var attrs = (0, _minDash.reduce)(traits, function (attrs, t) {
			return (0, _minDash.assign)(attrs, defaultTraits[t] || {});
		}, {});

		return additionalAttrs ? (0, _minDash.assign)(attrs, additionalAttrs) : attrs;
	};

	this.computeStyle = function (custom, traits, defaultStyles) {
		if (!(0, _minDash.isArray)(traits)) {
			defaultStyles = traits;
			traits = [];
		}

		return self.style(traits || [], (0, _minDash.assign)({}, defaultStyles, custom || {}));
	};
}

},{"59":59}],35:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _DefaultRenderer = _dereq_(33);

var _DefaultRenderer2 = _interopRequireDefault(_DefaultRenderer);

var _Styles = _dereq_(34);

var _Styles2 = _interopRequireDefault(_Styles);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	__init__: ['defaultRenderer'],
	defaultRenderer: ['type', _DefaultRenderer2.default],
	styles: ['type', _Styles2.default]
};

},{"33":33,"34":34}],36:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = InteractionEvents;

var _minDash = _dereq_(59);

var _minDom = _dereq_(60);

var _Mouse = _dereq_(52);

var _tinySvg = _dereq_(79);

var _RenderUtil = _dereq_(54);

function allowAll(e) {
	return true;
}

var LOW_PRIORITY = 500;

/**
 * A plugin that provides interaction events for diagram elements.
 *
 * It emits the following events:
 *
 *   * element.hover
 *   * element.out
 *   * element.click
 *   * element.dblclick
 *   * element.mousedown
 *   * element.contextmenu
 *
 * Each event is a tuple { element, gfx, originalEvent }.
 *
 * Canceling the event via Event#preventDefault()
 * prevents the original DOM operation.
 *
 * @param {EventBus} eventBus
 */
function InteractionEvents(eventBus, elementRegistry, styles) {

	var HIT_STYLE = styles.cls('djs-hit', ['no-fill', 'no-border'], {
		stroke: 'white',
		strokeWidth: 15
	});

	/**
	 * Fire an interaction event.
	 *
	 * @param {String} type local event name, e.g. element.click.
	 * @param {DOMEvent} event native event
	 * @param {djs.model.Base} [element] the diagram element to emit the event on;
	 *                                   defaults to the event target
	 */
	function fire(type, event, element) {

		if (isIgnored(type, event)) {
			return;
		}

		var target, gfx, returnValue;

		if (!element) {
			target = event.delegateTarget || event.target;

			if (target) {
				gfx = target;
				element = elementRegistry.get(gfx);
			}
		} else {
			gfx = elementRegistry.getGraphics(element);
		}

		if (!gfx || !element) {
			return;
		}

		returnValue = eventBus.fire(type, {
			element: element,
			gfx: gfx,
			originalEvent: event
		});

		if (returnValue === false) {
			event.stopPropagation();
			event.preventDefault();
		}
	}

	// TODO(nikku): document this
	var handlers = {};

	function mouseHandler(localEventName) {
		return handlers[localEventName];
	}

	function isIgnored(localEventName, event) {

		var filter = ignoredFilters[localEventName] || _Mouse.isPrimaryButton;

		// only react on left mouse button interactions
		// except for interaction events that are enabled
		// for secundary mouse button
		return !filter(event);
	}

	var bindings = {
		mouseover: 'element.hover',
		mouseout: 'element.out',
		click: 'element.click',
		dblclick: 'element.dblclick',
		mousedown: 'element.mousedown',
		mouseup: 'element.mouseup',
		contextmenu: 'element.contextmenu'
	};

	var ignoredFilters = {
		'element.contextmenu': allowAll
	};

	// manual event trigger

	/**
	 * Trigger an interaction event (based on a native dom event)
	 * on the target shape or connection.
	 *
	 * @param {String} eventName the name of the triggered DOM event
	 * @param {MouseEvent} event
	 * @param {djs.model.Base} targetElement
	 */
	function triggerMouseEvent(eventName, event, targetElement) {

		// i.e. element.mousedown...
		var localEventName = bindings[eventName];

		if (!localEventName) {
			throw new Error('unmapped DOM event name <' + eventName + '>');
		}

		return fire(localEventName, event, targetElement);
	}

	var elementSelector = 'svg, .djs-element';

	// event registration

	function registerEvent(node, event, localEvent, ignoredFilter) {

		var handler = handlers[localEvent] = function (event) {
			fire(localEvent, event);
		};

		if (ignoredFilter) {
			ignoredFilters[localEvent] = ignoredFilter;
		}

		handler.$delegate = _minDom.delegate.bind(node, elementSelector, event, handler);
	}

	function unregisterEvent(node, event, localEvent) {

		var handler = mouseHandler(localEvent);

		if (!handler) {
			return;
		}

		_minDom.delegate.unbind(node, event, handler.$delegate);
	}

	function registerEvents(svg) {
		(0, _minDash.forEach)(bindings, function (val, key) {
			registerEvent(svg, key, val);
		});
	}

	function unregisterEvents(svg) {
		(0, _minDash.forEach)(bindings, function (val, key) {
			unregisterEvent(svg, key, val);
		});
	}

	eventBus.on('canvas.destroy', function (event) {
		unregisterEvents(event.svg);
	});

	eventBus.on('canvas.init', function (event) {
		registerEvents(event.svg);
	});

	eventBus.on(['shape.added', 'connection.added'], function (event) {
		var element = event.element,
				gfx = event.gfx,
				hit;

		if (element.waypoints) {
			hit = (0, _RenderUtil.createLine)(element.waypoints);
		} else {
			hit = (0, _tinySvg.create)('rect');
			(0, _tinySvg.attr)(hit, {
				x: 0,
				y: 0,
				width: element.width,
				height: element.height
			});
		}

		(0, _tinySvg.attr)(hit, HIT_STYLE);

		(0, _tinySvg.append)(gfx, hit);
	});

	// Update djs-hit on change.
	// A low priortity is necessary, because djs-hit of labels has to be updated
	// after the label bounds have been updated in the renderer.
	eventBus.on('shape.changed', LOW_PRIORITY, function (event) {

		var element = event.element,
				gfx = event.gfx,
				hit = (0, _minDom.query)('.djs-hit', gfx);

		(0, _tinySvg.attr)(hit, {
			width: element.width,
			height: element.height
		});
	});

	eventBus.on('connection.changed', function (event) {

		var element = event.element,
				gfx = event.gfx,
				hit = (0, _minDom.query)('.djs-hit', gfx);

		(0, _RenderUtil.updateLine)(hit, element.waypoints);
	});

	// API

	this.fire = fire;

	this.triggerMouseEvent = triggerMouseEvent;

	this.mouseHandler = mouseHandler;

	this.registerEvent = registerEvent;
	this.unregisterEvent = unregisterEvent;
}

InteractionEvents.$inject = ['eventBus', 'elementRegistry', 'styles'];

/**
 * An event indicating that the mouse hovered over an element
 *
 * @event element.hover
 *
 * @type {Object}
 * @property {djs.model.Base} element
 * @property {SVGElement} gfx
 * @property {Event} originalEvent
 */

/**
 * An event indicating that the mouse has left an element
 *
 * @event element.out
 *
 * @type {Object}
 * @property {djs.model.Base} element
 * @property {SVGElement} gfx
 * @property {Event} originalEvent
 */

/**
 * An event indicating that the mouse has clicked an element
 *
 * @event element.click
 *
 * @type {Object}
 * @property {djs.model.Base} element
 * @property {SVGElement} gfx
 * @property {Event} originalEvent
 */

/**
 * An event indicating that the mouse has double clicked an element
 *
 * @event element.dblclick
 *
 * @type {Object}
 * @property {djs.model.Base} element
 * @property {SVGElement} gfx
 * @property {Event} originalEvent
 */

/**
 * An event indicating that the mouse has gone down on an element.
 *
 * @event element.mousedown
 *
 * @type {Object}
 * @property {djs.model.Base} element
 * @property {SVGElement} gfx
 * @property {Event} originalEvent
 */

/**
 * An event indicating that the mouse has gone up on an element.
 *
 * @event element.mouseup
 *
 * @type {Object}
 * @property {djs.model.Base} element
 * @property {SVGElement} gfx
 * @property {Event} originalEvent
 */

/**
 * An event indicating that the context menu action is triggered
 * via mouse or touch controls.
 *
 * @event element.contextmenu
 *
 * @type {Object}
 * @property {djs.model.Base} element
 * @property {SVGElement} gfx
 * @property {Event} originalEvent
 */

},{"52":52,"54":54,"59":59,"60":60,"79":79}],37:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _InteractionEvents = _dereq_(36);

var _InteractionEvents2 = _interopRequireDefault(_InteractionEvents);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	__init__: ['interactionEvents'],
	interactionEvents: ['type', _InteractionEvents2.default]
};

},{"36":36}],38:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Outline;

var _Elements = _dereq_(48);

var _tinySvg = _dereq_(79);

var _minDom = _dereq_(60);

var _minDash = _dereq_(59);

var LOW_PRIORITY = 500;

/**
 * @class
 *
 * A plugin that adds an outline to shapes and connections that may be activated and styled
 * via CSS classes.
 *
 * @param {EventBus} eventBus
 * @param {Styles} styles
 * @param {ElementRegistry} elementRegistry
 */
function Outline(eventBus, styles, elementRegistry) {

	this.offset = 6;

	var OUTLINE_STYLE = styles.cls('djs-outline', ['no-fill']);

	var self = this;

	function createOutline(gfx, bounds) {
		var outline = (0, _tinySvg.create)('rect');

		(0, _tinySvg.attr)(outline, (0, _minDash.assign)({
			x: 10,
			y: 10,
			width: 100,
			height: 100
		}, OUTLINE_STYLE));

		(0, _tinySvg.append)(gfx, outline);

		return outline;
	}

	// A low priortity is necessary, because outlines of labels have to be updated
	// after the label bounds have been updated in the renderer.
	eventBus.on(['shape.added', 'shape.changed'], LOW_PRIORITY, function (event) {
		var element = event.element,
				gfx = event.gfx;

		var outline = (0, _minDom.query)('.djs-outline', gfx);

		if (!outline) {
			outline = createOutline(gfx, element);
		}

		self.updateShapeOutline(outline, element);
	});

	eventBus.on(['connection.added', 'connection.changed'], function (event) {
		var element = event.element,
				gfx = event.gfx;

		var outline = (0, _minDom.query)('.djs-outline', gfx);

		if (!outline) {
			outline = createOutline(gfx, element);
		}

		self.updateConnectionOutline(outline, element);
	});
}

/**
 * Updates the outline of a shape respecting the dimension of the
 * element and an outline offset.
 *
 * @param  {SVGElement} outline
 * @param  {djs.model.Base} element
 */
Outline.prototype.updateShapeOutline = function (outline, element) {

	(0, _tinySvg.attr)(outline, {
		x: -this.offset,
		y: -this.offset,
		width: element.width + this.offset * 2,
		height: element.height + this.offset * 2
	});
};

/**
 * Updates the outline of a connection respecting the bounding box of
 * the connection and an outline offset.
 *
 * @param  {SVGElement} outline
 * @param  {djs.model.Base} element
 */
Outline.prototype.updateConnectionOutline = function (outline, connection) {

	var bbox = (0, _Elements.getBBox)(connection);

	(0, _tinySvg.attr)(outline, {
		x: bbox.x - this.offset,
		y: bbox.y - this.offset,
		width: bbox.width + this.offset * 2,
		height: bbox.height + this.offset * 2
	});
};

Outline.$inject = ['eventBus', 'styles', 'elementRegistry'];

},{"48":48,"59":59,"60":60,"79":79}],39:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _Outline = _dereq_(38);

var _Outline2 = _interopRequireDefault(_Outline);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	__init__: ['outline'],
	outline: ['type', _Outline2.default]
};

},{"38":38}],40:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Overlays;

var _minDash = _dereq_(59);

var _minDom = _dereq_(60);

var _Elements = _dereq_(48);

var _IdGenerator = _dereq_(51);

var _IdGenerator2 = _interopRequireDefault(_IdGenerator);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// document wide unique overlay ids
var ids = new _IdGenerator2.default('ov');

var LOW_PRIORITY = 500;

/**
 * A service that allows users to attach overlays to diagram elements.
 *
 * The overlay service will take care of overlay positioning during updates.
 *
 * @example
 *
 * // add a pink badge on the top left of the shape
 * overlays.add(someShape, {
 *   position: {
 *     top: -5,
 *     left: -5
 *   },
 *   html: '<div style="width: 10px; background: fuchsia; color: white;">0</div>'
 * });
 *
 * // or add via shape id
 *
 * overlays.add('some-element-id', {
 *   position: {
 *     top: -5,
 *     left: -5
 *   }
 *   html: '<div style="width: 10px; background: fuchsia; color: white;">0</div>'
 * });
 *
 * // or add with optional type
 *
 * overlays.add(someShape, 'badge', {
 *   position: {
 *     top: -5,
 *     left: -5
 *   }
 *   html: '<div style="width: 10px; background: fuchsia; color: white;">0</div>'
 * });
 *
 *
 * // remove an overlay
 *
 * var id = overlays.add(...);
 * overlays.remove(id);
 *
 *
 * You may configure overlay defaults during tool by providing a `config` module
 * with `overlays.defaults` as an entry:
 *
 * {
 *   overlays: {
 *     defaults: {
 *       show: {
 *         minZoom: 0.7,
 *         maxZoom: 5.0
 *       },
 *       scale: {
 *         min: 1
 *       }
 *     }
 * }
 *
 * @param {Object} config
 * @param {EventBus} eventBus
 * @param {Canvas} canvas
 * @param {ElementRegistry} elementRegistry
 */
function Overlays(config, eventBus, canvas, elementRegistry) {

	this._eventBus = eventBus;
	this._canvas = canvas;
	this._elementRegistry = elementRegistry;

	this._ids = ids;

	this._overlayDefaults = (0, _minDash.assign)({
		// no show constraints
		show: null,

		// always scale
		scale: true
	}, config && config.defaults);

	/**
	 * Mapping overlayId -> overlay
	 */
	this._overlays = {};

	/**
	 * Mapping elementId -> overlay container
	 */
	this._overlayContainers = [];

	// root html element for all overlays
	this._overlayRoot = createRoot(canvas.getContainer());

	this._init();
}

Overlays.$inject = ['config.overlays', 'eventBus', 'canvas', 'elementRegistry'];

/**
 * Returns the overlay with the specified id or a list of overlays
 * for an element with a given type.
 *
 * @example
 *
 * // return the single overlay with the given id
 * overlays.get('some-id');
 *
 * // return all overlays for the shape
 * overlays.get({ element: someShape });
 *
 * // return all overlays on shape with type 'badge'
 * overlays.get({ element: someShape, type: 'badge' });
 *
 * // shape can also be specified as id
 * overlays.get({ element: 'element-id', type: 'badge' });
 *
 *
 * @param {Object} search
 * @param {String} [search.id]
 * @param {String|djs.model.Base} [search.element]
 * @param {String} [search.type]
 *
 * @return {Object|Array<Object>} the overlay(s)
 */
Overlays.prototype.get = function (search) {

	if ((0, _minDash.isString)(search)) {
		search = { id: search };
	}

	if ((0, _minDash.isString)(search.element)) {
		search.element = this._elementRegistry.get(search.element);
	}

	if (search.element) {
		var container = this._getOverlayContainer(search.element, true);

		// return a list of overlays when searching by element (+type)
		if (container) {
			return search.type ? (0, _minDash.filter)(container.overlays, (0, _minDash.matchPattern)({ type: search.type })) : container.overlays.slice();
		} else {
			return [];
		}
	} else if (search.type) {
		return (0, _minDash.filter)(this._overlays, (0, _minDash.matchPattern)({ type: search.type }));
	} else {
		// return single element when searching by id
		return search.id ? this._overlays[search.id] : null;
	}
};

/**
 * Adds a HTML overlay to an element.
 *
 * @param {String|djs.model.Base}   element   attach overlay to this shape
 * @param {String}                  [type]    optional type to assign to the overlay
 * @param {Object}                  overlay   the overlay configuration
 *
 * @param {String|DOMElement}       overlay.html                 html element to use as an overlay
 * @param {Object}                  [overlay.show]               show configuration
 * @param {Number}                  [overlay.show.minZoom]       minimal zoom level to show the overlay
 * @param {Number}                  [overlay.show.maxZoom]       maximum zoom level to show the overlay
 * @param {Object}                  overlay.position             where to attach the overlay
 * @param {Number}                  [overlay.position.left]      relative to element bbox left attachment
 * @param {Number}                  [overlay.position.top]       relative to element bbox top attachment
 * @param {Number}                  [overlay.position.bottom]    relative to element bbox bottom attachment
 * @param {Number}                  [overlay.position.right]     relative to element bbox right attachment
 * @param {Boolean|Object}          [overlay.scale=true]         false to preserve the same size regardless of
 *                                                               diagram zoom
 * @param {Number}                  [overlay.scale.min]
 * @param {Number}                  [overlay.scale.max]
 *
 * @return {String}                 id that may be used to reference the overlay for update or removal
 */
Overlays.prototype.add = function (element, type, overlay) {

	if ((0, _minDash.isObject)(type)) {
		overlay = type;
		type = null;
	}

	if (!element.id) {
		element = this._elementRegistry.get(element);
	}

	if (!overlay.position) {
		throw new Error('must specifiy overlay position');
	}

	if (!overlay.html) {
		throw new Error('must specifiy overlay html');
	}

	if (!element) {
		throw new Error('invalid element specified');
	}

	var id = this._ids.next();

	overlay = (0, _minDash.assign)({}, this._overlayDefaults, overlay, {
		id: id,
		type: type,
		element: element,
		html: overlay.html
	});

	this._addOverlay(overlay);

	return id;
};

/**
 * Remove an overlay with the given id or all overlays matching the given filter.
 *
 * @see Overlays#get for filter options.
 *
 * @param {String} [id]
 * @param {Object} [filter]
 */
Overlays.prototype.remove = function (filter) {

	var overlays = this.get(filter) || [];

	if (!(0, _minDash.isArray)(overlays)) {
		overlays = [overlays];
	}

	var self = this;

	(0, _minDash.forEach)(overlays, function (overlay) {

		var container = self._getOverlayContainer(overlay.element, true);

		if (overlay) {
			(0, _minDom.remove)(overlay.html);
			(0, _minDom.remove)(overlay.htmlContainer);

			delete overlay.htmlContainer;
			delete overlay.element;

			delete self._overlays[overlay.id];
		}

		if (container) {
			var idx = container.overlays.indexOf(overlay);
			if (idx !== -1) {
				container.overlays.splice(idx, 1);
			}
		}
	});
};

Overlays.prototype.show = function () {
	setVisible(this._overlayRoot);
};

Overlays.prototype.hide = function () {
	setVisible(this._overlayRoot, false);
};

Overlays.prototype.clear = function () {
	this._overlays = {};

	this._overlayContainers = [];

	(0, _minDom.clear)(this._overlayRoot);
};

Overlays.prototype._updateOverlayContainer = function (container) {
	var element = container.element,
			html = container.html;

	// update container left,top according to the elements x,y coordinates
	// this ensures we can attach child elements relative to this container

	var x = element.x,
			y = element.y;

	if (element.waypoints) {
		var bbox = (0, _Elements.getBBox)(element);
		x = bbox.x;
		y = bbox.y;
	}

	setPosition(html, x, y);

	(0, _minDom.attr)(container.html, 'data-container-id', element.id);
};

Overlays.prototype._updateOverlay = function (overlay) {

	var position = overlay.position,
			htmlContainer = overlay.htmlContainer,
			element = overlay.element;

	// update overlay html relative to shape because
	// it is already positioned on the element

	// update relative
	var left = position.left,
			top = position.top;

	if (position.right !== undefined) {

		var width;

		if (element.waypoints) {
			width = (0, _Elements.getBBox)(element).width;
		} else {
			width = element.width;
		}

		left = position.right * -1 + width;
	}

	if (position.bottom !== undefined) {

		var height;

		if (element.waypoints) {
			height = (0, _Elements.getBBox)(element).height;
		} else {
			height = element.height;
		}

		top = position.bottom * -1 + height;
	}

	setPosition(htmlContainer, left || 0, top || 0);
};

Overlays.prototype._createOverlayContainer = function (element) {
	var html = (0, _minDom.domify)('<div class="djs-overlays" style="position: absolute" />');

	this._overlayRoot.appendChild(html);

	var container = {
		html: html,
		element: element,
		overlays: []
	};

	this._updateOverlayContainer(container);

	this._overlayContainers.push(container);

	return container;
};

Overlays.prototype._updateRoot = function (viewbox) {
	var scale = viewbox.scale || 1;

	var matrix = 'matrix(' + [scale, 0, 0, scale, -1 * viewbox.x * scale, -1 * viewbox.y * scale].join(',') + ')';

	setTransform(this._overlayRoot, matrix);
};

Overlays.prototype._getOverlayContainer = function (element, raw) {
	var container = (0, _minDash.find)(this._overlayContainers, function (c) {
		return c.element === element;
	});

	if (!container && !raw) {
		return this._createOverlayContainer(element);
	}

	return container;
};

Overlays.prototype._addOverlay = function (overlay) {

	var id = overlay.id,
			element = overlay.element,
			html = overlay.html,
			htmlContainer,
			overlayContainer;

	// unwrap jquery (for those who need it)
	if (html.get && html.constructor.prototype.jquery) {
		html = html.get(0);
	}

	// create proper html elements from
	// overlay HTML strings
	if ((0, _minDash.isString)(html)) {
		html = (0, _minDom.domify)(html);
	}

	overlayContainer = this._getOverlayContainer(element);

	htmlContainer = (0, _minDom.domify)('<div class="djs-overlay" data-overlay-id="' + id + '" style="position: absolute">');

	htmlContainer.appendChild(html);

	if (overlay.type) {
		(0, _minDom.classes)(htmlContainer).add('djs-overlay-' + overlay.type);
	}

	overlay.htmlContainer = htmlContainer;

	overlayContainer.overlays.push(overlay);
	overlayContainer.html.appendChild(htmlContainer);

	this._overlays[id] = overlay;

	this._updateOverlay(overlay);
	this._updateOverlayVisibilty(overlay, this._canvas.viewbox());
};

Overlays.prototype._updateOverlayVisibilty = function (overlay, viewbox) {
	var show = overlay.show,
			minZoom = show && show.minZoom,
			maxZoom = show && show.maxZoom,
			htmlContainer = overlay.htmlContainer,
			visible = true;

	if (show) {
		if ((0, _minDash.isDefined)(minZoom) && minZoom > viewbox.scale || (0, _minDash.isDefined)(maxZoom) && maxZoom < viewbox.scale) {
			visible = false;
		}

		setVisible(htmlContainer, visible);
	}

	this._updateOverlayScale(overlay, viewbox);
};

Overlays.prototype._updateOverlayScale = function (overlay, viewbox) {
	var shouldScale = overlay.scale,
			minScale,
			maxScale,
			htmlContainer = overlay.htmlContainer;

	var scale,
			transform = '';

	if (shouldScale !== true) {

		if (shouldScale === false) {
			minScale = 1;
			maxScale = 1;
		} else {
			minScale = shouldScale.min;
			maxScale = shouldScale.max;
		}

		if ((0, _minDash.isDefined)(minScale) && viewbox.scale < minScale) {
			scale = (1 / viewbox.scale || 1) * minScale;
		}

		if ((0, _minDash.isDefined)(maxScale) && viewbox.scale > maxScale) {
			scale = (1 / viewbox.scale || 1) * maxScale;
		}
	}

	if ((0, _minDash.isDefined)(scale)) {
		transform = 'scale(' + scale + ',' + scale + ')';
	}

	setTransform(htmlContainer, transform);
};

Overlays.prototype._updateOverlaysVisibilty = function (viewbox) {

	var self = this;

	(0, _minDash.forEach)(this._overlays, function (overlay) {
		self._updateOverlayVisibilty(overlay, viewbox);
	});
};

Overlays.prototype._init = function () {

	var eventBus = this._eventBus;

	var self = this;

	// scroll/zoom integration

	function updateViewbox(viewbox) {
		self._updateRoot(viewbox);
		self._updateOverlaysVisibilty(viewbox);

		self.show();
	}

	eventBus.on('canvas.viewbox.changing', function (event) {
		self.hide();
	});

	eventBus.on('canvas.viewbox.changed', function (event) {
		updateViewbox(event.viewbox);
	});

	// remove integration

	eventBus.on(['shape.remove', 'connection.remove'], function (e) {
		var element = e.element;
		var overlays = self.get({ element: element });

		(0, _minDash.forEach)(overlays, function (o) {
			self.remove(o.id);
		});

		var container = self._getOverlayContainer(element);

		if (container) {
			(0, _minDom.remove)(container.html);
			var i = self._overlayContainers.indexOf(container);
			if (i !== -1) {
				self._overlayContainers.splice(i, 1);
			}
		}
	});

	// move integration

	eventBus.on('element.changed', LOW_PRIORITY, function (e) {
		var element = e.element;

		var container = self._getOverlayContainer(element, true);

		if (container) {
			(0, _minDash.forEach)(container.overlays, function (overlay) {
				self._updateOverlay(overlay);
			});

			self._updateOverlayContainer(container);
		}
	});

	// marker integration, simply add them on the overlays as classes, too.

	eventBus.on('element.marker.update', function (e) {
		var container = self._getOverlayContainer(e.element, true);
		if (container) {
			(0, _minDom.classes)(container.html)[e.add ? 'add' : 'remove'](e.marker);
		}
	});

	// clear overlays with diagram

	eventBus.on('diagram.clear', this.clear, this);
};

// helpers /////////////////////////////

function createRoot(parent) {
	var root = (0, _minDom.domify)('<div class="djs-overlay-container" style="position: absolute; width: 0; height: 0;" />');
	parent.insertBefore(root, parent.firstChild);

	return root;
}

function setPosition(el, x, y) {
	(0, _minDash.assign)(el.style, { left: x + 'px', top: y + 'px' });
}

function setVisible(el, visible) {
	el.style.display = visible === false ? 'none' : '';
}

function setTransform(el, transform) {

	el.style['transform-origin'] = 'top left';

	['', '-ms-', '-webkit-'].forEach(function (prefix) {
		el.style[prefix + 'transform'] = transform;
	});
}

},{"48":48,"51":51,"59":59,"60":60}],41:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _Overlays = _dereq_(40);

var _Overlays2 = _interopRequireDefault(_Overlays);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	__init__: ['overlays'],
	overlays: ['type', _Overlays2.default]
};

},{"40":40}],42:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Selection;

var _minDash = _dereq_(59);

/**
 * A service that offers the current selection in a diagram.
 * Offers the api to control the selection, too.
 *
 * @class
 *
 * @param {EventBus} eventBus the event bus
 */
function Selection(eventBus) {

	this._eventBus = eventBus;

	this._selectedElements = [];

	var self = this;

	eventBus.on(['shape.remove', 'connection.remove'], function (e) {
		var element = e.element;
		self.deselect(element);
	});

	eventBus.on(['diagram.clear'], function (e) {
		self.select(null);
	});
}

Selection.$inject = ['eventBus'];

Selection.prototype.deselect = function (element) {
	var selectedElements = this._selectedElements;

	var idx = selectedElements.indexOf(element);

	if (idx !== -1) {
		var oldSelection = selectedElements.slice();

		selectedElements.splice(idx, 1);

		this._eventBus.fire('selection.changed', { oldSelection: oldSelection, newSelection: selectedElements });
	}
};

Selection.prototype.get = function () {
	return this._selectedElements;
};

Selection.prototype.isSelected = function (element) {
	return this._selectedElements.indexOf(element) !== -1;
};

/**
 * This method selects one or more elements on the diagram.
 *
 * By passing an additional add parameter you can decide whether or not the element(s)
 * should be added to the already existing selection or not.
 *
 * @method Selection#select
 *
 * @param  {Object|Object[]} elements element or array of elements to be selected
 * @param  {boolean} [add] whether the element(s) should be appended to the current selection, defaults to false
 */
Selection.prototype.select = function (elements, add) {
	var selectedElements = this._selectedElements,
			oldSelection = selectedElements.slice();

	if (!(0, _minDash.isArray)(elements)) {
		elements = elements ? [elements] : [];
	}

	// selection may be cleared by passing an empty array or null
	// to the method
	if (add) {
		(0, _minDash.forEach)(elements, function (element) {
			if (selectedElements.indexOf(element) !== -1) {
				// already selected
				return;
			} else {
				selectedElements.push(element);
			}
		});
	} else {
		this._selectedElements = selectedElements = elements.slice();
	}

	this._eventBus.fire('selection.changed', { oldSelection: oldSelection, newSelection: selectedElements });
};

},{"59":59}],43:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = SelectionBehavior;

var _Mouse = _dereq_(52);

var _minDash = _dereq_(59);

function SelectionBehavior(eventBus, selection, canvas, elementRegistry) {

	eventBus.on('create.end', 500, function (e) {

		// select the created shape after a
		// successful create operation
		if (e.context.canExecute) {
			selection.select(e.context.shape);
		}
	});

	eventBus.on('connect.end', 500, function (e) {

		// select the connect end target
		// after a connect operation
		if (e.context.canExecute && e.context.target) {
			selection.select(e.context.target);
		}
	});

	eventBus.on('shape.move.end', 500, function (e) {
		var previousSelection = e.previousSelection || [];

		var shape = elementRegistry.get(e.context.shape.id);

		// make sure at least the main moved element is being
		// selected after a move operation
		var inSelection = (0, _minDash.find)(previousSelection, function (selectedShape) {
			return shape.id === selectedShape.id;
		});

		if (!inSelection) {
			selection.select(shape);
		}
	});

	// Shift + click selection
	eventBus.on('element.click', function (event) {

		var element = event.element;

		// do not select the root element
		// or connections
		if (element === canvas.getRootElement()) {
			element = null;
		}

		var isSelected = selection.isSelected(element),
				isMultiSelect = selection.get().length > 1;

		// mouse-event: SELECTION_KEY
		var add = (0, _Mouse.hasPrimaryModifier)(event);

		// select OR deselect element in multi selection
		if (isSelected && isMultiSelect) {
			if (add) {
				return selection.deselect(element);
			} else {
				return selection.select(element);
			}
		} else if (!isSelected) {
			selection.select(element, add);
		} else {
			selection.deselect(element);
		}
	});
}

SelectionBehavior.$inject = ['eventBus', 'selection', 'canvas', 'elementRegistry'];

},{"52":52,"59":59}],44:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = SelectionVisuals;

var _minDash = _dereq_(59);

var MARKER_HOVER = 'hover',
		MARKER_SELECTED = 'selected';

/**
 * A plugin that adds a visible selection UI to shapes and connections
 * by appending the <code>hover</code> and <code>selected</code> classes to them.
 *
 * @class
 *
 * Makes elements selectable, too.
 *
 * @param {EventBus} events
 * @param {SelectionService} selection
 * @param {Canvas} canvas
 */
function SelectionVisuals(events, canvas, selection, styles) {

	this._multiSelectionBox = null;

	function addMarker(e, cls) {
		canvas.addMarker(e, cls);
	}

	function removeMarker(e, cls) {
		canvas.removeMarker(e, cls);
	}

	events.on('element.hover', function (event) {
		addMarker(event.element, MARKER_HOVER);
	});

	events.on('element.out', function (event) {
		removeMarker(event.element, MARKER_HOVER);
	});

	events.on('selection.changed', function (event) {

		function deselect(s) {
			removeMarker(s, MARKER_SELECTED);
		}

		function select(s) {
			addMarker(s, MARKER_SELECTED);
		}

		var oldSelection = event.oldSelection,
				newSelection = event.newSelection;

		(0, _minDash.forEach)(oldSelection, function (e) {
			if (newSelection.indexOf(e) === -1) {
				deselect(e);
			}
		});

		(0, _minDash.forEach)(newSelection, function (e) {
			if (oldSelection.indexOf(e) === -1) {
				select(e);
			}
		});
	});
}

SelectionVisuals.$inject = ['eventBus', 'canvas', 'selection', 'styles'];

},{"59":59}],45:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _interactionEvents = _dereq_(37);

var _interactionEvents2 = _interopRequireDefault(_interactionEvents);

var _outline = _dereq_(39);

var _outline2 = _interopRequireDefault(_outline);

var _Selection = _dereq_(42);

var _Selection2 = _interopRequireDefault(_Selection);

var _SelectionVisuals = _dereq_(44);

var _SelectionVisuals2 = _interopRequireDefault(_SelectionVisuals);

var _SelectionBehavior = _dereq_(43);

var _SelectionBehavior2 = _interopRequireDefault(_SelectionBehavior);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	__init__: ['selectionVisuals', 'selectionBehavior'],
	__depends__: [_interactionEvents2.default, _outline2.default],
	selection: ['type', _Selection2.default],
	selectionVisuals: ['type', _SelectionVisuals2.default],
	selectionBehavior: ['type', _SelectionBehavior2.default]
};

},{"37":37,"39":39,"42":42,"43":43,"44":44}],46:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.Base = Base;
exports.Shape = Shape;
exports.Root = Root;
exports.Label = Label;
exports.Connection = Connection;
exports.create = create;

var _minDash = _dereq_(59);

var _inherits = _dereq_(58);

var _inherits2 = _interopRequireDefault(_inherits);

var _objectRefs = _dereq_(74);

var _objectRefs2 = _interopRequireDefault(_objectRefs);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var parentRefs = new _objectRefs2.default({ name: 'children', enumerable: true, collection: true }, { name: 'parent' }),
		labelRefs = new _objectRefs2.default({ name: 'labels', enumerable: true, collection: true }, { name: 'labelTarget' }),
		attacherRefs = new _objectRefs2.default({ name: 'attachers', collection: true }, { name: 'host' }),
		outgoingRefs = new _objectRefs2.default({ name: 'outgoing', collection: true }, { name: 'source' }),
		incomingRefs = new _objectRefs2.default({ name: 'incoming', collection: true }, { name: 'target' });

/**
 * @namespace djs.model
 */

/**
 * @memberOf djs.model
 */

/**
 * The basic graphical representation
 *
 * @class
 *
 * @abstract
 */
function Base() {

	/**
	 * The object that backs up the shape
	 *
	 * @name Base#businessObject
	 * @type Object
	 */
	Object.defineProperty(this, 'businessObject', {
		writable: true
	});

	/**
	 * Single label support, will mapped to multi label array
	 *
	 * @name Base#label
	 * @type Object
	 */
	Object.defineProperty(this, 'label', {
		get: function get() {
			return this.labels[0];
		},
		set: function set(newLabel) {

			var label = this.label,
					labels = this.labels;

			if (!newLabel && label) {
				labels.remove(label);
			} else {
				labels.add(newLabel, 0);
			}
		}
	});

	/**
	 * The parent shape
	 *
	 * @name Base#parent
	 * @type Shape
	 */
	parentRefs.bind(this, 'parent');

	/**
	 * The list of labels
	 *
	 * @name Base#labels
	 * @type Label
	 */
	labelRefs.bind(this, 'labels');

	/**
	 * The list of outgoing connections
	 *
	 * @name Base#outgoing
	 * @type Array<Connection>
	 */
	outgoingRefs.bind(this, 'outgoing');

	/**
	 * The list of incoming connections
	 *
	 * @name Base#incoming
	 * @type Array<Connection>
	 */
	incomingRefs.bind(this, 'incoming');
}

/**
 * A graphical object
 *
 * @class
 * @constructor
 *
 * @extends Base
 */
function Shape() {
	Base.call(this);

	/**
	 * The list of children
	 *
	 * @name Shape#children
	 * @type Array<Base>
	 */
	parentRefs.bind(this, 'children');

	/**
	 * @name Shape#host
	 * @type Shape
	 */
	attacherRefs.bind(this, 'host');

	/**
	 * @name Shape#attachers
	 * @type Shape
	 */
	attacherRefs.bind(this, 'attachers');
}

(0, _inherits2.default)(Shape, Base);

/**
 * A root graphical object
 *
 * @class
 * @constructor
 *
 * @extends Shape
 */
function Root() {
	Shape.call(this);
}

(0, _inherits2.default)(Root, Shape);

/**
 * A label for an element
 *
 * @class
 * @constructor
 *
 * @extends Shape
 */
function Label() {
	Shape.call(this);

	/**
	 * The labeled element
	 *
	 * @name Label#labelTarget
	 * @type Base
	 */
	labelRefs.bind(this, 'labelTarget');
}

(0, _inherits2.default)(Label, Shape);

/**
 * A connection between two elements
 *
 * @class
 * @constructor
 *
 * @extends Base
 */
function Connection() {
	Base.call(this);

	/**
	 * The element this connection originates from
	 *
	 * @name Connection#source
	 * @type Base
	 */
	outgoingRefs.bind(this, 'source');

	/**
	 * The element this connection points to
	 *
	 * @name Connection#target
	 * @type Base
	 */
	incomingRefs.bind(this, 'target');
}

(0, _inherits2.default)(Connection, Base);

var types = {
	connection: Connection,
	shape: Shape,
	label: Label,
	root: Root
};

/**
 * Creates a new model element of the specified type
 *
 * @method create
 *
 * @example
 *
 * var shape1 = Model.create('shape', { x: 10, y: 10, width: 100, height: 100 });
 * var shape2 = Model.create('shape', { x: 210, y: 210, width: 100, height: 100 });
 *
 * var connection = Model.create('connection', { waypoints: [ { x: 110, y: 55 }, {x: 210, y: 55 } ] });
 *
 * @param  {String} type lower-cased model name
 * @param  {Object} attrs attributes to initialize the new model instance with
 *
 * @return {Base} the new model instance
 */
function create(type, attrs) {
	var Type = types[type];
	if (!Type) {
		throw new Error('unknown type: <' + type + '>');
	}
	return (0, _minDash.assign)(new Type(), attrs);
}

},{"58":58,"59":59,"74":74}],47:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.remove = remove;
exports.add = add;
exports.indexOf = indexOf;
/**
 * Failsafe remove an element from a collection
 *
 * @param  {Array<Object>} [collection]
 * @param  {Object} [element]
 *
 * @return {Number} the previous index of the element
 */
function remove(collection, element) {

	if (!collection || !element) {
		return -1;
	}

	var idx = collection.indexOf(element);

	if (idx !== -1) {
		collection.splice(idx, 1);
	}

	return idx;
}

/**
 * Fail save add an element to the given connection, ensuring
 * it does not yet exist.
 *
 * @param {Array<Object>} collection
 * @param {Object} element
 * @param {Number} idx
 */
function add(collection, element, idx) {

	if (!collection || !element) {
		return;
	}

	if (typeof idx !== 'number') {
		idx = -1;
	}

	var currentIdx = collection.indexOf(element);

	if (currentIdx !== -1) {

		if (currentIdx === idx) {
			// nothing to do, position has not changed
			return;
		} else {

			if (idx !== -1) {
				// remove from current position
				collection.splice(currentIdx, 1);
			} else {
				// already exists in collection
				return;
			}
		}
	}

	if (idx !== -1) {
		// insert at specified position
		collection.splice(idx, 0, element);
	} else {
		// push to end
		collection.push(element);
	}
}

/**
 * Fail save get the index of an element in a collection.
 *
 * @param {Array<Object>} collection
 * @param {Object} element
 *
 * @return {Number} the index or -1 if collection or element do
 *                  not exist or the element is not contained.
 */
function indexOf(collection, element) {

	if (!collection || !element) {
		return -1;
	}

	return collection.indexOf(element);
}

},{}],48:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.add = add;
exports.eachElement = eachElement;
exports.selfAndChildren = selfAndChildren;
exports.selfAndDirectChildren = selfAndDirectChildren;
exports.selfAndAllChildren = selfAndAllChildren;
exports.getClosure = getClosure;
exports.getBBox = getBBox;
exports.getEnclosedElements = getEnclosedElements;
exports.getType = getType;

var _minDash = _dereq_(59);

/**
 * Adds an element to a collection and returns true if the
 * element was added.
 *
 * @param {Array<Object>} elements
 * @param {Object} e
 * @param {Boolean} unique
 */
function add(elements, e, unique) {
	var canAdd = !unique || elements.indexOf(e) === -1;

	if (canAdd) {
		elements.push(e);
	}

	return canAdd;
}

/**
 * Iterate over each element in a collection, calling the iterator function `fn`
 * with (element, index, recursionDepth).
 *
 * Recurse into all elements that are returned by `fn`.
 *
 * @param  {Object|Array<Object>} elements
 * @param  {Function} fn iterator function called with (element, index, recursionDepth)
 * @param  {Number} [depth] maximum recursion depth
 */
function eachElement(elements, fn, depth) {

	depth = depth || 0;

	if (!(0, _minDash.isArray)(elements)) {
		elements = [elements];
	}

	(0, _minDash.forEach)(elements, function (s, i) {
		var filter = fn(s, i, depth);

		if ((0, _minDash.isArray)(filter) && filter.length) {
			eachElement(filter, fn, depth + 1);
		}
	});
}

/**
 * Collects self + child elements up to a given depth from a list of elements.
 *
 * @param  {djs.model.Base|Array<djs.model.Base>} elements the elements to select the children from
 * @param  {Boolean} unique whether to return a unique result set (no duplicates)
 * @param  {Number} maxDepth the depth to search through or -1 for infinite
 *
 * @return {Array<djs.model.Base>} found elements
 */
function selfAndChildren(elements, unique, maxDepth) {
	var result = [],
			processedChildren = [];

	eachElement(elements, function (element, i, depth) {
		add(result, element, unique);

		var children = element.children;

		// max traversal depth not reached yet
		if (maxDepth === -1 || depth < maxDepth) {

			// children exist && children not yet processed
			if (children && add(processedChildren, children, unique)) {
				return children;
			}
		}
	});

	return result;
}

/**
 * Return self + direct children for a number of elements
 *
 * @param  {Array<djs.model.Base>} elements to query
 * @param  {Boolean} allowDuplicates to allow duplicates in the result set
 *
 * @return {Array<djs.model.Base>} the collected elements
 */
function selfAndDirectChildren(elements, allowDuplicates) {
	return selfAndChildren(elements, !allowDuplicates, 1);
}

/**
 * Return self + ALL children for a number of elements
 *
 * @param  {Array<djs.model.Base>} elements to query
 * @param  {Boolean} allowDuplicates to allow duplicates in the result set
 *
 * @return {Array<djs.model.Base>} the collected elements
 */
function selfAndAllChildren(elements, allowDuplicates) {
	return selfAndChildren(elements, !allowDuplicates, -1);
}

/**
 * Gets the the closure for all selected elements,
 * their enclosed children and connections.
 *
 * @param {Array<djs.model.Base>} elements
 * @param {Boolean} [isTopLevel=true]
 * @param {Object} [existingClosure]
 *
 * @return {Object} newClosure
 */
function getClosure(elements, isTopLevel, closure) {

	if ((0, _minDash.isUndefined)(isTopLevel)) {
		isTopLevel = true;
	}

	if ((0, _minDash.isObject)(isTopLevel)) {
		closure = isTopLevel;
		isTopLevel = true;
	}

	closure = closure || {};

	var allShapes = copyObject(closure.allShapes),
			allConnections = copyObject(closure.allConnections),
			enclosedElements = copyObject(closure.enclosedElements),
			enclosedConnections = copyObject(closure.enclosedConnections);

	var topLevel = copyObject(closure.topLevel, isTopLevel && (0, _minDash.groupBy)(elements, function (e) {
		return e.id;
	}));

	function handleConnection(c) {
		if (topLevel[c.source.id] && topLevel[c.target.id]) {
			topLevel[c.id] = [c];
		}

		// not enclosed as a child, but maybe logically
		// (connecting two moved elements?)
		if (allShapes[c.source.id] && allShapes[c.target.id]) {
			enclosedConnections[c.id] = enclosedElements[c.id] = c;
		}

		allConnections[c.id] = c;
	}

	function handleElement(element) {

		enclosedElements[element.id] = element;

		if (element.waypoints) {
			// remember connection
			enclosedConnections[element.id] = allConnections[element.id] = element;
		} else {
			// remember shape
			allShapes[element.id] = element;

			// remember all connections
			(0, _minDash.forEach)(element.incoming, handleConnection);

			(0, _minDash.forEach)(element.outgoing, handleConnection);

			// recurse into children
			return element.children;
		}
	}

	eachElement(elements, handleElement);

	return {
		allShapes: allShapes,
		allConnections: allConnections,
		topLevel: topLevel,
		enclosedConnections: enclosedConnections,
		enclosedElements: enclosedElements
	};
}

/**
 * Returns the surrounding bbox for all elements in
 * the array or the element primitive.
 *
 * @param {Array<djs.model.Shape>|djs.model.Shape} elements
 * @param {Boolean} stopRecursion
 */
function getBBox(elements, stopRecursion) {

	stopRecursion = !!stopRecursion;
	if (!(0, _minDash.isArray)(elements)) {
		elements = [elements];
	}

	var minX, minY, maxX, maxY;

	(0, _minDash.forEach)(elements, function (element) {

		// If element is a connection the bbox must be computed first
		var bbox = element;
		if (element.waypoints && !stopRecursion) {
			bbox = getBBox(element.waypoints, true);
		}

		var x = bbox.x,
				y = bbox.y,
				height = bbox.height || 0,
				width = bbox.width || 0;

		if (x < minX || minX === undefined) {
			minX = x;
		}
		if (y < minY || minY === undefined) {
			minY = y;
		}

		if (x + width > maxX || maxX === undefined) {
			maxX = x + width;
		}
		if (y + height > maxY || maxY === undefined) {
			maxY = y + height;
		}
	});

	return {
		x: minX,
		y: minY,
		height: maxY - minY,
		width: maxX - minX
	};
}

/**
 * Returns all elements that are enclosed from the bounding box.
 *
 *   * If bbox.(width|height) is not specified the method returns
 *     all elements with element.x/y > bbox.x/y
 *   * If only bbox.x or bbox.y is specified, method return all elements with
 *     e.x > bbox.x or e.y > bbox.y
 *
 * @param {Array<djs.model.Shape>} elements List of Elements to search through
 * @param {djs.model.Shape} bbox the enclosing bbox.
 *
 * @return {Array<djs.model.Shape>} enclosed elements
 */
function getEnclosedElements(elements, bbox) {

	var filteredElements = {};

	(0, _minDash.forEach)(elements, function (element) {

		var e = element;

		if (e.waypoints) {
			e = getBBox(e);
		}

		if (!(0, _minDash.isNumber)(bbox.y) && e.x > bbox.x) {
			filteredElements[element.id] = element;
		}
		if (!(0, _minDash.isNumber)(bbox.x) && e.y > bbox.y) {
			filteredElements[element.id] = element;
		}
		if (e.x > bbox.x && e.y > bbox.y) {
			if ((0, _minDash.isNumber)(bbox.width) && (0, _minDash.isNumber)(bbox.height) && e.width + e.x < bbox.width + bbox.x && e.height + e.y < bbox.height + bbox.y) {

				filteredElements[element.id] = element;
			} else if (!(0, _minDash.isNumber)(bbox.width) || !(0, _minDash.isNumber)(bbox.height)) {
				filteredElements[element.id] = element;
			}
		}
	});

	return filteredElements;
}

function getType(element) {

	if ('waypoints' in element) {
		return 'connection';
	}

	if ('x' in element) {
		return 'shape';
	}

	return 'root';
}

// helpers ///////////////////////////////

function copyObject(src1, src2) {
	return (0, _minDash.assign)({}, src1 || {}, src2 || {});
}

},{"59":59}],49:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.getOriginal = getOriginal;
exports.stopPropagation = stopPropagation;
exports.toPoint = toPoint;
function __stopPropagation(event) {
	if (!event || typeof event.stopPropagation !== 'function') {
		return;
	}

	event.stopPropagation();
}

function getOriginal(event) {
	return event.originalEvent || event.srcEvent;
}

function stopPropagation(event, immediate) {
	__stopPropagation(event, immediate);
	__stopPropagation(getOriginal(event), immediate);
}

function toPoint(event) {

	if (event.pointers && event.pointers.length) {
		event = event.pointers[0];
	}

	if (event.touches && event.touches.length) {
		event = event.touches[0];
	}

	return event ? {
		x: event.clientX,
		y: event.clientY
	} : null;
}

},{}],50:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.getVisual = getVisual;
exports.getChildren = getChildren;

var _minDom = _dereq_(60);

/**
 * SVGs for elements are generated by the {@link GraphicsFactory}.
 *
 * This utility gives quick access to the important semantic
 * parts of an element.
 */

/**
 * Returns the visual part of a diagram element
 *
 * @param {Snap<SVGElement>} gfx
 *
 * @return {Snap<SVGElement>}
 */
function getVisual(gfx) {
	return (0, _minDom.query)('.djs-visual', gfx);
}

/**
 * Returns the children for a given diagram element.
 *
 * @param {Snap<SVGElement>} gfx
 * @return {Snap<SVGElement>}
 */
function getChildren(gfx) {
	return gfx.parentNode.childNodes[1];
}

},{"60":60}],51:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = IdGenerator;
/**
 * Util that provides unique IDs.
 *
 * @class djs.util.IdGenerator
 * @constructor
 * @memberOf djs.util
 *
 * The ids can be customized via a given prefix and contain a random value to avoid collisions.
 *
 * @param {String} prefix a prefix to prepend to generated ids (for better readability)
 */
function IdGenerator(prefix) {

	this._counter = 0;
	this._prefix = (prefix ? prefix + '-' : '') + Math.floor(Math.random() * 1000000000) + '-';
}

/**
 * Returns a next unique ID.
 *
 * @method djs.util.IdGenerator#next
 *
 * @returns {String} the id
 */
IdGenerator.prototype.next = function () {
	return this._prefix + ++this._counter;
};

},{}],52:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.isMac = undefined;

var _Platform = _dereq_(53);

Object.defineProperty(exports, 'isMac', {
	enumerable: true,
	get: function get() {
		return _Platform.isMac;
	}
});
exports.isPrimaryButton = isPrimaryButton;
exports.hasPrimaryModifier = hasPrimaryModifier;
exports.hasSecondaryModifier = hasSecondaryModifier;

var _Event = _dereq_(49);

function isPrimaryButton(event) {
	// button === 0 -> left áka primary mouse button
	return !((0, _Event.getOriginal)(event) || event).button;
}

function hasPrimaryModifier(event) {
	var originalEvent = (0, _Event.getOriginal)(event) || event;

	if (!isPrimaryButton(event)) {
		return false;
	}

	// Use alt as primary modifier key for mac OS
	if ((0, _Platform.isMac)()) {
		return originalEvent.metaKey;
	} else {
		return originalEvent.ctrlKey;
	}
}

function hasSecondaryModifier(event) {
	var originalEvent = (0, _Event.getOriginal)(event) || event;

	return isPrimaryButton(event) && originalEvent.shiftKey;
}

},{"49":49,"53":53}],53:[function(_dereq_,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.isMac = isMac;
function isMac() {
	return (/mac/i.test(navigator.platform)
	);
}

},{}],54:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.componentsToPath = componentsToPath;
exports.toSVGPoints = toSVGPoints;
exports.createLine = createLine;
exports.updateLine = updateLine;

var _tinySvg = _dereq_(79);

function componentsToPath(elements) {
	return elements.join(',').replace(/,?([A-z]),?/g, '$1');
}

function toSVGPoints(points) {
	var result = '';

	for (var i = 0, p; p = points[i]; i++) {
		result += p.x + ',' + p.y + ' ';
	}

	return result;
}

function createLine(points, attrs) {

	var line = (0, _tinySvg.create)('polyline');
	(0, _tinySvg.attr)(line, { points: toSVGPoints(points) });

	if (attrs) {
		(0, _tinySvg.attr)(line, attrs);
	}

	return line;
}

function updateLine(gfx, points) {
	(0, _tinySvg.attr)(gfx, { points: toSVGPoints(points) });

	return gfx;
}

},{"79":79}],55:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.transform = transform;
exports.translate = translate;
exports.rotate = rotate;
exports.scale = scale;

var _tinySvg = _dereq_(79);

/**
 * @param {<SVGElement>} element
 * @param {Number} x
 * @param {Number} y
 * @param {Number} angle
 * @param {Number} amount
 */
function transform(gfx, x, y, angle, amount) {
	var translate = (0, _tinySvg.createTransform)();
	translate.setTranslate(x, y);

	var rotate = (0, _tinySvg.createTransform)();
	rotate.setRotate(angle, 0, 0);

	var scale = (0, _tinySvg.createTransform)();
	scale.setScale(amount || 1, amount || 1);

	(0, _tinySvg.transform)(gfx, [translate, rotate, scale]);
}

/**
 * @param {SVGElement} element
 * @param {Number} x
 * @param {Number} y
 */
function translate(gfx, x, y) {
	var translate = (0, _tinySvg.createTransform)();
	translate.setTranslate(x, y);

	(0, _tinySvg.transform)(gfx, translate);
}

/**
 * @param {SVGElement} element
 * @param {Number} angle
 */
function rotate(gfx, angle) {
	var rotate = (0, _tinySvg.createTransform)();
	rotate.setRotate(angle, 0, 0);

	(0, _tinySvg.transform)(gfx, rotate);
}

/**
 * @param {SVGElement} element
 * @param {Number} amount
 */
function scale(gfx, amount) {
	var scale = (0, _tinySvg.createTransform)();
	scale.setScale(amount, amount);

	(0, _tinySvg.transform)(gfx, scale);
}

},{"79":79}],56:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Text;

var _minDash = _dereq_(59);

var _tinySvg = _dereq_(79);

var DEFAULT_BOX_PADDING = 0;

var DEFAULT_LABEL_SIZE = {
	width: 150,
	height: 50
};

function parseAlign(align) {

	var parts = align.split('-');

	return {
		horizontal: parts[0] || 'center',
		vertical: parts[1] || 'top'
	};
}

function parsePadding(padding) {

	if ((0, _minDash.isObject)(padding)) {
		return (0, _minDash.assign)({ top: 0, left: 0, right: 0, bottom: 0 }, padding);
	} else {
		return {
			top: padding,
			left: padding,
			right: padding,
			bottom: padding
		};
	}
}

function getTextBBox(text, fakeText) {

	fakeText.textContent = text;

	var textBBox;

	try {
		var bbox,
				emptyLine = text === '';

		// add dummy text, when line is empty to
		// determine correct height
		fakeText.textContent = emptyLine ? 'dummy' : text;

		textBBox = fakeText.getBBox();

		// take text rendering related horizontal
		// padding into account
		bbox = {
			width: textBBox.width + textBBox.x * 2,
			height: textBBox.height
		};

		if (emptyLine) {
			// correct width
			bbox.width = 0;
		}

		return bbox;
	} catch (e) {
		return { width: 0, height: 0 };
	}
}

/**
 * Layout the next line and return the layouted element.
 *
 * Alters the lines passed.
 *
 * @param  {Array<String>} lines
 * @return {Object} the line descriptor, an object { width, height, text }
 */
function layoutNext(lines, maxWidth, fakeText) {

	var originalLine = lines.shift(),
			fitLine = originalLine;

	var textBBox;

	for (;;) {
		textBBox = getTextBBox(fitLine, fakeText);

		textBBox.width = fitLine ? textBBox.width : 0;

		// try to fit
		if (fitLine === ' ' || fitLine === '' || textBBox.width < Math.round(maxWidth) || fitLine.length < 2) {
			return fit(lines, fitLine, originalLine, textBBox);
		}

		fitLine = shortenLine(fitLine, textBBox.width, maxWidth);
	}
}

function fit(lines, fitLine, originalLine, textBBox) {
	if (fitLine.length < originalLine.length) {
		var remainder = originalLine.slice(fitLine.length).trim();

		lines.unshift(remainder);
	}

	return {
		width: textBBox.width,
		height: textBBox.height,
		text: fitLine
	};
}

/**
 * Shortens a line based on spacing and hyphens.
 * Returns the shortened result on success.
 *
 * @param  {String} line
 * @param  {Number} maxLength the maximum characters of the string
 * @return {String} the shortened string
 */
function semanticShorten(line, maxLength) {
	var parts = line.split(/(\s|-)/g),
			part,
			shortenedParts = [],
			length = 0;

	// try to shorten via spaces + hyphens
	if (parts.length > 1) {
		while (part = parts.shift()) {
			if (part.length + length < maxLength) {
				shortenedParts.push(part);
				length += part.length;
			} else {
				// remove previous part, too if hyphen does not fit anymore
				if (part === '-') {
					shortenedParts.pop();
				}

				break;
			}
		}
	}

	return shortenedParts.join('');
}

function shortenLine(line, width, maxWidth) {
	var length = Math.max(line.length * (maxWidth / width), 1);

	// try to shorten semantically (i.e. based on spaces and hyphens)
	var shortenedLine = semanticShorten(line, length);

	if (!shortenedLine) {

		// force shorten by cutting the long word
		shortenedLine = line.slice(0, Math.max(Math.round(length - 1), 1));
	}

	return shortenedLine;
}

function getHelperSvg() {
	var helperSvg = document.getElementById('helper-svg');

	if (!helperSvg) {
		helperSvg = (0, _tinySvg.create)('svg');

		(0, _tinySvg.attr)(helperSvg, {
			id: 'helper-svg',
			width: 0,
			height: 0,
			style: 'visibility: hidden; position: fixed'
		});

		document.body.appendChild(helperSvg);
	}

	return helperSvg;
}

/**
 * Creates a new label utility
 *
 * @param {Object} config
 * @param {Dimensions} config.size
 * @param {Number} config.padding
 * @param {Object} config.style
 * @param {String} config.align
 */
function Text(config) {

	this._config = (0, _minDash.assign)({}, {
		size: DEFAULT_LABEL_SIZE,
		padding: DEFAULT_BOX_PADDING,
		style: {},
		align: 'center-top'
	}, config || {});
}

/**
 * Returns the layouted text as an SVG element.
 *
 * @param {String} text
 * @param {Object} options
 *
 * @return {SVGElement}
 */
Text.prototype.createText = function (text, options) {
	return this.layoutText(text, options).element;
};

/**
 * Returns a labels layouted dimensions.
 *
 * @param {String} text to layout
 * @param {Object} options
 *
 * @return {Dimensions}
 */
Text.prototype.getDimensions = function (text, options) {
	return this.layoutText(text, options).dimensions;
};

/**
 * Creates and returns a label and its bounding box.
 *
 * @method Text#createText
 *
 * @param {String} text the text to render on the label
 * @param {Object} options
 * @param {String} options.align how to align in the bounding box.
 *                               Any of { 'center-middle', 'center-top' },
 *                               defaults to 'center-top'.
 * @param {String} options.style style to be applied to the text
 * @param {boolean} options.fitBox indicates if box will be recalculated to
 *                                 fit text
 *
 * @return {Object} { element, dimensions }
 */
Text.prototype.layoutText = function (text, options) {
	var box = (0, _minDash.assign)({}, this._config.size, options.box),
			style = (0, _minDash.assign)({}, this._config.style, options.style),
			align = parseAlign(options.align || this._config.align),
			padding = parsePadding(options.padding !== undefined ? options.padding : this._config.padding),
			fitBox = options.fitBox || false;

	var lineHeight = getLineHeight(style);

	var lines = text.split(/\r?\n/g),
			layouted = [];

	var maxWidth = box.width - padding.left - padding.right;

	// ensure correct rendering by attaching helper text node to invisible SVG
	var helperText = (0, _tinySvg.create)('text');
	(0, _tinySvg.attr)(helperText, { x: 0, y: 0 });
	(0, _tinySvg.attr)(helperText, style);

	var helperSvg = getHelperSvg();

	(0, _tinySvg.append)(helperSvg, helperText);

	while (lines.length) {
		layouted.push(layoutNext(lines, maxWidth, helperText));
	}

	if (align.vertical === 'middle') {
		padding.top = padding.bottom = 0;
	}

	var totalHeight = (0, _minDash.reduce)(layouted, function (sum, line, idx) {
		return sum + (lineHeight || line.height);
	}, 0) + padding.top + padding.bottom;

	var maxLineWidth = (0, _minDash.reduce)(layouted, function (sum, line, idx) {
		return line.width > sum ? line.width : sum;
	}, 0);

	// the y position of the next line
	var y = padding.top;

	if (align.vertical === 'middle') {
		y += (box.height - totalHeight) / 2;
	}

	// magic number initial offset
	y -= (lineHeight || layouted[0].height) / 4;

	var textElement = (0, _tinySvg.create)('text');

	(0, _tinySvg.attr)(textElement, style);

	// layout each line taking into account that parent
	// shape might resize to fit text size
	(0, _minDash.forEach)(layouted, function (line) {

		var x;

		y += lineHeight || line.height;

		switch (align.horizontal) {
			case 'left':
				x = padding.left;
				break;

			case 'right':
				x = (fitBox ? maxLineWidth : maxWidth) - padding.right - line.width;
				break;

			default:
				// aka center
				x = Math.max(((fitBox ? maxLineWidth : maxWidth) - line.width) / 2 + padding.left, 0);
		}

		var tspan = (0, _tinySvg.create)('tspan');
		(0, _tinySvg.attr)(tspan, { x: x, y: y });

		tspan.textContent = line.text;

		(0, _tinySvg.append)(textElement, tspan);
	});

	(0, _tinySvg.remove)(helperText);

	var dimensions = {
		width: maxLineWidth,
		height: totalHeight
	};

	return {
		dimensions: dimensions,
		element: textElement
	};
};

function getLineHeight(style) {
	if ('fontSize' in style && 'lineHeight' in style) {
		return style.lineHeight * parseInt(style.fontSize, 10);
	}
}

},{"59":59,"79":79}],57:[function(_dereq_,module,exports){
'use strict';

var _typeof2 = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

Object.defineProperty(exports, '__esModule', { value: true });

var CLASS_PATTERN = /^class /;

function isClass(fn) {
	return CLASS_PATTERN.test(fn.toString());
}

function isArray(obj) {
	return Object.prototype.toString.call(obj) === '[object Array]';
}

function annotate() {
	var args = Array.prototype.slice.call(arguments);

	if (args.length === 1 && isArray(args[0])) {
		args = args[0];
	}

	var fn = args.pop();

	fn.$inject = args;

	return fn;
}

// Current limitations:
// - can't put into "function arg" comments
// function /* (no parenthesis like this) */ (){}
// function abc( /* xx (no parenthesis like this) */ a, b) {}
//
// Just put the comment before function or inside:
// /* (((this is fine))) */ function(a, b) {}
// function abc(a) { /* (((this is fine))) */}
//
// - can't reliably auto-annotate constructor; we'll match the
// first constructor(...) pattern found which may be the one
// of a nested class, too.

var CONSTRUCTOR_ARGS = /constructor\s*[^(]*\(\s*([^)]*)\)/m;
var FN_ARGS = /^function\s*[^(]*\(\s*([^)]*)\)/m;
var FN_ARG = /\/\*([^*]*)\*\//m;

function parse(fn) {

	if (typeof fn !== 'function') {
		throw new Error('Cannot annotate "' + fn + '". Expected a function!');
	}

	var match = fn.toString().match(isClass(fn) ? CONSTRUCTOR_ARGS : FN_ARGS);

	// may parse class without constructor
	if (!match) {
		return [];
	}

	return match[1] && match[1].split(',').map(function (arg) {
		match = arg.match(FN_ARG);
		return match ? match[1].trim() : arg.trim();
	}) || [];
}

function Module() {
	var providers = [];

	this.factory = function (name, factory) {
		providers.push([name, 'factory', factory]);
		return this;
	};

	this.value = function (name, value) {
		providers.push([name, 'value', value]);
		return this;
	};

	this.type = function (name, type) {
		providers.push([name, 'type', type]);
		return this;
	};

	this.forEach = function (iterator) {
		providers.forEach(iterator);
	};
}

var _typeof = typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol" ? function (obj) {
	return typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
} : function (obj) {
	return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
};

function _toConsumableArray(arr) {
	if (Array.isArray(arr)) {
		for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
			arr2[i] = arr[i];
		}return arr2;
	} else {
		return Array.from(arr);
	}
}

function Injector(modules, parent) {
	parent = parent || {
		get: function get(name, strict) {
			currentlyResolving.push(name);

			if (strict === false) {
				return null;
			} else {
				throw error('No provider for "' + name + '"!');
			}
		}
	};

	var currentlyResolving = [];
	var providers = this._providers = Object.create(parent._providers || null);
	var instances = this._instances = Object.create(null);

	var self = instances.injector = this;

	var error = function error(msg) {
		var stack = currentlyResolving.join(' -> ');
		currentlyResolving.length = 0;
		return new Error(stack ? msg + ' (Resolving: ' + stack + ')' : msg);
	};

	/**
	 * Return a named service.
	 *
	 * @param {String} name
	 * @param {Boolean} [strict=true] if false, resolve missing services to null
	 *
	 * @return {Object}
	 */
	var get = function get(name, strict) {
		if (!providers[name] && name.indexOf('.') !== -1) {
			var parts = name.split('.');
			var pivot = get(parts.shift());

			while (parts.length) {
				pivot = pivot[parts.shift()];
			}

			return pivot;
		}

		if (hasProp(instances, name)) {
			return instances[name];
		}

		if (hasProp(providers, name)) {
			if (currentlyResolving.indexOf(name) !== -1) {
				currentlyResolving.push(name);
				throw error('Cannot resolve circular dependency!');
			}

			currentlyResolving.push(name);
			instances[name] = providers[name][0](providers[name][1]);
			currentlyResolving.pop();

			return instances[name];
		}

		return parent.get(name, strict);
	};

	var fnDef = function fnDef(fn) {
		var locals = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

		if (typeof fn !== 'function') {
			if (isArray(fn)) {
				fn = annotate(fn.slice());
			} else {
				throw new Error('Cannot invoke "' + fn + '". Expected a function!');
			}
		}

		var inject = fn.$inject || parse(fn);
		var dependencies = inject.map(function (dep) {
			if (hasProp(locals, dep)) {
				return locals[dep];
			} else {
				return get(dep);
			}
		});

		return {
			fn: fn,
			dependencies: dependencies
		};
	};

	var instantiate = function instantiate(Type) {
		var _fnDef = fnDef(Type),
				dependencies = _fnDef.dependencies,
				fn = _fnDef.fn;

		return new (Function.prototype.bind.apply(fn, [null].concat(_toConsumableArray(dependencies))))();
	};

	var invoke = function invoke(func, context, locals) {
		var _fnDef2 = fnDef(func, locals),
				dependencies = _fnDef2.dependencies,
				fn = _fnDef2.fn;

		return fn.call.apply(fn, [context].concat(_toConsumableArray(dependencies)));
	};

	var createPrivateInjectorFactory = function createPrivateInjectorFactory(privateChildInjector) {
		return annotate(function (key) {
			return privateChildInjector.get(key);
		});
	};

	var createChild = function createChild(modules, forceNewInstances) {
		if (forceNewInstances && forceNewInstances.length) {
			var fromParentModule = Object.create(null);
			var matchedScopes = Object.create(null);

			var privateInjectorsCache = [];
			var privateChildInjectors = [];
			var privateChildFactories = [];

			var provider;
			var cacheIdx;
			var privateChildInjector;
			var privateChildInjectorFactory;
			for (var name in providers) {
				provider = providers[name];

				if (forceNewInstances.indexOf(name) !== -1) {
					if (provider[2] === 'private') {
						cacheIdx = privateInjectorsCache.indexOf(provider[3]);
						if (cacheIdx === -1) {
							privateChildInjector = provider[3].createChild([], forceNewInstances);
							privateChildInjectorFactory = createPrivateInjectorFactory(privateChildInjector);
							privateInjectorsCache.push(provider[3]);
							privateChildInjectors.push(privateChildInjector);
							privateChildFactories.push(privateChildInjectorFactory);
							fromParentModule[name] = [privateChildInjectorFactory, name, 'private', privateChildInjector];
						} else {
							fromParentModule[name] = [privateChildFactories[cacheIdx], name, 'private', privateChildInjectors[cacheIdx]];
						}
					} else {
						fromParentModule[name] = [provider[2], provider[1]];
					}
					matchedScopes[name] = true;
				}

				if ((provider[2] === 'factory' || provider[2] === 'type') && provider[1].$scope) {
					/* jshint -W083 */
					forceNewInstances.forEach(function (scope) {
						if (provider[1].$scope.indexOf(scope) !== -1) {
							fromParentModule[name] = [provider[2], provider[1]];
							matchedScopes[scope] = true;
						}
					});
				}
			}

			forceNewInstances.forEach(function (scope) {
				if (!matchedScopes[scope]) {
					throw new Error('No provider for "' + scope + '". Cannot use provider from the parent!');
				}
			});

			modules.unshift(fromParentModule);
		}

		return new Injector(modules, self);
	};

	var factoryMap = {
		factory: invoke,
		type: instantiate,
		value: function value(_value) {
			return _value;
		}
	};

	modules.forEach(function (module) {

		function arrayUnwrap(type, value) {
			if (type !== 'value' && isArray(value)) {
				value = annotate(value.slice());
			}

			return value;
		}

		// TODO(vojta): handle wrong inputs (modules)
		if (module instanceof Module) {
			module.forEach(function (provider) {
				var name = provider[0];
				var type = provider[1];
				var value = provider[2];

				providers[name] = [factoryMap[type], arrayUnwrap(type, value), type];
			});
		} else if ((typeof module === 'undefined' ? 'undefined' : _typeof(module)) === 'object') {
			if (module.__exports__) {
				var clonedModule = Object.keys(module).reduce(function (m, key) {
					if (key.substring(0, 2) !== '__') {
						m[key] = module[key];
					}
					return m;
				}, Object.create(null));

				var privateInjector = new Injector((module.__modules__ || []).concat([clonedModule]), self);
				var getFromPrivateInjector = annotate(function (key) {
					return privateInjector.get(key);
				});
				module.__exports__.forEach(function (key) {
					providers[key] = [getFromPrivateInjector, key, 'private', privateInjector];
				});
			} else {
				Object.keys(module).forEach(function (name) {
					if (module[name][2] === 'private') {
						providers[name] = module[name];
						return;
					}

					var type = module[name][0];
					var value = module[name][1];

					providers[name] = [factoryMap[type], arrayUnwrap(type, value), type];
				});
			}
		}
	});

	// public API
	this.get = get;
	this.invoke = invoke;
	this.instantiate = instantiate;
	this.createChild = createChild;
}

// helpers /////////////////

function hasProp(obj, prop) {
	return Object.hasOwnProperty.call(obj, prop);
}

exports.annotate = annotate;
exports.Module = Module;
exports.Injector = Injector;

},{}],58:[function(_dereq_,module,exports){
'use strict';

if (typeof Object.create === 'function') {
	// implementation from standard node.js 'util' module
	module.exports = function inherits(ctor, superCtor) {
		ctor.super_ = superCtor;
		ctor.prototype = Object.create(superCtor.prototype, {
			constructor: {
				value: ctor,
				enumerable: false,
				writable: true,
				configurable: true
			}
		});
	};
} else {
	// old school shim for old browsers
	module.exports = function inherits(ctor, superCtor) {
		ctor.super_ = superCtor;
		var TempCtor = function TempCtor() {};
		TempCtor.prototype = superCtor.prototype;
		ctor.prototype = new TempCtor();
		ctor.prototype.constructor = ctor;
	};
}

},{}],59:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

/**
 * Flatten array, one level deep.
 *
 * @param {Array<?>} arr
 *
 * @return {Array<?>}
 */
function flatten(arr) {
	return Array.prototype.concat.apply([], arr);
}

var nativeToString = Object.prototype.toString;
var nativeHasOwnProperty = Object.prototype.hasOwnProperty;

function isUndefined(obj) {
	return obj === undefined;
}

function isDefined(obj) {
	return obj !== undefined;
}

function isNil(obj) {
	return obj == null;
}

function isArray(obj) {
	return nativeToString.call(obj) === '[object Array]';
}

function isObject(obj) {
	return nativeToString.call(obj) === '[object Object]';
}

function isNumber(obj) {
	return nativeToString.call(obj) === '[object Number]';
}

function isFunction(obj) {
	return nativeToString.call(obj) === '[object Function]';
}

function isString(obj) {
	return nativeToString.call(obj) === '[object String]';
}

/**
 * Ensure collection is an array.
 *
 * @param {Object} obj
 */
function ensureArray(obj) {

	if (isArray(obj)) {
		return;
	}

	throw new Error('must supply array');
}

/**
 * Return true, if target owns a property with the given key.
 *
 * @param {Object} target
 * @param {String} key
 *
 * @return {Boolean}
 */
function has(target, key) {
	return nativeHasOwnProperty.call(target, key);
}

/**
 * Find element in collection.
 *
 * @param  {Array|Object} collection
 * @param  {Function|Object} matcher
 *
 * @return {Object}
 */
function find(collection, matcher) {

	matcher = toMatcher(matcher);

	var match;

	forEach(collection, function (val, key) {
		if (matcher(val, key)) {
			match = val;

			return false;
		}
	});

	return match;
}

/**
 * Find element index in collection.
 *
 * @param  {Array|Object} collection
 * @param  {Function} matcher
 *
 * @return {Object}
 */
function findIndex(collection, matcher) {

	matcher = toMatcher(matcher);

	var idx = isArray(collection) ? -1 : undefined;

	forEach(collection, function (val, key) {
		if (matcher(val, key)) {
			idx = key;

			return false;
		}
	});

	return idx;
}

/**
 * Find element in collection.
 *
 * @param  {Array|Object} collection
 * @param  {Function} matcher
 *
 * @return {Array} result
 */
function filter(collection, matcher) {

	var result = [];

	forEach(collection, function (val, key) {
		if (matcher(val, key)) {
			result.push(val);
		}
	});

	return result;
}

/**
 * Iterate over collection; returning something
 * (non-undefined) will stop iteration.
 *
 * @param  {Array|Object} collection
 * @param  {Function} iterator
 *
 * @return {Object} return result that stopped the iteration
 */
function forEach(collection, iterator) {

	if (isUndefined(collection)) {
		return;
	}

	var convertKey = isArray(collection) ? toNum : identity;

	for (var key in collection) {

		if (has(collection, key)) {
			var val = collection[key];

			var result = iterator(val, convertKey(key));

			if (result === false) {
				return;
			}
		}
	}
}

/**
 * Return collection without element.
 *
 * @param  {Array} arr
 * @param  {Function} matcher
 *
 * @return {Array}
 */
function without(arr, matcher) {

	if (isUndefined(arr)) {
		return [];
	}

	ensureArray(arr);

	matcher = toMatcher(matcher);

	return arr.filter(function (el, idx) {
		return !matcher(el, idx);
	});
}

/**
 * Reduce collection, returning a single result.
 *
 * @param  {Object|Array} collection
 * @param  {Function} iterator
 * @param  {Any} result
 *
 * @return {Any} result returned from last iterator
 */
function reduce(collection, iterator, result) {

	forEach(collection, function (value, idx) {
		result = iterator(result, value, idx);
	});

	return result;
}

/**
 * Return true if every element in the collection
 * matches the criteria.
 *
 * @param  {Object|Array} collection
 * @param  {Function} matcher
 *
 * @return {Boolean}
 */
function every(collection, matcher) {

	return reduce(collection, function (matches, val, key) {
		return matches && matcher(val, key);
	}, true);
}

/**
 * Return true if some elements in the collection
 * match the criteria.
 *
 * @param  {Object|Array} collection
 * @param  {Function} matcher
 *
 * @return {Boolean}
 */
function some(collection, matcher) {

	return !!find(collection, matcher);
}

/**
 * Transform a collection into another collection
 * by piping each member through the given fn.
 *
 * @param  {Object|Array}   collection
 * @param  {Function} fn
 *
 * @return {Array} transformed collection
 */
function map(collection, fn) {

	var result = [];

	forEach(collection, function (val, key) {
		result.push(fn(val, key));
	});

	return result;
}

/**
 * Get the collections keys.
 *
 * @param  {Object|Array} collection
 *
 * @return {Array}
 */
function keys(collection) {
	return collection && Object.keys(collection) || [];
}

/**
 * Shorthand for `keys(o).length`.
 *
 * @param  {Object|Array} collection
 *
 * @return {Number}
 */
function size(collection) {
	return keys(collection).length;
}

/**
 * Get the values in the collection.
 *
 * @param  {Object|Array} collection
 *
 * @return {Array}
 */
function values(collection) {
	return map(collection, function (val) {
		return val;
	});
}

/**
 * Group collection members by attribute.
 *
 * @param  {Object|Array} collection
 * @param  {Function} extractor
 *
 * @return {Object} map with { attrValue => [ a, b, c ] }
 */
function groupBy(collection, extractor) {
	var grouped = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	extractor = toExtractor(extractor);

	forEach(collection, function (val) {
		var discriminator = extractor(val) || '_';

		var group = grouped[discriminator];

		if (!group) {
			group = grouped[discriminator] = [];
		}

		group.push(val);
	});

	return grouped;
}

function uniqueBy(extractor) {

	extractor = toExtractor(extractor);

	var grouped = {};

	for (var _len = arguments.length, collections = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
		collections[_key - 1] = arguments[_key];
	}

	forEach(collections, function (c) {
		return groupBy(c, extractor, grouped);
	});

	var result = map(grouped, function (val, key) {
		return val[0];
	});

	return result;
}

var unionBy = uniqueBy;

/**
 * Sort collection by criteria.
 *
 * @param  {Object|Array} collection
 * @param  {String|Function} extractor
 *
 * @return {Array}
 */
function sortBy(collection, extractor) {

	extractor = toExtractor(extractor);

	var sorted = [];

	forEach(collection, function (value, key) {
		var disc = extractor(value, key);

		var entry = {
			d: disc,
			v: value
		};

		for (var idx = 0; idx < sorted.length; idx++) {
			var d = sorted[idx].d;

			if (disc < d) {
				sorted.splice(idx, 0, entry);
				return;
			}
		}

		// not inserted, append (!)
		sorted.push(entry);
	});

	return map(sorted, function (e) {
		return e.v;
	});
}

/**
 * Create an object pattern matcher.
 *
 * @example
 *
 * const matcher = matchPattern({ id: 1 });
 *
 * var element = find(elements, matcher);
 *
 * @param  {Object} pattern
 *
 * @return {Function} matcherFn
 */
function matchPattern(pattern) {

	return function (el) {

		return every(pattern, function (val, key) {
			return el[key] === val;
		});
	};
}

function toExtractor(extractor) {
	return isFunction(extractor) ? extractor : function (e) {
		return e[extractor];
	};
}

function toMatcher(matcher) {
	return isFunction(matcher) ? matcher : function (e) {
		return e === matcher;
	};
}

function identity(arg) {
	return arg;
}

function toNum(arg) {
	return Number(arg);
}

/**
 * Debounce fn, calling it only once if
 * the given time elapsed between calls.
 *
 * @param  {Function} fn
 * @param  {Number} timeout
 *
 * @return {Function} debounced function
 */
function debounce(fn, timeout) {

	var timer;

	var lastArgs;
	var lastThis;

	var lastNow;

	function fire() {

		var now = Date.now();

		var scheduledDiff = lastNow + timeout - now;

		if (scheduledDiff > 0) {
			return schedule(scheduledDiff);
		}

		fn.apply(lastThis, lastArgs);

		timer = lastNow = lastArgs = lastThis = undefined;
	}

	function schedule(timeout) {
		timer = setTimeout(fire, timeout);
	}

	return function () {

		lastNow = Date.now();

		for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
			args[_key] = arguments[_key];
		}

		lastArgs = args;
		lastThis = this;

		// ensure an execution is scheduled
		if (!timer) {
			schedule(timeout);
		}
	};
}

/**
 * Throttle fn, calling at most once
 * in the given interval.
 *
 * @param  {Function} fn
 * @param  {Number} interval
 *
 * @return {Function} throttled function
 */
function throttle(fn, interval) {

	var throttling = false;

	return function () {

		if (throttling) {
			return;
		}

		fn.apply(undefined, arguments);
		throttling = true;

		setTimeout(function () {
			throttling = false;
		}, interval);
	};
}

/**
 * Bind function against target <this>.
 *
 * @param  {Function} fn
 * @param  {Object}   target
 *
 * @return {Function} bound function
 */
function bind(fn, target) {
	return fn.bind(target);
}

var _extends = Object.assign || function (target) {
	for (var i = 1; i < arguments.length; i++) {
		var source = arguments[i];for (var key in source) {
			if (Object.prototype.hasOwnProperty.call(source, key)) {
				target[key] = source[key];
			}
		}
	}return target;
};

/**
 * Convenience wrapper for `Object.assign`.
 *
 * @param {Object} target
 * @param {...Object} others
 *
 * @return {Object} the target
 */
function assign(target) {
	for (var _len = arguments.length, others = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
		others[_key - 1] = arguments[_key];
	}

	return _extends.apply(undefined, [target].concat(others));
}

/**
 * Pick given properties from the target object.
 *
 * @param {Object} target
 * @param {Array} properties
 *
 * @return {Object} target
 */
function pick(target, properties) {

	var result = {};

	var obj = Object(target);

	forEach(properties, function (prop) {

		if (prop in obj) {
			result[prop] = target[prop];
		}
	});

	return result;
}

/**
 * Pick all target properties, excluding the given ones.
 *
 * @param {Object} target
 * @param {Array} properties
 *
 * @return {Object} target
 */
function omit(target, properties) {

	var result = {};

	var obj = Object(target);

	forEach(obj, function (prop, key) {

		if (properties.indexOf(key) === -1) {
			result[key] = prop;
		}
	});

	return result;
}

/**
 * Recursively merge `...sources` into given target.
 *
 * Does support merging objects; does not support merging arrays.
 *
 * @param {Object} target
 * @param {...Object} sources
 *
 * @return {Object} the target
 */
function merge(target) {
	for (var _len2 = arguments.length, sources = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
		sources[_key2 - 1] = arguments[_key2];
	}

	if (!sources.length) {
		return target;
	}

	forEach(sources, function (source) {

		// skip non-obj sources, i.e. null
		if (!source || !isObject(source)) {
			return;
		}

		forEach(source, function (sourceVal, key) {

			var targetVal = target[key];

			if (isObject(sourceVal)) {

				if (!isObject(targetVal)) {
					// override target[key] with object
					targetVal = {};
				}

				target[key] = merge(targetVal, sourceVal);
			} else {
				target[key] = sourceVal;
			}
		});
	});

	return target;
}

exports.flatten = flatten;
exports.find = find;
exports.findIndex = findIndex;
exports.filter = filter;
exports.forEach = forEach;
exports.without = without;
exports.reduce = reduce;
exports.every = every;
exports.some = some;
exports.map = map;
exports.keys = keys;
exports.size = size;
exports.values = values;
exports.groupBy = groupBy;
exports.uniqueBy = uniqueBy;
exports.unionBy = unionBy;
exports.sortBy = sortBy;
exports.matchPattern = matchPattern;
exports.debounce = debounce;
exports.throttle = throttle;
exports.bind = bind;
exports.isUndefined = isUndefined;
exports.isDefined = isDefined;
exports.isNil = isNil;
exports.isArray = isArray;
exports.isObject = isObject;
exports.isNumber = isNumber;
exports.isFunction = isFunction;
exports.isString = isString;
exports.ensureArray = ensureArray;
exports.has = has;
exports.assign = assign;
exports.pick = pick;
exports.omit = omit;
exports.merge = merge;

},{}],60:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

/**
 * Set attribute `name` to `val`, or get attr `name`.
 *
 * @param {Element} el
 * @param {String} name
 * @param {String} [val]
 * @api public
 */
function attr(el, name, val) {
	// get
	if (arguments.length == 2) {
		return el.getAttribute(name);
	}

	// remove
	if (val === null) {
		return el.removeAttribute(name);
	}

	// set
	el.setAttribute(name, val);

	return el;
}

var indexOf = [].indexOf;

var indexof = function indexof(arr, obj) {
	if (indexOf) return arr.indexOf(obj);
	for (var i = 0; i < arr.length; ++i) {
		if (arr[i] === obj) return i;
	}
	return -1;
};

/**
 * Taken from https://github.com/component/classes
 *
 * Without the component bits.
 */

/**
 * Whitespace regexp.
 */

var re = /\s+/;

/**
 * toString reference.
 */

var toString = Object.prototype.toString;

/**
 * Wrap `el` in a `ClassList`.
 *
 * @param {Element} el
 * @return {ClassList}
 * @api public
 */

function classes(el) {
	return new ClassList(el);
}

/**
 * Initialize a new ClassList for `el`.
 *
 * @param {Element} el
 * @api private
 */

function ClassList(el) {
	if (!el || !el.nodeType) {
		throw new Error('A DOM element reference is required');
	}
	this.el = el;
	this.list = el.classList;
}

/**
 * Add class `name` if not already present.
 *
 * @param {String} name
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.add = function (name) {
	// classList
	if (this.list) {
		this.list.add(name);
		return this;
	}

	// fallback
	var arr = this.array();
	var i = indexof(arr, name);
	if (!~i) arr.push(name);
	this.el.className = arr.join(' ');
	return this;
};

/**
 * Remove class `name` when present, or
 * pass a regular expression to remove
 * any which match.
 *
 * @param {String|RegExp} name
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.remove = function (name) {
	if ('[object RegExp]' == toString.call(name)) {
		return this.removeMatching(name);
	}

	// classList
	if (this.list) {
		this.list.remove(name);
		return this;
	}

	// fallback
	var arr = this.array();
	var i = indexof(arr, name);
	if (~i) arr.splice(i, 1);
	this.el.className = arr.join(' ');
	return this;
};

/**
 * Remove all classes matching `re`.
 *
 * @param {RegExp} re
 * @return {ClassList}
 * @api private
 */

ClassList.prototype.removeMatching = function (re) {
	var arr = this.array();
	for (var i = 0; i < arr.length; i++) {
		if (re.test(arr[i])) {
			this.remove(arr[i]);
		}
	}
	return this;
};

/**
 * Toggle class `name`, can force state via `force`.
 *
 * For browsers that support classList, but do not support `force` yet,
 * the mistake will be detected and corrected.
 *
 * @param {String} name
 * @param {Boolean} force
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.toggle = function (name, force) {
	// classList
	if (this.list) {
		if ('undefined' !== typeof force) {
			if (force !== this.list.toggle(name, force)) {
				this.list.toggle(name); // toggle again to correct
			}
		} else {
			this.list.toggle(name);
		}
		return this;
	}

	// fallback
	if ('undefined' !== typeof force) {
		if (!force) {
			this.remove(name);
		} else {
			this.add(name);
		}
	} else {
		if (this.has(name)) {
			this.remove(name);
		} else {
			this.add(name);
		}
	}

	return this;
};

/**
 * Return an array of classes.
 *
 * @return {Array}
 * @api public
 */

ClassList.prototype.array = function () {
	var className = this.el.getAttribute('class') || '';
	var str = className.replace(/^\s+|\s+$/g, '');
	var arr = str.split(re);
	if ('' === arr[0]) arr.shift();
	return arr;
};

/**
 * Check if class `name` is present.
 *
 * @param {String} name
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.has = ClassList.prototype.contains = function (name) {
	return this.list ? this.list.contains(name) : !!~indexof(this.array(), name);
};

/**
 * Remove all children from the given element.
 */
function clear(el) {

	var c;

	while (el.childNodes.length) {
		c = el.childNodes[0];
		el.removeChild(c);
	}

	return el;
}

/**
 * Element prototype.
 */

var proto = Element.prototype;

/**
 * Vendor function.
 */

var vendor = proto.matchesSelector || proto.webkitMatchesSelector || proto.mozMatchesSelector || proto.msMatchesSelector || proto.oMatchesSelector;

/**
 * Expose `match()`.
 */

var matchesSelector = match;

/**
 * Match `el` to `selector`.
 *
 * @param {Element} el
 * @param {String} selector
 * @return {Boolean}
 * @api public
 */

function match(el, selector) {
	if (vendor) return vendor.call(el, selector);
	var nodes = el.parentNode.querySelectorAll(selector);
	for (var i = 0; i < nodes.length; ++i) {
		if (nodes[i] == el) return true;
	}
	return false;
}

var closest = function closest(element, selector, checkYoSelf) {
	var parent = checkYoSelf ? element : element.parentNode;

	while (parent && parent !== document) {
		if (matchesSelector(parent, selector)) return parent;
		parent = parent.parentNode;
	}
};

var bind = window.addEventListener ? 'addEventListener' : 'attachEvent',
		unbind = window.removeEventListener ? 'removeEventListener' : 'detachEvent',
		prefix = bind !== 'addEventListener' ? 'on' : '';

/**
 * Bind `el` event `type` to `fn`.
 *
 * @param {Element} el
 * @param {String} type
 * @param {Function} fn
 * @param {Boolean} capture
 * @return {Function}
 * @api public
 */

var bind_1 = function bind_1(el, type, fn, capture) {
	el[bind](prefix + type, fn, capture || false);
	return fn;
};

/**
 * Unbind `el` event `type`'s callback `fn`.
 *
 * @param {Element} el
 * @param {String} type
 * @param {Function} fn
 * @param {Boolean} capture
 * @return {Function}
 * @api public
 */

var unbind_1 = function unbind_1(el, type, fn, capture) {
	el[unbind](prefix + type, fn, capture || false);
	return fn;
};

var componentEvent = {
	bind: bind_1,
	unbind: unbind_1
};

/**
 * Module dependencies.
 */

/**
 * Delegate event `type` to `selector`
 * and invoke `fn(e)`. A callback function
 * is returned which may be passed to `.unbind()`.
 *
 * @param {Element} el
 * @param {String} selector
 * @param {String} type
 * @param {Function} fn
 * @param {Boolean} capture
 * @return {Function}
 * @api public
 */

// Some events don't bubble, so we want to bind to the capture phase instead
// when delegating.
var forceCaptureEvents = ['focus', 'blur'];

var bind$1 = function bind$1(el, selector, type, fn, capture) {
	if (forceCaptureEvents.indexOf(type) !== -1) capture = true;

	return componentEvent.bind(el, type, function (e) {
		var target = e.target || e.srcElement;
		e.delegateTarget = closest(target, selector, true, el);
		if (e.delegateTarget) fn.call(el, e);
	}, capture);
};

/**
 * Unbind event `type`'s callback `fn`.
 *
 * @param {Element} el
 * @param {String} type
 * @param {Function} fn
 * @param {Boolean} capture
 * @api public
 */

var unbind$1 = function unbind$1(el, type, fn, capture) {
	if (forceCaptureEvents.indexOf(type) !== -1) capture = true;

	componentEvent.unbind(el, type, fn, capture);
};

var delegateEvents = {
	bind: bind$1,
	unbind: unbind$1
};

/**
 * Expose `parse`.
 */

var domify = parse;

/**
 * Tests for browser support.
 */

var innerHTMLBug = false;
var bugTestDiv;
if (typeof document !== 'undefined') {
	bugTestDiv = document.createElement('div');
	// Setup
	bugTestDiv.innerHTML = '  <link/><table></table><a href="/a">a</a><input type="checkbox"/>';
	// Make sure that link elements get serialized correctly by innerHTML
	// This requires a wrapper element in IE
	innerHTMLBug = !bugTestDiv.getElementsByTagName('link').length;
	bugTestDiv = undefined;
}

/**
 * Wrap map from jquery.
 */

var map = {
	legend: [1, '<fieldset>', '</fieldset>'],
	tr: [2, '<table><tbody>', '</tbody></table>'],
	col: [2, '<table><tbody></tbody><colgroup>', '</colgroup></table>'],
	// for script/link/style tags to work in IE6-8, you have to wrap
	// in a div with a non-whitespace character in front, ha!
	_default: innerHTMLBug ? [1, 'X<div>', '</div>'] : [0, '', '']
};

map.td = map.th = [3, '<table><tbody><tr>', '</tr></tbody></table>'];

map.option = map.optgroup = [1, '<select multiple="multiple">', '</select>'];

map.thead = map.tbody = map.colgroup = map.caption = map.tfoot = [1, '<table>', '</table>'];

map.polyline = map.ellipse = map.polygon = map.circle = map.text = map.line = map.path = map.rect = map.g = [1, '<svg xmlns="http://www.w3.org/2000/svg" version="1.1">', '</svg>'];

/**
 * Parse `html` and return a DOM Node instance, which could be a TextNode,
 * HTML DOM Node of some kind (<div> for example), or a DocumentFragment
 * instance, depending on the contents of the `html` string.
 *
 * @param {String} html - HTML string to "domify"
 * @param {Document} doc - The `document` instance to create the Node for
 * @return {DOMNode} the TextNode, DOM Node, or DocumentFragment instance
 * @api private
 */

function parse(html, doc) {
	if ('string' != typeof html) throw new TypeError('String expected');

	// default to the global `document` object
	if (!doc) doc = document;

	// tag name
	var m = /<([\w:]+)/.exec(html);
	if (!m) return doc.createTextNode(html);

	html = html.replace(/^\s+|\s+$/g, ''); // Remove leading/trailing whitespace

	var tag = m[1];

	// body support
	if (tag == 'body') {
		var el = doc.createElement('html');
		el.innerHTML = html;
		return el.removeChild(el.lastChild);
	}

	// wrap map
	var wrap = map[tag] || map._default;
	var depth = wrap[0];
	var prefix = wrap[1];
	var suffix = wrap[2];
	var el = doc.createElement('div');
	el.innerHTML = prefix + html + suffix;
	while (depth--) {
		el = el.lastChild;
	} // one element
	if (el.firstChild == el.lastChild) {
		return el.removeChild(el.firstChild);
	}

	// several elements
	var fragment = doc.createDocumentFragment();
	while (el.firstChild) {
		fragment.appendChild(el.removeChild(el.firstChild));
	}

	return fragment;
}

var proto$1 = typeof Element !== 'undefined' ? Element.prototype : {};
var vendor$1 = proto$1.matches || proto$1.matchesSelector || proto$1.webkitMatchesSelector || proto$1.mozMatchesSelector || proto$1.msMatchesSelector || proto$1.oMatchesSelector;

var matchesSelector$1 = match$1;

/**
 * Match `el` to `selector`.
 *
 * @param {Element} el
 * @param {String} selector
 * @return {Boolean}
 * @api public
 */

function match$1(el, selector) {
	if (!el || el.nodeType !== 1) return false;
	if (vendor$1) return vendor$1.call(el, selector);
	var nodes = el.parentNode.querySelectorAll(selector);
	for (var i = 0; i < nodes.length; i++) {
		if (nodes[i] == el) return true;
	}
	return false;
}

function query(selector, el) {
	el = el || document;

	return el.querySelector(selector);
}

function all(selector, el) {
	el = el || document;

	return el.querySelectorAll(selector);
}

function remove(el) {
	el.parentNode && el.parentNode.removeChild(el);
}

exports.attr = attr;
exports.classes = classes;
exports.clear = clear;
exports.closest = closest;
exports.delegate = delegateEvents;
exports.domify = domify;
exports.event = componentEvent;
exports.matches = matchesSelector$1;
exports.query = query;
exports.queryAll = all;
exports.remove = remove;

},{}],61:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _read = _dereq_(63);

Object.defineProperty(exports, 'Reader', {
	enumerable: true,
	get: function get() {
		return _read.Reader;
	}
});

var _write = _dereq_(64);

Object.defineProperty(exports, 'Writer', {
	enumerable: true,
	get: function get() {
		return _write.Writer;
	}
});

},{"63":63,"64":64}],62:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.hasLowerCaseAlias = hasLowerCaseAlias;
exports.serializeAsType = serializeAsType;
exports.serializeAsProperty = serializeAsProperty;
function hasLowerCaseAlias(pkg) {
	return pkg.xml && pkg.xml.tagAlias === 'lowerCase';
}

var DEFAULT_NS_MAP = exports.DEFAULT_NS_MAP = {
	'xsi': 'http://www.w3.org/2001/XMLSchema-instance'
};

var XSI_TYPE = exports.XSI_TYPE = 'xsi:type';

function serializeFormat(element) {
	return element.xml && element.xml.serialize;
}

function serializeAsType(element) {
	return serializeFormat(element) === XSI_TYPE;
}

function serializeAsProperty(element) {
	return serializeFormat(element) === 'property';
}

},{}],63:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.Context = Context;
exports.ElementHandler = ElementHandler;
exports.Reader = Reader;

var _minDash = _dereq_(59);

var _tinyStack = _dereq_(78);

var _tinyStack2 = _interopRequireDefault(_tinyStack);

var _saxen = _dereq_(77);

var _moddle = _dereq_(65);

var _moddle2 = _interopRequireDefault(_moddle);

var _ns = _dereq_(70);

var _types = _dereq_(73);

var _common = _dereq_(62);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function capitalize(str) {
	return str.charAt(0).toUpperCase() + str.slice(1);
}

function aliasToName(aliasNs, pkg) {

	if (!(0, _common.hasLowerCaseAlias)(pkg)) {
		return aliasNs.name;
	}

	return aliasNs.prefix + ':' + capitalize(aliasNs.localName);
}

function prefixedToName(nameNs, pkg) {

	var name = nameNs.name,
			localName = nameNs.localName;

	var typePrefix = pkg.xml && pkg.xml.typePrefix;

	if (typePrefix && localName.indexOf(typePrefix) === 0) {
		return nameNs.prefix + ':' + localName.slice(typePrefix.length);
	} else {
		return name;
	}
}

function normalizeXsiTypeName(name, model) {

	var nameNs = (0, _ns.parseName)(name);
	var pkg = model.getPackage(nameNs.prefix);

	return prefixedToName(nameNs, pkg);
}

function error(message) {
	return new Error(message);
}

/**
 * Get the moddle descriptor for a given instance or type.
 *
 * @param  {ModdleElement|Function} element
 *
 * @return {Object} the moddle descriptor
 */
function getModdleDescriptor(element) {
	return element.$descriptor;
}

function defer(fn) {
	setTimeout(fn, 0);
}

/**
 * A parse context.
 *
 * @class
 *
 * @param {Object} options
 * @param {ElementHandler} options.rootHandler the root handler for parsing a document
 * @param {boolean} [options.lax=false] whether or not to ignore invalid elements
 */
function Context(options) {

	/**
	 * @property {ElementHandler} rootHandler
	 */

	/**
	 * @property {Boolean} lax
	 */

	(0, _minDash.assign)(this, options);

	this.elementsById = {};
	this.references = [];
	this.warnings = [];

	/**
	 * Add an unresolved reference.
	 *
	 * @param {Object} reference
	 */
	this.addReference = function (reference) {
		this.references.push(reference);
	};

	/**
	 * Add a processed element.
	 *
	 * @param {ModdleElement} element
	 */
	this.addElement = function (element) {

		if (!element) {
			throw error('expected element');
		}

		var elementsById = this.elementsById;

		var descriptor = getModdleDescriptor(element);

		var idProperty = descriptor.idProperty,
				id;

		if (idProperty) {
			id = element.get(idProperty.name);

			if (id) {

				if (elementsById[id]) {
					throw error('duplicate ID <' + id + '>');
				}

				elementsById[id] = element;
			}
		}
	};

	/**
	 * Add an import warning.
	 *
	 * @param {Object} warning
	 * @param {String} warning.message
	 * @param {Error} [warning.error]
	 */
	this.addWarning = function (warning) {
		this.warnings.push(warning);
	};
}

function BaseHandler() {}

BaseHandler.prototype.handleEnd = function () {};
BaseHandler.prototype.handleText = function () {};
BaseHandler.prototype.handleNode = function () {};

/**
 * A simple pass through handler that does nothing except for
 * ignoring all input it receives.
 *
 * This is used to ignore unknown elements and
 * attributes.
 */
function NoopHandler() {}

NoopHandler.prototype = Object.create(BaseHandler.prototype);

NoopHandler.prototype.handleNode = function () {
	return this;
};

function BodyHandler() {}

BodyHandler.prototype = Object.create(BaseHandler.prototype);

BodyHandler.prototype.handleText = function (text) {
	this.body = (this.body || '') + text;
};

function ReferenceHandler(property, context) {
	this.property = property;
	this.context = context;
}

ReferenceHandler.prototype = Object.create(BodyHandler.prototype);

ReferenceHandler.prototype.handleNode = function (node) {

	if (this.element) {
		throw error('expected no sub nodes');
	} else {
		this.element = this.createReference(node);
	}

	return this;
};

ReferenceHandler.prototype.handleEnd = function () {
	this.element.id = this.body;
};

ReferenceHandler.prototype.createReference = function (node) {
	return {
		property: this.property.ns.name,
		id: ''
	};
};

function ValueHandler(propertyDesc, element) {
	this.element = element;
	this.propertyDesc = propertyDesc;
}

ValueHandler.prototype = Object.create(BodyHandler.prototype);

ValueHandler.prototype.handleEnd = function () {

	var value = this.body || '',
			element = this.element,
			propertyDesc = this.propertyDesc;

	value = (0, _types.coerceType)(propertyDesc.type, value);

	if (propertyDesc.isMany) {
		element.get(propertyDesc.name).push(value);
	} else {
		element.set(propertyDesc.name, value);
	}
};

function BaseElementHandler() {}

BaseElementHandler.prototype = Object.create(BodyHandler.prototype);

BaseElementHandler.prototype.handleNode = function (node) {
	var parser = this,
			element = this.element;

	if (!element) {
		element = this.element = this.createElement(node);

		this.context.addElement(element);
	} else {
		parser = this.handleChild(node);
	}

	return parser;
};

/**
 * @class Reader.ElementHandler
 *
 */
function ElementHandler(model, typeName, context) {
	this.model = model;
	this.type = model.getType(typeName);
	this.context = context;
}

ElementHandler.prototype = Object.create(BaseElementHandler.prototype);

ElementHandler.prototype.addReference = function (reference) {
	this.context.addReference(reference);
};

ElementHandler.prototype.handleEnd = function () {

	var value = this.body,
			element = this.element,
			descriptor = getModdleDescriptor(element),
			bodyProperty = descriptor.bodyProperty;

	if (bodyProperty && value !== undefined) {
		value = (0, _types.coerceType)(bodyProperty.type, value);
		element.set(bodyProperty.name, value);
	}
};

/**
 * Create an instance of the model from the given node.
 *
 * @param  {Element} node the xml node
 */
ElementHandler.prototype.createElement = function (node) {
	var attributes = node.attributes,
			Type = this.type,
			descriptor = getModdleDescriptor(Type),
			context = this.context,
			instance = new Type({}),
			model = this.model,
			propNameNs;

	(0, _minDash.forEach)(attributes, function (value, name) {

		var prop = descriptor.propertiesByName[name],
				values;

		if (prop && prop.isReference) {

			if (!prop.isMany) {
				context.addReference({
					element: instance,
					property: prop.ns.name,
					id: value
				});
			} else {
				// IDREFS: parse references as whitespace-separated list
				values = value.split(' ');

				(0, _minDash.forEach)(values, function (v) {
					context.addReference({
						element: instance,
						property: prop.ns.name,
						id: v
					});
				});
			}
		} else {
			if (prop) {
				value = (0, _types.coerceType)(prop.type, value);
			} else if (name !== 'xmlns') {
				propNameNs = (0, _ns.parseName)(name, descriptor.ns.prefix);

				// check whether attribute is defined in a well-known namespace
				// if that is the case we emit a warning to indicate potential misuse
				if (model.getPackage(propNameNs.prefix)) {

					context.addWarning({
						message: 'unknown attribute <' + name + '>',
						element: instance,
						property: name,
						value: value
					});
				}
			}

			instance.set(name, value);
		}
	});

	return instance;
};

ElementHandler.prototype.getPropertyForNode = function (node) {

	var name = node.name;
	var nameNs = (0, _ns.parseName)(name);

	var type = this.type,
			model = this.model,
			descriptor = getModdleDescriptor(type);

	var propertyName = nameNs.name,
			property = descriptor.propertiesByName[propertyName],
			elementTypeName,
			elementType;

	// search for properties by name first

	if (property) {

		if ((0, _common.serializeAsType)(property)) {
			elementTypeName = node.attributes[_common.XSI_TYPE];

			// xsi type is optional, if it does not exists the
			// default type is assumed
			if (elementTypeName) {

				// take possible type prefixes from XML
				// into account, i.e.: xsi:type="t{ActualType}"
				elementTypeName = normalizeXsiTypeName(elementTypeName, model);

				elementType = model.getType(elementTypeName);

				return (0, _minDash.assign)({}, property, {
					effectiveType: getModdleDescriptor(elementType).name
				});
			}
		}

		// search for properties by name first
		return property;
	}

	var pkg = model.getPackage(nameNs.prefix);

	if (pkg) {
		elementTypeName = aliasToName(nameNs, pkg);
		elementType = model.getType(elementTypeName);

		// search for collection members later
		property = (0, _minDash.find)(descriptor.properties, function (p) {
			return !p.isVirtual && !p.isReference && !p.isAttribute && elementType.hasType(p.type);
		});

		if (property) {
			return (0, _minDash.assign)({}, property, {
				effectiveType: getModdleDescriptor(elementType).name
			});
		}
	} else {
		// parse unknown element (maybe extension)
		property = (0, _minDash.find)(descriptor.properties, function (p) {
			return !p.isReference && !p.isAttribute && p.type === 'Element';
		});

		if (property) {
			return property;
		}
	}

	throw error('unrecognized element <' + nameNs.name + '>');
};

ElementHandler.prototype.toString = function () {
	return 'ElementDescriptor[' + getModdleDescriptor(this.type).name + ']';
};

ElementHandler.prototype.valueHandler = function (propertyDesc, element) {
	return new ValueHandler(propertyDesc, element);
};

ElementHandler.prototype.referenceHandler = function (propertyDesc) {
	return new ReferenceHandler(propertyDesc, this.context);
};

ElementHandler.prototype.handler = function (type) {
	if (type === 'Element') {
		return new GenericElementHandler(this.model, type, this.context);
	} else {
		return new ElementHandler(this.model, type, this.context);
	}
};

/**
 * Handle the child element parsing
 *
 * @param  {Element} node the xml node
 */
ElementHandler.prototype.handleChild = function (node) {
	var propertyDesc, type, element, childHandler;

	propertyDesc = this.getPropertyForNode(node);
	element = this.element;

	type = propertyDesc.effectiveType || propertyDesc.type;

	if ((0, _types.isSimple)(type)) {
		return this.valueHandler(propertyDesc, element);
	}

	if (propertyDesc.isReference) {
		childHandler = this.referenceHandler(propertyDesc).handleNode(node);
	} else {
		childHandler = this.handler(type).handleNode(node);
	}

	var newElement = childHandler.element;

	// child handles may decide to skip elements
	// by not returning anything
	if (newElement !== undefined) {

		if (propertyDesc.isMany) {
			element.get(propertyDesc.name).push(newElement);
		} else {
			element.set(propertyDesc.name, newElement);
		}

		if (propertyDesc.isReference) {
			(0, _minDash.assign)(newElement, {
				element: element
			});

			this.context.addReference(newElement);
		} else {
			// establish child -> parent relationship
			newElement.$parent = element;
		}
	}

	return childHandler;
};

/**
 * An element handler that performs special validation
 * to ensure the node it gets initialized with matches
 * the handlers type (namespace wise).
 *
 * @param {Moddle} model
 * @param {String} typeName
 * @param {Context} context
 */
function RootElementHandler(model, typeName, context) {
	ElementHandler.call(this, model, typeName, context);
}

RootElementHandler.prototype = Object.create(ElementHandler.prototype);

RootElementHandler.prototype.createElement = function (node) {

	var name = node.name,
			nameNs = (0, _ns.parseName)(name),
			model = this.model,
			type = this.type,
			pkg = model.getPackage(nameNs.prefix),
			typeName = pkg && aliasToName(nameNs, pkg) || name;

	// verify the correct namespace if we parse
	// the first element in the handler tree
	//
	// this ensures we don't mistakenly import wrong namespace elements
	if (!type.hasType(typeName)) {
		throw error('unexpected element <' + node.originalName + '>');
	}

	return ElementHandler.prototype.createElement.call(this, node);
};

function GenericElementHandler(model, typeName, context) {
	this.model = model;
	this.context = context;
}

GenericElementHandler.prototype = Object.create(BaseElementHandler.prototype);

GenericElementHandler.prototype.createElement = function (node) {

	var name = node.name,
			ns = (0, _ns.parseName)(name),
			prefix = ns.prefix,
			uri = node.ns[prefix + '$uri'],
			attributes = node.attributes;

	return this.model.createAny(name, uri, attributes);
};

GenericElementHandler.prototype.handleChild = function (node) {

	var handler = new GenericElementHandler(this.model, 'Element', this.context).handleNode(node),
			element = this.element;

	var newElement = handler.element,
			children;

	if (newElement !== undefined) {
		children = element.$children = element.$children || [];
		children.push(newElement);

		// establish child -> parent relationship
		newElement.$parent = element;
	}

	return handler;
};

GenericElementHandler.prototype.handleText = function (text) {
	this.body = this.body || '' + text;
};

GenericElementHandler.prototype.handleEnd = function () {
	if (this.body) {
		this.element.$body = this.body;
	}
};

/**
 * A reader for a meta-model
 *
 * @param {Object} options
 * @param {Model} options.model used to read xml files
 * @param {Boolean} options.lax whether to make parse errors warnings
 */
function Reader(options) {

	if (options instanceof _moddle2.default) {
		options = {
			model: options
		};
	}

	(0, _minDash.assign)(this, { lax: false }, options);
}

/**
 * Parse the given XML into a moddle document tree.
 *
 * @param {String} xml
 * @param {ElementHandler|Object} options or rootHandler
 * @param  {Function} done
 */
Reader.prototype.fromXML = function (xml, options, done) {

	var rootHandler = options.rootHandler;

	if (options instanceof ElementHandler) {
		// root handler passed via (xml, { rootHandler: ElementHandler }, ...)
		rootHandler = options;
		options = {};
	} else {
		if (typeof options === 'string') {
			// rootHandler passed via (xml, 'someString', ...)
			rootHandler = this.handler(options);
			options = {};
		} else if (typeof rootHandler === 'string') {
			// rootHandler passed via (xml, { rootHandler: 'someString' }, ...)
			rootHandler = this.handler(rootHandler);
		}
	}

	var model = this.model,
			lax = this.lax;

	var context = new Context((0, _minDash.assign)({}, options, { rootHandler: rootHandler })),
			parser = new _saxen.Parser({ proxy: true }),
			stack = new _tinyStack2.default();

	rootHandler.context = context;

	// push root handler
	stack.push(rootHandler);

	/**
	 * Handle error.
	 *
	 * @param  {Error} err
	 * @param  {Function} getContext
	 * @param  {boolean} lax
	 *
	 * @return {boolean} true if handled
	 */
	function handleError(err, getContext, lax) {

		var ctx = getContext();

		var line = ctx.line,
				column = ctx.column,
				data = ctx.data;

		// we receive the full context data here,
		// for elements trim down the information
		// to the tag name, only
		if (data.charAt(0) === '<' && data.indexOf(' ') !== -1) {
			data = data.slice(0, data.indexOf(' ')) + '>';
		}

		var message = 'unparsable content ' + (data ? data + ' ' : '') + 'detected\n\t' + 'line: ' + line + '\n\t' + 'column: ' + column + '\n\t' + 'nested error: ' + err.message;

		if (lax) {
			context.addWarning({
				message: message,
				error: err
			});

			console.warn('could not parse node');
			console.warn(err);

			return true;
		} else {
			console.error('could not parse document');
			console.error(err);

			throw error(message);
		}
	}

	function handleWarning(err, getContext) {
		// just like handling errors in <lax=true> mode
		return handleError(err, getContext, true);
	}

	/**
	 * Resolve collected references on parse end.
	 */
	function resolveReferences() {

		var elementsById = context.elementsById;
		var references = context.references;

		var i, r;

		for (i = 0; r = references[i]; i++) {
			var element = r.element;
			var reference = elementsById[r.id];
			var property = getModdleDescriptor(element).propertiesByName[r.property];

			if (!reference) {
				context.addWarning({
					message: 'unresolved reference <' + r.id + '>',
					element: r.element,
					property: r.property,
					value: r.id
				});
			}

			if (property.isMany) {
				var collection = element.get(property.name),
						idx = collection.indexOf(r);

				// we replace an existing place holder (idx != -1) or
				// append to the collection instead
				if (idx === -1) {
					idx = collection.length;
				}

				if (!reference) {
					// remove unresolvable reference
					collection.splice(idx, 1);
				} else {
					// add or update reference in collection
					collection[idx] = reference;
				}
			} else {
				element.set(property.name, reference);
			}
		}
	}

	function handleClose() {
		stack.pop().handleEnd();
	}

	var PREAMBLE_START_PATTERN = /^<\?xml /i;

	var ENCODING_PATTERN = / encoding="([^"]+)"/i;

	var UTF_8_PATTERN = /^utf-8$/i;

	function handleQuestion(question) {

		if (!PREAMBLE_START_PATTERN.test(question)) {
			return;
		}

		var match = ENCODING_PATTERN.exec(question);
		var encoding = match && match[1];

		if (!encoding || UTF_8_PATTERN.test(encoding)) {
			return;
		}

		context.addWarning({
			message: 'unsupported document encoding <' + encoding + '>, ' + 'falling back to UTF-8'
		});
	}

	function handleOpen(node, getContext) {
		var handler = stack.peek();

		try {
			stack.push(handler.handleNode(node));
		} catch (err) {

			if (handleError(err, getContext, lax)) {
				stack.push(new NoopHandler());
			}
		}
	}

	function handleCData(text) {
		stack.peek().handleText(text);
	}

	function handleText(text) {
		// strip whitespace only nodes, i.e. before
		// <!CDATA[ ... ]> sections and in between tags
		text = text.trim();

		if (!text) {
			return;
		}

		stack.peek().handleText(text);
	}

	var uriMap = model.getPackages().reduce(function (uriMap, p) {
		uriMap[p.uri] = p.prefix;

		return uriMap;
	}, {});

	parser.ns(uriMap).on('openTag', function (obj, decodeStr, selfClosing, getContext) {

		// gracefully handle unparsable attributes (attrs=false)
		var attrs = obj.attrs || {};

		var decodedAttrs = Object.keys(attrs).reduce(function (d, key) {
			var value = decodeStr(attrs[key]);

			d[key] = value;

			return d;
		}, {});

		var node = {
			name: obj.name,
			originalName: obj.originalName,
			attributes: decodedAttrs,
			ns: obj.ns
		};

		handleOpen(node, getContext);
	}).on('question', handleQuestion).on('closeTag', handleClose).on('cdata', handleCData).on('text', function (text, decodeEntities) {
		handleText(decodeEntities(text));
	}).on('error', handleError).on('warn', handleWarning);

	// deferred parse XML to make loading really ascnchronous
	// this ensures the execution environment (node or browser)
	// is kept responsive and that certain optimization strategies
	// can kick in
	defer(function () {
		var err;

		try {
			parser.parse(xml);

			resolveReferences();
		} catch (e) {
			err = e;
		}

		var element = rootHandler.element;

		// handle the situation that we could not extract
		// the desired root element from the document
		if (!err && !element) {
			err = error('failed to parse document as <' + rootHandler.type.$descriptor.name + '>');
		}

		done(err, err ? undefined : element, context);
	});
};

Reader.prototype.handler = function (name) {
	return new RootElementHandler(this.model, name);
};

},{"59":59,"62":62,"65":65,"70":70,"73":73,"77":77,"78":78}],64:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.Namespaces = Namespaces;
exports.Writer = Writer;

var _minDash = _dereq_(59);

var _types = _dereq_(73);

var _ns = _dereq_(70);

var _common = _dereq_(62);

var XML_PREAMBLE = '<?xml version="1.0" encoding="UTF-8"?>\n';

var ESCAPE_ATTR_CHARS = /<|>|'|"|&|\n\r|\n/g;
var ESCAPE_CHARS = /<|>|&/g;

function Namespaces(parent) {

	var prefixMap = {};
	var uriMap = {};
	var used = {};

	var wellknown = [];
	var custom = [];

	// API

	this.byUri = function (uri) {
		return uriMap[uri] || parent && parent.byUri(uri);
	};

	this.add = function (ns, isWellknown) {

		uriMap[ns.uri] = ns;

		if (isWellknown) {
			wellknown.push(ns);
		} else {
			custom.push(ns);
		}

		this.mapPrefix(ns.prefix, ns.uri);
	};

	this.uriByPrefix = function (prefix) {
		return prefixMap[prefix || 'xmlns'];
	};

	this.mapPrefix = function (prefix, uri) {
		prefixMap[prefix || 'xmlns'] = uri;
	};

	this.logUsed = function (ns) {
		var uri = ns.uri;

		used[uri] = this.byUri(uri);
	};

	this.getUsed = function (ns) {

		function isUsed(ns) {
			return used[ns.uri];
		}

		var allNs = [].concat(wellknown, custom);

		return allNs.filter(isUsed);
	};
}

function lower(string) {
	return string.charAt(0).toLowerCase() + string.slice(1);
}

function nameToAlias(name, pkg) {
	if ((0, _common.hasLowerCaseAlias)(pkg)) {
		return lower(name);
	} else {
		return name;
	}
}

function inherits(ctor, superCtor) {
	ctor.super_ = superCtor;
	ctor.prototype = Object.create(superCtor.prototype, {
		constructor: {
			value: ctor,
			enumerable: false,
			writable: true,
			configurable: true
		}
	});
}

function nsName(ns) {
	if ((0, _minDash.isString)(ns)) {
		return ns;
	} else {
		return (ns.prefix ? ns.prefix + ':' : '') + ns.localName;
	}
}

function getNsAttrs(namespaces) {

	return (0, _minDash.map)(namespaces.getUsed(), function (ns) {
		var name = 'xmlns' + (ns.prefix ? ':' + ns.prefix : '');
		return { name: name, value: ns.uri };
	});
}

function getElementNs(ns, descriptor) {
	if (descriptor.isGeneric) {
		return (0, _minDash.assign)({ localName: descriptor.ns.localName }, ns);
	} else {
		return (0, _minDash.assign)({ localName: nameToAlias(descriptor.ns.localName, descriptor.$pkg) }, ns);
	}
}

function getPropertyNs(ns, descriptor) {
	return (0, _minDash.assign)({ localName: descriptor.ns.localName }, ns);
}

function getSerializableProperties(element) {
	var descriptor = element.$descriptor;

	return (0, _minDash.filter)(descriptor.properties, function (p) {
		var name = p.name;

		if (p.isVirtual) {
			return false;
		}

		// do not serialize defaults
		if (!element.hasOwnProperty(name)) {
			return false;
		}

		var value = element[name];

		// do not serialize default equals
		if (value === p.default) {
			return false;
		}

		// do not serialize null properties
		if (value === null) {
			return false;
		}

		return p.isMany ? value.length : true;
	});
}

var ESCAPE_ATTR_MAP = {
	'\n': '#10',
	'\n\r': '#10',
	'"': '#34',
	'\'': '#39',
	'<': '#60',
	'>': '#62',
	'&': '#38'
};

var ESCAPE_MAP = {
	'<': 'lt',
	'>': 'gt',
	'&': 'amp'
};

function escape(str, charPattern, replaceMap) {

	// ensure we are handling strings here
	str = (0, _minDash.isString)(str) ? str : '' + str;

	return str.replace(charPattern, function (s) {
		return '&' + replaceMap[s] + ';';
	});
}

/**
 * Escape a string attribute to not contain any bad values (line breaks, '"', ...)
 *
 * @param {String} str the string to escape
 * @return {String} the escaped string
 */
function escapeAttr(str) {
	return escape(str, ESCAPE_ATTR_CHARS, ESCAPE_ATTR_MAP);
}

function escapeBody(str) {
	return escape(str, ESCAPE_CHARS, ESCAPE_MAP);
}

function filterAttributes(props) {
	return (0, _minDash.filter)(props, function (p) {
		return p.isAttr;
	});
}

function filterContained(props) {
	return (0, _minDash.filter)(props, function (p) {
		return !p.isAttr;
	});
}

function ReferenceSerializer(tagName) {
	this.tagName = tagName;
}

ReferenceSerializer.prototype.build = function (element) {
	this.element = element;
	return this;
};

ReferenceSerializer.prototype.serializeTo = function (writer) {
	writer.appendIndent().append('<' + this.tagName + '>' + this.element.id + '</' + this.tagName + '>').appendNewLine();
};

function BodySerializer() {}

BodySerializer.prototype.serializeValue = BodySerializer.prototype.serializeTo = function (writer) {
	writer.append(this.escape ? escapeBody(this.value) : this.value);
};

BodySerializer.prototype.build = function (prop, value) {
	this.value = value;

	if (prop.type === 'String' && value.search(ESCAPE_CHARS) !== -1) {
		this.escape = true;
	}

	return this;
};

function ValueSerializer(tagName) {
	this.tagName = tagName;
}

inherits(ValueSerializer, BodySerializer);

ValueSerializer.prototype.serializeTo = function (writer) {

	writer.appendIndent().append('<' + this.tagName + '>');

	this.serializeValue(writer);

	writer.append('</' + this.tagName + '>').appendNewLine();
};

function ElementSerializer(parent, propertyDescriptor) {
	this.body = [];
	this.attrs = [];

	this.parent = parent;
	this.propertyDescriptor = propertyDescriptor;
}

ElementSerializer.prototype.build = function (element) {
	this.element = element;

	var elementDescriptor = element.$descriptor,
			propertyDescriptor = this.propertyDescriptor;

	var otherAttrs, properties;

	var isGeneric = elementDescriptor.isGeneric;

	if (isGeneric) {
		otherAttrs = this.parseGeneric(element);
	} else {
		otherAttrs = this.parseNsAttributes(element);
	}

	if (propertyDescriptor) {
		this.ns = this.nsPropertyTagName(propertyDescriptor);
	} else {
		this.ns = this.nsTagName(elementDescriptor);
	}

	// compute tag name
	this.tagName = this.addTagName(this.ns);

	if (!isGeneric) {
		properties = getSerializableProperties(element);

		this.parseAttributes(filterAttributes(properties));
		this.parseContainments(filterContained(properties));
	}

	this.parseGenericAttributes(element, otherAttrs);

	return this;
};

ElementSerializer.prototype.nsTagName = function (descriptor) {
	var effectiveNs = this.logNamespaceUsed(descriptor.ns);
	return getElementNs(effectiveNs, descriptor);
};

ElementSerializer.prototype.nsPropertyTagName = function (descriptor) {
	var effectiveNs = this.logNamespaceUsed(descriptor.ns);
	return getPropertyNs(effectiveNs, descriptor);
};

ElementSerializer.prototype.isLocalNs = function (ns) {
	return ns.uri === this.ns.uri;
};

/**
 * Get the actual ns attribute name for the given element.
 *
 * @param {Object} element
 * @param {Boolean} [element.inherited=false]
 *
 * @return {Object} nsName
 */
ElementSerializer.prototype.nsAttributeName = function (element) {

	var ns;

	if ((0, _minDash.isString)(element)) {
		ns = (0, _ns.parseName)(element);
	} else {
		ns = element.ns;
	}

	// return just local name for inherited attributes
	if (element.inherited) {
		return { localName: ns.localName };
	}

	// parse + log effective ns
	var effectiveNs = this.logNamespaceUsed(ns);

	// LOG ACTUAL namespace use
	this.getNamespaces().logUsed(effectiveNs);

	// strip prefix if same namespace like parent
	if (this.isLocalNs(effectiveNs)) {
		return { localName: ns.localName };
	} else {
		return (0, _minDash.assign)({ localName: ns.localName }, effectiveNs);
	}
};

ElementSerializer.prototype.parseGeneric = function (element) {

	var self = this,
			body = this.body;

	var attributes = [];

	(0, _minDash.forEach)(element, function (val, key) {

		var nonNsAttr;

		if (key === '$body') {
			body.push(new BodySerializer().build({ type: 'String' }, val));
		} else if (key === '$children') {
			(0, _minDash.forEach)(val, function (child) {
				body.push(new ElementSerializer(self).build(child));
			});
		} else if (key.indexOf('$') !== 0) {
			nonNsAttr = self.parseNsAttribute(element, key, val);

			if (nonNsAttr) {
				attributes.push({ name: key, value: val });
			}
		}
	});

	return attributes;
};

ElementSerializer.prototype.parseNsAttribute = function (element, name, value) {
	var model = element.$model;

	var nameNs = (0, _ns.parseName)(name);

	var ns;

	// parse xmlns:foo="http://foo.bar"
	if (nameNs.prefix === 'xmlns') {
		ns = { prefix: nameNs.localName, uri: value };
	}

	// parse xmlns="http://foo.bar"
	if (!nameNs.prefix && nameNs.localName === 'xmlns') {
		ns = { uri: value };
	}

	if (!ns) {
		return {
			name: name,
			value: value
		};
	}

	if (model && model.getPackage(value)) {
		// register well known namespace
		this.logNamespace(ns, true, true);
	} else {
		// log custom namespace directly as used
		var actualNs = this.logNamespaceUsed(ns, true);

		this.getNamespaces().logUsed(actualNs);
	}
};

/**
 * Parse namespaces and return a list of left over generic attributes
 *
 * @param  {Object} element
 * @return {Array<Object>}
 */
ElementSerializer.prototype.parseNsAttributes = function (element, attrs) {
	var self = this;

	var genericAttrs = element.$attrs;

	var attributes = [];

	// parse namespace attributes first
	// and log them. push non namespace attributes to a list
	// and process them later
	(0, _minDash.forEach)(genericAttrs, function (value, name) {

		var nonNsAttr = self.parseNsAttribute(element, name, value);

		if (nonNsAttr) {
			attributes.push(nonNsAttr);
		}
	});

	return attributes;
};

ElementSerializer.prototype.parseGenericAttributes = function (element, attributes) {

	var self = this;

	(0, _minDash.forEach)(attributes, function (attr) {

		// do not serialize xsi:type attribute
		// it is set manually based on the actual implementation type
		if (attr.name === _common.XSI_TYPE) {
			return;
		}

		try {
			self.addAttribute(self.nsAttributeName(attr.name), attr.value);
		} catch (e) {
			console.warn('missing namespace information for ', attr.name, '=', attr.value, 'on', element, e);
		}
	});
};

ElementSerializer.prototype.parseContainments = function (properties) {

	var self = this,
			body = this.body,
			element = this.element;

	(0, _minDash.forEach)(properties, function (p) {
		var value = element.get(p.name),
				isReference = p.isReference,
				isMany = p.isMany;

		if (!isMany) {
			value = [value];
		}

		if (p.isBody) {
			body.push(new BodySerializer().build(p, value[0]));
		} else if ((0, _types.isSimple)(p.type)) {
			(0, _minDash.forEach)(value, function (v) {
				body.push(new ValueSerializer(self.addTagName(self.nsPropertyTagName(p))).build(p, v));
			});
		} else if (isReference) {
			(0, _minDash.forEach)(value, function (v) {
				body.push(new ReferenceSerializer(self.addTagName(self.nsPropertyTagName(p))).build(v));
			});
		} else {
			// allow serialization via type
			// rather than element name
			var asType = (0, _common.serializeAsType)(p),
					asProperty = (0, _common.serializeAsProperty)(p);

			(0, _minDash.forEach)(value, function (v) {
				var serializer;

				if (asType) {
					serializer = new TypeSerializer(self, p);
				} else if (asProperty) {
					serializer = new ElementSerializer(self, p);
				} else {
					serializer = new ElementSerializer(self);
				}

				body.push(serializer.build(v));
			});
		}
	});
};

ElementSerializer.prototype.getNamespaces = function (local) {

	var namespaces = this.namespaces,
			parent = this.parent,
			parentNamespaces;

	if (!namespaces) {
		parentNamespaces = parent && parent.getNamespaces();

		if (local || !parentNamespaces) {
			this.namespaces = namespaces = new Namespaces(parentNamespaces);
		} else {
			namespaces = parentNamespaces;
		}
	}

	return namespaces;
};

ElementSerializer.prototype.logNamespace = function (ns, wellknown, local) {
	var namespaces = this.getNamespaces(local);

	var nsUri = ns.uri,
			nsPrefix = ns.prefix;

	var existing = namespaces.byUri(nsUri);

	if (!existing) {
		namespaces.add(ns, wellknown);
	}

	namespaces.mapPrefix(nsPrefix, nsUri);

	return ns;
};

ElementSerializer.prototype.logNamespaceUsed = function (ns, local) {
	var element = this.element,
			model = element.$model,
			namespaces = this.getNamespaces(local);

	// ns may be
	//
	//   * prefix only
	//   * prefix:uri
	//   * localName only

	var prefix = ns.prefix,
			uri = ns.uri,
			newPrefix,
			idx,
			wellknownUri;

	// handle anonymous namespaces (elementForm=unqualified), cf. #23
	if (!prefix && !uri) {
		return { localName: ns.localName };
	}

	wellknownUri = _common.DEFAULT_NS_MAP[prefix] || model && (model.getPackage(prefix) || {}).uri;

	uri = uri || wellknownUri || namespaces.uriByPrefix(prefix);

	if (!uri) {
		throw new Error('no namespace uri given for prefix <' + prefix + '>');
	}

	ns = namespaces.byUri(uri);

	if (!ns) {
		newPrefix = prefix;
		idx = 1;

		// find a prefix that is not mapped yet
		while (namespaces.uriByPrefix(newPrefix)) {
			newPrefix = prefix + '_' + idx++;
		}

		ns = this.logNamespace({ prefix: newPrefix, uri: uri }, wellknownUri === uri);
	}

	if (prefix) {
		namespaces.mapPrefix(prefix, uri);
	}

	return ns;
};

ElementSerializer.prototype.parseAttributes = function (properties) {
	var self = this,
			element = this.element;

	(0, _minDash.forEach)(properties, function (p) {

		var value = element.get(p.name);

		if (p.isReference) {

			if (!p.isMany) {
				value = value.id;
			} else {
				var values = [];
				(0, _minDash.forEach)(value, function (v) {
					values.push(v.id);
				});
				// IDREFS is a whitespace-separated list of references.
				value = values.join(' ');
			}
		}

		self.addAttribute(self.nsAttributeName(p), value);
	});
};

ElementSerializer.prototype.addTagName = function (nsTagName) {
	var actualNs = this.logNamespaceUsed(nsTagName);

	this.getNamespaces().logUsed(actualNs);

	return nsName(nsTagName);
};

ElementSerializer.prototype.addAttribute = function (name, value) {
	var attrs = this.attrs;

	if ((0, _minDash.isString)(value)) {
		value = escapeAttr(value);
	}

	attrs.push({ name: name, value: value });
};

ElementSerializer.prototype.serializeAttributes = function (writer) {
	var attrs = this.attrs,
			namespaces = this.namespaces;

	if (namespaces) {
		attrs = getNsAttrs(namespaces).concat(attrs);
	}

	(0, _minDash.forEach)(attrs, function (a) {
		writer.append(' ').append(nsName(a.name)).append('="').append(a.value).append('"');
	});
};

ElementSerializer.prototype.serializeTo = function (writer) {
	var firstBody = this.body[0],
			indent = firstBody && firstBody.constructor !== BodySerializer;

	writer.appendIndent().append('<' + this.tagName);

	this.serializeAttributes(writer);

	writer.append(firstBody ? '>' : ' />');

	if (firstBody) {

		if (indent) {
			writer.appendNewLine().indent();
		}

		(0, _minDash.forEach)(this.body, function (b) {
			b.serializeTo(writer);
		});

		if (indent) {
			writer.unindent().appendIndent();
		}

		writer.append('</' + this.tagName + '>');
	}

	writer.appendNewLine();
};

/**
 * A serializer for types that handles serialization of data types
 */
function TypeSerializer(parent, propertyDescriptor) {
	ElementSerializer.call(this, parent, propertyDescriptor);
}

inherits(TypeSerializer, ElementSerializer);

TypeSerializer.prototype.parseNsAttributes = function (element) {

	// extracted attributes
	var attributes = ElementSerializer.prototype.parseNsAttributes.call(this, element);

	var descriptor = element.$descriptor;

	// only serialize xsi:type if necessary
	if (descriptor.name === this.propertyDescriptor.type) {
		return attributes;
	}

	var typeNs = this.typeNs = this.nsTagName(descriptor);
	this.getNamespaces().logUsed(this.typeNs);

	// add xsi:type attribute to represent the elements
	// actual type

	var pkg = element.$model.getPackage(typeNs.uri),
			typePrefix = pkg.xml && pkg.xml.typePrefix || '';

	this.addAttribute(this.nsAttributeName(_common.XSI_TYPE), (typeNs.prefix ? typeNs.prefix + ':' : '') + typePrefix + descriptor.ns.localName);

	return attributes;
};

TypeSerializer.prototype.isLocalNs = function (ns) {
	return ns.uri === (this.typeNs || this.ns).uri;
};

function SavingWriter() {
	this.value = '';

	this.write = function (str) {
		this.value += str;
	};
}

function FormatingWriter(out, format) {

	var indent = [''];

	this.append = function (str) {
		out.write(str);

		return this;
	};

	this.appendNewLine = function () {
		if (format) {
			out.write('\n');
		}

		return this;
	};

	this.appendIndent = function () {
		if (format) {
			out.write(indent.join('  '));
		}

		return this;
	};

	this.indent = function () {
		indent.push('');
		return this;
	};

	this.unindent = function () {
		indent.pop();
		return this;
	};
}

/**
 * A writer for meta-model backed document trees
 *
 * @param {Object} options output options to pass into the writer
 */
function Writer(options) {

	options = (0, _minDash.assign)({ format: false, preamble: true }, options || {});

	function toXML(tree, writer) {
		var internalWriter = writer || new SavingWriter();
		var formatingWriter = new FormatingWriter(internalWriter, options.format);

		if (options.preamble) {
			formatingWriter.append(XML_PREAMBLE);
		}

		new ElementSerializer().build(tree).serializeTo(formatingWriter);

		if (!writer) {
			return internalWriter.value;
		}
	}

	return {
		toXML: toXML
	};
}

},{"59":59,"62":62,"70":70,"73":73}],65:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _moddle = _dereq_(69);

Object.defineProperty(exports, 'default', {
	enumerable: true,
	get: function get() {
		return _interopRequireDefault(_moddle).default;
	}
});

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

},{"69":69}],66:[function(_dereq_,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Base;
/**
 * Moddle base element.
 */
function Base() {}

Base.prototype.get = function (name) {
	return this.$model.properties.get(this, name);
};

Base.prototype.set = function (name, value) {
	this.$model.properties.set(this, name, value);
};

},{}],67:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = DescriptorBuilder;

var _minDash = _dereq_(59);

var _ns = _dereq_(70);

/**
 * A utility to build element descriptors.
 */
function DescriptorBuilder(nameNs) {
	this.ns = nameNs;
	this.name = nameNs.name;
	this.allTypes = [];
	this.allTypesByName = {};
	this.properties = [];
	this.propertiesByName = {};
}

DescriptorBuilder.prototype.build = function () {
	return (0, _minDash.pick)(this, ['ns', 'name', 'allTypes', 'allTypesByName', 'properties', 'propertiesByName', 'bodyProperty', 'idProperty']);
};

/**
 * Add property at given index.
 *
 * @param {Object} p
 * @param {Number} [idx]
 * @param {Boolean} [validate=true]
 */
DescriptorBuilder.prototype.addProperty = function (p, idx, validate) {

	if (typeof idx === 'boolean') {
		validate = idx;
		idx = undefined;
	}

	this.addNamedProperty(p, validate !== false);

	var properties = this.properties;

	if (idx !== undefined) {
		properties.splice(idx, 0, p);
	} else {
		properties.push(p);
	}
};

DescriptorBuilder.prototype.replaceProperty = function (oldProperty, newProperty, replace) {
	var oldNameNs = oldProperty.ns;

	var props = this.properties,
			propertiesByName = this.propertiesByName,
			rename = oldProperty.name !== newProperty.name;

	if (oldProperty.isId) {
		if (!newProperty.isId) {
			throw new Error('property <' + newProperty.ns.name + '> must be id property ' + 'to refine <' + oldProperty.ns.name + '>');
		}

		this.setIdProperty(newProperty, false);
	}

	if (oldProperty.isBody) {

		if (!newProperty.isBody) {
			throw new Error('property <' + newProperty.ns.name + '> must be body property ' + 'to refine <' + oldProperty.ns.name + '>');
		}

		// TODO: Check compatibility
		this.setBodyProperty(newProperty, false);
	}

	// validate existence and get location of old property
	var idx = props.indexOf(oldProperty);
	if (idx === -1) {
		throw new Error('property <' + oldNameNs.name + '> not found in property list');
	}

	// remove old property
	props.splice(idx, 1);

	// replacing the named property is intentional
	//
	//  * validate only if this is a "rename" operation
	//  * add at specific index unless we "replace"
	//
	this.addProperty(newProperty, replace ? undefined : idx, rename);

	// make new property available under old name
	propertiesByName[oldNameNs.name] = propertiesByName[oldNameNs.localName] = newProperty;
};

DescriptorBuilder.prototype.redefineProperty = function (p, targetPropertyName, replace) {

	var nsPrefix = p.ns.prefix;
	var parts = targetPropertyName.split('#');

	var name = (0, _ns.parseName)(parts[0], nsPrefix);
	var attrName = (0, _ns.parseName)(parts[1], name.prefix).name;

	var redefinedProperty = this.propertiesByName[attrName];
	if (!redefinedProperty) {
		throw new Error('refined property <' + attrName + '> not found');
	} else {
		this.replaceProperty(redefinedProperty, p, replace);
	}

	delete p.redefines;
};

DescriptorBuilder.prototype.addNamedProperty = function (p, validate) {
	var ns = p.ns,
			propsByName = this.propertiesByName;

	if (validate) {
		this.assertNotDefined(p, ns.name);
		this.assertNotDefined(p, ns.localName);
	}

	propsByName[ns.name] = propsByName[ns.localName] = p;
};

DescriptorBuilder.prototype.removeNamedProperty = function (p) {
	var ns = p.ns,
			propsByName = this.propertiesByName;

	delete propsByName[ns.name];
	delete propsByName[ns.localName];
};

DescriptorBuilder.prototype.setBodyProperty = function (p, validate) {

	if (validate && this.bodyProperty) {
		throw new Error('body property defined multiple times ' + '(<' + this.bodyProperty.ns.name + '>, <' + p.ns.name + '>)');
	}

	this.bodyProperty = p;
};

DescriptorBuilder.prototype.setIdProperty = function (p, validate) {

	if (validate && this.idProperty) {
		throw new Error('id property defined multiple times ' + '(<' + this.idProperty.ns.name + '>, <' + p.ns.name + '>)');
	}

	this.idProperty = p;
};

DescriptorBuilder.prototype.assertNotDefined = function (p, name) {
	var propertyName = p.name,
			definedProperty = this.propertiesByName[propertyName];

	if (definedProperty) {
		throw new Error('property <' + propertyName + '> already defined; ' + 'override of <' + definedProperty.definedBy.ns.name + '#' + definedProperty.ns.name + '> by ' + '<' + p.definedBy.ns.name + '#' + p.ns.name + '> not allowed without redefines');
	}
};

DescriptorBuilder.prototype.hasProperty = function (name) {
	return this.propertiesByName[name];
};

DescriptorBuilder.prototype.addTrait = function (t, inherited) {

	var typesByName = this.allTypesByName,
			types = this.allTypes;

	var typeName = t.name;

	if (typeName in typesByName) {
		return;
	}

	(0, _minDash.forEach)(t.properties, (0, _minDash.bind)(function (p) {

		// clone property to allow extensions
		p = (0, _minDash.assign)({}, p, {
			name: p.ns.localName,
			inherited: inherited
		});

		Object.defineProperty(p, 'definedBy', {
			value: t
		});

		var replaces = p.replaces,
				redefines = p.redefines;

		// add replace/redefine support
		if (replaces || redefines) {
			this.redefineProperty(p, replaces || redefines, replaces);
		} else {
			if (p.isBody) {
				this.setBodyProperty(p);
			}
			if (p.isId) {
				this.setIdProperty(p);
			}
			this.addProperty(p);
		}
	}, this));

	types.push(t);
	typesByName[typeName] = t;
};

},{"59":59,"70":70}],68:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Factory;

var _minDash = _dereq_(59);

var _base = _dereq_(66);

var _base2 = _interopRequireDefault(_base);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * A model element factory.
 *
 * @param {Moddle} model
 * @param {Properties} properties
 */
function Factory(model, properties) {
	this.model = model;
	this.properties = properties;
}

Factory.prototype.createType = function (descriptor) {

	var model = this.model;

	var props = this.properties,
			prototype = Object.create(_base2.default.prototype);

	// initialize default values
	(0, _minDash.forEach)(descriptor.properties, function (p) {
		if (!p.isMany && p.default !== undefined) {
			prototype[p.name] = p.default;
		}
	});

	props.defineModel(prototype, model);
	props.defineDescriptor(prototype, descriptor);

	var name = descriptor.ns.name;

	/**
	 * The new type constructor
	 */
	function ModdleElement(attrs) {
		props.define(this, '$type', { value: name, enumerable: true });
		props.define(this, '$attrs', { value: {} });
		props.define(this, '$parent', { writable: true });

		(0, _minDash.forEach)(attrs, (0, _minDash.bind)(function (val, key) {
			this.set(key, val);
		}, this));
	}

	ModdleElement.prototype = prototype;

	ModdleElement.hasType = prototype.$instanceOf = this.model.hasType;

	// static links
	props.defineModel(ModdleElement, model);
	props.defineDescriptor(ModdleElement, descriptor);

	return ModdleElement;
};

},{"59":59,"66":66}],69:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Moddle;

var _minDash = _dereq_(59);

var _factory = _dereq_(68);

var _factory2 = _interopRequireDefault(_factory);

var _registry = _dereq_(72);

var _registry2 = _interopRequireDefault(_registry);

var _properties = _dereq_(71);

var _properties2 = _interopRequireDefault(_properties);

var _ns = _dereq_(70);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

//// Moddle implementation /////////////////////////////////////////////////

/**
 * @class Moddle
 *
 * A model that can be used to create elements of a specific type.
 *
 * @example
 *
 * var Moddle = require('moddle');
 *
 * var pkg = {
 *   name: 'mypackage',
 *   prefix: 'my',
 *   types: [
 *     { name: 'Root' }
 *   ]
 * };
 *
 * var moddle = new Moddle([pkg]);
 *
 * @param {Array<Package>} packages the packages to contain
 */
function Moddle(packages) {

	this.properties = new _properties2.default(this);

	this.factory = new _factory2.default(this, this.properties);
	this.registry = new _registry2.default(packages, this.properties);

	this.typeCache = {};
}

/**
 * Create an instance of the specified type.
 *
 * @method Moddle#create
 *
 * @example
 *
 * var foo = moddle.create('my:Foo');
 * var bar = moddle.create('my:Bar', { id: 'BAR_1' });
 *
 * @param  {String|Object} descriptor the type descriptor or name know to the model
 * @param  {Object} attrs   a number of attributes to initialize the model instance with
 * @return {Object}         model instance
 */
Moddle.prototype.create = function (descriptor, attrs) {
	var Type = this.getType(descriptor);

	if (!Type) {
		throw new Error('unknown type <' + descriptor + '>');
	}

	return new Type(attrs);
};

/**
 * Returns the type representing a given descriptor
 *
 * @method Moddle#getType
 *
 * @example
 *
 * var Foo = moddle.getType('my:Foo');
 * var foo = new Foo({ 'id' : 'FOO_1' });
 *
 * @param  {String|Object} descriptor the type descriptor or name know to the model
 * @return {Object}         the type representing the descriptor
 */
Moddle.prototype.getType = function (descriptor) {

	var cache = this.typeCache;

	var name = (0, _minDash.isString)(descriptor) ? descriptor : descriptor.ns.name;

	var type = cache[name];

	if (!type) {
		descriptor = this.registry.getEffectiveDescriptor(name);
		type = cache[name] = this.factory.createType(descriptor);
	}

	return type;
};

/**
 * Creates an any-element type to be used within model instances.
 *
 * This can be used to create custom elements that lie outside the meta-model.
 * The created element contains all the meta-data required to serialize it
 * as part of meta-model elements.
 *
 * @method Moddle#createAny
 *
 * @example
 *
 * var foo = moddle.createAny('vendor:Foo', 'http://vendor', {
 *   value: 'bar'
 * });
 *
 * var container = moddle.create('my:Container', 'http://my', {
 *   any: [ foo ]
 * });
 *
 * // go ahead and serialize the stuff
 *
 *
 * @param  {String} name  the name of the element
 * @param  {String} nsUri the namespace uri of the element
 * @param  {Object} [properties] a map of properties to initialize the instance with
 * @return {Object} the any type instance
 */
Moddle.prototype.createAny = function (name, nsUri, properties) {

	var nameNs = (0, _ns.parseName)(name);

	var element = {
		$type: name,
		$instanceOf: function $instanceOf(type) {
			return type === this.$type;
		}
	};

	var descriptor = {
		name: name,
		isGeneric: true,
		ns: {
			prefix: nameNs.prefix,
			localName: nameNs.localName,
			uri: nsUri
		}
	};

	this.properties.defineDescriptor(element, descriptor);
	this.properties.defineModel(element, this);
	this.properties.define(element, '$parent', { enumerable: false, writable: true });

	(0, _minDash.forEach)(properties, function (a, key) {
		if ((0, _minDash.isObject)(a) && a.value !== undefined) {
			element[a.name] = a.value;
		} else {
			element[key] = a;
		}
	});

	return element;
};

/**
 * Returns a registered package by uri or prefix
 *
 * @return {Object} the package
 */
Moddle.prototype.getPackage = function (uriOrPrefix) {
	return this.registry.getPackage(uriOrPrefix);
};

/**
 * Returns a snapshot of all known packages
 *
 * @return {Object} the package
 */
Moddle.prototype.getPackages = function () {
	return this.registry.getPackages();
};

/**
 * Returns the descriptor for an element
 */
Moddle.prototype.getElementDescriptor = function (element) {
	return element.$descriptor;
};

/**
 * Returns true if the given descriptor or instance
 * represents the given type.
 *
 * May be applied to this, if element is omitted.
 */
Moddle.prototype.hasType = function (element, type) {
	if (type === undefined) {
		type = element;
		element = this;
	}

	var descriptor = element.$model.getElementDescriptor(element);

	return type in descriptor.allTypesByName;
};

/**
 * Returns the descriptor of an elements named property
 */
Moddle.prototype.getPropertyDescriptor = function (element, property) {
	return this.getElementDescriptor(element).propertiesByName[property];
};

/**
 * Returns a mapped type's descriptor
 */
Moddle.prototype.getTypeDescriptor = function (type) {
	return this.registry.typeMap[type];
};

},{"59":59,"68":68,"70":70,"71":71,"72":72}],70:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.parseName = parseName;
/**
 * Parses a namespaced attribute name of the form (ns:)localName to an object,
 * given a default prefix to assume in case no explicit namespace is given.
 *
 * @param {String} name
 * @param {String} [defaultPrefix] the default prefix to take, if none is present.
 *
 * @return {Object} the parsed name
 */
function parseName(name, defaultPrefix) {
	var parts = name.split(/:/),
			localName,
			prefix;

	// no prefix (i.e. only local name)
	if (parts.length === 1) {
		localName = name;
		prefix = defaultPrefix;
	} else
		// prefix + local name
		if (parts.length === 2) {
			localName = parts[1];
			prefix = parts[0];
		} else {
			throw new Error('expected <prefix:localName> or <localName>, got ' + name);
		}

	name = (prefix ? prefix + ':' : '') + localName;

	return {
		name: name,
		prefix: prefix,
		localName: localName
	};
}

},{}],71:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Properties;
/**
 * A utility that gets and sets properties of model elements.
 *
 * @param {Model} model
 */
function Properties(model) {
	this.model = model;
}

/**
 * Sets a named property on the target element.
 * If the value is undefined, the property gets deleted.
 *
 * @param {Object} target
 * @param {String} name
 * @param {Object} value
 */
Properties.prototype.set = function (target, name, value) {

	var property = this.model.getPropertyDescriptor(target, name);

	var propertyName = property && property.name;

	if (isUndefined(value)) {
		// unset the property, if the specified value is undefined;
		// delete from $attrs (for extensions) or the target itself
		if (property) {
			delete target[propertyName];
		} else {
			delete target.$attrs[name];
		}
	} else {
		// set the property, defining well defined properties on the fly
		// or simply updating them in target.$attrs (for extensions)
		if (property) {
			if (propertyName in target) {
				target[propertyName] = value;
			} else {
				defineProperty(target, property, value);
			}
		} else {
			target.$attrs[name] = value;
		}
	}
};

/**
 * Returns the named property of the given element
 *
 * @param  {Object} target
 * @param  {String} name
 *
 * @return {Object}
 */
Properties.prototype.get = function (target, name) {

	var property = this.model.getPropertyDescriptor(target, name);

	if (!property) {
		return target.$attrs[name];
	}

	var propertyName = property.name;

	// check if access to collection property and lazily initialize it
	if (!target[propertyName] && property.isMany) {
		defineProperty(target, property, []);
	}

	return target[propertyName];
};

/**
 * Define a property on the target element
 *
 * @param  {Object} target
 * @param  {String} name
 * @param  {Object} options
 */
Properties.prototype.define = function (target, name, options) {
	Object.defineProperty(target, name, options);
};

/**
 * Define the descriptor for an element
 */
Properties.prototype.defineDescriptor = function (target, descriptor) {
	this.define(target, '$descriptor', { value: descriptor });
};

/**
 * Define the model for an element
 */
Properties.prototype.defineModel = function (target, model) {
	this.define(target, '$model', { value: model });
};

function isUndefined(val) {
	return typeof val === 'undefined';
}

function defineProperty(target, property, value) {
	Object.defineProperty(target, property.name, {
		enumerable: !property.isReference,
		writable: true,
		value: value,
		configurable: true
	});
}

},{}],72:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = Registry;

var _minDash = _dereq_(59);

var _types = _dereq_(73);

var _descriptorBuilder = _dereq_(67);

var _descriptorBuilder2 = _interopRequireDefault(_descriptorBuilder);

var _ns = _dereq_(70);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * A registry of Moddle packages.
 *
 * @param {Array<Package>} packages
 * @param {Properties} properties
 */
function Registry(packages, properties) {
	this.packageMap = {};
	this.typeMap = {};

	this.packages = [];

	this.properties = properties;

	(0, _minDash.forEach)(packages, (0, _minDash.bind)(this.registerPackage, this));
}

Registry.prototype.getPackage = function (uriOrPrefix) {
	return this.packageMap[uriOrPrefix];
};

Registry.prototype.getPackages = function () {
	return this.packages;
};

Registry.prototype.registerPackage = function (pkg) {

	// copy package
	pkg = (0, _minDash.assign)({}, pkg);

	var pkgMap = this.packageMap;

	ensureAvailable(pkgMap, pkg, 'prefix');
	ensureAvailable(pkgMap, pkg, 'uri');

	// register types
	(0, _minDash.forEach)(pkg.types, (0, _minDash.bind)(function (descriptor) {
		this.registerType(descriptor, pkg);
	}, this));

	pkgMap[pkg.uri] = pkgMap[pkg.prefix] = pkg;
	this.packages.push(pkg);
};

/**
 * Register a type from a specific package with us
 */
Registry.prototype.registerType = function (type, pkg) {

	type = (0, _minDash.assign)({}, type, {
		superClass: (type.superClass || []).slice(),
		extends: (type.extends || []).slice(),
		properties: (type.properties || []).slice(),
		meta: (0, _minDash.assign)(({}, type.meta || {}))
	});

	var ns = (0, _ns.parseName)(type.name, pkg.prefix),
			name = ns.name,
			propertiesByName = {};

	// parse properties
	(0, _minDash.forEach)(type.properties, (0, _minDash.bind)(function (p) {

		// namespace property names
		var propertyNs = (0, _ns.parseName)(p.name, ns.prefix),
				propertyName = propertyNs.name;

		// namespace property types
		if (!(0, _types.isBuiltIn)(p.type)) {
			p.type = (0, _ns.parseName)(p.type, propertyNs.prefix).name;
		}

		(0, _minDash.assign)(p, {
			ns: propertyNs,
			name: propertyName
		});

		propertiesByName[propertyName] = p;
	}, this));

	// update ns + name
	(0, _minDash.assign)(type, {
		ns: ns,
		name: name,
		propertiesByName: propertiesByName
	});

	(0, _minDash.forEach)(type.extends, (0, _minDash.bind)(function (extendsName) {
		var extended = this.typeMap[extendsName];

		extended.traits = extended.traits || [];
		extended.traits.push(name);
	}, this));

	// link to package
	this.definePackage(type, pkg);

	// register
	this.typeMap[name] = type;
};

/**
 * Traverse the type hierarchy from bottom to top,
 * calling iterator with (type, inherited) for all elements in
 * the inheritance chain.
 *
 * @param {Object} nsName
 * @param {Function} iterator
 * @param {Boolean} [trait=false]
 */
Registry.prototype.mapTypes = function (nsName, iterator, trait) {

	var type = (0, _types.isBuiltIn)(nsName.name) ? { name: nsName.name } : this.typeMap[nsName.name];

	var self = this;

	/**
	 * Traverse the selected trait.
	 *
	 * @param {String} cls
	 */
	function traverseTrait(cls) {
		return traverseSuper(cls, true);
	}

	/**
	 * Traverse the selected super type or trait
	 *
	 * @param {String} cls
	 * @param {Boolean} [trait=false]
	 */
	function traverseSuper(cls, trait) {
		var parentNs = (0, _ns.parseName)(cls, (0, _types.isBuiltIn)(cls) ? '' : nsName.prefix);
		self.mapTypes(parentNs, iterator, trait);
	}

	if (!type) {
		throw new Error('unknown type <' + nsName.name + '>');
	}

	(0, _minDash.forEach)(type.superClass, trait ? traverseTrait : traverseSuper);

	// call iterator with (type, inherited=!trait)
	iterator(type, !trait);

	(0, _minDash.forEach)(type.traits, traverseTrait);
};

/**
 * Returns the effective descriptor for a type.
 *
 * @param  {String} type the namespaced name (ns:localName) of the type
 *
 * @return {Descriptor} the resulting effective descriptor
 */
Registry.prototype.getEffectiveDescriptor = function (name) {

	var nsName = (0, _ns.parseName)(name);

	var builder = new _descriptorBuilder2.default(nsName);

	this.mapTypes(nsName, function (type, inherited) {
		builder.addTrait(type, inherited);
	});

	var descriptor = builder.build();

	// define package link
	this.definePackage(descriptor, descriptor.allTypes[descriptor.allTypes.length - 1].$pkg);

	return descriptor;
};

Registry.prototype.definePackage = function (target, pkg) {
	this.properties.define(target, '$pkg', { value: pkg });
};

///////// helpers ////////////////////////////

function ensureAvailable(packageMap, pkg, identifierKey) {

	var value = pkg[identifierKey];

	if (value in packageMap) {
		throw new Error('package with ' + identifierKey + ' <' + value + '> already defined');
	}
}

},{"59":59,"67":67,"70":70,"73":73}],73:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.coerceType = coerceType;
exports.isBuiltIn = isBuiltIn;
exports.isSimple = isSimple;
/**
 * Built-in moddle types
 */
var BUILTINS = {
	String: true,
	Boolean: true,
	Integer: true,
	Real: true,
	Element: true
};

/**
 * Converters for built in types from string representations
 */
var TYPE_CONVERTERS = {
	String: function String(s) {
		return s;
	},
	Boolean: function Boolean(s) {
		return s === 'true';
	},
	Integer: function Integer(s) {
		return parseInt(s, 10);
	},
	Real: function Real(s) {
		return parseFloat(s, 10);
	}
};

/**
 * Convert a type to its real representation
 */
function coerceType(type, value) {

	var converter = TYPE_CONVERTERS[type];

	if (converter) {
		return converter(value);
	} else {
		return value;
	}
}

/**
 * Return whether the given type is built-in
 */
function isBuiltIn(type) {
	return !!BUILTINS[type];
}

/**
 * Return whether the given type is simple
 */
function isSimple(type) {
	return !!TYPE_CONVERTERS[type];
}

},{}],74:[function(_dereq_,module,exports){
'use strict';

module.exports = _dereq_(76);

module.exports.Collection = _dereq_(75);

},{"75":75,"76":76}],75:[function(_dereq_,module,exports){
'use strict';

/**
 * An empty collection stub. Use {@link RefsCollection.extend} to extend a
 * collection with ref semantics.
 *
 * @class RefsCollection
 */

/**
 * Extends a collection with {@link Refs} aware methods
 *
 * @memberof RefsCollection
 * @static
 *
 * @param  {Array<Object>} collection
 * @param  {Refs} refs instance
 * @param  {Object} property represented by the collection
 * @param  {Object} target object the collection is attached to
 *
 * @return {RefsCollection<Object>} the extended array
 */

function extend(collection, refs, property, target) {

	var inverseProperty = property.inverse;

	/**
	 * Removes the given element from the array and returns it.
	 *
	 * @method RefsCollection#remove
	 *
	 * @param {Object} element the element to remove
	 */
	Object.defineProperty(collection, 'remove', {
		value: function value(element) {
			var idx = this.indexOf(element);
			if (idx !== -1) {
				this.splice(idx, 1);

				// unset inverse
				refs.unset(element, inverseProperty, target);
			}

			return element;
		}
	});

	/**
	 * Returns true if the collection contains the given element
	 *
	 * @method RefsCollection#contains
	 *
	 * @param {Object} element the element to check for
	 */
	Object.defineProperty(collection, 'contains', {
		value: function value(element) {
			return this.indexOf(element) !== -1;
		}
	});

	/**
	 * Adds an element to the array, unless it exists already (set semantics).
	 *
	 * @method RefsCollection#add
	 *
	 * @param {Object} element the element to add
	 * @param {Number} optional index to add element to
	 *                 (possibly moving other elements around)
	 */
	Object.defineProperty(collection, 'add', {
		value: function value(element, idx) {

			var currentIdx = this.indexOf(element);

			if (typeof idx === 'undefined') {

				if (currentIdx !== -1) {
					// element already in collection (!)
					return;
				}

				// add to end of array, as no idx is specified
				idx = this.length;
			}

			// handle already in collection
			if (currentIdx !== -1) {

				// remove element from currentIdx
				this.splice(currentIdx, 1);
			}

			// add element at idx
			this.splice(idx, 0, element);

			if (currentIdx === -1) {
				// set inverse, unless element was
				// in collection already
				refs.set(element, inverseProperty, target);
			}
		}
	});

	// a simple marker, identifying this element
	// as being a refs collection
	Object.defineProperty(collection, '__refs_collection', {
		value: true
	});

	return collection;
}

function isExtended(collection) {
	return collection.__refs_collection === true;
}

module.exports.extend = extend;

module.exports.isExtended = isExtended;

},{}],76:[function(_dereq_,module,exports){
'use strict';

var Collection = _dereq_(75);

function hasOwnProperty(e, property) {
	return Object.prototype.hasOwnProperty.call(e, property.name || property);
}

function defineCollectionProperty(ref, property, target) {

	var collection = Collection.extend(target[property.name] || [], ref, property, target);

	Object.defineProperty(target, property.name, {
		enumerable: property.enumerable,
		value: collection
	});

	if (collection.length) {

		collection.forEach(function (o) {
			ref.set(o, property.inverse, target);
		});
	}
}

function defineProperty(ref, property, target) {

	var inverseProperty = property.inverse;

	var _value = target[property.name];

	Object.defineProperty(target, property.name, {
		configurable: property.configurable,
		enumerable: property.enumerable,

		get: function get() {
			return _value;
		},

		set: function set(value) {

			// return if we already performed all changes
			if (value === _value) {
				return;
			}

			var old = _value;

			// temporary set null
			_value = null;

			if (old) {
				ref.unset(old, inverseProperty, target);
			}

			// set new value
			_value = value;

			// set inverse value
			ref.set(_value, inverseProperty, target);
		}
	});
}

/**
 * Creates a new references object defining two inversly related
 * attribute descriptors a and b.
 *
 * <p>
 *   When bound to an object using {@link Refs#bind} the references
 *   get activated and ensure that add and remove operations are applied
 *   reversely, too.
 * </p>
 *
 * <p>
 *   For attributes represented as collections {@link Refs} provides the
 *   {@link RefsCollection#add}, {@link RefsCollection#remove} and {@link RefsCollection#contains} extensions
 *   that must be used to properly hook into the inverse change mechanism.
 * </p>
 *
 * @class Refs
 *
 * @classdesc A bi-directional reference between two attributes.
 *
 * @param {Refs.AttributeDescriptor} a property descriptor
 * @param {Refs.AttributeDescriptor} b property descriptor
 *
 * @example
 *
 * var refs = Refs({ name: 'wheels', collection: true, enumerable: true }, { name: 'car' });
 *
 * var car = { name: 'toyota' };
 * var wheels = [{ pos: 'front-left' }, { pos: 'front-right' }];
 *
 * refs.bind(car, 'wheels');
 *
 * car.wheels // []
 * car.wheels.add(wheels[0]);
 * car.wheels.add(wheels[1]);
 *
 * car.wheels // [{ pos: 'front-left' }, { pos: 'front-right' }]
 *
 * wheels[0].car // { name: 'toyota' };
 * car.wheels.remove(wheels[0]);
 *
 * wheels[0].car // undefined
 */
function Refs(a, b) {

	if (!(this instanceof Refs)) {
		return new Refs(a, b);
	}

	// link
	a.inverse = b;
	b.inverse = a;

	this.props = {};
	this.props[a.name] = a;
	this.props[b.name] = b;
}

/**
 * Binds one side of a bi-directional reference to a
 * target object.
 *
 * @memberOf Refs
 *
 * @param  {Object} target
 * @param  {String} property
 */
Refs.prototype.bind = function (target, property) {
	if (typeof property === 'string') {
		if (!this.props[property]) {
			throw new Error('no property <' + property + '> in ref');
		}
		property = this.props[property];
	}

	if (property.collection) {
		defineCollectionProperty(this, property, target);
	} else {
		defineProperty(this, property, target);
	}
};

Refs.prototype.ensureRefsCollection = function (target, property) {

	var collection = target[property.name];

	if (!Collection.isExtended(collection)) {
		defineCollectionProperty(this, property, target);
	}

	return collection;
};

Refs.prototype.ensureBound = function (target, property) {
	if (!hasOwnProperty(target, property)) {
		this.bind(target, property);
	}
};

Refs.prototype.unset = function (target, property, value) {

	if (target) {
		this.ensureBound(target, property);

		if (property.collection) {
			this.ensureRefsCollection(target, property).remove(value);
		} else {
			target[property.name] = undefined;
		}
	}
};

Refs.prototype.set = function (target, property, value) {

	if (target) {
		this.ensureBound(target, property);

		if (property.collection) {
			this.ensureRefsCollection(target, property).add(value);
		} else {
			target[property.name] = value;
		}
	}
};

module.exports = Refs;

/**
 * An attribute descriptor to be used specify an attribute in a {@link Refs} instance
 *
 * @typedef {Object} Refs.AttributeDescriptor
 * @property {String} name
 * @property {boolean} [collection=false]
 * @property {boolean} [enumerable=false]
 */

},{"75":75}],77:[function(_dereq_,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

Object.defineProperty(exports, '__esModule', { value: true });

var fromCharCode = String.fromCharCode;

var hasOwnProperty = Object.prototype.hasOwnProperty;

var ENTITY_PATTERN = /&#(\d+);|&#x([0-9a-f]+);|&(\w+);/ig;

var ENTITY_MAPPING = {
	'amp': '&',
	'apos': '\'',
	'gt': '>',
	'lt': '<',
	'quot': '"'
};

// map UPPERCASE variants of supported special chars
Object.keys(ENTITY_MAPPING).forEach(function (k) {
	ENTITY_MAPPING[k.toUpperCase()] = ENTITY_MAPPING[k];
});

function replaceEntities(_, d, x, z) {

	// reserved names, i.e. &nbsp;
	if (z) {
		if (hasOwnProperty.call(ENTITY_MAPPING, z)) {
			return ENTITY_MAPPING[z];
		} else {
			// fall back to original value
			return '&' + z + ';';
		}
	}

	// decimal encoded char
	if (d) {
		return fromCharCode(d);
	}

	// hex encoded char
	return fromCharCode(parseInt(x, 16));
}

/**
 * A basic entity decoder that can decode a minimal
 * sub-set of reserved names (&amp;) as well as
 * hex (&#xaaf;) and decimal (&#1231;) encoded characters.
 *
 * @param {string} str
 *
 * @return {string} decoded string
 */
function decodeEntities(s) {
	if (s.length > 3 && s.indexOf('&') !== -1) {
		return s.replace(ENTITY_PATTERN, replaceEntities);
	}

	return s;
}

var XSI_URI = 'http://www.w3.org/2001/XMLSchema-instance';
var XSI_PREFIX = 'xsi';
var XSI_TYPE = 'xsi:type';

var NON_WHITESPACE_OUTSIDE_ROOT_NODE = 'non-whitespace outside of root node';

function error(msg) {
	return new Error(msg);
}

function missingNamespaceForPrefix(prefix) {
	return 'missing namespace for prefix <' + prefix + '>';
}

function getter(getFn) {
	return {
		'get': getFn,
		'enumerable': true
	};
}

function cloneNsMatrix(nsMatrix) {
	var clone = {},
			key;
	for (key in nsMatrix) {
		clone[key] = nsMatrix[key];
	}
	return clone;
}

function uriPrefix(prefix) {
	return prefix + '$uri';
}

function buildNsMatrix(nsUriToPrefix) {
	var nsMatrix = {},
			uri,
			prefix;

	for (uri in nsUriToPrefix) {
		prefix = nsUriToPrefix[uri];
		nsMatrix[prefix] = prefix;
		nsMatrix[uriPrefix(prefix)] = uri;
	}

	return nsMatrix;
}

function noopGetContext() {
	return { 'line': 0, 'column': 0 };
}

function throwFunc(err) {
	throw err;
}

/**
 * Creates a new parser with the given options.
 *
 * @constructor
 *
 * @param  {!Object<string, ?>=} options
 */
function Parser(options) {

	if (!this) {
		return new Parser(options);
	}

	var proxy = options && options['proxy'];

	var onText,
			onOpenTag,
			onCloseTag,
			onCDATA,
			onError = throwFunc,
			onWarning,
			onComment,
			onQuestion,
			onAttention;

	var getContext = noopGetContext;

	/**
	 * Do we need to parse the current elements attributes for namespaces?
	 *
	 * @type {boolean}
	 */
	var maybeNS = false;

	/**
	 * Do we process namespaces at all?
	 *
	 * @type {boolean}
	 */
	var isNamespace = false;

	/**
	 * The caught error returned on parse end
	 *
	 * @type {Error}
	 */
	var returnError = null;

	/**
	 * Should we stop parsing?
	 *
	 * @type {boolean}
	 */
	var parseStop = false;

	/**
	 * A map of { uri: prefix } used by the parser.
	 *
	 * This map will ensure we can normalize prefixes during processing;
	 * for each uri, only one prefix will be exposed to the handlers.
	 *
	 * @type {!Object<string, string>}}
	 */
	var nsUriToPrefix;

	/**
	 * Handle parse error.
	 *
	 * @param  {string|Error} err
	 */
	function handleError(err) {
		if (!(err instanceof Error)) {
			err = error(err);
		}

		returnError = err;

		onError(err, getContext);
	}

	/**
	 * Handle parse error.
	 *
	 * @param  {string|Error} err
	 */
	function handleWarning(err) {

		if (!onWarning) {
			return;
		}

		if (!(err instanceof Error)) {
			err = error(err);
		}

		onWarning(err, getContext);
	}

	/**
	 * Register parse listener.
	 *
	 * @param  {string}   name
	 * @param  {Function} cb
	 *
	 * @return {Parser}
	 */
	this['on'] = function (name, cb) {

		if (typeof cb !== 'function') {
			throw error('required args <name, cb>');
		}

		switch (name) {
			case 'openTag':
				onOpenTag = cb;break;
			case 'text':
				onText = cb;break;
			case 'closeTag':
				onCloseTag = cb;break;
			case 'error':
				onError = cb;break;
			case 'warn':
				onWarning = cb;break;
			case 'cdata':
				onCDATA = cb;break;
			case 'attention':
				onAttention = cb;break; // <!XXXXX zzzz="eeee">
			case 'question':
				onQuestion = cb;break; // <? ....  ?>
			case 'comment':
				onComment = cb;break;
			default:
				throw error('unsupported event: ' + name);
		}

		return this;
	};

	/**
	 * Set the namespace to prefix mapping.
	 *
	 * @example
	 *
	 * parser.ns({
	 *   'http://foo': 'foo',
	 *   'http://bar': 'bar'
	 * });
	 *
	 * @param  {!Object<string, string>} nsMap
	 *
	 * @return {Parser}
	 */
	this['ns'] = function (nsMap) {

		if (typeof nsMap === 'undefined') {
			nsMap = {};
		}

		if ((typeof nsMap === 'undefined' ? 'undefined' : _typeof(nsMap)) !== 'object') {
			throw error('required args <nsMap={}>');
		}

		var _nsUriToPrefix = {},
				k;

		for (k in nsMap) {
			_nsUriToPrefix[k] = nsMap[k];
		}

		// FORCE default mapping for schema instance
		_nsUriToPrefix[XSI_URI] = XSI_PREFIX;

		isNamespace = true;
		nsUriToPrefix = _nsUriToPrefix;

		return this;
	};

	/**
	 * Parse xml string.
	 *
	 * @param  {string} xml
	 *
	 * @return {Error} returnError, if not thrown
	 */
	this['parse'] = function (xml) {
		if (typeof xml !== 'string') {
			throw error('required args <xml=string>');
		}

		returnError = null;

		parse(xml);

		getContext = noopGetContext;
		parseStop = false;

		return returnError;
	};

	/**
	 * Stop parsing.
	 */
	this['stop'] = function () {
		parseStop = true;
	};

	/**
	 * Parse string, invoking configured listeners on element.
	 *
	 * @param  {string} xml
	 */
	function parse(xml) {
		var nsMatrixStack = isNamespace ? [] : null,
				nsMatrix = isNamespace ? buildNsMatrix(nsUriToPrefix) : null,
				_nsMatrix,
				nodeStack = [],
				anonymousNsCount = 0,
				tagStart = false,
				tagEnd = false,
				i = 0,
				j = 0,
				x,
				y,
				q,
				w,
				xmlns,
				elementName,
				_elementName,
				elementProxy;

		var attrsString = '',
				attrsStart = 0,
				cachedAttrs // false = parsed with errors, null = needs parsing
		;

		/**
		 * Parse attributes on demand and returns the parsed attributes.
		 *
		 * Return semantics: (1) `false` on attribute parse error,
		 * (2) object hash on extracted attrs.
		 *
		 * @return {boolean|Object}
		 */
		function getAttrs() {
			if (cachedAttrs !== null) {
				return cachedAttrs;
			}

			var nsUri,
					nsUriPrefix,
					nsName,
					defaultAlias = isNamespace && nsMatrix['xmlns'],
					attrList = isNamespace && maybeNS ? [] : null,
					i = attrsStart,
					s = attrsString,
					l = s.length,
					hasNewMatrix,
					newalias,
					value,
					alias,
					name,
					attrs = {},
					seenAttrs = {},
					skipAttr,
					w,
					j;

			parseAttr: for (; i < l; i++) {
				skipAttr = false;
				w = s.charCodeAt(i);

				if (w === 32 || w < 14 && w > 8) {
					// WHITESPACE={ \f\n\r\t\v}
					continue;
				}

				// wait for non whitespace character
				if (w < 65 || w > 122 || w > 90 && w < 97) {
					if (w !== 95 && w !== 58) {
						// char 95"_" 58":"
						handleWarning('illegal first char attribute name');
						skipAttr = true;
					}
				}

				// parse attribute name
				for (j = i + 1; j < l; j++) {
					w = s.charCodeAt(j);

					if (w > 96 && w < 123 || w > 64 && w < 91 || w > 47 && w < 59 || w === 46 || // '.'
					w === 45 || // '-'
					w === 95 // '_'
					) {
							continue;
						}

					// unexpected whitespace
					if (w === 32 || w < 14 && w > 8) {
						// WHITESPACE
						handleWarning('missing attribute value');
						i = j;

						continue parseAttr;
					}

					// expected "="
					if (w === 61) {
						// "=" == 61
						break;
					}

					handleWarning('illegal attribute name char');
					skipAttr = true;
				}

				name = s.substring(i, j);

				if (name === 'xmlns:xmlns') {
					handleWarning('illegal declaration of xmlns');
					skipAttr = true;
				}

				w = s.charCodeAt(j + 1);

				if (w === 34) {
					// '"'
					j = s.indexOf('"', i = j + 2);

					if (j === -1) {
						j = s.indexOf('\'', i);

						if (j !== -1) {
							handleWarning('attribute value quote missmatch');
							skipAttr = true;
						}
					}
				} else if (w === 39) {
					// "'"
					j = s.indexOf('\'', i = j + 2);

					if (j === -1) {
						j = s.indexOf('"', i);

						if (j !== -1) {
							handleWarning('attribute value quote missmatch');
							skipAttr = true;
						}
					}
				} else {
					handleWarning('missing attribute value quotes');
					skipAttr = true;

					// skip to next space
					for (j = j + 1; j < l; j++) {
						w = s.charCodeAt(j + 1);

						if (w === 32 || w < 14 && w > 8) {
							// WHITESPACE
							break;
						}
					}
				}

				if (j === -1) {
					handleWarning('missing closing quotes');

					j = l;
					skipAttr = true;
				}

				if (!skipAttr) {
					value = s.substring(i, j);
				}

				i = j;

				// ensure SPACE follows attribute
				// skip illegal content otherwise
				// example a="b"c
				for (; j + 1 < l; j++) {
					w = s.charCodeAt(j + 1);

					if (w === 32 || w < 14 && w > 8) {
						// WHITESPACE
						break;
					}

					// FIRST ILLEGAL CHAR
					if (i === j) {
						handleWarning('illegal character after attribute end');
						skipAttr = true;
					}
				}

				// advance cursor to next attribute
				i = j + 1;

				if (skipAttr) {
					continue parseAttr;
				}

				// check attribute re-declaration
				if (name in seenAttrs) {
					handleWarning('attribute <' + name + '> already defined');
					continue;
				}

				seenAttrs[name] = true;

				if (!isNamespace) {
					attrs[name] = value;
					continue;
				}

				// try to extract namespace information
				if (maybeNS) {
					newalias = name === 'xmlns' ? 'xmlns' : name.charCodeAt(0) === 120 && name.substr(0, 6) === 'xmlns:' ? name.substr(6) : null;

					// handle xmlns(:alias) assignment
					if (newalias !== null) {
						nsUri = decodeEntities(value);
						nsUriPrefix = uriPrefix(newalias);

						alias = nsUriToPrefix[nsUri];

						if (!alias) {
							// no prefix defined or prefix collision
							if (newalias === 'xmlns' || nsUriPrefix in nsMatrix && nsMatrix[nsUriPrefix] !== nsUri) {
								// alocate free ns prefix
								do {
									alias = 'ns' + anonymousNsCount++;
								} while (typeof nsMatrix[alias] !== 'undefined');
							} else {
								alias = newalias;
							}

							nsUriToPrefix[nsUri] = alias;
						}

						if (nsMatrix[newalias] !== alias) {
							if (!hasNewMatrix) {
								nsMatrix = cloneNsMatrix(nsMatrix);
								hasNewMatrix = true;
							}

							nsMatrix[newalias] = alias;
							if (newalias === 'xmlns') {
								nsMatrix[uriPrefix(alias)] = nsUri;
								defaultAlias = alias;
							}

							nsMatrix[nsUriPrefix] = nsUri;
						}

						// expose xmlns(:asd)="..." in attributes
						attrs[name] = value;
						continue;
					}

					// collect attributes until all namespace
					// declarations are processed
					attrList.push(name, value);
					continue;
				} /** end if (maybeNs) */

				// handle attributes on element without
				// namespace declarations
				w = name.indexOf(':');
				if (w === -1) {
					attrs[name] = value;
					continue;
				}

				// normalize ns attribute name
				if (!(nsName = nsMatrix[name.substring(0, w)])) {
					handleWarning(missingNamespaceForPrefix(name.substring(0, w)));
					continue;
				}

				name = defaultAlias === nsName ? name.substr(w + 1) : nsName + name.substr(w);
				// end: normalize ns attribute name

				// normalize xsi:type ns attribute value
				if (name === XSI_TYPE) {
					w = value.indexOf(':');

					if (w !== -1) {
						nsName = value.substring(0, w);
						// handle default prefixes, i.e. xs:String gracefully
						nsName = nsMatrix[nsName] || nsName;
						value = nsName + value.substring(w);
					} else {
						value = defaultAlias + ':' + value;
					}
				}
				// end: normalize xsi:type ns attribute value

				attrs[name] = value;
			}

			// handle deferred, possibly namespaced attributes
			if (maybeNS) {

				// normalize captured attributes
				for (i = 0, l = attrList.length; i < l; i++) {

					name = attrList[i++];
					value = attrList[i];

					w = name.indexOf(':');

					if (w !== -1) {
						// normalize ns attribute name
						if (!(nsName = nsMatrix[name.substring(0, w)])) {
							handleWarning(missingNamespaceForPrefix(name.substring(0, w)));
							continue;
						}

						name = defaultAlias === nsName ? name.substr(w + 1) : nsName + name.substr(w);
						// end: normalize ns attribute name

						// normalize xsi:type ns attribute value
						if (name === XSI_TYPE) {
							w = value.indexOf(':');

							if (w !== -1) {
								nsName = value.substring(0, w);
								// handle default prefixes, i.e. xs:String gracefully
								nsName = nsMatrix[nsName] || nsName;
								value = nsName + value.substring(w);
							} else {
								value = defaultAlias + ':' + value;
							}
						}
						// end: normalize xsi:type ns attribute value
					}

					attrs[name] = value;
				}
				// end: normalize captured attributes
			}

			return cachedAttrs = attrs;
		}

		/**
		 * Extract the parse context { line, column, part }
		 * from the current parser position.
		 *
		 * @return {Object} parse context
		 */
		function getParseContext() {
			var splitsRe = /(\r\n|\r|\n)/g;

			var line = 0;
			var column = 0;
			var startOfLine = 0;
			var endOfLine = j;
			var match;
			var data;

			while (i >= startOfLine) {

				match = splitsRe.exec(xml);

				if (!match) {
					break;
				}

				// end of line = (break idx + break chars)
				endOfLine = match[0].length + match.index;

				if (endOfLine > i) {
					break;
				}

				// advance to next line
				line += 1;

				startOfLine = endOfLine;
			}

			// EOF errors
			if (i == -1) {
				column = endOfLine;
				data = xml.substring(j);
			} else
				// start errors
				if (j === 0) {
					console.log(i - startOfLine);
					data = xml.substring(j, i);
				}
				// other errors
				else {
						column = i - startOfLine;
						data = j == -1 ? xml.substring(i) : xml.substring(i, j + 1);
					}

			return {
				'data': data,
				'line': line,
				'column': column
			};
		}

		getContext = getParseContext;

		if (proxy) {
			elementProxy = Object.create({}, {
				'name': getter(function () {
					return elementName;
				}),
				'originalName': getter(function () {
					return _elementName;
				}),
				'attrs': getter(getAttrs),
				'ns': getter(function () {
					return nsMatrix;
				})
			});
		}

		// actual parse logic
		while (j !== -1) {

			if (xml.charCodeAt(j) === 60) {
				// "<"
				i = j;
			} else {
				i = xml.indexOf('<', j);
			}

			// parse end
			if (i === -1) {
				if (nodeStack.length) {
					return handleError('unexpected end of file');
				}

				if (j === 0) {
					return handleError('missing start tag');
				}

				if (j < xml.length) {
					if (xml.substring(j).trim()) {
						handleWarning(NON_WHITESPACE_OUTSIDE_ROOT_NODE);
					}
				}

				return;
			}

			// parse text
			if (j !== i) {

				if (nodeStack.length) {
					if (onText) {
						onText(xml.substring(j, i), decodeEntities, getContext);

						if (parseStop) {
							return;
						}
					}
				} else {
					if (xml.substring(j, i).trim()) {
						handleWarning(NON_WHITESPACE_OUTSIDE_ROOT_NODE);

						if (parseStop) {
							return;
						}
					}
				}
			}

			w = xml.charCodeAt(i + 1);

			// parse comments + CDATA
			if (w === 33) {
				// "!"
				w = xml.charCodeAt(i + 2);
				if (w === 91 && xml.substr(i + 3, 6) === 'CDATA[') {
					// 91 == "["
					j = xml.indexOf(']]>', i);
					if (j === -1) {
						return handleError('unclosed cdata');
					}

					if (onCDATA) {
						onCDATA(xml.substring(i + 9, j), getContext);
						if (parseStop) {
							return;
						}
					}

					j += 3;
					continue;
				}

				if (w === 45 && xml.charCodeAt(i + 3) === 45) {
					// 45 == "-"
					j = xml.indexOf('-->', i);
					if (j === -1) {
						return handleError('unclosed comment');
					}

					if (onComment) {
						onComment(xml.substring(i + 4, j), decodeEntities, getContext);
						if (parseStop) {
							return;
						}
					}

					j += 3;
					continue;
				}

				j = xml.indexOf('>', i + 1);
				if (j === -1) {
					return handleError('unclosed tag');
				}

				if (onAttention) {
					onAttention(xml.substring(i, j + 1), decodeEntities, getContext);
					if (parseStop) {
						return;
					}
				}

				j += 1;
				continue;
			}

			if (w === 63) {
				// "?"
				j = xml.indexOf('?>', i);
				if (j === -1) {
					return handleError('unclosed question');
				}

				if (onQuestion) {
					onQuestion(xml.substring(i, j + 2), getContext);
					if (parseStop) {
						return;
					}
				}

				j += 2;
				continue;
			}

			j = xml.indexOf('>', i + 1);

			if (j == -1) {
				return handleError('unclosed tag');
			}

			// don't process attributes;
			// there are none
			cachedAttrs = {};

			// if (xml.charCodeAt(i+1) === 47) { // </...
			if (w === 47) {
				// </...
				tagStart = false;
				tagEnd = true;

				if (!nodeStack.length) {
					return handleError('missing open tag');
				}

				// verify open <-> close tag match
				x = elementName = nodeStack.pop();
				q = i + 2 + x.length;

				if (xml.substring(i + 2, q) !== x) {
					return handleError('closing tag mismatch');
				}

				// verify chars in close tag
				for (; q < j; q++) {
					w = xml.charCodeAt(q);

					if (w === 32 || w > 8 && w < 14) {
						// \f\n\r\t\v space
						continue;
					}

					return handleError('close tag');
				}
			} else {
				if (xml.charCodeAt(j - 1) === 47) {
					// .../>
					x = elementName = xml.substring(i + 1, j - 1);

					tagStart = true;
					tagEnd = true;
				} else {
					x = elementName = xml.substring(i + 1, j);

					tagStart = true;
					tagEnd = false;
				}

				if (!(w > 96 && w < 123 || w > 64 && w < 91 || w === 95 || w === 58)) {
					// char 95"_" 58":"
					return handleError('illegal first char nodeName');
				}

				for (q = 1, y = x.length; q < y; q++) {
					w = x.charCodeAt(q);

					if (w > 96 && w < 123 || w > 64 && w < 91 || w > 47 && w < 59 || w === 45 || w === 95 || w == 46) {
						continue;
					}

					if (w === 32 || w < 14 && w > 8) {
						// \f\n\r\t\v space
						elementName = x.substring(0, q);
						// maybe there are attributes
						cachedAttrs = null;
						break;
					}

					return handleError('invalid nodeName');
				}

				if (!tagEnd) {
					nodeStack.push(elementName);
				}
			}

			if (isNamespace) {

				_nsMatrix = nsMatrix;

				if (tagStart) {
					// remember old namespace
					// unless we're self-closing
					if (!tagEnd) {
						nsMatrixStack.push(_nsMatrix);
					}

					if (cachedAttrs === null) {
						// quick check, whether there may be namespace
						// declarations on the node; if that is the case
						// we need to eagerly parse the node attributes
						if (maybeNS = x.indexOf('xmlns', q) !== -1) {
							attrsStart = q;
							attrsString = x;

							getAttrs();

							maybeNS = false;
						}
					}
				}

				_elementName = elementName;

				w = elementName.indexOf(':');
				if (w !== -1) {
					xmlns = nsMatrix[elementName.substring(0, w)];

					// prefix given; namespace must exist
					if (!xmlns) {
						return handleError('missing namespace on <' + _elementName + '>');
					}

					elementName = elementName.substr(w + 1);
				} else {
					xmlns = nsMatrix['xmlns'];

					// if no default namespace is defined,
					// we'll import the element as anonymous.
					//
					// it is up to users to correct that to the document defined
					// targetNamespace, or whatever their undersanding of the
					// XML spec mandates.
				}

				// adjust namespace prefixs as configured
				if (xmlns) {
					elementName = xmlns + ':' + elementName;
				}
			}

			if (tagStart) {
				attrsStart = q;
				attrsString = x;

				if (onOpenTag) {
					if (proxy) {
						onOpenTag(elementProxy, decodeEntities, tagEnd, getContext);
					} else {
						onOpenTag(elementName, getAttrs, decodeEntities, tagEnd, getContext);
					}

					if (parseStop) {
						return;
					}
				}
			}

			if (tagEnd) {

				if (onCloseTag) {
					onCloseTag(proxy ? elementProxy : elementName, decodeEntities, tagStart, getContext);

					if (parseStop) {
						return;
					}
				}

				// restore old namespace
				if (isNamespace) {
					if (!tagStart) {
						nsMatrix = nsMatrixStack.pop();
					} else {
						nsMatrix = _nsMatrix;
					}
				}
			}

			j += 1;
		}
	} /** end parse */
}

exports.Parser = Parser;
exports.decode = decodeEntities;

},{}],78:[function(_dereq_,module,exports){
(function (global){
"use strict";

var _createClass = function () {
	function defineProperties(target, props) {
		for (var i = 0; i < props.length; i++) {
			var descriptor = props[i];descriptor.enumerable = descriptor.enumerable || false;descriptor.configurable = true;if ("value" in descriptor) descriptor.writable = true;Object.defineProperty(target, descriptor.key, descriptor);
		}
	}return function (Constructor, protoProps, staticProps) {
		if (protoProps) defineProperties(Constructor.prototype, protoProps);if (staticProps) defineProperties(Constructor, staticProps);return Constructor;
	};
}();

function _classCallCheck(instance, Constructor) {
	if (!(instance instanceof Constructor)) {
		throw new TypeError("Cannot call a class as a function");
	}
}

/**
 * Tiny stack for browser or server
 *
 * @author Jason Mulligan <jason.mulligan@avoidwork.com>
 * @copyright 2018
 * @license BSD-3-Clause
 * @link http://avoidwork.github.io/tiny-stack
 * @version 1.1.0
 */
(function (global) {
	"use strict";

	var TinyStack = function () {
		function TinyStack() {
			_classCallCheck(this, TinyStack);

			for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
				args[_key] = arguments[_key];
			}

			this.data = [null].concat(args);
			this.top = this.data.length - 1;
		}

		_createClass(TinyStack, [{
			key: "clear",
			value: function clear() {
				this.data.length = 1;
				this.top = 0;

				return this;
			}
		}, {
			key: "empty",
			value: function empty() {
				return this.top === 0;
			}
		}, {
			key: "length",
			value: function length() {
				return this.top;
			}
		}, {
			key: "peek",
			value: function peek() {
				return this.data[this.top];
			}
		}, {
			key: "pop",
			value: function pop() {
				var result = void 0;

				if (this.top > 0) {
					result = this.data.pop();
					this.top--;
				}

				return result;
			}
		}, {
			key: "push",
			value: function push(arg) {
				this.data[++this.top] = arg;

				return this;
			}
		}, {
			key: "search",
			value: function search(arg) {
				var index = this.data.indexOf(arg);

				return index === -1 ? -1 : this.data.length - index;
			}
		}]);

		return TinyStack;
	}();

	function factory() {
		for (var _len2 = arguments.length, args = Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
			args[_key2] = arguments[_key2];
		}

		return new (Function.prototype.bind.apply(TinyStack, [null].concat(args)))();
	}

	// Node, AMD & window supported
	if (typeof exports !== "undefined") {
		module.exports = factory;
	} else if (typeof define === "function" && define.amd !== void 0) {
		define(function () {
			return factory;
		});
	} else {
		global.stack = factory;
	}
})(typeof window !== "undefined" ? window : global);

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],79:[function(_dereq_,module,exports){
'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

function ensureImported(element, target) {

	if (element.ownerDocument !== target.ownerDocument) {
		try {
			// may fail on webkit
			return target.ownerDocument.importNode(element, true);
		} catch (e) {
			// ignore
		}
	}

	return element;
}

/**
 * appendTo utility
 */

/**
 * Append a node to a target element and return the appended node.
 *
 * @param  {SVGElement} element
 * @param  {SVGElement} target
 *
 * @return {SVGElement} the appended node
 */
function appendTo(element, target) {
	return target.appendChild(ensureImported(element, target));
}

/**
 * append utility
 */

/**
 * Append a node to an element
 *
 * @param  {SVGElement} element
 * @param  {SVGElement} node
 *
 * @return {SVGElement} the element
 */
function append(target, node) {
	appendTo(node, target);
	return target;
}

/**
 * attribute accessor utility
 */

var LENGTH_ATTR = 2;

var CSS_PROPERTIES = {
	'alignment-baseline': 1,
	'baseline-shift': 1,
	'clip': 1,
	'clip-path': 1,
	'clip-rule': 1,
	'color': 1,
	'color-interpolation': 1,
	'color-interpolation-filters': 1,
	'color-profile': 1,
	'color-rendering': 1,
	'cursor': 1,
	'direction': 1,
	'display': 1,
	'dominant-baseline': 1,
	'enable-background': 1,
	'fill': 1,
	'fill-opacity': 1,
	'fill-rule': 1,
	'filter': 1,
	'flood-color': 1,
	'flood-opacity': 1,
	'font': 1,
	'font-family': 1,
	'font-size': LENGTH_ATTR,
	'font-size-adjust': 1,
	'font-stretch': 1,
	'font-style': 1,
	'font-variant': 1,
	'font-weight': 1,
	'glyph-orientation-horizontal': 1,
	'glyph-orientation-vertical': 1,
	'image-rendering': 1,
	'kerning': 1,
	'letter-spacing': 1,
	'lighting-color': 1,
	'marker': 1,
	'marker-end': 1,
	'marker-mid': 1,
	'marker-start': 1,
	'mask': 1,
	'opacity': 1,
	'overflow': 1,
	'pointer-events': 1,
	'shape-rendering': 1,
	'stop-color': 1,
	'stop-opacity': 1,
	'stroke': 1,
	'stroke-dasharray': 1,
	'stroke-dashoffset': 1,
	'stroke-linecap': 1,
	'stroke-linejoin': 1,
	'stroke-miterlimit': 1,
	'stroke-opacity': 1,
	'stroke-width': LENGTH_ATTR,
	'text-anchor': 1,
	'text-decoration': 1,
	'text-rendering': 1,
	'unicode-bidi': 1,
	'visibility': 1,
	'word-spacing': 1,
	'writing-mode': 1
};

function getAttribute(node, name) {
	if (CSS_PROPERTIES[name]) {
		return node.style[name];
	} else {
		return node.getAttributeNS(null, name);
	}
}

function setAttribute(node, name, value) {
	var hyphenated = name.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();

	var type = CSS_PROPERTIES[hyphenated];

	if (type) {
		// append pixel unit, unless present
		if (type === LENGTH_ATTR && typeof value === 'number') {
			value = String(value) + 'px';
		}

		node.style[hyphenated] = value;
	} else {
		node.setAttributeNS(null, name, value);
	}
}

function setAttributes(node, attrs) {

	var names = Object.keys(attrs),
			i,
			name;

	for (i = 0, name; name = names[i]; i++) {
		setAttribute(node, name, attrs[name]);
	}
}

/**
 * Gets or sets raw attributes on a node.
 *
 * @param  {SVGElement} node
 * @param  {Object} [attrs]
 * @param  {String} [name]
 * @param  {String} [value]
 *
 * @return {String}
 */
function attr(node, name, value) {
	if (typeof name === 'string') {
		if (value !== undefined) {
			setAttribute(node, name, value);
		} else {
			return getAttribute(node, name);
		}
	} else {
		setAttributes(node, name);
	}

	return node;
}

/**
 * Clear utility
 */
function index(arr, obj) {
	if (arr.indexOf) {
		return arr.indexOf(obj);
	}

	for (var i = 0; i < arr.length; ++i) {
		if (arr[i] === obj) {
			return i;
		}
	}

	return -1;
}

var re = /\s+/;

var toString = Object.prototype.toString;

function defined(o) {
	return typeof o !== 'undefined';
}

/**
 * Wrap `el` in a `ClassList`.
 *
 * @param {Element} el
 * @return {ClassList}
 * @api public
 */

function classes(el) {
	return new ClassList(el);
}

function ClassList(el) {
	if (!el || !el.nodeType) {
		throw new Error('A DOM element reference is required');
	}
	this.el = el;
	this.list = el.classList;
}

/**
 * Add class `name` if not already present.
 *
 * @param {String} name
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.add = function (name) {

	// classList
	if (this.list) {
		this.list.add(name);
		return this;
	}

	// fallback
	var arr = this.array();
	var i = index(arr, name);
	if (!~i) {
		arr.push(name);
	}

	if (defined(this.el.className.baseVal)) {
		this.el.className.baseVal = arr.join(' ');
	} else {
		this.el.className = arr.join(' ');
	}

	return this;
};

/**
 * Remove class `name` when present, or
 * pass a regular expression to remove
 * any which match.
 *
 * @param {String|RegExp} name
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.remove = function (name) {
	if ('[object RegExp]' === toString.call(name)) {
		return this.removeMatching(name);
	}

	// classList
	if (this.list) {
		this.list.remove(name);
		return this;
	}

	// fallback
	var arr = this.array();
	var i = index(arr, name);
	if (~i) {
		arr.splice(i, 1);
	}
	this.el.className.baseVal = arr.join(' ');
	return this;
};

/**
 * Remove all classes matching `re`.
 *
 * @param {RegExp} re
 * @return {ClassList}
 * @api private
 */

ClassList.prototype.removeMatching = function (re) {
	var arr = this.array();
	for (var i = 0; i < arr.length; i++) {
		if (re.test(arr[i])) {
			this.remove(arr[i]);
		}
	}
	return this;
};

/**
 * Toggle class `name`, can force state via `force`.
 *
 * For browsers that support classList, but do not support `force` yet,
 * the mistake will be detected and corrected.
 *
 * @param {String} name
 * @param {Boolean} force
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.toggle = function (name, force) {
	// classList
	if (this.list) {
		if (defined(force)) {
			if (force !== this.list.toggle(name, force)) {
				this.list.toggle(name); // toggle again to correct
			}
		} else {
			this.list.toggle(name);
		}
		return this;
	}

	// fallback
	if (defined(force)) {
		if (!force) {
			this.remove(name);
		} else {
			this.add(name);
		}
	} else {
		if (this.has(name)) {
			this.remove(name);
		} else {
			this.add(name);
		}
	}

	return this;
};

/**
 * Return an array of classes.
 *
 * @return {Array}
 * @api public
 */

ClassList.prototype.array = function () {
	var className = this.el.getAttribute('class') || '';
	var str = className.replace(/^\s+|\s+$/g, '');
	var arr = str.split(re);
	if ('' === arr[0]) {
		arr.shift();
	}
	return arr;
};

/**
 * Check if class `name` is present.
 *
 * @param {String} name
 * @return {ClassList}
 * @api public
 */

ClassList.prototype.has = ClassList.prototype.contains = function (name) {
	return this.list ? this.list.contains(name) : !!~index(this.array(), name);
};

function remove(element) {
	var parent = element.parentNode;

	if (parent) {
		parent.removeChild(element);
	}

	return element;
}

/**
 * Clear utility
 */

/**
 * Removes all children from the given element
 *
 * @param  {DOMElement} element
 * @return {DOMElement} the element (for chaining)
 */
function clear(element) {
	var child;

	while (child = element.firstChild) {
		remove(child);
	}

	return element;
}

function clone(element) {
	return element.cloneNode(true);
}

var ns = {
	svg: 'http://www.w3.org/2000/svg'
};

/**
 * DOM parsing utility
 */

var SVG_START = '<svg xmlns="' + ns.svg + '"';

function parse(svg) {

	var unwrap = false;

	// ensure we import a valid svg document
	if (svg.substring(0, 4) === '<svg') {
		if (svg.indexOf(ns.svg) === -1) {
			svg = SVG_START + svg.substring(4);
		}
	} else {
		// namespace svg
		svg = SVG_START + '>' + svg + '</svg>';
		unwrap = true;
	}

	var parsed = parseDocument(svg);

	if (!unwrap) {
		return parsed;
	}

	var fragment = document.createDocumentFragment();

	var parent = parsed.firstChild;

	while (parent.firstChild) {
		fragment.appendChild(parent.firstChild);
	}

	return fragment;
}

function parseDocument(svg) {

	var parser;

	// parse
	parser = new DOMParser();
	parser.async = false;

	return parser.parseFromString(svg, 'text/xml');
}

/**
 * Create utility for SVG elements
 */

/**
 * Create a specific type from name or SVG markup.
 *
 * @param {String} name the name or markup of the element
 * @param {Object} [attrs] attributes to set on the element
 *
 * @returns {SVGElement}
 */
function create(name, attrs) {
	var element;

	if (name.charAt(0) === '<') {
		element = parse(name).firstChild;
		element = document.importNode(element, true);
	} else {
		element = document.createElementNS(ns.svg, name);
	}

	if (attrs) {
		attr(element, attrs);
	}

	return element;
}

/**
 * Events handling utility
 */

function on(node, event, listener, useCapture) {
	node.addEventListener(event, listener, useCapture);
}

function off(node, event, listener, useCapture) {
	node.removeEventListener(event, listener, useCapture);
}

/**
 * Geometry helpers
 */

// fake node used to instantiate svg geometry elements
var node = create('svg');

function extend(object, props) {
	var i,
			k,
			keys = Object.keys(props);

	for (i = 0; k = keys[i]; i++) {
		object[k] = props[k];
	}

	return object;
}

function createPoint(x, y) {
	var point = node.createSVGPoint();

	switch (arguments.length) {
		case 0:
			return point;
		case 2:
			x = {
				x: x,
				y: y
			};
			break;
	}

	return extend(point, x);
}

function createMatrix(a, b, c, d, e, f) {
	var matrix = node.createSVGMatrix();

	switch (arguments.length) {
		case 0:
			return matrix;
		case 6:
			a = {
				a: a,
				b: b,
				c: c,
				d: d,
				e: e,
				f: f
			};
			break;
	}

	return extend(matrix, a);
}

function createTransform(matrix) {
	if (matrix) {
		return node.createSVGTransformFromMatrix(matrix);
	} else {
		return node.createSVGTransform();
	}
}

/**
 * Serialization util
 */

var TEXT_ENTITIES = /([&<>]{1})/g;
var ATTR_ENTITIES = /([\n\r"]{1})/g;

var ENTITY_REPLACEMENT = {
	'&': '&amp;',
	'<': '&lt;',
	'>': '&gt;',
	'"': '\''
};

function escape(str, pattern) {

	function replaceFn(match, entity) {
		return ENTITY_REPLACEMENT[entity] || entity;
	}

	return str.replace(pattern, replaceFn);
}

function serialize(node, output) {

	var i, len, attrMap, attrNode, childNodes;

	switch (node.nodeType) {
		// TEXT
		case 3:
			// replace special XML characters
			output.push(escape(node.textContent, TEXT_ENTITIES));
			break;

		// ELEMENT
		case 1:
			output.push('<', node.tagName);

			if (node.hasAttributes()) {
				attrMap = node.attributes;
				for (i = 0, len = attrMap.length; i < len; ++i) {
					attrNode = attrMap.item(i);
					output.push(' ', attrNode.name, '="', escape(attrNode.value, ATTR_ENTITIES), '"');
				}
			}

			if (node.hasChildNodes()) {
				output.push('>');
				childNodes = node.childNodes;
				for (i = 0, len = childNodes.length; i < len; ++i) {
					serialize(childNodes.item(i), output);
				}
				output.push('</', node.tagName, '>');
			} else {
				output.push('/>');
			}
			break;

		// COMMENT
		case 8:
			output.push('<!--', escape(node.nodeValue, TEXT_ENTITIES), '-->');
			break;

		// CDATA
		case 4:
			output.push('<![CDATA[', node.nodeValue, ']]>');
			break;

		default:
			throw new Error('unable to handle node ' + node.nodeType);
	}

	return output;
}

/**
 * innerHTML like functionality for SVG elements.
 * based on innerSVG (https://code.google.com/p/innersvg)
 */

function set(element, svg) {

	var parsed = parse(svg);

	// clear element contents
	clear(element);

	if (!svg) {
		return;
	}

	if (!isFragment(parsed)) {
		// extract <svg> from parsed document
		parsed = parsed.documentElement;
	}

	var nodes = slice(parsed.childNodes);

	// import + append each node
	for (var i = 0; i < nodes.length; i++) {
		appendTo(nodes[i], element);
	}
}

function get(element) {
	var child = element.firstChild,
			output = [];

	while (child) {
		serialize(child, output);
		child = child.nextSibling;
	}

	return output.join('');
}

function isFragment(node) {
	return node.nodeName === '#document-fragment';
}

function innerSVG(element, svg) {

	if (svg !== undefined) {

		try {
			set(element, svg);
		} catch (e) {
			throw new Error('error parsing SVG: ' + e.message);
		}

		return element;
	} else {
		return get(element);
	}
}

function slice(arr) {
	return Array.prototype.slice.call(arr);
}

/**
 * Selection utilities
 */

function select(node, selector) {
	return node.querySelector(selector);
}

function selectAll(node, selector) {
	var nodes = node.querySelectorAll(selector);

	return [].map.call(nodes, function (element) {
		return element;
	});
}

/**
 * prependTo utility
 */

/**
 * Prepend a node to a target element and return the prepended node.
 *
 * @param  {SVGElement} node
 * @param  {SVGElement} target
 *
 * @return {SVGElement} the prepended node
 */
function prependTo(node, target) {
	return target.insertBefore(ensureImported(node, target), target.firstChild || null);
}

/**
 * prepend utility
 */

/**
 * Prepend a node to a target element
 *
 * @param  {SVGElement} target
 * @param  {SVGElement} node
 *
 * @return {SVGElement} the target element
 */
function prepend(target, node) {
	prependTo(node, target);
	return target;
}

/**
 * Replace utility
 */

function replace(element, replacement) {
	element.parentNode.replaceChild(ensureImported(replacement, element), element);
	return replacement;
}

/**
 * transform accessor utility
 */

function wrapMatrix(transformList, transform) {
	if (transform instanceof SVGMatrix) {
		return transformList.createSVGTransformFromMatrix(transform);
	} else {
		return transform;
	}
}

function setTransforms(transformList, transforms) {
	var i, t;

	transformList.clear();

	for (i = 0; t = transforms[i]; i++) {
		transformList.appendItem(wrapMatrix(transformList, t));
	}

	transformList.consolidate();
}

function transform(node, transforms) {
	var transformList = node.transform.baseVal;

	if (arguments.length === 1) {
		return transformList.consolidate();
	} else {
		if (transforms.length) {
			setTransforms(transformList, transforms);
		} else {
			transformList.initialize(wrapMatrix(transformList, transforms));
		}
	}
}

exports.append = append;
exports.appendTo = appendTo;
exports.attr = attr;
exports.classes = classes;
exports.clear = clear;
exports.clone = clone;
exports.create = create;
exports.innerSVG = innerSVG;
exports.prepend = prepend;
exports.prependTo = prependTo;
exports.remove = remove;
exports.replace = replace;
exports.transform = transform;
exports.on = on;
exports.off = off;
exports.createPoint = createPoint;
exports.createMatrix = createMatrix;
exports.createTransform = createTransform;
exports.select = select;
exports.selectAll = selectAll;

},{}]},{},[1])(1)
});

// #END
