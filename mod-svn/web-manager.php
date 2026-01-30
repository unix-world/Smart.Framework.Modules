<?php
// Controller: Svn/WebManager
// Route: admin.php?page=svn.web-manager
// (c) 2008-present unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // ADMIN only
define('SMART_APP_MODULE_AUTH', true); // requires auth always

class SmartAppAdminController extends SmartAbstractAppController {

	// v.20260128


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

		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-webdav')) { // req. for icons
			$this->PageViewSetErrorStatus(500, 'ERROR: SVN Manager requires Mod Webdav ...');
			return;
		} //end if
		//--
		if(!SmartAppInfo::TestIfModuleExists('mod-highlight-syntax')) { // req. for extra highlightjs syntax
			$this->PageViewSetErrorStatus(500, 'ERROR: SVN Manager requires Mod HighlightSyntax ...');
			return;
		} //end if
		//--

		//--
		if(
			(SmartAuth::test_login_privilege('svn') !== true)
		) {
			$this->PageViewSetCfg('error', 'This Area is Restricted by your Account Privileges !');
			return 403;
		} //end if
		//--

		//--
		if(Smart::array_size($this->ConfigParamGet('svn')) <= 0) {
			$this->PageViewSetErrorStatus(500, 'ERROR: No SVN Area Config available ...');
			return;
		} //end if
		//--

		//--
		$op = $this->RequestVarGet('op', 'info', 'string');
		//--

		//--
		switch((string)$op) {

			case 'compare': // show compare between revisions (mostly for paths but if file is passed will show for the dir of file ...)

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$rev = (string)  $this->RequestVarGet('rev', 'HEAD', 'string');

				$comparr = (array) \SmartModExtLib\Svn\SvnWebManager::getCompare($repo, $path, $rev);
				//print_r($comparr); die();
				$this->PageViewSetCfg('template-file', 'template-modal.htm'); // the default modal template
				$title = 'SVN - Web Manager :: Repo: '.$repo.' @ Compare: '.$path;
				$main = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'web-manager-list-compare.inc.htm',
					[
						'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
						'REPO-NAME' 		=> (string) $repo,
						'REPO-PATH' 		=> (string) ($path ? $path : '/'),
						'REVISION' 			=> (string) $comparr['metainfo']['rev'],
						'AUTHOR' 			=> (string) $comparr['metainfo']['author'],
						'DATE' 				=> (string) $comparr['metainfo']['date'],
						'MESSAGE' 			=> (string) $comparr['metainfo']['msg'],
						'COMPARR' 			=> (array)  $comparr['changes'],
						'URL-FILE-VIEW' 	=> 'admin.php?page='.$this->ControllerGetParam('controller').'&op=view&repo='.Smart::escape_url($repo).'&type=file&path=',
						'URL-FILE-DIFF' 	=> 'admin.php?page='.$this->ControllerGetParam('controller').'&op=diff&repo='.Smart::escape_url($repo).'&type=file&path=',
					]
				);

				break;

			case 'props': // proplist + propget

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$type = (string) $this->RequestVarGet('type', '', 'string');
				$rev = (string)  $this->RequestVarGet('rev', 'HEAD', 'string');

				if(((string)$path == '') OR ((string)$path == '/')) {
					$path = '/';
				} //end if

				$this->PageViewSetCfg('template-file', 'template-modal.htm'); // the default modal template
				$title = 'SVN - Web Manager :: Repo: '.$repo.' @ Path: '.$path;

				if((string)$type == 'file') {
					$icon  = (string) \SmartModExtLib\Webdav\DavUtils::getFileIcon((string)$path);
					$xicon = (string) \SmartModExtLib\Webdav\DavUtils::getFileTypeIcon((string)$path);
				} else {
					$icon  = (string) \SmartModExtLib\Webdav\DavUtils::getFolderIcon((string)$path);
					$xicon = 'folder';
				} //end if else

				$main = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'web-manager-view-props.inc.htm',
					[
						'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
						'REPO-NAME' 		=> (string) $repo,
						'REPO-PATH' 		=> (string) $path,
						'THE-ICON' 			=> (string) $icon,
						'THE-XICON' 		=> (string) $xicon,
						'REVISION' 			=> (string) $rev,
						'PROPS-ARR' 		=> (array)  \SmartModExtLib\Svn\SvnWebManager::getProps($repo, $path, $rev)
					]
				);

				break;

//			case 'blame': // text files only ; TODO
//				break;

			case 'diff': // text files only

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$type = (string) $this->RequestVarGet('type', '', 'string');
				$rev = (string)  $this->RequestVarGet('rev', 'HEAD', 'string');

				if(((string)$path == '') OR ((string)$path == '/')) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Empty or Invalid File Selected for Diff ...');
					return;
				} //end if

				if((string)$type != 'file') {
					$this->PageViewSetErrorStatus(400, 'ERROR: Empty Type Selected for Diff ...');
					return;
				} //end if

				$revs = (array) \SmartModExtLib\Svn\SvnWebManager::listRevs($repo, $path, $rev, 2);
				if(Smart::array_size($revs) < 1) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid Revisions Selected for Diff ...');
					return;
				} //end if
				//print_r($revs); die();
				$rev_new = (int) ((isset($revs[0]) && is_array($revs[0]) && isset($revs[0]['revision'])) ? $revs[0]['revision'] : 0);
				$rev_old = (int) ((isset($revs[1]) && is_array($revs[1]) && isset($revs[1]['revision'])) ? $revs[1]['revision'] : 0); // this can be zero if first selected

				$difftxt = (string) \SmartModExtLib\Svn\SvnWebManager::getDiffFile($repo, $path, $rev_old, $rev_new);
				$bsize = (int) strlen((string)$difftxt);
				$fsize = (string) \SmartUtils::pretty_print_bytes((int)$bsize, 1, ' '); // {{{SYNC-SVN-PRETTY-PRINT-BYTES}}}
				if((int)$bsize <= (int)\SmartModExtLib\Svn\SvnWebManager::MAX_FILESIZE_DISPLAY) {
					$difftxt = (string) SmartMarkersTemplating::prepare_nosyntax_html_template((string)Smart::escape_html((string)$difftxt));
				} else {
					$difftxt = '';
				} //end if else

				$this->PageViewSetCfg('template-file', 'template-modal.htm'); // the default modal template
				$title = 'SVN - Web Manager :: Repo: '.$repo.' @ File: '.$path;
				$main = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'web-manager-view-file.inc.htm',
					[
						'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
						'REPO-ACTION' 		=> (string) $op,
						'REPO-NAME' 		=> (string) $repo,
						'REPO-PATH' 		=> (string) $path,
						'TYPE' 				=> (string) $type,
						'MIMETYPE' 			=> (string) 'text/plain', // diff is text plain as in SmartFileSysUtils Mime Eval
						'FILE-SIZE-BYTES' 	=> (int)    $bsize,
						'FILE-SIZE' 		=> (string) $fsize,
						'ICON-SUFFIX' 		=> (string) \SmartModExtLib\Webdav\DavUtils::getFileTypeSuffixIcon((string)$path),
						'REVISION' 			=> (string) $rev,
						'CODE-HIGHLIGHT' 	=> (string) \SmartModExtLib\HighlightSyntax\SmartViewHtmlHelpers::htmlJsLoadHilightCodeSyntax('body', 'dracula'),
						'CODE-TYPE' 		=> 'diff',
						'CODE-HTML' 		=> (string) (((int)$bsize <= (int)\SmartModExtLib\Svn\SvnWebManager::MAX_FILESIZE_DISPLAY) ? $difftxt : ''),
						'CODE-EXT-HTML' 	=> (string) (((int)$bsize <= (int)\SmartModExtLib\Svn\SvnWebManager::MAX_FILESIZE_DISPLAY) ? '' : '<br><hr><center><h3 style="color:#666699;">File is too large to display diff here ...  File Size: '.(int)$bsize.' bytes ...</h3></center>'),
					]
				);

				break;


			case 'view': // text files only

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$type = (string) $this->RequestVarGet('type', '', 'string');
				$rev = (string)  $this->RequestVarGet('rev', 'HEAD', 'string');

				if(((string)$path == '') OR ((string)$path == '/')) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Empty or Invalid File Selected for View ...');
					return;
				} //end if

				if((string)$type != 'file') {
					$this->PageViewSetErrorStatus(400, 'ERROR: Empty Type Selected for View ...');
					return;
				} //end if
				$bsize = -1; // must be file
				$fsize = '';
				$farr = (array) \SmartModExtLib\Svn\SvnWebManager::listRepo($repo, $path, $rev); // must be file
				if(((int)Smart::array_size($farr) == 1) AND isset($farr[0]) AND (Smart::array_size($farr[0]) > 0)) {
					if(array_key_exists('size-bytes', $farr[0])) {
						$bsize = (int) $farr[0]['size-bytes'];
						$fsize = (string) $farr[0]['size'];
					} //end if
				} //end if

				$fname = (string) Smart::base_name((string)$path);
				$fmime = (array) SmartFileSysUtils::getArrMimeType((string)$fname);

				$this->PageViewSetCfg('template-file', 'template-modal.htm'); // the default modal template
				$title = 'SVN - Web Manager :: Repo: '.$repo.' @ File: '.$path;

				if(\SmartModExtLib\Svn\SvnWebManager::isTextFileByMimeType((string)$fmime[0]) === true) {
					$fhtml = '';
					if((int)$bsize <= (int)\SmartModExtLib\Svn\SvnWebManager::MAX_FILESIZE_DISPLAY) {
						if(\SmartModExtLib\Svn\SvnWebManager::isSvgImageFileByMimeType((string)$fmime[0]) === true) {
							$fhtml = '<br><hr><br><center><img style="width:auto; max-width:90%!important;" alt="'.Smart::escape_html((string)$fmime[0]).'" title="'.Smart::escape_html((string)$fmime[0]).'" src="data:'.Smart::escape_html((string)$fmime[0]).';base64,'.Smart::b64_enc((string)\SmartModExtLib\Svn\SvnWebManager::getFile($repo, $path, $rev)).'"></center><br>';
						} //end if
					} else {
						$fhtml = '<br><hr><center><h3 style="color:#666699;">File is too large to display here ...  Size: '.(int)$bsize.' bytes ...</h3></center>';
					} //end if
					$highlight_arr = (array) \SmartModExtLib\HighlightSyntax\SmartViewHtmlHelpers::getSyntaxByFileType((string)$path);
					$code_type = (string) $highlight_arr['type']; // first get from here
					if((string)$code_type == '') {
						$code_type = 'plaintext'; // fallback
					} //end if
					$highlight_arr = null;
					$main = (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'web-manager-view-file.inc.htm',
						[
							'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
							'REPO-ACTION' 		=> (string) $op,
							'REPO-NAME' 		=> (string) $repo,
							'REPO-PATH' 		=> (string) $path,
							'TYPE' 				=> (string) $type,
							'MIMETYPE' 			=> (string) $fmime[0],
							'FILE-SIZE-BYTES' 	=> (int)    $bsize,
							'FILE-SIZE' 		=> (string) $fsize,
							'ICON-SUFFIX' 		=> (string) \SmartModExtLib\Webdav\DavUtils::getFileTypeSuffixIcon((string)$path),
							'REVISION' 			=> (string) $rev,
							'CODE-HIGHLIGHT' 	=> (string) \SmartModExtLib\HighlightSyntax\SmartViewHtmlHelpers::htmlJsLoadHilightCodeSyntax('body', 'dracula'),
							'CODE-TYPE' 		=> (string) $code_type,
							'CODE-HTML' 		=> (string) (((int)$bsize <= (int)\SmartModExtLib\Svn\SvnWebManager::MAX_FILESIZE_DISPLAY) ? SmartMarkersTemplating::prepare_nosyntax_html_template((string)Smart::escape_html((string)\SmartModExtLib\Svn\SvnWebManager::getFile($repo, $path, $rev))) : ''),
							'CODE-EXT-HTML' 	=> (string) $fhtml,
						]
					);
				} else {
					$fhtml = '';
					if(\SmartModExtLib\Svn\SvnWebManager::isImageFileByMimeType((string)$fmime[0]) === true) {
						if((int)$bsize <= (int)\SmartModExtLib\Svn\SvnWebManager::MAX_FILESIZE_DISPLAY) {
							$fhtml = '<br><hr><br><center><img style="width:auto; max-width:90%!important;" alt="'.Smart::escape_html((string)$fmime[0]).'" title="'.Smart::escape_html((string)$fmime[0]).'" src="data:'.Smart::escape_html((string)$fmime[0]).';base64,'.Smart::b64_enc((string)\SmartModExtLib\Svn\SvnWebManager::getFile($repo, $path, $rev)).'"></center>';
						} else {
							$fhtml = '<br><hr><center><h3 style="color:#666699;">File is too large to display here ...  Size: '.(int)$bsize.' bytes ...</h3></center>';
						} //end switch
					} //end if
					$main = (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'web-manager-view-file.inc.htm',
						[
							'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
							'REPO-ACTION' 		=> (string) $op,
							'REPO-NAME' 		=> (string) $repo,
							'REPO-PATH' 		=> (string) $path,
							'TYPE' 				=> (string) $type,
							'MIMETYPE' 			=> (string) $fmime[0],
							'FILE-SIZE-BYTES' 	=> (int)    $bsize,
							'FILE-SIZE' 		=> (string) $fsize,
							'ICON-SUFFIX' 		=> (string) \SmartModExtLib\Webdav\DavUtils::getFileTypeSuffixIcon((string)$path),
							'REVISION' 			=> (string) $rev,
							'CODE-HIGHLIGHT' 	=> (string) '',
							'CODE-TYPE' 		=> (string) '',
							'CODE-HTML' 		=> (string) '<b><i>Mime-Type</i>:&nbsp;'.Smart::escape_html((string)$fmime[0]).'</b>',
							'CODE-EXT-HTML' 	=> (string) $fhtml,
						]
					);
				} //end if else

				break;

			case 'cat': // files only

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$type = (string) $this->RequestVarGet('type', '', 'string');
				$rev  = (string)  $this->RequestVarGet('rev', 'HEAD', 'string');

				if(((string)$path == '') OR ((string)$path == '/')) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Empty or Invalid File Selected ...');
					return;
				} //end if

				if((string)$type != 'file') {
					$this->PageViewSetErrorStatus(400, 'ERROR: Empty Type Selected ...');
					return;
				} //end if

				$fname = (string) Smart::base_name((string)$path);
				$fmime = (array) SmartFileSysUtils::getArrMimeType((string)$fname);

				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', (string)$fmime[0]); // set mime type: Image / PNG
				$this->PageViewSetCfg('rawdisp', 'attachment; filename="'.str_replace(['"'], ['\''], (string)$fname).'"'); // display inline and set the file name for the image
				$this->PageViewSetVar(
					'main', (string) \SmartModExtLib\Svn\SvnWebManager::getFile($repo, $path, $rev)
				);

				return; // STOP HERE, it is RAW Page

				break;

			case 'dwarch': // dir paths :: download archive

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$type = (string) $this->RequestVarGet('type', '', 'string');
				$rev  = (string)  $this->RequestVarGet('rev', 'HEAD', 'string');

				if((string)$path == '/') {
					$path = '';
				} //end if

				$repos = (array) \SmartModExtLib\Svn\SvnWebManager::getReposConfigs();
				if(((string)trim((string)$repo) == '') OR (Smart::array_size($repos[(string)trim((string)$repo)]) <= 0)) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid SVN Repo: ['.$repo.']');
					return;
				} //end if
				if(!$repos[(string)trim((string)$repo)]['allow-download']) {
					$this->PageViewSetErrorStatus(400, 'ERROR: This SVN Repo cannot be Downloaded: ['.$repo.']');
					return;
				} //end if

				if((string)$type == 'file') {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid SVN Path Type: ['.$repo.']');
					return;
				} // end if
				$crr_path = (string) $path.'/';

				$arr = (array) \SmartModExtLib\Svn\SvnWebManager::exportPath((string)$repos[(string)trim((string)$repo)]['allow-download'], $repo, $path, $rev);
				if(((string)$arr['f-content'] == '') OR ((string)$arr['f-mime'] == '') OR ((string)$arr['f-name'] == '')) {
					$this->PageViewSetErrorStatus(500, 'ERROR: Invalid SVN Archive Export');
					return;
				} //end if

				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', (string)$arr['f-mime']);
				$this->PageViewSetCfg('rawdisp', 'attachment; filename="'.Smart::safe_filename($arr['f-name']).'"'); // display inline and set the file name for the image
				$this->PageViewSetVar(
					'main', (string) $arr['f-content']
				);

				return; // STOP HERE, it is RAW Page

				break;

			case 'list': // dir paths :: list

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$type = (string) $this->RequestVarGet('type', '', 'string');
				$rev  = (string) $this->RequestVarGet('rev', 'HEAD', 'string');
				$prev = (string) $this->RequestVarGet('prev', '', 'string');
				if((string)$prev == '') {
					$prev = (string) $rev;
				} //end if

				if((string)$path == '/') {
					$path = '';
				} //end if

				$repos = (array) \SmartModExtLib\Svn\SvnWebManager::getReposConfigs();
				if(((string)trim((string)$repo) == '') OR (Smart::array_size($repos[(string)trim((string)$repo)]) <= 0)) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid SVN Repo: ['.$repo.']');
					return;
				} //end if

				$arch_dwn_allow = false;
				$arch_dwn_mode = '';
				if(!!$repos[(string)trim((string)$repo)]['allow-download']) {
					$arch_dwn_allow = true;
					$arch_dwn_mode = (string) $repos[(string)trim((string)$repo)]['allow-download'];
				} //end if

				if((string)$rev != (string)$prev) {
					$prev_path = (string) \SmartModExtLib\Svn\SvnWebManager::getRealPathFromPrevRevision($repo, $path, $rev, $prev);
					if(!$prev_path) {
						$this->PageViewSetErrorStatus(404, 'NOTICE: SVN Path: `'.$path.'` NOT Found in Revision: #'.$rev.' switch from Revision: #'.$prev.' ...');
						return;
					} //end if
					$path = (string) $prev_path;
				} //end if

				if((string)$type == 'file') {
					$isfile = true;
					$crr_path = (string) $path;
					$mimearr = (array) SmartFileSysUtils::getArrMimeType((string)$crr_path);
					$mimetype = (string) $mimearr[0];
					$mimedisp = (string) $mimearr[2];
					if(\SmartModExtLib\Svn\SvnWebManager::isTextFileByMimeType((string)$mimetype) === true) {
						$display_link = 'admin.php?page='.$this->ControllerGetParam('controller').'&op=view&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($crr_path).'&type=';
					} else {
						$display_link = '';
					} //end if else
					$mimearr = array(); // free mem
					$diffpath = false;
				} else { // dir
					$isfile = false;
					$crr_path = (string) (substr($path, -1, 1) == '/') ? $path : $path.'/';
					$mimetype = '';
					$mimedisp = '';
					$display_link = '';
					$diffpath = true;
				} //end if else

				$lstrevs = 250;
				$revs = (array) \SmartModExtLib\Svn\SvnWebManager::listRevs($repo, $path, $rev, (int)($lstrevs+1));
				if(Smart::array_size($revs) < 1) {
					$this->PageViewSetErrorStatus(404, 'NOTICE: SVN Path: `'.$path.'` NOT Revisions found for: #'.$rev.' ...');
					return;
				} //end if
				//print_r($revs); die();
				if(\Smart::array_size($revs) > $lstrevs) {
					$lastrevisfirst = 'no';
					unset($revs[(int)$lstrevs]); // remove last element
					$rev_first = -1; // unknown
				} else {
					$lastrevisfirst = 'yes';
					$rev_first = (int) $revs[(int)(count($revs)-1)]['revision'];
				} //end if else

				$arr = (array) \SmartModExtLib\Svn\SvnWebManager::listRepo($repo, $path, $rev);
				//print_r($arr); die();

				$rev_head = (int) \SmartModExtLib\Svn\SvnWebManager::getHeadRevision($repo);
				if(((string)$rev == '') OR ((string)$rev == 'HEAD')) {
					$rev_crr = (int) $rev_head; // $revs[0]['revision'];
				} else {
					$rev_crr = (int) $rev;
				} //end if else

				$title = 'SVN - Web Manager :: List Repo: '.$repo.' @ '.$crr_path;
				$main = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'web-manager-list-repo.inc.htm',
					[
						'VIEWS-PATH' 				=> (string) $this->ControllerGetParam('module-view-path'),
						'HOME-URL' 					=> (string) 'admin.php?page='.$this->ControllerGetParam('controller'),
						'MAX-FSIZE-PRETTY' 			=> (string) \SmartUtils::pretty_print_bytes((int)\SmartModExtLib\Svn\SvnWebManager::MAX_FILESIZE_DISPLAY, 1, ' '),
						'REPO-NAME' 				=> (string) $repo,
						'REPO-PATH' 				=> (string) $crr_path,
						'BACK-URL' 					=> $path ? 'admin.php?page='.$this->ControllerGetParam('controller').'&op=list&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url(Smart::dir_name($path)).'&rev='.Smart::escape_url($rev) : 'admin.php?page='.$this->ControllerGetParam('controller'),
						'SELECT-URLBASE' 			=> 'admin.php?page='.$this->ControllerGetParam('controller').'&op=list&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($crr_path),
						'SELECT-URLFILE' 			=> $isfile ? 'admin.php?page='.$this->ControllerGetParam('controller').'&op=cat&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($crr_path).'&type=file' : '#',
						'DOWNLOAD-ARCH-ALLOW' 		=> $arch_dwn_allow ? 'yes' : 'no',
						'DOWNLOAD-ARCH-MODE' 		=> (string) $arch_dwn_mode,
						'DOWNLOAD-ARCH-URL' 		=> $isfile ? '' : 'admin.php?page='.$this->ControllerGetParam('controller').'&op=dwarch&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($crr_path),
						'TYPE' 						=> (string) $type,
						'MIMETYPE' 					=> (string) $mimetype,
						'MIMEDISP' 					=> (string) $mimedisp,
						'DISPLAY-LINK' 				=> (string) $display_link,
						'REPODATA' 					=> (array) $arr,
						'REVSDATA' 					=> (array) $revs,
						'LASTREVISFIRST' 			=> (string) $lastrevisfirst,
						'REV-CRR' 					=> (int) $rev_crr,
						'REV-FIRST' 				=> (int) $rev_first,
						'REV-HEAD' 					=> (int) $rev_head,
						'COMPARE-ROOT-URL' 			=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&op=compare&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url('/').'&rev=',
						'COMPARE-URL' 				=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&op=compare&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($path).'&rev=',
						'COMPARE-PATH' 				=> (string) $path,
						'COMPARE-FILE-DIFF-URL' 	=> (((string)$type == 'file') ? 'admin.php?page='.$this->ControllerGetParam('controller').'&op=diff&repo='.Smart::escape_url($repo).'&type=file&path='.Smart::escape_url($path).'&rev=' : ''),
						'COMPARE-FILE-VIEW-URL' 	=> (((string)$type == 'file') ? 'admin.php?page='.$this->ControllerGetParam('controller').'&op=view&repo='.Smart::escape_url($repo).'&type=file&path='.Smart::escape_url($path).'&rev=' : ''),
						'PROPS-URL' 				=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&op=props&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($path).'&type='.Smart::escape_url($type).'&rev=',
						'HEAD-ROOT-URL' 			=> 'admin.php?page='.$this->ControllerGetParam('controller').'&op=list&repo='.Smart::escape_url($repo).'&path=/&rev=' // head revision must go into ROOT folder to avoid errors if the current folder have been deleted and is not available in the HEAD revision !!
					]
				);

				break;

			case 'info': // list all repos (listed in configs)
			default:

				$repos = (array) \SmartModExtLib\Svn\SvnWebManager::listRepos();

				$title = 'SVN - Web Manager :: List All Repos';
				$main = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'web-manager-info-repos.inc.htm',
					[
						'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
						'HOME-URL' 			=> (string) 'admin.php', // ?page='.$this->ControllerGetParam('controller')
						'SELECT-URLBASE' 	=> 'admin.php?page='.$this->ControllerGetParam('controller').'&op=list&path=&repo=',
						'REPOS' 			=> (array) $repos
					]
				);

		} //end switch
		//--

		//--
		$this->PageViewSetVars([
			'title' => (string) $title,
			'main' => (string) $main
		]);
		//--

	} //END FUNCTION


} //END CLASS


// end of php code
