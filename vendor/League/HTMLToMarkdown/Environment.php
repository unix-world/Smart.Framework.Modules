<?php

namespace League\HTMLToMarkdown;

use League\HTMLToMarkdown\Converter\BlockquoteConverter;
use League\HTMLToMarkdown\Converter\CodeConverter;
use League\HTMLToMarkdown\Converter\CommentConverter;
use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\Converter\DefaultConverter;
use League\HTMLToMarkdown\Converter\DivConverter;
use League\HTMLToMarkdown\Converter\SpanConverter;
use League\HTMLToMarkdown\Converter\EmphasisConverter;
use League\HTMLToMarkdown\Converter\HardBreakConverter;
use League\HTMLToMarkdown\Converter\HeaderConverter;
use League\HTMLToMarkdown\Converter\HorizontalRuleConverter;
use League\HTMLToMarkdown\Converter\ImageConverter;
use League\HTMLToMarkdown\Converter\LinkConverter;
use League\HTMLToMarkdown\Converter\ListBlockConverter;
use League\HTMLToMarkdown\Converter\ListItemConverter;
use League\HTMLToMarkdown\Converter\PreformattedConverter;
use League\HTMLToMarkdown\Converter\TextConverter;
use League\HTMLToMarkdown\Converter\TableConverter;

final class Environment {

	/** @var Configuration */
	protected $config;

	/** @var ConverterInterface[] */
	protected $converters = [];


	public function __construct(array $config=[]) {
		$this->config = new Configuration($config);
		$this->addConverter(new DefaultConverter());
	} //END FUNCTION


	public function getConfig(): Configuration {
		return $this->config;
	} //END FUNCTION


	public function addConverter(ConverterInterface $converter): void {
		if($converter instanceof ConfigurationAwareInterface) {
			$converter->setConfig($this->config);
		} //end if
		foreach($converter->getSupportedTags() as $tag) {
			$this->converters[$tag] = $converter;
		} //end foreach
	} //END FUNCTION


	public function getConverterByTag(string $tag): ConverterInterface {
		if(isset($this->converters[$tag])) {
			return $this->converters[$tag];
		} //end if
		return $this->converters[DefaultConverter::DEFAULT_CONVERTER];
	} //END FUNCTION


	public static function createDefaultEnvironment(array $config=[]): Environment {
		//--
		$environment = new static($config);
		//--
		$environment->addConverter(new BlockquoteConverter());
		$environment->addConverter(new CodeConverter());
		$environment->addConverter(new CommentConverter());
		$environment->addConverter(new DivConverter());
		$environment->addConverter(new EmphasisConverter());
		$environment->addConverter(new HardBreakConverter());
		$environment->addConverter(new HeaderConverter());
		$environment->addConverter(new HorizontalRuleConverter());
		$environment->addConverter(new ImageConverter());
		$environment->addConverter(new LinkConverter());
		$environment->addConverter(new ListBlockConverter());
		$environment->addConverter(new ListItemConverter());
		$environment->addConverter(new SpanConverter());
		$environment->addConverter(new PreformattedConverter());
		$environment->addConverter(new TextConverter());
		$environment->addConverter(new TableConverter());
		//--
		return $environment;
		//--
	} //END FUNCTION


} //END CLASS

// #end
