
# jQuery plugin for HammerJs - https://github.com/hammerjs/jquery.hammer.js

## A small jQuery plugin is available, and is just a small wrapper around the Hammer() class. It also extends the Manager.emit method by triggering jQuery events.

$(element).hammer(options).bind("pan", myPanHandler);

The Hammer instance is stored at $element.data("hammer").


### Basic Implementation

```js
var myElement = document.getElementById('myElement');

// create a simple instance
// by default, it only adds horizontal recognizers
var mc = new Hammer(myElement);

// listen to events...
mc.on("panleft panright tap press", function(ev) {
    myElement.textContent = ev.type +" gesture detected.";
});
```


### Basic with Vertical Pan recognizer

```js
var myElement = document.getElementById('myElement');

// create a simple instance
// by default, it only adds horizontal recognizers
var mc = new Hammer(myElement);

// let the pan gesture support all directions.
// this will block the vertical scrolling on a touch-device while on the element
mc.get('pan').set({ direction: Hammer.DIRECTION_ALL });

// listen to events...
mc.on("panleft panright panup pandown tap press", function(ev) {
    myElement.textContent = ev.type +" gesture detected.";
});
```

### Recognize With with Pinch and Rotate

```js
var myElement = document.getElementById('myElement');

var mc = new Hammer.Manager(myElement);

// create a pinch and rotate recognizer
// these require 2 pointers
var pinch = new Hammer.Pinch();
var rotate = new Hammer.Rotate();

// we want to detect both the same time
pinch.recognizeWith(rotate);

// add to the Manager
mc.add([pinch, rotate]);


mc.on("pinch rotate", function(ev) {
    myElement.textContent += ev.type +" ";
});
```

### RecognizeWith with a Quadrupletap recognizer

```js
var myElement = document.getElementById('myElement');

// We create a manager object, which is the same as Hammer(), but without the presetted recognizers. 
var mc = new Hammer.Manager(myElement);

// Default, tap recognizer
mc.add( new Hammer.Tap() );

// Tap recognizer with minimal 4 taps
mc.add( new Hammer.Tap({ event: 'quadrupletap', taps: 4 }) );

// we want to recognize this simulatenous, so a quadrupletap will be detected even while a tap has been recognized.
// the tap event will be emitted on every tap
mc.get('quadrupletap').recognizeWith('tap');


mc.on("tap quadrupletap", function(ev) {
    myElement.textContent += ev.type +" ";
});
```

### SingleTap and DoubleTap (with recognizeWith/requireFailure)

```js
var myElement = document.getElementById('myElement');

// We create a manager object, which is the same as Hammer(), but without the presetted recognizers. 
var mc = new Hammer.Manager(myElement);


// Tap recognizer with minimal 2 taps
mc.add( new Hammer.Tap({ event: 'doubletap', taps: 2 }) );
// Single tap recognizer
mc.add( new Hammer.Tap({ event: 'singletap' }) );


// we want to recognize this simulatenous, so a quadrupletap will be detected even while a tap has been recognized.
mc.get('doubletap').recognizeWith('singletap');
// we only want to trigger a tap, when we don't have detected a doubletap
mc.get('singletap').requireFailure('doubletap');


mc.on("singletap doubletap", function(ev) {
    myElement.textContent += ev.type +" ";
});
```

### Nested pan/swipe recognizers

```html
<div class="panes wrapper">
	<div class="pane bg1">
		<div class="panes">
			<div class="pane" style="background: rgba(0,0,0,0);">1.1</div>
			<div class="pane" style="background: rgba(0,0,0,.2);">1.2</div>
			<div class="pane" style="background: rgba(0,0,0,.4);">1.3</div>
			<div class="pane" style="background: rgba(0,0,0,.6);">1.4</div>
			<div class="pane" style="background: rgba(0,0,0,.8);">1.5</div>
		</div>
	</div>
	<div class="pane bg2">
		<div class="panes">
			<div class="pane" style="background: rgba(0,0,0,0);">2.1</div>
			<div class="pane" style="background: rgba(0,0,0,.2);">2.2</div>
			<div class="pane" style="background: rgba(0,0,0,.4);">2.3</div>
			<div class="pane" style="background: rgba(0,0,0,.6);">2.4</div>
			<div class="pane" style="background: rgba(0,0,0,.8);">2.5</div>
		</div>
	</div>
	<div class="pane bg3">
		<div class="panes">
			<div class="pane" style="background: rgba(0,0,0,0);">3.1</div>
			<div class="pane" style="background: rgba(0,0,0,.2);">3.2</div>
			<div class="pane" style="background: rgba(0,0,0,.4);">3.3</div>
			<div class="pane" style="background: rgba(0,0,0,.6);">3.4</div>
			<div class="pane" style="background: rgba(0,0,0,.8);">3.5</div>
		</div>
	</div>
	<div class="pane bg4">
		<div class="panes">
			<div class="pane" style="background: rgba(0,0,0,0);">4.1</div>
			<div class="pane" style="background: rgba(0,0,0,.2);">4.2</div>
			<div class="pane" style="background: rgba(0,0,0,.4);">4.3</div>
			<div class="pane" style="background: rgba(0,0,0,.6);">4.4</div>
			<div class="pane" style="background: rgba(0,0,0,.8);">4.5</div>
		</div>
	</div>
	<div class="pane bg5">
		<div class="panes">
			<div class="pane" style="background: rgba(0,0,0,0);">5.1</div>
			<div class="pane" style="background: rgba(0,0,0,.2);">5.2</div>
			<div class="pane" style="background: rgba(0,0,0,.4);">5.3</div>
			<div class="pane" style="background: rgba(0,0,0,.6);">5.4</div>
			<div class="pane" style="background: rgba(0,0,0,.8);">5.5</div>
		</div>
	</div>
</div>

<div class="container">
	<h1>Nested Pan recognizers</h1>

	<p>Nested recognizers are possible with some threshold and with use of <code>requireFailure()</code>.</p>
</div>
<script>
var reqAnimationFrame = (function() {
	return window[Hammer.prefixed(window, "requestAnimationFrame")] || function(callback) {
		setTimeout(callback, 1000 / 60);
	}
})();

function dirProp(direction, hProp, vProp) {
	return (direction & Hammer.DIRECTION_HORIZONTAL) ? hProp : vProp
}


/**
 * Carousel
 * @param container
 * @param direction
 * @constructor
 */
function HammerCarousel(container, direction) {
	this.container = container;
	this.direction = direction;

	this.panes = Array.prototype.slice.call(this.container.children, 0);
	this.containerSize = this.container[dirProp(direction, 'offsetWidth', 'offsetHeight')];

	this.currentIndex = 0;

	this.hammer = new Hammer.Manager(this.container);
	this.hammer.add(new Hammer.Pan({ direction: this.direction, threshold: 10 }));
	this.hammer.on("panstart panmove panend pancancel", Hammer.bindFn(this.onPan, this));

	this.show(this.currentIndex);
}


HammerCarousel.prototype = {
	/**
	 * show a pane
	 * @param {Number} showIndex
	 * @param {Number} [percent] percentage visible
	 * @param {Boolean} [animate]
	 */
	show: function(showIndex, percent, animate){
		showIndex = Math.max(0, Math.min(showIndex, this.panes.length - 1));
		percent = percent || 0;

		var className = this.container.className;
		if(animate) {
			if(className.indexOf('animate') === -1) {
				this.container.className += ' animate';
			}
		} else {
			if(className.indexOf('animate') !== -1) {
				this.container.className = className.replace('animate', '').trim();
			}
		}

		var paneIndex, pos, translate;
		for (paneIndex = 0; paneIndex < this.panes.length; paneIndex++) {
			pos = (this.containerSize / 100) * (((paneIndex - showIndex) * 100) + percent);
			if(this.direction & Hammer.DIRECTION_HORIZONTAL) {
				translate = 'translate3d(' + pos + 'px, 0, 0)';
			} else {
				translate = 'translate3d(0, ' + pos + 'px, 0)'
			}
			 this.panes[paneIndex].style.transform = translate;
			 this.panes[paneIndex].style.mozTransform = translate;
			 this.panes[paneIndex].style.webkitTransform = translate;
		}

		this.currentIndex = showIndex;
	},

	/**
	 * handle pan
	 * @param {Object} ev
	 */
	onPan : function (ev) {
		var delta = dirProp(this.direction, ev.deltaX, ev.deltaY);
		var percent = (100 / this.containerSize) * delta;
		var animate = false;

		if (ev.type == 'panend' || ev.type == 'pancancel') {
			if (Math.abs(percent) > 20 && ev.type == 'panend') {
				this.currentIndex += (percent < 0) ? 1 : -1;
			}
			percent = 0;
			animate = true;
		}

		this.show(this.currentIndex, percent, animate);
	}
};

// the horizontal pane scroller
var outer = new HammerCarousel(document.querySelector(".panes.wrapper"), Hammer.DIRECTION_HORIZONTAL);

// each pane should contain a vertical pane scroller
Hammer.each(document.querySelectorAll(".pane .panes"), function(container) {
	// setup the inner scroller
	var inner = new HammerCarousel(container, Hammer.DIRECTION_VERTICAL);

	// only recognize the inner pan when the outer is failing.
	// they both have a threshold of some px
	outer.hammer.get('pan').requireFailure(inner.hammer.get('pan'));
});
</script>
```


