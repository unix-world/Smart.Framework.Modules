<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 * (c) 2018 unix-world.org
 */

namespace Latte\Loaders;

use Latte;

// contains fixes by unixman


/**
 * Template loader.
 */
class StringLoader implements Latte\ILoader {

	use Latte\Strict;

	/** @var array|null [name => content] */
	private $templates;


	public function __construct(array $templates = null) {
		//--
		$this->templates = $templates;
		//--
	} //END FUNCTION


	/**
	 * Returns template source code.
	 * @return string
	 */
	public function getContent($name) {
		//--
		if($this->templates === null) {
			return $name;
		} elseif(isset($this->templates[$name])) {
			return $this->templates[$name];
		} else {
			throw new \RuntimeException('Missing template '.$name);
			return '';
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * @return bool
	 */
	public function isExpired($name, $time) {
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Returns referred template name.
	 * @return string
	 */
	public function getReferredName($name, $referringName) {
		//--
		if($this->templates === null) {
			throw new \LogicException('Missing template '.$name);
		} //end if
		//--
		return (string) $name;
		//--
	} //END FUNCTION


	/**
	 * Returns unique identifier for caching.
	 * @return string
	 */
	public function getUniqueId($name) {
		//--
		return (string) $this->getContent($name);
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
