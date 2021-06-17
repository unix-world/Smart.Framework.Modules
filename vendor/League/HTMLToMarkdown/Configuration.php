<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown;


class Configuration {

	/** @var array<string, mixed> */
	protected $config;


	/**
	 * @param array<string, mixed> $config
	 */
	public function __construct(array $config = []) {
		$this->config = $config;
	} //END FUNCTION


	/**
	 * @param array<string, mixed> $config
	 */
	public function merge(array $config = []): void {
		$this->config = \array_replace_recursive($this->config, $config);
	} //END FUNCTION


	/**
	 * @param array<string, mixed> $config
	 */
	public function replace(array $config = []): void {
		$this->config = $config;
	} //END FUNCTION


	/**
	 * @param mixed $value
	 */
	public function setOption(string $key, $value): void {
		$this->config[$key] = $value;
	} //END FUNCTION


	/**
	 * @param mixed|null $default
	 *
	 * @return mixed|null
	 */
	public function getOption(?string $key = null, $default = null) {
		if ($key === null) {
			return $this->config;
		}
		if (! isset($this->config[$key])) {
			return $default;
		}
		return $this->config[$key];
	} //END FUNCTION


} //END CLASS


// #end
