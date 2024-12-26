<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL;

/**
 * Implements a template variable.
 */

final class Variable {

	/**
	 * @var array The filters to execute on the variable
	 */
	private $filters;

	/**
	 * @var string The name of the variable
	 */
	private $name;

	/**
	 * @var string The markup of the variable
	 */
	private $markup;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param bool $isAssigned (must set to TRUE for in-template assigned variables to avoid double escaping (make Twig compatible))
	 */
	public function __construct(?string $markup, bool $isAssigned=false) {

		$this->markup = $markup;

		$filterSep = new Regexp('/'.Twist::get('FILTER_SEPARATOR').'\s*(.*)/m');
		$syntaxParser = new Regexp('/('.Twist::get('QUOTED_FRAGMENT').')(.*)/ms');
		$filterParser = new Regexp('/(?:\s+|'.Twist::get('QUOTED_FRAGMENT').'|'.Twist::get('ARGUMENT_SEPARATOR').')+/');
		$filterArgsRegex = new Regexp('/(?:'.Twist::get('FILTER_ARGUMENT_SEPARATOR').'|'.Twist::get('ARGUMENT_SEPARATOR').')\s*((?:\w+\s*\:\s*)?'.Twist::get('QUOTED_FRAGMENT').')/');

		$this->filters = [];
		if ($syntaxParser->match($markup)) {
			$nameMarkup = $syntaxParser->matches[1] ?? null;
			$this->name = $nameMarkup;
			$filterMarkup = $syntaxParser->matches[2] ?? null;

			if ($filterSep->match($filterMarkup)) {
				$filterParser->matchAll(($filterSep->matches[1] ?? null));

				foreach($filterParser->matches[0] as $filter) {
					$filter = \trim($filter);
					if(\preg_match('/\w+/', $filter, $matches)) {
						$filterName = $matches[0];
						$filterArgsRegex->matchAll($filter);
						$matches = Twist::arrayFlatten($filterArgsRegex->matches[1] ?? null);
						$this->filters[] = $this->parseFilterExpressions($filterName, $matches);
					}
				}
			}
		}

		if($isAssigned !== true) { // skip for assigned (set) variables)
			if(Twist::get('ESCAPE_HTML_BY_DEFAULT') === 'yes') {
				// if auto html (escape) is enabled, and
				// - there's no raw filter, and
				// - no html (escape) filter
				// - no other standard html-adding filter
				// then
				// - add a mandatory html (escape) filter
				$addHtmlEscFilter = true;
				foreach($this->filters as $kf => $filter) {
					// with empty filters set we would just move along
					if(\in_array($filter[0], [ 'html', 'raw' ])) {
						// if we have any raw-like filter, stop
						$addHtmlEscFilter = false;
						break;
					} //end if
				} //end foreach
				if($addHtmlEscFilter) {
					$this->filters[] = [ 'html', [] ];
				} //end if
			} //end if
		} //end if

	}

	/**
	 * @param string $filterName
	 * @param array $unparsedArgs
	 * @return array
	 */
	private static function parseFilterExpressions($filterName, array $unparsedArgs) {

		$filterArgs = array();
		$keywordArgs = array();

		$justTagAttributes = new Regexp('/\A'.\trim(Twist::get('TAG_ATTRIBUTES'), '/').'\z/');

		foreach ($unparsedArgs as $a) {
			if ($justTagAttributes->match($a)) {
				$keywordArgs[$justTagAttributes->matches[1]] = $justTagAttributes->matches[2] ?? null;
			} else {
				$filterArgs[] = $a;
			}
		}

		if(\count($keywordArgs)) {
			$filterArgs[] = $keywordArgs;
		}

		return array($filterName, $filterArgs);
	}

	/**
	 * Gets the variable name
	 *
	 * @return string The name of the variable
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets all Filters
	 *
	 * @return array
	 */
	public function getFilters() {
		return $this->filters;
	}

	/**
	 * Renders the variable with the data in the context
	 *
	 * @param Context $context
	 *
	 * @return mixed|string
	 */
	public function render(Context $context) {
		$output = $context->get($this->name);
		foreach ($this->filters as $filter) {
			list($filtername, $filterArgKeys) = $filter;

			$filterArgValues = array();
			$keywordArgValues = array();

			foreach ($filterArgKeys as $arg_key) {
				if (\is_array($arg_key)) {
					foreach ($arg_key as $keywordArgName => $keywordArgKey) {
						$keywordArgValues[$keywordArgName] = $context->get($keywordArgKey);
					}

					$filterArgValues[] = $keywordArgValues;
				} else {
					$filterArgValues[] = $context->get($arg_key);
				}
			}

			$output = $context->invoke($filtername, $output, $filterArgValues);
		}
		return $output;
	}

} //END CLASS

// #end
