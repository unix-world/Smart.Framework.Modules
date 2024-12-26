
// [LIB - Smart.Framework / JS / LZS Archiver]
// (c) 2006-2021 unix-world.org - all rights reserved

// DEPENDS: smartJ$Utils

//==================================================================
// based on LZString v.1.3.6 (a free LZ based compression algorithm)
// this is intended for on-the-fly archive/unarchive not for storing (where ZLib is a better option)
// it compatible with SmartFramework/PHP/SmartArchiverLZS
// License: BSD
// (c) 2013-2019 iradu@unix-world.org : optimizations, fixes, unicode safe
// Original work by (c) 2013 Pieroxy under the WTFPL License v2
//==================================================================

//================== [NO:evcode]

/**
 * CLASS :: LZS Archive
 * Compress or Decompress a LZS archive
 * This is very slow with large strings ... extremely slow !!!
 * This is why the max (hardcoded) length of the string it can compress/decompress is 4096 bytes
 * The purpose of this class is to compress/decompress cookies that can be shared also with PHP
 * The PHP version of this class is available in modules/mod-js-components/libs/ArchLzs.php
 *
 * @package modules.Javascript:Archivers
 *
 * @requires		smartJ$Utils
 * @requires		smartJ$CryptoHash
 *
 * @desc LZS Archiver for JavaScript :: compress on-the-fly a string using only Javascript with the LZS algorithm
 * @author unix-world.org
 * @license BSD
 * @file arch_utils.js
 * @version 20210526
 * @class ArchLzs
 * @static
 *
 * @example
 * var plainStr = 'Some String';
 * var archivedStr = ArchLzs.compressToBase64(plainStr);
 * var unArchivedStr = ArchLzs.decompressFromBase64(archivedStr);
 * if(plainStr !== unArchivedStr) {
 * 	alert('Javascript LZS Archiver Failed: Plain String is different after Archive/Unarchive operations !');
 * }
 * console.log(plainStr, archivedStr, unArchivedStr);
 */
var ArchLzs = new function() { // START CLASS

	// :: static

	var _class = this; // self referencing

	// lz priv props
	var _f = String.fromCharCode;

	// b64 priv props
	var _keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';


	/**
	 * Compress a string to LZS + Base64
	 *
	 * @memberof ArchLzs
	 * @method compressToBase64
	 * @static
	 *
	 * @param {String} input The original string to be compressed
	 * @return {String} The compressed string LZS + Base64
	 */
	this.compressToBase64 = function(input) {
		//--
		if((typeof input == 'undefined') || (input == '') || (input == null)) {
			return '';
		} //end if
		//--
		input = String(input);
		//--
		input = _class.compressRawLZS(input); // make it unicode safe
		//--
		var i = 0;
		var output = '';
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		//--
		while(i < (input.length * 2)) {
			//--
			if(i%2 == 0) {
				//--
				chr1 = input.charCodeAt(i/2) >> 8;
				chr2 = input.charCodeAt(i/2) & 255;
				//--
				if(i/2+1 < input.length) {
					chr3 = input.charCodeAt(i/2 + 1) >> 8;
				} else {
					chr3 = NaN;
				} //end if else
			} else {
				//--
				chr1 = input.charCodeAt((i-1) / 2) & 255;
				//--
				if((i+1)/2 < input.length) {
					chr2 = input.charCodeAt((i+1) / 2) >> 8;
					chr3 = input.charCodeAt((i+1) / 2) & 255;
				} else {
					chr2 = chr3 = NaN;
				} //end if else
				//--
			} //end if else
			//--
			i+=3;
			//--
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			//--
			if(isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if(isNaN(chr3)) {
				enc4 = 64;
			} //end if else
			//--
			output = output + _keyStr.charAt(enc1) + _keyStr.charAt(enc2) + _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
			//--
		} //end while
		//--
		return String(output);
		//--
	} //END FUNCTION


	/**
	 * Decompress from Base64 + LZS
	 *
	 * @memberof ArchLzs
	 * @method decompressFromBase64
	 * @static
	 *
	 * @param {String} input The LZS + Base64 compressed string
	 * @return {String} The uncompressed (original) string
	 */
	this.decompressFromBase64 = function(input) {
		//--
		if((typeof input == 'undefined') || (input == '') || (input == null)) {
			return '';
		} //end if
		//--
		input = String(input);
		//--
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, ''); // remove invalid non-b64 chars
		//--
		var f = _f;
		var i = 0, ol = 0;
		var	output = '', output_;
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		//--
		while(i < input.length) {
			//--
			enc1 = _keyStr.indexOf(input.charAt(i++));
			enc2 = _keyStr.indexOf(input.charAt(i++));
			enc3 = _keyStr.indexOf(input.charAt(i++));
			enc4 = _keyStr.indexOf(input.charAt(i++));
			//--
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			//--
			if((ol % 2) == 0) {
				//--
				output_ = chr1 << 8;
				//--
				if(enc3 != 64) {
					output += f(output_ | chr2);
				} //end if
				if(enc4 != 64) {
					output_ = chr3 << 8;
				} //end if
				//--
			} else {
				//--
				output = output + f(output_ | chr1);
				//--
				if(enc3 != 64) {
					output_ = chr2 << 8;
				} //end if
				if(enc4 != 64) {
					output += f(output_ | chr3);
				} //end if
				//--
			} //end if else
			//--
			ol+=3;
			//--
		} //end while
		//--
		return String(_class.decompressRawLZS(output));
		//--
	} //END FUNCTION


	/*
	 * Compress a string to LZS
	 *
	 * @private internal development only
	 *
	 * @memberof ArchLzs
	 * @method compressRawLZS
	 * @static
	 *
	 * @param {String} input The original string to be compressed
	 * @return {String} The compressed string LZS
	 */
	this.compressRawLZS = function(uncompressed) {
		//--
		if((typeof uncompressed == 'undefined') || (uncompressed == '') || (uncompressed == null)) {
			return '';
		} //end if
		//--
		uncompressed = String(uncompressed);
		if(uncompressed.length > 4096) {
			console.error('ArchLzs # Compressing a string with a length of more than 4096 bytes is not supported');
			return '';
		} //end if
		//--
		var arch = smartJ$Utils.bin2hex(uncompressed);
		//--
		return String(RawDeflate(String(arch + '#CHECKSUM-SHA1#' + smartJ$CryptoHash.sha1(arch))));
		//--
	} //END FUNCTION


	/*
	 * Decompress from LZS
	 *
	 * @private internal development only
	 *
	 * @memberof ArchLzs
	 * @method decompressRawLZS
	 * @static
	 *
	 * @param {String} input The LZS compressed string
	 * @return {String} The uncompressed (original) string
	 */
	this.decompressRawLZS = function(compressed) {
		//--
		if((typeof compressed == 'undefined') || (compressed == null) || (compressed == '')) {
			return '';
		} //end if
		//--
		compressed = String(compressed);
		if(compressed.length > 4096) {
			console.error('ArchLzs # Decompressing a string with a length of more than 4096 bytes is not supported');
			return '';
		} //end if
		//--
		var unarch = String(smartJ$Utils.stringTrim(RawInflate(String(compressed))));
		var parts = unarch.split('#CHECKSUM-SHA1#');
		unarch = smartJ$Utils.stringTrim(String(parts[0]));
		var checksum = smartJ$Utils.stringTrim(String(parts[1]));
		//--
		if(smartJ$CryptoHash.sha1(unarch) !== (String(checksum))) {
			console.error('JS-LZS Archiver / Decompress: Checksum Failed'); // do not raise error just alert
			return '';
		} //end if
		//--
		return String(smartJ$Utils.hex2bin(unarch));
		//--
	} //END FUNCTION


	//=============== PRIVATES


	var RawDeflate = function(uncompressed) {
		//--
		if((typeof uncompressed == 'undefined') || (uncompressed == '') || (uncompressed == null)) {
			return '';
		} //end if
		//--
		var f = _f;
		var i, ii, value;
		var	context_dictionary = {},
			context_dictionaryToCreate = {},
			context_c = '',
			context_wc = '',
			context_w = '',
			context_enlargeIn = 2, // compensate for the first entry which should not count
			context_dictSize = 3,
			context_numBits = 2,
			context_data_string = '',
			context_data_val = 0,
			context_data_position = 0;
		//--
		for(ii = 0; ii < uncompressed.length; ii += 1) {
			//--
			context_c = uncompressed.charAt(ii);
			//--
			if(!Object.prototype.hasOwnProperty.call(context_dictionary,context_c)) {
				context_dictionary[context_c] = context_dictSize++;
				context_dictionaryToCreate[context_c] = true;
			} //end if
			//--
			context_wc = context_w + context_c;
			//--
			if(Object.prototype.hasOwnProperty.call(context_dictionary,context_wc)) {
				//--
				context_w = context_wc;
				//--
			} else {
				//--
				if(Object.prototype.hasOwnProperty.call(context_dictionaryToCreate,context_w)) {
					//--
					if(context_w.charCodeAt(0) < 256) {
						//--
						for(i=0 ; i<context_numBits ; i++) {
							context_data_val = (context_data_val << 1);
							if(context_data_position == 15) {
								context_data_position = 0;
								context_data_string += f(context_data_val);
								context_data_val = 0;
							} else {
								context_data_position++;
							} //end if else
						} //end for
						//--
						value = context_w.charCodeAt(0);
						//--
						for(i=0 ; i<8 ; i++) {
							context_data_val = (context_data_val << 1) | (value&1);
							if(context_data_position == 15) {
								context_data_position = 0;
								context_data_string += f(context_data_val);
								context_data_val = 0;
							} else {
								context_data_position++;
							} //end if else
							value = value >> 1;
						} //end for
						//--
					} else {
						//--
						value = 1;
						//--
						for(i=0 ; i<context_numBits ; i++) {
							context_data_val = (context_data_val << 1) | value;
							if(context_data_position == 15) {
								context_data_position = 0;
								context_data_string += f(context_data_val);
								context_data_val = 0;
							} else {
								context_data_position++;
							} //end if else
							value = 0;
						} //end for
						//--
						value = context_w.charCodeAt(0);
						//--
						for(i=0 ; i<16 ; i++) {
							//--
							context_data_val = (context_data_val << 1) | (value&1);
							//--
							if(context_data_position == 15) {
								context_data_position = 0;
								context_data_string += f(context_data_val);
								context_data_val = 0;
							} else {
								context_data_position++;
							} //end if else
							//--
							value = value >> 1;
							//--
						} //end for
						//--
					} //end if else
					//--
					context_enlargeIn--;
					//--
					if(context_enlargeIn == 0) {
						context_enlargeIn = Math.pow(2, context_numBits);
						context_numBits++;
					} //end if
					//--
					delete context_dictionaryToCreate[context_w];
					//--
				} else {
					//--
					value = context_dictionary[context_w];
					//--
					for(i=0 ; i<context_numBits ; i++) {
						//--
						context_data_val = (context_data_val << 1) | (value&1);
						//--
						if(context_data_position == 15) {
							context_data_position = 0;
							context_data_string += f(context_data_val);
							context_data_val = 0;
						} else {
							context_data_position++;
						} //end if else
						//--
						value = value >> 1;
						//--
					} //end for
					//--
				} //end if else
				//--
				context_enlargeIn--;
				//--
				if(context_enlargeIn == 0) {
					context_enlargeIn = Math.pow(2, context_numBits);
					context_numBits++;
				} //end if
				//-- Add wc to the dictionary.
				context_dictionary[context_wc] = context_dictSize++;
				context_w = String(context_c);
				//--
			} //end if else
			//--
		} //end for
		//-- output the code for w
		if(context_w !== '') {
			//--
			if(Object.prototype.hasOwnProperty.call(context_dictionaryToCreate,context_w)) {
				//--
				if(context_w.charCodeAt(0) < 256) {
					//--
					for(i=0 ; i<context_numBits ; i++) {
						//--
						context_data_val = (context_data_val << 1);
						//--
						if(context_data_position == 15) {
							context_data_position = 0;
							context_data_string += f(context_data_val);
							context_data_val = 0;
						} else {
							context_data_position++;
						} //end if else
						//--
					} //end for
					//--
					value = context_w.charCodeAt(0);
					//--
					for(i=0 ; i<8 ; i++) {
						//--
						context_data_val = (context_data_val << 1) | (value&1);
						//--
						if(context_data_position == 15) {
							context_data_position = 0;
							context_data_string += f(context_data_val);
							context_data_val = 0;
						} else {
							context_data_position++;
						} //end if else
						//--
						value = value >> 1;
						//--
					} //end for
					//--
				} else {
					//--
					value = 1;
					//--
					for(i=0 ; i<context_numBits ; i++) {
						//--
						context_data_val = (context_data_val << 1) | value;
						//--
						if(context_data_position == 15) {
							context_data_position = 0;
							context_data_string += f(context_data_val);
							context_data_val = 0;
						} else {
							context_data_position++;
						} //end if else
						//--
						value = 0;
						//--
					} //end for
					//--
					value = context_w.charCodeAt(0);
					//--
					for(i=0 ; i<16 ; i++) {
						//--
						context_data_val = (context_data_val << 1) | (value&1);
						//--
						if(context_data_position == 15) {
							context_data_position = 0;
							context_data_string += f(context_data_val);
							context_data_val = 0;
						} else {
							context_data_position++;
						} //end if else
						//--
						value = value >> 1;
						//--
					} //end for
					//--
				} //end if else
				//--
				context_enlargeIn--;
				//--
				if(context_enlargeIn == 0) {
					context_enlargeIn = Math.pow(2, context_numBits);
					context_numBits++;
				} //end if
				//--
				delete context_dictionaryToCreate[context_w];
				//--
			} else {
				//--
				value = context_dictionary[context_w];
				//--
				for(i=0 ; i<context_numBits ; i++) {
					//--
					context_data_val = (context_data_val << 1) | (value&1);
					//--
					if(context_data_position == 15) {
						context_data_position = 0;
						context_data_string += f(context_data_val);
						context_data_val = 0;
					} else {
						context_data_position++;
					} //end if else
					//--
					value = value >> 1;
					//--
				} //end for
				//--
			} //end if else
			//--
			context_enlargeIn--;
			//--
			if(context_enlargeIn == 0) {
				context_enlargeIn = Math.pow(2, context_numBits);
				context_numBits++;
			} //end if
			//--
		} //end if else
		//-- mark the end of the stream
		value = 2;
		//--
		for(i=0 ; i<context_numBits ; i++) {
			//--
			context_data_val = (context_data_val << 1) | (value&1);
			//--
			if(context_data_position == 15) {
				context_data_position = 0;
				context_data_string += f(context_data_val);
				context_data_val = 0;
			} else {
				context_data_position++;
			} //end if else
			//--
			value = value >> 1;
			//--
		} //end for
		//-- flush the last char
		while(true) {
			//--
			context_data_val = (context_data_val << 1);
			//--
			if(context_data_position == 15) {
				context_data_string += f(context_data_val);
				break;
			} else {
				context_data_position++;
			} //end if else
			//--
		} //end while
		//--
		return context_data_string;
		//--
	} //END FUNCTION


	var RawInflate = function(compressed) {
		//--
		if((typeof compressed == 'undefined') || (compressed == null)) {
			return '';
		} //end if
		if(compressed == '') {
			return null; // this is a special case
		} //end if
		//--
		var f = _f;
		var i, w, c;
		var dictionary = [],
			next,
			enlargeIn = 4,
			dictSize = 4,
			numBits = 3,
			entry = '',
			result = '',
			bits, resb, maxpower, power;
		var data = {
			string:compressed,
			val:compressed.charCodeAt(0),
			position:32768,
			index:1
		};
		//--
		for(i=0; i<3; i+=1) {
		  dictionary[i] = i;
		} //end for
		//--
		bits = 0;
		maxpower = Math.pow(2,2);
		power=1;
		//--
		while(power != maxpower) {
			resb = data.val & data.position;
			data.position >>= 1;
			if(data.position == 0) {
				data.position = 32768;
				data.val = data.string.charCodeAt(data.index++);
			} //end if
			bits |= (resb>0 ? 1 : 0) * power;
			power <<= 1;
		} //end while
		//--
		switch(next = bits) {
			case 0:
				//--
				bits = 0;
				maxpower = Math.pow(2,8);
				power = 1;
				//--
				while(power != maxpower) {
					resb = data.val & data.position;
					data.position >>= 1;
					if(data.position == 0) {
						data.position = 32768;
						data.val = data.string.charCodeAt(data.index++);
					} //end if
					bits |= (resb>0 ? 1 : 0) * power;
					power <<= 1;
				} //end while
				//--
				c = f(bits);
				//--
				break;
			case 1:
				//--
				bits = 0;
				maxpower = Math.pow(2,16);
				power = 1;
				//--
				while(power != maxpower) {
					resb = data.val & data.position;
					data.position >>= 1;
					if(data.position == 0) {
						data.position = 32768;
						data.val = data.string.charCodeAt(data.index++);
					} //end if
					bits |= (resb>0 ? 1 : 0) * power;
					power <<= 1;
				} //end while
				//--
				c = f(bits);
				//--
				break;
			case 2:
				//--
				return '';
				//--
		} //end switch
		//--
		dictionary[3] = c;
		w = result = c;
		//--
		while(true) {
			//--
			if(data.index > data.string.length) {
				return '';
			} //end if
			//--
			bits = 0;
			maxpower = Math.pow(2,numBits);
			power = 1;
			//--
			while(power != maxpower) {
				resb = data.val & data.position;
				data.position >>= 1;
				if(data.position == 0) {
					data.position = 32768;
					data.val = data.string.charCodeAt(data.index++);
				} //end if
				bits |= (resb>0 ? 1 : 0) * power;
				power <<= 1;
			} //end while
			//--
			switch(c = bits) {
				case 0:
					//--
					bits = 0;
					maxpower = Math.pow(2,8);
					power = 1;
					//--
					while(power != maxpower) {
						resb = data.val & data.position;
						data.position >>= 1;
						if(data.position == 0) {
							data.position = 32768;
							data.val = data.string.charCodeAt(data.index++);
						} //end if
						bits |= (resb>0 ? 1 : 0) * power;
						power <<= 1;
					} //end while
					//--
					dictionary[dictSize++] = f(bits);
					c = dictSize-1;
					enlargeIn--;
					//--
					break;
				case 1:
					//--
					bits = 0;
					maxpower = Math.pow(2,16);
					power = 1;
					//--
					while(power != maxpower) {
						resb = data.val & data.position;
						data.position >>= 1;
						if(data.position == 0) {
							data.position = 32768;
							data.val = data.string.charCodeAt(data.index++);
						} //end if
						bits |= (resb>0 ? 1 : 0) * power;
						power <<= 1;
					} //end while
					//--
					dictionary[dictSize++] = f(bits);
					c = dictSize-1;
					enlargeIn--;
					//--
					break;
				case 2:
					//--
					return result;
					//--
			} //end switch
			//--
			if(enlargeIn == 0) {
				enlargeIn = Math.pow(2, numBits);
				numBits++;
			} //end if
			//--
			if(dictionary[c]) {
				entry = dictionary[c];
			} else {
				if(c === dictSize) {
					entry = w + w.charAt(0);
				} else {
					return null;
				} //end if else
			} //end if else
			//--
			result += entry;
			//-- add w+entry[0] to the dictionary
			dictionary[dictSize++] = w + entry.charAt(0);
			enlargeIn--;
			//--
			w = entry;
			//--
			if(enlargeIn == 0) {
				enlargeIn = Math.pow(2, numBits);
				numBits++;
			} //end if
			//--
		} //end while
		//--
	} //END FUNCTION


} //END CLASS

//==================================================================
//==================================================================

//#END
