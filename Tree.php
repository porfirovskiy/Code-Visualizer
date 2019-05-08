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
		echo "1\n";
		$this->rec('1');
	}
	
	private function rec($parent) {
		if (isset($this->structure[$parent])) {
			$string = '';
			foreach ($this->structure[$parent] as $element) {
				$string .= $element." ";
			}
			echo $string."\n";
			foreach ($this->structure[$parent] as $element) {
				//echo $element."\n";
				$this->rec($element);
			}
		}
	}
	
}

$tree = new Tree();
$tree->add('0', ['1']);
$tree->add('1', ['2', '3', '8']);
$tree->add('3', ['4']);

echo '<pre>';var_dump($tree->getStructure());

$tree->getAroundTheStructure();

