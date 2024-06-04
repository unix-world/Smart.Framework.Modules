<?php
// eComm Cart Utils
// (c) 2006-2024 unix-world.org - all rights reserved

namespace SmartModExtLib\EcommCart;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


/**
 * Cart: A complex PHP cart library, with support of Product / Services with attributes, formula calculations and multiple currency exchanges
 *
 */

final class cartUtils {

	// r.20240123

	/**
	 * Get all items in cart formated for display cart.
	 *
	 * @return array
	 */
	public static function getDisplayItems($all_items, $cart_mode, $show_empty_atts) {
		//--
		$display_items = [];
		//--
		if(!\is_array($all_items)) {
			return array();
		} //end if
		//--
		if(\Smart::array_size($all_items) > 0) {
			//print_r($all_items); die();
			foreach($all_items as $id => $items) {
				if(\is_array($items)) {
					foreach($items as $key => $item) {
						if(\is_array($item)) {
							//print_r($item); die();
							// {{{SYNC-ECART-ITEM-PROPS}}}
							$tmp_arr = [];
							$tmp_arr['position'] 		= (int)    (isset($item['position']) ? $item['position'] : null);
							$tmp_arr['dtime'] 			= (string) (isset($item['dtime']) ? $item['dtime'] : null);
							$tmp_arr['hash'] 			= (string) (isset($item['hash']) ? $item['hash'] : null);
							$tmp_arr['id'] 				= (string) (isset($item['id']) ? $item['id'] : null);
							$tmp_arr['type'] 			= (string) (isset($item['type']) ? $item['type'] : null);
							$tmp_arr['name'] 			= (string) ((isset($item['data']) && \is_array($item['data']) && isset($item['data']['name'])) ? $item['data']['name'] : null);
							$tmp_arr['quantity'] 		= (float)  \Smart::format_number_dec((isset($item['quantity']) ? $item['quantity'] : null), 2, '.', '');
							$tmp_arr['qtyerg'] 			= (float)  \Smart::format_number_dec((isset($item['qtyerg']) ? $item['qtyerg'] : null), 2, '.', '');
							$tmp_arr['currency'] 		= (string) (isset($item['currency']) ? $item['currency'] : null);
							$tmp_arr['price'] 			= (float)  \Smart::format_number_dec((isset($item['price']) ? $item['price'] : null), 2, '.', '');
							$tmp_arr['tax'] 			= (float)  \Smart::format_number_dec((isset($item['tax']) ? $item['tax'] : null), 2, '.', '');
							$tmp_arr['price_'] 			= (string) (isset($item['price_']) ? $item['price_'] : null);
							$tmp_arr['_price'] 			= (float)  (isset($item['_price']) ? $item['_price'] : null);
							$tmp_arr['discount'] 		= (string) \Smart::format_number_dec((isset($item['discount']) ? $item['discount'] : null), 2, '.', ''); // custom discount
							$tmp_arr['discount_'] 		= (string) (isset($item['discount_']) ? $item['discount_'] : null);
							$tmp_arr['_discount'] 		= (float)  (isset($item['_discount']) ? $item['_discount'] : null);
							$tmp_arr['_discount_'] 		= (float)  \Smart::format_number_dec($tmp_arr['_discount'] * $tmp_arr['_price'], 2, '.', ''); // just informative, do not use in calculations ; in calculations must use discounted value (price * quantity) not discounted individual price !
							$tmp_arr['_price_'] 		= (float)  \Smart::format_number_dec(($tmp_arr['_price'] - $tmp_arr['_discount_']), 2, '.', ''); // just informative, do not use in calculations ; in calculations must use discounted value (price * quantity) not discounted individual price !
							$tmp_arr['_currency'] 		= (string) (isset($item['_currency']) ? $item['_currency'] : null);
							$tmp_arr['_exchrate'] 		= (string) \Smart::format_number_dec((isset($item['_exchrate']) ? $item['_exchrate'] : null), 2, '.', '');
							//-- {{{SYNC-CART-CALC-TOTALS}}}
							$tmp_arr['tax-ratio'] 		= (float)  (0 + \Smart::format_number_dec(($tmp_arr['tax'] / 100), 2, '.', '')); // {{{SYNC-CART-TAX-RATIO}}} tax / 100 must have 4 decimals as tax % can have 2 decimals !!!
							$tmp_arr['tot-price-notax'] = (float)  (0 + \Smart::format_number_dec(($tmp_arr['quantity'] * $tmp_arr['price']), 2, '.', ''));
							$tmp_arr['tot-price-tax'] 	= (float)  (0 + \Smart::format_number_dec(($tmp_arr['quantity'] * $tmp_arr['price'] * $tmp_arr['tax-ratio']), 2, '.', ''));
							$tmp_arr['tot-disc-notax'] 	= (float)  (0 + \Smart::format_number_dec($tmp_arr['_discount'] * ($tmp_arr['_price'] * $tmp_arr['quantity']), 2, '.', ''));
							$tmp_arr['tot-disc-tax'] 	= (float)  (0 + \Smart::format_number_dec(($tmp_arr['_discount'] * ($tmp_arr['_price'] * $tmp_arr['quantity'])) * $tmp_arr['tax-ratio'], 2, '.', ''));
							$tmp_arr['tot-amount'] 		= (float)  (0 + \Smart::format_number_dec($tmp_arr['tot-price-notax'] - $tmp_arr['tot-disc-notax'], 2, '.', ''));
							$tmp_arr['tot-tax'] 		= (float)  (0 + \Smart::format_number_dec($tmp_arr['tot-price-tax'] - $tmp_arr['tot-disc-tax'], 2, '.', ''));
							//--
							$tmp_arr['um'] 				= (string) '';
							$tmp_arr['umtype'] 			= (string) '';
							$tmp_arr['umerg'] 			= (string) '';
							$tmp_arr['ummin'] 			= (float)  0;
							if(isset($item['data']) AND \is_array($item['data']) AND isset($item['data']['pak']) AND \is_array($item['data']['pak'])) {
								$tmp_arr['um'] 				= (string) (isset($item['data']['pak']['um']) ? $item['data']['pak']['um'] : null);
								$tmp_arr['umtype'] 			= (string) (isset($item['data']['pak']['umtype']) ? $item['data']['pak']['umtype'] : null);
								$tmp_arr['umerg'] 			= (string) (isset($item['data']['pak']['umerg']) ? $item['data']['pak']['umerg'] : null);
								$tmp_arr['ummin'] 			= (float)  (0 + \Smart::format_number_dec((isset($item['data']['pak']['ummin']) ? $item['data']['pak']['ummin'] : null), 2, '.', ''));
							} //end if
							//--
							$tmp_arr['attributes'] = array();
							if(isset($item['data']) AND (\Smart::array_size($item['data']) > 0)) {
								if(isset($item['data']['atts']) AND (\Smart::array_size($item['data']['atts']) > 0)) {
									//print_r($item['data']['atts']); die();
									foreach($item['data']['atts'] as $zk => $zv) {
										$tmp_z_name = (string) $zv['name'];
										if(!$tmp_z_name) {
											$tmp_z_name = (string) $zk; // if no attribute name use attribute key as name
										} //end if
										$tmp_z_oldval = (string) ((isset($item['attributes']) && \is_array($item['attributes']) && isset($item['attributes'][(string)$zk])) ? $item['attributes'][(string)$zk] : null);
										$tmp_z_display = true;
										if((string)$tmp_z_oldval == '') {
											if($show_empty_atts === false) {
												switch((string)$zv['optional']) {
													case 'inventory':
														if((string)$cart_mode != 'inventory') {
															$tmp_z_display = false;
														} //end if
														break;
													case 'all':
													case 'validation':
														$tmp_z_display = false;
														break;
													default:
														// nothing
												} //end switch
											} else {
												switch((string)$zv['optional']) {
													case 'inventory':
														if((string)$cart_mode != 'inventory') {
															$tmp_z_display = false;
														} //end if
														break;
													default:
														// nothing
												} //end switch
											} //end if else
										} //end if
										$tmp_arr['attributes'][(string)$zk] = [
											'name' 			=> (string) $tmp_z_name,
											'value' 		=> (string) $tmp_z_oldval,
											'display' 		=> (string) ($tmp_z_display ? 'yes' : 'no'),
											'optional' 		=> (string) (isset($zv['optional']) ? $zv['optional'] : null),
											'validhint' 	=> (string) (isset($zv['validhint']) ? $zv['validhint'] : null),
											'validation' 	=> (isset($zv['validation']) ? $zv['validation'] : ''), // mixed: can be string or array if list
											'decimals' 		=> (string) (isset($zv['decimals']) ? $zv['decimals'] : null),
											'min' 			=> (string) (isset($zv['min']) ? $zv['min'] : null),
											'minlen' 		=> (string) (isset($zv['minlen']) ? $zv['minlen'] : null),
											'minval' 		=> (string) (isset($zv['minval']) ? $zv['minval'] : null),
											'max' 			=> (string) (isset($zv['max']) ? $zv['max'] : null),
											'maxlen' 		=> (string) (isset($zv['maxlen']) ? $zv['maxlen'] : null),
											'maxval' 		=> (string) (isset($zv['maxval']) ? $zv['maxval'] : null),
										];
										$tmp_z_name = null;
										$tmp_z_oldval = null;
										$tmp_z_display = null;
									} //end foreach
								} //end if
							} //end if
							if($tmp_arr['quantity'] > 0) {
								$display_items[] = (array) $tmp_arr;
							} //end if
						} //end if
					} //end foreach
				} //end if
			} //end foreach
		} //end if
		//--
		if(\Smart::array_size($display_items) > 0) {
			\usort($display_items, function($a,$b){ return $a['position'] - $b['position']; });
			$display_items = (array) \array_values($display_items);
		} //end if
		//--
		return (array) $display_items;
		//--
	} //END FUNCTION


	/**
	 * Get the cart totals.
	 *
	 * @return array
	 */
	public static function getDisplayTotals($all_items) { // {{{SYNC-CART-CALC-TOTALS}}}
		//--
		$total_disc_notax = 0;
		$total_disc_tax = 0;
		$total_notax = 0;
		$total_tax = 0;
		$grand_total = 0;
		//--
		if(!\is_array($all_items)) {
			$all_items = array();
		} //end if
		//--
		foreach($all_items as $key => $items) {
			if(\is_array($items)) {
				foreach($items as $kk => $item) {
					if(\is_array($item)) {
						$tmp_item_qty   = \Smart::format_number_dec(($item['quantity'] ?? null), 2, '.', '');
						$tmp_item_price = \Smart::format_number_dec(($item['price'] ?? null), 2, '.', '');
						$tmp_item_tax   = \Smart::format_number_dec(((float)($item['tax'] ?? null) / 100), 2, '.', ''); // {{{SYNC-CART-TAX-RATIO}}} tax / 100 must have 4 decimals as tax % can have 2 decimals !!!
						$tmp_item_tot_price_notax = \Smart::format_number_dec(($tmp_item_qty * $tmp_item_price), 2, '.', '');
						$tmp_item_tot_price_tax = \Smart::format_number_dec(($tmp_item_tot_price_notax * $tmp_item_tax), 2, '.', '');
						$tmp_item_tot_price_all = \Smart::format_number_dec(($tmp_item_tot_price_notax + $tmp_item_tot_price_tax), 2, '.', '');
					//	\Smart::log_notice('qty='.$tmp_item_qty.' ; '.'price='.$tmp_item_price.' ; '.'tax='.$tmp_item_tax.' ; '.'tot-price-notax='.$tmp_item_tot_price_notax.' ; '.'tot-price-tax='.$tmp_item_tot_price_tax.' ; tot-all='.$tmp_item_tot_price_all);
						$total_notax += $tmp_item_tot_price_notax;
						$total_tax += $tmp_item_tot_price_tax;
						$grand_total += $tmp_item_tot_price_all;
						if(isset($item['_discount']) AND ($item['_discount'] > 0) AND (!$item['price_'])) {
							$total_disc_notax += \Smart::format_number_dec($item['_discount'] * ($item['_price'] * $tmp_item_qty), 2, '.', '');
							$total_disc_tax += \Smart::format_number_dec(($item['_discount'] * ($item['_price'] * $tmp_item_qty)) * $tmp_item_tax, 2, '.', '');
						} //end if
					} //end if
				} //end foreach
			} //end if
		} //end foreach
		//--
		return array(
			'discount-notax' => (float) (0 + \Smart::format_number_dec($total_disc_notax, 2, '.', '')),
			'discount-tax' => (float) (0 + \Smart::format_number_dec($total_disc_tax, 2, '.', '')),
			'total-notax' => (float) (0 + \Smart::format_number_dec($total_notax - $total_disc_notax, 2, '.', '')),
			'total-tax'   => (float) (0 + \Smart::format_number_dec($total_tax - $total_disc_tax,   2, '.', '')),
			'grand-total' => (float) (0 + \Smart::format_number_dec($grand_total - $total_disc_notax - $total_disc_tax, 2, '.', ''))
		);
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
