<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL\FileSystem;

use \TwistTPL\Regexp;
use \TwistTPL\Twist;

/**
 * This implements an abstract file system which retrieves template files named in a manner similar to Rails partials,
 * ie. with the template name prefixed with an underscore. The extension ".twist" is also added.
 *
 * For security reasons, template paths are only allowed to contain letters, numbers, and underscore.
 */
class LocalFileSystem implements \TwistTPL\AbstractInterfaceFileSystem {

	/**
	 * The root path
	 *
	 * @var string
	 */
	private $root = '';


	/**
	 * Constructor
	 *
	 * @param string $root The root path for templates
	 * @throws \Exception
	 */
	public function __construct(string $root) {
		//-- since root path can only be set from constructor, we check it once right here
		$realRoot = (string) $root;
		$realRoot = (string) \trim((string)$realRoot);
		$realRoot = (string) \trim((string)$realRoot, '/');
		$realRoot = (string) \trim((string)$realRoot);
		//--
		if(((string)$realRoot == '') OR \in_array((string)$realRoot, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Root path could not be found or is invalid: `'.$realRoot.'`');
			return;
		} //end if
		//--
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$realRoot)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # Root path Path is Invalid: `'.$realRoot.'`');
			return;
		} //end if
		//--
		if(!\is_dir((string)$realRoot)) {
			throw new \Exception(__METHOD__.' # Root path must be a directory: `'.$realRoot.'`');
			return;
		} //end if
		//--
		$this->root = (string) $realRoot;
		//--
	} //END FUNCTION


	/**
	 * Retrieve a template file
	 *
	 * @param string $templatePath
	 *
	 * @return string template content
	 */
	public function readTemplateFile(string $templateFile, string $templatePath) : string {
		//--
		$templateFile = (string) \trim((string)$templateFile);
		$templatePath = (string) \trim((string)$templatePath);
		//--
		if((string)$templateFile == '') {
			throw new \Exception(__METHOD__.' # TPL File Name is Invalid (1): `'.$templateFile.'`');
			return '';
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$templateFile)) { // {{{SYNC-CHK-SAFE-PATH}}} ; can be a relative path if sub-tpl not necessary just a filename
			throw new \Exception(__METHOD__.' # TPL File Name is Invalid (2): `'.$templateFile.'`');
			return '';
		} //end if
		//--
		if(((string)$templatePath == '') OR \in_array((string)$templatePath, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # TPL Path is Invalid (1): `'.$templatePath.'`');
			return '';
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$templatePath)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # TPL Path is Invalid (2): `'.$templatePath.'`');
			return '';
		} //end if
		//--
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$templatePath.$templateFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # TPL File Path is Invalid: `'.$templatePath.$templateFile.'`');
			return '';
		} //end if
		//--
		if(\strpos((string)$templatePath.$templateFile, (string)$this->root) !== 0) {
			throw new \Exception(__METHOD__.' # TPL Path must be under the TPL root path: `'.$this->root.'`');
			return '';
		} //end if
		if(\strpos((string)$templatePath.$templateFile, '..') !== false) {
			throw new \Exception(__METHOD__.' # TPL Path cannot contain `..`');
			return '';
		} //end if
		//--
		if(!\is_file((string)$templatePath.$templateFile)) {
			throw new \Exception(__METHOD__.' # The TPL is not a file: `'.$templatePath.$templateFile.'`');
			return '';
		} //end if
		if(!\is_readable((string)$templatePath.$templateFile)) {
			throw new \Exception(__METHOD__.' # The TPL file is not readable: `'.$templatePath.$templateFile.'`');
			return '';
		} //end if
		//--
		$tpl = (string) \file_get_contents((string)$templatePath.$templateFile, false);
		if((string)$tpl == '') {
			throw new \Exception(__METHOD__.' # Failed to read the TPL path: `'.$templatePath.$templateFile.'`');
			return '';
		} //end if
		//--
		return (string) $tpl;
		//--
	} //END FUNCTION


} //END CLASS

// #end
