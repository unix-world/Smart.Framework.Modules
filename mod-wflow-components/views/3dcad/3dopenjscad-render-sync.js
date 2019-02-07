
// 3dopenjscad-render-sync.js
// 3D Js CAD - Sync Renderer (Function)
// (c) 2017-2019 unix-world.org
// License: GPLv3
// Fixes by unixman r.20190202

// based on: jscad-function.js
// == OpenJSCAD.org, Copyright (c) 2013-2016, Licensed under MIT License
// History:
//   2016/02/02: 0.4.0: GUI refactored, functionality split up into more files, mostly done by Z3 Dev
//
// Create an function for processing the JSCAD script into CSG/CAG objects
//
// fullurl  - URL to original script
// script   - FULL script with all possible support routines, etc
// callback - function to call, returning results or errors
//
// This function creates an anonymous Function, which is invoked to execute the thread.
// The function executes in the GLOBAL context, so all necessary parameters are provided.

OpenJsCad.createJscadFunction = function(fullurl, script, callback) {

	//console.log('Render Sync (Function): ' + fullurl);
	//console.log("createJscadFunction()");

	// determine the relative base path for include(<relativepath>)
	var relpath = fullurl;
	if (relpath.lastIndexOf('/') >= 0) {
		relpath = relpath.substring(0,relpath.lastIndexOf('/')+1);
	}

	var source = '// SYNC-FX\n';
	source += '  var relpath = "' + relpath + '";\n';
	source += '  var include = includeJscadSync;\n';
	source += '\n';
	source += includeJscadSync.toString() + '\n';
	source += '\n';
	source += script + '\n';
	source += '\n';
	source += "return main(params);\n";

	//console.log("SOURCE: "+source);

	var f = new Function('params', source);
	return f;

};

//==
// THESE FUNCTIONS ARE SERIALIZED FOR INCLUSION IN THE FULL SCRIPT
// TODO It might be possible to cache the serialized versions
//==

// Include the requested script via XHR Request
// (Note: This function is appended together with the JSCAD script)
function includeJscadSync(fn) {

	/* disabled by unixman, unused
	// include the requested script via MemFs if possible
	if (typeof(gMemFs) == 'object') {
		for(var fs in gMemFs) {
			if (gMemFs[fs].name == fn) {
				eval(gMemFs[fs].source);
				return;
			}
		}
	}
	*/

	/* disabled by unixman: do not use externals include
	// include the requested script via webserver access
	var xhr = new XMLHttpRequest();
	var url = relpath+fn;
	if (fn.match(/^(https:|http:)/i)) {
		url = fn;
	}
	xhr.overrideMimeType('text/plain');
	xhr.open('GET',url,false);
	xhr.onload = function() {
		if((this.readyState === 4) && (this.status === 200) && this.responseText) {
			var src = this.responseText;
			//console.log(src);
			eval(src);
		} else {
			console.error('includeJscadSync ERROR: Status: ' + xhr.status);
		}
	};
	xhr.onerror = function() {
	};
	xhr.send();
	*/

	console.warn('WARNING: EXTERNAL Include Scripts Feature IS DISABLED for security reasons (script not included): ' + fn);
	return false;

};

// #END
