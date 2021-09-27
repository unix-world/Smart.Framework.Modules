<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: EcommCart/Test
// Route: ?/page/ecomm-cart.test (?page=ecomm-cart.test)
// (c) 2006-2021 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'INDEX'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {


	public function Initialize() {
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) {
			$this->PageViewSetErrorStatus(500, ' # Mod AuthAdmins is missing !');
			return false;
		} //end if
		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-auth-admins/templates/');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
		return true;
		//--
	} //END FUNCTION


	public function Run() {

		// r.20210318

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!SmartAppInfo::TestIfModuleExists('vendor')) {
			$this->PageViewSetCfg('error', 'ERROR: Vendor Libs are missing from modules ...');
			return 500;
		} //end if
		//--
		if(!class_exists('\\PHPMathParser\\Math')) {
			require_once('modules/vendor/PHPMathParser/autoload.php');
		} //end if
		//--

		//--
		$op = $this->RequestVarGet('op', '', 'string');
		//--

		//--
		$cart = new \SmartModExtLib\EcommCart\cartManager([
			'cartId' 			=> 'eCommCart',
			'cartMaxItem' 		=> 20, // Maximum item can added to cart, 0 = Unlimited
			'itemMaxQuantity' 	=> 50, // Maximum quantity of a item can be added to cart, 0 = Unlimited
			'cartCurrency' 		=> 'US$',
		//	'cartShowEmptyAtts' => true, // debug only
		//	'noPrice' 			=> true, // special operating mode with no prices
			'cartMode' 			=> 'sales' // inventory | sales | customer (default)
		]);
		//--

		//--
		$products = [];
		//--
		for($i=1; $i<=4; $i++) {
			if($i > 99) {
				$this->PageViewSetCfg('error', 'ERROR: Sample Items Index too high ...');
				return 500;
			} //end if
			$yaml = (string) $i;
			if(strlen((string)$yaml) < 2) {
				$yaml = '0'.$yaml;
			} //end if
			$yaml = 'modules/mod-ecomm-cart/doc/sample-items/item-'.Smart::safe_filename($yaml).'.yaml';
			$product = (string) SmartFileSystem::read((string)$yaml);
			if((string)$product == '') {
				$this->PageViewSetCfg('error', 'ERROR: Vendor Libs are missing from modules ...');
				return 500;
			} //end if
			$product = (new SmartYamlConverter())->parse((string)$product);
			if(Smart::array_size($product['item']) > 0) {
				$products[] = (array) $product['item'];
			} //end if
			$product = null;
			$yaml = null;
		} //end for
		//--
		/*
		echo "<pre>";
		echo Smart::escape_html(print_r($products,1));
		echo "</pre>";
		die();
		*/
		//--

		//--
		if((string)$op == 'cart-json') {
			//--
			$this->PageViewSetCfg('rawpage', true);
			//--
			//print_r($_POST); die();
			$cart_op = $this->RequestVarGet('cart_action', '', 'string');
			$frm = $this->RequestVarGet('frm', [], 'array');
			$frx = $this->RequestVarGet('frx', [], 'array');
			//--
			$redirect = '';
			$message = '???';
			$is_ok = false;
			$cart->resetError();
			//-- Empty the cart
			if((string)$cart_op == 'empty') {
				$message = 'Cart cleared';
				$is_ok = (bool) ($cart->clear() && $cart->destroy());
				$redirect = '?page='.Smart::escape_url($this->ControllerGetParam('controller')).'&op=cart';
			} //end if
			//-- Add item
			if((string)$cart_op == 'add') {
				$cart_item_id 	= (string) (isset($frm['id'])   ? $frm['id']   : null);
				$cart_item_qty 	= (string) (isset($frm['qty'])  ? $frm['qty']  : null);
				$cart_item_atts = (array)  (isset($frm['att'])  ? $frm['att']  : null);
			//	$cart_item_hash = (string) (isset($frm['hash']) ? $frm['hash'] : null);
				$message = 'Item added';
				$product = array();
				foreach($products as $key => $product) {
					if((string)$cart_item_id == (string)$product['id']) {
						break;
					} //end if
				} //end foreach
				if(($cart_item_qty > 0) AND (Smart::array_size($product) > 0)) {
					$is_ok = (bool) $cart->add(
						(array)  $product,
						(array)  $cart_item_atts,
						(string) $cart_item_qty
					);
				} //end if
			} //end if
			//-- Update item or cart
			if((string)$cart_op == 'update') {
				$redirect = '?page='.Smart::escape_url($this->ControllerGetParam('controller')).'&op=cart';
				if(isset($frm['cart']) AND ((string)$frm['cart'] == '@cart')) {
					$message = 'Cart updated';
					$is_ok = (bool) $cart->multiupdate((array)$frm);
				} else {
					$message = 'Item quantity updated';
					$is_ok = (bool) $cart->update(
						(string) (isset($frm['id'])   ? $frm['id']   : null),
						(string) (isset($frm['hash']) ? $frm['hash'] : null),
						(string) (isset($frm['qty'])  ? $frm['qty']  : null)
					);
				} //end if else
			} //end if
			//-- Replace Item
			if((string)$cart_op == 'replace') {
				$is_ok = false;
				if(isset($frm['cart']) AND ((string)$frm['cart'] == '@cart')) {
					if(isset($frx['hash']) AND isset($frm[(string)$frx['hash']]) AND (Smart::array_size($frm[(string)$frx['hash']]) > 0)) {
						if(isset($frm[(string)$frx['hash']]['att']) AND is_array($frm[(string)$frx['hash']]['att'])) {
							$message = 'Item replaced';
							$redirect = '?page='.Smart::escape_url($this->ControllerGetParam('controller')).'&op=cart';
							$is_ok = (bool) $cart->replace(
								(string) (isset($frx['id'])   ? $frx['id']   : null),
								(string) (isset($frx['hash']) ? $frx['hash'] : null),
								(string) (isset($frx['qty'])  ? $frx['qty']  : null),
								(array)  $frm[(string)$frx['hash']]['att'] // already check if isset
							);
						} //end if
					} //end if
				} //end if
			} //end if
			//-- Remove item
			if((string)$cart_op == 'remove') {
				$message = 'Item removed';
				$redirect = '?page='.Smart::escape_url($this->ControllerGetParam('controller')).'&op=cart';
				$is_ok = (bool) $cart->remove(
					(string) (isset($frm['id'])   ? $frm['id']   : null),
					(string) (isset($frm['hash']) ? $frm['hash'] : null)
				);
			} //end if
			//--
			$err = (string) $cart->getError();
			if($is_ok && (!$err)) {
				$answer = 'OK';
			} else {
				$answer = 'ERR';
				$message = ($err ? $err : 'Operation not completed').' ...';
				$redirect = '';
			} //end if
			$title = 'Cart';
			//--
			$this->PageViewSetVar(
				'main',
				(string) SmartViewHtmlHelpers::js_ajax_replyto_html_form($answer, $title, $message, $redirect)
			);
			return;
			//--
		} elseif((string)$op == 'cart') { // display cart
			//--
			$tpl = 'cart.mtpl.htm';
			//--
			$citems = (array)  $cart->getItems();
			$cmode  = (string) $cart->getCartMode();
			$csea   = (bool)   $cart->getCartShowEmptyAtts();
			$cerr   = (string) $cart->getError(); // get error at the end when displaying the cart !!!
			//--
			$totals = (array) \SmartModExtLib\EcommCart\cartUtils::getDisplayTotals($citems);
			//--
			$arr = [
				'PAGE-URL' 				=> (string) $this->ControllerGetParam('controller'),
				'DATE-TIME' 			=> (string) date('Y-m-d H:i:s'),
				'CART-MODE' 			=> (string) $cmode,
				'CART-NOPRICE' 			=> (string) ($cart->getCartNoPriceMode() ? 'yes' : 'no'),
				'CART-DISCOUNT-LVL' 	=> (string) $cart->getCartDiscountLevel(),
				'CART-CURRENCY' 		=> (string) $cart->getCartCurrency(),
				'CART-TOTAL-NOTAX' 		=> (string) $totals['total-notax'],
				'CART-TOTAL-TAX' 		=> (string) $totals['total-tax'],
				'CART-GRAND-TOTAL' 		=> (string) $totals['grand-total'],
				'CART-ITEMS' 			=> (array)  \SmartModExtLib\EcommCart\cartUtils::getDisplayItems((array)$citems, (string)$cmode, (bool)$csea),
				'CART-ERROR' 			=> (string) $cart->getError(), // this must be at the end
				'CART-FINALIZE-URL' 	=> (string) '?page=ecomm-cart.checkout',
				'CART-FINALIZE-TEXT' 	=> (string) 'Checkout',
				'CART-FINALIZE-ICON' 	=> (string) 'credit-card' // from sfi icons, will add the prefix: sfi sfi-{icon}
			];
			//--
		} else { // display shop
			//--
			$arr = [];
			if(is_array($products)) {
				foreach($products as $key => $val) {
					//print_r($products); die();
					//print_r($val); die();
					$attributes = (array) $cart->getItemAttributes((isset($val['attributes']) ? $val['attributes'] : null), true);
					$display_atts = array();
					foreach($attributes as $kk => $vv) { // create a copy of attributes because extra keys must not be includded in calculateHash
						if(Smart::array_size($vv) > 0) {
							$vv['display'] = 'yes';
							if(isset($vv['optional']) AND ($vv['optional'] == 'inventory')) {
								if((string)$cart->getCartMode() != 'inventory') {
									$vv['display'] = 'no';
								} //end if
							} //end if
							$display_atts[(string)$kk] = (array) $vv;
						} //end if
					} //end foreach
					//print_r($display_atts); die();
					$arr[] = [
						'id' 		=> (string) (isset($val['id']) ? $val['id'] : null),
						'name' 		=> (string) (isset($val['name']) ? $val['name'] : null),
						'price' 	=> (string) (isset($val['price']) ? $val['price'] : null),
						'currency' 	=> (string) (isset($val['currency']) ? $val['currency'] : null),
						'um' 		=> (string) ((isset($val['pak']) && is_array($val['pak']) && isset($val['pak']['um'])) ? $val['pak']['um'] : null),
						'img-src' 	=> (string) ((isset($val['image']) && is_array($val['image']) && isset($val['image']['source'])) ? $val['image']['source'] : null),
						'img-w' 	=> (string) ((isset($val['image']) && is_array($val['image']) && isset($val['image']['width'])) ? $val['image']['width'] : null),
						'img-h' 	=> (string) ((isset($val['image']) && is_array($val['image']) && isset($val['image']['height'])) ? $val['image']['height'] : null),
						'atts' 		=> (array)  $display_atts,
						'uuid' 		=> (string) sha1((string)(isset($val['id']) ? $val['id'] : null))
					];
				} //end foreach
			} //end if
			//--
			$tpl = 'shop.mtpl.htm';
			$arr = [
				'PAGE-URL' 			=> (string) $this->ControllerGetParam('controller'),
				'DATE-TIME' 		=> (string) date('Y-m-d H:i:s'),
				'CART-MODE' 		=> (string) $cart->getCartMode(),
				'CART-NOPRICE' 		=> (string) ($cart->getCartNoPriceMode() ? 'yes' : 'no'),
				'PRODUCTS-ARR' 		=> (array) $arr
			];
			//--
		} //end if else
		//--
		$this->PageViewSetVars([
			'title' => 'eCommerce Test',
			'main' => SmartMarkersTemplating::render_file_template(
				(string) $this->ControllerGetParam('module-view-path').$tpl,
				(array) $arr,
				'no' // don't use caching (use of caching make sense only if file template is used more than once per execution)
			)
		]);
		//--

	} //END FUNCTION


} //END CLASS

// end of php code
