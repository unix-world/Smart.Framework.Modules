<?php

namespace League\HTMLToMarkdown;

interface ConfigurationAwareInterface
{
	public function setConfig(Configuration $config): void;
}
