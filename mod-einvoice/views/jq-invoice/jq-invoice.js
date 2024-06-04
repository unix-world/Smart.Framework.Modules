
// jQuery Invoice v.1.0 :: r.20240123
// (c) 2023-2024 unix-world.org


const smartJQeInvoice = new class{constructor(){ // STATIC CLASS
	const _N$ = 'smartJQeInvoice';

	// :: static
	const _C$ = this; // self referencing

	const _p$ = console;

	let SECURED = false;
	_C$.secureClass = () => { // implements class security
		if(SECURED === true) {
			_p$.warn(_N$, 'Class is already SECURED');
		} else {
			SECURED = true;
			Object.freeze(_C$);
		} //end if
	}; //END

	const $ = jQuery; // jQuery referencing

	const _Utils$ = smartJ$Utils;
//	const _Crypto$Hash = smartJ$CryptoHash;
//	const _Crypto$Cipher = smartJ$CipherCrypto;
//	const _Date$ = smartJ$Date;
	const _BwUtils$ = smartJ$Browser;


	const regexValidCurrency = RegExp(/^[A-Z]{3}$/);

	let fxSaveHandler = (jsonData) => {};
	let $itemRow;
	let lastErr = '';

	const formatAsFixedDecimal = (amount) => {
		//--
		return _Utils$.stringReplaceAll(',', ' ', _Utils$.format_number_dec(amount, 2, true, false, true)); // string
		//--
	};
	// no export

	const formatAsDecimal = (amount) => {
		//--
		return Number(_Utils$.format_number_dec(amount, 2, true, true)); // number
		//--
	};
	// no export

	const reissueItemNumbers = () => {
		//--
		let itemsNum = 0;
		$('table#invoice td.item-num').each((index, element) => {
			itemsNum++;
			$(element).find('div.num').empty().text(String(itemsNum || '0') + '.');
		});
		//--
		$('table#invoice td span.items-number').empty().text(itemsNum);
		//--
	};

	const calculateSubtotal = (row) => {
		//--
		const $row = $(row);
		//--
		const $inputName = $row.find('input.name');
		let itemName = _Utils$.stringPureVal($inputName.val(), true);
		if(itemName == '') {
			itemName = 'Item Name';
		} //end if
		if(itemName.length > 100) {
			itemName = itemName.substring(0, 100);
		} //end if
		$inputName.val(itemName);
		//--
		const $inputDetails = $row.find('textarea.details');
		let itemDetails = _Utils$.stringPureVal($inputDetails.val(), true);
		if(itemDetails.length > 512) {
			itemDetails = itemDetails.substring(0, 512);
		} //end if
		$inputDetails.val(itemDetails);
		//--
		const $inputQty = $row.find('input.qty');
		const $inputUPrice = $row.find('input.price');
		//--
		const $inputUM = $row.find('input.um');
		let itemUM = _Utils$.stringPureVal($inputUM.val(), true);
		if(itemUM == '') {
			itemUM = 'unit';
		} //end if
		if(itemUM.length > 10) {
			itemUM = itemUM.substring(0, 10);
		} //end if
		$inputUM.val(itemUM);
		//--
		const uprice = formatAsDecimal($inputUPrice.val() || 0);
		//--
		let qty = formatAsDecimal($inputQty.val() || 0);
		if(qty === 0) { // row remove is no more handled by zero qty, have it's own remove button
			//--
		//	$row.remove();
		//	reissueItemNumbers();
		//	return;
			//--
			qty = 1;
			$inputQty.val(qty); // fix: dissalow quantity zero
			//--
		} else if((qty < 0) && (uprice < 0)) { // disallow both: unitPrice and Quantity to be negative ; can be both positive or either one negative, not both !
			//--
			qty = -1 * qty;
			$inputQty.val(qty); // fix: quantity, inverse sign
			//--
		} //end if
		//--
		const $xtax = $('select#xtax');
		const taxExempt = $xtax.val();
		//--
		const $tax = $row.find('input.tax');
		//--
		let ptax = formatAsDecimal($tax.val() || 0);
		if(ptax <= 0) {
			ptax = formatAsDecimal($tax.attr('data-tax') || 0);
		} //end if
		//--
		if(ptax < 0) {
			ptax = 0;
		} else if(ptax > 100) {
			ptax = 100;
		} //end if
		//--
		if(taxExempt != '') { // tax exempt
			$tax.attr('data-tax', ptax).val('0').prop('readonly', true);
			ptax = 0;
		} else {
			$tax.val(ptax).attr('data-tax', 0).prop('readonly', false);
		} //end if
		//--
		const subtotal = formatAsDecimal(qty * uprice);
		const tax = formatAsDecimal(subtotal * ptax / 100);
		//--
		$inputQty.val(formatAsDecimal(qty));
		$inputUPrice.val(formatAsDecimal(uprice));
		$row.find('td:eq(5)').find('div.calc').empty().text(formatAsFixedDecimal(subtotal));
		$row.find('td:eq(6)').find('div.calc').empty().text(formatAsFixedDecimal(tax));
		//--
		return { sumnet: formatAsDecimal(subtotal), sumtax: formatAsDecimal(tax) };
		//--
	};
	// no export

	const calculateTotal = () => {
		//--
		_BwUtils$.GrowlNotificationRemove();
		//--
		lastErr = '';
		//--
		const $totalnet = $('table#invoice tr.sub-totals td:eq(1)');
		const $totaltax = $('table#invoice tr.sub-totals td:eq(2)');
		const $total    = $('table#invoice tr.total td:eq(1)');
		//--
		const $hTotalNet = $('table#invoice tr td input#totalnet');
		const $hTotalTax = $('table#invoice tr td input#totaltax');
		const $hTotal    = $('table#invoice tr td input#total');
		//--
		const $itemRows = $('table#invoice tr.item');
		if($itemRows.length <= 0) {
			//--
			lastErr = 'Invoice is Empty';
			//--
			_BwUtils$.GrowlNotificationAdd('Errors Detected', '<i class="sfi sfi-notification"></i> &nbsp; ' + _Utils$.escape_html(lastErr), null, 0, true, 'pink');
			//--
			$totalnet.empty().html('<i class="sfi sfi-blocked"></i>');
			$totaltax.empty().html('<i class="sfi sfi-blocked"></i>');
			$total.empty().html('<i class="sfi sfi-blocked"></i>');
			//--
			$hTotalNet.val(0);
			$hTotalTax.val(0);
			$hTotal.val(0);
			//--
			return;
			//--
		} //end if
		//--
		reissueItemNumbers();
		//--
		const subtotals = $itemRows.map((idx, val) => calculateSubtotal(val)).get();
		const totalnet = subtotals.reduce((a, v) => a + formatAsDecimal(v.sumnet), 0);
		const totaltax = subtotals.reduce((a, v) => a + formatAsDecimal(v.sumtax), 0);
	//	const total = subtotals.reduce((a, v) => a + formatAsDecimal(v.sumnet) + formatAsDecimal(v.sumtax), 0);
		const total = formatAsDecimal(totalnet + totaltax);
		//--
		if(total > 999999999999) {
			//--
			lastErr = 'Invoice Amount Overflow';
			//--
			_BwUtils$.GrowlNotificationAdd('Errors Detected', '<i class="sfi sfi-notification"></i> &nbsp; ' + _Utils$.escape_html(lastErr) + '<br>Max Invoice Amount is:<br>999,999,999,999', null, 0, true, 'pink');
			//--
			$totalnet.empty().html('<i class="sfi sfi-warning"></i>');
			$totaltax.empty().html('<i class="sfi sfi-warning"></i>');
			$total.empty().html('<i class="sfi sfi-warning"></i>');
			//--
			$hTotalNet.val(0);
			$hTotalTax.val(0);
			$hTotal.val(0);
			//--
			return;
			//--
		} //end if
		//--
		const $currency = $('input#crr');
		const crr = _Utils$.stringPureVal($currency.val(), false); // do not trim
		if(crr.length == 1) {
			switch(crr) {
				case '$': // dollar
				case '£': // pound
				case '€': // euro
				case '¥': // yen
				case '₽': // ruble
					break;
				default:
					lastErr = 'Invalid Currency Symbol';
			} //end switch
		} else if(crr.length == 3) {
			if(!(regexValidCurrency.test(crr))) {
				lastErr = 'Invalid Currency. Must be as `XYZ`';
			} //end if
		} else {
			lastErr = 'Invalid Currency';
		} //end if else
		//--
		if(lastErr != '') {
			_BwUtils$.GrowlNotificationAdd('Errors Detected', '<i class="sfi sfi-notification"></i> &nbsp; ' + _Utils$.escape_html(lastErr), null, 0, true, 'red');
		} else {
			_BwUtils$.GrowlNotificationAdd('No Errors Detected ...', '<i class="sfi sfi-checkmark2 sfi-2x"></i>', null, 0, true, 'green');
		} //end if
		//--
		$totalnet.empty().text(formatAsFixedDecimal(totalnet));
		$totaltax.empty().text(formatAsFixedDecimal(totaltax));
		$total.empty().text(formatAsFixedDecimal(total));
		//--
		$hTotalNet.val(totalnet);
		$hTotalTax.val(totaltax);
		$hTotal.val(total);
		//--
	};
	// no export

	const handleChanges = () => {
		//--
		$('table#metainfo').on('change', 'select#ctype', (evt) => {
			//-- DO NOT CAST ON EVENT BLUR ! will loose the data ...
			const $cType = $(evt.currentTarget);
			const cType = _Utils$.stringPureVal($cType.val(), true);
			//--
			$('table#metainfo input#ctaxid').val('');
			$('table#metainfo input#cregno').val('');
			//--
			if(cType === 'p') {
				$('table#metainfo div#c-taxid').hide();
				$('table#metainfo div#c-regno').hide();
			} else {
				$('table#metainfo div#c-taxid').show();
				$('table#metainfo div#c-regno').show();
			} //end
			//--
		});
		//--
		$('table#invoice').on('change blur', 'select#xtax', () => {
			//--
			calculateTotal();
			//--
		});
		//--
		$('table#invoice').on('change blur', 'input[type=text]', () => {
			//--
			calculateTotal();
			//--
		});
		//--
		$('table#invoice').on('change blur', 'textarea', () => {
			//--
			calculateTotal();
			//--
		});
		//--
	};
	// no export

	const handleBtnUpdate = () => {
		//--
		$('button#btn-upd').on('click', () => {
			//--
			_BwUtils$.GrowlNotificationRemove();
			//--
			calculateTotal();
			//--
			_BwUtils$.GrowlNotificationAdd('Updating ...', '<i class="sfi sfi-hour-glass sfi-2x"></i> &nbsp; <i class="sfi sfi-libreoffice sfi-2x"></i> &nbsp; <i class="sfi sfi-calculator sfi-2x"></i> &nbsp; <i class="sfi sfi-spinner10 sfi-2x"></i>', null, 1500, false, 'white');
			//--
			return false;
			//--
		});
	};
	// no export

	const registerHandleRowBtnRemove = ($row) => {
		//--
		const $btn = $row.find('td.item-num').find('button.btn-remove');
		$btn.on('click', () => {
			//--
			_BwUtils$.ConfirmDialog('<h4>Confirm to remove the selected row from the invoice.</h4>', () => { $row.remove(); calculateTotal(); _BwUtils$.GrowlNotificationAdd('Item Removed ...', '<i class="sfi sfi-bin2 sfi-2x"></i>', null, 1000, false, 'black'); }, 'Remove row from Invoice', 600, 220, 'alertable', false);
			//--
			return false;
			//--
		});
		//--
	};
	// no export

	const handleBtnAdd = () => {
		//--
		$('button#btn-add').on('click', () => {
			//--
			const $newRow  = $itemRow.clone();
			//--
			registerHandleRowBtnRemove($newRow);
			//--
			$newRow.find('input.name').val('Item Name');
			$newRow.find('textarea.details').val('');
			$newRow.find('input.um').val('unit');
			$newRow.find('input.qty').val('1');
			$newRow.find('input.price').val('0');
			$newRow.find('td:eq(0)').find('div.num').empty();
			$newRow.find('td:eq(5)').find('div.calc').empty().text('0');
			$newRow.find('td:eq(6)').find('div.calc').empty().text('0');
			$('table#invoice').append($newRow);
			$newRow.find('textarea.name').focus();
			//--
			calculateTotal();
			//--
			_BwUtils$.GrowlNotificationAdd('New Item Added ...', '<i class="sfi sfi-plus sfi-2x"></i>', null, 1000, false, 'blue');
			//--
			return false;
			//--
		});
	};
	// no export

	const saveAndExit = () => {
		//--
		const customer = _BwUtils$.SerializeFormAsObject('frm-metainfo', 'data');
		const invoice  = _BwUtils$.SerializeFormAsObject('frm-invoice', 'data');
		//--
		_BwUtils$.GrowlNotificationRemove();
		_BwUtils$.OverlayShow('<i class="sfi sfi-box-remove sfi-2x"></i>', 'Saving ...');
		_BwUtils$.setFlag('PageAway', true);
		//--
		fxSaveHandler({ customer:customer, invoice:invoice });
		//--
	};

	const handleBtnSave = () => {
		//--
		$('button#btn-export').on('click', () => {
			//--
			calculateTotal();
			//--
			if(lastErr) {
				_BwUtils$.AlertDialog('<h3>The Document contains some errors:</h3><h5>Last Error: `' + lastErr + '`.</h5><br>Fix all errors prior to save.', null, 'Document Save', 600, 220, 'alertable', false);
				return false;
			} //end if
			//--
			_BwUtils$.ConfirmDialog('<h4>Confirm to save the document.</h4>', () => { saveAndExit(); }, 'Document Save', 600, 220, 'alertable', false);
			//--
			return false;
			//--
		});
		//--
	};
	// no export

	//--

	const InvoicePreventPageUnload = () => { // required only after re-save, if necessary to call this again
		//--
		_BwUtils$.setFlag('PageAway', false);
		_BwUtils$.OverlayHide();
		//--
	};
	_C$.InvoicePreventPageUnload = InvoicePreventPageUnload; // export


	const InvoiceHandler = (saveHandler) => {
		//--
		_BwUtils$.PageAwayControl('Leave the page without Save ?');
		//--
		if(typeof(saveHandler) === 'function') {
			fxSaveHandler = saveHandler;
		} //end if
		//--
		const $tBody = $('table#invoice tbody');
		$itemRow = $tBody.find('tr.item:last'); // declared above
		$tBody.empty().show();
		//--
		handleBtnUpdate();
		handleBtnAdd();
		handleBtnSave();
		//--
		calculateTotal();
		handleChanges();
		//--
	};
	_C$.InvoiceHandler = InvoiceHandler; // export

}}; //END CLASS

smartJQeInvoice.secureClass(); // implements class security

window.smartJQeInvoice = smartJQeInvoice; // global export

// #END
