<?php

//declare(strict_types=1);

namespace HTML2Markdown;

abstract class AbstractConverterConfig {

//	protected $escapeMode = 'default';
//	private const MARKDOWN_CODE_ESCAPE_MODES = [ 'default' ];

	private $options = [];


	final public function __construct(array $options) {
		//--
		$this->options = (array) $options;
		//--
	} //END FUNCTION


	final protected function getConfig(?string $key, $defval) { // : mixed
		//--
		$key = (string) \trim((string)$key);
		if((string)$key == '') {
			return $defval; // mixed
		} //end if
		//--
		if(!\array_key_exists((string)$key, (array)$this->options)) {
			return $defval; // mixed
		} //end if
		//--
		return $this->options[(string)$key]; // mixed
		//--
	} //END FUNCTION


//	final public function getEscapeMode : string {
//		//--
//		if(!\in_array($this->escapeMode, self::MARKDOWN_CODE_ESCAPE_MODES)) {
//			return 'default';
//		} //end if
//		//--
//		return (string) $this->escapeMode;
//		//--
//	} //END FUNCTION


} //END CLASS

// #end
