<?php

// fixes by unixman

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
 * This class represents the entire template document.
 */
final class Document extends \TwistTPL\AbstractBlock {


	private $twistVersion = null; 	// the Twist-TPL version ; null | string
	private $tplIsRoot = null; 		// set to TRUE for main TPL, set to FALSE for includes ; null | true | false
	private $tplFile = null; 		// the path of the tpl file being rendered ; null | '' | 'some-path/to/tpl'


	/**
	 * Constructor.
	 *
	 * @param array $tokens
	 * @param AbstractInterfaceFileSystem $fileSystem
	 */
	public function __construct(array &$tokens, ?\TwistTPL\AbstractInterfaceFileSystem $fileSystem=null) { // unixman: added nullable type (PHP 8.4 fix)
		//--
		$this->fileSystem = $fileSystem;
		//--
		$this->parse($tokens);
		//--
	} //END FUNCTION


	/**
	 * Get Document MetaData
	 *
	 * @param string $what : twistVersion ; tplIsRoot ; tplFile
	 *
	 * @return mixed
	 */
	public function getMetaData(string $what) {
		//--
		switch((string)$what) {
			case 'twistVersion':
				return $this->twistVersion;
				break;
			case 'tplIsRoot':
				return $this->tplIsRoot;
				break;
			case 'tplFile':
				return $this->tplFile;
				break;
		} //end switch
		//--
		return null;
		//--
	} //END FUNCTION


	/**
	 * Allow set One-Time the Twist Version used to validate back from cache
	 *
	 * @param string $version
	 *
	 * @return Boolean
	 */
	public function setTwistVersion(string $version) : bool {
		//--
		if($this->twistVersion !== null) {
			return false; // dissalow set twice ; this version must stamp the exported Document !
		} //end if
		//--
		$this->twistVersion = (string) $version;
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Allow set One-Time the TPL File Root status (if main or partial)
	 *
	 * @param string $tplPath
	 *
	 * @return Boolean
	 */
	public function setTplIsRoot(bool $isRoot) : bool {
		//--
		if($this->tplIsRoot !== null) {
			return false; // dissalow set twice ; this variable must stamp the exported Document !
		} //end if
		//--
		$this->tplIsRoot = (bool) $isRoot;
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Allow set One-Time the TPL File Path
	 *
	 * @param string $tplPath
	 *
	 * @return Boolean
	 */
	public function setTplFile(string $tplPath) : bool {
		//--
		if($this->tplFile !== null) {
			return false; // dissalow set twice ; each rendered file should use a separate Document !
		} //end if
		//--
		$this->tplFile = (string) $tplPath;
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Check for cached includes; if there are - do not use cache
	 *
	 * @see \TwistTPL\Tag\TagInclude::hasIncludes()
	 * @return bool if need to discard cache
	 */
	public function hasIncludes() : bool {
		//--
		if(\is_array($this->nodelist)) {
			foreach($this->nodelist as $kk => $token) {
				if(($token instanceof \TwistTPL\Tag\TagInclude) && $token->hasIncludes()) { // check any of the tokens for includes
					return true;
				} //end if
			} //end foreach
		} //end if
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * There isn't a real delimiter
	 *
	 * @return string
	 */
	protected function blockDelimiter() : string {
		//--
		return '';
		//--
	} //END FUNCTION


	/**
	 * Document blocks don't need to be terminated since they are not actually opened
	 */
	protected function assertMissingDelimitation() : void {
		//--
		return;
		//--
	} //END FUNCTION


} //END CLASS

// #end
