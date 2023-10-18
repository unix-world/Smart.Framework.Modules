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
 * ERP Inventory Management
 *
 * Accounting Principles CMP # https://contabilul.manager.ro/a/8178/cum-se-aplica-metoda-cost-mediu-ponderat-in-cazul-gestiunii-de-cereales.html
 * Variant 1: at the end of month ; on month close clear data and re-init data with a prev-month qty + final cmp
 * Variant 2: after each entry or return
 *
 */

final class erpInventory {

	// r.20230915


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
