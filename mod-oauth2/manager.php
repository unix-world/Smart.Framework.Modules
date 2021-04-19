<?php
// Controller: OAuth2 Manager
// Route: ?/page/oauth2.manager (?page=oauth2.manager)
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // INDEX, ADMIN, SHARED
define('SMART_APP_MODULE_AUTH', true);

/**
 * Admin Controller
 *
 * @ignore
 * @version v.20210411
 *
 */
class SmartAppAdminController extends SmartAbstractAppController {

	public function Run() {

		//--
		if(SmartAuth::check_login() !== true) {
			$this->PageViewSetCfg('error', 'Oauth2 Manager Requires Authentication ! ...');
			return 403;
		} //end if
		//--
		if(SmartAuth::test_login_privilege('admin') !== true) { // PRIVILEGES
			$this->PageViewSetCfg('error', 'Oauth2 Manager requires the following privileges: ADMIN');
			return 403;
		} //end if
		//--

		//--
		$action = $this->RequestVarGet('action', '', 'string');
		//--

		switch((string)$action) {


			case 'close-modal': // Closes the Modal and Refresh the Parent (OUTPUTS: HTML)
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				//--
				$this->PageViewSetVars([
					'title' => 'Wait ...',
					'main' => '<br><div><center><img src="lib/framework/img/loading-bars.svg" width="64" height="64"></center></div>'.
					'<script type="text/javascript">smartJ$Browser.RefreshParent();</script>'.
					'<script type="text/javascript">smartJ$Browser.CloseDelayedModalPopUp();</script>'
				]);
				//--
				break;

			case 'new-form': // Form for Add new Record (OUTPUTS: HTML)
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				//--
				$this->PageViewSetVars([
					'title' => 'Oauth2 Manager - Create New Auth API',
					'main' => SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'form-add-record.mtpl.htm',
						[
							'REGEX-VALID-ID' 		=> (string) \SmartModExtLib\Oauth2\Oauth2Api::OAUTH2_REGEX_VALID_ID,
							'DEFAULT-REDIRECT-URL' 	=> (string) \SmartModExtLib\Oauth2\Oauth2Api::OAUTH2_STANDALONE_REFRESH_URL, // {{{SYNC-OAUTH2-DEFAULT-REDIRECT-URL}}}
							'ACTIONS-URL' 			=> 'admin.php?page='.$this->ControllerGetParam('controller').'&action=new-add',
							'TPL-AUTH-URL-PARAMS' 	=> (string) SmartMarkersTemplating::escape_template((string)\SmartModExtLib\Oauth2\Oauth2Api::OAUTH2_AUTHORIZE_URL_PARAMS)
						]
					)
				]);
				//--
				break;

			case 'new-add': // Do Add new Record (OUTPUTS: JSON)
				//--
				$this->PageViewSetCfg('rawpage', true);
				//--
				$data = $this->RequestVarGet('frm', [], 'array');
				//--
				$message = ''; // {{{SYNC-MOD-AUTH-VALIDATIONS}}}
				$status = 'INVALID';
				$redirect = '';
				$jsevcode = '';
				//--
				$test = \SmartModExtLib\Oauth2\Oauth2Api::initApiData($data, 15); // mixed
				if(is_array($test)) {
					$status = 'OK';
					$message = 'OAUTH2 Initialization Done';
					$redirect = 'admin.php?page='.$this->ControllerGetParam('controller').'&action=close-modal';
				} else {
					$status = 'ERROR';
					$message = 'ERR.Message: '.Smart::escape_html($test);
				} //end if else
				//--
				$this->PageViewSetVar(
					'main',
					SmartViewHtmlHelpers::js_ajax_replyto_html_form(
						$status,
						'Initialize Oauth2 API Tokens',
						$message,
						$redirect,
						'',
						'',
						$jsevcode
					)
				);
				//--
				break;

			case 'edit-cell':
				//--
				$this->PageViewSetCfg('rawpage', true);
				//--
				$column = $this->RequestVarGet('column', '', 'string');
				$value = $this->RequestVarGet('value', '', 'string');
				$id = $this->RequestVarGet('id', '', 'string');
				//--
				$title = 'Update Column ['.$column.'] for ID: '.$id; //.' @ '.$value;
				$status = 'ERROR';
				$message = '???';
				//--
				switch((string)$column) {
					case 'active':
						//--
						$upd = (int) \SmartModExtLib\Oauth2\Oauth2Api::updateApiStatus((string)$id, (string)$value);
						//--
						if((int)$upd == 1) {
							$status = 'OK';
							$message = 'Status ['.ucfirst((string)$column).'] updated';
						} else {
							$message = 'FAILED to update Status ['.ucfirst((string)$column).']';
						} //end if else
						//--
						break;
					default:
						$message = 'Data column is not editable: '.$column;
				} //end switch
				//--
				$this->PageViewSetVar(
					'main',
					SmartViewHtmlHelpers::js_ajax_replyto_html_form(
						(string) $status,
						(string) $title,
						(string) Smart::escape_html((string)$message)
					)
				);
				//--
				break;

			case 'view-data': // Form for Display Record (OUTPUTS: HTML)
				//--
				$id = $this->RequestVarGet('id', '', 'string');
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				//--
				$title = 'Oauth2 Manager - Display Record';
				//--
				$data = (array) \SmartModExtLib\Oauth2\Oauth2Api::getApiData((string)$id);
				//--
				$this->PageViewSetVars([
					'title' => (string) $title,
					'main' => (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'form-view-record.mtpl.htm',
						[
							'THE-TITLE' 			=> (string) $title,
							'DATE-NOW' 				=> (string) \date('Y-m-d H:i:s O'),
							'ACTION-GET-TOKEN' 		=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&action=get-the-access-token&id='.Smart::escape_url((string)$id),
							'ACTION-REFRESH-TOKEN' 	=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&action=refresh-token&id='.Smart::escape_url((string)$id),
							'ACTION-DELETE-TOKEN' 	=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&action=delete-token&id='.Smart::escape_url((string)$id),
							'IS-EXPIRING' 			=> (string) ($data['refresh_token'] ? 'yes' : 'no'),
							'DATA-ARR' 				=> (array)  $data
						]
					)
				]);
				//--
				break;

			case 'refresh-token': // Refresh the Token for an API (OUTPUTS: HTML)
				//--
				$id = $this->RequestVarGet('id', '', 'string');
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				//--
				$title = 'Refreshing the Access Token for Oauth2 API';
				//--
				$upd = (array) \SmartModExtLib\Oauth2\Oauth2Api::updateApiAccessToken((string)$id, 15);
				if(Smart::array_size($upd) > 0) {
					$result = 'OK';
					$img = 'lib/framework/img/sign-ok.svg';
				} else {
					$result = 'FAILED';
					$img = 'lib/framework/img/sign-warn.svg';
				} //end if else
				//--
				$this->PageViewSetVars([
					'title' => (string) $title,
					'main' => '<h1 style="color:#003366;!important">'.Smart::escape_html($title).'</h1><h2>'.Smart::escape_html($id).'</h2><div style="font-size:2rem;">[ '.Smart::escape_html($result).' ]<br><img src="'.Smart::escape_html($img).'"></div><div><center><img src="lib/framework/img/loading-spin.svg" width="64" height="64"></center></div>'.
					'<script type="text/javascript">smartJ$Browser.RefreshParent();</script>'.
					'<script type="text/javascript">setTimeout(function(){ self.location=\''.Smart::escape_js('admin.php?page='.$this->ControllerGetParam('controller').'&action=view-data&id='.Smart::escape_url((string)$id)).'\'; }, 3000);</script>'
				]);
				//--
				break;

			case 'get-the-access-token': // Get the Access Token and If Expired will Update for an API (OUTPUTS: HTML)
				//--
				$id = $this->RequestVarGet('id', '', 'string');
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				//--
				$title = 'Getting the Access Token for Oauth2 API';
				//--
				$token = \SmartModExtLib\Oauth2\Oauth2Api::getApiAccessToken((string)$id, 15);
				if($token !== null) {
					$result = 'OK';
					$img = 'lib/framework/img/sign-ok.svg';
				} else {
					$result = 'FAILED';
					$img = 'lib/framework/img/sign-warn.svg';
				} //end if else
				//--
				$this->PageViewSetVars([
					'title' => (string) $title,
					'main' => '<h1 style="color:#003366;!important">'.Smart::escape_html($title).'</h1><h2>'.Smart::escape_html($id).'</h2><div style="font-size:2rem;">[ '.Smart::escape_html($result).' ]<br><img src="'.Smart::escape_html($img).'"></div><div><center><h1>'.Smart::escape_html($token).'</h1></center></div><br>'.
					'<button class="ux-button" onClick="self.location=\''.Smart::escape_js('admin.php?page='.$this->ControllerGetParam('controller').'&action=view-data&id='.Smart::escape_url((string)$id)).'\';"><i class="sfi sfi-undo2"></i> &nbsp; Go Back</button>'.
					'<script type="text/javascript">smartJ$Browser.RefreshParent();</script>'
				]);
				//--
				break;

			case 'delete-token': // Delete the Token for an API (OUTPUTS: HTML)
				//--
				$id = $this->RequestVarGet('id', '', 'string');
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				//--
				$title = 'Deleting the Access Token for Oauth2 API';
				//--
				$del = (int) \SmartModExtLib\Oauth2\Oauth2Api::deleteApiAccessToken((string)$id);
				if((int)$del == 1) {
					$result = 'OK';
				} else {
					$result = 'FAILED';
				} //end if else
				//--
				$this->PageViewSetVars([
					'title' => (string) $title,
					'main' => '<h1 style="color:#FF3300;!important">'.Smart::escape_html($title).'</h1><h2>'.Smart::escape_html($id).'</h2><h3>[ '.Smart::escape_html($result).' ]</h3><br><div><center><img src="lib/framework/img/loading-spin.svg" width="64" height="64"></center></div>'.
					'<script type="text/javascript">smartJ$Browser.RefreshParent();</script>'.
					'<script type="text/javascript">smartJ$Browser.CloseDelayedModalPopUp();</script>'
				]);
				//--
				break;

			case 'list': // list data (RETURNS: JSON)
				//--
				$this->PageViewSetCfg('rawpage', true);
				//-- list vars
				$ofs = $this->RequestVarGet('ofs', 0, 'integer+');
				$sortby = $this->RequestVarGet('sortby', 'id', 'string');
				$sortdir = $this->RequestVarGet('sortdir', 'ASC', 'string');
				$sorttype = $this->RequestVarGet('sorttype', 'string', 'string');
				//-- filter vars
				$id = $this->RequestVarGet('id', '', 'string');
				//-- output var(s)
				$data['status'] = 'OK';
				$data['crrOffset'] = (int) $ofs;
				$data['itemsPerPage'] = 25;
				$data['sortBy'] = (string) $sortby;
				$data['sortDir'] = (string) $sortdir;
				$data['sortType'] = (string) $sorttype;
				$data['filter'] = array(
					'id' => (string) $id
				);
				$model = new \SmartModDataModel\Oauth2\SqOauth2(); // open connection
				$data['totalRows'] = $model->countByFilter($id);
				$data['rowsList'] = $model->getListByFilter([], $data['itemsPerPage'], $ofs, $sortby, $sortdir, $id);
				unset($model); // close connection
				//--
				$this->PageViewSetVar(
					'main', Smart::json_encode((array)$data)
				);
				//--
				break;

			default: // display the grid (OUTPUTS: HTML)
				//--
				$this->PageViewSetVars([
					'title' => 'OAUTH2 Manager',
					'main' => (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'list-records.mtpl.htm',
						[
							'ACTIONS-URL' => 'admin.php?page='.$this->ControllerGetParam('controller').'&action=',
							'RELEASE-HASH' => (string) $this->ControllerGetParam('release-hash')
						]
					)
				]);
				//--

		} // end switch

	} //END FUNCTION

} //END CLASS


// end of php code
