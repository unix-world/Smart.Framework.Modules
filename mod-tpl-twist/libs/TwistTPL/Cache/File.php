<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL\Cache;

/**
 * Implements cache stored in files.
 */
final class File extends \TwistTPL\AbstractCache {

	/**
	 * Constructor.
	 *
	 * It checks the availability of cache directory.
	 *
	 * @param array $options
	 *
	 * @throws NotFoundException if Cachedir not exists.
	 */
	public function __construct(array $options=[]) {
		//--
		parent::__construct($options);
		//--
		if(!isset($options['root:path'])) {
			throw new \Exception(__METHOD__.' # Root Path was not set');
			return;
		} //end if
		//--
		if(!isset($options['cache:path'])) {
			throw new \Exception(__METHOD__.' # Cache Path was not set');
			return;
		} //end if
		//--
		$root = (string) \trim((string)$options['root:path']);
		$path = (string) \trim((string)$options['cache:path']);
		//--
		if(((string)$root == '') OR \in_array((string)$root, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Root Path is Invalid (1): `'.$root.'`');
			return;
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$root)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Root Path is Invalid (2): `'.$root.'`');
			return;
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$root) !== 1) {
			throw new \Exception(__METHOD__.' # Root Path is Unsafe: `'.$root.'`');
			return;
		} //end if
		if(\SmartFileSysUtils::isDir((string)$root, true) !== true) {
			throw new \Exception(__METHOD__.' # Root Path is not a directory or does not exists: `'.$root.'`');
			return;
		} //end if
		//--
		if(((string)$path == '') OR \in_array((string)$path, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache Path is Invalid (1): `'.$path.'`');
			return;
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$path)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache Path is Invalid (2): `'.$path.'`');
			return;
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$path) !== 1) {
			throw new \Exception(__METHOD__.' # Cache Path is Unsafe: `'.$path.'`');
			return;
		} //end if
		if(\SmartFileSysUtils::isDir((string)$path, true) !== true) {
			throw new \Exception(__METHOD__.' # Cache Path is not a directory or does not exists: `'.$path.'`');
			return;
		} //end if
		if(\SmartFileSysUtils::pathIsWritable((string)$path) !== true) {
			throw new \Exception(__METHOD__.' # Cache Path is not writable: `'.$path.'`');
			return;
		} //end if
		//--
		$this->root = (string) $root;
		$this->path = (string) $path;
		//--
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	public function exists(string $key) : bool {
		//--
		$cacheFile = (string) $this->path.$this->prefix.$key.'.json';
		//--
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$cacheFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache File Name is Invalid: `'.$cacheFile.'`');
			return false;
		} //end if
		//--
		if(\Smart::random_number(0, 1000) == 500) {
			$this->gc(); // run gc before read below, the mtime is verified too
		} //end if
		//--
		if(
			(\SmartFileSysUtils::isFile((string)$cacheFile, true) !== true) // use caching
			||
			(\SmartFileSysUtils::pathIsReadable((string)$cacheFile) !== true)
			||
			((int)((int)\SmartFileSysUtils::fileOrDirMTime((string)$cacheFile) + (int)$this->expire) < (int)\time())
		) {
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	public function read(string $key) : ?\TwistTPL\Document {
		//--
		$cacheFile = (string) $this->path.$this->prefix.$key.'.json';
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$cacheFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache File Name is Invalid: `'.$cacheFile.'`');
			return false;
		} //end if
		//--
		if(!$this->exists($key)) {
			return null;
		} //end if
		//--
		$value = (string) \SmartFileSysUtils::readStaticFile((string)$cacheFile, (int)\Smart::SIZE_BYTES_16M, true); // {{{SYNC-TPL-MAX-SIZE}}} ; max read size enforced, don't read if oversized
		if((string)$value == '') {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Read the Cache File: `'.$cacheFile.'`', \E_USER_WARNING);
			return null;
		} //end if
		//--
		$value = \json_decode((string)$value, true, 3+1, \JSON_BIGINT_AS_STRING); // array ; {{{SYNC-JSON-LEVELS}}} ; must be levels+1
		if(empty($value)) {
			\trigger_error('#'.__METHOD__.'.WARNING# Empty Data', \E_USER_WARNING);
			return null;
		} //end if
		if(!\is_array($value)) {
			\trigger_error('#'.__METHOD__.'.WARNING# Invalid Data Type', \E_USER_WARNING);
			return null;
		} //end if
		//--
		if((!isset($value['tpl-records'])) OR (!isset($value['tpl-key'])) OR (!isset($value['tpl-cksum'])) OR (!isset($value['tpl-obj'])) OR (!isset($value['tpl-type'])) OR (!isset($value['tpl-file'])) OR (!isset($value['tpl-version'])) OR (!isset($value['tpl-engine']))) {
			\trigger_error('#'.__METHOD__.'.WARNING# Invalid Data Structure', \E_USER_WARNING);
			return null;
		} //end if
		if((!\is_array($value['tpl-records'])) OR (!\is_string($value['tpl-key'])) OR (!\is_string($value['tpl-cksum'])) OR (!\is_string($value['tpl-obj'])) OR (!\is_string($value['tpl-type'])) OR (!\is_string($value['tpl-file'])) OR (!\is_string($value['tpl-version'])) OR (!\is_string($value['tpl-engine']))) {
			\trigger_error('#'.__METHOD__.'.WARNING# Invalid Data Structure Type', \E_USER_WARNING);
			return null;
		} //end if
		if((string)$value['tpl-key'] != (string)$key) {
			\trigger_error('#'.__METHOD__.'.WARNING# Key does not match', \E_USER_WARNING);
			return null;
		} //end if
		if((string)$value['tpl-version'] != (string)\TwistTPL\Twist::VERSION) {
			\trigger_error('#'.__METHOD__.'.WARNING# Version does not match', \E_USER_WARNING);
			return null;
		} //end if
		if((string)$value['tpl-engine'] != (string)\TwistTPL\Twist::NAME) {
			\trigger_error('#'.__METHOD__.'.WARNING# Engine does not match', \E_USER_WARNING);
			return null;
		} //end if
		$value['tpl-obj'] = (string) \base64_decode((string)$value['tpl-obj'], true); // strict
		if((string)$value['tpl-cksum'] != (string)\SmartHashCrypto::sha384((string)$this->root.\chr(0).$key.\chr(0).$value['tpl-engine'].':'.$value['tpl-version'].\chr(0).$value['tpl-file'].\chr(0).$value['tpl-type'].\chr(0).$value['tpl-obj'].\chr(0).\strrev((string)$key).\chr(0).\TwistTPL\Twist::securityKey(), true)) { // sha384-B64
			\trigger_error('#'.__METHOD__.'.WARNING# Checksum does not match', \E_USER_WARNING);
			return null;
		} //end if
		foreach($value['tpl-records'] as $key => $val) {
			if(is_array($val)) {
				\TwistTPL\Twist::setRenderedTplRecord((string)($val['name'] ?? null), (string)($val['type'] ?? null), (string)($val['hash'] ?? null));
			} //end if
		} //end foreach
		$value = \unserialize((string)$value['tpl-obj']); // obj
		//--
		if(!\is_object($value)) {
			\trigger_error('#'.__METHOD__.'.WARNING# Not an Object', \E_USER_WARNING);
			return null;
		} //end if
		if(!($value instanceof \TwistTPL\Document)) {
			\trigger_error('#'.__METHOD__.'.WARNING# Wrong Object Type', \E_USER_WARNING);
			return null;
		} //end if
		//--
		return (object) $value; // expects object
		//--
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	public function write(string $key, ?\TwistTPL\Document $value) : bool {
		//--
		$cacheFile = (string) $this->path.$this->prefix.$key.'.json';
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$cacheFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache File Name is Invalid: `'.$cacheFile.'`');
			return false;
		} //end if
		//--
		if($value === null) {
			return false;
		} //end if
		//--
		$tplFile    = (string) \strval($value->getMetaData('tplFile'));
		$tplType    = (string) (($value->getMetaData('tplIsRoot') === true) ? 'tpl' : 'sub-tpl');
		$tplVersion = (string) \strval($value->getMetaData('twistVersion'));
		//--
		$value = (string) \serialize($value); // this is mandatory
		if((string)\trim((string)$value) == '') {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Serialize the OBJ Value ... (empty)', \E_USER_WARNING);
			return false;
		} //end if
		//--
		$value = (string) \json_encode([
			'tpl-engine' 	=> (string) \TwistTPL\Twist::NAME,
			'tpl-version' 	=> (string) $tplVersion,
			'tpl-file' 		=> (string) $tplFile,
			'tpl-type' 		=> (string) $tplType,
			'tpl-obj' 		=> (string) \base64_encode((string)$value),
			'tpl-key' 		=> (string) $key,
			'tpl-cksum' 	=> (string) \SmartHashCrypto::sha384((string)$this->root.\chr(0).$key.\chr(0).\TwistTPL\Twist::NAME.':'.$tplVersion.\chr(0).$tplFile.\chr(0).$tplType.\chr(0).$value.\chr(0).\strrev((string)$key).\chr(0).\TwistTPL\Twist::securityKey(), true), // sha384-B64
			'tpl-records' 	=> (array)  \TwistTPL\Twist::getRenderedTplRecords(),
		], \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT | \JSON_INVALID_UTF8_SUBSTITUTE, 3); // {{{SYNC-JSON-LEVELS}}}
		if((string)\trim((string)$value) == '') {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Serialize the ARR Value ... (empty)', \E_USER_WARNING);
			return false;
		} //end if
		//--
		$ok = \SmartFileSysUtils::writeFile((string)$cacheFile, (string)$value); // don't skip if identical, need to update mtime
		//--
		$this->gc(); // run gc after write above, the mtime is updated
		//--
		if($ok !== true) {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Write Cache File: `'.$cacheFile.'`', \E_USER_WARNING);
		} //end if
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	public function flush(bool $expiredOnly=false) : void { // delete cache files or expired cache files
		//--
		$scanPath = (string) $this->path.$this->prefix;
		if(\SmartFileSysUtils::checkIfSafePath((string)$scanPath) !== 1) {
			\Smart::log_warning(__METHOD__.' # Scan Path is Unsafe: `'.$scanPath.'`');
		} //end if
		//--
		$arr = \glob((string)$scanPath.'*.json');
		if(!is_array($arr)) {
			return;
		} //end if
		if(\count($arr) <= 0) {
			return;
		} //end if
		//--
		foreach($arr as $kk => $file) {
			if(\SmartFileSysUtils::checkIfSafePath((string)$file) === 1) {
				if(\SmartFileSysUtils::isFile((string)$file, true) === true) { // use caching
					$allowDelete = true;
					if($expiredOnly === true) {
						$allowDelete = (bool) ((int)((int)\SmartFileSysUtils::fileOrDirMTime((string)$file) + (int)$this->expire) < (int)\time()); // only if expired
					} //end if
					if($allowDelete === true) {
						\SmartFileSysUtils::deleteFileOrDir((string)$file);
					//	\Smart::log_notice(__METHOD__.' # Deleted a cache File: `'.$file.'`');
					} //end if
				} //end if
			} else {
				\Smart::log_warning(__METHOD__.' # Unsafe cache File: `'.$file.'`');
			} //end if else
		} //end foreach
		//--
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	protected function gc() {
		//--
		$this->flush(true); // expired only
		//--
	} //END FUNCTION


} //END FUNCTION

// #end
