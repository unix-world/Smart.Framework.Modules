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
 * A Twist file system is way to let your templates retrieve other templates for use with the include tag.
 *
 * You can implement subclasses that retrieve templates from the database, from the file system using a different
 * path structure, you can provide them as hard-coded inline strings, or any manner that you see fit.
 *
 * You can add additional instance variables, arguments, or methods as needed.
 */
interface AbstractInterfaceFileSystem {
	/**
	 * Retrieve a template file.
	 *
	 * @param string $templateFile
	 * @param string $templatePath
	 *
	 * @return string
	 */
	public function readTemplateFile(string $templateFile, string $templatePath) : string;
}
