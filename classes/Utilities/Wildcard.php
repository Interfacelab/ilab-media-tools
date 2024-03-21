<?php

namespace MediaCloud\Plugin\Utilities;

class Wildcard {
	private $pattern = null;
	private $delimiters = [];
	private $regex = null;

	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	public function match($string) {
		if ($this->pattern === '**') {
			return true;
		}

		return preg_match($this->getRegex(), $string) === 1;
	}

	public static function isDynamic($pattern) {
		$pattern = preg_replace('/\\\\./', '', $pattern);
		return preg_match('/[*{?\[]/', $pattern) === 1;
	}

	private function getRegex() {
		if ($this->regex !== null) {
			return $this->regex;
		}

		$replacements = [
			'\*\*' => '.*',
			'\\\\\\\\' => '\\\\',
			'\\\\\\*' => '[*]',
			'\\\\\\?' => '[?]',
			'\\\\\\[' => '[\[]',
			'\\\\\\]' => '[\]]',
		];

		if ($this->delimiters === []) {
			$replacements += [
				'\*' => '.*',
				'\?' => '?',
			];
		} else {
			$notDelimiters = '[^' . preg_quote(implode('', $this->delimiters), '#') . ']';
			$replacements += [
				'\*' => "$notDelimiters*",
				'\?' => $notDelimiters,
			];
		}

		$replacements += [
			'\[\!' => '[^',
			'\[' => '[',
			'\]' => ']',
			'\-' => '-',
		];

		$pattern = strtr(preg_quote($this->pattern, '#'), $replacements);
		$pattern = '#^' . $pattern . '$#us';
		$pattern .= 'i';

		$this->regex = $pattern;

		return $this->regex;
	}

}