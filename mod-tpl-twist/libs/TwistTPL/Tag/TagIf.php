<?php

/*
 * This file is part of the Twist package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Twist
 */

namespace TwistTPL\Tag;


/**
 * An if statement
 *
 * Example:
 *
 *     {% if true %} YES {% else %} NO {% endif %}
 *
 *     will return:
 *     YES
 */
final class TagIf extends \TwistTPL\Decision {

	/**
	 * Array holding the nodes to render for each logical block
	 *
	 * @var array
	 */
	private $nodelistHolders = [];

	/**
	 * Array holding the block type, block markup (conditions) and block nodelist
	 *
	 * @var array
	 */
	protected $blocks = [];


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param \TwistTPL\AbstractInterfaceFileSystem $fileSystem
	 */
	public function __construct(?string $markup, array &$tokens, ?\TwistTPL\AbstractInterfaceFileSystem $fileSystem=null) {
		$this->nodelist = & $this->nodelistHolders[count($this->blocks)];
		array_push($this->blocks, array('if', $markup, &$this->nodelist));
		parent::__construct($markup, $tokens, $fileSystem);
	} //END FUNCTION


	/**
	 * Handler for unknown tags, handle else tags
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 */
	public function unknownTag($tag, $params, array $tokens) : void {
		if(((string)$tag == 'else') || ((string)$tag == 'elsif')) {
			// Update reference to nodelistHolder for this block
			$this->nodelist = & $this->nodelistHolders[count($this->blocks) + 1];
			$this->nodelistHolders[count($this->blocks) + 1] = array();
			array_push($this->blocks, array($tag, $params, &$this->nodelist));
		} else {
			parent::unknownTag($tag, $params, $tokens);
		} //end if else
	} //END FUNCTION


	/**
	 * Render the tag
	 *
	 * @param \TwistTPL\Context $context
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		$context->push();
		//--
		$logicalRegex = new \TwistTPL\Regexp('/\s+(and|or)\s+/');
		$conditionalRegex = new \TwistTPL\Regexp('/('.\TwistTPL\Twist::get('QUOTED_FRAGMENT').')\s*([=!<>a-z_]+)?\s*('.\TwistTPL\Twist::get('QUOTED_FRAGMENT').')?/');
		//--
		$result = '';
		if(!\is_array($this->blocks)) {
			$this->blocks = [];
		} //end if
		foreach($this->blocks as $vk => $block) {
			if($block[0] == 'else') {
				$result = $this->renderAll($block[2], $context);
				break;
			} //end if
			if($block[0] === 'if' || $block[0] === 'elsif') {
				$logicalRegex->matchAll($block[1]); // Extract logical operators
				$logicalOperators = $logicalRegex->matches;
				$logicalOperators = $logicalOperators[1];
				$temp = $logicalRegex->split($block[1]); // Extract individual conditions
				$conditions = array();
				foreach($temp as $kvk => $condition) {
					if($conditionalRegex->match($condition)) {
						$left = (isset($conditionalRegex->matches[1])) ? $conditionalRegex->matches[1] : null;
						$operator = (isset($conditionalRegex->matches[2])) ? $conditionalRegex->matches[2] : null;
						$right = (isset($conditionalRegex->matches[3])) ? $conditionalRegex->matches[3] : null;
						\array_push($conditions, [
							'left' 		=> $left,
							'operator' 	=> $operator,
							'right' 	=> $right
						]);
					} else {
						throw new \Exception('Syntax Error in tag `if` - Valid syntax: if [condition]');
					} //end if else
				} //end foreach
				if(count($logicalOperators)) {
					$display = $this->interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context); // If statement contains and/or
					foreach($logicalOperators as $k => $logicalOperator) {
						if ($logicalOperator == 'and') {
							$display = ($display && $this->interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context));
						} else {
							$display = ($display || $this->interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context));
						} //end if else
					} //end foreach
				} else { // If statement is a single condition
					$display = $this->interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
				} //end if else
				if ($display) {
					$result = $this->renderAll($block[2], $context);
					break;
				} //end if
			} //end if
		} //end foreach
		//--
		$context->pop();
		//--
		return (string) $result;
		//--
	} //END FUNCTION


} //END CLASS

// #end
