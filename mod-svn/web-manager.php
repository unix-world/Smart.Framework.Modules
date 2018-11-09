<?php
// Controller: Svn/WebManager
// Route: admin.php?page=svn.web-manager
// Author: unix-world.org
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // ADMIN only
define('SMART_APP_MODULE_AUTH', true); // requires auth always

class SmartAppAdminController extends SmartAbstractAppController {

	/*
	 * IDEA: list +50 / -50 from the current selected revision ; if returns EMPTY TRY/CATCH TEST, it means selected path was deleted
	 * FOR DELETED Paths add TEST
	 *
	 */

	public function Run() {

		//--
		if(Smart::array_size($this->ConfigParamGet('svn')) <= 0) {
			$this->PageViewSetErrorStatus(500, 'ERROR: No SVN Area Config available ...');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetCfg('template-path', 'default'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template.htm'); // the default template
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
				$rev_new = (int) $revs[0]['revision'];
				$rev_old = (int) $revs[1]['revision']; // this can be zero if first selected

				$this->PageViewSetCfg('template-file', 'template-modal.htm'); // the default modal template
				$title = 'SVN - Web Manager :: Repo: '.$repo.' @ File: '.$path;
				$main = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'web-manager-view-file.inc.htm',
					[
						'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
						'REPO-NAME' 		=> (string) $repo,
						'REPO-PATH' 		=> (string) $path,
						'REVISION' 			=> (string) $rev,
						'CODE-HIGHLIGHT' 	=> (string) SmartComponents::js_code_highlightsyntax('div', ['web']),
						'CODE-TYPE' 		=> 'diff',
						'CODE-HTML' 		=> (string) SmartMarkersTemplating::prepare_nosyntax_html_template((string)Smart::escape_html((string)\SmartModExtLib\Svn\SvnWebManager::getDiffFile($repo, $path, $rev_old, $rev_new)))
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

				$fname = (string) Smart::base_name((string)$path);
				$fmime = (array) SmartFileSysUtils::mime_eval($fname);

				$this->PageViewSetCfg('template-file', 'template-modal.htm'); // the default modal template
				$title = 'SVN - Web Manager :: Repo: '.$repo.' @ File: '.$path;

				if(\SmartModExtLib\Svn\SvnWebManager::isTextFileByMimeType((string)$fmime[0]) === true) {
					$highlight_arr = (array) SmartComponents::filetype_highlightsyntax((string)$path);
					$main = (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'web-manager-view-file.inc.htm',
						[
							'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
							'REPO-NAME' 		=> (string) $repo,
							'REPO-PATH' 		=> (string) $path,
							'REVISION' 			=> (string) $rev,
							'CODE-HIGHLIGHT' 	=> (string) SmartComponents::js_code_highlightsyntax('div', (array)$highlight_arr['pack']),
							'CODE-TYPE' 		=> (string) $highlight_arr['type'],
							'CODE-HTML' 		=> (string) SmartMarkersTemplating::prepare_nosyntax_html_template((string)Smart::escape_html((string)\SmartModExtLib\Svn\SvnWebManager::getFile($repo, $path, $rev)))
						]
					);
				} else {
					$main = (string) SmartMarkersTemplating::render_file_template(
						$this->ControllerGetParam('module-view-path').'web-manager-view-file.inc.htm',
						[
							'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
							'REPO-NAME' 		=> (string) $repo,
							'REPO-PATH' 		=> (string) $path,
							'REVISION' 			=> (string) $rev,
							'CODE-HIGHLIGHT' 	=> (string) '',
							'CODE-TYPE' 		=> (string) '',
							'CODE-HTML' 		=> (string) '<b>'.Smart::escape_html((string)$fmime[0]).'</b>'
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
				$fmime = (array) SmartFileSysUtils::mime_eval($fname);

				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', (string)$fmime[0]); // set mime type: Image / PNG
				$this->PageViewSetCfg('rawdisp', 'attachment; filename="'.str_replace(['"'], ['\''], (string)$fname).'"'); // display inline and set the file name for the image
				$this->PageViewSetVar(
					'main', (string) \SmartModExtLib\Svn\SvnWebManager::getFile($repo, $path, $rev)
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

				$repos = (array) Smart::get_from_config('svn.repos');
				if(((string)trim((string)$repo) == '') OR (Smart::array_size($repos[(string)trim((string)$repo)]) <= 0)) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid SVN Repo: ['.$repo.']');
					return;
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
					$mimearr = (array) SmartFileSysUtils::mime_eval($crr_path);
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

				$lstrevs = 100;
				$revs = (array) \SmartModExtLib\Svn\SvnWebManager::listRevs($repo, $path, $rev, (int)($lstrevs+1));
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
						'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
						'HOME-URL' 			=> (string) 'admin.php?page='.$this->ControllerGetParam('controller'),
						'REPO-NAME' 		=> (string) $repo,
						'REPO-PATH' 		=> (string) $crr_path,
						'BACK-URL' 			=> $path ? 'admin.php?page='.$this->ControllerGetParam('controller').'&op=list&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url(Smart::dir_name($path)).'&rev='.Smart::escape_url($rev) : 'admin.php?page='.$this->ControllerGetParam('controller'),
						'SELECT-URLBASE' 	=> 'admin.php?page='.$this->ControllerGetParam('controller').'&op=list&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($crr_path),
						'SELECT-URLFILE' 	=> $isfile ? 'admin.php?page='.$this->ControllerGetParam('controller').'&op=cat&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($crr_path).'&type=file' : '#',
						'DOWNLOAD-ARCH-URL' => $isfile ? '' : 'admin.php?page='.$this->ControllerGetParam('controller').'&op=dwarch&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($crr_path),
						'TYPE' 				=> (string) $type,
						'MIMETYPE' 			=> (string) $mimetype,
						'MIMEDISP' 			=> (string) $mimedisp,
						'DISPLAY-LINK' 		=> (string) $display_link,
						'REPODATA' 			=> (array) $arr,
						'REVSDATA' 			=> (array) $revs,
						'LASTREVISFIRST' 	=> (string) $lastrevisfirst,
						'REV-CRR' 			=> (int) $rev_crr,
						'REV-FIRST' 		=> (int) $rev_first,
						'REV-HEAD' 			=> (int) $rev_head,
						'COMPARE-ROOT-URL' 	=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&op=compare&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url('/').'&rev=',
						'COMPARE-URL' 		=> (string) 'admin.php?page='.$this->ControllerGetParam('controller').'&op=compare&repo='.Smart::escape_url($repo).'&path='.Smart::escape_url($path).'&rev=',
						'HEAD-ROOT-URL' 	=> 'admin.php?page='.$this->ControllerGetParam('controller').'&op=list&repo='.Smart::escape_url($repo).'&path=/&rev=' // head revision must go into ROOT folder to avoid errors if the current folder have been deleted and is not available in the HEAD revision !!
					]
				);

				break;

			case 'dwarch': // dir paths :: download archive

				$repo = (string) $this->RequestVarGet('repo', '', 'string');
				$path = (string) $this->RequestVarGet('path', '', 'string');
				$type = (string) $this->RequestVarGet('type', '', 'string');
				$rev  = (string)  $this->RequestVarGet('rev', 'HEAD', 'string');

				if((string)$path == '/') {
					$path = '';
				} //end if

				$repos = (array) Smart::get_from_config('svn.repos');
				if(((string)trim((string)$repo) == '') OR (Smart::array_size($repos[(string)trim((string)$repo)]) <= 0)) {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid SVN Repo: ['.$repo.']');
					return;
				} //end if

				if((string)$type == 'file') {
					$this->PageViewSetErrorStatus(400, 'ERROR: Invalid SVN Path Type: ['.$repo.']');
					return;
				} // end if
				$crr_path = (string) $path.'/';

				$arr = (array) \SmartModExtLib\Svn\SvnWebManager::exportPath($repo, $path, $rev);
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

			case 'info': // list all repos (listed in configs)
			default:

				$repos = (array) \SmartModExtLib\Svn\SvnWebManager::listRepos();

				$title = 'SVN - Web Manager :: List All Repos';
				$main = (string) SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-view-path').'web-manager-info-repos.inc.htm',
					[
						'VIEWS-PATH' 		=> (string) $this->ControllerGetParam('module-view-path'),
						'HOME-URL' 			=> (string) 'admin.php?page='.$this->ControllerGetParam('controller'),
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


//end of php code
?>