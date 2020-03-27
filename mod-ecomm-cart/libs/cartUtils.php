<?php

namespace SmartModExtLib\EcommCart;

/**
 * Cart: A complex PHP cart library, with support of Product / Services with attributes, formula calculations and multiple currency exchanges
 * Copyright (c) 2018-2020 unix-world.org
 *
 */

final class cartUtils {

	// r.20200325

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
							$tmp_arr['position'] 		= (int)    $item['position'];
							$tmp_arr['dtime'] 			= (string) $item['dtime'];
							$tmp_arr['hash'] 			= (string) $item['hash'];
							$tmp_arr['id'] 				= (string) $item['id'];
							$tmp_arr['type'] 			= (string) $item['type'];
							$tmp_arr['name'] 			= (string) $item['data']['name'];
							$tmp_arr['quantity'] 		= (float)  (0 + \Smart::format_number_dec($item['quantity'], 4, '.', ''));
							$tmp_arr['qtyerg'] 			= (float)  (0 + \Smart::format_number_dec($item['qtyerg'], 4, '.', ''));
							$tmp_arr['currency'] 		= (string) $item['currency'];
							$tmp_arr['price'] 			= (float)  (0 + \Smart::format_number_dec($item['price'], 2, '.', ''));
							$tmp_arr['tax'] 			= (float)  (0 + \Smart::format_number_dec($item['tax'], 2, '.', ''));
							$tmp_arr['price_'] 			= (string) $item['price_'];
							$tmp_arr['_price'] 			= (string) $item['_price'];
							$tmp_arr['discount_'] 		= (string) $item['discount_'];
							$tmp_arr['_discount'] 		= (string) $item['_discount'];
							$tmp_arr['_currency'] 		= (string) $item['_currency'];
							$tmp_arr['_exchrate'] 		= (string) $item['_exchrate'];
							//-- {{{SYNC-CART-CALC-TOTALS}}}
							$tmp_arr['tax-ratio'] 		= (float)  (0 + \Smart::format_number_dec(($tmp_arr['tax'] / 100), 4, '.', '')); // {{{SYNC-CART-TAX-RATIO}}} tax / 100 must have 4 decimals as tax % can have 2 decimals !!!
							$tmp_arr['tot-price-notax'] = (float)  (0 + \Smart::format_number_dec(($tmp_arr['quantity'] * $tmp_arr['price']), 2, '.', ''));
							$tmp_arr['tot-price-tax'] 	= (float)  (0 + \Smart::format_number_dec(($tmp_arr['quantity'] * $tmp_arr['price'] * $tmp_arr['tax-ratio']), 2, '.', ''));
							//--
							$tmp_arr['um'] 				= (string) $item['data']['pak']['um'];
							$tmp_arr['umtype'] 			= (string) $item['data']['pak']['umtype'];
							$tmp_arr['umerg'] 			= (string) $item['data']['pak']['umerg'];
							$tmp_arr['ummin'] 			= (float)  (0 + \Smart::format_number_dec($item['data']['pak']['ummin'], 4, '.', ''));
							//--
							$tmp_arr['attributes'] 		= array();
							if(\Smart::array_size($item['data']) > 0) {
								if(\Smart::array_size($item['data']['atts']) > 0) {
									//print_r($item['data']['atts']); die();
									foreach($item['data']['atts'] as $zk => $zv) {
										$tmp_z_name = (string) $zv['name'];
										if(!$tmp_z_name) {
											$tmp_z_name = (string) $zk; // if no attribute name use attribute key as name
										} //end if
										$tmp_z_oldval = (string) $item['attributes'][(string)$zk];
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
											'optional' 		=> (string) $zv['optional'],
											'validhint' 	=> (string) $zv['validhint'],
											'validation' 	=> (string) $zv['validation'],
											'decimals' 		=> (string) $zv['decimals'],
											'min' 			=> (string) $zv['min'],
											'minlen' 		=> (string) $zv['minlen'],
											'minval' 		=> (string) $zv['minval'],
											'max' 			=> (string) $zv['max'],
											'maxlen' 		=> (string) $zv['maxlen'],
											'maxval' 		=> (string) $zv['maxval'],
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
						$tmp_item_qty   = \Smart::format_number_dec($item['quantity'], 4, '.', '');
						$tmp_item_price = \Smart::format_number_dec($item['price'], 2, '.', '');
						$tmp_item_tax   = \Smart::format_number_dec(($item['tax'] / 100), 4, '.', ''); // {{{SYNC-CART-TAX-RATIO}}} tax / 100 must have 4 decimals as tax % can have 2 decimals !!!
						$tmp_item_tot_price_notax = \Smart::format_number_dec(($tmp_item_qty * $tmp_item_price), 2, '.', '');
						$tmp_item_tot_price_tax = \Smart::format_number_dec(($tmp_item_qty * $tmp_item_price * $tmp_item_tax), 2, '.', '');
						$tmp_item_tot_price_all = \Smart::format_number_dec(($tmp_item_tot_price_notax + $tmp_item_tot_price_tax), 2, '.', '');
					//	\Smart::log_notice('qty='.$tmp_item_qty.' ; '.'price='.$tmp_item_price.' ; '.'tax='.$tmp_item_tax.' ; '.'tot-price-notax='.$tmp_item_tot_price_notax.' ; '.'tot-price-tax='.$tmp_item_tot_price_tax.' ; tot-all='.$tmp_item_tot_price_all);
						$total_notax += $tmp_item_tot_price_notax;
						$total_tax += $tmp_item_tot_price_tax;
						$grand_total += $tmp_item_tot_price_all;
					} //end if
				} //end foreach
			} //end if
		} //end foreach
		//--
		return array(
			'total-notax' => (float) (0 + \Smart::format_number_dec($total_notax, 2, '.', '')),
			'total-tax'   => (float) (0 + \Smart::format_number_dec($total_tax,   2, '.', '')),
			'grand-total' => (float) (0 + \Smart::format_number_dec($grand_total, 2, '.', ''))
		);
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
