<?php

namespace HTML2Markdown;

use HTML2Markdown\Converter\BlockquoteConverter;
use HTML2Markdown\Converter\CodeConverter;
use HTML2Markdown\Converter\CommentConverter;
use HTML2Markdown\Converter\ConverterInterface;
use HTML2Markdown\Converter\DefaultConverter;
use HTML2Markdown\Converter\DivConverter;
use HTML2Markdown\Converter\SpanConverter;
use HTML2Markdown\Converter\EmphasisConverter;
use HTML2Markdown\Converter\HardBreakConverter;
use HTML2Markdown\Converter\HeaderConverter;
use HTML2Markdown\Converter\HorizontalRuleConverter;
use HTML2Markdown\Converter\ImageConverter;
use HTML2Markdown\Converter\SvgConverter;
use HTML2Markdown\Converter\ButtonConverter;
use HTML2Markdown\Converter\LinkConverter;
use HTML2Markdown\Converter\ListBlockConverter;
use HTML2Markdown\Converter\ListItemConverter;
use HTML2Markdown\Converter\PreformattedConverter;
use HTML2Markdown\Converter\TextConverter;
use HTML2Markdown\Converter\TableConverter;

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
		$environment->addConverter(new SvgConverter());
		$environment->addConverter(new ButtonConverter());
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
