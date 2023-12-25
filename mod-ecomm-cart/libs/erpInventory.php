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


//####
// WARNING:
// Float Point Problem: as $a=1.3333; $b=1.6666; $c=2.9999; $d=($a+$b); if($d != $c) { echo 'Float Inequality'; }
// To fix it, always use Smart::format_number_dec($number, $decimals, '.', '');
//####

//------
// DocTypes: + Cancel
//------
//  [+] 	in = Inventory Init
//  [+] 	sp = Supplier Purchase
//  [-] 	sr = Supplier Return
//  [-]		cs = Customer Sale
//  [+] 	cr = Customer Return
// [+/-]	zx = Inventory Adjustments / Transfers / Manufacture
//------
// {{{SYNC-QUER-STOCKLOG-ARCH-SUMS}}}
//	in			-> tvalue
//	buy			-> tvalue
//	rbuy		-> tvalue
//	zin			-> tvalue
//	zout 		-> tovalue
//	sell		-> tovalue
//	outsell		-> tdvalue
//	rsell 		-> tovalue
//	outrsell 	-> tdvalue
//	fin 		-> fin_qty * cmp
//------
// INVENTORY OPERATION MODE: CMP calculated at the end of period ; estimation will be made by CMP calculated after each entry, but will be updated overall at the end of month
// Hints: Cronologic CMP is not good for stock reservations and other sensitive cancel of documents
//------
// PROFIT IS ESTABLISHED AS [for One Month]:
// OUTSTOCK (Values) = PREV + (INTin - INTout) + (BUYin - BUYout) - FINAL
// PROFIT (Values) = (SALESout - SALESin) - OUTSTOCK
// WARNING: Returns are NEGATIVE, so the signs between paranthesis are +PLUS instead of -MINUS
//------

/*

nir:
create: sp: erp_buys 									[+] 	*** SMPrice
create 	sr: erp_buys_returns 							[-]		x

nir:
cancel: sp: erp_buys.recept_note_cancel 				[-] 	*** SMPrice
cancel: sr: erp_buys_returns.return_recept_note_cancel  [+] 	x

stockfix create:
	add											+ 		*** SMPrice
	substract 									-		x
	move										-/+ 	x

stockfix cancel:
	add 										+ 		x
	substract 									- 		*** SMPrice
	move 										+/- 	x


Sales Return
//====
//-- price to register for history in stocklog as the CMP :: here we must use a strategy to get the original stockprice since when the original invoice was created
//== (strategies to get the old CMP)
//- [strategy 1] :: use the CMP registered in OLD Invoice Inventory XML :: {{{SYNC-COMPARE-HASH}}}
//- [strategy 2] :: read the archive table (ONLY IF MONTH IS CLOSED) ELSE CALCULATE THE CURRENT CMP and get real CMP if possible

*/


/**
 * ERP Inventory Management
 *
 * Accounting Principles CMP # https://contabilul.manager.ro/a/8178/cum-se-aplica-metoda-cost-mediu-ponderat-in-cazul-gestiunii-de-cereales.html
 * Variant 1: at the end of month ; on month close clear data and re-init data with a prev-month qty + final cmp
 * Variant 2: after each entry or return
 *
 */

final class erpInventory {

	// r.20230915



	//==================================================================
	public static function blended_avg_price($y_o_qty, $y_o_price, $y_n_qty, $y_n_price) {

		//--
		$new_qty = $y_o_qty + $y_n_qty;
		//--

		//--
		if($new_qty > 0) {
			$blended = (($y_o_qty * $y_o_price) + ($y_n_qty * $y_n_price)) / $new_qty;
		} else {
			$blended = 0;
		} //end if else
		//--

		//--
		return Smart::format_number_dec(0+$blended, 4, '.', '');
		//--

	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function blended_reverse_avg_price($y_o_qty, $y_o_price, $y_n_qty, $y_n_price) {

		//--
		$prev_stock_value = ($y_o_qty * $y_o_price);
		$new_stock_value = ($y_n_qty * $y_n_price);
		//-
		$prev_qty = $y_o_qty - $y_n_qty ;
		$crr_val = $prev_stock_value - $new_stock_value;
		//--

		//--
		if($crr_val < 0) {
			$crr_val = 0;
		} //end if
		//--

		//--
		if($prev_qty > 0) { // avoid divide by zero
			$rev_blended = $crr_val / $prev_qty;
		} else {
			$rev_blended = 0;
		} //end if else
		//--

		//--
		return Smart::format_number_dec(0+$rev_blended, 4, '.', '');
		//--

	} //END FUNCTION
	//==================================================================



	//======================================================================
	/**
	 * [PRIVATE] Reads the Medium Stock Price (4 Decimals) for ITEM / ATTRIB Combination
	 *
	 * @param STRING $y_item_code			:: Item CODE
	 * @param STRING $y_item_attrib 		:: Item ATTRIBUTE
	 * @return 4-Decimals Formated Number
	 */
	private function _SMPRICE_Item__read($y_item_code, $y_item_attrib) {
		//--
		$arr_rd = SmartPgsqlDb::read_data('SELECT "smprice", "ref_qty" FROM "ecomm_erp_stockprice" WHERE (("id" = \''.SmartPgsqlDb::escape_str($y_item_code).'\') AND ("attrib" = \''.SmartPgsqlDb::escape_str($y_item_attrib).'\')) LIMIT 1 OFFSET 0');
		//--
		return array('mprice'=>Smart::format_number_dec($arr_rd[0], 4, '.', ''), 'mqty'=>Smart::format_number_dec($arr_rd[1], 4, '.', ''));
		//--
	} //END FUNCTION
	//======================================================================


	//======================================================================
	/**
	 * [PRIVATE] Updates the Stock Medium Price for One for ITEM / ATTRIB Combination
	 * THIS MUST BE USED JUST FOR STOCK IN / OUT ONLY WHEN UPDATING SMPRICE (BUYS)
	 * !! MUST BE ENCLOSED IN A TRANSACTION !!
	 *
	 * @param ENUM $y_item_type			:: p (for s and d ... we do not need)
	 * @param STRING $y_item_code			:: Item CODE
	 * @param STRING $y_item_attrib		:: Item ATTRIBUTE
	 * @param ENUM $y_sign				:: Operation Sign + | -
	 * @param DECIMAL+ $y_newprice_to_upd	:: New Price
	 * @param DECIMAL+ $y_newqty_to_upd		:: New Qty
	 * @return ARRAY
	 */
	private function _SMPRICE_Item__update($y_item_type, $y_item_code, $y_item_attrib, $y_sign, $y_newprice_to_upd, $y_newqty_to_upd) {

		//--
		$err = ''; // ERR CODES :: 1050 - 1099
		//--

		//--
		$tmp_msg_obj = Smart::escape_html('\''.$y_item_code.'\' @ \''.$y_item_attrib.'\'');
		//--

		//--
		$new_in_qty = 0;
		$new_smed_price = 0;
		//--

		//--
		$where = '(("id" = \''.SmartPgsqlDb::escape_str($y_item_code).'\') AND ("attrib" = \''.SmartPgsqlDb::escape_str($y_item_attrib).'\'))';
		$old_data = SmartPgsqlDb::read_data('SELECT "smprice", "ref_qty", "id" FROM "ecomm_erp_stockprice" WHERE '.$where.' LIMIT 1 OFFSET 0');
		$chk_data = array();
		//--

		//--
		if($y_newqty_to_upd <= 0) {
			if((string)$err == '') {
				$err = 'ERROR (1052): Medium Stock Price Update - Invalid Ref-Quantity on: '.$tmp_msg_obj;
			} //end if
		} //end if
		//--

		//-- calculate
		if((string)$err == '') {
			//--
			if((string)$y_sign == '-') { // - [MINUS]
				//--
				$new_in_qty = $old_data[1] - $y_newqty_to_upd;
				//--
				// to CHECK: avoid abnormal results in the following conditions:
				// 1. month is closed, 99% of stock is sold
				// 2. the return price is significantly different than smprice
				//-
				// decision: we will keep the smprice as reference
				//-
				//$new_smed_price = $old_data[0]; // keep smprice
				$new_smed_price = NorthICE_SmartEcomm::blended_reverse_avg_price($old_data[1], $old_data[0], $y_newqty_to_upd, $y_newprice_to_upd);
				//--
			} elseif((string)$y_sign == '+') { // + [PLUS]
				//--
				$new_in_qty = $old_data[1] + $y_newqty_to_upd;
				//--
				$new_smed_price = NorthICE_SmartEcomm::blended_avg_price($old_data[1], $old_data[0], $y_newqty_to_upd, $y_newprice_to_upd);
				//--
			} else { // INVALID SIGN
				//--
				if((string)$err == '') {
					$err = 'ERROR (1053): Medium Stock Price Update - Invalid Operation Sign on: '.$tmp_msg_obj;
				} //end if
				//--
			} //end if else
			//--
			if($new_in_qty < 0) {
				$new_in_qty = 0;
			} //end if
			//--
			if((string)$y_item_type != 'p') {
				$new_in_qty = 0;
			} //end if
			//--
		} //end if
		//--

		//--
		if((string)$err == '') {
			if($new_smed_price < 0) {
				$err = 'ERROR (1054): Medium Stock Price Update - Negative New-Medium Price on: '.$tmp_msg_obj;
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			if(Smart::check_dec_number_overflow_max($new_smed_price)) {
				$err = 'ERROR (1055): Medium Stock Price Update - New-Medium Price is higher than MaxLimit on: '.$tmp_msg_obj;
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			if($new_in_qty < 0) {
				$err = 'ERROR (1056): Medium Stock Price Update - Negative New-Medium Ref-Quantity on: '.$tmp_msg_obj;
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			if(Smart::check_dec_number_overflow_max($new_in_qty)) {
				$err = 'ERROR (1057): Medium Stock Price Update - New-Medium Ref-Quantity is higher than MaxLimit on: '.$tmp_msg_obj;
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			//-- insert or update
			if(strlen($old_data[2]) <= 0) { // insert new
				//--
				$tmp_wr = SmartPgsqlDb::write_data('INSERT INTO "ecomm_erp_stockprice" ("id", "attrib", "smprice", "ref_qty") VALUES (\''.SmartPgsqlDb::escape_str($y_item_code).'\', \''.SmartPgsqlDb::escape_str($y_item_attrib).'\', \'0\', \'0\')');
				//--
				if($tmp_wr[1] != 1) {
					$err = 'ERROR (1058): Medium Stock Price - Insert ... ['.$tmp_wr[1].'] on: '.$tmp_msg_obj;
				} //end if
				//--
			} //end if
			//-- then update it (skip check result, to avoid errors)
			$tmp_wr = SmartPgsqlDb::write_data('UPDATE "ecomm_erp_stockprice" SET "date_time" = \''.date('Y-m-d H:i:s').'\', "smprice" = \''.Smart::format_number_dec($new_smed_price, 4, '.', '').'\', "ref_qty" = \''.Smart::format_number_dec($new_in_qty, 4, '.', '').'\' WHERE '.$where);
			//--
			// we do not need to check this because is not very important ... the real SMPrice will be calculated at the end of month and the smprice can be the same so will result in zero affected rows
			// but we want to avoid updating too many rown than one
			if($tmp_wr[1] > 1) {
				$err = 'ERROR (1059): Medium Stock Price - Update too Many ... ['.$tmp_wr[1].'] on: '.$tmp_msg_obj;
			} //end if
			//--
		} //end if
		//--

		//-- read again to check for negative qty
		if((string)$err == '') {
			//--
			$chk_data = SmartPgsqlDb::read_data('SELECT "smprice", "ref_qty", "id" FROM "ecomm_erp_stockprice" WHERE '.$where.' LIMIT 1 OFFSET 0');
			//--
			if(strlen($chk_data[2]) <= 0) {
				if((string)$err == '') {
					$err = 'ERROR (1060): Medium Stock Price Update - CHK: Record Not Found for: '.$tmp_msg_obj;
				} //end if
			} //end if
			//--
			if($chk_data[0] < 0) {
				if((string)$err == '') {
					$err = 'ERROR (1061): Medium Stock Price Update - CHK: Negative Result SMPrice for: '.$tmp_msg_obj;
				} //end if
			} //end if
			//--
			if(Smart::check_dec_number_overflow_max($chk_data[0])) {
				if((string)$err == '') {
					$err = 'ERROR (1062): Medium Stock Price Update - CHK: SMPrice is higher than MaxLimit for: '.$tmp_msg_obj;
				} //end if
			} //end if
			//--
			if($chk_data[1] < 0) {
				if((string)$err == '') {
					$err = 'ERROR (1063): Medium Stock Price Update - CHK: Negative Result Ref-Quantity for: '.$tmp_msg_obj;
				} //end if
			} //end if
			//--
			if(Smart::check_dec_number_overflow_max($chk_data[1])) {
				if((string)$err == '') {
					$err = 'ERROR (1064): Medium Stock Price Update - CHK: Ref-Quantity is higher than MaxLimit for: '.$tmp_msg_obj;
				} //end if
			} //end if
			//--
		} //end if
		//--

		//--
		return array('error'=>$err, 'new_mprice'=>Smart::format_number_dec($chk_data[0], 4, '.', ''), 'old_mqty'=>Smart::format_number_dec($old_data[1], 4, '.', ''), 'new_mqty'=>Smart::format_number_dec($chk_data[1], 4, '.', ''));
		//--

	} //END FUNCTION
	//======================================================================


	//======================================================================
	/**
	 * [PRIVATE] StockLog Add Item (just for type [P] Items) :: operate stockLOG for ONE Item in a Document
	 * !! MUST BE ENCLOSED IN A TRANSACTION !!
	 *
	 * @param DATE $y_operation_date		:: YYYY-mm-dd HH:ii:ss
	 * @param ENUM $y_doc_type 				:: Document Type
	 * @param STRING $y_doc_id				:: Document ID
	 * @param ENUM $y_is_manufact			:: Item Is Manufactured
	 * @param ENUM $y_partner_type			:: Document Partner Type as: ''=internal ; c=corporate customer ; p=private customer ; s=supplier
	 * @param STRING $y_partner_id 			:: Document Partner ID
	 * @param STRING $y_warehouse_id		:: Item Warehouse ID (location)
	 * @param STRING $y_item_code			:: Item CODE
	 * @param STRING $y_item_att_hash		:: Item ATTRIBUTES HASH
	 * @param ARRAY $y_item_att_arr 		:: Item ATTRIBUTES ARRAY
	 * @param DECIMAL+/- $y_item_qty		:: Item Quantity
	 * @param DECIMAL+ $y_smprice 			:: Item Stock Medium Price per Unit in All Warehouses before (REQUIRED ONLY to compare with sell price)
	 * @param DECIMAL+ $y_item_price		:: Item Operation Price per Unit to register in DB as history (can be different than Item Stock Medium Price)
	 * @param DECIMAL+ $y_item_outprice 	:: Item OutPrice per Unit (for Sales and Sales Returns)
	 * @param PERCENT% $y_item_tax			:: Item TAX %
	 * @param PERCENT% $y_item_discount		:: Item Discount % for OutPrice (for Sales and Sales Returns)
	 * @param ENUM $y_item_type				:: Item Type: p | s
	 * @param STRING $y_item_package 		:: Item Package
	 * @return STRING 						:: 'ERROR: MESSAGE...' | ''
	 */
	private function _STOCKLOG_Item__add(string $y_operation_date, string $y_doc_type, string $y_doc_id, string $y_is_manufact, string $y_partner_type, string $y_partner_id, string $y_warehouse_id, string $y_item_code, string $y_item_att_hash, array $y_item_att_arr, string $y_item_qty, string $y_smprice, string $y_item_price, string $y_item_outprice, string $y_item_tax, string $y_item_discount, string $y_item_type, string $y_item_package) : string {

		//--
		$err = '';
		//--
		$msg_obj = (string) '`'.$y_item_code.'` @ `'.$y_item_att_hash.'` :: `'.$y_warehouse_id.'`';
		//--

		//-- document types
		$stock_sign = '';
		$stock_delivered = -1;
		$service_status_sell = -1;
		//--
		switch((string)$y_doc_type) {
			case 'sp': // supplier purchase (add to stocks)
				//--
				$stock_sign = ''; // + PLUS
				//--
				$stock_delivered = 1; // here it is always = 1 (must be exported to archive)
				$service_status_sell = 1; // here it is always = 1 (for stats only)
				//--
				break;
			case 'sr': // supplier return (substract from stocks)
				//--
				$stock_sign = '-'; // - MINUS
				//--
				$stock_delivered = 1; // here it is always = 1 (must be exported to archive)
				$service_status_sell = 1; // here it is always = 1 (for stats only)
				//--
				break;
			case 'cs': // customer sale (substract from stocks)
				//--
				$stock_sign = '-'; // - MINUS
				//--
				$stock_delivered = 0; // is = 0 :: only reserved, not delivered (after delivery it will be 1)
				$service_status_sell = 0; // is = 0 :: initialy we mark it as zero ; when invoice is issued it will be = 1 (stats of invoiced inventory items in the same month)
				//-- CMP Control of UnderSell: do not allow sale under stock medium price
				if($y_item_price < $y_smprice) {
					$err = 'WARNING: (5) ITEM STOCKLOG OPERATE'."\n".'Item Under Sell Price: '.$y_item_price.' &lt; '.$y_smprice.' on Item: '.$msg_obj."\n".'Hint: Use a higher Sell Price in combination with Discounts to fix this problem.';
				} //end if
				//--
				break;
			case 'cr': // customer return (add to stocks)
				//--
				$stock_sign = ''; // + PLUS
				//--
				$stock_delivered = 1; // here it is always = 1 (must be exported to archive)
				$service_status_sell = 1; // here it is always = 1 (invoiced when released)
				//--
				break;
			case 'zx': // internal inventory operations
				//--
				switch((string)$y_partner_id) { // in this case the partner id is the sign
					case '+':
						$stock_sign = ''; // + PLUS
						break;
					case '-':
						$stock_sign = '-'; // - MINUS
						break;
					default:
						$err = 'ERROR: (6) ITEM STOCKLOG OPERATE'."\n".'Invalid Internal ZX Operation Sign: '.$y_partner_id.' on Item: '.$msg_obj;
				} //end switch
				//--
				$stock_delivered = 1; // here it is always = 1 (must be exported to archive)
				$service_status_sell = 1; // here it is always = 1 (for stats only)
				//--
				break;
			default:
				$err = 'ERROR: (0) ITEM STOCKLOG OPERATE'."\n".'Invalid Document Type.';
		} //end switch
		//--

		//--
		if((string)$y_item_code == '') {
			$err = 'ERROR: (1) ITEM STOCKLOG OPERATE'."\n".'Invalid Item Code on: '.$msg_obj;
		} //end if
		//--

		//-- check item type
		if((string)$y_item_type != 'p') {
			$err = 'ERROR: (2) ITEM STOCKLOG OPERATE'."\n".'Invalid Item Type on Item: '.$msg_obj;
		} //end if
		//--

		//-- check quantity
		if($y_item_qty <= 0) {
			$err = 'ERROR: (3) ITEM STOCKLOG OPERATE'."\n".'Invalid Item Quantity: '.$y_item_qty.' on Item: '.$msg_obj;
		} //end if
		//--

		//-- check if any error until now
		if((string)$err != '') {
			return (string) $err;
		} //end if
		//--


		//--
		$json_item_att_data = (string) \SmartPgsqlDb::json_encode((array)$y_item_att_arr, 1);
		if((string)\trim((string)$json_item_att_data) == '[]') {
			$json_item_att_data = '{}';
		} //end if
		// TODO: verify hash of attributes, array must be empty or associative 1 level only
		//-- create write array for one item
		$arr_data = [];
		$arr_data['id'] 				= (string) \SmartPgsqlDb::new_safe_id('uid15seq', 'id', 'inventory_log', 'erp');
		$arr_data['date_time'] 			= (string) \SmartPgsqlDb::escape_str((string)$y_operation_date);
		$arr_data['doc_type'] 			= (string) \SmartPgsqlDb::escape_str((string)$y_doc_type);
		$arr_data['doc_id'] 			= (string) \SmartPgsqlDb::escape_str((string)$y_doc_id);
		$arr_data['manufactured'] 		= (string) \SmartPgsqlDb::escape_str((string)$y_is_manufact);
		$arr_data['partner_type'] 		= (string) \SmartPgsqlDb::escape_str((string)$y_partner_type);
		$arr_data['partner_id'] 		= (string) \SmartPgsqlDb::escape_str((string)$y_partner_id);
		$arr_data['warehouse'] 			= (string) \SmartPgsqlDb::escape_str((string)$y_warehouse_id);
		$arr_data['item_code'] 			= (string) \SmartPgsqlDb::escape_str((string)$y_item_code);
		$arr_data['item_att_hash'] 		= (string) \SmartPgsqlDb::escape_str((string)$y_item_att_hash);
		$arr_data['item_att_data'] 		= (string) \SmartPgsqlDb::escape_str((string)$json_item_att_data);
		$arr_data['package'] 			= (string) \SmartPgsqlDb::escape_str((string)$y_item_package);
		$arr_data['item_qty'] 			= (string) $stock_sign.\Smart::format_number_dec((string)$y_item_qty, 4, '.', '');
		$arr_data['item_price'] 		= (string) \Smart::format_number_dec((string)$y_item_price, 4, '.', '');
		$arr_data['item_outprice'] 		= (string) \Smart::format_number_dec((string)$y_item_outprice, 4, '.', '');
		$arr_data['item_tax'] 			= (string) \Smart::format_number_dec((string)$y_item_tax, 2, '.', '');
		$arr_data['item_discount'] 		= (string) \Smart::format_number_dec((string)$y_item_discount, 2, '.', '');
		$arr_data['delivery_status'] 	= (int)    $stock_delivered;
		$arr_data['sell_status'] 		= (int)    $service_status_sell; // this is used only for statistics of sell deliveries without invoicing in the same month
		//-- prepare sql query
		$query = \SmartPgsqlDb::prepare_write_statement((array)$arr_data, 'insert', false); // escaping is done above
		$arr_data = null;
		//-- write item to db (stocks)
		$arr_result = (array) \SmartPgsqlDb::write_data('INSERT INTO "inventory_log" '.$query);
		$query = '';
		//-- check operation
		if($arr_result[1] != 1) { // sensitive control over operation as Affected Rows
			if((string)$err == '') {
				$err = 'ERROR: (4) ITEM STOCKLOG OPERATE'."\n".'SQL Insert Failed on Item: '.$msg_obj;
			} //end if
		} //end if
		//--

		//--
		return (string) $err;
		//--

	} //END FUNCTION
	//======================================================================


} //END CLASS


/*


-- START :: PostgreSQL Table: netvision / inventory_log #####

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

-- Table Schema #####

BEGIN;

CREATE SCHEMA IF NOT EXISTS erp;
COMMENT ON SCHEMA erp IS 'Smart ERP';

SET search_path = erp, pg_catalog;

CREATE TABLE IF NOT EXISTS inventory_log (
    id character varying(45) NOT NULL,
    date_time character varying(22) DEFAULT ''::character varying NOT NULL,
    doc_type character varying(2) DEFAULT ''::character varying NOT NULL,
    doc_id character varying(10) DEFAULT ''::character varying NOT NULL,
    manufactured character varying(1) DEFAULT ''::character varying NOT NULL,
    partner_type character varying(1) DEFAULT ''::character varying NOT NULL,
    partner_id character varying(10) DEFAULT ''::character varying NOT NULL,
    warehouse character varying(8) DEFAULT ''::character varying NOT NULL,
    item_code character varying(20) DEFAULT ''::character varying NOT NULL,
    item_att_hash character varying(128) DEFAULT ''::character varying NOT NULL,
    item_att_data jsonb DEFAULT '{}'::jsonb NOT NULL,
    package character varying(75) DEFAULT ''::character varying NOT NULL,
    item_qty numeric(18,4) DEFAULT 0.0000 NOT NULL,
    item_price numeric(18,4) DEFAULT 0.0000 NOT NULL,
    item_outprice numeric(18,4) DEFAULT 0.0000 NOT NULL,
    item_tax numeric(4,2) DEFAULT 0.00 NOT NULL,
    item_discount numeric(5,2) DEFAULT 0.00 NOT NULL,
    delivery_status smallint DEFAULT 0 NOT NULL,
    sell_status smallint DEFAULT 0 NOT NULL,
    fin_twqty numeric(19,4) DEFAULT 0.0000 NOT NULL,
    fin_tqty numeric(19,4) DEFAULT 0.0000 NOT NULL,
    fin_cmp numeric(19,4) DEFAULT 0.0000 NOT NULL,
    accounting_id character varying(24) DEFAULT ''::character varying NOT NULL,
    CONSTRAINT inventory_log__check_date_time CHECK ((char_length((date_time)::text) >= 10)),
    CONSTRAINT inventory_log__check_delivery_status CHECK (((delivery_status = 0) OR (delivery_status = 1))),
    CONSTRAINT inventory_log__check_doc_id CHECK ((char_length((doc_id)::text) = 10)),
    CONSTRAINT inventory_log__check_doc_type CHECK ((char_length((doc_type)::text) = 2)),
    CONSTRAINT inventory_log__check_fin_cmp CHECK ((fin_cmp >= (0)::numeric)),
    CONSTRAINT inventory_log__check_fin_tqty CHECK ((fin_tqty >= (0)::numeric)),
    CONSTRAINT inventory_log__check_fin_twqty CHECK ((fin_twqty >= (0)::numeric)),
    CONSTRAINT inventory_log__check_id CHECK ((char_length((id)::text) >= 1)),
    CONSTRAINT inventory_log__check_item_att_hash CHECK (((char_length((item_att_hash)::text) > 0) AND (char_length((item_att_hash)::text) <= 128))),
    CONSTRAINT inventory_log__check_item_code CHECK (((char_length((item_code)::text) >= 1) AND (char_length((item_code)::text) <= 20))),
    CONSTRAINT inventory_log__check_item_discount CHECK (((item_discount >= (0)::numeric) AND (item_discount <= (100)::numeric))),
    CONSTRAINT inventory_log__check_item_outprice CHECK ((item_outprice >= (0)::numeric)),
    CONSTRAINT inventory_log__check_item_price CHECK ((item_price > (0)::numeric)),
    CONSTRAINT inventory_log__check_item_qty CHECK ((item_qty <> (0)::numeric)),
    CONSTRAINT inventory_log__check_item_tax CHECK (((item_tax >= (0)::numeric) AND (item_tax < (100)::numeric))),
    CONSTRAINT inventory_log__check_manufactured CHECK (((((manufactured)::text = ''::text) OR ((manufactured)::text = 'a'::text)) OR ((manufactured)::text = 'd'::text))),
    CONSTRAINT inventory_log__check_package CHECK ((char_length((package)::text) >= 1)),
    CONSTRAINT inventory_log__check_partner_id CHECK ((char_length((partner_id)::text) >= 1)),
    CONSTRAINT inventory_log__check_partner_type CHECK ((((((((partner_type)::text = ''::text) OR ((partner_type)::text = 's'::text)) OR ((partner_type)::text = 'p'::text)) OR ((partner_type)::text = 'c'::text)) OR ((partner_type)::text = 'z'::text)) OR ((partner_type)::text = 'x'::text))),
    CONSTRAINT inventory_log__check_sell_status CHECK (((sell_status = 0) OR (sell_status = 1))),
    CONSTRAINT inventory_log__check_warehouse CHECK ((char_length((warehouse)::text) > 0))
);

-- Table Index Constraints #####

ALTER TABLE ONLY inventory_log ADD CONSTRAINT inventory_log__id PRIMARY KEY (id);

-- Table Indexes #####

CREATE INDEX inventory_log__accounting_id 		ON inventory_log USING btree (accounting_id);
CREATE INDEX inventory_log__date_time 			ON inventory_log USING btree (date_time);
CREATE INDEX inventory_log__delivery_status 	ON inventory_log USING btree (delivery_status);
CREATE INDEX inventory_log__doc_id 				ON inventory_log USING btree (doc_id);
CREATE INDEX inventory_log__doc_type 			ON inventory_log USING btree (doc_type);
CREATE INDEX inventory_log__item_att_hash 		ON inventory_log USING btree (item_att_hash);
CREATE INDEX inventory_log__item_code 			ON inventory_log USING btree (item_code);
CREATE INDEX inventory_log__manufactured 		ON inventory_log USING btree (manufactured);
CREATE INDEX inventory_log__partner_id 			ON inventory_log USING btree (partner_id);
CREATE INDEX inventory_log__partner_type 		ON inventory_log USING btree (partner_type);
CREATE INDEX inventory_log__sell_status 		ON inventory_log USING btree (sell_status);
CREATE INDEX inventory_log__warehouse 			ON inventory_log USING btree (warehouse);

-- Table Comments #####

COMMENT ON TABLE  inventory_log IS 'ERP - Inventory Log v.2023.09.15';
COMMENT ON COLUMN inventory_log.id IS 'UUID';
COMMENT ON COLUMN inventory_log.date_time IS 'DateTime as yyyy-mm-dd hh:ii:ss';
COMMENT ON COLUMN inventory_log.doc_type IS 'Document Type';
COMMENT ON COLUMN inventory_log.doc_id IS 'Document ID';
COMMENT ON COLUMN inventory_log.manufactured IS 'Manufacturing Status : ''''=no manuf ; a=assembly ; d=dissasembly';
COMMENT ON COLUMN inventory_log.partner_type IS 'Partner Type : ''''=internal ; c=corporate customer ; p=private customer ; s=supplier ; z = int. adj ; x = int transf.';
COMMENT ON COLUMN inventory_log.partner_id IS 'Partner ID (or internal + -)';
COMMENT ON COLUMN inventory_log.warehouse IS 'Warehouse ID';
COMMENT ON COLUMN inventory_log.item_code IS 'Item Unique Code';
COMMENT ON COLUMN inventory_log.item_att_hash IS 'Item Attributes Hash';
COMMENT ON COLUMN inventory_log.item_att_data IS 'Item Attributes Data (JSON Object)';
COMMENT ON COLUMN inventory_log.package IS 'U.M.';
COMMENT ON COLUMN inventory_log.item_qty IS 'Item Quantity: + in ; - out';
COMMENT ON COLUMN inventory_log.item_price IS 'Item Inventory Price (in/out)';
COMMENT ON COLUMN inventory_log.item_outprice IS 'Item Sales Price (sales / rsales)';
COMMENT ON COLUMN inventory_log.item_tax IS 'Item Sales Tax % (percent)';
COMMENT ON COLUMN inventory_log.item_discount IS 'Item Sales Price Discount % (0..100 : percent)';
COMMENT ON COLUMN inventory_log.delivery_status IS 'Delivery Status :: IN: 0=on ordered, 1=in warehouse ; OUT: 0=reserved, 1=delivered';
COMMENT ON COLUMN inventory_log.sell_status IS 'Sell Status :: 0 = proform ; 1 = invoiced';
COMMENT ON COLUMN inventory_log.fin_twqty IS 'Calculated Final Qty. by Warehouse';
COMMENT ON COLUMN inventory_log.fin_tqty IS 'Calculated Final Qty. Overall';
COMMENT ON COLUMN inventory_log.fin_cmp IS 'Calculated Final CMP';
COMMENT ON COLUMN inventory_log.accounting_id IS 'Accounting Acnt. Number';

COMMIT;

-- END #####


*/



// end of php code
