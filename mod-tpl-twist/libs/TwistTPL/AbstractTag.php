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
 * Base class for tags.
 */
abstract class AbstractTag {

	/**
	 * The markup for the tag
	 *
	 * @var string
	 */
	protected $markup = null;

	/**
	 * AbstractInterfaceFileSystem object is used to load included template files
	 *
	 * @var AbstractInterfaceFileSystem
	 */
	protected $fileSystem = null;

	/**
	 * Additional attributes
	 *
	 * @var array
	 */
	protected $attributes = [];


	/**
	 * Constructor.
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param AbstractInterfaceFileSystem $fileSystem
	 */
	public function __construct(?string $markup, array &$tokens, ?\TwistTPL\AbstractInterfaceFileSystem $fileSystem=null) {
		$this->markup = (string) $markup;
		$this->fileSystem = $fileSystem;
		$this->parse($tokens);
	} //END FUNCTION


	/**
	 * Parse the given tokens.
	 *
	 * @param array $tokens
	 */
	public function parse(array &$tokens) : void {
		// Do nothing by default
	} //END FUNCTION


	/**
	 * Render the tag with the given context.
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	abstract public function render(\TwistTPL\Context $context) : string;


	/**
	 * Extracts tag attributes from a markup string.
	 *
	 * @param string $markup
	 */
	protected function extractAttributes(?string $markup) : void {
		$this->attributes = array();
		$attributeRegexp = new \TwistTPL\Regexp(\TwistTPL\Twist::get('TAG_ATTRIBUTES'));
		$matches = $attributeRegexp->scan($markup);
		foreach($matches as $kk => $match) {
			$this->attributes[$match[0]] = $match[1] ?? null;
		} //end foreach
	} //END FUNCTION


	/**
	 * Returns the name of the tag.
	 *
	 * @return string
	 */
	protected function name() : string {
		return (string) \strtolower((string)\get_class($this));
	} //END FUNCTION


} //END CLASS

// #end
