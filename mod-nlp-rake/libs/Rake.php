<?php

namespace SmartModExtLib\NlpRake;

/**
 * PHP implementation of Rapid Automatic Keyword Exraction algorithm (RAKE) for extracting multi-word phrases from text.
 *
 * As described in:
 * ROSE, Stuart, et al. Automatic keyword extraction from individual documents. Text Mining, 2010, 1-20.
 * With help of Python implementation - <a href="https://github.com/aneesha/RAKE">github.com/aneesha/RAKE</a>
 *
 * based on PHP Rake by Richard Filipčík <richard@filipcik.sk> https://github.com/Richdark/RAKE-PHP @ version 0.1
 *
 * @author unixman, (c) 2020 unix-world.org
 * @version 1.1
 */

class Rake {

	/**
	 * @var string $stopwords_path
	 */
	public $stopwords_path;

	/**
	 * @var string $stopwords_pattern
	 */
	private $stopwords_pattern;


	/**
	 * Build stop words pattern from file given by parameter
	 *
	 * @param string $stopwords_path Path to the file with stop words
	 */
	function __construct(string $language='') {
		switch((string)$language) {
			case 'bg':
			case 'cs':
			case 'da':
			case 'de':
			case 'el':
			case 'en':
			case 'es':
			case 'et':
			case 'fi':
			case 'fr':
			case 'hr':
			case 'hu':
			case 'it':
			case 'ja':
			case 'ko':
			case 'lt':
			case 'lv':
			case 'nl':
			case 'no':
			case 'pl':
			case 'pt':
			case 'ro':
			case 'ru':
			case 'sl':
			case 'sv':
			case 'zh':
				break;
			default:
				$language = '@default';
		} //end switch
		$this->stopwords_path = 'modules/mod-nlp-rake/libs/stopwords/stopwords-'.$language.'.txt';
		$this->stopwords_pattern = $this->build_stopwords_regex();
	} //END FUNCTION


	/**
	 * Extract key phrases from input text
	 *
	 * @param string $text Input text
	 */
	public function extract(string $text) {
		$phrases_plain = self::split_sentences($text);
		$phrases = $this->get_phrases($phrases_plain);
		$scores = $this->get_scores($phrases);
		$keywords = $this->get_keywords($phrases, $scores);
		\arsort($keywords);
		return (array) $keywords;
	} //END FUNCTION


	/**
	 * @param string $text Text to be splitted into sentences
	 */
	public static function split_sentences(string $text) {
		return (array) \preg_split('/[.!?,;:\t\"\(\)]+/', $text); // fix by unixman from RakePlus
	} //END FUNCTION


	/**
	 * @param string $phrase Phrase to be splitted into words
	 */
	public static function split_phrase(string $phrase) {
		$words_temp = (array) \str_word_count($phrase, 1, '0123456789');
		$words = array();
		foreach($words_temp as $kk => $w) {
			if(((string)$w != '') AND !(\is_numeric($w))) {
				\array_push($words, (string)$w);
			} //end if
		} //end foreach
		return (array) $words;
	} //END FUNCTION


	/**
	 * Split sentences into phrases by loaded stop words
	 *
	 * @param array $sentences Array of sentences
	 */
	private function get_phrases(array $sentences) {
		$phrases_arr = array();
		foreach($sentences as $kk => $s) {
			$phrases_temp = \preg_replace($this->stopwords_pattern, '|', $s);
			$phrases = \explode('|', $phrases_temp);
			foreach($phrases as $kkk => $p) {
				$p = (string) \strtolower((string)\trim((string)$p));
				if((string)$p != '') {
					\array_push($phrases_arr, $p);
				} //end if
			} //end foreach
		} //end foreach
		return (array) $phrases_arr;
	} //END FUNCTION


	/**
	 * Calculate score for each word
	 *
	 * @param array $phrases Array containing individual phrases
	 */
	private function get_scores(array $phrases) {
		$frequencies = array();
		$degrees = array();
		foreach($phrases as $kk => $p) {
			$words = self::split_phrase($p);
			$words_count = (int) \Smart::array_size($words);
			$words_degree = $words_count - 1;
			foreach($words as $kkk => $w) {
				$frequencies[$w] = (isset($frequencies[$w]))? $frequencies[$w] : 0;
				$frequencies[$w] += 1;
				$degrees[$w] = (isset($degrees[$w]))? $degrees[$w] : 0;
				$degrees[$w] += $words_degree;
			} //end foreach
		} //end foreach
		foreach ($frequencies as $word => $freq) {
			$degrees[$word] += $freq;
		} //end foreach
		$scores = array();
		foreach ($frequencies as $word => $freq) {
			$scores[$word] = (isset($scores[$word]))? $scores[$word] : 0;
			$scores[$word] = $degrees[$word] / (float) $freq;
		} //end foreach
		return (array) $scores;
	} //END FUNCTION


	/**
	 * Calculate score for each phrase by words scores
	 *
	 * @param array $phrases Array of phrases (optimally) returned by get_phrases() method
	 * @param array $scores Array of words and their scores returned by get_scores() method
	 */
	private function get_keywords(array $phrases, array $scores) {
		$keywords = array();
		foreach($phrases as $kk => $p) {
			$keywords[$p] = (isset($keywords[$p])) ? $keywords[$p] : 0;
			$words = self::split_phrase($p);
			$score = 0;
			foreach($words as $kkk => $w) {
				$score += (float) $scores[$w];
			} //end foreach
			$keywords[$p] = (float) $score;
		} //end foreach
		return $keywords;
	} //END FUNCTION


	/**
	 * Get loaded stop words and return regex containing each stop word
	 */
	private function build_stopwords_regex() {
		$stopwords_arr = $this->load_stopwords();
		$stopwords_regex_arr = array();
		foreach($stopwords_arr as $kk => $word) {
			\array_push($stopwords_regex_arr, '\b'.\preg_quote($word).'\b');
		} //end foreach
		return (string) '/'.\implode('|', $stopwords_regex_arr).'/i';
	} //END FUNCTION


	/**
	 * Load stop words from an input file
	 */
	private function load_stopwords() {
		if(!\SmartFileSysUtils::check_if_safe_path($this->stopwords_path)) {
			\Smart::raise_error(
				'Rake Stopwords File path is invalid !',
				__METHOD__.'() The Stopwords File path is invalid: '.$this->stopwords_path
			);
			return array();
		} //end if
		if(!\SmartFileSystem::is_type_file($this->stopwords_path)) {
			\Smart::raise_error(
				'Rake Stopwords File cannot be found !',
				__METHOD__.'() The Stopwords File is missing: '.$this->stopwords_path
			);
			return array();
		} //end if
		if(!\SmartFileSystem::have_access_read($this->stopwords_path)) {
			\Smart::raise_error(
				'Rake Stopwords File cannot be read !',
				__METHOD__.'() The Stopwords File is unreadable: '.$this->stopwords_path
			);
			return array();
		} //end if
		$stopwords = array();
		$all = (string) \SmartFileSystem::read($this->stopwords_path);
		$all = (string) \trim((string)$all);
		$all = (string) \str_replace(["\r\n", "\r", "\t"], ["\n", "\n", ' '], (string)$all);
		$arr = (array)  \explode("\n", $all);
		foreach($arr as $kk => $line) {
			$line = (string) \trim((string)$line);
			if(((string)$line != '') AND (\strpos($line, '#') !== 0)) {
				\array_push($stopwords, $line);
			} //end if
		} //end foreach
		if(\Smart::array_size($stopwords) < 1) {
			\Smart::raise_error(
				'Rake Stopwords File is empty !',
				__METHOD__.'() The Stopwords File is empty: '.$this->stopwords_path
			);
			return array();
		} //end if
		return (array) $stopwords;
	} //END FUNCTION


} //END CLASS


// end of php code
