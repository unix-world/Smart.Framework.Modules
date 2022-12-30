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
		if(!isset($options['cache:path'])) {
			throw new \Exception(__METHOD__.' # Cache Path was not set');
			return;
		} //end if
		//--
		$path = (string) \trim((string)$options['cache:path']);
		//--
		if(((string)$path == '') OR \in_array((string)$path, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache Path is Invalid (1): `'.$path.'`');
			return;
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$path)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache Path is Invalid (2): `'.$path.'`');
			return;
		} //end if
		//--
		if(!\is_dir($path)) {
			throw new \Exception(__METHOD__.' # Cache Path is not a directory: `'.$path.'`');
			return;
		} //end if
		if(!\is_writable($path)) {
			throw new \Exception(__METHOD__.' # Cache Path is not writable: `'.$path.'`');
			return;
		} //end if
		//--
		$this->path = (string) $path;
		//--
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	public function exists(string $key) : bool {
		//--
		$cacheFile = (string) $this->path.$this->prefix.$key;
		//--
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$cacheFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache File Name is Invalid: `'.$cacheFile.'`');
			return false;
		} //end if
		//--
		if((!\is_file($cacheFile)) || (!\is_readable($cacheFile)) || ((int)((int)\filemtime($cacheFile) + (int)$this->expire) < (int)\time())) {
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
		$cacheFile = (string) $this->path.$this->prefix.$key;
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$cacheFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache File Name is Invalid: `'.$cacheFile.'`');
			return false;
		} //end if
		//--
		if(!$this->exists($key)) {
			return null;
		} //end if
		//--
		$value = (string) \file_get_contents((string)$cacheFile, false);
		if((string)$value == '') {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Read the Cache File: `'.$cacheFile.'`', \E_USER_WARNING);
			return null;
		} //end if
		//--
		$value = \unserialize((string)$value); // obj
		if(empty($value)) {
			\trigger_error('#'.__METHOD__.'.WARNING# Empty Data', \E_USER_WARNING);
			return null;
		} //end if
		//--
		if(!\is_array($value)) {
			\trigger_error('#'.__METHOD__.'.WARNING# Invalid Data', \E_USER_WARNING);
			return null;
		} //end if
		if((!isset($value['tpl-cksum'])) OR (!isset($value['tpl-obj']))) {
			\trigger_error('#'.__METHOD__.'.WARNING# Invalid Data Structure', \E_USER_WARNING);
			return null;
		} //end if
		if((!\is_string($value['tpl-cksum'])) OR (!\is_string($value['tpl-obj']))) {
			\trigger_error('#'.__METHOD__.'.WARNING# Invalid Data Type', \E_USER_WARNING);
			return null;
		} //end if
		if((string)$value['tpl-cksum'] != (string)\sha1((string)$key."\n".$value['tpl-obj']."\n".\strrev((string)$key))) {
			\trigger_error('#'.__METHOD__.'.WARNING# Checksum does not match', \E_USER_WARNING);
			return null;
		} //end if
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
		$cacheFile = (string) $this->path.$this->prefix.$key;
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$cacheFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Cache File Name is Invalid: `'.$cacheFile.'`');
			return false;
		} //end if
		//--
		if($value === null) {
			return false;
		} //end if
		//--
		$value = (string) \serialize($value); // this is mandatory
		if((string)\trim((string)$value) == '') {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Serialize the OBJ Value ... (empty)', \E_USER_WARNING);
			return false;
		} //end if
		//--
		$value = (string) \serialize([ 'tpl-cksum' => (string)\sha1((string)$key."\n".$value."\n".\strrev((string)$key)), 'tpl-obj' => (string)$value ]);
		if((string)\trim((string)$value) == '') {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Serialize the ARR Value ... (empty)', \E_USER_WARNING);
			return false;
		} //end if
		//--
		$bytes = \file_put_contents((string)$cacheFile, (string)$value);
		//--
		$this->gc();
		//--
		$ok = (bool) ($bytes !== false);
		//--
		if(!$ok) {
			\trigger_error('#'.__METHOD__.'.WARNING# Failed to Write Cache File: `'.$cacheFile.'`', \E_USER_WARNING);
		} //end if
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	public function flush(bool $expiredOnly=false) : void {
		// TODO: delete only cache files not all ... there might be .htaccess or index.html !
		foreach(\glob($this->path.$this->prefix.'*') as $file) {
			if($expiredOnly) {
				if((int)((int)\filemtime($file) + (int)$this->expire) < (int)\time()) {
					\unlink($file);
				} //end if
			} else {
				\unlink($file);
			} //end if else
		} //end foreach
	} //END FUNCTION


	/**
	 * {@inheritdoc}
	 */
	protected function gc() {
		$this->flush(true);
	}

} //END FUNCTION

// #end
