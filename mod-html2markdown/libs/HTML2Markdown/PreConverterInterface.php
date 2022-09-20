<?php

//declare(strict_types=1);

namespace HTML2Markdown;

interface PreConverterInterface {

	public function preConvert(ElementInterface $element): void;

} //END INTERFACE

// #end
