<?php
// Class: \SmartModExtLib\NlpStemmer\Stemmer
// [Smart.Framework.Modules - NLP Stemmer]
// (c) 2006-2021 unix-world.org - all rights reserved

namespace SmartModExtLib\NlpStemmer;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * @author Luís Cobucci <lcobucci@gmail.com>
 */
interface Stemmer {

	/**
	 * Main function to get the STEM of a word
	 *
	 * @param string $word A valid UTF-8 word
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function stem(string $word);

} //END CLASS

// #end
