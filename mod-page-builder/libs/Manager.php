<?php
// Class: \SmartModExtLib\PageBuilder\Manager
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

namespace SmartModExtLib\PageBuilder;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//# Depends on:
//	* Smart
//	* SmartUnicode
//	* SmartUtils
//	* SmartAuth
//	* SmartComponents
//	* SmartTextTranslations

//==================================================================
/*
//-- PRIVILEGES
$administrative_privileges['pagebuilder-create'] 		= 'WebPages // Create';
$administrative_privileges['pagebuilder-edit'] 			= 'WebPages // Edit Code';
$administrative_privileges['pagebuilder-data-edit'] 	= 'WebPages // Edit Data';
$administrative_privileges['pagebuilder-delete'] 		= 'WebPages // Delete';
$administrative_privileges['pagebuilder-manage'] 		= 'WebPages // Manage (Special Pages)';
//--
*/
//==================================================================

//define('SMART_PAGEBUILDER_DB_TYPE', 'sqlite'); // this must be set in etc/config.php to activate the PageBuilder module ; possible values for the DB Type: 'sqlite' to use with SQLite DB or 'pgsql' to use with PostgreSQL DB
//define('SMART_PAGEBUILDER_DISABLE_PAGES', true); // this can be set in etc/config.php to disable the use of pages and allow only segments
//define('SMART_PAGEBUILDER_DISABLE_DELETE', true); // this can be set in etc/config-admin.php to disable page deletions in PageBuilder Manager (optional)

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: PageBuilder Manager
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20190109
 * @package 	PageBuilder
 *
 */
final class Manager {

	// ::

	private static $MaxStrCodeSize = 16777216; // 16 MB

	private static $ModulePath = 'modules/mod-page-builder/';
	private static $ModuleScript = 'admin.php';
	private static $ModulePageURLParam = 'page';
	private static $ModulePageURLId = 'page-builder.manage'; // if used directly, must be escaped with \Smart::escape_url()


	//==================================================================
	public static function text($ykey, $y_escape_html=true) {

		//--
		$text = array();
		//--

		//-- ttls
		$text['ttl_list'] 			= 'PageBuilder Objects';
		$text['ttl_records'] 		= 'List';
		$text['ttl_trecords'] 		= 'TreeList';
		$text['ttl_add'] 			= 'Add New Object';
		$text['ttl_edt'] 			= 'Edit Object Properties';
		$text['ttl_edtc'] 			= 'Edit Object Code';
		$text['ttl_edtac'] 			= 'Edit Object Data';
		$text['ttl_del'] 			= 'Delete this Object';
		$text['ttl_ch_list'] 		= 'List Mode Change';
		$text['ttl_reset_hits'] 	= 'Reset Hits Counter on All Records';
		//-- buttons
		$text['search']				= 'Filter';
		$text['reset']				= 'Reset';
		$text['cancel']				= 'Cancel';
		$text['close']				= 'Close';
		$text['save']				= 'Save';
		$text['yes'] 				= 'Yes';
		$text['no']		   			= 'No';
		$text['segment_page'] 		= 'Segment';
		//-- page data mode
		$text['record_runtime'] 	= 'Data';
		$text['record_data'] 		= 'YAML';
		$text['record_syntax'] 		= 'Syntax';
		$text['record_code'] 		= 'Code';
		$text['record_sytx_html'] 	= 'HTML';
		$text['record_sytx_mkdw'] 	= 'MARKDOWN';
		$text['record_sytx_text'] 	= 'TEXT';
		$text['record_sytx_raw'] 	= 'RAW';
		//-- tab nav
		$text['tab_props'] 			= 'Properties';
		$text['tab_code'] 			= 'Code';
		$text['tab_data'] 			= 'Data';
		$text['tab_info'] 			= 'Info';
		//-- list data
		$text['records'] 			= 'Records';
		$text['cnp']				= 'Create A New Object';
		$text['vep']				= 'View/Edit Object';
		$text['dp']					= 'Delete Object';
		//-- fields
		$text['search_by']			= 'Filter by';
		$text['keyword']			= 'Keyword';
		$text['op_compl']			= 'Operation completed';
		$text['op_ncompl'] 			= 'Operation NOT completed';
		//-- errors
		$text['err_1']				= 'ERROR: Invalid Object ID !';
		$text['err_2'] 				= 'Invalid manage operation !';
		$text['err_3'] 				= 'ID already in use !';
		$text['err_4'] 				= 'Invalid ID';
		$text['err_5'] 				= 'An error occured. Please try again !';
		$text['err_6'] 				= 'Invalid Name for Object';
		$text['err_7'] 				= 'Some Edit Fields are not allowed here !';
		$text['err_8']				= 'Required Objects cannot be deleted !';
		$text['err_9'] 				= 'Invalid Object Syntax Type';
		//-- messages
		$text['msg_confirm_del'] 	= 'Please confirm you want to delete this object';
		$text['msg_unsaved'] 	  	= 'NOTICE: Any unsaved change will be lost.';
		$text['msg_no_priv_add']  	= 'WARNING: You have not enough privileges to Create New Objects !';
		$text['msg_no_priv_read'] 	= 'WARNING: You have not enough privileges to READ this Object !';
		$text['msg_no_priv_edit'] 	= 'WARNING: You have not enough privileges to EDIT this Object !';
		$text['msg_no_priv_del']  	= 'WARNING: You have not enough privileges to DELETE this Object !';
		$text['msg_invalid_cksum'] 	= 'NOTICE: Invalid Object CHECKSUM ! Edit and Save again the Object Code or Object Data to (Re)Validate it !';
		//--
		$text['id'] 				= 'ID';
		$text['ref'] 				= 'Ref.';
		$text['refs'] 				= 'Related Objects';
		$text['ctrl'] 				= 'Controller';
		$text['layout'] 			= 'Design Layout';
		$text['name'] 				= 'Name';
		$text['active']				= 'Active';
		$text['special'] 			= 'Special';
		$text['login'] 				= 'Login Restricted';
		$text['modified']			= 'Modified';
		$text['size'] 				= 'Size';
		$text['free_acc'] 			= 'Public Access';
		$text['login_acc'] 			= 'Access by Login';
		$text['restr_acc'] 			= 'Restricted Access';
		$text['activate']			= 'Activate';
		$text['deactivate'] 		= 'Deactivate';
		$text['content'] 			= 'Content';
		$text['acontent'] 			= 'ActiveContent';
		$text['admin'] 				= 'Author';
		$text['published'] 			= 'Published';
		$text['auth'] 				= 'Auth';
		$text['translatable'] 		= 'Translatable';
		$text['translations'] 		= 'Translations';
		$text['warn_translations'] 	= 'WARNING: Not Translatable but some Translations are present';
		$text['counter'] 			= 'Hits Counter';
		$text['pw_code'] 			= 'Code Preview';
		$text['pw_data'] 			= 'Data Preview';
		//--
		$text['hint_0'] 			= 'Select a filtering criteria from below';
		$text['hint_1'] 			= 'Hints: `[]` for Empty ; `![]` for Non-Empty ; `expr` for containing expression';
		$text['hint_2'] 			= 'Hints: `ro` for records having this language code Translation ; `!ro` for records NOT having this language code Translation ; `!` for NON-Translatable records ; `"` for Translatable records';
		$text['hint_3'] 			= 'Fill the filtering expression';
		//--

		//--
		$outText = (string) $text[(string)$ykey];
		//--
		if((string)trim((string)$outText) == '') {
			$outText = '[MISSING-TEXT@'.__CLASS__.']:'.(string)$ykey;
			\Smart::log_warning('Invalid Text Key: ['.$ykey.'] in: '.__METHOD__.'()');
		} //end if else
		//--
		if($y_escape_html !== false) {
			$outText = (string) \Smart::escape_html($outText);
		} //end if
		//--
		return (string) $outText;
		//--

	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayHighlightCode($y_id) {
		//--
		$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordById($y_id);
		//--
		if((string)$query['id'] == '') {
			return \SmartComponents::operation_warn(self::text('err_4'));
		} //end if
		//--
		if((string)$query['mode'] == 'text') {
			$type = 'plaintext'; // fix for text
		} else {
			$type = (string) $query['mode'];
		} //end if else
		//--
		$out = \SmartComponents::html_jsload_editarea();
		$out .= \SmartComponents::js_code_highlightsyntax('body');
		$out .= '<div style="text-align:left;">';
		$out .= '<h3>Code Preview: '.\Smart::escape_html($query['name']).' :: '.\Smart::escape_html($query['id']).'</h3>';
		$out .= '<pre style="background:#ebe9e9;"><code class="'.\Smart::escape_html($type).'" id="code-view-area">'.\Smart::escape_html(base64_decode((string)$query['code'])).'</code></pre>';
		$out .= '</div>';
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayHighlightData($y_id) {
		//--
		$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordById($y_id);
		//--
		if((string)$query['id'] == '') {
			return \SmartComponents::operation_warn(self::text('err_4'));
		} //end if
		//--
		$type = 'yaml';
		//--
		$out = \SmartComponents::html_jsload_editarea();
		$out .= \SmartComponents::js_code_highlightsyntax('body');
		$out .= '<div style="text-align:left;">';
		$out .= '<h3>Data Preview: '.\Smart::escape_html($query['name']).' :: '.\Smart::escape_html($query['id']).'</h3>';
		$out .= '<pre style="background:#ebe9e9;"><code class="'.\Smart::escape_html($type).'" id="data-view-area">'.\Smart::escape_html(base64_decode((string)$query['data'])).'</code></pre>';
		$out .= '</div>';
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayRecord($y_id, $y_disp, $y_lang='') {
		//--
		$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordDetailsById($y_id);
		//--
		if((string)$query['id'] == '') {
			return \SmartComponents::operation_warn(self::text('err_4'));
		} //end if
		//--
		$action_code = 'record-view-tab-code';
		//--
		$xtra_args = '';
		switch((string)$y_disp) {
			case 'code':
				$selected_tab = '1';
				if((string)$y_lang != '') {
					$xtra_args = '&translate='.\Smart::escape_url((string)$y_lang);
				} //end if
				break;
			case 'yaml':
				$selected_tab = '2';
				break;
			case 'info':
				$selected_tab = '3';
				break;
			case 'props':
			default:
				$selected_tab = '0';
		} //end switch
		//--
		if(self::testIsSegmentPage($query['id'])) {
			$draw_name = '<font color="#003399">'.\Smart::escape_html($query['name']).'<font>';
		} else {
			$draw_name = \Smart::escape_html($query['name']);
		} //end if else
		//--
		$translator_window = \SmartTextTranslations::getTranslator('@core', 'window');
		//--
		$out = '';
	//	$out .= \SmartComponents::html_jsload_htmlarea(''); // {{{SYNC-PAGEBUILDER-HTML-WYSIWYG}}}
		$out .= \SmartComponents::html_jsload_editarea();
		$out .= '<script>'.\SmartComponents::js_code_init_away_page('The changes will be lost !').'</script>';
		$out .= \SmartMarkersTemplating::render_file_template(
			(string) self::$ModulePath.'libs/views/manager/view-record.mtpl.htm',
			[
				'RECORD-ID'			=> (string) \Smart::escape_html($query['id']),
				'RECORD-NAME' 		=> (string) $draw_name,
				'BUTTONS-CLOSE' 	=> (string) '<input type="button" value="'.\Smart::escape_html($translator_window->text('button_close')).'" class="ux-button" onClick="SmartJS_BrowserUtils.CloseModalPopUp(); return false;">',
				'TAB-TXT-PROPS'		=> (string) '<img height="16" src="'.self::$ModulePath.'libs/views/manager/img/props.svg'.'" alt="'.self::text('tab_props').'" title="'.self::text('tab_props').'">'.'&nbsp;'.self::text('tab_props'),
				'TAB-LNK-PROPS'		=> (string) self::composeUrl('op=record-view-tab-props&id='.\Smart::escape_url($query['id'])),
				'TAB-TXT-CODE'		=> (string) self::getImgForCodeType($query['id'], $query['mode']).'&nbsp;'.self::text('tab_code'),
				'TAB-LNK-CODE'		=> (string) self::composeUrl('op='.$action_code.'&id='.\Smart::escape_url($query['id']).$xtra_args),
				'TAB-TXT-DATA'		=> (string) '<img height="16" src="'.self::$ModulePath.'libs/views/manager/img/syntax-data.svg'.'" alt="'.self::text('tab_data').'" title="'.self::text('tab_data').'">'.'&nbsp;'.self::text('tab_data'),
				'TAB-LNK-DATA'		=> (string) self::composeUrl('op=record-view-tab-data&id='.\Smart::escape_url($query['id'])),
				'TAB-TXT-INFO'		=> (string) '<img height="16" src="'.self::$ModulePath.'libs/views/manager/img/info.svg'.'" alt="'.self::text('tab_info').'" title="'.self::text('tab_info').'">'.'&nbsp;'.self::text('tab_info'),
				'TAB-LNK-INFO'		=> (string) self::composeUrl('op=record-view-tab-info&id='.\Smart::escape_url($query['id'])),
				'JS-TABS'			=> (string) '<script type="text/javascript">SmartJS_BrowserUIUtils.Tabs_Init(\'tabs\', '.(int)$selected_tab.', false);</script>'
			]
		);
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	// view or display form entry for PROPS
	// $y_mode :: 'list' | 'form'
	public static function ViewFormProps($y_id, $y_mode) {
		//--
		$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordPropsById($y_id);
		if((string)$query['id'] == '') {
			return \SmartComponents::operation_error('FormView Props // Invalid ID');
		} //end if
		//--
		if(self::testIsSegmentPage($query['id'])) {
			$arr_pmodes = array('html' => 'HTML Code', 'markdown' => 'Markdown Code', 'text' => 'Text / Plain', 'settings' => 'Settings');
		} else {
			$arr_pmodes = array('html' => 'HTML Code', 'markdown' => 'Markdown Code', 'text' => 'Text / Plain', 'raw' => 'Raw Output');
		} //end if else
		//--
		$arr_refs = array();
		$q_refs = \Smart::json_decode((string)$query['ref']);
		if(!is_array($q_refs)) {
			$q_refs = array();
		} //end if
		foreach($q_refs as $key => $val) {
			if(!is_array($val)) {
				if((string)$val != '') {
					if(!in_array((string)$val, $arr_refs)) {
						$arr_refs[] = (string) $val;
					} //end if
				} //end if
			} //end if
		} //end if
		$q_refs = null;
		$arr_refs = (array) \Smart::array_sort((array)$arr_refs, 'natsort');
		//--
		$is_subsegment = false;
		if(\Smart::array_size($arr_refs) > 0) {
			$is_subsegment = true;
		} //end if
		//--
		$q_refs = \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordsByRef($y_id);
		for($i=0; $i<\Smart::array_size($q_refs); $i++) {
			if((string)$q_refs[$i]['id'] != '') {
				if(!in_array((string)$q_refs[$i]['id'], $arr_refs)) {
					$arr_refs[] = (string) $q_refs[$i]['id'];
				} //end if
			} //end if
		} //end if
		$q_refs = null;
		//--
		$bttns = '';
		//--
		$translator_window = \SmartTextTranslations::getTranslator('@core', 'window');
		//--
		if((string)$y_mode == 'form') {
			//--
			$bttns .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-save.svg'.'" alt="'.self::text('save').'" title="'.self::text('save').'" style="cursor:pointer;" onClick="'.\SmartComponents::js_ajax_submit_html_form('page_form_props', self::composeUrl('op=record-edit-do&id='.\Smart::escape_url($query['id']))).'">';
			$bttns .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$bttns .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-back.svg'.'" alt="'.self::text('cancel').'" title="'.self::text('cancel').'" style="cursor:pointer;" onClick="'.\SmartComponents::js_code_ui_confirm_dialog('<h3>'.self::text('msg_unsaved').'</h3>'.'<br>'.'<b>'.\Smart::escape_html($translator_window->text('confirm_action')).'</b>', "SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#adm-page-props').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-view-tab-props&id='.\Smart::escape_url($query['id'])))."', 'GET', 'html');").'">';
			//--
			$fld_name = '<input type="text" name="frm[name]" value="'.\Smart::escape_html($query['name']).'" size="70" maxlength="150" autocomplete="off" placeholder="Internal Page Title" required>';
			//--
			if(((string)$query['mode'] == 'raw') OR ((string)$query['mode'] == 'settings')) { // raw or settings cannot be changed to other modes !
				unset($arr_pmodes['html']);
				unset($arr_pmodes['markdown']);
				unset($arr_pmodes['text']);
				$fld_pmode = \SmartComponents::html_select_list_single('pmode', $query['mode'], 'form', $arr_pmodes, 'frm[mode]', '150/0', '', 'no', 'no');
			} else {
				unset($arr_pmodes['raw']);
				unset($arr_pmodes['settings']);
				$fld_pmode = \SmartComponents::html_select_list_single('pmode', $query['mode'], 'form', $arr_pmodes, 'frm[mode]', '150/0', '', 'no', 'no');
			} //end if else
			//--
			$fld_ctrl = self::drawFieldCtrl($query['ctrl'], $is_subsegment, 'form', 'frm[ctrl]');
			$fld_special = \SmartComponents::html_selector_true_false('frm[special]', $query['special']);
			$fld_active = \SmartComponents::html_selector_true_false('frm[active]', $query['active']);
			$fld_auth = \SmartComponents::html_selector_true_false('frm[auth]', $query['auth']);
			$fld_trans = \SmartComponents::html_selector_true_false('frm[translations]', $query['translations']);
			//--
			$fld_layout = self::drawListLayout($query['mode'], 'form', $query['layout'], 'frm[layout]');
			//--
			$extra_form_start = '<form class="ux-form" name="page_form_props" id="page_form_props" method="post" action="#" onsubmit="return false;"><input type="hidden" name="frm[form_mode]" value="props">';
			$extra_form_end = '</form>';
			$extra_scripts = '<script type="text/javascript">SmartJS_BrowserUtils_PageAway = false;</script>';
			$extra_scripts .= '<script>SmartJS_BrowserUIUtils.Tabs_Activate("tabs", false);</script>';
			$extra_scripts .= '<script type="text/javascript">SmartJS_BrowserUtils.RefreshParent();</script>';
			//--
		} else {
			//--
			if(!defined('SMART_PAGEBUILDER_DISABLE_DELETE')) {
				$bttns .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-delete.svg'.'" alt="'.self::text('ttl_del').'" title="'.self::text('ttl_del').'" style="cursor:pointer;" onClick="self.location=\''.\Smart::escape_js(self::composeUrl('op=record-delete&id='.\Smart::escape_url($query['id']))).'\';">';
				$bttns .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			} //end if
			$bttns .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-edit.svg'.'" alt="'.self::text('ttl_edt').'" title="'.self::text('ttl_edt').'" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#adm-page-props').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-edit-tab-props&id='.\Smart::escape_url($query['id'])))."', 'GET', 'html');".'">';
			if((string)$query['checksum'] != (string)$query['calc_checksum']) {
				$bttns .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$bttns .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$bttns .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$bttns .= '<img src="'.self::$ModulePath.'libs/views/manager/img/no-hash.svg'.'" alt="'.self::text('msg_invalid_cksum').'" title="'.self::text('msg_invalid_cksum').'" style="cursor:help;">';
			} //end if
			//--
			$fld_name = \Smart::escape_html($query['name']);
			$fld_pmode = \SmartComponents::html_select_list_single('pmode', $query['mode'], 'list', $arr_pmodes);
			$fld_ctrl = self::drawFieldCtrl($query['ctrl'], $is_subsegment, 'list');
			$fld_special = \SmartComponents::html_selector_true_false('', $query['special']);
			$fld_active = \SmartComponents::html_selector_true_false('', $query['active']);
			$fld_auth = \SmartComponents::html_selector_true_false('', $query['auth']);
			$fld_trans = \SmartComponents::html_selector_true_false('', $query['translations']);
			//--
			$fld_layout = self::drawListLayout($query['mode'], 'list', $query['layout']);
			//--
			$extra_form_start = '';
			$extra_form_end = '';
			$extra_scripts = '<script>SmartJS_BrowserUtils_PageAway = true;</script>';
			$extra_scripts .= '<script>SmartJS_BrowserUIUtils.Tabs_Activate("tabs", true);</script>';
			//--
		} //end if else
		//--
		$codetype = array();
		if($query['len_code'] > 0) {
			$codetype[] = self::text('record_code').'&nbsp;['.\Smart::escape_html(\SmartUtils::pretty_print_bytes((int)$query['len_code'],2)).']';
		} //end if
		if($query['len_data'] > 0) {
			$codetype[] = self::text('record_runtime').'&nbsp;['.\Smart::escape_html(\SmartUtils::pretty_print_bytes((int)$query['len_data'],2)).']';
		} //end if
		if(\Smart::array_size($codetype) > 0) {
			$codetype = (string) str_replace(' ', '&nbsp;', (string)implode('&nbsp;&nbsp;/&nbsp;&nbsp;', (array)$codetype));
		} else {
			$codetype = '';
		} //end if
		//--
		$arr_raw_langs = (array) \SmartTextTranslations::getListOfLanguages();
		$transl_arr = array();
		$show_translations = false;
		if(\Smart::array_size($arr_raw_langs) > 1) {
			$show_translations = true;
			$transl_arr = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordsTranslationsById($y_id);
		} //end if
		if(\Smart::array_size($transl_arr) > 0) {
			for($i=0; $i<count($transl_arr); $i++) {
				$transl_arr[$i] = (string) \SmartComponents::html_select_list_single('', (string)$transl_arr[$i], 'list', (array)$arr_raw_langs);
			} //end if
		} //end if
		if((string)$query['mode'] == 'settings') {
			$show_translations = false;
		} //end if
		//--
		$transl_cnt = (int) \Smart::array_size($transl_arr);
		//--
		$the_template = self::$ModulePath.'libs/views/manager/view-record-frm-props.mtpl.htm';
		//--
		$out = \SmartMarkersTemplating::render_file_template(
			(string) $the_template,
			[
				'MODE' 						=> (string) $y_mode,
				'IS-SEGMENT' 				=> (string) self::testIsSegmentPage($query['id']),
				'IS-SUBSEGMENT' 			=> (string) $is_subsegment ? 1 : 0,
				'BUTTONS'					=> (string) $bttns,
				'CODE-TYPE'					=> (string) $codetype,
				'TEXT-NAME'					=> (string) self::text('name'),
				'FIELD-NAME' 				=> (string) $fld_name,
				'TEXT-CTRL'					=> (string) self::text('ctrl'),
				'FIELD-CTRL' 				=> (string) $fld_ctrl,
				'TEXT-PMODE'				=> (string) self::text('record_syntax'),
				'FIELD-PMODE' 				=> (string) $fld_pmode,
				'TEXT-SPECIAL'				=> (string) self::text('special'),
				'FIELD-SPECIAL'				=> (string) $fld_special,
				'TEXT-ACTIVE'				=> (string) self::text('active'),
				'FIELD-ACTIVE'				=> (string) $fld_active,
				'TEXT-AUTH'					=> (string) self::text('login'),
				'FIELD-AUTH'				=> (string) $fld_auth,
				'TEXT-TRANS'				=> (string) self::text('translatable'),
				'FIELD-TRANS'				=> (string) $fld_trans,
				'MODULE-PATH' 				=> (string) self::$ModulePath,
				'TEXT-TRANSLATIONS' 		=> (string) self::text('translations'),
				'SHOW-TRANSLATIONS' 		=> (int)    $show_translations,
				'COUNT-TRANSLATIONS' 		=> (int)    $transl_cnt,
				'ARR-TRANSLATIONS' 			=> (array)  $transl_arr,
				'IS-TRANSLATABLE' 			=> (int)    $query['translations'],
				'WARN-TRANSLATABLE' 		=> (string) self::text('warn_translations'),
				'TEXT-LAYOUT'				=> (string) self::text('layout'),
				'FIELD-LAYOUT'				=> (string) $fld_layout,
				'MODE-PAGETYPE' 			=> (string) $query['mode'],
				'TEXT-REFS' 				=> (string) self::text('refs'),
				'ARR-REFS' 					=> (array)  $arr_refs,
				'URL-REF' 					=> (string) self::composeUrl('op=record-view&id=')
			]
		);
		//--
		return '<div id="adm-page-props" align="left">'.$extra_form_start.$out.$extra_form_end.'</div>'.$extra_scripts;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	// view or display form entry for Markup Code
	// $y_mode :: 'list' | 'form'
	public static function ViewFormMarkupCode($y_id, $y_mode, $y_lang='') {
		//--
		if(((string)$y_lang == '') OR (strlen($y_lang) != 2) OR \SmartTextTranslations::validateLanguage($y_lang) !== true) {
			$y_lang = '';
		} //end if
		//--
		if((string)$y_lang != '') {
			$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getTranslationCodeById($y_id, $y_lang);
		} else {
			$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordCodeById($y_id);
		} //end if else
		if((string)$query['id'] == '') {
			return \SmartComponents::operation_error('FormView Code // Invalid ID');
		} //end if
		//--
		$arr_raw_langs = (array) \SmartTextTranslations::getListOfLanguages();
		$arr_langs = [];
		$first_lang = true;
		foreach($arr_raw_langs as $key => $val) {
			if($first_lang) {
				$key = ''; // make empty key for the first language as this will be the default
				$first_lang = false;
			} //end if
			$arr_langs[(string)$key] = (string) $val;
		} //end foreach
		//--
		$tselect = '';
		if((string)$y_mode == 'form') {
			$tselmode = '';
		} else {
			$tselmode = 'form';
		} //end if else
		if(\Smart::array_size($arr_langs) > 1) {
			$tselect = (string) \SmartComponents::html_select_list_single(
				'language-select',
				(string) $y_lang,
				(string) $tselmode,
				(array) $arr_langs,
				'translate',
				'150/0', // $y_dimension
				'onChange="var theSelLang = String(jQuery(this).val()); self.location = \''.\Smart::escape_js(self::composeUrl('op=record-view&sop=code&id='.\Smart::escape_url($query['id']))).'\' + \'&translate=\' + SmartJS_CoreUtils.escape_url(theSelLang);"', // $y_custom_js
				'no', // $y_raw
				'no', // $y_allowblank
				'#JS-UI#' // $y_extrastyle
			);
		} //end if
		//--
		if($query['translations'] != 1) {
			$tselect = ''; // not translatable page
		} //end if
		//--
		$query['code'] = (string) base64_decode($query['code']);
		//--
		$translator_window = \SmartTextTranslations::getTranslator('@core', 'window');
		//--
		$query['code'] = (string) $query['code'];
		//--
		if((\SmartAuth::test_login_privilege('superadmin') === true) OR ((\SmartAuth::test_login_privilege('pagebuilder-edit') === true) AND ((string)$query['special'] != '1')) OR ((\SmartAuth::test_login_privilege('pagebuilder-edit') === true) AND (\SmartAuth::test_login_privilege('pagebuilder-manage') === true) AND ((string)$query['special'] == '1'))) {
			//--
			if((string)$y_mode == 'form') {
				//--
				$out = '';
				//--
				if((string)$query['mode'] == 'settings') {
					//--
					$out .= '<div align="center" title="'.\Smart::escape_html($query['code']).'"><img src="'.self::$ModulePath.'libs/views/manager/img/syntax-settings.svg" width="256" height="256" alt="Settings Page" title="Settings Page" style="opacity:0.7"></div>';
					//--
				} else {
					//-- EDITOR
					$out .= '<div id="code-editor" align="left">';
					if((string)$query['mode'] == 'raw') {
						$out .= '<font size="4" color="#FF7700"><b>&lt;<i>raw</i>&gt;</b>'.' - '.self::text('ttl_edtc').'</font>';
					} elseif((string)$query['mode'] == 'text') {
						$out .= '<font size="4" color="#007700"><b>&lt;<i>text</i>&gt;</b>'.' - '.self::text('ttl_edtc').'</font>';
					} elseif((string)$query['mode'] == 'markdown') {
						$out .= '<font size="4" color="#003399"><b>&lt;<i>markdown</i>&gt;</b>'.' - '.self::text('ttl_edtc').'</font>';
					} else { // html
						$out .= '<font size="4" color="#666699"><b>&lt;<i>html5</i>&gt;</b>'.' - '.self::text('ttl_edtc').'</font>';
					} //end if else
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= (string) $tselect;
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-save.svg'.'" alt="'.self::text('save').'" title="'.self::text('save').'" style="cursor:pointer;" onClick="'.\SmartComponents::js_ajax_submit_html_form('page_form_html', self::composeUrl('op=record-edit-do&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang))).'">';
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-back.svg'.'" alt="'.self::text('cancel').'" title="'.self::text('cancel').'" style="cursor:pointer;" onClick="'.\SmartComponents::js_code_ui_confirm_dialog('<h3>'.self::text('msg_unsaved').'</h3>'.'<br>'.'<b>'.\Smart::escape_html($translator_window->text('confirm_action')).'</b>', "SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#code-editor').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-view-tab-code&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang)))."', 'GET', 'html');").'">';
					$out .= (string) self::getPreviewButtons($query['id']);
					$out .= '</div>'."\n";
					$out .= '<form name="page_form_html" id="page_form_html" method="post" action="#" onsubmit="return false;">';
					$out .= '<input type="hidden" name="frm[form_mode]" value="code">';
					if((string)$y_lang != '') {
						$out .= '<input type="hidden" name="frm[language]" value="'.\Smart::escape_html((string)$y_lang).'">';
					} //end if
					if((string)$query['mode'] == 'raw') {
						$out .= \SmartComponents::html_js_editarea('pbld_code_editor', 'frm[code]', $query['code'], 'text', true, '885px', '70vh');
					} elseif((string)$query['mode'] == 'text') {
						$out .= \SmartComponents::html_js_editarea('pbld_code_editor', 'frm[code]', $query['code'], 'text', true, '885px', '70vh');
					} elseif((string)$query['mode'] == 'markdown') {
						$out .= \SmartComponents::html_js_editarea('pbld_code_editor', 'frm[code]', $query['code'], 'markdown', true, '885px', '70vh');
					} else {
					//	$out .= \SmartComponents::html_js_htmlarea('pbld_code_htmleditor', 'frm[code]', $query['code'], '885px', '70vh', true); // {{{SYNC-PAGEBUILDER-HTML-WYSIWYG}}}
						$out .= \SmartComponents::html_js_editarea('pbld_code_editor', 'frm[code]', $query['code'], 'html', true, '885px', '70vh');
					} //end if else
					$out .= "\n".'</form>'."\n";
					$out .= '<div align="left">';
					if((string)$query['mode'] == 'raw') {
						$out .= '<font size="4" color="#FF7700"><b>&lt;/<i>raw</i>&gt;</b></font>';
					} elseif((string)$query['mode'] == 'text') {
						$out .= '<font size="4" color="#007700"><b>&lt;/<i>text</i>&gt;</b></font>';
					} elseif((string)$query['mode'] == 'markdown') {
						$out .= '<font size="4" color="#003399"><b>&lt;/<i>markdown</i>&gt;</b></font>';
					} else { // html
						$out .= '<font size="4" color="#666699"><b>&lt;/<i>html5</i>&gt;</b></font>';
					} //end if else
					$out .= '</div>'."\n";
					$out .= '<script>SmartJS_BrowserUtils_PageAway = false;</script>';
					$out .= '<script>SmartJS_BrowserUIUtils.Tabs_Activate("tabs", false);</script>';
					$out .= '<script type="text/javascript">SmartJS_BrowserUtils.RefreshParent();</script>'; // not necessary
					//--
				} //end if else
				//--
			} else {
				//-- CODE VIEW
				$out = '';
				//--
				if((string)$query['mode'] == 'settings') {
					//--
					$out .= '<div align="center" title="'.\Smart::escape_html($query['code']).'"><img src="'.self::$ModulePath.'libs/views/manager/img/syntax-settings.svg" width="256" height="256" alt="Settings Page" title="Settings Page" style="opacity:0.7"></div>';
					//--
				} else {
					//--
					$out .= '<div id="code-viewer" align="left" style="min-height:35px;">';
					if((string)$query['mode'] == 'raw') {
						$out .= '<font size="4"><b>&lt;raw&gt;</b></font>';
					} elseif((string)$query['mode'] == 'text') {
						$out .= '<font size="4"><b>&lt;text&gt;</b></font>';
					} elseif((string)$query['mode'] == 'markdown') {
						$out .= '<font size="4"><b>&lt;markdown&gt;</b></font>';
					} else {
						$out .= '<font size="4"><b>&lt;html5&gt;</b></font>';
					} //end if else
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= (string) $tselect;
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-edit.svg'.'" alt="'.self::text('ttl_edtc').'" title="'.self::text('ttl_edtc').'" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#code-viewer').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-edit-tab-code&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang)))."', 'GET', 'html');".'">';
					//--
					if(((string)$y_mode == 'codeview') OR ((string)$y_mode == 'codesrcview')) {
						//--
						if((string)$query['mode'] == 'raw') {
							$out .= '</div>'."\n";
							$out .= \SmartComponents::html_js_editarea('pbld_code_editor', '', $query['code'], 'text', false, '885px', '70vh');
						} elseif((string)$query['mode'] == 'text') {
							$out .= '</div>'."\n";
							$out .= \SmartComponents::html_js_editarea('pbld_code_editor', '', $query['code'], 'text', false, '885px', '70vh');
						} elseif((string)$query['mode'] == 'markdown') {
							$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-preview.svg'.'" alt="'.self::text('record_sytx_html').'" title="'.self::text('record_sytx_html').'" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#code-viewer').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-preview-tab-code&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang)))."', 'GET', 'html');".'">';
							if((string)$y_mode == 'codesrcview') {
								$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
								$out .= '<img alt="'.self::text('record_sytx_mkdw').'" title="'.self::text('record_sytx_mkdw').'" src="'.self::$ModulePath.'libs/views/manager/img/syntax-markdown.svg'.'" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#code-viewer').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-view-tab-code&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang)))."', 'GET', 'html');".'">';
								$query['code'] = \SmartModExtLib\PageBuilder\Utils::renderMarkdown((string)$query['code']); // render on the fly
							} //end if
							$out .= '</div>'."\n";
							$out .= \SmartComponents::html_js_editarea('pbld_code_editor', '', $query['code'], 'markdown', false, '885px', '70vh');
						} else { // html
							$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-preview.svg'.'" alt="'.self::text('record_sytx_html').' Preview" title="'.self::text('record_sytx_html').' Preview" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#code-viewer').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-preview-tab-code&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang)))."', 'GET', 'html');".'">';
							$out .= '</div>'."\n";
							$out .= \SmartComponents::html_js_editarea('pbld_code_editor', '', $query['code'], 'html', false, '885px', '70vh');
						} //end if else
						//--
					} else { // view
						//--
						if(((string)$query['mode'] == 'raw') OR ((string)$query['mode'] == 'text')) {
							$out .= '</div>'."\n";
							$out .= \SmartComponents::operation_notice('FormView HTML Source // Raw or Text Pages does not have this feature ...', '100%');
						} else { // markdown / html
							if((string)$query['mode'] == 'markdown') {
								$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
								$out .= '<img alt="'.self::text('record_sytx_mkdw').'" title="'.self::text('record_sytx_mkdw').'" src="'.self::$ModulePath.'libs/views/manager/img/syntax-markdown.svg'.'" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#code-viewer').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-view-tab-code&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang)))."', 'GET', 'html');".'">';
							} //end if
							$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-view-code.svg'.'" alt="'.self::text('record_sytx_html').' Code" title="'.self::text('record_sytx_html').' Source" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#code-viewer').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-view-tab-code&mode=codesrcview&id='.\Smart::escape_url($query['id']).'&translate='.\Smart::escape_url($y_lang)))."', 'GET', 'html');".'">';
							$out .= '</div>'."\n";
							if((string)$query['mode'] == 'markdown') {
								$the_editor_styles = '<link rel="stylesheet" type="text/css" href="lib/core/plugins/css/markdown.css">';
								$query['code'] = \SmartModExtLib\PageBuilder\Utils::renderMarkdown((string)$query['code']); // render on the fly
							} else {
							//	$the_editor_styles = '<link rel="stylesheet" type="text/css" href="lib/js/jsedithtml/cleditor/jquery.cleditor.smartframeworkcomponents.css">'; // {{{SYNC-PAGEBUILDER-HTML-WYSIWYG}}}
								$query['code'] = (string) \SmartModExtLib\PageBuilder\Utils::fixSafeCode((string)$query['code']); // {{{SYNC-PAGEBUILDER-HTML-SAFETY}}} avoid PHP code + cleanup XHTML tag style
							} //end if else
							//$the_website_styles = '<link rel="stylesheet" type="text/css" href="etc/templates/website/styles.css">';
							$the_website_styles = '<style>* { font-family: tahoma,arial,sans-serif; font-smooth: always; } a, th, td, div, span, p, blockquote, pre, code { font-size:13px; }</style>';
							$out .= \SmartComponents::html_js_preview_iframe('pbld_code_editor', '<!DOCTYPE html><html><head>'.$the_website_styles.$the_editor_styles.'</head><body style="background:#FFFFFF;">'.$query['code'].'</body></html></html>', $y_width='885px', $y_height='70vh');
						} //end if else
						//--
					} //end if else
					//--
					$out .= '<div align="left">';
					if((string)$query['mode'] == 'raw') {
						$out .= '<font size="4"><b>&lt;/raw&gt;</b></font>';
					} elseif((string)$query['mode'] == 'text') {
						$out .= '<font size="4"><b>&lt;/text&gt;</b></font>';
					} elseif((string)$query['mode'] == 'markdown') {
						$out .= '<font size="4"><b>&lt;/markdown&gt;</b></font>';
					} else { // html
						$out .= '<font size="4"><b>&lt;/html5&gt;</b></font>';
					} //end if else
					$out .= '</div>'."\n";
					$out .= '<script>SmartJS_BrowserUtils_PageAway = true; SmartJS_BrowserUIUtils.Tabs_Activate("tabs", true);</script>';
					//--
				} //end if else
				//--
			} //end if else
			//--
		} else {
			//--
			if((string)$y_mode == 'form') {
				$msg = self::text('msg_no_priv_edit');
			} else {
				$msg = self::text('msg_no_priv_read');
			} //end if else
			//--
			$out = \SmartComponents::operation_notice($msg);
			//--
		} //end if else
		//--
		return $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	// view or display form entry for YAML Code
	// $y_mode :: 'list' | 'form'
	public static function ViewFormYamlData($y_id, $y_mode) {
		//--
		$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordDataById($y_id);
		if((string)$query['id'] == '') {
			return \SmartComponents::operation_error('FormView YAML Data // Invalid ID');
		} //end if
		//--
		$translator_window = \SmartTextTranslations::getTranslator('@core', 'window');
		//--
		$query['data'] = (string) base64_decode($query['data']);
		//--
		if((\SmartAuth::test_login_privilege('superadmin') === true) OR ((\SmartAuth::test_login_privilege('pagebuilder-edit') === true) AND (\SmartAuth::test_login_privilege('pagebuilder-data-edit') === true) AND ((string)$query['special'] != '1')) OR ((\SmartAuth::test_login_privilege('pagebuilder-edit') === true) AND (\SmartAuth::test_login_privilege('pagebuilder-data-edit') === true) AND (\SmartAuth::test_login_privilege('pagebuilder-manage') === true) AND ((string)$query['special'] == '1'))) {
			//--
			if((string)$y_mode == 'form') {
				//-- CODE EDITOR
				$out = '';
				$out .= '<div align="left" id="yaml-editor"><font size="4" color="#003399"><b>&lt;<i>yaml</i>&gt;</b>'.' - '.self::text('ttl_edtac').'</font>';
				$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-save.svg'.'" alt="'.self::text('save').'" title="'.self::text('save').'" style="cursor:pointer;" onClick="'.\SmartComponents::js_ajax_submit_html_form('page_form_yaml', self::composeUrl('op=record-edit-do&id='.\Smart::escape_url($query['id']))).'">';
				$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-back.svg'.'" alt="'.self::text('cancel').'" title="'.self::text('cancel').'" style="cursor:pointer;" onClick="'.\SmartComponents::js_code_ui_confirm_dialog('<h3>'.self::text('msg_unsaved').'</h3>'.'<br>'.'<b>'.\Smart::escape_html($translator_window->text('confirm_action')).'</b>', "SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#yaml-editor').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-view-tab-data&id='.\Smart::escape_url($query['id'])))."', 'GET', 'html');").'">';
				$out .= (string) self::getPreviewButtons($query['id']);
				$out .= '</div>'."\n";
				$out .= '<form class="ux-form" name="page_form_yaml" id="page_form_yaml" method="post" action="#" onsubmit="return false;">';
				$out .= '<input type="hidden" name="frm[form_mode]" value="yaml">';
				$out .= \SmartComponents::html_js_editarea('record_sytx_yaml', 'frm[data]', $query['data'], 'yaml', true, '885px', '70vh'); // OK.new
				$out .= "\n".'</form>'."\n";
				$out .= '<div align="left"><font size="4" color="#003399"><b>&lt;/<i>yaml</i>&gt;</b></font></div>'."\n";
				$out .= '<script>SmartJS_BrowserUtils_PageAway = false;</script>';
				$out .= '<script>SmartJS_BrowserUIUtils.Tabs_Activate("tabs", false);</script>';
				$out .= '<script type="text/javascript">SmartJS_BrowserUtils.RefreshParent();</script>'; // not necessary
				//--
			} else {
				//-- CODE VIEW
				$out = '';
				$out .= '<div align="left" id="yaml-viewer"><font size="4"><b>&lt;yaml&gt;</b></font>';
				$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-edit.svg'.'" alt="'.self::text('ttl_edtac').'" title="'.self::text('ttl_edtac').'" style="cursor:pointer;" onClick="'."SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(jQuery('#yaml-viewer').parent().prop('id'), 'lib/framework/img/loading-bars.svg', '".\Smart::escape_js(self::composeUrl('op=record-edit-tab-data&id='.\Smart::escape_url($query['id'])))."', 'GET', 'html');".'">';
				$out .= '</div>'."\n";
				$out .= \SmartComponents::html_js_editarea('record_sytx_yaml', '', $query['data'], 'yaml', false, '885px', '70vh'); // OK.new
				$out .= '<div align="left"><font size="4"><b>&lt;/yaml&gt;</b></font></div>'."\n";
				$out .= '<script>SmartJS_BrowserUtils_PageAway = true; SmartJS_BrowserUIUtils.Tabs_Activate("tabs", true);</script>';
				//--
			} //end if else
			//--
		} else {
			//--
			if((string)$y_mode == 'form') {
				$msg = self::text('msg_no_priv_edit');
			} else {
				$msg = self::text('msg_no_priv_read');
			} //end if else
			//--
			$out = \SmartComponents::operation_notice($msg);
			//--
		} //end if else
		//--
		return $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	// view or display form entry for INFO
	// $y_mode :: 'list'
	public static function ViewFormInfo($y_id, $y_mode) {
		//--
		$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordInfById($y_id);
		if((string)$query['id'] == '') {
			return \SmartComponents::operation_error('FormView Info // Invalid ID');
		} //end if
		//--
		$the_template = self::$ModulePath.'libs/views/manager/view-record-info.mtpl.htm';
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			(string) $the_template,
			[
				'TEXT-MODIFIED'			=> (string) self::text('modified'),
				'FIELD-MODIFIED' 		=> (string) \Smart::escape_html($query['modified']),
				'TEXT-ADMIN'			=> (string) self::text('admin'),
				'FIELD-ADMIN' 			=> (string) \Smart::escape_html($query['admin']),
				'TEXT-PUBLISHED'		=> (string) self::text('published'),
				'FIELD-PUBLISHED' 		=> (string) \Smart::escape_html(date('Y-m-d H:i:s', $query['published'])),
				'TEXT-COUNTER'			=> (string) self::text('counter'),
				'FIELD-COUNTER' 		=> (string) \Smart::escape_html($query['counter'])
			]
		);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewFormAdd() {
		//--
		$translator_window = \SmartTextTranslations::getTranslator('@core', 'window');
		//--
		$out = '';
		//-- SMART_PAGEBUILDER_DISABLE_PAGES
		$arr_objects_segments = [
			'#OPTGROUP#Segments' => 'Segments',
				'html-segment' 		=> 'Segment Page - HTML Syntax',
				'markdown-segment' 	=> 'Segment Page - Markdown Syntax',
				'text-segment' 		=> 'Segment Page - Text Syntax',
				'settings-segment' 	=> 'Segment Page - Settings'
		];
		$arr_objects_pages = [
			'#OPTGROUP#Pages' => 'Pages',
				'html-page' 		=> 'Page - HTML Syntax',
				'markdown-page' 	=> 'Page - Markdown Syntax',
				'text-page' 		=> 'Page - Text Syntax',
				'raw-page' 			=> 'Page - Raw'
		];
		if(\SmartModExtLib\PageBuilder\Utils::allowPages() === true) {
			$arr_objects = (array) array_merge((array)$arr_objects_pages, (array)$arr_objects_segments);
		} else {
			$arr_objects = (array) $arr_objects_segments;
		} //end if else
		//--
		$out .= '<script>'.\SmartComponents::js_code_init_away_page('The changes will be lost !').'</script>';
		$out .= \SmartMarkersTemplating::render_file_template(
			(string) self::$ModulePath.'libs/views/manager/view-record-frm-add.mtpl.htm',
			[
				'BUTTONS-CLOSE' 	=> (string) '<input type="button" value="'.\Smart::escape_html($translator_window->text('button_close')).'" class="ux-button" onClick="SmartJS_BrowserUtils.CloseModalPopUp(); return false;">',
				'THE-TTL' 			=> (string) '<img height="16" src="'.self::$ModulePath.'libs/views/manager/img/op-add.svg'.'" alt="'.self::text('ttl_add').'" title="'.self::text('ttl_add').'">'.'&nbsp;'.self::text('ttl_add'),
				'REFRESH-PARENT' 	=> (string) '<script type="text/javascript">SmartJS_BrowserUtils.RefreshParent();</script>',
				'FORM-NAME' 		=> (string) 'page_form_add',
				'LABELS-TYPE'		=> (string) self::text('record_syntax'),
				'CONTROLS-TYPE' 	=> (string) \SmartComponents::html_select_list_single('ptype', '', 'form', (array)$arr_objects, 'frm[ptype]', '275/0', '', 'no', 'yes'),
				'LABELS-ID'			=> (string) self::text('id'),
				'LABELS-NAME'		=> (string) self::text('name'),
				'LABELS-CTRL' 		=> (string) self::text('ctrl'),
				'BUTTONS-SUBMIT' 	=> (string) '<button class="ux-button ux-button-highlight" type="button" onClick="'.\SmartComponents::js_ajax_submit_html_form('page_form_add', self::composeUrl('op=record-add-do')).' return false;">'.' &nbsp; '.'<i class="fa fa-floppy-o"></i>'.' &nbsp; '.self::text('save').'</button>'
			],
			'no'
		);
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewFormsSubmit($y_mode, $y_frm, $y_id='', $y_redir=true) {
		//--
		$y_frm = (array) $y_frm;
		$y_id = (string) trim((string)$y_id);
		//--
		$data = array();
		$error = '';
		$redirect = '';
		$rdr_sufx = '';
		//--
		$proc_write_ok = false; 	// only if true will run the insert or update query
		$proc_id = ''; 				// '' for insert | 'the-uid' for update
		$proc_mode = ''; 			// insert | update
		$proc_upd_cksum = false;	// if true will update the page checksum: id+data
		//--
		switch((string)$y_mode) {
			//--
			case 'add': // OK
				//--
				$proc_mode = 'insert';
				//--
				if((\SmartAuth::test_login_privilege('superadmin') === true) OR (\SmartAuth::test_login_privilege('pagebuilder-create') === true)) {
					//--
					$y_frm['id'] = (string) trim((string)$y_frm['id']);
					//--
					if(strlen($y_frm['id']) >= 2) { // in DB we have a constraint to be minimum 2 characters
						//--
						$data = array();
						//--
						$data['id'] = (string) $y_frm['id'];
						$data['id'] = (string) \Smart::safe_validname($data['id'], ''); // allow: [a-z0-9] _ - . @
						$data['id'] = (string) str_replace(array('.', '@'), array('-', '-'), (string)$data['id']); // dissalow: . @ [@ is for special pages ; . will conflict with SmartFramework style pages like module.page when using Semantic URL Rules ; @ is reserved for special pages ]
						//--
						switch((string)$y_frm['ptype']) {
							case 'settings-segment':
								$data['id'] = '#'.$data['id']; // segment page
								$data['mode'] = 'settings';
								break;
							case 'text-segment':
								$data['id'] = '#'.$data['id']; // segment page
								$data['mode'] = 'text';
								break;
							case 'markdown-segment':
								$data['id'] = '#'.$data['id']; // segment page
								$data['mode'] = 'markdown';
								break;
							case 'html-segment':
								$data['id'] = '#'.$data['id']; // segment page
								$data['mode'] = 'html';
								break;
							case 'raw-page':
								$data['mode'] = 'raw';
								break;
							case 'text-page':
								$data['mode'] = 'text';
								break;
							case 'markdown-page':
								$data['mode'] = 'markdown';
								break;
							case 'html-page':
								$data['mode'] = 'html';
								break;
							default:
								$error = self::text('err_9')."\n"; // invalid object type
						} //end switch
						//--
						$redirect = self::composeUrl('op=record-view&id='.\Smart::escape_url($data['id']));
						//--
						$data['ref'] = '[]'; // reference parent, by default is empty json array []
						$data['name'] = (string) trim((string)$y_frm['name']);
						$data['active'] = '0'; // the page will be inactive at creation time
						$data['ctrl'] = (string) \SmartUnicode::sub_str((string)trim((string)$y_frm['ctrl']), 0, 128);
						$data['published'] = time();
						//--
						if((string)$error == '') {
							if(((string)$data['id'] == '') OR ((string)$data['id'] == '#')) {
								$error = self::text('err_4')."\n"; // invalid (empty) ID
							} //end if
						} //end if
						if((string)$error == '') {
							$chk_id = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordIdsById($data['id']);
							if(strlen($chk_id['id']) > 0) {
								$error = self::text('err_3')."\n"; // duplicate ID
							} //end if
						} //end if
						if((string)$error == '') {
							if((string)$data['name'] == '') {
								$error = self::text('err_6')."\n"; // invalid (empty) Title
							} //end if
						} //end if
						//--
						if((string)$error == '') {
							//--
							$proc_write_ok = true;
							//--
						} // end if else
						//--
					} else {
						//--
						$error = self::text('err_4')."\n";
						//--
					} // end if else
					//--
				} else {
					//--
					$error = self::text('msg_no_priv_add')."\n";
					//--
				} // end if else
				//--
				break;
			//--
			case 'edit':
				//--
				$proc_mode = 'update';
				//--
				$query = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordDetailsById($y_id);
				//--
				if(((string)$y_id == (string)$query['id']) AND ((\SmartAuth::test_login_privilege('superadmin') === true) OR ((\SmartAuth::test_login_privilege('pagebuilder-edit') === true) AND ((string)$query['special'] != '1')) OR ((\SmartAuth::test_login_privilege('pagebuilder-edit') === true) AND (\SmartAuth::test_login_privilege('pagebuilder-manage') === true) AND ((string)$query['special'] == '1')))) {
					//--
					$proc_id = (string) $query['id'];
					//--
					if((string)$y_frm['form_mode'] == 'props') { // PROPS
						//--
						$redirect = self::composeUrl('op=record-view&id='.\Smart::escape_url($query['id']));
						//--
						$data = array();
						//--
						$data['name'] = (string) \SmartUnicode::sub_str((string)trim((string)$y_frm['name']), 0, 255);
						if((string)$error == '') {
							if((string)$data['name'] == '') {
								$error = self::text('err_6')."\n"; // invalid (empty) Title
							} //end if
						} //end if
						//--
						$data['ctrl'] = (string) \SmartUnicode::sub_str((string)trim((string)$y_frm['ctrl']), 0, 128);
						//--
						$data['translations'] = (int) $y_frm['translations'];
						if($data['translations'] !== 0) {
							$data['translations'] = 1;
						} //end if
						//--
						if(((\SmartAuth::test_login_privilege('superadmin') !== true) AND (\SmartAuth::test_login_privilege('pagebuilder-manage') !== true)) AND ((string)$query['special'] == '0') AND ((string)$y_frm['special'] == '1')) {
							// avoid unprivileged admins to mark a page as special
						} else {
							//--
							$data['special'] = \Smart::format_number_int($y_frm['special'], '+');
							if(((string)$data['special'] != '0') AND ((string)$data['special'] != '1')) {
								$data['special'] = '0';
							} //end if
							//--
						} //end if
						//--
						if(!self::testIsSegmentPage($query['id'])) {
							//--
							$data['active'] = \Smart::format_number_int($y_frm['active'], '+');
							if(((string)$data['active'] != '0') AND ((string)$data['active'] != '1')) {
								$data['active'] = '1';
							} //end if
							//--
							$data['auth'] = \Smart::format_number_int($y_frm['auth'], '+');
							if(((string)$data['auth'] != '0') AND ((string)$data['auth'] != '1')) {
								$data['auth'] = '0';
							} //end if
							//--
							$data['mode'] = strtolower(trim($y_frm['mode']));
							switch((string)$data['mode']) {
								case 'raw':
									$data['mode'] = 'raw';
									break;
								case 'text':
									$data['mode'] = 'text';
									break;
								case 'markdown':
									$data['mode'] = 'markdown';
									break;
								case 'html':
								default:
									$data['mode'] = 'html';
							} //end switch
							//--
							$data['layout'] = (string) trim((string)$y_frm['layout']);
							if(strlen((string)$data['layout']) > 75) {
								$data['layout'] = ''; // fix to avoid DB overflow
							} //end if
							if((string)$data['mode'] == 'raw') {
								$data['layout'] = ''; // force for raw pages
							} //end if
							//--
						} else {
							//--
							$data['active'] = 0;
							$data['auth'] = 0;
							//--
							$data['mode'] = (string) strtolower((string)trim((string)$y_frm['mode']));
							switch((string)$data['mode']) {
								case 'settings':
									$data['mode'] = 'settings';
									$data['code'] = '';
									break;
								case 'text':
									$data['mode'] = 'text';
									break;
								case 'markdown':
									$data['mode'] = 'markdown';
									break;
								case 'html':
								default:
									$data['mode'] = 'html';
							} //end switch
							//--
							$data['layout'] = '';
							//--
						} //end if
						//--
						$proc_write_ok = true;
						//--
					} elseif((string)$y_frm['form_mode'] == 'code') { // CODE
						//--
						$proc_upd_cksum = true;
						//--
						if((string)$y_frm['data'] == '') { // frm[data] must not be set here
							//--
							$redirect = self::composeUrl('op=record-view&id='.\Smart::escape_url($query['id']).'&sop=code');
							//--
							$data = array();
							//--
							if((string)trim((string)$y_frm['code']) == '') {
								//--
								$data['code'] = ''; // avoid save empty with only spaces
								//--
							} else {
								//--
								$data['code'] = (string) $y_frm['code'];
								//--
								/*
								if(((string)$query['mode'] == 'markdown') OR ((string)$query['mode'] == 'html')) {
									// {{{SYNC-PAGEBUILDER-HTML-SAFETY}}} :: fixSafeCode is managed later on display
								} elseif((string)$query['mode'] == 'raw') {
									// {{{SYNC-PAGEBUILDER-RAWPAGE-SAFETY}}} :: managed later on display, depends on mime type
								} //end if
								*/
								//--
								$data['code'] = (string) str_replace(["\r\n", "\r"], "\n", (string)$data['code']); 		// normalize line endings
								$data['code'] = (string) str_replace(["\x0B", "\0", "\f"], ' ', (string)$data['code']); // fix weird characters
								$data['code'] = (string) preg_replace('/[ ]+[\\n]/', "\n", (string)$data['code']); 		// remove empty line spaces
								//--
								$data['code'] = (string) base64_encode((string)$data['code']);
								//--
							} //end if
							//--
							$y_frm['code'] = ''; // free mem
							//--
							if((int)strlen((string)$data['code']) > (int)self::$MaxStrCodeSize) {
								$error = 'Page Code is OVERSIZED !'."\n";
							} //end if
							//--
							if((string)$error == '') {
								$proc_write_ok = true;
							} //end if
							//--
						} else {
							//--
							$error = self::text('err_7').' (2)'."\n";
							//--
						} //end if else
						//--
					} elseif((string)$y_frm['form_mode'] == 'yaml') { // YAML
						//--
						$proc_upd_cksum = true;
						//--
						if((string)$y_frm['code'] == '') { // frm[code] must not be set here
							//--
							if((\SmartAuth::test_login_privilege('superadmin') === true) OR (\SmartAuth::test_login_privilege('pagebuilder-data-edit') === true)) {
								//--
								$redirect = self::composeUrl('op=record-view&id='.\Smart::escape_url($query['id']).'&sop=yaml');
								//--
								$data = array();
								//--
								if((string)trim((string)$y_frm['data']) == '') {
									$data['data'] = ''; // avoid save empty with only spaces
								} else {
									$data['data'] = (string) str_replace(["\r\n", "\r"], "\n", (string)$y_frm['data']); // normalize line endings
									$data['data'] = (string) str_replace(["\x0B", "\0", "\f"], ' ', (string)$data['data']); // fix weird characters
									$data['data'] = (string) base64_encode((string)$data['data']); // encode data b64 (encode must be here because will be transmitted later as B64 encode and must cover all error situations)
								} //end if
								$y_frm['data'] = '';
								//--
								if((int)strlen($data['data']) > (int)(self::$MaxStrCodeSize/10)) {
									$error = 'Page Data is OVERSIZED !'."\n";
								} //end if
								//--
								if((string)$error == '') {
									$proc_write_ok = true;
								} //end if
								//--
							} else {
								//--
								$error = self::text('msg_no_priv_edit')."\n";
								//--
							} //end if else
							//--
						} else {
							//--
							$error = self::text('err_7').' (3)'."\n";
							//--
						} //end if else
						//--
					} else {
						//--
						$error = 'Invalid Operation !';
						//--
					} //end if else
					//--
				} else {
					//--
					$error = self::text('msg_no_priv_edit')."\n";
					//--
				} //end if else
				//--
				break;
			//--
			default: // OK
				//--
				$error = self::text('err_2')."\n";
				//--
		} // end switch
		//--
		if((string)$error == '') {
			//--
			if($proc_write_ok) {
				//--
				if(\Smart::array_size($data) > 0) {
					//--
					$data['admin'] = \SmartAuth::get_login_id();
					$data['modified'] = date('Y-m-d H:i:s');
					//--
					if((string)$proc_mode == 'insert') {
						$wr = \SmartModDataModel\PageBuilder\PageBuilderBackend::insertRecord($data);
					} elseif((string)$proc_mode == 'update') {
						if((string)$y_frm['language'] != '') {
							$rdr_sufx = '&translate='.\Smart::escape_url((string)$y_frm['language']);
							$wr = \SmartModDataModel\PageBuilder\PageBuilderBackend::updateTranslationById($proc_id, $y_frm['language'], $data);
						} else {
							$wr = \SmartModDataModel\PageBuilder\PageBuilderBackend::updateRecordById($proc_id, $data, $proc_upd_cksum);
						} //end if
					} else {
						$wr = -100; // invalid op mode
					} //end if else
					//--
					if($wr !== 1) {
						$error = self::text('err_5').' @ '.$wr."\n";
					} // end if else
					//--
				} else {
					//--
					$error = 'Internal ERROR ... (Data is Empty)';
					//--
				} //end if else
				//--
			} //end if
			//--
		} // end if
		//--
		if((string)$error == '') {
			//--
			$result = 'OK';
			$title = '*';
			$message = '<font size="3"><b>'.self::text('op_compl').'</b></font>';
			if($y_redir !== true) {
				$redirect = (string) $y_redir;
			} //end if
			if((string)$redirect != '') {
				$redirect .= $rdr_sufx;
			} //end if
			//--
		} else {
			//--
			$result = 'ERROR';
			$title = self::text('op_ncompl');
			$message = '<font size="3"><b>'.$error.'</b></font>';
			$redirect = ''; // avoid redirect if error
			//--
		} //end if
		//--
		return (string) \SmartComponents::js_ajax_replyto_html_form($result, $title, $message, $redirect);
		//--
	} // END FUNCTION
	//==================================================================


	//==================================================================
	/**
	 * Delete a page
	 *
	 * @param string $y_id
	 * @param string $y_delete
	 * @return string
	 */
	public static function ViewFormDelete($y_id, $y_delete) {

		//--
		$tmp_rd_arr = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordDetailsById($y_id);
		//--
		if((string)$tmp_rd_arr['id'] == '') {
			return \SmartComponents::operation_error(self::text('err_4'));
		} //end if
		//--

		//-- very special page override
		if((substr((string)$tmp_rd_arr['id'], 0, 1) == '@') OR (substr((string)$tmp_rd_arr['id'], 0, 2) == '#@')) {
			$tmp_allow_deletion = false;
		} else {
			$tmp_allow_deletion = true;
		} //end if else
		//--

		//--
		$out = '';
		//--
		if((string)$y_delete == 'yes') {
			//--
			if($tmp_allow_deletion) {
				//--
				if((\SmartAuth::test_login_privilege('superadmin') === true) OR ((\SmartAuth::test_login_privilege('pagebuilder-delete') === true) AND ((string)$tmp_rd_arr['special'] != '1')) OR ((\SmartAuth::test_login_privilege('pagebuilder-delete') === true) AND (\SmartAuth::test_login_privilege('pagebuilder-manage') === true) AND ((string)$tmp_rd_arr['special'] == '1'))) {
					//--
					$rdw = '<script type="text/javascript">'.\SmartComponents::js_code_wnd_redirect(self::composeUrl('op=record-view&id='.\Smart::escape_url($tmp_rd_arr['id'])), 3000).'</script>';
					//--
					$out .= '<script type="text/javascript">'.\SmartComponents::js_code_wnd_refresh_parent().'</script>';
					//--
					$chk_del = (int) \SmartModDataModel\PageBuilder\PageBuilderBackend::deleteRecordById($tmp_rd_arr['id']);
					//--
					if($chk_del == 1) {
						$out .= '<br>'.\SmartComponents::operation_ok(self::text('op_compl'));
						$out .= '<script type="text/javascript">'.\SmartComponents::js_code_wnd_close_modal_popup().'</script>'; // ok
					} elseif($chk_del == -1) {
						$out .= '<br>'.\SmartComponents::operation_warn('Delete Failed: Empty ID');
						$out .= $rdw;
					} elseif($chk_del == -2) {
						$out .= '<br>'.\SmartComponents::operation_notice('Delete Canceled: The selected segment is in use in other pages or segments. Relations must be cleared first !');
						$out .= $rdw;
					} else {
						$out .= '<br>'.\SmartComponents::operation_error('Something goes really wrong ... Delete returned an invalid number rows: '.$chk_del);
						$out .= $rdw;
					} //end if else
					//--
				} else {
					//--
					$out .= '<br>'.\SmartComponents::operation_error(self::text('msg_no_priv_del'));
					$out .= '<script type="text/javascript">'.\SmartComponents::js_code_wnd_refresh_parent().'</script>';
				$out .= '<script type="text/javascript">'.\SmartComponents::js_code_wnd_close_modal_popup(1500).'</script>'; // ok
					//--
				} //end if else
				//--
			} else {
				//--
				$out .= '<br>'.\SmartComponents::operation_warn(self::text('err_8'));
				$out .= '<script type="text/javascript">'.\SmartComponents::js_code_wnd_refresh_parent().'</script>';
				$out .= '<script type="text/javascript">'.\SmartComponents::js_code_wnd_close_modal_popup(2500).'</script>'; // ok
				//--
			} //end if else
			//--
		} else {
			//--
			$out .= \SmartComponents::operation_question(self::text('ttl_del').' ?<div style="display:inline-block; margin-left:100px; min-width:200px;"><a class="ux-button ux-button-special" onClick="'.\Smart::escape_html(\SmartComponents::js_code_ui_confirm_dialog('<h1>'.self::text('msg_confirm_del').' !</h1>', 'self.location=\''.self::composeUrl('op=record-delete&delete=yes&id='.\Smart::escape_url($y_id)).'\';', '550', '250', self::text('dp').' ?')).'; return false;" href="#">Yes</a><a class="ux-button ux-button-primary" href="'.\Smart::escape_html(self::composeUrl('op=record-view&id='.\Smart::escape_url($y_id))).'">No</a></div>', '720');
			$out .= self::ViewDisplayRecord((string)$y_id, 0);
			//--
		} //end if else
		//--

		//--
		return $out;
		//--

	} // END FUNCTION
	//==================================================================


	//##### PRIVATES #####


	//==================================================================
	private static function composeUrl($y_suffix) {
		//--
		return (string) \Smart::url_add_suffix(
			(string) self::$ModuleScript.'?/'.\Smart::escape_url(self::$ModulePageURLParam).'/'.\Smart::escape_url(self::$ModulePageURLId),
			(string) $y_suffix
		);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function testIsSegmentPage($y_id) {
		//--
		$out = 0;
		//--
		if(substr((string)$y_id, 0, 1) == '#') {
			$out = 1;
		} //endd if
		//--
		return (int) $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function getPreviewButtons($id) {
		//--
		$out = '';
		//--
		$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		//--
		$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-preview-code.svg'.'" alt="'.self::text('pw_code').'" title="'.self::text('pw_code').'" style="cursor:pointer;" onClick="SmartJS_BrowserUtils.PopUpLink(\''.\Smart::escape_js(self::composeUrl('op=record-view-highlight-code&id='.\Smart::escape_url($id))).'\', \'page-builder-pw\', null, null, 1); return false;">';
		$out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$out .= '<img src="'.self::$ModulePath.'libs/views/manager/img/op-preview-data.svg'.'" alt="'.self::text('pw_data').'" title="'.self::text('pw_data').'" style="cursor:pointer;" onClick="SmartJS_BrowserUtils.PopUpLink(\''.\Smart::escape_js(self::composeUrl('op=record-view-highlight-data&id='.\Smart::escape_url($id))).'\', \'page-builder-pw\', null, null, 1); return false;">';
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function getImgForPageType($y_id) {
		//--
		if(self::testIsSegmentPage($y_id)) { // segment
			$img = self::$ModulePath.'libs/views/manager/img/type-segment.svg';
		} else { // page
			$img = self::$ModulePath.'libs/views/manager/img/type-page.svg';
		} //end if else
		//--
		return (string) $img;
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function getImgForRef($y_ref) {
		//--
		$y_ref = (string) trim((string)$y_ref);
		//--
		if((string)$y_ref == '') {
			return '';
		} //end if
		//--
		if((string)$y_ref == '-') {
			return '<img height="16" src="'.self::$ModulePath.'libs/views/manager/img/ref-n-a.svg'.'" alt="-" title="-">'; // for pages that cannot be assigned with a ref (ex: website menu)
		} //end if
		//--
		return '<img height="16" src="'.self::$ModulePath.'libs/views/manager/img/ref-parent.svg'.'" alt="'.\Smart::escape_html($y_ref).'" title="'.\Smart::escape_html($y_ref).'">';
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function getImgForCodeType($y_id, $y_type) {
		//--
		$ttl = '[Unknown] Page';
		$img = self::$ModulePath.'libs/views/manager/img/syntax-unknown.svg';
		//--
		if(self::testIsSegmentPage($y_id)) {
			switch((string)$y_type) {
				case 'settings':
					$ttl = 'SETTINGS Segment';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-settings.svg';
					break;
				case 'text':
					$ttl = 'TEXT Segment';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-text.svg';
					break;
				case 'markdown':
					$ttl = 'MARKDOWN Segment';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-markdown.svg';
					break;
				case 'html':
					$ttl = 'HTML Segment';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-html.svg';
					break;
				default:
					// unknown
			} //end switch
		} else {
			switch((string)$y_type) {
				case 'raw':
					$ttl = 'RAW Page';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-raw.svg';
					break;
				case 'text':
					$ttl = 'TEXT Page';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-text.svg';
					break;
				case 'markdown':
					$ttl = 'MARKDOWN Page';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-markdown.svg';
					break;
				case 'html':
					$ttl = 'HTML Page';
					$img = self::$ModulePath.'libs/views/manager/img/syntax-html.svg';
				default:
					// unknown
			} //end switch
		} //end if else
		//--
		return '<img height="16" src="'.$img.'" alt="'.$ttl.'" title="'.$ttl.'">';
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function getImgForRestrictionsStatus($y_id, $y_status) {
		//--
		if(self::testIsSegmentPage($y_id)) {
			$img = self::$ModulePath.'libs/views/manager/img/restr-private.svg';
			$ttl = self::text('restr_acc');
		} elseif($y_status == 1) {
			$img = self::$ModulePath.'libs/views/manager/img/restr-login.svg';
			$ttl = self::text('login_acc');
		} else {
			$img = self::$ModulePath.'libs/views/manager/img/restr-public.svg';
			$ttl = self::text('free_acc');
		} //end if else
		//--
		return '<img height="16" src="'.$img.'" alt="'.$ttl.'" title="'.$ttl.'">';
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function getImgForActiveStatus($y_id, $y_status) {
		//--
		if(self::testIsSegmentPage($y_id)) {
			return '';
		} else {
			switch((string)$y_status) {
				case '1':
					$img = self::$ModulePath.'libs/views/manager/img/status-active.svg';
					$ttl = self::text('yes');
					break;
				case '0':
				default:
					$img = self::$ModulePath.'libs/views/manager/img/status-inactive.svg';
					$ttl = self::text('no');
			} //end switch
		} //end if else
		//--
		return '<img src="'.$img.'" alt="'.$ttl.'" title="'.$ttl.'">';
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function getImgForSpecialStatus($y_status) {
		//--
		switch((string)$y_status) {
			case '1':
				$img = self::$ModulePath.'libs/views/manager/img/admin-special.svg';
				$ttl = self::text('yes');
				break;
			case '0':
			default:
				$img = self::$ModulePath.'libs/views/manager/img/admin-default.svg';
				$ttl = self::text('no');
		} //end switch
		//--
		return '<img src="'.$img.'" alt="'.$ttl.'" title="'.$ttl.'">';
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function drawFieldCtrl($y_id, $y_issubsegment, $y_mode, $y_var='', $y_width='65') {
		//--
		if((string)$y_mode == 'form') {
			if($y_issubsegment === true) {
				$prop_placeholder = 'Controller Name (N/A)';
				$prop_readonly = ' readonly disabled';
			} else {
				$prop_placeholder = 'Controller Name';
				$prop_readonly = '';
			} //end if else
			return (string) '<input type="text" name="'.\Smart::escape_html((string)$y_var).'" value="'.\Smart::escape_html((string)$y_id).'" size="'.\Smart::format_number_int($y_width,'+').'" maxlength="128" autocomplete="off" placeholder="'.\Smart::escape_html($prop_placeholder).'"'.$prop_readonly.'>';
		} else {
			return (string) \Smart::escape_html($y_id);
		} //end if else
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	private static function drawListLayout($y_mode, $y_listmode, $y_value, $y_htmlvar='') {
		// TO BE DONE ...
		//--
		return \SmartComponents::html_select_list_single('', $y_value, $y_listmode, (array)\SmartModExtLib\PageBuilder\Utils::getAvailableLayouts(), $y_htmlvar, '250', '', 'no', 'no');
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayTree($y_tpl, $srcby, $src) {
		//--
		$flimit = 500; // filter limit
		//--
		$src = (string) trim((string)$src);
		if((string)trim((string)$src) == '') {
			$srcby = '';
		} elseif((string)trim((string)$srcby) == '') {
			$src = '';
		} //end if
		//--
		$collapse = 'collapsed';
		$fcollapse = '';
		$filter = array();
		if(((string)trim((string)$src) != '') AND ((string)trim((string)$srcby) != '')) {
			$tmp_filter = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::listGetRecords($srcby, $src, (int)$flimit, 0, 'ASC', 'id');
			for($i=0; $i<\Smart::array_size($tmp_filter); $i++) {
				$filter[] = [ 'id' => (string)$tmp_filter[$i]['id'], 'hash-id' => (string)sha1((string)$tmp_filter[$i]['id']) ];
			} //end for
			$tmp_filter = array();
		} //end if
		if(\Smart::array_size($filter) > 0) {
			$fcollapse = (string) $collapse;
		} //end if
		//--
		$total = [];
		//--
		$css_cls_a = 'simpletree-item-active';
		$css_cls_i = 'simpletree-item-inactive';
		//--
		$arr_controllers = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordsUniqueControllers();
		$arr_pages_data = array();
		for($i=0; $i<\Smart::array_size($arr_controllers); $i++) {
			$tmp_arr_lvl1 = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordsByCtrl((string)$arr_controllers[$i]);
			for($j=0; $j<\Smart::array_size($tmp_arr_lvl1); $j++) {
				if(\Smart::array_size($tmp_arr_lvl1[$j]) > 0) {
					$tmp_arr_lvl2 = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordsByRef((string)$tmp_arr_lvl1[$j]['id']);
					$tmp_arr_lvl1[$j]['hash-id'] = (string) sha1((string)$tmp_arr_lvl1[$j]['id']);
					$tmp_arr_lvl1[$j]['is-segment'] = (int) self::testIsSegmentPage((string)$tmp_arr_lvl1[$j]['id']);
					if(((string)$tmp_arr_lvl1[$j]['active'] == 1) OR ($tmp_arr_lvl1[$j]['is-segment'] == 1)) {
						$tmp_arr_lvl1[$j]['style-class'] = (string) $css_cls_a;
					} else {
						$tmp_arr_lvl1[$j]['style-class'] = (string) $css_cls_i;
					} //end if else
					$tmp_arr_lvl1[$j]['icon-type'] = (string) self::getImgForPageType((string)$tmp_arr_lvl1[$j]['id']);
					$tmp_arr_lvl1[$j]['img-type-html'] = (string) self::getImgForCodeType((string)$tmp_arr_lvl1[$j]['id'], (string)$tmp_arr_lvl1[$j]['mode']);
					$tmp_arr_lvl1[$j]['ref-childs'] = array();
					if(\Smart::array_size($tmp_arr_lvl2) > 0) {
						for($k=0; $k<\Smart::array_size($tmp_arr_lvl2); $k++) {
							if(\Smart::array_size($tmp_arr_lvl2[$k]) > 0) {
								$tmp_arr_lvl3 = (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::getRecordsByRef((string)$tmp_arr_lvl2[$k]['id']);
								$tmp_arr_lvl2[$k]['hash-id'] = (string) sha1((string)$tmp_arr_lvl2[$k]['id']);
								$tmp_arr_lvl2[$k]['is-segment'] = (int) self::testIsSegmentPage((string)$tmp_arr_lvl2[$k]['id']);
								if(((string)$tmp_arr_lvl2[$k]['active'] == 1) OR ($tmp_arr_lvl2[$k]['is-segment'] == 1)) {
									$tmp_arr_lvl2[$k]['style-class'] = (string) $css_cls_a;
								} else {
									$tmp_arr_lvl2[$k]['style-class'] = (string) $css_cls_i;
								} //end if else
								$tmp_arr_lvl2[$k]['icon-type'] = (string) self::getImgForPageType((string)$tmp_arr_lvl2[$k]['id']);
								$tmp_arr_lvl2[$k]['img-type-html'] = (string) self::getImgForCodeType((string)$tmp_arr_lvl2[$k]['id'], (string)$tmp_arr_lvl2[$k]['mode']);
								$tmp_arr_lvl2[$k]['ref-childs'] = array();
								if(\Smart::array_size($tmp_arr_lvl3) > 0) {
									for($z=0; $z<\Smart::array_size($tmp_arr_lvl3); $z++) {
										$tmp_arr_lvl3[$z]['hash-id'] = (string) sha1((string)$tmp_arr_lvl3[$z]['id']);
										$tmp_arr_lvl3[$z]['is-segment'] = (int) self::testIsSegmentPage((string)$tmp_arr_lvl3[$z]['id']);
										if(((string)$tmp_arr_lvl3[$z]['active'] == 1) OR ($tmp_arr_lvl3[$z]['is-segment'] == 1)) {
											$tmp_arr_lvl3[$z]['style-class'] = (string) $css_cls_a;
										} else {
											$tmp_arr_lvl3[$z]['style-class'] = (string) $css_cls_i;
										} //end if else
										$tmp_arr_lvl3[$z]['icon-type'] = (string) self::getImgForPageType((string)$tmp_arr_lvl3[$z]['id']);
										$tmp_arr_lvl3[$z]['img-type-html'] = (string) self::getImgForCodeType((string)$tmp_arr_lvl3[$z]['id'], (string)$tmp_arr_lvl3[$z]['mode']);
									} //end for
									$tmp_arr_lvl2[$k]['ref-childs'] = (array) $tmp_arr_lvl3;
									$total[(string)$tmp_arr_lvl3[$z]['id']] += 1;
								} //end if
								$tmp_arr_lvl3 = array();
								$total[(string)$tmp_arr_lvl2[$k]['id']] += 1;
							} //end if
						} //end for
						$tmp_arr_lvl1[$j]['ref-childs'] = (array) $tmp_arr_lvl2;
					} //end if
					$tmp_arr_lvl2 = array();
					$arr_pages_data[(string)$arr_controllers[$i]][] = (array) $tmp_arr_lvl1[$j];
					$total[(string)$tmp_arr_lvl1[$j]['id']] += 1;
				} //end if
			} //end for
			$tmp_arr_lvl1 = array();
		} //end if
		//print_r($total); die();
		//print_r($arr_pages_data); die();
		//--
		$the_link_list = (string) self::composeUrl('op=records-tree&tpl='.\Smart::escape_url($y_tpl));
		$the_alt_link_list = (string) self::composeUrl('tpl='.\Smart::escape_url($y_tpl).'#!'.'&srcby='.\Smart::escape_url($srcby).'&src='.\Smart::escape_url($src));
		//-- {{{SYNC-PAGEBUILDER-MANAGER-DEF-LINKS}}}
		$the_link_add = (string) self::composeUrl('op=record-add-form');
		$the_link_view = (string) self::composeUrl('op=record-view&id=');
		$the_link_delete = '';
		if(!defined('SMART_PAGEBUILDER_DISABLE_DELETE')) {
			$the_link_delete = (string) self::composeUrl('op=record-delete&id=');
		} //end if
		//--
		if(\Smart::array_size((array)\SmartTextTranslations::getListOfLanguages()) > 1) {
			$show_translations = 'yes';
		} else {
			$show_translations = 'no';
		} //end if else
		//-- #{{{SYNC-PAGEBUILDER-MANAGER-DEF-LINKS}}}
		return (string) \SmartMarkersTemplating::render_file_template(
			self::$ModulePath.'libs/views/manager/view-list-tree.mtpl.htm',
			[
				'SHOW-FILTER-TYPE' 	=> 'no',
				'SHOW-TRANSLATIONS' => (string) $show_translations,
				'LIST-FORM-URL' 	=> (string) self::$ModuleScript,
				'LIST-FORM-METHOD' 	=> 'GET',
				'LIST-FORM-VARS' 	=> (array) [
					[ 'name' => 'page', 'value' => (string) self::$ModulePageURLId ],
					[ 'name' => 'op',   'value' => 'records-tree' ],
					[ 'name' => 'tpl',  'value' => (string) $y_tpl ]
				],
				'LIST-VAL-SRC' 		=> (string) $src,
				'LIST-VAL-SRCBY' 	=> (string) $srcby,
				'LIST-BTN-RESET' 	=> (string) $the_link_list,
				'LIST-NEW-URL' 		=> (string) $the_link_add,
				'LIST-RECORD-URL' 	=> (string) $the_link_view,
				'LIST-DELETE-URL' 	=> (string) $the_link_delete,
				'LIST-ALT-COOKIE' 	=> (string) '',
				'LIST-CRR-LINK' 	=> (string) $the_link_list,
				'LIST-ALT-LINK' 	=> (string) $the_alt_link_list,
				'TXT-ALT-LINK' 		=> (string) self::text('ttl_ch_list', false),
				'TXT-RESET-COUNTER' => (string) self::text('ttl_reset_hits', false),
				'COLLAPSE' 			=> (string) $collapse,
				'FILTER-COLLAPSE' 	=> (string) $fcollapse,
				'FILTER' 			=> (array)  $filter,
				'DATA' 				=> (array)  $arr_pages_data,
				'PATH-MODULE' 		=> (string) self::$ModulePath,
				'LIST-TTL' 			=> (string) self::text('ttl_list', false),
				'LIST-RECORDS' 		=> (string) self::text('ttl_trecords', false),
				'TXT-RECORDS' 		=> (string) self::text('records', false),
				'TXT-SEARCH-BY' 	=> (string) self::text('search_by', false),
				'TXT-FILTER' 		=> (string) self::text('search', false),
				'TXT-RESET' 		=> (string) self::text('reset', false),
				'TXT-ADD-NEW' 		=> (string) self::text('ttl_add', false),
				'TXT-COL-ID' 		=> (string) self::text('id', false),
				'TXT-COL-REFID' 	=> (string) self::text('ref', false),
				'TXT-COL-NAME' 		=> (string) self::text('name', false),
				'TXT-COL-CTRL' 		=> (string) self::text('ctrl', false),
				'TXT-COL-CODE' 		=> (string) self::text('record_code', false),
				'TXT-COL-RUNTIME' 	=> (string) self::text('record_runtime', false),
				'TXT-COL-SYNTAX' 	=> (string) self::text('record_syntax', false),
				'TXT-COL-SPECIAL' 	=> (string) self::text('special', false),
				'TXT-COL-ACTIVE' 	=> (string) self::text('active', false),
				'TXT-COL-AUTH' 		=> (string) self::text('auth', false),
				'TXT-COL-TRANSL' 	=> (string) self::text('translations', false),
				'TXT-COL-COUNTER' 	=> (string) self::text('counter', false),
				'HINT-0' 			=> (string) self::text('hint_0', false),
				'HINT-1' 			=> (string) self::text('hint_1', false),
				'HINT-2' 			=> (string) self::text('hint_2', false),
				'HINT-3' 			=> (string) self::text('hint_3', false),
				'FMT-LIST' 			=> (string) \Smart::array_size($filter).' / '.\Smart::array_size($total),
				'DB-TYPE' 			=> (string) \SmartModExtLib\PageBuilder\Utils::getDbType()
			]
		);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayListTable($y_tpl) {
		//--
		$the_link_list = (string) self::composeUrl('op=records-list-json&');
		$the_back_link_list = (string) self::composeUrl('op=records-list&tpl='.\Smart::escape_url($y_tpl)); // \SmartFrameworkRegistry::getCookieVar('PageBuilder_Smart_Slickgrid_List_URL')
		$the_alt_link_list = (string) self::composeUrl('op=records-tree&tpl='.\Smart::escape_url($y_tpl)); // \SmartFrameworkRegistry::getCookieVar('PageBuilder_Smart_Slickgrid_List_URL')
		//-- {{{SYNC-PAGEBUILDER-MANAGER-DEF-LINKS}}}
		$the_link_add = (string) self::composeUrl('op=record-add-form');
		$the_link_view = (string) self::composeUrl('op=record-view&id=');
		$the_link_delete = '';
		if(!defined('SMART_PAGEBUILDER_DISABLE_DELETE')) {
			$the_link_delete = (string) self::composeUrl('op=record-delete&id=');
		} //end if
		//--
		if(\Smart::array_size((array)\SmartTextTranslations::getListOfLanguages()) > 1) {
			$show_translations = 'yes';
		} else {
			$show_translations = 'no';
		} //end if else
		//-- #{{{SYNC-PAGEBUILDER-MANAGER-DEF-LINKS}}}
		return (string) \SmartMarkersTemplating::render_file_template(
			(string) self::$ModulePath.'libs/views/manager/view-list.mtpl.htm',
			[
				'SHOW-FILTER-TYPE' 	=> 'yes',
				'SHOW-TRANSLATIONS' => (string) $show_translations,
				'LIST-FORM-URL' 	=> '#',
				'LIST-FORM-METHOD' 	=> 'POST',
				'LIST-FORM-VARS' 	=> (array) [],
				'LIST-JSON-URL' 	=> (string) $the_link_list,
				'LIST-NEW-URL' 		=> (string) $the_link_add,
				'LIST-RECORD-URL' 	=> (string) $the_link_view,
				'LIST-DELETE-URL' 	=> (string) $the_link_delete,
				'LIST-ALT-COOKIE' 	=> (string) 'PageBuilder_Smart_Slickgrid_List_URL',
				'LIST-CRR-LINK' 	=> (string) $the_back_link_list,
				'LIST-ALT-LINK' 	=> (string) $the_alt_link_list,
				'TXT-ALT-LINK' 		=> (string) self::text('ttl_ch_list', false),
				'TXT-RESET-COUNTER' => (string) self::text('ttl_reset_hits', false),
				'PATH-MODULE' 		=> (string) self::$ModulePath,
				'LIST-TTL' 			=> (string) self::text('ttl_list', false),
				'LIST-RECORDS' 		=> (string) self::text('ttl_records', false),
				'TXT-RECORDS' 		=> (string) self::text('records', false),
				'TXT-SEARCH-BY' 	=> (string) self::text('search_by', false),
				'TXT-FILTER' 		=> (string) self::text('search', false),
				'TXT-RESET' 		=> (string) self::text('reset', false),
				'TXT-ADD-NEW' 		=> (string) self::text('ttl_add', false),
				'TXT-COL-ID' 		=> (string) self::text('id', false),
				'TXT-COL-REFID' 	=> (string) self::text('ref', false),
				'TXT-COL-NAME' 		=> (string) self::text('name', false),
				'TXT-COL-CTRL' 		=> (string) self::text('ctrl', false),
				'TXT-COL-CODE' 		=> (string) self::text('record_code', false),
				'TXT-COL-RUNTIME' 	=> (string) self::text('record_runtime', false),
				'TXT-COL-SYNTAX' 	=> (string) self::text('record_syntax', false),
				'TXT-COL-SPECIAL' 	=> (string) self::text('special', false),
				'TXT-COL-ACTIVE' 	=> (string) self::text('active', false),
				'TXT-COL-AUTH' 		=> (string) self::text('auth', false),
				'TXT-COL-TRANSL' 	=> (string) self::text('translations', false),
				'TXT-COL-COUNTER' 	=> (string) self::text('counter', false),
				'HINT-0' 			=> (string) self::text('hint_0', false),
				'HINT-1' 			=> (string) self::text('hint_1', false),
				'HINT-2' 			=> (string) self::text('hint_2', false),
				'HINT-3' 			=> (string) self::text('hint_3', false),
				'FMT-LIST' 			=> '# / # @',
				'DB-TYPE' 			=> (string) \SmartModExtLib\PageBuilder\Utils::getDbType()
			]
		);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayListJson($ofs, $sortby, $sortdir, $srcby, $src) {
		//--
		$ofs = (int) \Smart::format_number_int($ofs, '+');
		//--
		$sortdir = (string) strtoupper((string)$sortdir);
		if((string)$sortdir != 'ASC') {
			$sortdir = 'DESC';
		} //end if
		//--
		$limit = 25;
		//--
		$src = (string) trim((string)$src);
		if((string)trim((string)$src) == '') {
			$srcby = '';
		} elseif((string)trim((string)$srcby) == '') {
			$src = '';
		} //end if
		//--
		$data = [
			'status'  			=> 'OK',
			'crrOffset' 		=> (int)    $ofs,
			'itemsPerPage' 		=> (int)    $limit,
			'sortBy' 			=> (string) $sortby,
			'sortDir' 			=> (string) $sortdir,
			'sortType' 			=> (string) '', // applies only with clientSort (not used for Server-Side sort)
			'filter' 			=> [
				'srcby' => (string) $srcby,
				'src' => (string) $src
			]
		];
		//--
		$data['totalRows'] 	= (int) \SmartModDataModel\PageBuilder\PageBuilderBackend::listCountRecords((string)$srcby, (string)$src);
		$data['rowsList'] 	= (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::listGetRecords((string)$srcby, (string)$src, (int)$limit, (int)$ofs, (string)$sortdir, (string)$sortby);
		//--
		return (string) \Smart::json_encode((array)$data);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayResetCounter($y_redir_url='') {
		//--
		$wr = \SmartModDataModel\PageBuilder\PageBuilderBackend::resetCounterOnAllRecords();
		if($wr[1] >= 0) { // there can be no records, thus can be also zero
			$status = 'OK';
			$message = 'Hit Counter was reset on all records';
		} else {
			$status = 'ERROR';
			$message = 'There was an error trying to reset the Hit Counter on all records';
		} //end if else
		//--
		return (string) \SmartComponents:: js_ajax_replyto_html_form(
			(string) $status,
			'Reset Hit Counter on All Records',
			(string) $message,
			(string) $y_redir_url
		);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayExportData($y_tpl) {
		//--
		$y_tpl = (string) $y_tpl;
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			(string) self::$ModulePath.'libs/views/manager/view-export.mtpl.htm',
			[
				'URL-FORM-ACTION' 	=> (string) self::composeUrl('op=export-translations-ods'),
				'LANGUAGE-DEFAULT' 	=> (string) \SmartTextTranslations::getDefaultLanguage(),
				'LANGUAGES-ARR' 	=> (array)  \SmartTextTranslations::getListOfLanguages()
			]
		);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayExportOdsData($mode, $lang) {
		//--
		if(((string)$mode != 'all') AND ((string)$mode != 'missing')) {
			return array();
		} //end if else
		if(\SmartTextTranslations::validateLanguage($lang) !== true) {
			return array();
		} //end if
		//--
		return (array) \SmartModDataModel\PageBuilder\PageBuilderBackend::exportTranslationsByLang((string)$lang, (string)$mode);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayImportData($y_tpl, $y_appname='PageBuilder', $y_action='') {
		//--
		$y_tpl = (string) $y_tpl;
		$y_appname = (string) $y_appname;
		$y_action = (string) $y_action;
		//--
		if((string)$y_action == '') {
			$y_action = (string) self::composeUrl('op=import-translations-do');
		} //end if
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			(string) self::$ModulePath.'libs/views/manager/view-import-form.mtpl.htm',
			[
				'TPL-VAR' 			=> (string) $y_tpl,
				'APP-NAME' 			=> (string) $y_appname,
				'URL-FORM-ACTION' 	=> (string) $y_action
			]
		);
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	public static function ViewDisplayImportDoData($y_tpl, $y_appname='PageBuilder', $y_modelclass='') {
		//--
		$y_tpl = (string) $y_tpl;
		$y_appname = (string) $y_appname;
		$y_modelclass = (string) $y_modelclass;
		//--
		if(!$_FILES['import_file']['tmp_name']) {
			return (string) \SmartComponents::operation_notice('No File to Import (.fods)');
		} //end if
		if(substr((string)$_FILES['import_file']['name'], -5, 5) != '.fods') {
			return (string) \SmartComponents::operation_warn('Invalid File to Import (.fods)');
		} //end if
		//--
		$input_str = (string) \SmartFileSystem::read_uploaded((string)$_FILES['import_file']['tmp_name']);
		//--
		$input_str = (array) \SmartModExtLib\PageBuilder\Utils::parseFodsXmlSpreadSheetToArray($input_str);
		if(\Smart::array_size($input_str) <= 0) {
			return \SmartComponents::operation_error('Invalid FODS/Xml File Format to Import');
		} //end if
		$hdr_arr = (array) $input_str['header'];
		$data_arr = (array) $input_str['data'];
		//print_r($data_arr); die();
		$input_str = null; // free mem
		//--
		$def_lang = (string) \SmartTextTranslations::getDefaultLanguage();
		//--
		$out_total = 0;
		$real_imported = 0;
		$arr_xdata = [];
		foreach($data_arr as $lang => $val) {
			//--
			$x_iterator = 0;
			//--
			if(((string)$lang != (string)$def_lang) AND (\SmartTextTranslations::validateLanguage((string)$lang))) {
				//--
				if(is_array($val)) {
					//--
					for($i=0; $i<\Smart::array_size($val); $i++) {
						//--
						$x_is_all_empty = false;
						$x_is_empty = true;
						$x_is_tempty = true;
						$x_is_diff = true;
						$x_is_not_imported = true;
						$diffs_arr_rows = [];
						//--
						if((string)trim((string)$data_arr[(string)$def_lang][$i]) != '') {
							//--
							$x_is_empty = false;
							//--
							if((string)trim((string)$val[$i]) != '') {
								//--
								$x_is_tempty = false;
								//--
								$arr_placeholder_and_marker_diffs = (array) \SmartModExtLib\PageBuilder\Utils::comparePlaceholdersAndMarkers((string)$data_arr[(string)$def_lang][$i], (string)$val[$i]);
								//--
								if(\Smart::array_size($arr_placeholder_and_marker_diffs) <= 0) {
									//--
									$x_is_diff = false;
									//--
									if((string)$y_modelclass != '') {
										$upd = (int) $y_modelclass::updateTranslationByText((string)$data_arr[(string)$def_lang][$i], (string)$lang, (string)$val[$i], (string)\SmartAuth::get_login_id());
									} else {
										$upd = (int) \SmartModDataModel\PageBuilder\PageBuilderBackend::updateTranslationByText((string)$data_arr[(string)$def_lang][$i], (string)$lang, (string)$val[$i], (string)\SmartAuth::get_login_id());
									} //end if else
									//--
									if($upd > 0) {
										$real_imported++;
										$x_is_not_imported = false;
									} //end if
									if((string)$dbg == 'yes') {
										if($upd < -1) {
											\SmartFrameworkRegistry::setDebugMsg('extra', 'IMPORT-TRANSLATIONS', [
												'title' => '[Import Translations: '.$y_appname.']',
												'data' => 'ERROR('.$upd.'): Could not Find for Update PageBuilder Translations for text: `'.(string)$data_arr[(string)$def_lang][$i].'`'
											]);
										} elseif($upd == -1) {
											// no translation
										} elseif($upd == 0) {
											\SmartFrameworkRegistry::setDebugMsg('extra', 'IMPORT-TRANSLATIONS', [
												'title' => '[Import Translations: '.$y_appname.']',
												'data' => 'WARN: Could not Update PageBuilder Translations for text: `'.(string)$data_arr[(string)$def_lang][$i].'`'
											]);
										} //end if else
									} //end if
									//--
								} else {
									//--
									$diffs_arr_rows = (array) $arr_placeholder_and_marker_diffs;
									//--
								} //end if else
								//--
								$arr_placeholder_and_marker_diffs = array();
								//--
							} //end if
							//--
						} elseif((string)trim((string)$val[$i]) == '') { // skip if both empty
							//--
							$x_is_all_empty = true;
							//--
						} //end if else
						//--
						if($x_is_all_empty === false) {
							//--
							if(!is_array($arr_xdata[(int)$x_iterator])) {
								$arr_xdata[(int)$x_iterator] = [];
							} //end if
							$status = 'ok';
							if($x_is_empty || $x_is_tempty) {
								$x_is_diff = false; // FIX
							} //end if
							if($x_is_empty || $x_is_tempty || $x_is_diff || $x_is_not_imported) {
								$status = 'warn';
								if(!$x_is_tempty) {
									$status = 'warn-crit';
								} //end if
							} //end if
							$arr_xdata[(int)$x_iterator]['is_transl_empty'] = (string) ($x_is_tempty ? 'yes' : 'no');
							$arr_xdata[(int)$x_iterator]['is_base_empty'] = (string) ($x_is_empty ? 'yes' : 'no');
							$arr_xdata[(int)$x_iterator]['is_base_diff_transl'] = (string) ($x_is_diff ? 'yes' : 'no');
							$arr_xdata[(int)$x_iterator]['is_imported'] = (string) (!$x_is_not_imported ? 'yes' : 'no');
							$arr_xdata[(int)$x_iterator]['status'] = (string) $status;
							$arr_xdata[(int)$x_iterator]['diffs'] = (string) implode(', ', (array)$diffs_arr_rows);
							$arr_xdata[(int)$x_iterator]['translate'] = (string) $val[$i];
							$x_iterator++;
							//--
						} //end if
						//--
					} //end for
					//--
				} //end if
				//--
			} elseif((string)$lang == (string)$def_lang) {
				//--
				if(is_array($val)) {
					//--
					for($i=0; $i<\Smart::array_size($val); $i++) {
						//--
						if(((string)trim((string)$data_arr[(string)$def_lang][$i]) != '') AND ((string)trim((string)$val[$i]) != '')) { // skip all empty records
							//--
							if(!is_array($arr_xdata[(int)$x_iterator])) {
								$arr_xdata[(int)$x_iterator] = [];
							} //end if
							//--
							$arr_xdata[(int)$x_iterator]['default'] = (string) $val[$i];
							$x_iterator++;
							//--
							$out_total++;
							//--
						} //end if
						//--
					} //end for
					//--
				} //end if
				//--
			} //end if
			//--
		} //end foreach
		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			(string) self::$ModulePath.'libs/views/manager/view-import-result.mtpl.htm',
			[
				'TPL-VAR' 			=> (string) $y_tpl,
				'APP-NAME' 			=> (string) $y_appname,
				'TOTAL-RECORDS' 	=> (int)    $out_total,
				'TOTAL-IMPORTED' 	=> (int)    $real_imported,
				'TOTAL-ERRORS' 		=> (int)    ($out_total - $real_imported),
				'HEAD-ARR' 			=> (array)  $hdr_arr,
				'DATA-ARR' 			=> (array)  $arr_xdata
			]
		);
		//--
	} //END FUNCTION
	//==================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>