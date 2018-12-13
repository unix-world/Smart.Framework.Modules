<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 * (c) 2018 unix-world.org
 */

// contains fixes by unixman

namespace Latte\Loaders;

use Latte;


/**
 * Template loader.
 */
class FileLoader implements Latte\ILoader {

	use Latte\Strict;

	/** @var string|null */
//	private $baseDir; // fix by unixman


//	public function __construct($baseDir = null) {
	public function __construct() { // dissalow base dir
//		$this->baseDir = $baseDir ? $this->normalizePath("$baseDir/") : null; // fix by unixman
	} //END FUNCTION


	/**
	 * Returns template source code.
	 * @return string
	 */
	public function getContent($fileName) {
		//-- fixes by unixman
	//	$file = $this->baseDir . $fileName;
		$file = (string) $fileName;
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \RuntimeException('Unsafe Template FilePath (1): '.$file);
			return '';
		} //end if
		//--
	//	if ($this->baseDir && !Latte\Helpers::startsWith($this->normalizePath($file), $this->baseDir)) {
	//		throw new \RuntimeException("Template '$file' is not within the allowed path '$this->baseDir'.");
	//	} elseif (!is_file($file)) {
		if(!\SmartFileSystem::is_type_file($file)) {
			throw new \RuntimeException('Missing template file: '.$file);
			return '';
		} elseif($this->isExpired($fileName, time())) {
			if(@touch($file) === false) {
			//	trigger_error('File\'s modification time is in the future. Cannot update it: '.Helpers::errGetLast('message'), E_USER_WARNING);
				\Smart::log_warning('File\'s modification time is in the future. Cannot update the file: '.$file.' # ErrorMessage: '.Helpers::errGetLast('message'));
			} //end if
		} //end if else
	//	return file_get_contents($file);
		return (string) \SmartFileSystem::read($file);
		// #fix
	} //END FUNCTION


	/**
	 * @return bool
	 */
	public function isExpired($file, $time) {
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($file)) {
			throw new \RuntimeException('Unsafe Template FilePath (2): '.$file);
			return true;
		} //end if
		if(!\SmartFileSystem::path_exists($file)) {
			return true;
		} elseif(!\SmartFileSystem::is_type_file($file)) {
			return true;
		} //end if
		//--
	//	return @filemtime($this->baseDir . $file) > $time; // @ - stat may fail
		return (bool) (\SmartFileSystem::get_file_mtime($file) > $time); // fix by unixman
		//--
	} //END FUNCTION


	/**
	 * Returns referred template name.
	 * @return string
	 */
	public function getReferredName($file, $referringFile) {
		//-- fixes by unixman
	/*	if ($this->baseDir || !preg_match('#/|\\\\|[a-z][a-z0-9+.-]*:#iA', $file)) {
			$file = $this->normalizePath($referringFile . '/../' . $file);
		} */
	//	\Smart::log_warning('File: '.$file.' # RefFile: '.$referringFile);
		if($file && $referringFile) {
			if(!\SmartFileSysUtils::check_if_safe_path($file)) {
				throw new \RuntimeException('Unsafe Sub-Template FileName (1): '.$file);
				return '';
			} //end if
			if(!\SmartFileSysUtils::check_if_safe_path($referringFile)) {
				throw new \RuntimeException('Unsafe Template FileName in Refering Template: '.$referringFile);
				return '';
			} //end if
			$file = \SmartFileSysUtils::add_dir_last_slash(\SmartFileSysUtils::get_dir_from_path($referringFile)).$file;
			if(!\SmartFileSysUtils::check_if_safe_path($file)) {
				throw new \RuntimeException('Unsafe Sub-Template FileName (2): '.$file);
				return '';
			} //end if
		} //end if
		//-- #fix
		return (string) $file;
		//--
	} //END FUNCTION


	/**
	 * Returns unique identifier for caching.
	 * @return string
	 */
	public function getUniqueId($file) {
		//--
	//	return $this->baseDir . strtr($file, '/', DIRECTORY_SEPARATOR);
		return (string) $file; // fix by unixman
		//--
	} //END FUNCTION


	/**
	 * @return string
	 */
	/*
	// fix by unixman
	private static function normalizePath($path) {
		//--
		$res = [];
		//--
		foreach(explode('/', strtr($path, '\\', '/')) as $part) {
			if($part === '..' && $res && end($res) !== '..') {
				array_pop($res);
			} elseif($part !== '.') {
				$res[] = $part;
			} //end if else
		} //end foreach
		//--
		return (string) implode(DIRECTORY_SEPARATOR, $res);
		//--
	} //END FUNCTION
	*/


} //END CLASS


// end of php code
