<?php

// modified and fixed by unixman
// custom filters by unixman to integrate with Smart.Framework
// depends: Smart.Framework PHP

namespace Dust;

class Dust implements \Serializable {

	const VERSION = 'v.0.1.91-r.20191115.sfm'; // github.com/Bloafer/dust-php

	private $parser;

	private $evaluator;

	private $templates;
	private $fsRdRpls;

	private $filters;

	private $helpers;

	private $automaticFilters;

	private $autoloaderOverride;

	private $tplBasePath;

	public function __construct($tplBasePath, $parser=null, $evaluator=null, $options=null) {
		//-- by unixman
		$this->tplBasePath = (string) $tplBasePath;
		$this->checkSafeBasePath();
		//--
		if($parser === null) {
			$parser = new Parse\Parser();
		}
		if($evaluator === null) {
			$evaluator = new Evaluate\Evaluator($this);
		}
		$this->parser = $parser;
		$this->evaluator = $evaluator;
		$this->templates = [];
		$this->fsRdRpls = [];
		$this->filters = [
			//--
			's' => new Filter\SuppressEscape(),
			//--
			/* originals
			'u'  => new Filter\EncodeUri(),
			'uc' => new Filter\EncodeUriComponent(),
			'js' => new Filter\JsonEncode(),
			'jp' => new Filter\JsonDecode()
			*/
			//--
			'h'  => new Filter\EscapeHtml(),
			'j'  => new Filter\EscapeJs(),
			'c'  => new Filter\EscapeCss(), // new by unixman
			'u'  => new Filter\EscapeUrl(),
			'o'  => new Filter\EscapeJson(),
			//--
			'b'  => new Filter\ForceBool(), // new by unixman
			'i'  => new Filter\ForceInteger(), // new by unixman
			'd'  => new Filter\ForceDecimal(), // new by unixman (w. 2 decimals)
			'n'  => new Filter\ForceNumeric(), // new by unixman
			//--
			't'  => new Filter\StrTrim(), // new by unixman
			'ml' => new Filter\StrToLower(), // new by unixman
			'mu' => new Filter\StrToUpper(), // new by unixman
			'mf' => new Filter\StrUcFirst(), // new by unixman
			'mw' => new Filter\StrUcWords(), // new by unixman
			//--
			'ih' => new Filter\FormatHtmlId(), // new by unixman
			'vj' => new Filter\FormatJsVar(), // new by unixman
			'fn' => new Filter\FormatNl2Br(), // new by unixman
		];
		$this->helpers = [
		//	'select'      => new Helper\Select(),
		//	'math'        => new Helper\Math(),
		//	'eq'          => new Helper\Eq(),
		//	'ne'          => new Helper\Ne(),
		//	'lt'          => new Helper\Lt(),
		//	'lte'         => new Helper\Lte(),
		//	'gt'          => new Helper\Gt(),
		//	'gte'         => new Helper\Gte(),
		//	'default'     => new Helper\DefaultHelper(),
		//	'size'        => new Helper\Size(),
			'if'          => new Helper\IfHelper(),
			'sep'         => new Helper\Sep(), // works only inside array context
			'contextDump' => new Helper\ContextDump()
		];
		$this->automaticFilters = [
		//	$this->filters['h']
		];
		if(is_array($options)) {
			// handle options ...
		}

	}

	public function getTemplates() { // by unixman, used by Debug Profiler
		return (array) $this->templates;
	}

	public function getFsRdRpls() { // by unixman, used by Debug Profiler
		return (array) $this->fsRdRpls;
	}

	public function getHelpers() { // by unixman, used by Evaluator
		return (array) $this->helpers;
	}

	public function getFilters() { // by unixman, used by Evaluator
		return (array) $this->filters;
	}

	public function getAutomaticFilters() { // by unixman, used by Evaluator
		return (array) $this->automaticFilters;
	}

	public function compile($source, $name=null) {
		$parsed = $this->parser->parse($source);
		if($name != null) {
			$this->register($name, $parsed);
		}
		return $parsed;
	}

	/*
	public function compileFn($source, $name = null) {
		$parsed = $this->compile($source, $name);
		return function ($context) use ($parsed) { return $this->renderTemplate($parsed, $context); };
	}
	*/

	public function compileFile($path, $basePath=null) {
		//just compile w/ the path as the name
		$tplName = (string) $path;
		if((string)$basePath != '') {
			if(\SmartFileSysUtils::check_if_safe_path((string)$basePath)) {
				$this->tplBasePath = (string) $basePath;
				$this->checkSafeBasePath();
			} //end if else
		} //end if
		if(((string)substr($path, 0, 1) == '!') AND ((string)substr($path, -1, 1) == '!')) {
			$path = (string) trim((string)$path, '!');
			$path = (string) trim((string)$path);
		} else {
			$path = (string) $this->tplBasePath.$path;
		} //end if else
		//$compiled = $this->compile(file_get_contents($path), $path);
		if(!\SmartFileSysUtils::check_if_safe_path((string)$path)) {
			throw new DustException('Unsafe File to Render: '.$path);
			return null;
		}
		if((!\SmartFileSystem::is_type_file((string)$path)) OR (!\SmartFileSystem::have_access_read((string)$path))) {
			throw new DustException('Invalid File to Render: '.$path);
			return null;
		} //end if
		$fcontents = (string) \SmartFileSystem::read((string)$path);
		$compiled = $this->compile((string)$fcontents, (string)$tplName);
		$compiled->filePath = (string) $path;
		//--
		if(\SmartFrameworkRuntime::ifDebug()) {
			$this->fsRdRpls[(string)$path] += 1;
			\SmartFrameworkRegistry::setDebugMsg('extra', 'DUST-TEMPLATING', [
				'title' => '[TPL-ReadFileTemplate-From-FS] :: Dust-'.(((string)$basePath == '') ? 'Sub' : '').'TPL / File-Read: '.$path.' ;',
				'data' => 'Content SubStr[0-'.(int)$this->smartGetdebugTplLength().']: '."\n".\Smart::text_cut_by_limit((string)$fcontents, (int)$this->smartGetdebugTplLength(), true, '[...]')
			]);
		} //end if
		//--
		return $compiled;
	}

	public function loadTemplate($name) { // used by Evaluator
		//if there is an override, use it instead
		if($this->autoloaderOverride != null) {
			return $this->autoloaderOverride->__invoke($name);
		}
		//is it there w/ the normal name?
		if(!isset($this->templates[$name])) {
			//if name is null and not in the templates array, put it there automatically
			if(!isset($this->templates[$name])) {
				$this->compileFile($name);
			}
		}
		return $this->templates[$name];
	}

	public function render($name, $context) {
		return $this->renderTemplate($this->loadTemplate($name), $context);
	}

	public function renderTemplate(Ast\Body $template, $context) {
		return $this->evaluator->evaluate($template, $context);
	}

	public function serialize() {
		return serialize($this->templates);
	}

	public function unserialize($data) {
		$this->templates = unserialize($data);
	}

	public function getVersion(){
		return (string) self::VERSION;
	}

	private function register($name, Ast\Body $template) {
		$this->templates[$name] = $template;
	}

	private function checkSafeBasePath() { // by unixman
		if(!\SmartFileSysUtils::check_if_safe_path((string)$this->tplBasePath)) {
			throw new DustException('Unsafe TPL Base Path: '.$this->tplBasePath);
			return null;
		}
		if(!\SmartFileSystem::is_type_dir($this->tplBasePath)) {
			throw new DustException('Unsafe TPL Base Path: '.$this->tplBasePath);
		}
	}

	private function smartGetdebugTplLength() {
		$len = 255;
		if(\defined('\\SMART_SOFTWARE_MKTPL_DEBUG_LEN')) {
			if((int)\SMART_SOFTWARE_MKTPL_DEBUG_LEN >= 255) {
				if((int)\SMART_SOFTWARE_MKTPL_DEBUG_LEN <= 524280) {
					$len = (int) \SMART_SOFTWARE_MKTPL_DEBUG_LEN;
				} //end if
			} //end if
		} //end if
		$len = \Smart::format_number_int($len,'+');
		return (int) $len;
	}

}

// #end of php code
