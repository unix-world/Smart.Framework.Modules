<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL;

/**
 * Base class for AbstractCache.
 */
abstract class AbstractCache {

	/** @var int */
	protected $expire = 3600;
	/** @var string */
	protected $prefix = 'TwistTPL_';
	/** @var string  */
	protected $path;

	/**
	 * @param array $options
	 */
	public function __construct(array $options=[]) {
		if(isset($options['cache_expire'])) {
			$this->expire = $options['cache_expire'];
		}
		if(isset($options['cache_prefix'])) {
			$this->prefix = $options['cache_prefix'];
		}
	}

	/**
	 * Check if specified key exists in cache.
	 *
	 * @param string $key a unique key identifying the cached value
	 *
	 * @return boolean true if the key is in cache, false otherwise
	 */
	abstract public function exists(string $key) : bool;

	/**
	 * Retrieves a value from cache with a specified key.
	 *
	 * @param string $key a unique key identifying the cached value
	 *
	 * @return mixed|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	abstract public function read(string $key) : ?\TwistTPL\Document;

	/**
	 * Stores a value identified by a key in cache.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 *
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	abstract public function write(string $key, \TwistTPL\Document $value) : bool;

	/**
	 * Deletes all values from cache.
	 *
	 * @param bool $expiredOnly
	 *
	 * @return boolean whether the flush operation was successful.
	 */
	abstract public function flush(bool $expiredOnly=false) : void;

} //END CLASS

// #end
