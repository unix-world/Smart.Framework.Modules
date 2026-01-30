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
		//--
		if((string)$root == '') {
			throw new \Exception(__METHOD__.' # Root path is empty');
			return;
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$root) !== 1) {
			throw new \Exception(__METHOD__.' # Root path is invalid: `'.$root.'`');
			return;
		} //end if
		//--
		if(\SmartFileSysUtils::isDir((string)$root, true) !== true) { // use caching
			throw new \Exception(__METHOD__.' # Root path must be a directory: `'.$root.'`');
			return;
		} //end if
		//--
		$this->root = (string) $root;
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
			throw new \Exception(__METHOD__.' # TPL File Name is Empty');
			return '';
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$templateFile) !== 1) {
			throw new \Exception(__METHOD__.' # TPL File Name is Invalid (1): `'.$templateFile.'`');
			return '';
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$templateFile)) { // {{{SYNC-CHK-SAFE-PATH}}} ; can be a relative path if sub-tpl not necessary just a filename
			throw new \Exception(__METHOD__.' # TPL File Name is Invalid (2): `'.$templateFile.'`');
			return '';
		} //end if
		//--
		if((string)$templatePath == '') {
			throw new \Exception(__METHOD__.' # TPL Path is Empty');
			return '';
		} //end if
		if(\SmartFileSysUtils::checkIfSafePath((string)$templatePath) !== 1) {
			throw new \Exception(__METHOD__.' # TPL Path is Invalid (1): `'.$templatePath.'`');
			return '';
		} //end if
		if(((string)$templatePath == '') OR \in_array((string)$templatePath, (array)\TwistTPL\Twist::INVALID_PATH)) { // {{{SYNC-TWIST-CHECK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # TPL Path is Invalid (2): `'.$templatePath.'`');
			return '';
		} //end if
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$templatePath)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # TPL Path is Invalid (3): `'.$templatePath.'`');
			return '';
		} //end if
		if(\SmartFileSysUtils::isDir((string)$templatePath, true) !== true) { // use caching
			throw new \Exception(__METHOD__.' # TPL Path must be a Dir: `'.$templatePath.'`');
			return '';
		} //end if
		//--
		if(!\preg_match((string)\TwistTPL\Twist::REGEX_SAFE_PATH_NAME, (string)$templatePath.$templateFile)) { // {{{SYNC-CHK-SAFE-PATH}}}
			throw new \Exception(__METHOD__.' # TPL File Path is Invalid: `'.$templatePath.$templateFile.'`');
			return '';
		} //end if
		if(\strpos((string)$templatePath.$templateFile, (string)$this->root) !== 0) {
			throw new \Exception(__METHOD__.' # TPL File Path must be under the TPL root path: `'.$templatePath.$templateFile.'` vs. `'.$this->root.'`');
			return '';
		} //end if
		if(\SmartFileSysUtils::isFile((string)$templatePath.$templateFile, true) !== true) { // use caching
			throw new \Exception(__METHOD__.' # TPL TPL File Path must be a File: `'.$templatePath.$templateFile.'`');
			return '';
		} //end if
		if(\SmartFileSysUtils::pathIsReadable((string)$templatePath.$templateFile) !== true) { // use caching
			throw new \Exception(__METHOD__.' # TPL TPL File Path is Not Readable: `'.$templatePath.$templateFile.'`');
			return '';
		} //end if
		//--
		$tpl = (string) \SmartFileSysUtils::readStaticFile((string)$templatePath.$templateFile, (int)\Smart::SIZE_BYTES_16M, true); // {{{SYNC-TPL-MAX-SIZE}}} ; max read size enforced, don't read if oversized
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
