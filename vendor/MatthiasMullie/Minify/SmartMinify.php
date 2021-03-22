<?php

// (c) 2021 unixman
// version: 20210322

namespace MatthiasMullie\Minify;

final class SmartMinify {

	const SAFE_PATH_REGEX = '/^[_a-zA-Z0-9\-\.@\/]+$/';


	public static function minifyCss(string $theFile, string $basePath, array $arrFiles, array $importExtensions=[ 'svg' => 'data:image/svg+xml', 'png' => 'data:image/png', 'jpg' => 'data:image/jpeg', 'gif' => 'data:image/gif' ], int $maxImportSizeKb=250) {
		//--
		$theFile = (string) trim((string)$theFile);
		if( // {{{SYNC-CSS-CHEKS}}}
			((string)$theFile == '') OR
			((string)$theFile == '/') OR
			((string)$theFile == '.') OR
			((string)$theFile == '..') OR
			(strpos((string)$theFile, '..') !== false) OR
			((string)\substr((string)$theFile, 0, 1) == '.') OR
			((string)\substr((string)$theFile, 0, 1) == '/') OR
			((string)\substr((string)$theFile, -4, 4) != '.css') OR
			((int)\strlen((string)$theFile) < 5) OR
			(!\preg_match(self::SAFE_PATH_REGEX, (string)$theFile))
		) {
			throw new \Exception(__METHOD__.' # FAILED: Invalid CSS Export File: '.$theFile);
			return false;
		} //end if
		//--
		$basePath = self::getSafeBasePath((string)$basePath); // mixed
		if($basePath === false) {
			throw new \Exception(__METHOD__.' # FAILED: Invalid Base Path: '.$basePath);
			return false;
		} //end if
		//--
		if(!is_array($arrFiles) OR (count($arrFiles) <= 0)) {
			throw new \Exception(__METHOD__.' # FAILED: Empty or Invalid Array of Import Extensions ...');
			return false;
		} //end if
		//--
		if(!is_array($importExtensions) OR (count($importExtensions) <= 0)) {
			throw new \Exception(__METHOD__.' # FAILED: Empty or Invalid Array of Import Extensions ...');
			return false;
		} //end if
		//--
		$maxImportSizeKb = (int) $maxImportSizeKb;
		if($maxImportSizeKb < 10) {
			$maxImportSizeKb = 10;
		} elseif($maxImportSizeKb > 1000) {
			$maxImportSizeKb = 1000;
		} //end if else
		//--
		$cssUnionFiles = [];
		//--
		try {
			//--
			$cssMinifier = new \MatthiasMullie\Minify\CSS('/* CSS */');
			//--
			$cssMinifier->setMaxImportSize((int)$maxImportSizeKb);
			$cssMinifier->setImportExtensions((array)$importExtensions);
			//--
			foreach($arrFiles as $path => $type) {
				//--
				if((string)\strtolower((string)$type) == 'css') {
					//--
					if( // {{{SYNC-CSS-CHEKS}}}
						((string)$path == '') OR
						((string)$path == '/') OR
						((string)$path == '.') OR
						((string)$path == '..') OR
						(strpos((string)$path, '..') !== false) OR
						((string)\substr((string)$path, 0, 1) == '.') OR
						((string)\substr((string)$path, 0, 1) == '/') OR
						((string)\substr((string)$path, -4, 4) != '.css') OR
						((int)\strlen((string)$path) < 5) OR
						(!\preg_match(self::SAFE_PATH_REGEX, (string)$path))
					) {
						throw new \Exception(__METHOD__.' # FAILED: Invalid CSS Import File: '.$path);
						return false;
						break;
					} //end if
					if(!\is_file($basePath.$path)) {
						throw new \Exception(__METHOD__.' # FAILED: Could not access a CSS file: '.$basePath.$path);
						return false;
						break;
					} //end if
					//--
					$cssUnionFiles[] = (string) $basePath.$path;
					$cssMinifier->add((string)$basePath.$path);
					//--
				} //end if
				//--
			} //end foreach
			//--
			$cssUnionContent = (string) $cssMinifier->minify();
			$cssMinifier = null;
			//--
		} catch(\Exception $e) {
			throw new \Exception(__METHOD__.' # FAILED: '.$e->getMessage());
			return false;
		} //end try catch
		//--
		$cssUnionContent = '/* [@[#[!SF.DEV-ONLY!]#]@] */'."\n".'/* mmUnion.CSS# */'."\n\n".$cssUnionContent."\n".'/* #mmUnion.CSS */'."\n";
		//--
		$cssUnionFilePath = (string) $basePath.$theFile;
		//--
		if(\is_file((string)$cssUnionFilePath)) {
			if(!\unlink((string)$cssUnionFilePath)) {
				throw new \Exception(__METHOD__.' # FAILED: To remove the old CSS file: '.$cssUnionFilePath);
				return false;
			} //end if
		} //end if
		if(!\file_put_contents((string)$cssUnionFilePath, (string)$cssUnionContent, LOCK_EX)) {
			throw new \Exception(__METHOD__.' # FAILED: Could not write the CSS file: '.$cssUnionFilePath);
			return false;
		} //end if
		if(!\is_file((string)$cssUnionFilePath)) {
			throw new \Exception(__METHOD__.' # FAILED: Could not find the CSS file: '.$cssUnionFilePath);
			return false;
		} //end if
		if(!\is_readable((string)$cssUnionFilePath)) {
			throw new \Exception(__METHOD__.' # FAILED: the CSS file is not readable: '.$cssUnionFilePath);
			return false;
		} //end if
		//--
		if((int)\filesize((string)$cssUnionFilePath) <= 0) {
			throw new \Exception(__METHOD__.' # FAILED: the CSS file empty: '.$cssUnionFilePath);
			return false;
		} //end if
		//--
		return (array) $cssUnionFiles;
		//--
	} //END FUNCTION


	public static function minifyJs(string $theFile, string $basePath, array $arrFiles, bool $checkForMinifiedJs=true) {
		//--
		$theFile = (string) trim((string)$theFile);
		if( // {{{SYNC-JS-CHEKS}}}
			((string)$theFile == '') OR
			((string)$theFile == '/') OR
			((string)$theFile == '.') OR
			((string)$theFile == '..') OR
			(strpos((string)$theFile, '..') !== false) OR
			((string)\substr((string)$theFile, 0, 1) == '.') OR
			((string)\substr((string)$theFile, 0, 1) == '/') OR
			((string)\substr((string)$theFile, -3, 3) != '.js') OR
			((int)\strlen((string)$theFile) < 4) OR
			(!\preg_match(self::SAFE_PATH_REGEX, (string)$theFile))
		) {
			throw new \Exception(__METHOD__.' # FAILED: Invalid JS Export File: '.$theFile);
			return false;
		} //end if
		//--
		$basePath = self::getSafeBasePath((string)$basePath); // mixed
		if($basePath === false) {
			throw new \Exception(__METHOD__.' # FAILED: Invalid Base Path: '.$basePath);
			return false;
		} //end if
		//--
		if(!is_array($arrFiles) OR (count($arrFiles) <= 0)) {
			throw new \Exception(__METHOD__.' # FAILED: Empty or Invalid Array of Import Extensions ...');
			return false;
		} //end if
		//--
		$jsUnionFiles = [];
		//--
		$jsUnionFilesContent = '';
		//--
		if($checkForMinifiedJs === true) {
			$haveJsMin = false;
		} else {
			$haveJsMin = true; // noi check, assume TRUE
		} //end if else
		//--
		try {
			//--
			$jsMinifier = new \MatthiasMullie\Minify\JS('/* JS */');
			//--
			foreach($arrFiles as $path => $type) {
				//--
				if((string)\strtolower((string)$type) == 'js') {
					//--
					if( // {{{SYNC-JS-CHEKS}}}
						((string)$path == '') OR
						((string)$path == '/') OR
						((string)$path == '.') OR
						((string)$path == '..') OR
						(strpos((string)$path, '..') !== false) OR
						((string)\substr((string)$path, 0, 1) == '.') OR
						((string)\substr((string)$path, 0, 1) == '/') OR
						((string)\substr((string)$path, -3, 3) != '.js') OR
						((int)\strlen((string)$path) < 4) OR
						(!\preg_match(self::SAFE_PATH_REGEX, (string)$path))
					) {
						throw new \Exception(__METHOD__.' # FAILED: Invalid JS Import File: '.$path);
						return false;
						break;
					} //end if
					if(!\is_file($basePath.$path)) {
						throw new \Exception(__METHOD__.' # FAILED: Could not access a JS file: '.$basePath.$path);
						return false;
						break;
					} //end if
					//--
					$jsUnionFiles[] = (string) $basePath.$path;
					$jsMinifier->add((string)$basePath.$path);
					//--
					$fcontent = (string) \file_get_contents((string)$basePath.$path);
					$jsUnionFilesContent .= (string) '//=== '.$path."\n".$fcontent."\n".';'."\n";
					if($checkForMinifiedJs === true) {
						if($haveJsMin !== true) {
							if( // integrates with AppCodePack minify signatures
								(\strpos($fcontent, '// JS-Script (UM):') !== false) OR // minified by nodejs
								(\strpos($fcontent, '// JS-Script (GM):') !== false) OR // minified by google closures compiler
								(\strpos($fcontent, '// JS-Script (XM):') !== false) OR // minified by google closures compiler (js only)
								(\strpos($fcontent, '// JS-Script (YM):') !== false)    // minified by YUI Compressor
							) {
								$haveJsMin = true;
							} //end if
						} //end if
					} //end if
					//--
					$fcontent = '';
					//--
				} //end if
				//--
			} //end foreach
			//--
			if($haveJsMin !== true) {
				$jsUnionContent = (string) $jsMinifier->minify();
			} else {
				$jsUnionContent = (string) $jsUnionFilesContent;
			} //end if else
			$jsMinifier = null;
			$jsUnionFilesContent = '';
			//--
		} catch(\Exception $e) {
			throw new \Exception(__METHOD__.' # FAILED: '.$e->getMessage());
			return false;
		} //end try catch
		//--
		$jsUnionContent = '// [@[#[!SF.DEV-ONLY!]#]@]'."\n".'/* mmUnion.JS# */'.(($haveJsMin !== true) ? "\n".'// mmJsMin' : '')."\n\n".$jsUnionContent."\n".'/* #mmUnion.JS */'."\n";
		//--
		$jsUnionFilePath = (string) $basePath.$theFile;
		//--
		if(\is_file((string)$jsUnionFilePath)) {
			if(!\unlink((string)$jsUnionFilePath)) {
				throw new \Exception(__METHOD__.' # FAILED: To remove the old JS file: '.$jsUnionFilePath);
				return false;
			} //end if
		} //end if
		if(!\file_put_contents((string)$jsUnionFilePath, (string)$jsUnionContent, LOCK_EX)) {
			throw new \Exception(__METHOD__.' # FAILED: Could not write the JS file: '.$jsUnionFilePath);
			return false;
		} //end if
		if(!\is_file((string)$jsUnionFilePath)) {
			throw new \Exception(__METHOD__.' # FAILED: Could not find the JS file: '.$jsUnionFilePath);
			return false;
		} //end if
		if(!\is_readable((string)$jsUnionFilePath)) {
			throw new \Exception(__METHOD__.' # FAILED: the JS file is not readable: '.$jsUnionFilePath);
			return false;
		} //end if
		//--
		if((int)\filesize((string)$jsUnionFilePath) <= 0) {
			throw new \Exception(__METHOD__.' # FAILED: the JS file empty: '.$jsUnionFilePath);
			return false;
		} //end if
		//--
		return (array) $jsUnionFiles;
		//--
	} //END FUNCTION


	//==== PRIVATES


	private static function getSafeBasePath(string $basePath) {
		//--
		$basePath = (string) trim((string)$basePath); // can be empty of must be safe
		//--
		if((string)$basePath != '') {
			if(!\preg_match(self::SAFE_PATH_REGEX, (string)$basePath)) { // {{{SYNC-VALID-APPCODEPACK-APPID}}} allow safe path characters except: # / which are reserved
				throw new \Exception(__METHOD__.' # Invalid characters in the Base Path: '.$basePath);
				return false;
			} //end if
			if(((string)$basePath == '.') OR ((string)$basePath == '..')) {
				throw new \Exception(__METHOD__.' # Invalid Base Path (. ..): '.$basePath);
				return false;
			} //end if
			$basePath = (string) \rtrim((string)$basePath,'/').'/'; // add the trailing slash if non empty and safe !!!
			if((string)\substr((string)$basePath, 0, 1) == '/') {
				throw new \Exception(__METHOD__.' # Invalid Base Path (/ Absolute path denied): '.$basePath);
				return false;
			} //end if
		} //end if else
		//--
		return (string) $basePath;
		//--
	} //END FUNCTION


} //END CLASS

// #END PHP
