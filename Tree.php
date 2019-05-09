<?php

class Tree {
	
	private $structure = [];

	public function add(string $parent, array $elements) {
		if (!isset($this->structure[$parent])) {
			$this->structure[$parent] = $elements;
		}
	}
	
	public function getStructure() {
		return $this->structure;
	}
	
	
	public function getAroundTheStructure() {
		echo 'get_words_pairs($first_lang, $second_lang, $first_letter)'."\n";
		$this->rec('get_words_pairs($first_lang, $second_lang, $first_letter)');
	}
	
	private function rec($parent) {
		if (isset($this->structure[$parent])) {
			$string = '';
			foreach ($this->structure[$parent] as $element) {
				//$string .= "(".$parent.")".$element." ";
				$string .= $element." ";
			}
			echo $string."\n";
			//var_dump($this->structure[$parent]);
			foreach ($this->structure[$parent] as $element) {
				//echo $element."\n";
				$this->rec($element);
			}
		}
	}

	public function getAroundTheStructure2($hill, &$img, $textColor) {
		imagestring($img, 5, 50, 30, $hill, $textColor);
		echo $hill."\n";
		$y = 70;
		$this->rec2($hill, $img, $textColor, $y);
	}
	
	private function rec2($parent, &$img, $textColor, &$y) {
		if (isset($this->structure[$parent])) {
			$string = '';
			foreach ($this->structure[$parent] as $element) {
				$string .= $element." ";
			}
			echo $string."\n";
			imagestring($img, 1, 50, $y, $string, $textColor);
			$y += 50;
			foreach ($this->structure[$parent] as $element) {
				//echo $element."\n";
				$this->rec2($element, $img, $textColor, $y);
			}
		}
	}
	
}

/*$tree = new Tree();
$tree->add('0', ['1']);
$tree->add('1', ['2', '3', '8']);
$tree->add('3', ['4']);
$tree->add('4', ['5', '6']);
$tree->add('6', ['7']);

//echo '<pre>';var_dump($tree->getStructure());

$tree->getAroundTheStructure();*/

