[%%%COMMENT%%%]
	// Partial Template: ModJsComponents.TextEditorCallBack (Js)
	// This is a Javascript template intended to be used for inline Javascript into a HTML on*=""
	// DO NOT ADD Javascript or HTML Comments here or you may break this code when executing in HTML on*="" context
[%%%/COMMENT%%%]
(function(){
	var url = '[###URL|js###]';
	if([###IS_POPUP|num###]) {
		if(window.opener) {
			window.opener.CKEDITOR.tools.callFunction(window.opener.Smart_CKEditor_fileBrowserCallExchange(), url);
		} else {
			parent.CKEDITOR.tools.callFunction(parent.Smart_CKEditor_fileBrowserCallExchange(), url);
		}
		smartJ$Browser.CloseModalPopUp();
		return false;
	} else {
		return CKEDITOR.tools.callFunction(Smart_CKEditor_fileBrowserCallExchange(), url);
	}
})();