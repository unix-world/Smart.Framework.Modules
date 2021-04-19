
// fix back the Smart.Framework.Js for renamed classes and functions
// (c) 2021 unix-world.org
// v.20210419

window.SmartJS_CoreUtils = window.smartJ$Utils; // renamed class

window.SmartJS_DateUtils = window.smartJ$Date; // renamed class

window.SmartJS_TestCrypto = window.smartJ$TestCrypto; // renamed class
window.SmartJS_Base64 = window.smartJ$Base64; // renamed class
window.SmartJS_CryptoHash = window.smartJ$CryptoHash; // renamed class
window.SmartJS_CryptoBlowfish = window.smartJ$CryptoBlowfish; // renamed class

window.SmartJS_ModalBox = window.smartJ$ModalBox; // renamed class

window.SmartJS_BrowserUtils = {...window.smartJ$Browser}; // renamed class, clone
window.SmartJS_BrowserUtils.Load_Div_Content_By_Ajax 		= window.SmartJS_BrowserUtils.LoadElementContentByAjax; // renamed function
window.SmartJS_BrowserUtils.Submit_Form_By_Ajax 			= window.SmartJS_BrowserUtils.SubmitFormByAjax; // renamed function
window.SmartJS_BrowserUtils.Ajax_XHR_GetByForm 				= window.SmartJS_BrowserUtils.AjaxRequestByForm; // renamed function
window.SmartJS_BrowserUtils.Ajax_XHR_PostMultiPart_To_URL 	= window.SmartJS_BrowserUtils.AjaxPostMultiPartToURL; // renamed function
window.SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL 		= window.SmartJS_BrowserUtils.AjaxRequestFromURL; // renamed function
window.SmartJS_BrowserUtils.Overlay_Show 					= window.SmartJS_BrowserUtils.OverlayShow; // renamed function
window.SmartJS_BrowserUtils.Overlay_Clear 					= window.SmartJS_BrowserUtils.OverlayClear; // renamed function
window.SmartJS_BrowserUtils.Overlay_Hide 					= window.SmartJS_BrowserUtils.OverlayHide; // renamed function
window.SmartJS_BrowserUtils.Control_ModalCascading 			= window.SmartJS_BrowserUtils.ControlModalCascading; // renamed function
window.SmartJS_BrowserUtils.Message_AjaxForm_Notification 	= window.SmartJS_BrowserUtils.MessageNotification; // renamed function
window.SmartJS_BrowserUtils.confirm_Dialog 					= window.SmartJS_BrowserUtils.ConfirmDialog; // renamed function
window.SmartJS_BrowserUtils.alert_Dialog 					= window.SmartJS_BrowserUtils.AlertDialog; // renamed function
window.SmartJS_BrowserUtils.catch_ENTERKey 					= window.SmartJS_BrowserUtils.catchKeyENTER; // renamed function
window.SmartJS_BrowserUtils.catch_TABKey 					= window.SmartJS_BrowserUtils.catchKeyTAB; // renamed function
window.SmartJS_BrowserUtils.textArea_addLimit 				= window.SmartJS_BrowserUtils.TextAreaAddLimit; // renamed function
window.SmartJS_BrowserUtils.checkAll_CkBoxes 				= window.SmartJS_BrowserUtils.CheckAllCheckBoxes; // renamed function
/*

// window.SmartJS_BrowserUtils.param_PageAway # get/set: changed, provided by methods
		getFlag('PageAway');
		setFlag('PageAway', true);
		setFlag('PageAway', false);
// window.SmartJS_BrowserUtils.param_PageUnloadConfirm # get/set: changed, provided by methods
		getFlag('PageUnloadConfirm');
		setFlag('PageUnloadConfirm', true);
		setFlag('PageUnloadConfirm', false);
// window.SmartJS_BrowserUtils.param_RefreshState # get/set: changed, provided by methods
		getFlag('RefreshState');
		setFlag('RefreshState', true);
		setFlag('RefreshState', false);
// window.SmartJS_BrowserUtils.param_RefreshURL # get/set: changed, provided by methods
		getFlag('RefreshURL');
		setFlag('RefreshURL', '%url%');
		setFlag('RefreshURL', '');

// window.SmartJS_BrowserUtils.param_PopUpWindow # get only: changed, provided by method ; set can be done only internal
		getRefPopup();

*/

window.Test_Browser_Compliance = window.smartJ$TestBrowser; // renamed class

window.SmartJS_BrowserUIUtils = {...window.smartJ$UI}; // renamed class, clone
window.SmartJS_BrowserUIUtils.Smart_SelectList = window.SmartJS_BrowserUIUtils.SelectList; // renamed function
window.SmartJS_BrowserUIUtils.Tabs_Init = window.SmartJS_BrowserUIUtils.TabsInit; // renamed function
window.SmartJS_BrowserUIUtils.Tabs_Activate = window.SmartJS_BrowserUIUtils.TabsActivate; // renamed function
window.SmartJS_BrowserUIUtils.Date_Picker_Init = window.SmartJS_BrowserUIUtils.DatePickerInit; // renamed function
window.SmartJS_BrowserUIUtils.Date_Picker_Display = window.SmartJS_BrowserUIUtils.DatePickerDisplay; // renamed function
window.SmartJS_BrowserUIUtils.Time_Picker_Init = window.SmartJS_BrowserUIUtils.TimePickerInit; // renamed function
window.SmartJS_BrowserUIUtils.Time_Picker_Display = window.SmartJS_BrowserUIUtils.TimePickerDisplay; // renamed function
window.SmartJS_BrowserUIUtils.Smart_DataTable_Init = window.SmartJS_BrowserUIUtils.DataTableInit; // renamed function
window.SmartJS_BrowserUIUtils.Smart_DataTable_FilterColumns = window.SmartJS_BrowserUIUtils.DataTableColumnsFilter; // renamed function

window.Smart_Grid = window.SmartGrid; // renamed class

// #END
