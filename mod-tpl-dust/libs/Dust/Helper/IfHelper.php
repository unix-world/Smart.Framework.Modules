<?php
// modified by unixman to avoid using eval()
namespace Dust\Helper;

use Dust\Evaluate;
class IfHelper {
	public function __invoke(Evaluate\Chunk $chunk, Evaluate\Context $context, Evaluate\Bodies $bodies) {
		$lexpr = (string) $context->get('lexpr');
		$rexpr = (string) $context->get('rexpr');
		$operator = (string) trim((string)$context->get('operator'));
		switch((string)$operator) {
			//-- arrays
			case '@==': // array(lexpr) count ==
				if(\Smart::array_size($lexpr) == (int)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '@!=': // array(lexpr) count !=
				if(\Smart::array_size($lexpr) != (int)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '@<=': // array(lexpr) count <=
				if(\Smart::array_size($lexpr) <= (int)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '@<': // array(lexpr) count <
				if(\Smart::array_size($lexpr) < (int)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '@>=': // array(lexpr) count >=
				if(\Smart::array_size($lexpr) >= (int)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '@>': // array(lexpr) count >
				if(\Smart::array_size($lexpr) > (int)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			//-- numbers
			case '==':
				if((string)$lexpr == (string)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '!=':
				if((string)$lexpr != (string)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '<=':
				if((float)$lexpr <= (float)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '<':
				if((float)$lexpr < (float)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '>=':
				if((float)$lexpr >= (float)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '>':
				if((float)$lexpr > (float)$rexpr) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '%':
				if(((int)$lexpr % (int)$rexpr) == 0) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '!%':
				if(((int)$lexpr % (int)$rexpr) != 0) {
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			//-- string lists
			case '?': // in list(rexpr)='a|b|c' the key lexpr
				$tmp_compare_arr = (array) \explode('|', (string)$rexpr);
				if(\in_array((string)$lexpr, (array)$tmp_compare_arr)) { // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				$tmp_compare_arr = array();
				break; //--
			case '!?': // not in list(rexpr)='a|b|c' the key lexpr
				$tmp_compare_arr = (array) \explode('|', (string)$rexpr);
				if(!\in_array((string)$lexpr, (array)$tmp_compare_arr)) { // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				$tmp_compare_arr = array();
				break; //--
			//-- strings
			case '^~': // if lexpr starts with rexpr, case sensitive
				if(\SmartUnicode::str_pos((string)$lexpr, (string)$rexpr) === 0) { // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '^*': // if lexpr starts with rexpr, case insensitive
				if(\SmartUnicode::str_ipos((string)$lexpr, (string)$rexpr) === 0) { // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '&~': // if variable contains part, case sensitive
				if(\SmartUnicode::str_contains((string)$lexpr, (string)$rexpr)) { // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '&*': // if variable contains part, case insensitive
				if(\SmartUnicode::str_icontains((string)$lexpr, (string)$rexpr)) { // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '$~': // if variable ends with part, case sensitive
				if(\SmartUnicode::sub_str((string)$lexpr, (-1 * \SmartUnicode::str_len((string)$rexpr)), \SmartUnicode::str_len((string)$rexpr)) == (string)$rexpr) { // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			case '$*': // if variable ends with part, case insensitive ### !!! Expensive in Execution !!! ###
				if(
					(\SmartUnicode::str_tolower(\SmartUnicode::sub_str((string)$lexpr, (-1 * \SmartUnicode::str_len(\SmartUnicode::str_tolower((string)$rexpr))), \SmartUnicode::str_len(\SmartUnicode::str_tolower((string)$rexpr)))) == (string)\SmartUnicode::str_tolower((string)$rexpr))
					OR
					(\SmartUnicode::str_toupper(\SmartUnicode::sub_str((string)$lexpr, (-1 * \SmartUnicode::str_len(\SmartUnicode::str_toupper((string)$rexpr))), \SmartUnicode::str_len(\SmartUnicode::str_toupper((string)$rexpr)))) == (string)\SmartUnicode::str_toupper((string)$rexpr)))
				{ // if evaluate to true keep the inner content
					return $chunk->render($bodies->block, $context);
				} elseif(isset($bodies['else'])) {
					return $chunk->render($bodies['else'], $context);
				} else {
					return $chunk;
				}
				break; //--
			//--
			default:
				$chunk->setError('Invalid condition operator for if');
			//--
		} //end switch
	}

}

// #end of php code
