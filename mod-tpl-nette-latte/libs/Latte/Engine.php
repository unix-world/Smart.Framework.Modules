<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 * (c) 2018-2019 unix-world.org
 */

// contains fixes by unixman

namespace Latte;


/**
 * Templating engine Latte.
 */
final class Engine {

	use Strict;

	const VERSION = '2.4.8-r.20190226.sfm';

	/** Content types */
	const CONTENT_HTML = 'html',
		CONTENT_XHTML = 'xhtml',
		CONTENT_XML = 'xml',
		CONTENT_JS = 'js',
		CONTENT_CSS = 'css',
		CONTENT_ICAL = 'ical',
		CONTENT_TEXT = 'text';

	/** @var callable[] */
	public $onCompile = [];

	/** @var Parser */
	private $parser;

	/** @var Compiler */
	private $compiler;

	/** @var ILoader */
	private $loader;

	/** @var Runtime\FilterExecutor */
	private $filters;

	/** @var array */
	private $providers = [];

	/** @var string */
	private $contentType = self::CONTENT_HTML;

	/** @var string */
	private $tempDirectory;

	/** @var bool */
	private $autoRefresh = true;


	public function __construct() {
		//--
		$this->filters = new Runtime\FilterExecutor;
		//--
	} //END FUNCTION


	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render($name, array $params = [], $block = null) {
		//--
		$this->createTemplate($name, $params + ['_renderblock' => $block])->render();
		//--
	} //END FUNCTION


	/**
	 * Renders template to string.
	 * @return string
	 */
	public function renderToString($name, array $params = [], $block = null) {
		//--
		$template = $this->createTemplate($name, $params + ['_renderblock' => $block]);
		//--
		return $template->capture([$template, 'render']);
		//--
	} //END FUNCTION


	/**
	 * Creates template object.
	 * @return Runtime\Template
	 */
	public function createTemplate($name, array $params = []) {
		//--
		$class = $this->getTemplateClass($name);
		//--
		if(!class_exists($class, false)) {
			$this->loadTemplate($name);
		} //end if
		//--
		return new $class($this, $params, $this->filters, $this->providers, $name);
		//--
	} //END FUNCTION


	/**
	 * Compiles template to PHP code.
	 * @return string
	 */
	public function compile($name) {
		//--
		foreach($this->onCompile ?: [] as $cb) {
			call_user_func(Helpers::checkCallback($cb), $this);
		} //end foreach
		$this->onCompile = [];
		//--
		$source = $this->getLoader()->getContent($name);
		//--
		try {
			$tokens = $this->getParser()->setContentType($this->contentType)->parse($source);
			$code = $this->getCompiler()->setContentType($this->contentType)->compile($tokens, $this->getTemplateClass($name));
		} catch(\Exception $e) {
			if(!$e instanceof CompileException) {
				$e = new CompileException('Thrown exception '.$e->getMessage(), 0, $e);
			} //end if
			$line = isset($tokens) ? $this->getCompiler()->getLine() : $this->getParser()->getLine();
			throw $e->setSource($source, $line, $name);
		} //end try catch
		//--
		if(!preg_match('#\n|\?#', $name)) {
			$code = "<?php\n// source: $name\n?>" . $code;
		} //end if
		//--
		$code = PhpHelpers::reformatCode($code);
		//--
		return $code;
		//--
	} //END FUNCTION


	/**
	 * Compiles template to cache.
	 * @param  string
	 * @return void
	 * @throws \LogicException
	 */
	public function warmupCache($name) {
		//--
		if(!$this->tempDirectory) {
			throw new \LogicException('Path to temporary directory is not set.');
		} //end if
		//--
		$class = $this->getTemplateClass($name);
		if(!class_exists($class, false)) {
			$this->loadTemplate($name);
		} //end if
		//--
	} //END FUNCTION


	/**
	 * @return void
	 */
	private function loadTemplate($name) {
		//--
		if(!$this->tempDirectory) {
			//--
			$code = $this->compile($name);
			if(@eval('?>' . $code) === false) { // @ is escalated to exception
				throw (new CompileException('Error in template: ' . Helpers::errGetLast('message')))->setSource($code, Helpers::errGetLast('line'), $name.' (compiled)');
			} //end if
			return;
		} //end if
		//--
		$file = $this->getCacheFile($name);
		//--
		if(!$this->isExpired($file, $name) && (@include $file) !== false) { // @ - file may not exist
			return;
		} //end if
		//--
		/* fix by unixman: don't use extra lock, will use atomic lock: LOCK_EX available with file_put_contents() and invoked by \SmartFileSystem::write()
		if(!is_dir($this->tempDirectory) && !@mkdir($this->tempDirectory) && !is_dir($this->tempDirectory)) { // @ - dir may already exist
			throw new \RuntimeException("Unable to create directory '$this->tempDirectory'. " . Helpers::errGetLast('message'));
		} //end if
		$handle = @fopen("$file.lock", 'c+'); // @ is escalated to exception
		if(!$handle) {
			throw new \RuntimeException("Unable to create file '$file.lock'. " . Helpers::errGetLast('message'));
		} elseif(!@flock($handle, LOCK_EX)) { // @ is escalated to exception
			throw new \RuntimeException("Unable to acquire exclusive lock on '$file.lock'. " . Helpers::errGetLast('message'));
		} //end if
		*/
		$cache_dir = \SmartFileSysUtils::get_dir_from_path($file);
		if(!\SmartFileSysUtils::check_if_safe_path($cache_dir)) {
			throw new \RuntimeException('Unsafe cache directory: '.$cache_dir);
		} //end if
		\SmartFileSystem::dir_create($cache_dir, true);
		if(!\SmartFileSystem::is_type_dir($cache_dir)) { // @ - dir may already exist
			throw new \RuntimeException('Unable to create cache directory: '.$cache_dir);
		} //end if
		//--
		if(!\SmartFileSystem::is_type_file($file) || $this->isExpired($file, $name)) {
			//--
			$code = $this->compile($name);
			//--
		//	if (file_put_contents("$file.tmp", $code) !== strlen($code) || !rename("$file.tmp", $file)) {
			//--
			$tmpname = (string) $file.'.tmp-'.(float)microtime(true);
		//	if(file_put_contents($tmpname, $code, LOCK_EX) !== strlen($code) || !rename($tmpname, $file)) { // fix by unixman
		//	\SmartFileSystem::delete($file); // no more necessary as the rename below will use atomic rewrite destination by set rewrite destination to TRUE
			if(!\SmartFileSystem::write($tmpname, $code) || !\SmartFileSystem::rename($tmpname, $file, true)) { // fix by unixman
				//--
		//		@unlink("$file.tmp"); // @ - file may not exist
		//		@unlink((string)$tmpname);
				\SmartFileSystem::delete($tmpname); // @ - file may not exist
				//--
				throw new \RuntimeException('Unable to create file: '.$file);
				//--
			} //end if
			//--
		//	} elseif (function_exists('opcache_invalidate')) {
			if(function_exists('opcache_invalidate')) {
				@opcache_invalidate($file, true); // @ can be restricted
			} //end if
		} //end if
		//--
		if((include($file)) === false) {
			throw new \RuntimeException('Unable to load file: '.$file);
		} //end if
		/* fix by unixman: don't use extra lock, will use atomic lock: LOCK_EX available with file_put_contents() and invoked by \SmartFileSystem::write()
		flock($handle, LOCK_UN);
		fclose($handle);
		@unlink("$file.lock"); // @ file may become locked on Windows
		*/
		//--
	} //END FUNCTION


	/**
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	private function isExpired($file, $name) {
		//-- fix by unixman
		if(!\SmartFileSysUtils::check_if_safe_path($file)) { // unixman: path safety check for $name is made via: $this->getLoader()->isExpired()
			throw new \RuntimeException('Unsafe TPL Cache FilePath (2): '.$file);
			return true;
		} //end if
		if(!\SmartFileSystem::path_exists($file)) {
			return true;
		} elseif(!\SmartFileSystem::is_type_file($file)) {
			return true;
		} //end if
	//	return $this->autoRefresh && $this->getLoader()->isExpired($name, (int) @filemtime($file)); // @ - file may not exist
		return (bool) $this->autoRefresh && $this->getLoader()->isExpired($name, (int)\SmartFileSystem::get_file_mtime($file)); // fix by unixman
		//-- #fix
	} //END FUNCTION


	/**
	 * @return string
	 */
	public function getCacheFile($name) {
		//-- fix by unixman
		/*
		$hash = substr($this->getTemplateClass($name), 8);
		$base = preg_match('#([/\\\\][\w@.-]{3,35}){1,3}\z#', $name, $m)
			? preg_replace('#[^\w@.-]+#', '-', substr($m[0], 1)) . '--'
			: '';
		return "$this->tempDirectory/$base$hash.php";
		*/
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($name)) {
			throw new \RuntimeException('Unsafe Cache FilePath (2): '.$name);
			return $this->tempDirectory.'/'.'-----nLatte-Tpl-Error-'.(int)time().'-----'.'.php';
		} //end if
		//--
		$base = (string) \SmartFileSysUtils::get_dir_from_path($name);
		$base = (string) str_replace('/', '#', (string)$base); // fix for smart.framework
		//--
		return (string) \Smart::safe_pathname(rtrim($this->tempDirectory, '/').'/v.'.\Smart::safe_filename(self::VERSION).'/'.\Smart::safe_filename((string)$base).'/'.\Smart::safe_filename(strtolower((string)$this->getTemplateClass($name))).'.php');
		//--
	} //END FUNCTION


	/**
	 * @return string
	 */
	public function getTemplateClass($name) {
		//--
		$key = (string) $this->getLoader()->getUniqueId($name)."\00".self::VERSION;
		//--
	//	return 'Template' . substr(md5($key), 0, 10);
		return 'nLatteTpl__'.sha1($key); // enhancement by unixman
		//--
	} //END FUNCTION


	/**
	 * Registers run-time filter.
	 * @param  string|null
	 * @param  callable
	 * @return static
	 */
	public function addFilter($name, $callback) {
		//--
		$this->filters->add($name, $callback);
		//--
		return $this;
		//--
	} //END FUNCTION


	/**
	 * Returns all run-time filters.
	 * @return string[]
	 */
	public function getFilters() {
		//--
		return $this->filters->getAll();
		//--
	} //END FUNCTION


	/**
	 * Call a run-time filter.
	 * @param  string  filter name
	 * @param  array   arguments
	 * @return mixed
	 */
	public function invokeFilter($name, array $args) {
		//--
		return call_user_func_array($this->filters->$name, $args);
		//--
	} //END FUNCTION


	/**
	 * Adds new macro.
	 * @return static
	 */
	public function addMacro($name, IMacro $macro) {
		//--
		$this->getCompiler()->addMacro($name, $macro);
		//--
		return $this;
		//--
	} //END FUNCTION


	/**
	 * Adds new provider.
	 * @return static
	 */
	public function addProvider($name, $value) {
		//--
		$this->providers[(string)$name] = $value;
		//--
		return $this;
		//--
	} //END FUNCTION


	/**
	 * Returns all providers.
	 * @return array
	 */
	public function getProviders() {
		//--
		return $this->providers;
		//--
	} //END FUNCTION


	/**
	 * @return static
	 */
	public function setContentType($type) {
		//--
		$this->contentType = $type;
		//--
		return $this;
		//--
	} //END FUNCTION


	/**
	 * Sets path to temporary directory.
	 * @return static
	 */
	public function setTempDirectory($path) {
		//--
		$this->tempDirectory = (string) $path;
		//--
		return $this;
		//--
	} //END FUNCTION


	/**
	 * Sets auto-refresh mode.
	 * @return static
	 */
	public function setAutoRefresh($on=true) {
		//--
		$this->autoRefresh = (bool) $on;
		//--
		return $this;
		//--
	} //END FUNCTION


	/**
	 * @return Parser
	 */
	public function getParser() {
		//--
		if(!$this->parser) {
			$this->parser = new Parser;
		} //end if
		//--
		return $this->parser;
		//--
	} //END FUNCTION


	/**
	 * @return Compiler
	 */
	public function getCompiler() {
		//--
		if(!$this->compiler) {
			$this->compiler = new Compiler;
			Macros\CoreMacros::install($this->compiler);
			Macros\BlockMacros::install($this->compiler);
		} //end if
		//--
		return $this->compiler;
		//--
	} //END FUNCTION


	/**
	 * @return static
	 */
	public function setLoader(ILoader $loader) {
		//--
		$this->loader = $loader;
		//--
		return $this;
		//--
	} //END FUNCTION


	/**
	 * @return ILoader
	 */
	public function getLoader() {
		//--
		if(!$this->loader) {
			$this->loader = new Loaders\FileLoader;
		} //end if
		//--
		return $this->loader;
		//--
	} //END FUNCTION


} //END CLASS


// end of php code
