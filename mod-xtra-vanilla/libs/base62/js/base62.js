
// https://codegolf.stackexchange.com/questions/1620/arbitrary-base-conversion/21672#21672
// https://stackoverflow.com/questions/40100096/what-is-equivalent-php-chr-and-ord-functions-in-javascript

function baseConvert(number, src_base, dst_base) {
	var res = [];
	var quotient;
	var remainder;
	while (number.length) {
		// divide successive powers of dst_base
		quotient = [];
		remainder = 0;
		var len = number.length;
		for (var i = 0 ; i != len ; i++) {
			var accumulator = number[i] + remainder * src_base;
			var digit = accumulator / dst_base | 0; // rounding faster than Math.floor
			remainder = accumulator % dst_base;
			if (quotient.length || digit) quotient.push(digit);
		}
		// the remainder of current division is the next rightmost digit
		res.unshift(remainder);
		// rinse and repeat with next power of dst_base
		number = quotient;
	}
	return res;
}

// #END

