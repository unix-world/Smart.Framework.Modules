<?php

namespace HTML2Markdown;

interface ConfigurationAwareInterface
{
	public function setConfig(Configuration $config): void;
}
