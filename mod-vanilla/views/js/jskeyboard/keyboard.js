
// JS Virtual Keyboard
// Modified by unix-world.org
// v.2018.12.07

/* HTML Virtual Keyboard Interface Script - v1.36
 * Copyright (c) 2010 - GreyWyvern
 * Licenced for free distribution under the BSD License http://www.opensource.org/licenses/bsd-license.php
 */


var VirtualKeyboard = new function() { // START CLASS :: v.170831

var VKeyboard_Image = 'lib/js/jskeyboard/img/keyboard.png';
var VKeyboard_Class = 'keyboardInput';

var VKI_attach, VKI_close;

this.VKI_buildKeyboardInputs = function(imgIcon, className) {

	if(imgIcon) {
		VKeyboard_Image = String(imgIcon);
	} //end if

	if(className) {
		VKeyboard_Class = String(className);
	} //end if

	var self = this;

	this.VKI_version = ""; //"1.36.unxw.2";
	this.VKI_showVersion = false;
	this.VKI_target = false;
	this.VKI_shift = this.VKI_shiftlock = false;
	this.VKI_altgr = this.VKI_altgrlock = false;
	this.VKI_dead = false;
	this.VKI_deadkeysOn = false;
	this.VKI_kts = this.VKI_kt = "English / International";  // Default keyboard layout
	this.VKI_langAdapt = false;  // Use lang attribute of input to select keyboard
	this.VKI_size = 4;  // Default keyboard size (1-5)
	this.VKI_sizeAdj = false;  // Allow user to adjust keyboard size
	this.VKI_clearPasswords = false;  // Clear password fields on focus
	this.VKI_imageURI = '' + VKeyboard_Image;
	this.VKI_clickless = 0;  // 0 = disabled, > 0 = delay in ms
	this.VKI_keyCenter = 3;

	this.VKI_isIE = false;
	this.VKI_isIElt8 = false;
	this.VKI_isWebKit = window.opera;
	this.VKI_isOpera = RegExp("Opera").test(navigator.userAgent);
	this.VKI_isMoz = (!this.VKI_isWebKit && navigator.product == "Gecko");


	/* ***** i18n text strings ************************************* */
	this.VKI_i18n = {
		'00': "Virtual Keyboard Interface",
		'01': "Display Virtual Keyboard Interface",
		'02': "Select Keyboard Layout",
		'03': "Disabled Keys",
		'04': "On",
		'05': "Off",
		'06': "Close the Keyboard",
		'07': "CLR",
		'08': "Clear this Input",
		'09': "Version",
		'10': "Adjust Keyboard Size"
	};


	/* ***** Create keyboards ************************************** */
	this.VKI_layout = {};

	this.VKI_layout["# Numpad #"] = [ // Number pad
		[["$"], ["\u00a3"], ["\u20ac"], ["\u00a5"], ["/"], ["^"], ["Bksp", "Bksp"]],
		[["."], ["7"], ["8"], ["9"], ["*"], ["<"], ["("], ["["]],
		[["="], ["4"], ["5"], ["6"], ["-"], [">"], [")"], ["]"]],
		[["0"], ["1"], ["2"], ["3"], ["+"], ["Enter", "Enter"]],
		[[" "]]
	]; this.VKI_layout["# Numpad #"].DDK = true;

	this.VKI_layout["English / International"] = [ // US International Keyboard
		[["`", "~"], ["1", "!", "\u00a1", "\u00b9"], ["2", "@", "\u00b2"], ["3", "#", "\u00b3"], ["4", "$", "\u00a4", "\u00a3"], ["5", "%", "\u20ac"], ["6", "^", "\u00bc"], ["7", "&", "\u00bd"], ["8", "*", "\u00be"], ["9", "(", "\u2018"], ["0", ")", "\u2019"], ["-", "_", "\u00a5"], ["=", "+", "\u00d7", "\u00f7"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q", "\u00e4", "\u00c4"], ["w", "W", "\u00e5", "\u00c5"], ["e", "E", "\u00e9", "\u00c9"], ["r", "R", "\u00ae"], ["t", "T", "\u00fe", "\u00de"], ["y", "Y", "\u00fc", "\u00dc"], ["u", "U", "\u00fa", "\u00da"], ["i", "I", "\u00ed", "\u00cd"], ["o", "O", "\u00f3", "\u00d3"], ["p", "P", "\u00f6", "\u00d6"], ["[", "{", "\u00ab"], ["]", "}", "\u00bb"], ["\\", "|", "\u00ac", "\u00a6"]],
		[["Caps", "Caps"], ["a", "A", "\u00e1", "\u00c1"], ["s", "S", "\u00df", "\u00a7"], ["d", "D", "\u00f0", "\u00d0"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L", "\u00f8", "\u00d8"], [";", ":", "\u00b6", "\u00b0"], ["'", '"', "\u00b4", "\u00a8"], ["Enter", "Enter"]],
		[["Shift", "Shift"], ["z", "Z", "\u00e6", "\u00c6"], ["x", "X"], ["c", "C", "\u00a9", "\u00a2"], ["v", "V"], ["b", "B"], ["n", "N", "\u00f1", "\u00d1"], ["m", "M", "\u00b5"], [",", "<", "\u00e7", "\u00c7"], [".", ">"], ["/", "?", "\u00bf"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["Alt", "Alt"]]
	]; //this.VKI_layout["English / International"].lang = ["en"];

	/* Optionals */

	this.VKI_layout["Japanese / 日本人"] = [ // Basic Japanese Hiragana/Katakana Keyboard
		[["\uff5e"], ["\u306c", "\u30cc"], ["\u3075", '\u30d5'], ["\u3042", "\u30a2", "\u3041", "\u30a1"], ["\u3046", "\u30a6", "\u3045", "\u30a5"], ["\u3048", "\u30a8", "\u3047", "\u30a7"], ["\u304a", "\u30aa", "\u3049","\u30a9"], ["\u3084", "\u30e4", "\u3083", "\u30e3"], ["\u3086", "\u30e6", "\u3085", "\u30e5"], ["\u3088", "\u30e8", "\u3087", "\u30e7"], ["\u308f", "\u30ef", "\u3092", "\u30f2"], ["\u307b", "\u30db", "\u30fc", "\uff1d"], ["\u3078", "\u30d8" ,"\uff3e", "\uff5e"], ['"', '"', "\uffe5", "\uff5c"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["\u305f", "\u30bf"], ["\u3066", "\u30c6"], ["\u3044", "\u30a4", "\u3043", "\u30a3"], ["\u3059", "\u30b9"], ["\u304b", "\u30ab"], ["\u3093", "\u30f3"], ["\u306a", "\u30ca"], ["\u306b", "\u30cb"], ["\u3089", "\u30e9"], ["\u305b", "\u30bb"],["\u3001", "\u3001", "\uff20", "\u2018"],["\u3002", "\u3002", "\u300c", "\uff5b"],["\uffe5","", "", "\uff0a"]],
		[["Caps", "Caps"], ["\u3061", "\u30c1"], ["\u3068", "\u30c8"], ["\u3057", "\u30b7"], ["\u306f", "\u30cf"], ["\u304d", "\u30ad"], ["\u304f", "\u30af"], ["\u307e", "\u30de"], ["\u306e", "\u30ce"], ["\u308c", "\u30ec", "\uff1b", "\uff0b"], ["\u3051", "\u30b1", "\uff1a", "\u30f6"], ["\u3080", "\u30e0", "\u300d", "\uff5d"],["Enter", "Enter"]],
		[["Shift", "Shift"], ["\u3064", "\u30c4"], ["\u3055", "\u30b5"], ["\u305d", "\u30bd"], ["\u3072", "\u30d2"], ["\u3053", "\u30b3"], ["\u307f", "\u30df"], ["\u3082", "\u30e2"], ["\u306d", "\u30cd", "\u3001", "\uff1c"], ["\u308b", "\u30eb", "\u3002", "\uff1e"], ["\u3081", "\u30e1", "\u30fb", "\uff1f"], ["\u308d", "\u30ed", "", "\uff3f"], ["Shift", "Shift"]],
		[["AltLk", "AltLk"], [" ", " ", " ", " "], ["Alt", "Alt"]]
	];

/*
	this.VKI_layout["English UK"] = [ // UK Standard Keyboard
		[["`", "\u00ac", "\u00a6"], ["1", "!"], ["2", '"'], ["3", "\u00a3"], ["4", "$", "\u20ac"], ["5", "%"], ["6", "^"], ["7", "&"], ["8", "*"], ["9", "("], ["0", ")"], ["-", "_"], ["=", "+"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u00e9", "\u00c9"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U", "\u00fa", "\u00da"], ["i", "I", "\u00ed", "\u00cd"], ["o", "O", "\u00f3", "\u00d3"], ["p", "P"], ["[", "{"], ["]", "}"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A", "\u00e1", "\u00c1"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], [";", ":"], ["'", "@"], ["#", "~"]],
		[["Shift", "Shift"], ["\\", "|"], ["z", "Z"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M"], [",", "<"], [".", ">"], ["/", "?"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];
*/

	this.VKI_layout["German Deutsch / Österreicher"] = [ // German Standard Keyboard
		[["\u005e", "\u00b0"], ["1", "!"], ["2", '"', "\u00b2"], ["3", "\u00a7", "\u00b3"], ["4", "$"], ["5", "%"], ["6", "&"], ["7", "/", "{"], ["8", "(", "["], ["9", ")", "]"], ["0", "=", "}"], ["\u00df", "?", "\\"], ["\u00b4", "\u0060"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q", "\u0040"], ["w", "W"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["z", "Z"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00fc", "\u00dc"], ["+", "*", "~"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u00f6", "\u00d6"], ["\u00e4", "\u00c4"], ["#", "'"]],
		[["Shift", "Shift"], ["<", ">", "\u00a6"], ["y", "Y"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M", "\u00b5"], [",", ";"], [".", ":"], ["-", "_"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

	this.VKI_layout["French Français / Belgian"] = [ // French Standard Keyboard
		[["\u00b2", "\u00b3"], ["&", "1"], ["\u00e9", "2", "~"], ['"', "3", "#"], ["'", "4", "{"], ["(", "5", "["], ["-", "6", "|"], ["\u00e8", "7", "\u0060"], ["_", "8", "\\"], ["\u00e7", "9", "\u005e"], ["\u00e0", "0", "\u0040"], [")", "\u00b0", "]"], ["=", "+", "}"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["a", "A"], ["z", "Z"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["^", "\u00a8"], ["$", "\u00a3", "\u00a4"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["q", "Q"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["m", "M"], ["\u00f9", "%"], ["*", "\u03bc"]],
		[["Shift", "Shift"], ["<", ">"], ["w", "W"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], [",", "?"], [";", "."], [":", "/"], ["!", "\u00a7"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

/* Can use the French keyboard
	this.VKI_layout.Belgian = [ // Belgian Standard Keyboard
		[["\u00b2", "\u00b3"], ["&", "1", "|"], ["\u00e9", "2", "@"], ['"', "3", "#"], ["'", "4"], ["(", "5"], ["\u00a7", "6", "^"], ["\u00e8", "7"], ["!", "8"], ["\u00e7", "9", "{"], ["\u00e0", "0", "}"], [")", "\u00b0"], ["-", "_"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["a", "A"], ["z", "Z"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u005e", "\u00a8", "["], ["$", "*", "]"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["q", "Q"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["m", "M"], ["\u00f9", "%", "\u00b4"], ["\u03bc", "\u00a3", "`"]],
		[["Shift", "Shift"], ["<", ">", "\\"], ["w", "W"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], [",", "?"], [";", "."], [":", "/"], ["=", "+", "~"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];
*/

/* Can use the International keyboard
	this.VKI_layout["Dutch / Nederlands"] = [ // Dutch Standard Keyboard
		[["@", "\u00a7", "\u00ac"], ["1", "!", "\u00b9"], ["2", '"', "\u00b2"], ["3", "#", "\u00b3"], ["4", "$", "\u00bc"], ["5", "%", "\u00bd"], ["6", "&", "\u00be"], ["7", "_", "\u00a3"], ["8", "(", "{"], ["9", ")", "}"], ["0", "'"], ["/", "?", "\\"], ["\u00b0", "~", "\u00b8"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u20ac"], ["r", "R", "\u00b6"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00a8", "^"], ["*", "|"], ["<", ">"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S", "\u00df"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["+", "\u00b1"], ["\u00b4", "\u0060"], ["Enter", "Enter"]],
		[["Shift", "Shift"], ["]", "[", "\u00a6"], ["z", "Z", "\u00ab"], ["x", "X", "\u00bb"], ["c", "C", "\u00a2"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M", "\u00b5"], [",", ";"], [".", ":", "\u00b7"], ["-", "="], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];
*/

	this.VKI_layout["Danish Dansk / Norwegian Norsk"] = [ // Danish Standard Keyboard
		[["\u00bd", "\u00a7"], ["1", "!"], ["2", '"', "@"], ["3", "#", "\u00a3"], ["4", "\u00a4", "$"], ["5", "%", "\u20ac"], ["6", "&"], ["7", "/", "{"], ["8", "(", "["], ["9", ")", "]"], ["0", "=", "}"], ["+", "?"], ["\u00b4", "`", "|"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00e5", "\u00c5"], ["\u00a8", "^", "~"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u00e6", "\u00c6"], ["\u00f8", "\u00d8"], ["'", "*"]],
		[["Shift", "Shift"], ["<", ">", "\\"], ["z", "Z"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M", "\u03bc", "\u039c"], [",", ";"], [".", ":"], ["-", "_"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

/* Can use the Dannish keyboard
	this.VKI_layout.Norwegian = [ // Norwegian Standard Keyboard
		[["|", "\u00a7"], ["1", "!"], ["2", '"', "@"], ["3", "#", "\u00a3"], ["4", "\u00a4", "$"], ["5", "%"], ["6", "&"], ["7", "/", "{"], ["8", "(", "["], ["9", ")", "]"], ["0", "=", "}"], ["+", "?"], ["\\", "`", "\u00b4"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00e5", "\u00c5"], ["\u00a8", "^", "~"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u00f8", "\u00d8"], ["\u00e6", "\u00c6"], ["'", "*"]],
		[["Shift", "Shift"], ["<", ">"], ["z", "Z"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M", "\u03bc", "\u039c"], [",", ";"], [".", ":"], ["-", "_"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];
*/

	this.VKI_layout["Swedish Svenska / Finnish Suomi"] = [ // Swedish Standard Keyboard
		[["\u00a7", "\u00bd"], ["1", "!"], ["2", '"', "@"], ["3", "#", "\u00a3"], ["4", "\u00a4", "$"], ["5", "%", "\u20ac"], ["6", "&"], ["7", "/", "{"], ["8", "(", "["], ["9", ")", "]"], ["0", "=", "}"], ["+", "?", "\\"], ["\u00b4", "`"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00e5", "\u00c5"], ["\u00a8", "^", "~"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u00f6", "\u00d6"], ["\u00e4", "\u00c4"], ["'", "*"]],
		[["Shift", "Shift"], ["<", ">", "|"], ["z", "Z"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M", "\u03bc", "\u039c"], [",", ";"], [".", ":"], ["-", "_"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

	this.VKI_layout["Spanish Español / Portuguese Português"] = [ // Spanish (Spain) Standard Keyboard
		[["\u00ba", "\u00aa", "\\"], ["1", "!", "|"], ["2", '"', "@"], ["3", "'", "#"], ["4", "$", "~"], ["5", "%", "\u20ac"], ["6", "&","\u00ac"], ["7", "/"], ["8", "("], ["9", ")"], ["0", "="], ["'", "?"], ["\u00a1", "\u00bf"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u0060", "^", "["], ["\u002b", "\u002a", "]"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u00f1", "\u00d1"], ["\u00b4", "\u00a8", "{"], ["\u00e7", "\u00c7", "}"]],
		[["Shift", "Shift"], ["<", ">"], ["z", "Z"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M"], [",", ";"], [".", ":"], ["-", "_"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

/* Can use the Spanish keyboard
	this.VKI_layout.Portuguese = [ // Portuguese (Brazil) Standard Keyboard
		[["'", '"'], ["1", "!", "\u00b9"], ["2", "@", "\u00b2"], ["3", "#", "\u00b3"], ["4", "$", "\u00a3"], ["5", "%", "\u00a2"], ["6", "\u00a8", "\u00ac"], ["7", "&"], ["8", "*"], ["9", "("], ["0", ")"], ["-", "_"], ["=", "+", "\u00a7"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q", "/"], ["w", "W", "?"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00b4", "`"], ["[", "{", "\u00aa"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u00e7", "\u00c7"], ["~", "^"], ["]", "}", "\u00ba"], ["/", "?"]],
		[["Shift", "Shift"], ["\\", "|"], ["z", "Z"], ["x", "X"], ["c", "C", "\u20a2"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M"], [",", "<"], [".", ">"], [":", ":"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];
*/

/* Will use the International keyboard
	this.VKI_layout.Italian = [ // Italian Standard Keyboard
		[["\u005c", "\u007c"], ["1", "!"], ["2", '"'], ["3", "\u00a3"], ["4", "$", "\u20ac"], ["5", "%"], ["6", "&"], ["7", "/"], ["8", "("], ["9", ")"], ["0", "="], ["'", "?"], ["\u00ec", "\u005e"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u20ac"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00e8", "\u00e9", "[", "{"], ["+", "*", "]", "}"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u00f2", "\u00e7", "@"], ["\u00e0", "\u00b0", "#"], ["\u00f9", "\u00a7"]],
		[["Shift", "Shift"], ["<", ">"], ["z", "Z"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M"], [",", ";"], [".", ":"], ["-", "_"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];
*/

	this.VKI_layout["Romanian Română"] = [ // Romanian Standard Keyboard (Unicode)
		[["\u201E", "\u201D", "\u0060", "~"], ["1", "!","~"], ["2", "\u0040", "\u02C7"], ["3", "#","\u005E"], ["4", "$", "\u02D8"], ["5", "%", "\u00B0"], ["6", "\u005E", "\u02DB"], ["7", "&", "\u0060"], ["8", "*", "\u02D9"], ["9", "(", "\u00B4"], ["0", ")", "\u02DD"], ["-", "_", "\u00A8"], ["=", "+", "\u00B8", "\u00B1"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u20AC"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P", "\u00A7"], ["\u0103", "\u0102", "[", "{"], ["\u00EE", "\u00CE", "]","}"], ["\u00E2", "\u00C2", "\\", "|"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S", "\u00df"], ["d", "D", "\u00f0", "\u00D0"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L", "\u0142", "\u0141"], [(this.VKI_isIElt8) ? "\u015F" : "\u0219", (this.VKI_isIElt8) ? "\u015E" : "\u0218", ";", ":"], [(this.VKI_isIElt8) ? "\u0163" : "\u021B", (this.VKI_isIElt8) ? "\u0162" : "\u021A", "\'", "\""], ["Enter", "Enter"]],
		[["Shift", "Shift"], ["\\", "|"], ["z", "Z"], ["x", "X"], ["c", "C", "\u00A9"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M"], [",", ";", "<", "\u00AB"], [".", ":", ">", "\u00BB"], ["/", "?"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

	this.VKI_layout["Greek Eλληνική"] = [ // Greek Standard Keyboard
		[["`", "~"], ["1", "!"], ["2", "@", "\u00b2"], ["3", "#", "\u00b3"], ["4", "$", "\u00a3"], ["5", "%", "\u00a7"], ["6", "^", "\u00b6"], ["7", "&"], ["8", "*", "\u00a4"], ["9", "(", "\u00a6"], ["0", ")", "\u00ba"], ["-", "_", "\u00b1"], ["=", "+", "\u00bd"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], [";", ":"], ["\u03c2", "^"], ["\u03b5", "\u0395"], ["\u03c1", "\u03a1"], ["\u03c4", "\u03a4"], ["\u03c5", "\u03a5"], ["\u03b8", "\u0398"], ["\u03b9", "\u0399"], ["\u03bf", "\u039f"], ["\u03c0", "\u03a0"], ["[", "{", "\u201c"], ["]", "}", "\u201d"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["\u03b1", "\u0391"], ["\u03c3", "\u03a3"], ["\u03b4", "\u0394"], ["\u03c6", "\u03a6"], ["\u03b3", "\u0393"], ["\u03b7", "\u0397"], ["\u03be", "\u039e"], ["\u03ba", "\u039a"], ["\u03bb", "\u039b"], ["\u0384", "\u00a8", "\u0385"], ["'", '"'], ["\\", "|", "\u00ac"]],
		[["Shift", "Shift"], ["<", ">"], ["\u03b6", "\u0396"], ["\u03c7", "\u03a7"], ["\u03c8", "\u03a8"], ["\u03c9", "\u03a9"], ["\u03b2", "\u0392"], ["\u03bd", "\u039d"], ["\u03bc", "\u039c"], [",", "<"], [".", ">"], ["/", "?"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

	this.VKI_layout["Polish Polski"] = [ // Polish Programmers Keyboard
		[["\u02DB", "\u00B7"], ["1", "!", "~"], ["2", '"', "\u02C7"], ["3", "#", "^"], ["4", "\u00A4", "\u02D8"], ["5", "%", "\u00B0"], ["6", "&", "\u02DB"], ["7", "/", "`"], ["8", "(", "\u00B7"], ["9", ")", "\u00B4"], ["0", "=", "\u02DD"], ["+", "?", "\u00A8"], ["'", "*", "\u00B8"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q", "\\"], ["w", "W", "\u00A6"], ["e", "E"], ["r", "R"], ["t", "T"], ["z", "Z"], ["u", "U", "\u20AC"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u017C", "\u0144", "\u00F7"], ["\u015B", "\u0107", "\u00D7"], ["\u00F3", "\u017A"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S", "\u0111"], ["d", "D", "\u0110"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u0142", "\u0141", "$"], ["\u0105", "\u0119", "\u00DF"], ["Enter", "Enter"]],
		[["Shift", "Shift"], ["<", ">"], ["y", "Y"], ["x", "X"], ["c", "C"], ["v", "V", "@"], ["b", "B", "{"], ["n", "N", "}"], ["m", "M", "\u00A7"], [",", ";", "<"], [".", ":", ">"], ["-", "_"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

	this.VKI_layout["Czech / Slovak"] = [ // Czech / Slovak Keyboard
		[[";", "\u00b0", "`", "~"], ["+", "1", "!"], ["\u011B", "2", "@"], ["\u0161", "3", "#"], ["\u010D", "4", "$"], ["\u0159", "5", "%"], ["\u017E", "6", "^"], ["\u00FD", "7", "&"], ["\u00E1", "8", "*"], ["\u00ED", "9", "("], ["\u00E9", "0", ")"], ["=", "%", "-", "_"], ["\u00B4", "\u02c7", "=", "+"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q"], ["w", "W"], ["e", "E", "\u20AC"], ["r", "R"], ["t", "T"], ["y", "Y"], ["u", "U"], ["i", "I"], ["o", "O"], ["p", "P"], ["\u00FA", "/", "[", "{"], [")", "(", "]", "}"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A"], ["s", "S"], ["d", "D"], ["f", "F"], ["g", "G"], ["h", "H"], ["j", "J"], ["k", "K"], ["l", "L"], ["\u016F", '"', ";", ":"], ["\u00A7", "!", "\u00a4", "^"], ["\u00A8", "'", "\\", "|"]],
		[["Shift", "Shift"], ["\\", "|", "", "\u02dd"], ["z", "Z"], ["x", "X"], ["c", "C"], ["v", "V"], ["b", "B"], ["n", "N"], ["m", "M"], [",", "?", "<", "\u00d7"], [".", ":", ">", "\u00f7"], ["-", "_", "/", "?"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["Alt", "Alt"]]
	];

	this.VKI_layout["Hungarian Magyar"] = [ // Hungarian Standard Keyboard
		[["0", "\u00a7"], ["1", "'", "\u007e"], ["2", '"', "\u02c7"], ["3", "+", "\u02c6"], ["4", "!", "\u02d8"], ["5", "%", "\u00b0"], ["6", "/", "\u02db"], ["7", "=", "\u0060"], ["8", "(", "\u02d9"], ["9", ")", "\u00b4"], ["\u00f6", "\u00d6", "\u02dd"], ["\u00fc", "\u00dc", "\u00a8"], ["\u00f3", "\u00d3", "\u00b8"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["q", "Q", "\u005c"], ["w", "W", "\u007c"], ["e", "E", "\u00c4"], ["r", "R"], ["t", "T"], ["z", "Z"], ["u", "U", "\u20ac"], ["i", "I", "\u00cd"], ["o", "O"], ["p", "P"], ["\u0151", "\u0150", "\u00f7"], ["\u00fa", "\u00da", "\u00d7"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["a", "A", "\u00e4"], ["s", "S","\u0111"], ["d", "D","\u0110"], ["f", "F","\u005b"], ["g", "G","\u005d"], ["h", "H"], ["j", "J","\u00ed"], ["k", "K","\u0141"], ["l", "L","\u0142"], ["\u00e9", "\u00c9","\u0024"], ["\u00e1", "\u00c1","\u00df"], ["\u0171", "\u0170","\u00a4"]],
		[["Shift", "Shift"], ["\u00ed", "\u00cd","\u003c"], ["y", "Y","\u003e"], ["x", "X","\u0023"], ["c", "C","\u0026"], ["v", "V","\u0040"], ["b", "B","\u007b"], ["n", "N","\u007d"], ["m", "M","\u003c"], [",", "?","\u003b"], [".", ":","\u003e"], ["-", "_","\u002a"], ["Shift", "Shift"]],
		[[" ", " ", " ", " "], ["AltGr", "AltGr"]]
	];

	this.VKI_layout["Bulgarian български"] = [ // Bulgarian Phonetic Keyboard
		[["\u0447", "\u0427"], ["1", "!"], ["2", "@"], ["3", "#"], ["4", "$"], ["5", "%"], ["6", "^"], ["7", "&"], ["8", "*"], ["9", "("], ["0", ")"], ["-", "_"], ["=", "+"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["\u044F", "\u042F"], ["\u0432", "\u0412"], ["\u0435", "\u0415"], ["\u0440", "\u0420"], ["\u0442", "\u0422"], ["\u044A", "\u042A"], ["\u0443", "\u0423"], ["\u0438", "\u0418"], ["\u043E", "\u041E"], ["\u043F", "\u041F"], ["\u0448", "\u0428"], ["\u0449", "\u0429"], ["\u044E", "\u042E"]],
		[["Caps", "Caps"], ["\u0430", "\u0410"], ["\u0441", "\u0421"], ["\u0434", "\u0414"], ["\u0444", "\u0424"], ["\u0433", "\u0413"], ["\u0445", "\u0425"], ["\u0439", "\u0419"], ["\u043A", "\u041A"], ["\u043B", "\u041B"], [";", ":"], ["'", '"'], ["Enter", "Enter"]],
		[["Shift", "Shift"], ["\u0437", "\u0417"], ["\u044C", "\u042C"], ["\u0446", "\u0426"], ["\u0436", "\u0416"], ["\u0431", "\u0411"], ["\u043D", "\u041D"], ["\u043C", "\u041C"], [",", "<"], [".", ">"], ["/", "?"], ["Shift", "Shift"]],
		[[" ", " "]]
	];

	this.VKI_layout["Russian Русский"] = [ // Russian Standard Keyboard
		[["\u0451", "\u0401"], ["1", "!"], ["2", '"'], ["3", "\u2116"], ["4", ";"], ["5", "%"], ["6", ":"], ["7", "?"], ["8", "*"], ["9", "("], ["0", ")"], ["-", "_"], ["=", "+"], ["Bksp", "Bksp"]],
		[["Tab", "Tab"], ["\u0439", "\u0419"], ["\u0446", "\u0426"], ["\u0443", "\u0423"], ["\u043A", "\u041A"], ["\u0435", "\u0415"], ["\u043D", "\u041D"], ["\u0433", "\u0413"], ["\u0448", "\u0428"], ["\u0449", "\u0429"], ["\u0437", "\u0417"], ["\u0445", "\u0425"], ["\u044A", "\u042A"], ["Enter", "Enter"]],
		[["Caps", "Caps"], ["\u0444", "\u0424"], ["\u044B", "\u042B"], ["\u0432", "\u0412"], ["\u0430", "\u0410"], ["\u043F", "\u041F"], ["\u0440", "\u0420"], ["\u043E", "\u041E"], ["\u043B", "\u041B"], ["\u0434", "\u0414"], ["\u0436", "\u0416"], ["\u044D", "\u042D"], ["\\", "/"]],
		[["Shift", "Shift"], ["/", "|"], ["\u044F", "\u042F"], ["\u0447", "\u0427"], ["\u0441", "\u0421"], ["\u043C", "\u041C"], ["\u0438", "\u0418"], ["\u0442", "\u0422"], ["\u044C", "\u042C"], ["\u0431", "\u0411"], ["\u044E", "\u042E"], [".", ","], ["Shift", "Shift"]],
		[[" ", " "]]
	];

	/* END Optionals */

	/* ***** Define Dead Keys ************************************** */
	this.VKI_deadkey = {};

	// - Lay out each dead key set in one row of sub-arrays.  The rows
	//   below are wrapped so uppercase letters are below their
	//   lowercase equivalents.
	//
	// - The first letter in each sub-array is the letter pressed after
	//   the diacritic.  The second letter is the letter this key-combo
	//   will generate.
	//
	// - Note that if you have created a new keyboard layout and want
	//   it included in the distributed script, PLEASE TELL ME if you
	//   have added additional dead keys to the ones below.

	this.VKI_deadkey['"'] = this.VKI_deadkey['\u00a8'] = [ // Umlaut / Diaeresis / Greek Dialytika
		["a", "\u00e4"], ["e", "\u00eb"], ["i", "\u00ef"], ["o", "\u00f6"], ["u", "\u00fc"], ["y", "\u00ff"], ["\u03b9", "\u03ca"], ["\u03c5", "\u03cb"], ["\u016B", "\u01D6"], ["\u00FA", "\u01D8"], ["\u01D4", "\u01DA"], ["\u00F9", "\u01DC"],
		["A", "\u00c4"], ["E", "\u00cb"], ["I", "\u00cf"], ["O", "\u00d6"], ["U", "\u00dc"], ["Y", "\u0178"], ["\u0399", "\u03aa"], ["\u03a5", "\u03ab"], ["\u016A", "\u01D5"], ["\u00DA", "\u01D7"], ["\u01D3", "\u01D9"], ["\u00D9", "\u01DB"],
		["\u304b", "\u304c"], ["\u304d", "\u304e"], ["\u304f", "\u3050"], ["\u3051", "\u3052"], ["\u3053", "\u3054"],
		["\u305f", "\u3060"], ["\u3061", "\u3062"], ["\u3064", "\u3065"], ["\u3066", "\u3067"], ["\u3068", "\u3069"],
		["\u3055", "\u3056"], ["\u3057", "\u3058"], ["\u3059", "\u305a"], ["\u305b", "\u305c"], ["\u305d", "\u305e"],
		["\u306f", "\u3070"], ["\u3072", "\u3073"], ["\u3075", "\u3076"], ["\u3078", "\u3079"], ["\u307b", "\u307c"],
		["\u30ab", "\u30ac"], ["\u30ad", "\u30ae"], ["\u30af", "\u30b0"], ["\u30b1", "\u30b2"], ["\u30b3", "\u30b4"],
		["\u30bf", "\u30c0"], ["\u30c1", "\u30c2"], ["\u30c4", "\u30c5"], ["\u30c6", "\u30c7"], ["\u30c8", "\u30c9"],
		["\u30b5", "\u30b6"], ["\u30b7", "\u30b8"], ["\u30b9", "\u30ba"], ["\u30bb", "\u30bc"], ["\u30bd", "\u30be"],
		["\u30cf", "\u30d0"], ["\u30d2", "\u30d3"], ["\u30d5", "\u30d6"], ["\u30d8", "\u30d9"], ["\u30db", "\u30dc"]
	];
	this.VKI_deadkey['~'] = [ // Tilde / Stroke
		["a", "\u00e3"], ["l", "\u0142"], ["n", "\u00f1"], ["o", "\u00f5"],
		["A", "\u00c3"], ["L", "\u0141"], ["N", "\u00d1"], ["O", "\u00d5"]
	];
	this.VKI_deadkey['^'] = [ // Circumflex
		["a", "\u00e2"], ["e", "\u00ea"], ["i", "\u00ee"], ["o", "\u00f4"], ["u", "\u00fb"], ["w", "\u0175"], ["y", "\u0177"],
		["A", "\u00c2"], ["E", "\u00ca"], ["I", "\u00ce"], ["O", "\u00d4"], ["U", "\u00db"], ["W", "\u0174"], ["Y", "\u0176"]
	];
	this.VKI_deadkey['\u02c7'] = [ // Baltic caron
		["c", "\u010D"], ["d", "\u010f"], ["e", "\u011b"], ["s", "\u0161"], ["l", "\u013e"], ["n", "\u0148"], ["r", "\u0159"], ["t", "\u0165"], ["u", "\u01d4"], ["z", "\u017E"], ["\u00fc", "\u01da"],
		["C", "\u010C"], ["D", "\u010e"], ["E", "\u011a"], ["S", "\u0160"], ["L", "\u013d"], ["N", "\u0147"], ["R", "\u0158"], ["T", "\u0164"], ["U", "\u01d3"], ["Z", "\u017D"], ["\u00dc", "\u01d9"]
	];
	this.VKI_deadkey['\u02d8'] = [ // Romanian and Turkish breve
		["a", "\u0103"], ["g", "\u011f"],
		["A", "\u0102"], ["G", "\u011e"]
	];
	this.VKI_deadkey['-'] = this.VKI_deadkey['\u00af'] = [ // Macron
		["a", "\u0101"], ["e", "\u0113"], ["i", "\u012b"], ["o", "\u014d"], ["u", "\u016B"], ["y", "\u0233"], ["\u00fc", "\u01d6"],
		["A", "\u0100"], ["E", "\u0112"], ["I", "\u012a"], ["O", "\u014c"], ["U", "\u016A"], ["Y", "\u0232"], ["\u00dc", "\u01d5"]
	];
	this.VKI_deadkey['`'] = [ // Grave
		["a", "\u00e0"], ["e", "\u00e8"], ["i", "\u00ec"], ["o", "\u00f2"], ["u", "\u00f9"], ["\u00fc", "\u01dc"],
		["A", "\u00c0"], ["E", "\u00c8"], ["I", "\u00cc"], ["O", "\u00d2"], ["U", "\u00d9"], ["\u00dc", "\u01db"]
	];
	this.VKI_deadkey["'"] = this.VKI_deadkey['\u00b4'] = this.VKI_deadkey['\u0384'] = [ // Acute / Greek Tonos
		["a", "\u00e1"], ["e", "\u00e9"], ["i", "\u00ed"], ["o", "\u00f3"], ["u", "\u00fa"], ["y", "\u00fd"], ["\u03b1", "\u03ac"], ["\u03b5", "\u03ad"], ["\u03b7", "\u03ae"], ["\u03b9", "\u03af"], ["\u03bf", "\u03cc"], ["\u03c5", "\u03cd"], ["\u03c9", "\u03ce"], ["\u00fc", "\u01d8"],
		["A", "\u00c1"], ["E", "\u00c9"], ["I", "\u00cd"], ["O", "\u00d3"], ["U", "\u00da"], ["Y", "\u00dd"], ["\u0391", "\u0386"], ["\u0395", "\u0388"], ["\u0397", "\u0389"], ["\u0399", "\u038a"], ["\u039f", "\u038c"], ["\u03a5", "\u038e"], ["\u03a9", "\u038f"], ["\u00dc", "\u01d7"]
	];
	this.VKI_deadkey['\u02dd'] = [ // Hungarian Double Acute Accent
		["o", "\u0151"], ["u", "\u0171"],
		["O", "\u0150"], ["U", "\u0170"]
	];
	this.VKI_deadkey['\u0385'] = [ // Greek Dialytika + Tonos
		["\u03b9", "\u0390"], ["\u03c5", "\u03b0"]
	];
	this.VKI_deadkey['\u00b0'] = this.VKI_deadkey['\u00ba'] = [ // Ring
		["a", "\u00e5"], ["u", "\u016f"],
		["A", "\u00c5"], ["U", "\u016e"]
	];
	this.VKI_deadkey['\u02DB'] = [ // Ogonek
		["a", "\u0106"], ["e", "\u0119"], ["i", "\u012f"], ["o", "\u01eb"], ["u", "\u0173"], ["y", "\u0177"],
		["A", "\u0105"], ["E", "\u0118"], ["I", "\u012e"], ["O", "\u01ea"], ["U", "\u0172"], ["Y", "\u0176"]
	];
	this.VKI_deadkey['\u02D9'] = [ // Dot-above
		["c", "\u010B"], ["e", "\u0117"], ["g", "\u0121"], ["z", "\u017C"],
		["C", "\u010A"], ["E", "\u0116"], ["G", "\u0120"], ["Z", "\u017B"]
	];
	this.VKI_deadkey['\u00B8'] = this.VKI_deadkey['\u201a'] = [ // Cedilla
		["c", "\u00e7"], ["s", "\u015F"],
		["C", "\u00c7"], ["S", "\u015E"]
	];
	this.VKI_deadkey[','] = [ // Comma
		["s", (this.VKI_isIElt8) ? "\u015F" : "\u0219"], ["t", (this.VKI_isIElt8) ? "\u0163" : "\u021B"],
		["S", (this.VKI_isIElt8) ? "\u015E" : "\u0218"], ["T", (this.VKI_isIElt8) ? "\u0162" : "\u021A"]
	];
	this.VKI_deadkey['\u3002'] = [ // Hiragana/Katakana Point
		["\u306f", "\u3071"], ["\u3072", "\u3074"], ["\u3075", "\u3077"], ["\u3078", "\u307a"], ["\u307b", "\u307d"],
		["\u30cf", "\u30d1"], ["\u30d2", "\u30d4"], ["\u30d5", "\u30d7"], ["\u30d8", "\u30da"], ["\u30db", "\u30dd"]
	];


	/* ***** Define Symbols **************************************** */
	this.VKI_symbol = {
	  '\u200c': "ZW\r\nNJ", '\u200d': "ZW\r\nJ"
	};


	/* ****************************************************************
	 * Attach the keyboard to an element
	 *
	 */
	this.VKI_attachKeyboard = VKI_attach = function(elem) {
	  if (elem.VKI_attached) return false;
	  var keybut = document.createElement('img');
		  keybut.src = this.VKI_imageURI;
		  keybut.alt = this.VKI_i18n['00'] + ' ' + this.VKI_version;
		  keybut.className = "keyboardInputInitiator";
		  keybut.title = this.VKI_i18n['01'];
		  keybut.elem = elem;
		  keybut.onclick = function() { self.VKI_show(this.elem); };
	  elem.VKI_attached = true;
	  elem.parentNode.insertBefore(keybut, (elem.dir == "rtl") ? elem : elem.nextSibling);
	  if (this.VKI_isIE) {
		elem.onclick = elem.onselect = elem.onkeyup = function(e) {
		  if ((e || event).type != "keyup" || !this.readOnly)
			this.range = document.selection.createRange();
		};
	  }
	};


	/* ***** Find tagged input & textarea elements ***************** */
	var inputElems = [
	  document.getElementsByTagName('input'),
	  document.getElementsByTagName('textarea')
	];
	for (var x = 0, elem; elem = inputElems[x++];)
	  for (var y = 0, ex; ex = elem[y++];)
		if ((ex.nodeName == "TEXTAREA" || ex.type == "text" || ex.type == "password") && ex.className.indexOf(VKeyboard_Class) > -1)
		  this.VKI_attachKeyboard(ex);


	/* ***** Build the keyboard interface ************************** */
	this.VKI_keyboard = document.createElement('table');
	this.VKI_keyboard.id = "keyboardInputMaster";
	this.VKI_keyboard.dir = "ltr";
	this.VKI_keyboard.cellSpacing = this.VKI_keyboard.border = "0";

	var thead = document.createElement('thead');
	  var tr = document.createElement('tr');
		var th = document.createElement('th');
		  var abbr = document.createElement('abbr');
			  abbr.title = this.VKI_i18n['00'] + ' ' + this.VKI_version;
			  abbr.appendChild(document.createTextNode('vKey'));
			th.appendChild(abbr);

		  var kblist = document.createElement('select');
			  kblist.title = this.VKI_i18n['02'];
			for (ktype in this.VKI_layout) {
			  if (typeof this.VKI_layout[ktype] == "object") {
				if (!this.VKI_layout[ktype].lang) this.VKI_layout[ktype].lang = [];
				var opt = document.createElement('option');
					opt.value = ktype;
					opt.appendChild(document.createTextNode(ktype));
				  kblist.appendChild(opt);
			  }
			}
			if (kblist.options.length) {
				kblist.value = this.VKI_kt;
				kblist.onchange = function() {
				  self.VKI_kts = self.VKI_kt = this.value;
				  self.VKI_buildKeys();
				  self.VKI_position(true);
				};
			  th.appendChild(kblist);
			}

		  if (this.VKI_sizeAdj) {
			this.VKI_size = Math.min(5, Math.max(1, this.VKI_size));
			var kbsize = document.createElement('select');
				kbsize.title = this.VKI_i18n['10'];
			  for (var x = 1; x <= 5; x++) {
				var opt = document.createElement('option');
					opt.value = x;
					opt.appendChild(document.createTextNode(x));
				  kbsize.appendChild(opt);
			  } kbsize.value = this.VKI_size;
				kbsize.onchange = function() {
				  self.VKI_keyboard.className = self.VKI_keyboard.className.replace(/ ?keyboardInputSize\d ?/, "");
				  if (this.value != 2) self.VKI_keyboard.className += " keyboardInputSize" + this.value;
				  self.VKI_position(true);
				  alert(self.VKI_keyboard.className);
				};
			th.appendChild(kbsize);
		  } else {
		  	if(this.VKI_size != 2) {
		  		self.VKI_keyboard.className += " keyboardInputSize" + this.VKI_size; // unixw
		  	}
		  }

/*
		var label = document.createElement('label');
		  var checkbox = document.createElement('input');
			  checkbox.type = "checkbox";
			  checkbox.title = this.VKI_i18n['03'] + ": " + ((this.VKI_deadkeysOn) ? this.VKI_i18n['04'] : this.VKI_i18n['05']);
			  checkbox.defaultChecked = this.VKI_deadkeysOn;
			  checkbox.onclick = function() {
				self.VKI_deadkeysOn = this.checked;
				this.title = self.VKI_i18n['03'] + ": " + ((this.checked) ? self.VKI_i18n['04'] : self.VKI_i18n['05']);
				self.VKI_modify("");
				return true;
			  };
			label.appendChild(this.VKI_deadkeysElem = checkbox);
			  checkbox.checked = this.VKI_deadkeysOn;
		th.appendChild(label);
*/
		tr.appendChild(th);

		var td = document.createElement('td');
		  var clearer = document.createElement('span');
			  clearer.id = "keyboardInputClear";
			  clearer.appendChild(document.createTextNode(this.VKI_i18n['07']));
			  clearer.title = this.VKI_i18n['08'];
			  clearer.onmousedown = function() { this.className = "pressed"; };
			  clearer.onmouseup = function() { this.className = ""; };
			  clearer.onclick = function() {
				self.VKI_target.value = "";
				self.VKI_target.focus();
				return false;
			  };
			td.appendChild(clearer);

		  var closer = document.createElement('strong');
			  closer.id = "keyboardInputClose";
			  closer.appendChild(document.createTextNode('X'));
			  closer.title = this.VKI_i18n['06'];
			  closer.onmousedown = function() { this.className = "pressed"; };
			  closer.onmouseup = function() { this.className = ""; };
			  closer.onclick = function() { self.VKI_close(); };
			td.appendChild(closer);

		  tr.appendChild(td);
		thead.appendChild(tr);
	this.VKI_keyboard.appendChild(thead);

	var tbody = document.createElement('tbody');
	  var tr = document.createElement('tr');
		var td = document.createElement('td');
			td.colSpan = "2";
		  var div = document.createElement('div');
			  div.id = "keyboardInputLayout";
			td.appendChild(div);
		  if (this.VKI_showVersion) {
			var div = document.createElement('div');
			  var ver = document.createElement('var');
				  ver.title = this.VKI_i18n['09'] + " " + this.VKI_version;
				  ver.appendChild(document.createTextNode("v" + this.VKI_version));
				div.appendChild(ver);
			  td.appendChild(div);
		  }
		  tr.appendChild(td);
		tbody.appendChild(tr);
	this.VKI_keyboard.appendChild(tbody);


	/* ****************************************************************
	 * Build or rebuild the keyboard keys
	 *
	 */
	this.VKI_buildKeys = function() {
	  this.VKI_shift = this.VKI_shiftlock = this.VKI_altgr = this.VKI_altgrlock = this.VKI_dead = false;
	  //this.VKI_deadkeysOn = (this.VKI_layout[this.VKI_kt].DDK) ? false : this.VKI_keyboard.getElementsByTagName('label')[0].getElementsByTagName('input')[0].checked;

	  var container = this.VKI_keyboard.tBodies[0].getElementsByTagName('div')[0];
	  while (container.firstChild) container.removeChild(container.firstChild);

	  for (var x = 0, hasDeadKey = false, lyt; lyt = this.VKI_layout[this.VKI_kt][x++];) {
		var table = document.createElement('table');
			table.cellSpacing = table.border = "0";
		if (lyt.length <= this.VKI_keyCenter) table.className = "keyboardInputCenter";
		  var tbody = document.createElement('tbody');
			var tr = document.createElement('tr');
			for (var y = 0, lkey; lkey = lyt[y++];) {
			  var td = document.createElement('td');
				if (this.VKI_symbol[lkey[0]]) {
				  var span = document.createElement('span');
					  span.className = lkey[0];
					  span.appendChild(document.createTextNode(this.VKI_symbol[lkey[0]]));
					td.appendChild(span);
				} else td.appendChild(document.createTextNode(lkey[0] || "\xa0"));

				var className = [];
				if (this.VKI_deadkeysOn)
				  for (key in this.VKI_deadkey)
					if (key === lkey[0]) { className.push("alive"); break; }
				if (lyt.length > this.VKI_keyCenter && y == lyt.length) className.push("last");
				if (lkey[0] == " ") className.push("space");
				  td.className = className.join(" ");

				  td.VKI_clickless = 0;
				  if (!td.click) {
					td.click = function() {
					  var evt = this.ownerDocument.createEvent('MouseEvents');
					  evt.initMouseEvent('click', true, true, this.ownerDocument.defaultView, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
					  this.dispatchEvent(evt);
					};
				  }
				  td.onmouseover = function() {
					if (self.VKI_clickless) {
					  var _class = this;
					  clearTimeout(this.VKI_clickless);
					  this.VKI_clickless = setTimeout(function() { _class.click(); }, self.VKI_clickless);
					}
					if ((this.firstChild.nodeValue || this.firstChild.className) != "\xa0") this.className += " hover";
				  };
				  td.onmouseout = function() {
					if (self.VKI_clickless) clearTimeout(this.VKI_clickless);
					this.className = this.className.replace(/ ?(hover|pressed)/g, "");
				  };
				  td.onmousedown = function() {
					if (self.VKI_clickless) clearTimeout(this.VKI_clickless);
					if ((this.firstChild.nodeValue || this.firstChild.className) != "\xa0") this.className += " pressed";
				  };
				  td.onmouseup = function() {
					if (self.VKI_clickless) clearTimeout(this.VKI_clickless);
					this.className = this.className.replace(/ ?pressed/g, "");
				  };
				  td.ondblclick = function() { return false; };

				switch (lkey[1]) {
				  case "Caps": case "Shift":
				  case "Alt": case "AltGr": case "AltLk":
					td.onclick = (function(type) { return function() { self.VKI_modify(type); return false; }; })(lkey[1]);
					break;
				  case "Tab":
					td.onclick = function() { self.VKI_insert("\t"); return false; };
					break;
				  case "Bksp":
					td.onclick = function() {
					  self.VKI_target.focus();
					  if (self.VKI_target.setSelectionRange) {
						if (self.VKI_target.readOnly && self.VKI_isWebKit) {
						  var rng = [self.VKI_target.selStart || 0, self.VKI_target.selEnd || 0];
						} else var rng = [self.VKI_target.selectionStart, self.VKI_target.selectionEnd];
						if (rng[0] < rng[1]) rng[0]++;
						self.VKI_target.value = self.VKI_target.value.substr(0, rng[0] - 1) + self.VKI_target.value.substr(rng[1]);
						self.VKI_target.setSelectionRange(rng[0] - 1, rng[0] - 1);
						if (self.VKI_target.readOnly && self.VKI_isWebKit) {
						  var range = window.getSelection().getRangeAt(0);
						  self.VKI_target.selStart = range.startOffset;
						  self.VKI_target.selEnd = range.endOffset;
						}
					  } else if (self.VKI_target.createTextRange) {
						try {
						  self.VKI_target.range.select();
						} catch(e) { self.VKI_target.range = document.selection.createRange(); }
						if (!self.VKI_target.range.text.length) self.VKI_target.range.moveStart('character', -1);
						self.VKI_target.range.text = "";
					  } else self.VKI_target.value = self.VKI_target.value.substr(0, self.VKI_target.value.length - 1);
					  if (self.VKI_shift) self.VKI_modify("Shift");
					  if (self.VKI_altgr) self.VKI_modify("AltGr");
					  self.VKI_target.focus();
					  return true;
					};
					break;
				  case "Enter":
					td.onclick = function() {
					  if (self.VKI_target.nodeName != "TEXTAREA") {
						self.VKI_close();
						this.className = this.className.replace(/ ?(hover|pressed)/g, "");
					  } else self.VKI_insert("\n");
					  return true;
					};
					break;
				  default:
					td.onclick = function() {
					  var character = this.firstChild.nodeValue || this.firstChild.className;
					  if (self.VKI_deadkeysOn && self.VKI_dead) {
						if (self.VKI_dead != character) {
						  for (key in self.VKI_deadkey) {
							if (key == self.VKI_dead) {
							  if (character != " ") {
								for (var z = 0, rezzed = false, dk; dk = self.VKI_deadkey[key][z++];) {
								  if (dk[0] == character) {
									self.VKI_insert(dk[1]);
									rezzed = true;
									break;
								  }
								}
							  } else {
								self.VKI_insert(self.VKI_dead);
								rezzed = true;
							  } break;
							}
						  }
						} else rezzed = true;
					  } self.VKI_dead = false;

					  if (!rezzed && character != "\xa0") {
						if (self.VKI_deadkeysOn) {
						  for (key in self.VKI_deadkey) {
							if (key == character) {
							  self.VKI_dead = key;
							  this.className += " dead";
							  if (self.VKI_shift) self.VKI_modify("Shift");
							  if (self.VKI_altgr) self.VKI_modify("AltGr");
							  break;
							}
						  }
						  if (!self.VKI_dead) self.VKI_insert(character);
						} else self.VKI_insert(character);
					  }

					  self.VKI_modify("");
					  if (self.VKI_isOpera) {
						this.style.width = "50px";
						var foo = this.offsetWidth;
						this.style.width = "";
					  }
					  return false;
					};

				}
				tr.appendChild(td);
			  tbody.appendChild(tr);
			table.appendChild(tbody);

			for (var z = 0; z < 4; z++)
			  if (this.VKI_deadkey[lkey[z] = lkey[z] || "\xa0"]) hasDeadKey = true;
		}
		container.appendChild(table);
	  }
	  //this.VKI_deadkeysElem.style.display = (!this.VKI_layout[this.VKI_kt].DDK && hasDeadKey) ? "inline" : "none";
	};

	this.VKI_buildKeys();
	VKI_disableSelection(this.VKI_keyboard);


	/* ****************************************************************
	 * Controls modifier keys
	 *
	 */
	this.VKI_modify = function(type) {
	  switch (type) {
		case "Alt":
		case "AltGr": this.VKI_altgr = !this.VKI_altgr; break;
		case "AltLk": this.VKI_altgrlock = !this.VKI_altgrlock; break;
		case "Caps": this.VKI_shiftlock = !this.VKI_shiftlock; break;
		case "Shift": this.VKI_shift = !this.VKI_shift; break;
	  } var vchar = 0;
	  if (!this.VKI_shift != !this.VKI_shiftlock) vchar += 1;
	  if (!this.VKI_altgr != !this.VKI_altgrlock) vchar += 2;

	  var tables = this.VKI_keyboard.getElementsByTagName('table');
	  for (var x = 0; x < tables.length; x++) {
		var tds = tables[x].getElementsByTagName('td');
		for (var y = 0; y < tds.length; y++) {
		  var className = [], lkey = this.VKI_layout[this.VKI_kt][x][y];

		  if (tds[y].className.indexOf('hover') > -1) className.push("hover");

		  switch (lkey[1]) {
			case "Alt":
			case "AltGr":
			  if (this.VKI_altgr) className.push("dead");
			  break;
			case "AltLk":
			  if (this.VKI_altgrlock) className.push("dead");
			  break;
			case "Shift":
			  if (this.VKI_shift) className.push("dead");
			  break;
			case "Caps":
			  if (this.VKI_shiftlock) className.push("dead");
			  break;
			case "Tab": case "Enter": case "Bksp": break;
			default:
			  if (type) {
				tds[y].removeChild(tds[y].firstChild);
				if (this.VKI_symbol[lkey[vchar]]) {
				  var span = document.createElement('span');
					  span.className = lkey[vchar];
					  span.appendChild(document.createTextNode(this.VKI_symbol[lkey[vchar]]));
					tds[y].appendChild(span);
				} else tds[y].appendChild(document.createTextNode(lkey[vchar]));
			  }
			  if (this.VKI_deadkeysOn) {
				var character = tds[y].firstChild.nodeValue || tds[y].firstChild.className;
				if (this.VKI_dead) {
				  if (character == this.VKI_dead) className.push("dead");
				  for (var z = 0; z < this.VKI_deadkey[this.VKI_dead].length; z++) {
					if (character == this.VKI_deadkey[this.VKI_dead][z][0]) {
					  className.push("target");
					  break;
					}
				  }
				}
				for (key in this.VKI_deadkey)
				  if (key === character) { className.push("alive"); break; }
			  }
		  }

		  if (y == tds.length - 1 && tds.length > this.VKI_keyCenter) className.push("last");
		  if (lkey[0] == " ") className.push("space");
		  tds[y].className = className.join(" ");
		}
	  }
	};


	/* ****************************************************************
	 * Insert text at the cursor
	 *
	 */
	this.VKI_insert = function(text) {
	  this.VKI_target.focus();
	  if (this.VKI_target.maxLength) this.VKI_target.maxlength = this.VKI_target.maxLength;
	  if (typeof this.VKI_target.maxlength == "undefined" ||
		  this.VKI_target.maxlength < 0 ||
		  this.VKI_target.value.length < this.VKI_target.maxlength) {
		if (this.VKI_target.setSelectionRange) {
		  if (this.VKI_target.readOnly && this.VKI_isWebKit) {
			var rng = [this.VKI_target.selStart || 0, this.VKI_target.selEnd || 0];
		  } else var rng = [this.VKI_target.selectionStart, this.VKI_target.selectionEnd];
		  this.VKI_target.value = this.VKI_target.value.substr(0, rng[0]) + text + this.VKI_target.value.substr(rng[1]);
		  if (text == "\n" && window.opera) rng[0]++;
		  this.VKI_target.setSelectionRange(rng[0] + text.length, rng[0] + text.length);
		  if (this.VKI_target.readOnly && this.VKI_isWebKit) {
			var range = window.getSelection().getRangeAt(0);
			this.VKI_target.selStart = range.startOffset;
			this.VKI_target.selEnd = range.endOffset;
		  }
		} else if (this.VKI_target.createTextRange) {
		  try {
			this.VKI_target.range.select();
		  } catch(e) { this.VKI_target.range = document.selection.createRange(); }
		  this.VKI_target.range.text = text;
		  this.VKI_target.range.collapse(true);
		  this.VKI_target.range.select();
		} else this.VKI_target.value += text;
		if (this.VKI_shift) this.VKI_modify("Shift");
		if (this.VKI_altgr) this.VKI_modify("AltGr");
		this.VKI_target.focus();
	  } else if (this.VKI_target.createTextRange && this.VKI_target.range)
		this.VKI_target.range.select();
	};


	/* ****************************************************************
	 * Show the keyboard interface
	 *
	 */
	this.VKI_show = function(elem) {
	  if (!this.VKI_target) {
		this.VKI_target = elem;
		if (this.VKI_langAdapt && this.VKI_target.lang) {
		  var chg = false, sub = [];
		  for (ktype in this.VKI_layout) {
			if (typeof this.VKI_layout[ktype] == "object") {
			  for (var x = 0; x < this.VKI_layout[ktype].lang.length; x++) {
				if (this.VKI_layout[ktype].lang[x].toLowerCase() == this.VKI_target.lang.toLowerCase()) {
				  chg = kblist.value = this.VKI_kt = ktype;
				  break;
				} else if (this.VKI_layout[ktype].lang[x].toLowerCase().indexOf(this.VKI_target.lang.toLowerCase()) == 0)
				  sub.push([this.VKI_layout[ktype].lang[x], ktype]);
			  }
			} if (chg) break;
		  } if (sub.length) {
			sub.sort(function (a, b) { return a[0].length - b[0].length; });
			chg = kblist.value = this.VKI_kt = sub[0][1];
		  } if (chg) this.VKI_buildKeys();
		}
		if (this.VKI_isIE) {
		  if (!this.VKI_target.range) {
			this.VKI_target.range = this.VKI_target.createTextRange();
			this.VKI_target.range.moveStart('character', this.VKI_target.value.length);
		  } this.VKI_target.range.select();
		}
		try { this.VKI_keyboard.parentNode.removeChild(this.VKI_keyboard); } catch (e) {}
		if (this.VKI_clearPasswords && this.VKI_target.type == "password") this.VKI_target.value = "";

		var elem = this.VKI_target;
		this.VKI_target.keyboardPosition = "absolute";
		do {
		  if (VKI_getStyle(elem, "position") == "fixed") {
			this.VKI_target.keyboardPosition = "fixed";
			break;
		  }
		} while (elem = elem.offsetParent);

		document.body.appendChild(this.VKI_keyboard);
		this.VKI_keyboard.style.position = this.VKI_target.keyboardPosition;
		if (this.VKI_isOpera) kblist.value = this.VKI_kt;

		this.VKI_position(true);
		if (self.VKI_isMoz || self.VKI_isWebKit) this.VKI_position(true);
		this.VKI_target.focus();
	  } else this.VKI_close();
	};


	/* ****************************************************************
	 * Position the keyboard
	 *
	 */
	this.VKI_position = function(force) {
	  if (self.VKI_target) {
		var kPos = VKI_findPos(self.VKI_keyboard), wDim = VKI_innerDimensions(), sDis = VKI_scrollDist();
		var place = false, fudge = self.VKI_target.offsetHeight + 3;
		if (force !== true) {
		  if (kPos[1] + self.VKI_keyboard.offsetHeight - sDis[1] - wDim[1] > 0) {
			place = true;
			fudge = -self.VKI_keyboard.offsetHeight - 3;
		  } else if (kPos[1] - sDis[1] < 0) place = true;
		}
		if (place || force === true) {
		  var iPos = VKI_findPos(self.VKI_target);
		  self.VKI_keyboard.style.top = iPos[1] - ((self.VKI_target.keyboardPosition == "fixed" && !self.VKI_isIE && !self.VKI_isMoz) ? sDis[1] : 0) + fudge + "px";
		  self.VKI_keyboard.style.left = Math.max(10, Math.min(wDim[0] - self.VKI_keyboard.offsetWidth - 25, iPos[0])) + "px";
		}
		if (force === true) self.VKI_position();
	  }
	};


	if(window.addEventListener) {
		window.addEventListener('resize', this.VKI_position, false);
		window.addEventListener('scroll', this.VKI_position, false);
	} else if (window.attachEvent) {
		window.attachEvent('onresize', this.VKI_position);
		window.attachEvent('onscroll', this.VKI_position);
	}


	/* ****************************************************************
	 * Close the keyboard interface
	 *
	 */
	this.VKI_close = VKI_close = function() {
	  if (this.VKI_target) {
		try {
		  this.VKI_keyboard.parentNode.removeChild(this.VKI_keyboard);
		} catch (e) {}
		if (this.VKI_kt != this.VKI_kts) {
		  kblist.value = this.VKI_kt = this.VKI_kts;
		  this.VKI_buildKeys();
		}
		this.VKI_target.focus();
		this.VKI_target = false;
	  }
	};

} //END FUNCTION


function VKI_findPos(obj) {
	var curleft = curtop = 0;
	do {
		curleft += obj.offsetLeft;
		curtop += obj.offsetTop;
	} while (obj = obj.offsetParent);
	return [curleft, curtop];
} //END FUNCTION


function VKI_innerDimensions() {
	if(self.innerHeight) {
		return [self.innerWidth, self.innerHeight];
	} else if (document.documentElement && document.documentElement.clientHeight) {
		return [document.documentElement.clientWidth, document.documentElement.clientHeight];
	} else if (document.body)
		return [document.body.clientWidth, document.body.clientHeight];
	return [0, 0];
} //END FUNCTION


function VKI_scrollDist() {
	var html = document.getElementsByTagName('html')[0];
	if(html.scrollTop && document.documentElement.scrollTop) {
		return [html.scrollLeft, html.scrollTop];
	} else if (html.scrollTop || document.documentElement.scrollTop) {
		return [html.scrollLeft + document.documentElement.scrollLeft, html.scrollTop + document.documentElement.scrollTop];
	} else if (document.body.scrollTop)
		return [document.body.scrollLeft, document.body.scrollTop];
	return [0, 0];
} //END FUNCTION


function VKI_getStyle(obj, styleProp) {
	if (obj.currentStyle) {
		var y = obj.currentStyle[styleProp];
	} else if (window.getComputedStyle)
		var y = window.getComputedStyle(obj, null)[styleProp];
	return y;
} //END FUNCTION


function VKI_disableSelection(elem) {
	elem.onselectstart = function() { return false; };
	elem.unselectable = "on";
	elem.style.MozUserSelect = "none";
	elem.style.cursor = "default";
	if(window.opera) {
		elem.onmousedown = function() { return false; };
	}
} //END FUNCTION


} // END CLASS

/* END */
