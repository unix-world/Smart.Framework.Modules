<?php

//declare(strict_types=1);

namespace HTML2Markdown;

final class Element implements ElementInterface {

	/** @var \DOMNode */
	protected $node;

	/** @var ElementInterface|null */
	private $nextCached;

	/** @var \DOMNode|null */
	private $previousSiblingCached;


	public function __construct(\DOMNode $node) {
		//--
		$this->node = $node;
		//--
		$this->previousSiblingCached = $this->node->previousSibling;
		//--
	} //END FUNCTION


	public function isBlock(): bool {
		//--
		switch((string)$this->getTagName()) {
			case 'html':
			case 'body':
			case 'hr':
			case 'div':
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
			case 'blockquote':
			case 'li':
			case 'p':
			case 'ol':
			case 'ul':
			case 'table': // added by unixman
		//	case 'pre': // added by unixman ... is this needed ? perhaps not !
				return true;
			default:
		} //end switch
		//--
		return false;
		//--
	} //END FUNCTION


	public function isText(): bool {
		return $this->getTagName() === '#text';
	} //END FUNCTION


	public function isWhitespace(): bool {
		return $this->getTagName() === '#text' && \trim($this->getValue()) === '';
	} //END FUNCTION


	public function getTagName(): string {
		return $this->node->nodeName;
	} //END FUNCTION


	public function getValue(): string {
		return $this->node->nodeValue ?? '';
	} //END FUNCTION


	public function hasParent(): bool {
		return $this->node->parentNode !== null;
	} //END FUNCTION


	public function getParent(): ?ElementInterface {
		return $this->node->parentNode ? new self($this->node->parentNode) : null;
	} //END FUNCTION


	public function getNextSibling(): ?ElementInterface {
		return $this->node->nextSibling !== null ? new self($this->node->nextSibling) : null;
	} //END FUNCTION


	public function getPreviousSibling(): ?ElementInterface {
		return $this->previousSiblingCached !== null ? new self($this->previousSiblingCached) : null;
	} //END FUNCTION


	public function hasChildren(): bool {
		return $this->node->hasChildNodes();
	} //END FUNCTION


	/**
	 * @return ElementInterface[]
	 */
	public function getChildren(): array {
		//--
		$ret = [];
		foreach ($this->node->childNodes as $node) {
			$ret[] = new self($node);
		} //end foreach
		//--
		return $ret;
		//--
	} //END FUNCTION


	public function getNext(): ?ElementInterface {
		//--
		if($this->nextCached === null) {
			$nextNode = $this->getNextNode($this->node);
			if($nextNode !== null) {
				$this->nextCached = new self($nextNode);
			} //end if
		} //end if
		//--
		return $this->nextCached;
		//--
	} //END FUNCTION


	private function getNextNode(\DomNode $node, bool $checkChildren = true): ?\DomNode {
		//--
		if($checkChildren && $node->firstChild) {
			return $node->firstChild;
		} //end if
		//--
		if($node->nextSibling) {
			return $node->nextSibling;
		} //end if
		//--
		if($node->parentNode) {
			return $this->getNextNode($node->parentNode, false);
		} //end if
		//--
		return null;
		//--
	} //END FUNCTION


	/**
	 * @param string[]|string $tagNames
	 */
	public function isDescendantOf($tagNames): bool {
		//--
		if(!\is_array($tagNames)) {
			$tagNames = [$tagNames];
		} //end if
		//--
		for($p = $this->node->parentNode; $p !== false; $p = $p->parentNode) {
			if($p === null) {
				return false;
			} //end if
			if(\in_array($p->nodeName, $tagNames, true)) {
				return true;
			} //end if
		} //end for
		//--
		return false;
		//--
	} //END FUNCTION


	public function setFinalMarkdown(string $markdown): void {
		//--
		if($this->node->ownerDocument === null) {
			SmartFixes::logNotice((string)__METHOD__, 'Unowned node'); // fix by unixman
			return;
		} //end if
		//--
		if($this->node->parentNode === null) {
			SmartFixes::logNotice((string)__METHOD__, 'Cannot use this method on a node without a parent'); // fix by unixman
			return;
		} //end if
		//--
		$markdownNode = $this->node->ownerDocument->createTextNode($markdown);
		$this->node->parentNode->replaceChild($markdownNode, $this->node);
		//--
	} //END FUNCTION


	public function getChildrenAsString(): string {
		//--
		return $this->node->C14N();
		//--
	} //END FUNCTION


	public function getSiblingPosition(): int {
		//--
		$position = 0;
		$parent = $this->getParent();
		if($parent === null) {
			return $position;
		} //end if
		//-- loop through all nodes and find the given $node
		foreach($parent->getChildren() as $currentNode) {
			//--
			if(!$currentNode->isWhitespace()) {
				$position++;
			} //end if
			//--
			// TODO: Need a less-buggy way of comparing these
			// Perhaps we can somehow ensure that we always have the exact same object and use === instead?
			if($this->equals($currentNode)) {
				break;
			} //end if
			//--
		} //end foreach
		//--
		return $position;
		//--
	} //END FUNCTION


	public function getListItemLevel(): int {
		//--
		$level  = 0;
		$parent = $this->getParent();
		//--
		while($parent !== null && $parent->hasParent()) {
			if($parent->getTagName() === 'li') {
				$level++;
			} //end if
			$parent = $parent->getParent();
		} //end while
		//--
		return (int) $level;
		//--
	} //END FUNCTION


	public function getAttribute(string $name): string {
		//--
		if($this->node instanceof \DOMElement) {
			return $this->node->getAttribute($name);
		} //end if
		//--
		return '';
		//--
	} //END FUNCTION


	public function equals(ElementInterface $element): bool {
		//--
		if($element instanceof self) {
			return $element->node === $this->node;
		} //end if
		//--
		return false;
		//--
	} //END FUNCTION


} //END CLASS


//# end
