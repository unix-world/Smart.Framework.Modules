<?php
// [LIB - SmartFramework / Svn / Svn Web Manager]
// (c) 2008-present unix-world.org - all rights reserved

// Class: \SmartModExtLib\Svn\SvnWebManager
// Type: Module Library

namespace SmartModExtLib\Svn;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


final class SvnWebManager {

	// ::
	// v.20250107

	const MAX_FILESIZE_DISPLAY = 8388608; // 8MB

	private static $svn_cache_dir = 'tmp/cache/svn/'; 		// must have trailing slash :: the svn proc jail root
	private static $configs = null;


	//============================================================ OK
	public static function getReposConfigs() {
		//--
		if(\is_array(self::$configs)) {
			return (array) self::$configs;
		} //end if
		//--
		if(!\is_array(self::$configs)) {
			self::$configs = array();
		} //end if
		//--
		$repos = (array) \Smart::get_from_config('svn.repos', 'array');
		if(\is_array($repos)) {
			foreach($repos as $key => $val) {
				$key = (string) \trim((string)$key);
				if((string)$key != '') {
					$tmp_repo = array();
					if(\is_array($val)) {
						$val = (array) \Smart::array_init_keys(
							$val,
							[
								'url',
								'path',
								'user',
								'pass',
								'encrypted-pass',
								'allow-download',
								'readonly'
							]
						);
						foreach($val as $k => $v) {
							if(\Smart::is_nscalar($v)) {
								$tmp_repo[(string)$k] = $v;
							} //end if
						} //end foreach
					} //end if
					if(\Smart::array_size($tmp_repo) > 0) {
						$tmp_repo['url'] = (string) \trim((string)$tmp_repo['url']);
						if((string)$tmp_repo['path'] != '') {
							$tmp_repo['path'] = (string) \trim((string)$tmp_repo['path']);
							$tmp_repo['path'] = (string) \trim((string)$tmp_repo['path'], '/');
							if((string)$tmp_repo['path'] != '') {
								$tmp_repo['path'] = '/'.$tmp_repo['path'];
							} //end if
						} //end if
						if($tmp_repo['encrypted-pass'] === true) {
							$tmp_repo['pass'] = (string) \SmartCipherCrypto::bf_decrypt((string)$tmp_repo['pass']);
						} //end if
						if($tmp_repo['readonly'] !== true) {
							$tmp_repo['readonly'] = false;
						} //end if
						self::$configs[(string)$key] = (array) $tmp_repo;
					} //end if
				} //end if
			} //end foreach
		} //end if
		//--
		$repos = null;
		//--
		return (array) self::$configs;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function listRepos() {
		//--
		$repos = (array) self::getReposConfigs();
		if(\Smart::array_size($repos) <= 0) {
			return array();
		} //end if
		//--
		$arr = array();
		//--
		foreach($repos as $key => $val) {
			//--
			if(((string)\trim((string)$key) != '') AND (self::validateCfgRepoEntry($val))) {
				//--
				$tmp_arr = (array) self::execSvnCmd('info', (string)$val['url'], '', $val['user'], $val['pass'], 'xml-arr'); // OK
				//--
				if(\Smart::array_size($tmp_arr) > 0) {
					if(\Smart::array_size($tmp_arr['info']) > 0) {
						if(\Smart::array_size($tmp_arr['info'][0]) > 0) {
							//--
							$tmp_path = (string) $val['path'];
							//--
							$tmp_repo_protocol = '';
							if(\stripos((string)$val['url'], 'file:///') === 0) {
								$tmp_repo_protocol = 'file:///';
							} else {
								$tmp_repo_protocol = (array) \explode('//', (string)$val['url']);
								$tmp_repo_protocol = (string) \strtolower((string)\trim((string)$tmp_repo_protocol[0])).'//';
							} //end if
							//--
							$tmp_secure_pass = (int) $val['encrypted-pass'];
							$tmp_is_readonly = (int) $val['readonly'];
							//--
							$arr[] = [
								'repo-name' 		=> (string) \trim((string)$key),
								'repo-url' 			=> (string) $val['url'],
								'repo-protocol' 	=> (string) $tmp_repo_protocol,
								'repo-path' 		=> (string) $tmp_path,
								'repo-user' 		=> (string) $val['user'],
								'repo-secure-pass' 	=> (int)    $tmp_secure_pass,
								'repo-readonly' 	=> (int)    $tmp_is_readonly,
								'last-rev-num' 		=> (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'info.0.entry.0.commit|@attributes.0.revision', '.'),
								'last-rev-author' 	=> (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'info.0.entry.0.commit.0.author.0', '.'),
								'last-rev-date' 	=> (string) \date('D, d M Y H:i:s', \strtotime((string)\Smart::array_get_by_key_path((array)$tmp_arr, 'info.0.entry.0.commit.0.date.0', '.')))
							];
							//--
						} //end if
					} //end if
				} //end if
				//--
				$tmp_arr = array();
				//--
			} //end if
			//--
		} //end foreach
		//--
		return (array) $arr;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function listRepo($repo, $path, $rev) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return array();
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return array();
		} //end if
		//--
		$arr = array();
		//--
		$tmp_arr = (array) self::execSvnCmd('list', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'xml-arr', [ 'rev' => (string)$rev ]); // OK
		if(\Smart::array_size($tmp_arr) <= 0) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN List: Invalid XML (1): ['.$path.']',
				'ERR: SVN List: Invalid XML (1)' // msg to display
			);
			return array();
		} //end if
		//--
		if((!isset($tmp_arr['lists'])) OR (\Smart::array_size($tmp_arr['lists']) <= 0)) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN List: Invalid XML (2): ['.$path.']',
				'ERR: SVN List: Invalid XML (2)' // msg to display
			);
			return array();
		} //end if
		//--
		if((!isset($tmp_arr['lists'][0])) OR (\Smart::array_size($tmp_arr['lists'][0]) <= 0)) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN List: Invalid XML (3): ['.$path.']',
				'ERR: SVN List: Invalid XML (3)' // msg to display
			);
			return array();
		} //end if
		//--
		$tmp_arr = (array) $tmp_arr['lists'][0];
		//--
		if((\Smart::array_size($tmp_arr['list']) <= 0) OR (\Smart::array_size($tmp_arr['list|@attributes']) <= 0)) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN List: Invalid XML (4): ['.$path.']',
				'ERR: SVN List: Invalid XML (4)' // msg to display
			);
			return array();
		} //end if
		//--
		$tmp_path = (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'list|@attributes.0.path', '.');
		$tmp_entratt = \Smart::array_get_by_key_path((array)$tmp_arr, 'list.0.entry|@attributes', '.'); // don't force array !! because (array)'' = array(0=>'')
		$tmp_entries = \Smart::array_get_by_key_path((array)$tmp_arr, 'list.0.entry', '.'); // don't force array !!
		$tmp_arr = array();
		if(\Smart::array_size($tmp_entries) <= 0) {
			return array(); // Fix: no entries found
		} //end if
		if(\Smart::array_size($tmp_entratt) <= 0) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN List: Invalid XML (5): ['.$path.']',
				'ERR: SVN List: Invalid XML (5)' // msg to display
			);
			return array(); // no entries found
		} //end if
		//--
		for($i=0; $i<\Smart::array_size($tmp_entries); $i++) {
			//--
			$val = (array) $tmp_entries[$i];
			//--
			if(\Smart::array_size($val) > 0) {
				//--
				$size = '-';
				$bsize = 0;
				if(\array_key_exists('size', (array)$val)) {
					$bsize = (int) \Smart::array_get_by_key_path((array)$val, 'size.0', '.');
					$size = (string) \SmartUtils::pretty_print_bytes((int)$bsize, 1, ' '); // {{{SYNC-SVN-PRETTY-PRINT-BYTES}}}
				} //end if
				//--
				$the_item_name = (string) \Smart::array_get_by_key_path((array)$val, 'name.0', '.');
				$the_item_type = (string) \Smart::array_get_by_key_path((array)$tmp_entratt, $i.'.kind', '.');
				$the_item_icon_suffix = '';
				if((string)$the_item_type == 'file') {
					$the_item_icon_suffix = (string) \SmartModExtLib\Webdav\DavUtils::getFileTypeSuffixIcon((string)$the_item_name);
				} //end if
				//--
				$arr[] = [
					'repo-name' => (string) $repo,
					'base-path' => (string) $tmp_path,
					'type' => (string) $the_item_type,
					'name' => (string) $the_item_name,
					'icon-suffix' => (string) $the_item_icon_suffix,
					'size-bytes' => (int) $bsize,
					'size' => (string) $size,
					'last-rev-num' => (string) \Smart::array_get_by_key_path((array)$val, 'commit|@attributes.0.revision', '.'),
					'last-rev-author' => (string) \Smart::array_get_by_key_path((array)$val, 'commit.0.author.0', '.'),
					'last-rev-date' => (string) \date('D, d M Y H:i:s', \strtotime((string)\Smart::array_get_by_key_path((array)$val, 'commit.0.date.0', '.'))),
				];
				//--
			} //end if
			//--
		} //end for
		//--
		return (array) $arr;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function getHeadRevision($repo) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return -1;
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return -1;
		} //end if
		//--
		$tmp_arr = (array) self::execSvnCmd('get-revs', (string)$rdata['url'], '', $rdata['user'], $rdata['pass'], 'xml-arr', [ 'start-rev' => 'HEAD', 'num-revs' => 1 ]); // get latest revision
		//--
		$entry_zero = \Smart::array_get_by_key_path((array)$tmp_arr, 'log.0.logentry|@attributes.0', '.');
		if((\Smart::array_size($entry_zero) <= 0) OR (!\array_key_exists('revision', (array)$entry_zero))) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Export: Failed to get SVN Export Head Revision ...',
				'ERR: Failed to get SVN Export Head Revision' // msg to display
			);
		} //end if
		//--
		return (string) ($entry_zero['revision'] ? (int)$entry_zero['revision'] : '');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function getProps($repo, $path, $rev) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return array();
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return array();
		} //end if
		//--
		$arr = array();
		//--
		$tmp_arr = (array) self::execSvnCmd('proplist', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'xml-arr', [ 'rev' => (string)$rev ]); // OK
		//--
		if(\Smart::array_size($tmp_arr) <= 0) {
			return array();
		} //end if
		//--
		if(\Smart::array_size($tmp_arr['properties']) <= 0) {
			return array();
		} //end if
		if(\Smart::array_size($tmp_arr['properties'][0]) <= 0) {
			return array();
		} //end if
		if(\Smart::array_size($tmp_arr['properties'][0]['target']) <= 0) {
			return array();
		} //end if
		if(\Smart::array_size($tmp_arr['properties'][0]['target'][0]) <= 0) {
			return array();
		} //end if
		if(\Smart::array_size($tmp_arr['properties'][0]['target'][0]['property|@attributes']) <= 0) {
			return array();
		} //end if
		//--
		$arr = [];
		foreach($tmp_arr['properties'][0]['target'][0]['property|@attributes'] as $key => $val) {
			if(\is_array($val)) {
				if(\array_key_exists('name', $val)) {
					$val['name'] = (string) \trim((string)$val['name']);
					if((string)$val['name'] != '') {
						$arr[(string)$val['name']] = (string) \Smart::array_get_by_key_path(
							(array) self::execSvnCmd('propget', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'xml-arr', [ 'rev' => (string)$rev, 'prop' => (string)$val['name'] ]),
							'properties.0.target.0.property.0',
							'.'
						);
					} //end if
				} //end if
			} //end if
		} //end foreach
		//--
		return (array) $arr;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function getCompare($repo, $path, $rev) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return array();
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return array();
		} //end if
		//-- get compare just for root and later filter by path
		$tmp_arr = self::execSvnCmd('compare', (string)$rdata['url'], '/', $rdata['user'], $rdata['pass'], 'xml-arr', [ 'rev' => (string)$rev ]); // OK
		//--
		if(\Smart::array_size($tmp_arr['log']) <= 0) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Compare: Invalid XML (1): ['.$path.']',
				'ERR: SVN Compare: Invalid XML (1)' // msg to display
			);
			return array();
		} //end if
		if(\Smart::array_size($tmp_arr['log'][0]) <= 0) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Compare: Invalid XML (2): ['.$path.']',
				'ERR: SVN Compare: Invalid XML (2)' // msg to display
			);
			return array();
		} //end if
		//--
		$tmp_arr = (array) $tmp_arr['log'][0];
		$tmp_rev = (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'logentry|@attributes.0.revision', '.');
		$tmp_author = (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'logentry.0.author.0', '.');
		$tmp_date = (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'logentry.0.date.0', '.');
		$tmp_msg = (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'logentry.0.msg.0', '.');
		$tmp_arr = \Smart::array_get_by_key_path((array)$tmp_arr, 'logentry.0.paths.0', '.');
		//--
		if((string)$tmp_rev !== (string)$rev) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Compare: Invalid XML Rev.['.$tmp_rev.'] / Rev.['.$rev.'] (3): ['.$path.']',
				'ERR: SVN Compare: Invalid XML (3)' // msg to display
			);
			return array();
		} //end if
		if((\Smart::array_size($tmp_arr) <= 0) OR (\Smart::array_size($tmp_arr['path']) <= 0) OR (\Smart::array_size($tmp_arr['path|@attributes']) <= 0) OR (\Smart::array_size($tmp_arr['path']) != \Smart::array_size($tmp_arr['path|@attributes']))) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Compare: Invalid XML (4): ['.$path.']',
				'ERR: SVN Compare: Invalid XML (4)' // msg to display
			);
			return array();
		} //end if
		//--
		$arr = array();
		$arr['metainfo'] = [
			'rev' 		=> (string) $tmp_rev,
			'author' 	=> (string) $tmp_author,
			'date' 		=> (string) \date('D, d M Y H:i:s', \strtotime((string)$tmp_date)),
			'msg' 		=> (string) $tmp_msg
		];
		$arr['changes'] = [];
		for($i=0; $i<\Smart::array_size($tmp_arr['path']); $i++) {
			if(((string)$path == '') OR ((string)$path == '/') OR (\strpos($tmp_arr['path'][$i], (((string)$rdata['path'] != '') ? $rdata['path'] : '').$path) === 0)) {
				$fixed_path = (string) self::fixTrunkPath((string)$tmp_arr['path'][$i], (array)$rdata);
				$tmp_atts = (array) $tmp_arr['path|@attributes'][$i];
				$the_item_icon_suffix = '';
				if((string)$tmp_atts['kind'] == 'file') {
					$the_item_icon_suffix = (string) \SmartModExtLib\Webdav\DavUtils::getFileTypeSuffixIcon((string)$fixed_path);
				} //end if
				$arr['changes'][] = [
					'icon-suffix' 	=> (string) $the_item_icon_suffix,
					'path' 			=> (string) $fixed_path,
					'type' 			=> (string) (isset($tmp_atts['kind']) ? $tmp_atts['kind'] : null),
					'txtmod' 		=> (string) (isset($tmp_atts['text-mods']) ? $tmp_atts['text-mods'] : null),
					'action' 		=> (string) (isset($tmp_atts['action']) ? $tmp_atts['action'] : null),
					'propsmod' 		=> (string) (isset($tmp_atts['prop-mods']) ? $tmp_atts['prop-mods'] : null),
					'copyfromp' 	=> (string) (isset($tmp_atts['copyfrom-path']) ? $tmp_atts['copyfrom-path'] : null),
					'copyfromr' 	=> (string) (isset($tmp_atts['copyfrom-rev']) ? $tmp_atts['copyfrom-rev'] : null)
				];
			} //end if
		} //end for
		//--
		return (array) $arr;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function getFile($repo, $path, $rev) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return '';
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return '';
		} //end if
		//--
		return (string) self::execSvnCmd('cat', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'string', [ 'rev' => (string)$rev ]); // OK
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ aaa
	public static function getRealPathFromPrevRevision($repo, $path, $revx, $revy) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return '';
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return '';
		} //end if
		//--
		$tmp_arr = (array) self::execSvnCmd('prev-info', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'xml-arr', [ 'rev-old' => (string)$revx, 'rev-new' => (string)$revy ]); // OK
		//--
		$path = (string) \Smart::array_get_by_key_path((array)$tmp_arr, 'info.0.entry.0.relative-url.0', '.');
		$rpath = (string) \trim((string)\substr((string)$path, 1));
		if((\substr((string)$path, 0, 1) != '^') OR ((string)$rpath == '')) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Revision Path: Invalid Real Path for: ['.$path.'] @ ['.$path.']',
				'ERR: Invalid SVN Revision Path' // msg to display
			);
		} //end if
		//--
		return (string) self::fixTrunkPath((string)$rpath, (array)$rdata);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function getDiffFile($repo, $path, $revx, $revy) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return '';
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return '';
		} //end if
		//--
		return (string) self::execSvnCmd('diff', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'string', [ 'rev-old' => (string)$revx, 'rev-new' => (string)$revy ]); // OK
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function exportPath($archtype, $repo, $path, $rev) {
		//--
		if((string)$archtype == '') {
			return array();
		} //end if
		//--
		$mime_type = 'application/octet-stream'; // unknown
		switch((string)$archtype) { // {{{SYNC-SVN-EXP-ARCHS}}}
			case '7z': // OK
				$mime_type = 'application/x-7z-compressed';
				break;
			case 'zip': // OK
				$mime_type = 'application/zip';
				break;
			case 'tar.gz': // OK
				$mime_type = 'application/x-compressed';
				break;
			default:
				// NOT OK
				\Smart::log_warning((string)__METHOD__.' #ERR# SVN Export: Invalid Export Mode:['.$archtype.']');
				return array();
		} //end switch
		//--
		$repo  = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return array();
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return array();
		} //end if
		//--
		$path = (string) \trim((string)$path);
		if(((string)$path == '') OR ((string)$path == '/')) {
			$finpath = '';
		} else {
			$finpath = (string) '_'.\trim((string)\Smart::safe_filename((string)$path, '-'), '-');
		} //end if
		$expname = (string) \Smart::safe_filename((string)$repo.'.r'.$rev.$finpath);
		$expdir = (string) 'svn-exp/'.\Smart::uuid_10_seq().'-'.\Smart::uuid_10_str().'-'.\Smart::uuid_10_num();
		$archdir = (string) \SmartFileSysUtils::addPathTrailingSlash((string)$expdir).$expname;
		if(!\SmartFileSysUtils::checkIfSafePath((string)$archdir)) {
			\Smart::log_warning((string)__METHOD__.' #ERR# SVN Export: Invalid Export Dir:['.$archdir.']');
			return array();
		} //end if
		//--
		\SmartFileSystem::dir_create((string)self::$svn_cache_dir.$expdir, true); // recursive
		//--
		$ok = self::execSvnCmd('export', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'exit-code', [ 'export-dir' => (string)$archdir, 'rev' => (string)$rev ]); // OK
		$archname = '';
		$fcontent = '';
		if(($ok) AND (\SmartFileSystem::is_type_dir((string)self::$svn_cache_dir.$archdir))) {
			$archname = (string) self::buildArchive((string)$expdir, (string)$expname, (string)$archtype);
			if((string)$archname != '') {
				$fcontent = (string) \SmartFileSystem::read((string)$archname);
			} //end if
		} //end if
		//--
		self::rmDirRecursive((string)self::$svn_cache_dir.$expdir); // fix: because some paths in SVN may contain unsafe characters not allowed in Smart.Framework FileSystem this function will check just if dir is safe and minimal safety for paths
		//--
		return array(
			'f-content' => (string) $fcontent,
			'f-mime' 	=> (string) ($archname ? (string)$mime_type : ''), // 'application/zip'
			'f-name' 	=> (string) ($archname ? (string)\SmartFileSysUtils::extractPathFileName((string)$archname) : '')
		);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	public static function listRevs($repo, $path, $start, $num) {
		//--
		$repo = (string) \trim((string)$repo);
		if((string)\trim((string)$repo) == '') {
			return array();
		} //end if
		//--
		$repos = (array) self::getReposConfigs();
		$rdata = (array) $repos[(string)\trim((string)$repo)];
		if(!self::validateCfgRepoEntry($rdata)) {
			return array();
		} //end if
		//--
		$tmp_arr = (array) self::execSvnCmd('get-revs', (string)$rdata['url'], (string)$path, $rdata['user'], $rdata['pass'], 'xml-arr', [ 'start-rev' => (string)$start, 'num-revs' => (int)$num, 'rev' => (string)$start ]);
		//--
		if((!isset($tmp_arr['log'])) OR (\Smart::array_size($tmp_arr['log']) <= 0)) {
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Rev. List: Invalid XML (1): ['.$path.']',
				'ERR: SVN Rev. List: Invalid XML (1)' // msg to display
			);
			return array();
		} //end if
		if((!isset($tmp_arr['log'][0])) OR (\Smart::array_size($tmp_arr['log'][0]) <= 0)) {
			/* if zero revisions avoid fatal error
			\Smart::raise_error(
				__METHOD__.' #ERR# SVN Rev. List: Invalid XML (2): ['.$path.']',
				'ERR: SVN Rev. List: Invalid XML (2)' // msg to display
			);
			*/
			return array();
		} //end if
		//--
		$entries = (array) $tmp_arr['log'][0];
		$tmp_arr = array();
		$arr = [];
		for($i=0; $i<\Smart::array_size($entries['logentry']); $i++) {
			//--
			$arr[] = [
				'revision' => (string) \Smart::array_get_by_key_path((array)$entries['logentry|@attributes'], $i.'.revision', '.'),
				'author' => (string) \Smart::array_get_by_key_path((array)$entries['logentry'], $i.'.author.0', '.'),
				'date' => (string) \date('D, d M Y H:i:s', \strtotime((string)\Smart::array_get_by_key_path((array)$entries['logentry'], $i.'.date.0', '.'))),
				'msg' => (string) \Smart::array_get_by_key_path((array)$entries['logentry'], $i.'.msg.0', '.')
			];
			//--
		} //end for
		//--
		return (array) $arr;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function isTextFileByMimeType(string $mimetype) {
		//--
		$out = false;
		//--
		if(strpos((string)$mimetype, 'text/') === 0) {
			$out = true;
		} elseif(\in_array((string)$mimetype, [
			'message/rfc822',
			'application/x-php',
			'application/javascript',
			'application/json',
			'application/xml',
			'application/pgp-signature',
			'image/svg+xml',
		])) {
			$out = true;
		} //end if else
		//--
		return (bool) $out;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function isImageFileByMimeType(string $mimetype) {
		//--
		if(\in_array((string)$mimetype, [
			'image/svg+xml',
			'image/png',
			'image/jpeg',
			'image/gif',
			'image/webp'
		])) {
			$out = true;
		} else {
			$out = false;
		} //end if else
		//--
		return (bool) $out;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function isSvgImageFileByMimeType(string $mimetype) {
		//--
		if((string)$mimetype == 'image/svg+xml') {
			$out = true;
		} else {
			$out = false;
		} //end if else
		//--
		return (bool) $out;
		//--
	} //END FUNCTION
	//============================================================


	//===== PRIVATES


	//============================================================ OK
	private static function validateCfgRepoEntry($repo_entry) {
		//--
		if(\Smart::array_size($repo_entry) <= 0) {
			return false;
		} //end if
		//--
		if((string)\trim((string)$repo_entry['url']) == '') { // need at least the repo URL key, it is mandatory
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	private static function execSvnCmd($what, $repo, $path, $auth_user, $auh_pass, $format, $options=[]) {
		//--
		$cmd = (string) self::buildSvnCmd($what, $repo, $path, $auth_user, $auh_pass, $options);
		if((string)$cmd == '') {
			return array();
		} //end if
		//--
		$exearr = (array) \SmartUtils::run_proc_cmd((string)$cmd, null, (string)self::$svn_cache_dir); // avoid proc open in web root !!
		if(($exearr['exitcode'] !== 0) OR ((string)$exearr['stderr'] != '')) {
			//-- Fix: not raise error here ! in some cases when path renames going backward to select diff, props or view if path renames will have an error ; make this a nice error and do not raise fatal
			if(\SmartEnvironment::ifDebug()) {
				\Smart::log_notice(__METHOD__.' #ERR# SVN Command:['.$cmd.'] Returned Some Errors ; ExitCode=['.$exearr['exitcode'].'] ; ErrorMsg: '.$exearr['stderr']);
			} //end if
			if(!\headers_sent()) {
				\http_response_code(400);
			} //end if
			die((string)\SmartComponents::http_message_400_badrequest('Message: `'.$exearr['stderr'].'`', '<h6 style="color:#333333;">ExitCode:&nbsp;['.\Smart::escape_html($exearr['exitcode']).']</h6>'));
			//-- #fix
		} //end if
		$out = (string) $exearr['stdout']; // do no trim here ; only xml should be trimmed and will be done below
		$exearr = array(); // free mem
		//--
		switch((string)$format) {
			case 'xml-arr':
				$out = (string) \trim((string)$out);
				if((string)$out == '') {
					\Smart::raise_error( // should be a fatal error or 404 ??
						__METHOD__.' #ERR# SVN Command:['.$cmd.'] Returned Empty Output ...',
						'ERR: Errors when running command' // msg to display
					);
				} //end if
				$arr = (array) (new \SmartXmlParser('extended'))->transform((string)$out);
				if(\Smart::array_size($arr) <= 0) {
					\Smart::raise_error(
						__METHOD__.' #ERR# SVN Command:['.$cmd.'] Returned Invalid Output:['."\n".$out."\n".']',
						'ERR: Errors when running command' // msg to display
					);
				} //end if
				return (array) $arr;
				break;
			case 'string':
				return (string) $out; // do no trim here to avoid alter download files ; only xml should be trimmed and this is not the case
				break;
			case 'exit-code':
				return true; // return TRUE as the real exit code was checked above
			default:
				if(\Smart::array_size($arr) <= 0) {
					\Smart::raise_error(
						__METHOD__.' #ERR# SVN Command:['.$cmd.'] Invalid command Output Type selected:['.$format.']',
						'ERR: Invalid command Output Type selected' // msg to display
					);
				} //end if
				return null;
		} //end switch
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	private static function buildSvnCmd($what, $repo, $path, $auth_user, $auh_pass, $options) {
		//-- fix for PHP8
		if(!\is_array($options)) {
			$options = array();
		} //end if
		$init_keys = [
			'start-rev',
			'num-revs',
			'rev-old',
			'rev-new',
			'rev',
			'prop',
			'export-dir',
		];
		$options = (array) \Smart::array_init_keys($options, $init_keys);
		//-- #end fix
		$repo = (string) \trim((string)$repo);
		if(\strpos((string)$repo, '://') === false) {
			$repo = 'file:///-INVALID-REPOSITORY-/--invalid-repo-name-err-svn--';
		} //end if
		if((string)\substr((string)$repo, -1, 1) == '/') {
			$repo = (string) \substr((string)$repo, 0, -1);
		} //end if
		//--
		$path = (string) \trim((string)$path);
		if((string)\substr((string)$path, 0, 1) != '/') {
			$path = (string) '/'.$path;
		} //end if
		//--
		$cmdsvn = (string) \trim((string)\Smart::get_from_config('svn.cmd'));
		if((string)$cmdsvn == '') {
			return '';
		} //end if
		//--
		$base_cmd = (string) self::escapeCmdExe((string)$cmdsvn).' --non-interactive';
		$base_cmd .= ' --config-dir '.self::escapeCmdArg('svn-cfg'); // this path is relative to the proc jailroot
		if((string)$auth_user !== '') {
			$base_cmd .= ' --no-auth-cache --username '.self::escapeCmdArg((string)$auth_user);
			if((string)$auh_pass !== '') {
				$base_cmd .= ' --password '.self::escapeCmdArg((string)$auh_pass);
			} //end if
		} //end if
		//--
		$cmd = '';
		switch((string)$what) {
			case 'info':
				$cmd = (string) $base_cmd.' --xml info '.self::escapeCmdArg((string)$repo);
				break;
			case 'prev-info': // get real path (maybe changed from previous revisions)
				$cmd = (string) $base_cmd.' --xml info --revision '.self::escapeCmdArg((string)$options['rev-old']).' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$options['rev-new']);
				break;
			case 'list':
				if((string)\trim((string)$options['rev']) != '') {
					$rev = (string) $options['rev'];
				} else {
					$rev = 'HEAD';
				} //end if else
				$cmd = (string) $base_cmd.' --xml ls --revision '.self::escapeCmdArg((string)$rev).' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$rev);
				break;
			case 'proplist':
				if((string)\trim((string)$options['rev']) != '') {
					$rev = (string) $options['rev'];
				} else {
					$rev = 'HEAD';
				} //end if else
				$cmd = (string) $base_cmd.' --xml proplist --revision '.self::escapeCmdArg((string)$rev).' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$rev);
				break;
			case 'propget':
				if((string)\trim((string)$options['rev']) != '') {
					$rev = (string) $options['rev'];
				} else {
					$rev = 'HEAD';
				} //end if else
				if((string)\trim((string)$options['prop']) == '') {
					\Smart::raise_error(
						__METHOD__.' #ERR# SVN PropGet: Empty or Invalid Property',
						'ERR: Invalid Property for SVN PropGet' // msg to display
					);
				} //end if
				$cmd = (string) $base_cmd.' --xml propget '.self::escapeCmdArg((string)$options['prop']).' --revision '.self::escapeCmdArg((string)$rev).' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$rev);
				break;
			case 'cat':
				if((string)\trim((string)$options['rev']) != '') {
					$rev = (string) $options['rev'];
				} else {
					$rev = 'HEAD';
				} //end if else
				$cmd = (string) $base_cmd.' cat --revision '.self::escapeCmdArg((string)$rev).' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$rev);
				break;
			case 'compare': // compare changes between revisions
				if((string)\trim((string)$options['rev']) != '') {
					$rev = (string) $options['rev'];
				} else {
					$rev = 'HEAD';
				} //end if else
				$cmd = (string) $base_cmd.' --xml log --verbose --revision '.self::escapeCmdArg((string)$options['rev']).':'.self::escapeCmdArg((string)$options['rev']).' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$options['rev']);
				break;
			case 'diff': // show diff on a text file
				$cmd = (string) $base_cmd.' diff --revision '.self::escapeCmdArg((string)$options['rev-old']).':'.self::escapeCmdArg((string)$options['rev-new']).' '.self::escapeCmdArg((string)$repo.$path.'@');
				break;
			case 'export':
				if((string)$options['export-dir'] == '') {
					\Smart::raise_error(
						__METHOD__.' #ERR# SVN Export: Empty or Invalid Export Dir:['.$options['export-dir'].']',
						'ERR: Invalid Dir for SVN Export' // msg to display
					);
				} //end if
				if((string)\trim((string)$options['rev']) != '') {
					$rev = (string) $options['rev'];
				} else {
					$rev = 'HEAD';
				} //end if else
				$cmd = (string) $base_cmd.' export --revision '.self::escapeCmdArg((string)$rev).' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$rev).' '.self::escapeCmdArg((string)$options['export-dir'].'@');
				break;
			case 'get-revs':
				$start_rev = (string) $options['start-rev'];
				$num_revs = (int) $options['num-revs'];
				if((string)\trim((string)$options['rev']) != '') {
					$rev = (string) $options['rev'];
				} else { // $rev is required for removed or changed paths
					$rev = 'HEAD';
				} //end if else
				if($num_revs <= 0) {
					$num_revs = 1;
				} //end if
				$cmd = (string) $base_cmd.' --xml log --revision '.self::escapeCmdArg($start_rev).':0 --limit '.(int)$num_revs.' '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$rev);
				break;
			case 'get-head':
				$cmd = (string) $base_cmd.' --xml info '.self::escapeCmdArg((string)$repo.$path.'@'.(string)$rev);
				break;
			default:
				// nothing
		} //end switch
		//--
		return (string) $cmd;
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	private static function buildArchive($dir, $archname, $format) {
		//--
		switch((string)$format) { // {{{SYNC-SVN-EXP-ARCHS}}}
			case '7z':
			case 'zip':
			case 'tar.gz':
				// OK
				break;
			default:
				// NOT OK
				\Smart::log_warning((string)__METHOD__.' #ERR# SVN Export: Invalid Archive Type:['.$format.']');
				return '';
		} //end switch
		//--
		$dir = (string) \Smart::safe_pathname((string)\SmartFileSysUtils::addPathTrailingSlash((string)$dir));
		\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$dir);
		$arch_dir = (string) \SmartFileSysUtils::addPathTrailingSlash((string)\Smart::safe_filename((string)$archname)); // archive name dir
		\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$arch_dir);
		$arch_file = (string) \Smart::safe_filename((string)$archname.'.'.$format); // archive name
		\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$arch_file);
		$fpatharch = (string) \Smart::safe_pathname((string)self::$svn_cache_dir.$dir.$arch_file);
		\SmartFileSysUtils::raiseErrorIfUnsafePath((string)$fpatharch);
		//--
		$cmdarch = '';
		switch((string)$format) { // {{{SYNC-SVN-EXP-ARCHS}}}
			case '7z':
				$cmdarch = (string) \trim((string)\Smart::get_from_config('svn.7za'));
				break;
			case 'zip':
				$cmdarch = (string) \trim((string)\Smart::get_from_config('svn.7za'));
				break;
			case 'tar.gz':
				$cmdarch = (string) \trim((string)\Smart::get_from_config('svn.tar'));
				break;
			default:
				$cmdarch = '';
		} //end switch
		if((string)$cmdarch == '') {
			\Smart::raise_error(
				__METHOD__.' #ERR# Empty Archive Command for Archive Type: '.$format,
				'ERR: Empty Archive Command' // msg to display
			);
		} //end if
		//--
		$cmd = '';
		switch((string)$format) { // {{{SYNC-SVN-EXP-ARCHS}}}
			case '7z':
				$cmd = (string) self::escapeCmdExe((string)$cmdarch).' a -ssc -t7z -m0=lzma '.self::escapeCmdArg((string)$arch_file).' '.self::escapeCmdArg((string)$arch_dir);
				break;
			case 'zip':
				$cmd = (string) self::escapeCmdExe((string)$cmdarch).' a -tzip '.self::escapeCmdArg((string)$arch_file).' '.self::escapeCmdArg((string)$arch_dir);
				break;
			case 'tar.gz':
				$cmd = (string) self::escapeCmdExe((string)$cmdarch).' -czf '.self::escapeCmdArg((string)$arch_file).' '.self::escapeCmdArg((string)$arch_dir);
				break;
			default:
				$cmd = '';
		} //end switch
		if((string)$cmd == '') {
			\Smart::raise_error(
				__METHOD__.' #ERR# Empty Archive Command Switches for Archive Type: '.$format,
				'ERR: Empty Archive Command Switches' // msg to display
			);
		} //end if
		//--
		$exearr = (array) \SmartUtils::run_proc_cmd((string)$cmd, null, (string)self::$svn_cache_dir.$dir);
		if(($exearr['exitcode'] !== 0) OR ((string)$exearr['stderr'] != '')) {
			\Smart::raise_error(
				__METHOD__.' #ERR# Archive Command:['.$cmd.'] Returned Some Errors ; ExitCode=['.$exearr['exitcode'].'] ; ErrorMsg: '.$exearr['stderr'],
				'ERR: Errors when running archive command' // msg to display
			);
		} //end if
		$exearr = array(); // free mem
		//--
		if(\SmartFileSystem::is_type_file((string)$fpatharch)) {
			if(\filesize((string)$fpatharch) > 0) {
				//--
				$testcmd = '';
				$testtxt = '';
				switch((string)$format) { // {{{SYNC-SVN-EXP-ARCHS}}}
					case '7z':
					case 'zip':
						$testcmd = (string) self::escapeCmdExe((string)$cmdarch).' t '.self::escapeCmdArg((string)$arch_file);
						$testtxt = "\n".'Everything is Ok'."\n";
						break;
					case 'tar.gz':
						$testcmd = (string) self::escapeCmdExe((string)$cmdarch).' -tzf '.self::escapeCmdArg((string)$arch_file);
						$testtxt = '';
						break;
					default:
						$testcmd = '';
						$testtxt = '';
				} //end switch
				if((string)$testcmd == '') {
					\Smart::raise_error(
						__METHOD__.' #ERR# Empty Archive Test Command Switches for Archive Type: '.$format,
						'ERR: Empty Archive Test Command Switches' // msg to display
					);
				} //end if
				//--
				$exearr = (array) \SmartUtils::run_proc_cmd((string)$testcmd, null, (string)self::$svn_cache_dir.$dir);
				if(($exearr['exitcode'] !== 0) OR ((string)$exearr['stderr'] != '') OR (((string)$testtxt != '') AND (\stripos((string)$exearr['stdout'], (string)$testtxt) === false))) {
					\Smart::raise_error(
						__METHOD__.' #ERR# Archive Test Command:['.$testcmd.'] Returned Some Errors ; ExitCode=['.$exearr['exitcode'].'] ; ErrorMsg: '.$exearr['stderr'].' ; StdOut: '.$exearr['stdout'],
						'ERR: Errors when running archive test command' // msg to display
					);
				} //end if
				$exearr = array(); // free mem
				//--
				return (string) $fpatharch;
				//--
			} //end if
		} //end if
		//--
		return '';
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	private static function fixTrunkPath(string $fixed_path, array $rdata) {
		//--
		$path = '';
		if(\array_key_exists('path', (array)$rdata)) {
			$path = (string) $rdata['path'];
			$path = (string) \trim((string)$path);
			$path = (string) \trim((string)$path, '/');
			$path = (string) \trim((string)$path);
		} //end if
		//--
		if((string)$path != '') {
			$path = '/'.$path;
			if((string)$fixed_path == (string)$path) {
				$fixed_path = '';
			} elseif(strpos((string)$fixed_path, (string)$path.'/') === 0) {
				$fixed_path = (string) substr((string)$fixed_path, (int)strlen((string)$path));
			} //end if
		} //end if
		//--
		if((string)trim((string)$fixed_path) == '') {
			$fixed_path = '/';
		} //end if
		//--
		return (string) $fixed_path;
		//--
	} //END IF
	//============================================================


	//============================================================ OK
	private static function escapeCmdExe($cmd) {
		//--
		$cmd = (string) \trim((string)\Smart::normalize_spaces((string)$cmd));
		//--
		return (string) \escapeshellcmd((string)$cmd);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	private static function escapeCmdArg($arg) {
		//--
		$arg = (string) \trim((string)\Smart::normalize_spaces((string)$arg));
		//--
		return (string) \escapeshellarg((string)$arg);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================ OK
	private static function rmDirRecursive($dir_name) {
		//--
		$dir_name = (string) \rtrim((string)$dir_name, '/'); // remove any trailing slashes
		//--
		if(!\SmartFileSysUtils::checkIfSafePath((string)$dir_name)) {
			\Smart::log_warning(__METHOD__.'() // FAILED to delete a directory with unsafe path (1): '.$dir_name);
			return false;
		} //end if
		//--
		\clearstatcache(true, (string)$dir_name);
		//--
		if(\is_link((string)$dir_name)) {
			return (bool) @\unlink((string)$dir_name);
		} //end if
		//--
		$dir_name = (string) $dir_name.'/'; // add trailing slash (previous trailing slashes were removed above)
		//--
		if(!\SmartFileSysUtils::checkIfSafePath((string)$dir_name)) {
			\Smart::log_warning(__METHOD__.'() // FAILED to delete a directory with unsafe path (2): '.$dir_name);
			return false;
		} //end if
		//--
		$files = (array) @\scandir((string)$dir_name);
		//--
		foreach($files as $k => $file) {
			//--
			if(((string)$file != '') AND ((string)$file != '.') AND ((string)$file != '..')) {
				//--
				$path = (string) $dir_name.$file;
				//-- minimal test: valid, backward, absolute
				if(
					(\strpos((string)$path, '://') !== false) OR // valid
					((\strpos((string)$path, '/../') !== false) OR (\strpos((string)$path, '/./') !== false) OR (\strpos((string)$path, '/..') !== false) OR (\strpos((string)$path, '../') !== false)) OR // backward
					((string)\substr((string)\trim((string)$path), 0, 1) == '/') // absolute
				) {
					\Smart::log_warning(__METHOD__.'() // FAILED to delete an unsafe path: '.$path);
					return false;
				} //end if
				//--
				\clearstatcache(true, (string)$path);
				if((\is_dir((string)$path)) AND !\is_link($path)) {
					self::rmDirRecursive((string)$path); // if dir but not link
				} else {
					@\unlink((string)$path); // if file or link
				} //end if else
				//--
			} //end if
			//--
		} //end foreach
		//--
		\clearstatcache(true, (string)$dir_name);
		if(\rmdir((string)$dir_name)) {
			if((\file_exists((string)$dir_name)) OR (\is_link((string)$dir_name))) { // {{{SYNC-SF-PATH-EXISTS}}}
				\Smart::log_warning(__METHOD__.'() // FAILED to delete a directory: '.$dir_name);
				return false; // dir still exists after deletion ... why !?
			} else {
				return true; // OK
			} //end if else
		} else {
			return false; // not deleted
		} //end if else
		//--
	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
