<?php

/*
 * Copyright 2013 Metzli and ZXing authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Metzli\Encoder;

use Metzli\Utils\BitMatrix;

class AztecCode
{
	private $compact;
	private $size;
	private $layers;
	private $codeWords;
	private $matrix;

	public function isCompact()
	{
		return $this->compact;
	}

	public function setCompact($compact)
	{
		$this->compact = $compact;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function setSize($size)
	{
		$this->size = $size;
	}

	public function getLayers()
	{
		return $this->layers;
	}

	public function setLayers($layers)
	{
		$this->layers = $layers;
	}

	public function getCodeWords()
	{
		return $this->codeWords;
	}

	public function setCodeWords($codeWords)
	{
		$this->codeWords = $codeWords;
	}

	public function getMatrix()
	{
		return $this->matrix;
	}

	public function setMatrix(BitMatrix $matrix)
	{
		$this->matrix = $matrix;
	}
}

// end of php code
