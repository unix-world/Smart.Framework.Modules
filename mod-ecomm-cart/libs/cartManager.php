<?php
// eComm Cart Manager
// (c) 2006-2023 unix-world.org - all rights reserved

namespace SmartModExtLib\EcommCart;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


/**
 * Cart: A complex PHP cart library, with support of Product / Services with attributes, formula calculations and multiple currency exchanges
 * TODO: implement real currency exchange rates
 *
 */

final class cartManager {

	// r.20231209

	/**
	 * An unique ID for the cart.
	 *
	 * @var string
	 */
	private $cartId = 'eComm_Cart';

	/**
	 * Maximum item allowed in the cart.
	 *
	 * @var int
	 */
	private $cartMaxItem = 25;

	/**
	 * Maximum quantity of a item allowed in the cart.
	 *
	 * @var int
	 */
	private $itemMaxQuantity = 999999.99;

	/**
	 * Enable or disable cookie.
	 *
	 * @var bool
	 */
	private $useCookie = true;

	/**
	 * A collection of cart items.
	 *
	 * @var array
	 */
	private $items = [];

	/**
	 * Cart Error Message
	 *
	 * @var string
	 */
	private $errmsg = '';

	private $cartCurrency = 'US$';
	private $cartShowEmptyAtts = false; // display or not in cart empty attributes
	private $cartNoPriceMode = false; // special operating mode with no prices
	private $cartMode = 'customer'; // customer | sales | inventory
	private $cartDiscountLevel = 'p1';

	/**
	 * Initialize cart.
	 *
	 * @param array $options
	 */
	public function __construct($options=[]) {
		//--
		if(!\class_exists('\\PHPMathParser\\Math')) {
			\Smart::raise_error(__METHOD__.'() :: eComm Cart Requires the \\PHPMathParser\\Math class ...');
			return;
		} //end if
		//--
		if(!\is_array($options)) {
			$options = array();
		} //end if
		//--
		if(isset($options['cartShowEmptyAtts'])) {
			$this->cartShowEmptyAtts = (bool) $options['cartShowEmptyAtts'];
		} //end if
		//--
		if(isset($options['cartMode']) && ((string)$options['cartMode'] != '')) {
			switch((string)$options['cartMode']) {
				case 'sales': 		// sales mode: allow cart edit of attributes, special prices and custom discounts
				case 'inventory': 	// inventory mode: like sales + items are mandatory
					$this->cartMode = (string) $options['cartMode'];
					$this->cartShowEmptyAtts = true;
					break;
				case 'customer': 	// customer mode (default)
				default:
					// invalid mode, don't set
			} //end switch
		} //end if
		if(isset($options['noPrice'])) {
			$this->cartNoPriceMode = (bool) $options['noPrice'];
		} //end if
		//--
		if(isset($options['cartId']) && ((string)$options['cartId'] != '') && (\preg_match('/^[a-zA-Z0-9_]+$/', (string)$options['cartId']))) {
			$this->cartId = (string) $options['cartId'];
		} //end if
		if(isset($options['cartMaxItem']) && \preg_match('/^\d+$/', $options['cartMaxItem'])) {
			$this->cartMaxItem = \Smart::format_number_int($options['cartMaxItem'],'+');
		} //end if
		if(isset($options['itemMaxQuantity']) && \preg_match('/^\d+$/', $options['itemMaxQuantity'])) {
			$this->itemMaxQuantity = $options['itemMaxQuantity'];
		} //end if
		if(isset($options['useCookie']) && $options['useCookie']) {
			$this->useCookie = true;
		} //end if
		//--
		if((string)$options['cartCurrency'] != '') {
			if(\preg_match('/^[A-Z\$]{3}$/', (string)$options['cartCurrency'])) { // {{{SYNC-VALIDATE-CART-CURRENCY}}}
				$this->cartCurrency = (string) $options['cartCurrency'];
			} //end if
		} //end if
		//--
		$this->read();
		//--
	} //END FUNCTION


	/**
	 * Public Add item to cart.
	 *
	 * @param string $id
	 * @param array  $attributes
	 * @param int    $quantity
	 *
	 * @return bool
	 */
	public function add($product, $attributes, $quantity=1) {
		//--
		return (bool) $this->doAdd($product, $attributes, $quantity);
		//--
	} //END FUNCTION


	/**
	 * Public Replace item with new attributes, keeping quantity.
	 * Does not make sense for items with no attributes
	 *
	 * @param string $id					ID
	 * @param string $hash					Old Hash
	 * @param int    $quantity				Old Quantity
	 * @param array  $attributes			New Attributes
	 *
	 * @return bool
	 */
	public function replace($id, $hash, $quantity, $attributes) {
		//--
		if((string)$this->getCartMode() == 'customer') {
			return false;
		} //end if
		//--
		return (bool) $this->doReplace($id, $hash, $quantity, $attributes);
		//--
	} //END FUNCTION


	/**
	 * Public Update item in cart.
	 *
	 * @param string        $id
	 * @param array/string  $attributes
	 * @param int           $quantity
	 *
	 * @return bool
	 */
	public function update($id, $attributes, $quantity=1) {
		//--
		if($this->errmsg) {
			return false;
		} //end if
		//--
		return (bool) $this->doUpdate($id, $attributes, $quantity);
		//--
	} //END FUNCTION


	/**
	 * Public Multi-Update items in cart.
	 *
	 * @param array $arr[ hash => [id, qty, att], ... ]
	 *
	 * @return bool
	 */
	public function multiupdate($arr) {
		//--
		if($this->errmsg) {
			return false;
		} //end if
		//--
		return (bool) $this->doMultiUpdate($arr);
		//--
	} //END FUNCTION


	/**
	 * Public Remove item from cart.
	 *
	 * @param string $id
	 * @param array/string  $attributes
	 *
	 * @return bool
	 */
	public function remove($id, $attributes) {
		//--
		return (bool) $this->doRemove($id, $attributes);
		//--
	} //END FUNCTION


	public function getCartNoPriceMode() {
		//--
		return (bool) $this->cartNoPriceMode;
		//--
	} //END FUNCTION


	public function getCartDiscountLevel() {
		//--
		return (string) $this->cartDiscountLevel;
		//--
	} //END FUNCTION


	public function getCartCurrency() {
		//--
		return (string) $this->cartCurrency;
		//--
	} //END FUNCTION


	public function getCurrencyExchangeRate($currency) {
		//--
		if((string)$currency == (string)$this->cartCurrency) {
			return 1;
		} //end if
		//--
		// TODO: implement real currency exchange rates
		if(((string)$currency == 'US$') && ((string)$this->cartCurrency == 'EUR')) {
			return 0.9;
		} elseif(((string)$currency == 'EUR') && ((string)$this->cartCurrency == 'US$')) {
			return 1.1;
		} //end if
		throw new \Exception('Currency Exchange Rates not yet implemented for: '.$currency);
		return 0;
		//--
	} //END FUNCTION


	public function getCartShowEmptyAtts() {
		//--
		return (bool) $this->cartShowEmptyAtts;
		//--
	} //END FUNCTION


	public function getCartMode() {
		//--
		return (string) $this->cartMode;
		//--
	} //END FUNCTION


	public function getItemAttributes($attribs, $displaymode=false) {
		//--
		$displaymode = (bool) $displaymode;
		//--
		$attributes = array();
		//--
		if(\is_array($attribs)) {
			foreach($attribs as $k => $v) {
				//--
				if(\is_array($v)) {
					//--
					$attvalues = array();
					//--
					switch((string)(isset($v['type']) ? $v['type'] : null)) {
						case 'list':
							if((!isset($v['validation'])) OR (\Smart::array_size($v['validation']) <= 0)) {
								$v['type'] = 'free';
							} //end if
							break;
						case 'date':
						case 'time':
							if(isset($v['@ref-val']) AND ((string)$v['@ref-val'] != '')) {
								$attvalues['@ref-val'] = (string) $v['@ref-val'];
							} else {
								$attvalues['@ref-val'] = (string) \date('Y-m-d H:i:s O');
							} //end if else
							break;
						case 'free':
						default:
							$v['type'] = 'free';
					} //end switch
					//--
					$attvalues['type'] = (string) (isset($v['type']) ? $v['type'] : null);
					$attvalues['name'] = (string) (isset($v['name']) ? $v['name'] : null);
					//--
					if($displaymode === true) { // initialize some req. values for display mode
						$attvalues['optional'] = null;
						$attvalues['decimals'] = null;
						$attvalues['min'] = null;
						$attvalues['max'] = null;
						$attvalues['length'] = null;
						$attvalues['minlen'] = null;
						$attvalues['maxlen'] = null;
						$attvalues['minval'] = null;
						$attvalues['maxval'] = null;
						$attvalues['validhint'] = null;
					} //end if
					//--
					if(!array_key_exists('optional', $v)) {
						$v['optional'] = null;
					} //end if
					switch((string)$v['optional']) {
						case 'all':
						case 'inventory':
						case 'validation':
							$attvalues['optional'] = (string) $v['optional'];
							break;
						default:
							if($displaymode === true) {
								$attvalues['optional'] = '';
							} //end if
					} //end switch
					//--
					if(isset($v['validation']) AND (\Smart::array_size($v['validation']) > 0)) { // validation is array, must pick a value from that array
						$attvalues['validation'] = array();
						foreach($v['validation'] as $kk => $vv) {
							if(\is_array($vv)) {
								$vv['id'] = (string) \trim((string)(isset($vv['id']) ? $vv['id'] : null));
								if((string)$vv['id'] != '') {
									// {{{SYNC-CART-ATT-VALIDATION-ARR}}}
									if($displaymode === true) {
										$attvalues['validation'][(string)$vv['id']] = [
											'adjust-price' 	=> \Smart::format_number_dec((isset($vv['adjust-price']) ? $vv['adjust-price'] : null), 2, '.', ''),
											'hint' 			=> (string) (isset($vv['hint']) ? $vv['hint'] : null),
											'style' 		=> isset($vv['style']) ? ((\Smart::array_size($vv['style']) > 0) ? (array) $vv['style'] : (string) $vv['style']) : '', // mixed
										];
									} else {
										$attvalues['validation'][(string)$vv['id']] = [
											'adjust-price' 	=> \Smart::format_number_dec((isset($vv['adjust-price']) ? $vv['adjust-price'] : null), 2, '.', ''),
											'hint' 			=> (string) (isset($vv['hint']) ? $vv['hint'] : null)
										];
									} //end if else
								} //end if
							} //end if
						} //end foreach
					} else { // can be: integer(+) / decimal(+) / string
						$attvalues['validation'] = (string) (isset($v['validation']) ? $v['validation'] : '');
						if(((string)$attvalues['validation'] == 'integer') OR ((string)$attvalues['validation'] == 'integer+')) {
							$attvalues['min'] = (int) \Smart::format_number_int((isset($v['min']) ? $v['min'] : null), '+');
							$attvalues['max'] = (int) \Smart::format_number_dec((isset($v['max']) ? $v['max'] : null), '+');
						} elseif(((string)$attvalues['validation'] == 'decimal') OR ((string)$attvalues['validation'] == 'decimal+')) {
							$tmp_decimals = (int) (isset($v['decimals']) ? $v['decimals'] : null);
							$attvalues['decimals'] = (int) ((((int)$tmp_decimals > 0) && ((int)$tmp_decimals <= 4)) ? $tmp_decimals : 1);
							$attvalues['min'] = (string) \Smart::format_number_dec((isset($v['min']) ? $v['min'] : null), (int)$tmp_decimals, '.', '');
							$attvalues['max'] = (string) \Smart::format_number_dec((isset($v['max']) ? $v['max'] : null), (int)$tmp_decimals, '.', '');
							$tmp_decimals = null;
						} else { // string
							if(isset($v['length']) AND ((int)$v['length'] > 0)) {
								$attvalues['length'] = (int) $v['length'];
							} else {
								if(isset($v['minlen']) AND ((int)$v['minlen'] > 0)) {
									$attvalues['minlen'] = (int) $v['minlen'];
								} //end if
								if(isset($v['maxlen']) AND ((int)$v['maxlen'] > 0)) {
									$attvalues['maxlen'] = (int) $v['maxlen'];
								} //end if
							} //end if
							if(isset($v['minval']) AND ((string)\trim((string)$v['minval']) != '')) {
								$attvalues['minval'] = (string) \trim((string)$v['minval']);
							} //end if
							if(isset($v['maxval']) AND ((string)\trim((string)$v['maxval']) != '')) {
								$attvalues['maxval'] = (string) \trim((string)$v['maxval']);
							} //end if
						} //end if else
					} //end if
					//--
					if(isset($v['validhint']) AND ((string)\trim((string)$v['validhint']) != '')) {
						$attvalues['validhint'] = (string) \trim((string)$v['validhint']);
					} //end if
					//--
					if($displaymode === true) { // get by select language or non-empty as ???
						if(isset($v['style']) AND \is_array($v['style'])) {
							$attvalues['style'] = (array) $v['style'];
						} elseif(isset($v['style'])) {
							$attvalues['style'] = (string) $v['style'];
						} else {
							$attvalues['style'] = '';
						} //end if else
					} //end if
					//--
					$attributes[(string)$k] = (array) $attvalues;
					//--
				} //end if
			} //end foreach
		} //end if
		//--
		//print_r($attributes); die();
		return (array) $attributes;
		//--
	} //END FUNCTION


	/**
	 * Reset cart Error message.
	 *
	 * @return array
	 */
	public function resetError() {
		//--
		$this->errmsg = '';
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Get cart Error message.
	 *
	 * @return array
	 */
	public function getError() {
		//--
		return (string) $this->errmsg;
		//--
	} //END FUNCTION


	/**
	 * Get all items in cart, as they are stored.
	 *
	 * @return array
	 */
	public function getItems() {
		//--
		if(\is_array($this->items)) {
			foreach($this->items as $key => $items) {
				//--
				if(\is_array($items)) {
					foreach($items as $kk => $item) {
						//--
						$id = (string) $key;
						$attributes = (array) $item['attributes'];
						$data = (array) $item['data'];
						//--
						if(!$this->errmsg) {
							$this->errmsg = (string) $this->validateAtts($id, $data, $attributes);
						} //end if
						//--
					} //end foreach
				} //end if
				//--
			} //end foreach
		} //end if
		//--
		return (array) $this->items;
		//--
	} //END FUNCTION


	/**
	 * Check if the cart is empty.
	 *
	 * @return bool
	 */
	public function isEmpty() {
		//--
		return (bool) empty(\array_filter($this->items));
		//--
	} //END FUNCTION


	/**
	 * Get the total of items in cart.
	 *
	 * @return int
	 */
	public function getNumberOfItems() {
		//--
		$total = 0;
		//--
		if(!\is_array($this->items)) {
			$this->items = array();
		} //end if
		//--
		foreach($this->items as $key => $items) {
			if(\is_array($items)) {
				foreach($items as $kk => $item) {
					$total++;
				} //end foreach
			} //end if
		} //end foreach
		//--
		return (int) $total;
		//--
	} //END FUNCTION


	/**
	 * Calculate item cart hash as SHA3-256/B62 (is designed to be used also in HTML properties).
	 *
	 * @return string
	 */
	public function calculateHash(string $id, array $attributes) : string {
		//--
		$attributes = (array) \Smart::array_sort($attributes, 'ksort'); // sort by key
		//--
		$uuid = [];
		$uuid[] = '{';
		$uuid[] = (string) \trim((string)\Smart::normalize_spaces((string)$id));
		foreach($attributes as $key => $val) {
			$uuid[] = (string) '['.\trim((string)\Smart::normalize_spaces((string)$key))."\t".\trim((string)\Smart::normalize_spaces((string)$val)).']';
		} //end if
		$uuid[] = '}';
		$uuid = (string) \trim((string)\implode((string)"\n", (array)$uuid));
		$uuid = (string) \Smart::base_from_hex_convert((string)\SmartHashCrypto::sh3a256((string)$uuid), 62);
		//--
		return (string) $uuid;
		//--
	} //END FUNCTION


	/**
	 * Check if a item exist in cart.
	 *
	 * @param string $id
	 * @param array  $attributes
	 *
	 * @return bool
	 */
	public function isItemInCart($id, $attributes) {
		//--
		if($this->isEmpty()) {
			return false;
		} //end if
		//--
		$id = (string) $id;
		if((string)\trim((string)$id) == '') {
			return false;
		} //end if
		//--
		if(\is_array($attributes)) { // must test is array not array size > 0
			$hash = (string) $this->calculateHash((string)$id, (array)$attributes);
		} else {
			$hash = (string) $attributes;
			$attributes = [];
		} //end if else
		if((string)\trim((string)$hash) == '') {
			return false;
		} //end if
		//--
		$all_items = (array) $this->getItems();
		foreach($all_items as $sku => $items) {
			foreach((array)$items as $key => $item) {
				if((string)$item['id'] == (string)$id) {
					if((string)$item['hash'] == (string)$hash) {
						return true;
					} //end if
				} //end if
			} //end foreach
		} //end foreach
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Destroy cart.
	 */
	public function destroy() {
		//--
		$this->items = [];
		//--
		if($this->useCookie) {
			return (bool) \SmartUtils::unset_cookie((string)$this->cartId);
		} else {
			return (bool) \SmartSession::unsets((string)$this->cartId);
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Remove all items from cart.
	 */
	public function clear() {
		//--
		$this->items = [];
		//--
		return (bool) $this->write();
		//--
	} //END FUNCTION


	/*
	public function getValueByLanguage($value, $empty='???') {
		//--
		$empty = (string) \trim((string)$empty);
		//--
		if(!\is_array($value)) {
			//--
			$text = (string) $value;
			//--
		} else {
			//--
			$value = (array) $value;
			//--
			$language = (string) \SmartTextTranslations::getLanguage();
			$text = (string) $value[(string)$language];
			if(!$text) {
				if(!\SmartTextTranslations::isDefaultLanguage()) {
					$deflanguage = (string) \SmartTextTranslations::getDefaultLanguage();
					$text = (string) $value[(string)$deflanguage];
				} //end if
			} //end if
			if(!$text) { // fallback to any language available
				foreach($value as $k => $v) {
					if(\strlen((string)$k) == 2) {
						if((string)\trim((string)$v) != '') {
							$text = (string) $v;
							break;
						} //end if
					} //end if
				} //end if
			} //end if
			//--
		} //end if else
		//--
		if((string)$empty != '') {
			if((string)\trim((string)$text) == '') {
				$text = (string) $empty;
			} //end if
		} //end if
		//--
		return (string) $text;
		//--
	} //END FUNCTION
	*/


	//===== PRIVATES


	/**
	 * Private Add item to cart, with many options.
	 *
	 * @param string $id
	 * @param array  $attributes
	 * @param int    $quantity
	 * @param string $replace (empty or the replace hash)
	 * @param int    $position
	 *
	 * @return bool
	 */
	private function doAdd($product, $attributes, $quantity=1, $replace='', $position=0) {
		//--
		if(($this->cartMaxItem > 0) AND ($this->getNumberOfItems() >= $this->cartMaxItem)) {
			if(!$this->errmsg) {
				$this->errmsg = 'Max Cart Items is: '.(int)$this->cartMaxItem;
			} //end if
			return false;
		} //end if
		//--
		if(\Smart::array_size($product) <= 0) {
			return false;
		} //end if
		//--
		$id = (string) $product['id'];
		if((string)\trim((string)$id) == '') {
			return false;
		} //end if
		if(\strlen((string)$id) > 25) { // id must be between 1 and 25 chars
			return false;
		} //end if else
		//--
		if(!\is_array($attributes)) {
			$attributes = array();
		} //end if
		$raw_atts = (array) $attributes;
		$attributes = array();
		foreach($raw_atts as $key => $val) {
			$key = (string) \trim((string)$key);
			$val = (string) \trim((string)$val);
			if((string)$key != '') {
				if(\strlen((string)$val) <= 255) { // {{{SYNC-ECART-ATT-MAX-LEN}}}
					$attributes[(string)$key] = (string) $val; // allow max 255 chars per attribute to avoid large indexes
				} //end if
			} //end if
		} // end foreach
		$raw_atts = array();
		//--
		$position = (int) $position;
		if(($position <= 0)) {
			$position = (int) ($this->getNumberOfItems() + 1);
		} //end if
		//--
		if(!\is_array($product['pak'])) {
			$product['pak'] = array();
		} //end if
		//--
		$data = array();
		$data['type'] 		= (string) $product['type'];
		$data['name'] 		= (string) $product['name'];
		$data['pak'] 		= array(
			'um' 		=> (string) $product['pak']['um'],
			'umtype' 	=> (string) $product['pak']['umtype'],
			'umerg' 	=> (string) ((\strpos((string)$product['pak']['umerg'], 'formula:') !== 0) ? \Smart::format_number_dec($product['pak']['umerg'], 2, '.', '') : $product['pak']['umerg']), // can be formula string or number
			'ummin' 	=> (string) \Smart::format_number_dec($product['pak']['ummin'], 2, '.', '')
		);
		$data['currency'] 	= (string) $product['currency'];  // the original currency
		if($this->cartNoPriceMode === true) {
			$data['price'] 		= '0.00';
			$data['tax'] 		= '0.00';
			$data['discounts'] 	= array();
		} else {
			$data['price'] 		= (string) \Smart::format_number_dec($product['price'], 2, '.', ''); // the original price
			$data['tax'] 		= (string) \Smart::format_number_dec($product['tax'], 2, '.', ''); // the original sales tax
			$data['discounts'] 	= array();
			if(isset($product['discounts']) AND \is_array($product['discounts'])) {
				foreach((array)$product['discounts'] as $key => $val) {
					$key = (string) \trim((string)$key);
					$xval = (string) $this->fixPercentDiscountAsNumeric((string)$val);
					if(((string)$key != '') AND ($xval > 0) AND ($xval < 100)) {
						$data['discounts'][(string)$key] = (string) $xval.'%';
					} //end if
				} //end foreach
			} //end if
		} //end if else
		if((string)$replace != '') {
			$data['atts'] 		= (array)  $product['atts'];
		} else {
			$data['atts'] 		= (array)  $this->getItemAttributes(isset($product['attributes']) ? $product['attributes'] : null);
		} //end if else
		//-- validate data before others
		$this->errmsg = (string) $this->validateProps($id, $data);
		if($this->errmsg) {
			return false;
		} //end if
		//-- validate attributes before fix price and qty
		$this->errmsg = (string) $this->validateAtts($id, $data, $attributes);
		if($this->errmsg) {
			return false;
		} //end if
		//-- fix price
		$fix_currency = (string) $data['currency']; // the currency used in cart
		$fix_price = (string) $this->fixPrice((array)$data, (array)$attributes); // this is price with adjustements or converted to the cart currency
		$fix_tax = (string) $data['tax']; // the tax used in cart
		if((!preg_match('/^[A-Z\$]{3}$/', (string)$fix_currency)) OR ((float)$fix_price < 0) OR ((float)$fix_price > 999999999) OR ((float)$fix_tax < 0) OR ((float)$fix_tax >= 100)) {
			return false;
		} //end if
		//-- fix qty :: {{{SYNC-CART-QTY-TYPE}}}
		$fix_qtyerg = (string) $this->fixErgQty((string)$data['pak']['umerg'], (array)$attributes); // can be calculate:(expr) or float
		$quantity = $this->fixQty(
			(float)  $quantity,
			(string) $data['pak']['umtype'],
			(float)  $fix_qtyerg,
			(float)  $data['pak']['ummin']
		);
		if($quantity <= 0) {
			return false;
		} //end if
		//--
		$fix_exchrate = (string) \Smart::format_number_dec($this->getCurrencyExchangeRate($fix_currency), 2, '.', '');
		//--
		$hash = $this->calculateHash((string)$id, (array)$attributes);
		//--
		if((string)$replace != '') {
			if(isset($this->items[$id]) AND \is_array($this->items[$id])) {
				foreach($this->items[$id] as $index => $item) {
					if((string)$item['hash'] == (string)$replace) {
						unset($this->items[$id][$index]);
					} //end if
				} //end foreach
			} //end if
		} else {
			if(isset($this->items[$id]) AND \is_array($this->items[$id])) {
				foreach($this->items[$id] as $index => $item) {
					if((string)$item['hash'] == (string)$hash) {
						if(!\array_key_exists('quantity', $this->items[$id][$index])) {
							$this->items[$id][$index]['quantity'] = 0;
						} //end if
						$this->items[$id][$index]['quantity'] += $quantity;
						$this->items[$id][$index]['quantity'] = ($this->itemMaxQuantity < $this->items[$id][$index]['quantity'] && $this->itemMaxQuantity != 0) ? $this->itemMaxQuantity : $this->items[$id][$index]['quantity'];
						return (bool) $this->write();
					} //end if
				} //end foreach
			} //end if
		} //end if
		//--
		$item_key = (string) \Smart::base_from_hex_convert((string)\SmartHashCrypto::sha1((string)$hash), 36);
		$this->items[$id][(string)$item_key] = [
			// {{{SYNC-ECART-ITEM-PROPS}}}
			'dtime'      => (string) \date('Y-m-d H:i:s O'),
			'hash'       => (string) $hash,
			'id'         => (string) $id,
			'attributes' => (array)  $attributes,
			'quantity'   => (string) \Smart::format_number_dec((($this->itemMaxQuantity < $quantity && $this->itemMaxQuantity != 0) ? $this->itemMaxQuantity : $quantity), 2, '.', ''),
			'qtyerg'     => (string) $fix_qtyerg,
			'umtype'     => (string) ($data['pak']['umtype'] ?? null),
			'um'         => (string) ($data['pak']['um'] ?? null),
			'type'       => (string) ($data['type'] ?? null),
			'currency'   => (string) $this->cartCurrency,
			'price'      => (string) \Smart::format_number_dec(($fix_price * $fix_exchrate), 2, '.', ''), // {{{SYNC-CALC-PRICE-BY-EXCHRATE}}} this may have modified by sales operator in non-customer cart modes
			'tax'        => (string) $fix_tax, // tax % (ex: 19)
			'price_'     => (string) '', // keep original calculated price using exchange rate if using a custom price (if this is non empty string it means the price is custom !)
			'_price'     => (string) $fix_price, // keep original price as calculated by attributes in the original item _currency
			'discount_'  => (string) (isset($data['discounts'][(string)$this->cartDiscountLevel]) ? $data['discounts'][(string)$this->cartDiscountLevel] : null), // keep original discount as percent
			'_discount'  => (string) \Smart::format_number_dec(($this->fixPercentDiscountAsNumeric((isset($data['discounts'][(string)$this->cartDiscountLevel]) ? $data['discounts'][(string)$this->cartDiscountLevel] : null)) / 100), 2, '.', ''), // {{{SYNC-CART-DISCOUNT-NUMERIC-DECIMAL4}}} ; must use 4 decimals to can use by ex: 10.55%
			'_currency'  => (string) $fix_currency, // keep original item currency
			'_exchrate'  => (string) $fix_exchrate, // the exchage rate as: item-currency / cart-currency
			'data'       => (array)  $data,
			'position'   => (int)    $position
		];
		//--
		return (bool) $this->write();
		//--
	} //END FUNCTION


	/**
	 * Private Replace item with new attributes, keeping quantity.
	 * Does not make sense for items with no attributes
	 *
	 * @param string $id					ID
	 * @param string $hash					Old Hash
	 * @param int    $quantity				Old Quantity
	 * @param array  $attributes			New Attributes
	 *
	 * @return bool
	 */
	public function doReplace($id, $hash, $quantity, $attributes) {
		//--
		if($this->isEmpty()) {
			return false;
		} //end if
		//--
		$id = (string) $id;
		if((string)\trim((string)$id) == '') {
			return false;
		} //end if
		//--
		$hash = (string) $hash;
		if((string)\trim((string)$hash) == '') {
			return false;
		} //end if
		//--
		$quantity = (float) $quantity;
		if($quantity <= 0) {
			return false;
		} //end if
		//--
		if(!\is_array($attributes)) {
			return false;
		} //end if
		//--
		$all_items = (array) $this->getItems();
		foreach($all_items as $sku => $items) {
			foreach((array)$items as $key => $item) {
				if((string)$item['id'] == (string)$id) {
					if((string)$item['hash'] == (string)$hash) {
						if((array)$item['attributes'] === (array)$attributes) {
							$this->errmsg = ''; // FIX: avoid transmit err message for other items in cart in this case
							return true; // item not changed
						} elseif(\is_array($item['data'])) { // only if attributes changed
							$this->errmsg = ''; // FIX: clear err msg before validating to avoid transmit err message for other items in cart in this case
							return (bool) $this->doAdd( // here the err msg will come from validating this item only
								(array)  \array_merge(['id' => (string)$item['id']], (array)$item['data']),
								(array)  $attributes,
								(string) $quantity,
								(string) $hash, // replace
								(int)    $item['position'] // keep position
							);
						} //end if
					} //end if
				} //end if
			} //end foreach
		} //end foreach
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Private Update item in cart, with more options
	 *
	 * @param string        $id
	 * @param array/string  $attributes
	 * @param int           $quantity
	 * @param int           $position
	 * @param bool          $write (on multiupdate this should be set to false and let the multiupdate commit the write ...)
	 *
	 * @return bool
	 */
	private function doUpdate($id, $attributes, $quantity=1, $position=-1, $price='', $write=true) {
		//--
		$id = (string) $id;
		if((string)\trim((string)$id) == '') {
			return false;
		} //end if
		//--
		if($quantity <= 0) {
			$quantity = 0.0001;
		} //end if
		//--
		$position = (int) $position;
		//--
		if((string)$this->getCartMode() != 'customer') {
			if((string)$price != '') {
				if((float)$price >= 0) {
					$price = (string) \Smart::format_number_dec($price, 2, '.', '');
				} //end if
			} //end if
		} else {
			$price = '';
		} //end if
		//--
		if($this->cartNoPriceMode === true) {
			$price = '0.00';
		} //end if
		//--
		if(\Smart::array_size($this->items[(string)$id]) > 0) {
			if(\is_array($attributes)) { // must test is array not array size > 0
				$hash = (string) $this->calculateHash((string)$id, (array)$attributes);
			} else {
				$hash = (string) $attributes;
				$attributes = [];
			} //end if else
			if((string)\trim((string)$hash) == '') {
				return false;
			} //end if
			foreach($this->items[$id] as $index => $item) {
				if(\is_array($item)) {
					//--
					if((string)$item['hash'] == (string)$hash) {
						//--
						$data = (array) $item['data'];
						//--
						$this->errmsg = (string) $this->validateAtts($id, $item['data'], $item['attributes']);
						if($this->errmsg) {
							return false;
						} //end if
						//-- fix qty :: {{{SYNC-CART-QTY-TYPE}}}
						$quantity = $this->fixQty(
							(float)  $quantity,
							(string) $data['pak']['umtype'],
							(float)  $item['qtyerg'],
							(float)  $data['pak']['ummin']
						);
						if($quantity <= 0) {
							return true;
						} //end if
						//--
						if($position >= 0) {
							$this->items[$id][$index]['position'] = $position;
						} //end if
						$this->items[$id][$index]['quantity'] = $quantity;
						$this->items[$id][$index]['quantity'] = ($this->itemMaxQuantity < $this->items[$id][$index]['quantity'] && $this->itemMaxQuantity != 0) ? $this->itemMaxQuantity : $this->items[$id][$index]['quantity'];
						//--
						$this->items[$id][$index]['discount_'] = (string) (isset($data['discounts'][(string)$this->cartDiscountLevel]) ? $data['discounts'][(string)$this->cartDiscountLevel] : null);
						$this->items[$id][$index]['_discount'] = (string) \Smart::format_number_dec(($this->fixPercentDiscountAsNumeric((isset($data['discounts'][(string)$this->cartDiscountLevel]) ? $data['discounts'][(string)$this->cartDiscountLevel] : null)) / 100), 2, '.', ''); // {{{SYNC-CART-DISCOUNT-NUMERIC-DECIMAL4}}} ; must use 4 decimals to can use by ex: 10.55%
						//--
						if((string)$price != '') {
							if($price >= 0) {
								// \Smart::log_notice('Price='.$price.' / Price_='.$this->items[$id][$index]['price_']);
								if((string)$price == (string)$this->items[$id][$index]['price_']) {
									$this->items[$id][$index]['price'] = (string) \Smart::format_number_dec(($this->items[$id][$index]['_price'] * $this->items[$id][$index]['_exchrate']), 2, '.', ''); // {{{SYNC-CALC-PRICE-BY-EXCHRATE}}} ; restore
									$this->items[$id][$index]['price_'] = ''; // reset
								} elseif((string)$price != (string)$this->items[$id][$index]['price']) {
									$this->items[$id][$index]['price_'] = (string) \Smart::format_number_dec(($this->items[$id][$index]['_price'] * $this->items[$id][$index]['_exchrate']), 2, '.', ''); // {{{SYNC-CALC-PRICE-BY-EXCHRATE}}} ; set
									$this->items[$id][$index]['price'] = (string) $price; // use a custom  price
								} //end if else
							} //end if
						} //end if
						//--
						if($write) {
							return (bool) $this->write();
						} else {
							return true;
						} //end if else
						//--
					} //end if
					//--
				} //end if
			} //end foreach
		} //end if
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Public Multi-Update items in cart.
	 *
	 * @param array $arr[ hash => [id, qty, att], ... ]
	 *
	 * @return bool
	 */
	private function doMultiUpdate($arr) {
		//--
		if(\Smart::array_size($arr) > 0) {
			//--
			$updt = 0;
			//--
			foreach($arr as $key => $val) {
				//--
				if(($key) AND (\Smart::array_size($val) > 0)) {
					//--
					$test = false;
					//--
					$item_hash = (string) $key;
					$item_id   = (string) $val['id'];
					$item_qty  = (string) $val['qty'];
					//--
					if((string)$this->getCartMode() != 'customer') {
						$item_price = (string) $val['price'];
					} else {
						$item_price = ''; // keep original price
					} //end if
					//--
					$test = (bool) $this->doUpdate(
						(string) $item_id,
						(string) $item_hash,
						(float)  $item_qty,
						(int)    ($updt + 1),
						(string) $item_price,
						(bool)   false
					);
					//--
					if(!$test) {
						return false;
					} //end if
					//--
					$updt++;
					//--
				} //end if
				//--
			} //end foreach
			//--
			if($updt) {
				return (bool) $this->write();
			} else {
				return false;
			} //end if else
			//--
		} //end if
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Private Remove item from cart.
	 *
	 * @param string $id
	 * @param array/string  $attributes
	 *
	 * @return bool
	 */
	public function doRemove($id, $attributes) {
		//--
		$id = (string) $id;
		//--
		if(!\is_array($this->items[$id])) {
			return false;
		} //end if
		//--
		if(\is_array($attributes)) { // must test is array not array size > 0
			if(empty($attributes)) {
				unset($this->items[$id]);
				$this->write();
				return true;
			} //end if
			$hash = $this->calculateHash((string)$id, (array)$attributes);
		} else {
			$hash = (string) $attributes;
			$attributes = [];
		} //end if else
		//--
		if((string)$hash == '') {
			return false;
		} //end if
		//--
		foreach($this->items[$id] as $index => $item) {
			if((string)$item['hash'] == (string)$hash) {
				unset($this->items[$id][$index]);
				return (bool) $this->write();
			} //end if
		} //end foreach
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Read items from cart session storage.
	 */
	private function read() {
		//--
		if($this->useCookie) {
			$this->items = \Smart::json_decode(\SmartUtils::data_unarchive(\SmartFrameworkRegistry::getCookieVar((string)$this->cartId)));
		} else { // session
			$this->items = \SmartSession::get((string)$this->cartId);
		} //end if else
		//--
		if(!\is_array($this->items)) {
			$this->items = [];
		} //end if
		//--
		return (array) $this->items;
		//--
	} //END FUNCTION


	/**
	 * Write changes into cart session.
	 */
	private function write() {
		//--
		if($this->useCookie) {
			$data = (string) \SmartUtils::data_archive((string)\Smart::json_encode((array)$this->items));
			$size = (int) \strlen((string)$data);
			if((int)$size > (int)\SmartUtils::cookie_size_max()) {
				if(!$this->errmsg) {
					$this->errmsg = 'Max Cart Size limit reached ('.(int)$size.'/'.(int)\SmartUtils::cookie_size_max().')';
				} //end if
				return false;
			} //end if
			return (bool) \SmartUtils::set_cookie($this->cartId, (string)$data, 604800);
		} else {
			return (bool) \SmartSession::set((string)$this->cartId, (array)$this->items);
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Validate item props
	 */
	private function validateProps($id, $data) {
		//--
		$id = (string) $id;
		$data = (array) $data;
		//--
		if(\Smart::array_size($data) <= 0) {
			return '(011) Empty Item Props: '.$id;
		} //end if
		//--
		if(((string)$data['type'] != 'p') AND ((string)$data['type'] != 's')) {
			return '(012) Invalid Item Props / Type: '.$id;
		} //end if
		//--
		if((string)\trim((string)$data['name']) == '') {
			return '(013) Invalid Item Props / Name: '.$id;
		} //end if
		if(\strlen((string)$data['name']) > 150) {
			return '(014) Invalid Item Props / Name is too long: '.$id;
		} //end if
		//--
		if(\Smart::array_size($data['pak']) <= 0) {
			return '(021) Empty Item Props / Package: '.$id;
		} //end if
		if((string)\trim((string)$data['pak']['um']) == '') {
			return '(022) Empty Item Props / Package / UM: '.$id;
		} //end if
		if(\strlen((string)$data['pak']['um']) > 15) {
			return '(023) Invalid Item Props / Package / UM is too long: '.$id;
		} //end if
		if(((string)$data['pak']['umtype'] != 'int') AND ((string)$data['pak']['umtype'] != 'dec')) {
			return '(024) Invalid Item Props / Package / UMType: '.$id;
		} //end if
		if((\strpos((string)$data['pak']['umerg'], 'formula:') !== 0) AND ((float)$data['pak']['umerg'] < 0)) {
			return '(025) Invalid Item Props / Package / UMErg: '.$id;
		} //end if
		if((float)$data['pak']['ummin'] < 0) {
			return '(026) Invalid Item Props / Package / UMMin: '.$id;
		} //end if
		//--
		if(!\preg_match('/^[A-Z\$]{3}$/', (string)$data['currency'])) { // {{{SYNC-VALIDATE-CART-CURRENCY}}}
			return '(027) Invalid Item Currency format: '.$id;
		} //end if
		if(((float)$data['price'] < 0) OR ((float)$data['price'] > 999999999)) {
			return '(028) Invalid Item Price: '.$id;
		} //end if
		if(((float)$data['tax'] < 0) OR ((float)$data['tax'] >= 100)) {
			return '(029) Invalid Item Tax: '.$id;
		} //end if
		if(!\is_array($data['discounts'])) {
			return '(030) Invalid Item Discounts format: '.$id;
		} //end if
		//--
		if(!\is_array($data['atts'])) {
			return '(031) Invalid Item Attributes format: '.$id;
		} //end if
		//--
		return '';
		//--
	} //END FUNCTION


	/**
	 * Validate item attributes
	 */
	private function validateAtts($id, $data, $attributes) {
		//--
		$id = (string) $id;
		$data = (array) $data;
		$attributes = (array) $attributes;
		//--
		if(!\is_array($data['atts'])) {
			return '(100) Item Attribute Invalid Format: '.$id;
		} //end if
		//--
		foreach($data['atts'] as $key => $val) {
			//--
			$validhint = isset($val['validhint']) ? $val['validhint'] : '';
			if(!\Smart::is_nscalar($validhint)) {
				$validhint = ''; // if array get by lang ?
			} //end if
			//--
			if((!\is_array($val)) OR ((string)\trim((string)$key) == '')) {
				//--
				return '(101) Item Attribute Invalid Format: '.$id.' / '.$key;
				//--
			} else {
				//--
				$tmp_name = (string) \trim((string)$val['name']);
				if((string)$tmp_name == '') {
					$tmp_name = (string) \trim((string)$key);
				} //end if
				//--
				$tmp_val = (string) \trim((string)$attributes[(string)$key]);
				//--
				$is_optional = false;
				if(!\array_key_exists('optional', $val)) {
					$val['optional'] = null;
				} //end if
				switch((string)$val['optional']) {
					case 'validation':
						if(\is_array($val['validation'])) {
							return '(102) Item Attribute is Empty: '.$id.' / '.$tmp_name;
						} elseif(\strpos((string)$val['validation'], 'regex:') !== 0) {
							return '(103) Item Attribute is Empty: '.$id.' / '.$tmp_name;
						} //end if else
						break;
					case 'all':
						$is_optional = true;
						break;
					case 'inventory':
						if((string)$this->getCartMode() == 'inventory') {
							if((string)$tmp_val == '') {
								return '(104) Item Attribute is Empty: '.$id.' / '.$tmp_name;
							} //end if
						} else {
							$is_optional = true;
						} //end if
						break;
					default:
						if((string)$tmp_val == '') {
							return '(105) Item Attribute is Empty: '.$id.' / '.$tmp_name;
						} //end if
				} //end switch
				//--
				if(($is_optional === false) OR (($is_optional === true) AND ((string)$tmp_val != ''))) {
					//print_r($val);
					//--
					if(\is_array($val['validation'])) {
						$found = false;
						foreach($val['validation'] as $kk => $vv) { // {{{SYNC-CART-ATT-VALIDATION-ARR}}}
							if(((string)\trim((string)$kk) != '') AND \is_array($vv)) {
								if((string)$kk == (string)$tmp_val) {
									$found = true;
									break;
								} //end if
							} //end if
						} //end foreach
						if(!$found) {
							return '(111) Item Attribute Contains an Invalid Value (not in the list): '.$id.' / '.$tmp_name.': `'.$tmp_val.'`';
						} //end if
					} elseif(\strpos((string)$val['validation'], 'regex:') === 0) {
						$regex = (string) \substr((string)$val['validation'], (int)\strlen('regex:'));
						if($regex) {
							foreach($attributes as $akey => $aval) {
								$regex = (string) \str_replace('[[[@'.$akey.']]]', (string)\preg_quote($aval), (string)$regex);
							} //end foreach
						} //end if
						//\Smart::log_notice($regex);
						if(!$regex) {
							return '(112) Item Attribute Contains an Invalid Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Empty Regex';
						} //end if
						if(!\preg_match((string)$regex, (string)$tmp_val)) {
							return '(113) Item Attribute Contains an Invalid Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Regex: `'.$regex.'`'.($validhint ? ' # '.$validhint : '');
						} //end if
					} elseif(\strpos((string)$val['validation'], 'serial:') === 0) {
						// Reserved ERR: 114, 115
					} else {
						switch((string)$val['validation']) {
							case 'integer+':
								if(!\preg_match('/^([0-9]+)$/', (string)$tmp_val)) {
									return '(116) Item Attribute Contains an Invalid Integer+ Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'`';
								} //end if
								break;
							case 'integer':
								if(!\preg_match('/^((\-)?[0-9]+)$/', (string)$tmp_val)) {
									return '(117) Item Attribute Contains an Invalid Integer Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'`';
								} //end if
								break;
							case 'decimal+':
								if(!\preg_match('/^([0-9\.]+)$/', (string)$tmp_val)) {
									return '(118) Item Attribute Contains an Invalid Decimal+ Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'`';
								} //end if
								break;
							case 'decimal':
								if(!\preg_match('/^((\-)?[0-9\.]+)$/', (string)$tmp_val)) {
									return '(119) Item Attribute Contains an Invalid Decimal Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'`';
								} //end if
								break;
						} //end switch
					} //end if else
					//--
					if(!\is_array($val['validation'])) {
						if(((string)$val['validation'] == 'decimal+') OR ((string)$val['validation'] == 'decimal')) {
							if(((int)$val['decimals'] > 0) AND ((int)$val['decimals'] <= 4)) {
								if((int)\strrpos((string)\strrev((string)$tmp_val), '.') != (int)$val['decimals']) {
									return '(120) Item Attribute Must Contain '.(int)$val['decimals'].' Decimals: '.$id.' / '.$tmp_name.': `'.$tmp_val.'`';
								} //end if
							} //end if
						} //end if
					} //end if
					//--
					if(isset($val['min']) AND $val['min']) {
						if($tmp_val < $val['min']) {
							return '(121) Item Attribute Contains an Invalid Min. Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Min. is: `'.$val['min'].'`';
						} //end if
					} //end if
					if(isset($val['max']) AND $val['max']) {
						if($tmp_val > $val['max']) {
							return '(122) Item Attribute Contains an Invalid Max. Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Max. is: `'.$val['max'].'`';
						} //end if
					} //end if
					if(isset($val['length']) AND ((int)$val['length'])) {
						if((int)\strlen((string)$tmp_val) != (int)$val['length']) {
							return '(123) Item Attribute Contains an Invalid Length Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Length is: `'.(int)$val['length'].'`';
						} //end if
					} else {
						if(isset($val['minlen']) AND ((int)$val['minlen'])) {
							if((int)\strlen((string)$tmp_val) < (int)$val['minlen']) {
								return '(124) Item Attribute Contains an Invalid Min.Length Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Min.Length is: `'.(int)$val['minlen'].'`';
							} //end if
						} //end if
						if(isset($val['maxlen']) AND ((int)$val['maxlen'])) {
							if((int)\strlen((string)$tmp_val) > (int)$val['maxlen']) {
								return '(125) Item Attribute Contains an Invalid Max.Length Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Max.Length is: `'.(int)$val['maxlen'].'`';
							} //end if
						} //end if
					} //end if
					//-- aaa: !? check minval / maxval if inventory or sales ?? if((string)$this->getCartMode() != 'customer') {
					if(isset($val['minval']) AND ((string)$val['minval'] != '')) {
						if(\strpos((string)$val['minval'], 'date:') === 0) {
							$val['minval'] = \date('Y-m-d', @\strtotime((string)substr((string)$val['minval'], (int)\strlen('date:')), @\strtotime((string)$val['@ref-val'])));
						} elseif(\strpos((string)$val['minval'], 'time:') === 0) {
							$val['minval'] = \date('HH:ii', @\strtotime((string)substr((string)$val['minval'], (int)\strlen('time:')), @\strtotime((string)$val['@ref-val'])));
						} //end if
						if((string)$tmp_val < (string)$val['minval']) {
							return '(126) Item Attribute Contains an Invalid Min.Val Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Min.Val is: `'.(string)$val['minval'].'`';
						} //end if
					} //end if
					if(isset($val['maxval']) AND ((string)$val['maxval'] != '')) {
						if(\strpos((string)$val['maxval'], 'date:') === 0) {
							$val['maxval'] = \date('Y-m-d', @\strtotime((string)substr((string)$val['maxval'], (int)\strlen('date:')), @\strtotime((string)$val['@ref-val'])));
						} elseif(\strpos((string)$val['maxval'], 'time:') === 0) {
							$val['maxval'] = \date('HH:ii', @\strtotime((string)substr((string)$val['maxval'], (int)\strlen('time:')), @\strtotime((string)$val['@ref-val'])));
						} //end if
						if((string)$tmp_val > (string)$val['maxval']) {
							return '(127) Item Attribute Contains an Invalid Max.Val Value: '.$id.' / '.$tmp_name.': `'.$tmp_val.'` ; Max.Val is: `'.(string)$val['maxval'].'`';
						} //end if
					} //end if
					//--
				} //end if
				//--
				if((int)\strlen((string)$tmp_val) > 255) {
					return '(128) Item Attribute is Too Large (must be <= 255 characters)'; // {{{SYNC-ECART-ATT-MAX-LEN}}}
				} //end if
				//--
			} //end if
			//--
		} //end foreach
		//--
		return '';
		//--
	} //END FUNCTION


	/**
	 * Calculate Price by Attributes
	 */
	private function fixPrice($data, $attributes) {
		//--
		$data = (array) $data;
		$attributes = (array) $attributes;
		//--
		$price = (float) $data['price'];
		//--
		if(\Smart::array_size($data['atts']) > 0) {
			foreach($attributes as $key => $val) {
				if((string)\trim((string)$key) != '') {
					if((string)\trim((string)$val) != '') {
						if(\Smart::array_size($data['atts'][(string)$key]) > 0) {
							if(\Smart::array_size($data['atts'][(string)$key]['validation']) > 0) {
								if(\Smart::array_size($data['atts'][(string)$key]['validation'][(string)$val]) > 0) {
									$tmp_adjust = (string) \Smart::format_number_dec((float)$data['atts'][(string)$key]['validation'][(string)$val]['adjust-price'], 2, '.', '');
									if($tmp_adjust != 0) {
										$price += (float) $tmp_adjust;
									} //end if
									$tmp_adjust = 0;
								} //end if
							} //end if
						} //end if
					} //end if
				} //end if
			} //end foreach
		} //end if
		//--
		if($price < 0) {
			return 0;
		} //end if
		//--
		return (string) \Smart::format_number_dec($price, 2, '.', '');
		//--
	} //END FUNCTION


	/**
	 * Calculate Erg.Quantity by Attributes
	 */
	private function fixErgQty($y_qty_erg, $attributes) {
		//--
		$y_qty_erg = (string) \trim((string)$y_qty_erg);
		$attributes = (array) $attributes;
		//--
		$qty_erg = (string) $y_qty_erg;
		//--
		if((string)$qty_erg == '') {
			return 0;
		} //end if
		//--
		if(\strpos((string)$qty_erg, 'formula:') !== 0) {
			return (float) $qty_erg;
		} //end if
		//--
		$qty_erg = (string) \substr((string)$qty_erg, (int)\strlen('formula:'));
		if(!$qty_erg) {
			return 0;
		} //end if
		foreach($attributes as $akey => $aval) {
			$qty_erg = (string) \str_replace('[[[@'.$akey.']]]', (float)$aval, (string)$qty_erg);
		} //end foreach
		if(!$qty_erg) {
			return 0;
		} //end if
		//--
		if(!\class_exists('\\PHPMathParser\\Math')) {
			throw new \Exception('PHPMathParser/Math class is missing ...');
		} //end if
		$math = new \PHPMathParser\Math();
		$result = 0;
		try {
			$result = (float) $math->evaluate((string)$qty_erg);
		} catch(\Exception $e) {
			\Smart::log_warning(__METHOD__.'() Math Formula Evaluate Error: '.$e->getMessage().' # `'.$y_qty_erg.'` # `'.$qty_erg.'`');
			return 0;
		} //end try catch
		//--
		return (string) \Smart::format_number_dec((float)$result, 2, '.', '');
		//--
	} //END FUNCTION


	/**
	 * Calculate Quantity by Attributes
	 */
	private function fixQty($y_qty_cart, $y_qty_type, $y_qty_erg, $y_qty_min) { // {{{SYNC-CART-QTY-TYPE}}}
		//--
		if((string)$y_qty_type == 'dec') {
			//--
			$y_qty_cart = (float) $y_qty_cart;
			$y_qty_erg = (float) $y_qty_erg;
			$y_qty_min = (float) $y_qty_min;
			//--
		} else { // int
			//--
			$y_qty_cart = (int) \ceil((float)$y_qty_cart);
			$y_qty_erg = (int) \ceil((float)$y_qty_erg);
			$y_qty_min = (int) \ceil((float)$y_qty_min);
			//--
		} //end if else
		//-- fix positive qty
		if($y_qty_cart < 0) {
			return 0;
		} //end if
		//-- fix min qty
		if($y_qty_min > 0) {
			if($y_qty_cart < $y_qty_min) {
				$y_qty_cart = $y_qty_min;
			} //end if
		} //end if
		//-- fix erg qty
		if($y_qty_erg > 0) {
			if($y_qty_cart > $y_qty_erg) {
				$y_qty_cart = (float) (\ceil($y_qty_cart / $y_qty_erg) * $y_qty_erg);
			} else {
				$y_qty_cart = $y_qty_erg;
			} //end if else
		} //end if
		//-- fix negative qty
		if($y_qty_cart < 0) {
			return 0;
		} //end if
		//-- format by type
		if((string)$y_qty_type == 'dec') {
			$y_qty_cart = (string) \Smart::format_number_dec((float)$y_qty_cart, 2, '.', '');
		} else { // int
			$y_qty_cart = (string) \Smart::format_number_int((int)\ceil((float)$y_qty_cart));
		} //end if
		//-- cast as string to preserve zeroes
		return (string) $y_qty_cart;
		//--
	} //END FUNCTION


	private function fixPercentDiscountAsNumeric($y_discount_with_percent) {
		//--
		$y_discount_with_percent = (string) \Smart::normalize_spaces((string)$y_discount_with_percent);
		$y_discount_with_percent = (string) \str_replace([' ', '%'], '', (string)$y_discount_with_percent);
		$y_discount_with_percent = (string) \trim((string)$y_discount_with_percent);
		//--
		return (string) \Smart::format_number_dec((string)$y_discount_with_percent, 2, '.', '');
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
