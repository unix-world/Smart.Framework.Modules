
// NetVision JS - Contextual Advertising
// (c) 2006-2017 unix-world.org
// r.2017.04.28

//========================

var eContextDiv = 'myDiv';
var eContextWidth = '100';
var eContextHeight = '50';
var eContextReferer = 'contexual-advertising://ads.display?keywords=key1;key2;key3+key4';
var eContextURL = 'http://www.somewebsite.ext';
var eContextHideTime = 6000; 		// miliseconds before close advert on MouseOut
var eContextHideMaxTime = 60000; 	// miliseconds before close advert on MouseOver (max)


//========================

var eContextAdvertX = new function() {

// :: static

var _class = this; // self referencing

var coordinatesX = 0;
var coordinatesY = 0;
var keywordsFound = false; // initialize
var TimeOut = null; // init only (req. for timeout)


this.initRun = function() {
	//--
	Hilite.hilite();
	//--
} //END FUNCTION


this.closeOff = function(hideTime) {
	//--
	clearTimeout(TimeOut);
	TimeOut = setTimeout(function(){ eContextAdvertX.toggleOnOff(eContextDiv,0); }, hideTime);
	//--
	return false;
	//--
} //END FUNCTION


this.toggleOnOff = function(DivID, iState, eKeyWord) { // 1 visible, 0 hidden
	//--
	var obj = document.getElementById(DivID);
	var ifrm = document.getElementById('AdvrtX__IFRM');
	//var BrowsAgent = window.navigator.userAgent.toLowerCase();
	//-
	if(iState) {
		//-
		if(obj.style.visibility != "visible") { // avoid reload if already visible
			//--
			if(eContextWidth > window.innerWidth) {
				eContextWidth = window.innerWidth - 55;
			} //end if
			if(eContextHeight > window.innerHeight) {
				eContextHeight = window.innerHeight - 55;
			} //end if
			//--
			ifrm.src = '' + eContextURL + eKeyWord;
			ifrm.width = eContextWidth;
			ifrm.height = eContextHeight;
			//-
			obj.style.position = 'fixed';
			//--
			var doc = document.documentElement;
			var left = (window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0);
			var top = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);
			//--
			var theX = coordinatesX - left;
			if((theX + eContextWidth + 50) > window.innerWidth) {
				theX = theX - ((theX + eContextWidth + 50) - window.innerWidth); // fix overflow on right (with 50 px scrollbar offset)
			} //end if
			if(theX < 5) {
				theX = 5;
			} //end if
			obj.style.left = parseInt(theX) + 'px';
			//--
			var theY = coordinatesY - top;
			if((theY + eContextHeight + 50) > window.innerHeight) {
				theY = theY - ((theY + eContextHeight + 50) - window.innerHeight); // fix overflow on right (with 50 px scrollbar offset)
			} //end if
			if(theY < 5) {
				theY = 5;
			} //end if
			obj.style.top = parseInt(theY) + 'px';
			//--
		} //end if
		//-
		obj.style.zIndex = SmartJS_BrowserUtils.getHighestZIndex();
		obj.style.visibility = "visible";
		//-
	} else {
		//-
		ifrm.src = '';
		ifrm.width = eContextWidth;
		ifrm.height = eContextHeight;
		//-
		obj.style.visibility = "hidden";
		//-
	} //end if
	//--
	return false;
	//--
} //END FUNCTION

//##########

var Hilite = { // Configuration:

	// based on se-highlight 1.5

	/**
	 * Element ID to be highlighted. If set, then only content inside this DOM
	 * element will be highlighted, otherwise everything inside document.body
	 * will be searched.
	 */
	elementid: 'content',

	/**
	 * Whether we are matching an exact word. For example, searching for
	 * "highlight" will only match "highlight" but not "highlighting" if exact
	 * is set to true.
	 */
	exact: true,

	/**
	 * Maximum number of DOM nodes to test, before handing the control back to
	 * the GUI thread. This prevents locking up the UI when parsing and
	 * replacing inside a large document.
	 */
	max_nodes: 1000,

	/**
	 * Whether to automatically hilite a section of the HTML document, by
	 * binding the "Hilite.hilite()" to window.onload() event. If this
	 * attribute is set to false, you can still manually trigger the hilite by
	 * calling Hilite.hilite() in Javascript after document has been fully
	 * loaded.
	 */
	onload: true,

	/**
	 * Name of the style to be used. Default to 'hilite'.
	 */
	style_name: 'eContextHilit'

};

Hilite.search_engines = [['ads\\.display', 'keywords']]; // srv, param

/**
 * Decode the referrer string and return a list of search keywords.
 */
Hilite.decodeReferrer = function(referrer) {
	var query = null;
	var regex = new RegExp('');

	for (var i = 0; i < Hilite.search_engines.length; i ++) {
		var se = Hilite.search_engines[i];
		regex.compile('^contexual-advertising://' + se[0], 'i');
		var match = referrer.match(regex);
		if (match) {
			var result;
			if (isNaN(se[1])) {
				result = Hilite.decodeReferrerQS(referrer, se[1]);
			} else {
				result = match[se[1] + 1];
			}
			if (result) {
				result = decodeURIComponent(result);
				if (se.length > 2 && se[2]) {
					result = decodeURIComponent(result); // some URIs requires decoding twice.
				} //end if
				result = result.replace(/\'|"/g, '');
				result = result.split(/[\s,;\.]+/);
				return result;
			}
			break;
		}
	}
	//--
	return false;
	//--
};

Hilite.decodeReferrerQS = function(referrer, match) {
	var idx = referrer.indexOf('?');
	var idx2;
	if (idx >= 0) {
		var qs = new String(referrer.substring(idx + 1));
		idx  = 0;
		idx2 = 0;
		while ((idx >= 0) && ((idx2 = qs.indexOf('=', idx)) >= 0)) {
			var key, val;
			key = qs.substring(idx, idx2);
			idx = qs.indexOf('&', idx2) + 1;
			if (key == match) {
				if (idx <= 0) {
					return qs.substring(idx2+1);
				} else {
					return qs.substring(idx2+1, idx - 1);
				}
			}
		}
	}
	//--
	return false;
	//--
};


// Highlight a DOM element with a list of keywords
Hilite.hiliteElement = function(elm, query) {

	if (!query || elm.childNodes.length == 0) {
		return;
	}

	var qre = new Array();
	for (var i = 0; i < query.length; i ++) {
		query[i] = query[i].toLowerCase();
		query[i] = query[i].replace(/\+/g, ' '); // unixw
		if (Hilite.exact) {
			qre.push('\\b'+query[i]+'\\b');
		} else {
			qre.push(query[i]);
		}
	}

	qre = new RegExp(qre.join("|"), "i");

	var stylemapper = {};
	for (var i = 0; i < query.length; i ++) {
		stylemapper[query[i]] = Hilite.style_name;
	}

	var textproc = function(node) {
		var match = qre.exec(node.data);
		if (match) {
			//--
			var val = match[0];
			var k = '';
			var node2 = node.splitText(match.index);
			var node3 = node2.splitText(val.length);
			//--
			var atext = document.createTextNode(val);
			var alink = document.createElement('A');
			alink.setAttribute('id', 'adlink');
			alink.setAttribute('href', 'javascript:void(0)');
			alink.onmouseover = function() {
				coordinatesX = parseInt(this.offsetLeft);
				coordinatesY = parseInt(this.offsetTop);
				_class.toggleOnOff(eContextDiv, 1, encodeURIComponent(val));
			} //end function
			alink.appendChild(atext);
			alink.innerHTML = val;
			//--
			var span = document.createElement('SPAN');
			span.className = stylemapper[val.toLowerCase()];
			span.appendChild(alink);
			//--
			node.parentNode.replaceChild(span, node2);
			//--
			keywordsFound = true;
			//--
			return span;
		} else {
			return node;
		}
	};

	Hilite.walkElements(elm.childNodes[0], 1, textproc);

	//--
	return false;
	//--
};

/**
 * Highlight a HTML document using keywords extracted from adcontext referer.
 * This is the main function to be called to perform search engine highlight
 * on a document.
 *
 * Currently it would check for DOM element 'content', element 'container' and
 * then document.body in that order, so it only highlights appropriate section
 * on WordPress and Movable Type pages.
 */
Hilite.hilite = function() {
	var q = eContextReferer;
	var e = null;
	q = Hilite.decodeReferrer(q);
	if (q && ((Hilite.elementid && (e = document.getElementById(Hilite.elementid))) || (e = document.body))) {
	Hilite.hiliteElement(e, q);
	}
	//--
	if(keywordsFound === false) { // unixw :: toggle advertising if no keywords
		_class.toggleOnOff(eContextDiv,1);
	} //end if
	//--
	return false;
	//--
};

Hilite.walkElements = function(node, depth, textproc) {
	var skipre = /^(script|style|textarea)/i;
	var count = 0;
	while (node && depth > 0) {
		count ++;
		if (count >= Hilite.max_nodes) {
			var handler = function() {
				Hilite.walkElements(node, depth, textproc);
			};
			setTimeout(handler, 50);
			return;
		}

		if (node.nodeType == 1) { // ELEMENT_NODE
			if (!skipre.test(node.tagName) && node.childNodes.length > 0) {
				node = node.childNodes[0];
				depth ++;
				continue;
			}
		} else if (node.nodeType == 3) { // TEXT_NODE
			node = textproc(node);
		}

		if (node.nextSibling) {
			node = node.nextSibling;
		} else {
			while (depth > 0) {
				node = node.parentNode;
				depth --;
				if (node.nextSibling) {
					node = node.nextSibling;
					break;
				}
			}
		}
	}
	return false;
};

//##########

} //END CLASS
//========================

// #END
