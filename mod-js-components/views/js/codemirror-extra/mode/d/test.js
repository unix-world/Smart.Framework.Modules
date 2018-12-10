// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: https://codemirror.net/LICENSE

// mode/d/test.js
// codemirror: v.5.42.0

(function() {
	var mode = CodeMirror.getMode({indentUnit: 2}, "d");
	function MT(name) { test.mode(name, mode, Array.prototype.slice.call(arguments, 1)); }

	MT("nested_comments",
		 "[comment /+]","[comment comment]","[comment +/]","[variable void] [variable main](){}");

})();

// #END
