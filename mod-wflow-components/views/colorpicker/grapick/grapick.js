/*! grapick - 0.1.16 */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["Grapick"] = factory();
	else
		root["Grapick"] = factory();
})(typeof self !== 'undefined' ? self : this, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.on = on;
exports.off = off;
function on(el, ev, fn) {
  ev = ev.split(/\s+/);

  for (var i = 0; i < ev.length; ++i) {
    el.addEventListener(ev[i], fn);
  }
}

function off(el, ev, fn) {
  ev = ev.split(/\s+/);

  for (var i = 0; i < ev.length; ++i) {
    el.removeEventListener(ev[i], fn);
  }
}

var isFunction = exports.isFunction = function isFunction(fn) {
  return typeof fn === 'function';
};

var isDef = exports.isDef = function isDef(val) {
  return typeof val !== 'undefined';
};

var getPointerEvent = exports.getPointerEvent = function getPointerEvent(ev) {
  return ev.touches && ev.touches[0] || ev;
};

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _Grapick = __webpack_require__(2);

var _Grapick2 = _interopRequireDefault(_Grapick);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = function (o) {
  return new _Grapick2.default(o);
};

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _emitter = __webpack_require__(3);

var _emitter2 = _interopRequireDefault(_emitter);

var _Handler = __webpack_require__(4);

var _Handler2 = _interopRequireDefault(_Handler);

var _utils = __webpack_require__(0);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var comparator = function comparator(l, r) {
  return l.position - r.position;
};

var typeName = function typeName(name) {
  return name + '-gradient(';
};

/**
 * Main Grapick class
 * @extends EventEmitter
 */

var Grapick = function (_EventEmitter) {
  _inherits(Grapick, _EventEmitter);

  function Grapick() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _classCallCheck(this, Grapick);

    var _this = _possibleConstructorReturn(this, (Grapick.__proto__ || Object.getPrototypeOf(Grapick)).call(this));

    var pfx = 'grp';
    options = Object.assign({}, options);
    var defaults = {
      // Class prefix
      pfx: pfx,

      // HTMLElement/query string on which the gradient input will be attached
      el: '.' + pfx,

      // Element to use for the custom color picker, eg. '<div class="my-custom-el"></div>'
      // Will be added inside the color picker container
      colorEl: '',

      // Minimum handler position, default: 0
      min: 0,

      // Maximum handler position, default: 100
      max: 100,

      // Any supported gradient direction: '90deg' (default), 'top', 'bottom', 'right', '135deg', etc.
      direction: '90deg',

      // Gradient type, available options: 'linear' (default) | 'radial' | 'repeating-linear' | 'repeating-radial'
      type: 'linear',

      // Gradient input height, default: '30px'
      height: '30px',

      // Gradient input width, default: '100%'
      width: '100%',

      // Default empty color (when you click on an empty color picker)
      emptyColor: '#000',

      // Format handler position value, default (to avoid floats): val => parseInt(val)
      onValuePos: function onValuePos(val) {
        return parseInt(val);
      }
    };

    for (var name in defaults) {
      if (!(name in options)) options[name] = defaults[name];
    }

    var el = options.el;
    el = typeof el == 'string' ? document.querySelector(el) : el;

    if (!(el instanceof HTMLElement)) {
      throw 'Element not found, given ' + el;
    }

    _this.el = el;
    _this.handlers = [];
    _this.options = options;
    _this.on('handler:color:change', function (h, c) {
      return _this.change(c);
    });
    _this.on('handler:position:change', function (h, c) {
      return _this.change(c);
    });
    _this.on('handler:remove', function (h) {
      return _this.change(1);
    });
    _this.on('handler:add', function (h) {
      return _this.change(1);
    });
    _this.render();
    return _this;
  }

  /**
   * Destroy Grapick
   */


  _createClass(Grapick, [{
    key: 'destroy',
    value: function destroy() {
      var _this2 = this;

      this.clear();
      this.e = {}; // Clear all events;
      [// Clear the state
      'el', 'handlers', 'options', 'colorPicker'].forEach(function (i) {
        return _this2[i] = 0;
      });
      [// Remove DOM elements
      'previewEl', 'wrapperEl', 'sandEl'].forEach(function (key) {
        var el = _this2[key];
        el && el.parentNode && el.parentNode.removeChild(el);
        delete _this2[key];
      });
    }

    /**
     * Set custom color picker
     * @param {Object} cp Color picker interface
     * @example
     * const gp = new Grapick({
     *  el: '#gp',
     *  colorEl: '<input id="colorpicker"/>'
     * });
     * gp.setColorPicker(handler => {
     *    const colorEl = handler.getEl().querySelector('#colorpicker');
     *
     *    // Or you might face something like this
     *    colorPicker({
     *      el: colorEl,
     *      startColoer: handler.getColor(),
     *      change(color) {
     *        handler.setColor(color);
     *      }
     *    });
     *
     *    // jQuery style color picker
     *    $(colorEl).colorPicker2({...}).on('change', () => {
     *      handler.setColor(this.value);
     *    })
     *
     *    // In order to avoid memory leaks, return a function and call there
     *    // the destroy method of you color picker instance
     *    return () => {
     *      // destroy your color picker instance
     *    }
     * })
     */

  }, {
    key: 'setColorPicker',
    value: function setColorPicker(cp) {
      this.colorPicker = cp;
    }

    /**
     * Get the complete style value
     * @return {string}
     * @example
     * const ga = new Grapick({...});
     * ga.addHandler(0, '#000');
     * ga.addHandler(55, 'white');
     * console.log(ga.getValue());
     * // -> `linear-gradient(left, #000 0%, white 55%)`
     */

  }, {
    key: 'getValue',
    value: function getValue(type, angle) {
      var color = this.getColorValue();
      var tp = type || this.getType();
      var defDir = ['top', 'left', 'bottom', 'right', 'center'];
      var ang = angle || this.getDirection();

      if (['linear', 'repeating-linear'].indexOf(tp) >= 0 && defDir.indexOf(ang) >= 0) {
        ang = ang === 'center' ? 'to right' : 'to ' + ang;
      }

      if (['radial', 'repeating-radial'].indexOf(tp) >= 0 && defDir.indexOf(ang) >= 0) {
        ang = 'circle at ' + ang;
      }

      return color ? tp + '-gradient(' + ang + ', ' + color + ')' : '';
    }

    /**
     * Get the gradient value with the browser prefix if necessary.
     * The usage of this method is deprecated (noticed weird behaviors in modern browsers).
     * @return {string}
     * @deprecated
     */

  }, {
    key: 'getSafeValue',
    value: function getSafeValue(type, angle) {
      var previewEl = this.previewEl;
      var value = this.getValue(type, angle);
      !this.sandEl && (this.sandEl = document.createElement('div'));

      if (!previewEl || !value) {
        return '';
      }

      var style = this.sandEl.style;
      var values = [value].concat(_toConsumableArray(this.getPrefixedValues(type, angle)));
      var val = void 0;

      for (var i = 0; i < values.length; i++) {
        val = values[i];
        style.backgroundImage = val;

        if (style.backgroundImage == val) {
          break;
        }
      }

      return style.backgroundImage;
    }

    /**
     * Parse and apply the value to the picker
     * @param {string} value Any valid gradient string
     * @param {Object} [options={}] Options
     * @param {Boolean} [options.silent] Don't trigger events
     * @example
     * ga.setValue('linear-gradient(90deg, rgba(18, 215, 151, 0.75) 31.25%, white 85.1562%)');
     * ga.setValue('-webkit-radial-gradient(left, red 10%, blue 85%)');
     */

  }, {
    key: 'setValue',
    value: function setValue() {
      var _this3 = this;

      var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      var type = this.type;
      var direction = this.direction;
      var start = value.indexOf('(') + 1;
      var end = value.lastIndexOf(')');
      var gradients = value.substring(start, end);
      var values = gradients.split(/,(?![^(]*\)) /);
      this.clear(options);

      if (!gradients) {
        this.updatePreview();
        return;
      }

      if (values.length > 2) {
        direction = values.shift();
      }

      var typeFound = void 0;
      var types = ['repeating-linear', 'repeating-radial', 'linear', 'radial'];
      types.forEach(function (name) {
        if (value.indexOf(typeName(name)) > -1 && !typeFound) {
          typeFound = 1;
          type = name;
        }
      });

      this.setDirection(direction, options);
      this.setType(type, options);
      values.forEach(function (value) {
        var hdlValues = value.split(' ');
        var position = parseFloat(hdlValues.pop());
        var color = hdlValues.join('');
        _this3.addHandler(position, color, 0, options);
      });
      this.updatePreview();
    }

    /**
     * Get only colors value
     * @return {string}
     * @example
     * const ga = new Grapick({...});
     * ga.addHandler(0, '#000');
     * ga.addHandler(55, 'white');
     * console.log(ga.getColorValue());
     * // -> `#000 0%, white 55%`
     */

  }, {
    key: 'getColorValue',
    value: function getColorValue() {
      var handlers = this.handlers;
      handlers.sort(comparator);
      handlers = handlers.length == 1 ? [handlers[0], handlers[0]] : handlers;
      return handlers.map(function (handler) {
        return handler.getValue();
      }).join(', ');
    }

    /**
     * Get an array with browser specific values
     * @return {Array}
     * @example
     * const ga = new Grapick({...});
     * ga.addHandler(0, '#000');
     * ga.addHandler(55, 'white');
     * console.log(ga.getPrefixedValues());
     * // -> [
     *  "-moz-linear-gradient(left, #000 0%, white 55%)",
     *  "-webkit-linear-gradient(left, #000 0%, white 55%)"
     *  "-o-linear-gradient(left, #000 0%, white 55%)"
     * ]
     */

  }, {
    key: 'getPrefixedValues',
    value: function getPrefixedValues(type, angle) {
      var value = this.getValue(type, angle);
      return ['-moz-', '-webkit-', '-o-', '-ms-'].map(function (prefix) {
        return '' + prefix + value;
      });
    }

    /**
     * Trigger change
     * @param {Boolean} complete Indicates if the change is complete (eg. while dragging is not complete)
     * @param {Object} [options={}] Options
     * @param {Boolean} [options.silent] Don't trigger events
     */

  }, {
    key: 'change',
    value: function change() {
      var complete = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      this.updatePreview();
      !options.silent && this.emit('change', complete);
      // TODO can't make it work with jsdom
      //timerChange && clearTimeout(timerChange);
      //timerChange = setTimeout(() => this.emit('change', complete), 0);
    }

    /**
     * Set gradient direction, eg. 'top', 'left', 'bottom', 'right', '90deg', etc.
     * @param {string} direction Any supported direction
     * @param {Object} [options={}] Options
     * @param {Boolean} [options.silent] Don't trigger events
     */

  }, {
    key: 'setDirection',
    value: function setDirection(direction) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      this.options.direction = direction;
      var _options$complete = options.complete,
          complete = _options$complete === undefined ? 1 : _options$complete;

      this.change(complete, options);
    }

    /**
     * Set gradient direction, eg. 'top', 'left', 'bottom', 'right', '90deg', etc.
     * @param {string} direction Any supported direction
     */

  }, {
    key: 'getDirection',
    value: function getDirection() {
      return this.options.direction;
    }

    /**
     * Set gradient type, available options: 'linear' or 'radial'
     * @param {string} direction Any supported direction
     * @param {Object} [options={}] Options
     * @param {Boolean} [options.silent] Don't trigger events
     */

  }, {
    key: 'setType',
    value: function setType(type) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      this.options.type = type;
      var _options$complete2 = options.complete,
          complete = _options$complete2 === undefined ? 1 : _options$complete2;

      this.change(complete, options);
    }

    /**
     * Get gradient type
     * @return {string}
     */

  }, {
    key: 'getType',
    value: function getType() {
      return this.options.type;
    }

    /**
     * Add gradient handler
     * @param {integer} position Position integer in percentage
     * @param {string} color Color string, eg. red, #123, 'rgba(30,87,153,1)', etc..
     * @param {Boolean} select Select the handler once it's added
     * @param {Object} [options={}] Handler options
     * @param {Boolean} [options.silent] Don't trigger events
     * @return {Object} Handler object
     */

  }, {
    key: 'addHandler',
    value: function addHandler(position, color) {
      var select = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
      var options = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};

      var handler = new _Handler2.default(this, position, color, select, options);
      !options.silent && this.emit('handler:add', handler);
      return handler;
    }

    /**
     * Get handler by index
     * @param  {integer} index
     * @return {Object}
     */

  }, {
    key: 'getHandler',
    value: function getHandler(index) {
      return this.handlers[index];
    }

    /**
     * Get all handlers
     * @return {Array}
     */

  }, {
    key: 'getHandlers',
    value: function getHandlers() {
      return this.handlers;
    }

    /**
     * Remove all handlers
     * @param {Object} [options={}] Options
     * @param {Boolean} [options.silent] Don't trigger events
     * @example
     * ga.clear();
     * // Don't trigger events
     * ga.clear({silent: 1});
     */

  }, {
    key: 'clear',
    value: function clear() {
      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

      var handlers = this.handlers;

      for (var i = handlers.length - 1; i >= 0; i--) {
        handlers[i].remove(options);
      }
    }

    /**
     * Return selected handler if one exists
     * @return {Handler|null}
     */

  }, {
    key: 'getSelected',
    value: function getSelected() {
      var handlers = this.getHandlers();

      for (var i = 0; i < handlers.length; i++) {
        var handler = handlers[i];

        if (handler.isSelected()) {
          return handler;
        }
      }

      return null;
    }

    /**
     * Update preview element
     */

  }, {
    key: 'updatePreview',
    value: function updatePreview() {
      var previewEl = this.previewEl;
      previewEl && (previewEl.style.backgroundImage = this.getValue('linear', 'to right'));
    }
  }, {
    key: 'initEvents',
    value: function initEvents() {
      var _this4 = this;

      var pEl = this.previewEl;
      pEl && (0, _utils.on)(pEl, 'click', function (e) {
        // First of all, find a position of the click in percentage
        var opt = _this4.options;
        var min = opt.min,
            max = opt.max;

        var elDim = {
          w: pEl.clientWidth,
          h: pEl.clientHeight
        };
        var x = e.offsetX - pEl.clientLeft;
        var y = e.offsetY - pEl.clientTop;
        var percentage = x / elDim.w * 100;

        if (percentage > max || percentage < min || y > elDim.h || y < 0) {
          return;
        }

        // Now let's find the pixel color by using the canvas
        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d');
        canvas.width = elDim.w;
        canvas.height = elDim.h;
        var grad = context.createLinearGradient(0, 0, elDim.w, elDim.h);
        _this4.getHandlers().forEach(function (h) {
          return grad.addColorStop(h.position / 100, h.color);
        });
        context.fillStyle = grad;
        context.fillRect(0, 0, canvas.width, canvas.height);
        canvas.style.background = 'black';
        var rgba = canvas.getContext('2d').getImageData(x, y, 1, 1).data;
        var color = 'rgba(' + rgba[0] + ', ' + rgba[1] + ', ' + rgba[2] + ', ' + rgba[3] + ')';
        var fc = color == 'rgba(0, 0, 0, 0)' ? opt.emptyColor : color;
        _this4.addHandler(percentage, fc);
      });
    }

    /**
     * Render the gradient picker
     */

  }, {
    key: 'render',
    value: function render() {
      var opt = this.options;
      var el = this.el;
      var pfx = opt.pfx;
      var height = opt.height;
      var width = opt.width;

      if (!el) {
        return;
      }

      var wrapperCls = pfx + '-wrapper';
      var previewCls = pfx + '-preview';
      el.innerHTML = '\n      <div class="' + wrapperCls + '">\n        <div class="' + previewCls + '"></div>\n      </div>\n    ';
      var wrapperEl = el.querySelector('.' + wrapperCls);
      var previewEl = el.querySelector('.' + previewCls);
      var styleWrap = wrapperEl.style;
      styleWrap.position = 'relative';
      this.wrapperEl = wrapperEl;
      this.previewEl = previewEl;

      if (height) {
        styleWrap.height = height;
      }

      if (width) {
        styleWrap.width = width;
      }

      this.initEvents();
      this.updatePreview();
    }
  }]);

  return Grapick;
}(_emitter2.default);

exports.default = Grapick;

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var EventEmitter = function () {
  function EventEmitter() {
    _classCallCheck(this, EventEmitter);
  }

  _createClass(EventEmitter, [{
    key: "on",
    value: function on(name, callback, ctx) {
      var e = this.e || (this.e = {});

      (e[name] || (e[name] = [])).push({
        fn: callback,
        ctx: ctx
      });

      return this;
    }
  }, {
    key: "once",
    value: function once(name, callback, ctx) {
      var self = this;
      function listener() {
        self.off(name, listener);
        callback.apply(ctx, arguments);
      };

      listener._ = callback;
      return this.on(name, listener, ctx);
    }
  }, {
    key: "emit",
    value: function emit(name) {
      var data = [].slice.call(arguments, 1);
      var evtArr = ((this.e || (this.e = {}))[name] || []).slice();
      var i = 0;
      var len = evtArr.length;

      for (i; i < len; i++) {
        evtArr[i].fn.apply(evtArr[i].ctx, data);
      }

      return this;
    }
  }, {
    key: "off",
    value: function off(name, callback) {
      var e = this.e || (this.e = {});
      var evts = e[name];
      var liveEvents = [];

      if (evts && callback) {
        for (var i = 0, len = evts.length; i < len; i++) {
          if (evts[i].fn !== callback && evts[i].fn._ !== callback) liveEvents.push(evts[i]);
        }
      }

      // Remove event from queue to prevent memory leak
      // Suggested by https://github.com/lazd
      // Ref: https://github.com/scottcorgan/tiny-emitter/commit/c6ebfaa9bc973b33d110a84a307742b7cf94c953#commitcomment-5024910

      liveEvents.length ? e[name] = liveEvents : delete e[name];

      return this;
    }
  }]);

  return EventEmitter;
}();

exports.default = EventEmitter;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _utils = __webpack_require__(0);

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Handler is the color stop of the gradient
 */
var Handler = function () {
  function Handler(Grapick) {
    var position = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
    var color = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'black';
    var select = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 1;
    var opts = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};

    _classCallCheck(this, Handler);

    Grapick.getHandlers().push(this);
    this.gp = Grapick;
    this.position = position;
    this.color = color;
    this.selected = 0;
    this.render();
    select && this.select(opts);
  }

  _createClass(Handler, [{
    key: 'toJSON',
    value: function toJSON() {
      return {
        position: this.position,
        selected: this.selected,
        color: this.color
      };
    }

    /**
     * Set color
     * @param {string} color Color string, eg. red, #123, 'rgba(30,87,153,1)', etc..
     * @param {Boolean} complete Indicates if the action is complete
     * @example
     * handler.setColor('red');
     */

  }, {
    key: 'setColor',
    value: function setColor(color) {
      var complete = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;

      this.color = color;
      this.emit('handler:color:change', this, complete);
    }

    /**
     * Set color
     * @param {integer} position Position integer in percentage, eg. 20, 50, 100
     * @param {Boolean} complete Indicates if the action is complete
     * @example
     * handler.setPosition(55);
     */

  }, {
    key: 'setPosition',
    value: function setPosition(position) {
      var complete = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;

      var el = this.getEl();
      this.position = position;
      el && (el.style.left = position + '%');
      this.emit('handler:position:change', this, complete);
    }

    /**
     * Get color of the handler
     * @return {string} Color string
     */

  }, {
    key: 'getColor',
    value: function getColor() {
      return this.color;
    }

    /**
     * Get position of the handler
     * @return {integer} Position integer
     */

  }, {
    key: 'getPosition',
    value: function getPosition() {
      var position = this.position,
          gp = this.gp;
      var onValuePos = gp.options.onValuePos;

      return (0, _utils.isFunction)(onValuePos) ? onValuePos(position) : position;
    }

    /**
     * Indicates if the handler is the selected one
     * @return {Boolean}
     */

  }, {
    key: 'isSelected',
    value: function isSelected() {
      return !!this.selected;
    }

    /**
     * Get value of the handler
     * @return {string}
     * @example
     * handler.getValue(); // -> `black 0%`
     */

  }, {
    key: 'getValue',
    value: function getValue() {
      return this.getColor() + ' ' + this.getPosition() + '%';
    }

    /**
     * Select the current handler and deselect others
     * @param {Object} [options={}] Options
     * @param {Boolean} [options.keepSelect=false] Avoid deselecting other handlers
     */

  }, {
    key: 'select',
    value: function select() {
      var opts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

      var el = this.getEl();
      var handlers = this.gp.getHandlers();
      !opts.keepSelect && handlers.forEach(function (handler) {
        return handler.deselect();
      });
      this.selected = 1;
      var clsNameSel = this.getSelectedCls();
      el && (el.className += ' ' + clsNameSel);
      this.emit('handler:select', this);
    }

    /**
     * Deselect the current handler
     */

  }, {
    key: 'deselect',
    value: function deselect() {
      var el = this.getEl();
      this.selected = 0;
      var clsNameSel = this.getSelectedCls();
      el && (el.className = el.className.replace(clsNameSel, '').trim());
      this.emit('handler:deselect', this);
    }
  }, {
    key: 'getSelectedCls',
    value: function getSelectedCls() {
      var pfx = this.gp.options.pfx;
      return pfx + '-handler-selected';
    }

    /**
     * Remove the current handler
     * @param {Object} [options={}] Options
     * @param {Boolean} [options.silent] Don't trigger events
     * @return {Handler} Removed handler (itself)
     */

  }, {
    key: 'remove',
    value: function remove() {
      var _this = this;

      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      var cpFn = this.cpFn;

      var el = this.getEl();
      var handlers = this.gp.getHandlers();
      var removed = handlers.splice(handlers.indexOf(this), 1)[0];
      el && el.parentNode.removeChild(el);
      !options.silent && this.emit('handler:remove', removed);
      (0, _utils.isFunction)(cpFn) && cpFn(this);
      ['el', 'gp'].forEach(function (i) {
        return _this[i] = 0;
      });
      return removed;
    }

    /**
     * Get handler element
     * @return {HTMLElement}
     */

  }, {
    key: 'getEl',
    value: function getEl() {
      return this.el;
    }
  }, {
    key: 'initEvents',
    value: function initEvents() {
      var _this2 = this;

      var eventDown = 'touchstart mousedown';
      var eventMove = 'touchmove mousemove';
      var eventUp = 'touchend mouseup';
      var el = this.getEl();
      var previewEl = this.gp.previewEl;
      var options = this.gp.options;
      var min = options.min;
      var max = options.max;
      var closeEl = el.querySelector('[data-toggle=handler-close]');
      var colorContEl = el.querySelector('[data-toggle=handler-color-c]');
      var colorWrapEl = el.querySelector('[data-toggle=handler-color-wrap]');
      var colorEl = el.querySelector('[data-toggle=handler-color]');
      var dragEl = el.querySelector('[data-toggle=handler-drag]');
      var upColor = function upColor(ev) {
        var complete = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
        var value = ev.target.value;

        _this2.setColor(value, complete);
        colorWrapEl && (colorWrapEl.style.backgroundColor = value);
      };
      colorContEl && (0, _utils.on)(colorContEl, 'click', function (e) {
        return e.stopPropagation();
      });
      closeEl && (0, _utils.on)(closeEl, 'click', function (e) {
        e.stopPropagation();
        _this2.remove();
      });
      if (colorEl) {
        (0, _utils.on)(colorEl, 'change', upColor);
        (0, _utils.on)(colorEl, 'input', function (ev) {
          return upColor(ev, 0);
        });
      }

      if (dragEl) {
        var pos = 0;
        var posInit = 0;
        var dragged = 0;
        var elDim = {};
        var startPos = {};
        var deltaPos = {};
        var axis = 'x';
        var drag = function drag(e) {
          var evP = (0, _utils.getPointerEvent)(e);
          dragged = 1;
          deltaPos.x = evP.clientX - startPos.x;
          deltaPos.y = evP.clientY - startPos.y;
          pos = (axis == 'x' ? deltaPos.x : deltaPos.y) * 100;
          pos = pos / (axis == 'x' ? elDim.w : elDim.h);
          pos = posInit + pos;
          pos = pos < min ? min : pos;
          pos = pos > max ? max : pos;
          _this2.setPosition(pos, 0);
          _this2.emit('handler:drag', _this2, pos);
          // In case the mouse button was released outside of the window
          (0, _utils.isDef)(e.button) && e.which === 0 && _stopDrag(e);
        };
        var _stopDrag = function _stopDrag(e) {
          (0, _utils.off)(document, eventMove, drag);
          (0, _utils.off)(document, eventUp, _stopDrag);
          if (!dragged) {
            return;
          }
          dragged = 0;
          _this2.setPosition(pos);
          _this2.emit('handler:drag:end', _this2, pos);
        };
        var initDrag = function initDrag(e) {
          //Right or middel click
          if ((0, _utils.isDef)(e.button) && e.button !== 0) {
            return;
          }
          _this2.select();
          var evP = (0, _utils.getPointerEvent)(e);
          posInit = _this2.position;
          elDim.w = previewEl.clientWidth;
          elDim.h = previewEl.clientHeight;
          startPos.x = evP.clientX;
          startPos.y = evP.clientY;
          (0, _utils.on)(document, eventMove, drag);
          (0, _utils.on)(document, eventUp, _stopDrag);
          _this2.emit('handler:drag:start', _this2);
        };

        (0, _utils.on)(dragEl, eventDown, initDrag);
        (0, _utils.on)(dragEl, 'click', function (e) {
          return e.stopPropagation();
        });
      }
    }
  }, {
    key: 'emit',
    value: function emit() {
      var _gp;

      (_gp = this.gp).emit.apply(_gp, arguments);
    }

    /**
     * Render the handler
     * @return {HTMLElement} Rendered element
     */

  }, {
    key: 'render',
    value: function render() {
      var gp = this.gp;
      var opt = gp.options;
      var previewEl = gp.previewEl;
      var colorPicker = gp.colorPicker;
      var pfx = opt.pfx;
      var colorEl = opt.colorEl;
      var color = this.getColor();

      if (!previewEl) {
        return;
      }

      var hEl = document.createElement('div');
      var style = hEl.style;
      var baseCls = pfx + '-handler';
      hEl.className = baseCls;
      hEl.innerHTML = '\n      <div class="' + baseCls + '-close-c">\n        <div class="' + baseCls + '-close" data-toggle="handler-close">&Cross;</div>\n      </div>\n      <div class="' + baseCls + '-drag" data-toggle="handler-drag"></div>\n      <div class="' + baseCls + '-cp-c" data-toggle="handler-color-c">\n        ' + (colorEl || '\n          <div class="' + baseCls + '-cp-wrap" data-toggle="handler-color-wrap" style="background-color: ' + color + '">\n            <input type="color" data-toggle="handler-color" value="' + color + '">\n          </div>') + '\n      </div>\n    ';
      style.position = 'absolute';
      style.top = 0;
      style.left = this.position + '%';
      previewEl.appendChild(hEl);
      this.el = hEl;
      this.initEvents();
      this.cpFn = colorPicker && colorPicker(this);
      return hEl;
    }
  }]);

  return Handler;
}();

exports.default = Handler;

/***/ })
/******/ ]);
});