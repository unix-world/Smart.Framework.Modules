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
 * A switch statement
 *
 * Example:
 *
 *     {% case condition %}{% when foo %} foo {% else %} bar {% endcase %}
 */
final class TagCase extends \TwistTPL\Decision {

	/**
	 * Stack of nodelists
	 *
	 * @var array
	 */
	public $nodelists;

	/**
	 * The nodelist for the else (default) nodelist
	 *
	 * @var array
	 */
	public $elseNodelist;

	/**
	 * The left value to compare
	 *
	 * @var string
	 */
	public $left;

	/**
	 * The current right value to compare
	 *
	 * @var mixed
	 */
	public $right;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param \TwistTPL\AbstractInterfaceFileSystem $fileSystem
	 *
	 * @throws \Exception
	 */
	public function __construct(?string $markup, array &$tokens, ?\TwistTPL\AbstractInterfaceFileSystem $fileSystem=null) {
		//--
		$this->nodelists = array();
		$this->elseNodelist = array();
		//--
		parent::__construct($markup, $tokens, $fileSystem);
		//--
		$syntaxRegexp = new \TwistTPL\Regexp('/'.\TwistTPL\Twist::get('QUOTED_FRAGMENT').'/');
		//--
		if($syntaxRegexp->match($markup)) {
			$this->left = $syntaxRegexp->matches[0];
		} else {
			throw new \Exception("Syntax Error in tag 'case' - Valid syntax: case [condition]");
			return;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Pushes the last nodelist onto the stack
	 */
	public function endTag() : void {
		//--
		$this->pushNodelist();
		//--
	} //END FUNCTION


	/**
	 * Unknown tag handler
	 *
	 * @param string $tag
	 * @param string $params
	 * @param array $tokens
	 *
	 * @throws \Exception
	 */
	public function unknownTag(?string $tag, ?string $params, array $tokens) : void {
		//--
		$whenSyntaxRegexp = new \TwistTPL\Regexp('/'.\TwistTPL\Twist::get('QUOTED_FRAGMENT').'/');
		//--
		switch((string)$tag) {
			case 'when': // push the current nodelist onto the stack and prepare for a new one
				if($whenSyntaxRegexp->match($params)) {
					$this->pushNodelist();
					$this->right = $whenSyntaxRegexp->matches[0];
					$this->nodelist = array();
				} else {
					throw new \Exception("Syntax Error in tag 'case' - Valid when condition: when [condition]");
					return;
				} //end if else
				break;
			case 'else': // push the last nodelist onto the stack and prepare to receive the else nodes
				$this->pushNodelist();
				$this->right = null;
				$this->elseNodelist = &$this->nodelist;
				$this->nodelist = array();
				break;
			default:
				parent::unknownTag((string)$tag, (string)$params, (array)$tokens);
		} //end switch
		//--
	} //END FUNCTION


	/**
	 * Renders the node
	 *
	 * @param \TwistTPL\Context $context
	 *
	 * @return string
	 */
	public function render(\TwistTPL\Context $context) : string {
		//--
		$output = '';
		//--
		$runElseBlock = true;
		//--
		if(!\is_array($this->nodelists)) {
			$this->nodelists = [];
		} //end if
		//--
		foreach($this->nodelists as $kk => $data) {
			list($right, $nodelist) = $data;
			if($this->equalVariables($this->left, $right, $context)) {
				$runElseBlock = false;
				$context->push();
				$output .= (string) $this->renderAll($nodelist, $context);
				$context->pop();
			} //end if
		} //end foreach
		//--
		if($runElseBlock) {
			$context->push();
			$output .= (string) $this->renderAll($this->elseNodelist, $context);
			$context->pop();
		} //end if
		//--
		return (string) $output;
		//--
	} //END FUNCTION


	/**
	 * Pushes the current right value and nodelist into the nodelist stack
	 */
	private function pushNodelist() {
		//--
		if(!\is_null($this->right)) {
			$this->nodelists[] = [ $this->right, $this->nodelist ];
		} //end if
	} //END FUNCTION


} //END CLASS

// #end
