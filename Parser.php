<?php

class Parser {
	
	const SUB_METHOD_PATTERN = "/\\$[a-zA-Z0-9 ,\n()\$_]+->[a-zA-Z0-9 ,\n()\$_\->]+\);/";
	const FIRST_LEVEL_PATTERN = "/function[a-zA-Z0-9 ,\n()\$_]+\{/";
	const SUB_PATTERN_BEGIN = '/function[ ]+';
	const SUB_PATTERN_END = '[a-zA-Z0-9 ,\n()\$_\->\{\;\\$\->=\[\]}+\/"\%.!:@?\'\t*\\\]+?(public|private|protected|static|function)/';
	
	public $methods = [];
	public $methodsWithSubMethods = [];
	public $tree;
	
	function __construct() {
		$this->tree = new Tree();
    }
	
	public function analyze(string $className) {
		$this->log("start analiyzing");
		$code = $this->getCodeFromFile($className);
		$this->getAllMethodsFromCode($code);
		$this->getSubMethods($this->methods, $code);
		//echo '<pre>';var_dump($this->tree->getStructure());
		//$this->tree->getAroundTheStructure();
		//echo '<pre>';var_dump($this->methodsWithSubMethods);die();
	}
	
	private function getCodeFromFile(string $file): string {
        $code = file_get_contents($file);
		$code = str_replace(array("\r","\n"),"",$code);
		$this->log("get code from file");
        return $code;
    }
	
	private function getAllMethodsFromCode(string $code) {
		preg_match_all(self::FIRST_LEVEL_PATTERN, $code, $methods, PREG_SET_ORDER);
        if (!empty($methods)) {
            $this->methods = $this->cleanMethods($methods);
        }
    }
	
	private function cleanMethods(array $methods): array {
		return array_map(function (array $rawName) {
			$name = preg_replace(['/function/', '/{/'], '', $rawName[0]);
			return trim($name);
		}, $methods);
    }
	
	private function getSubMethods($methods, $code) {
		foreach($methods as $method) {
			$methodName = $method;
			$method = preg_replace(['/\(/', '/\)/'], ['\(', '\)'], $method);
			$method = str_replace('$', '\$', $method);
			preg_match_all(self::SUB_PATTERN_BEGIN.$method.self::SUB_PATTERN_END, $code, $subMethods, PREG_SET_ORDER);
			if (!empty($subMethods)) {
				$subMethodsString = $subMethods[0][0];
				$this->getSubMethodsR($subMethodsString, $methodName, $code);
			}	
		}
	}
	
	private function getSubMethodsR(string $subMethodsString, string $methodName, string $code) {
		preg_match_all(self::SUB_METHOD_PATTERN, $subMethodsString, $list, PREG_SET_ORDER);
			if (!empty($list)) {
				$list = array_map(function (array $rawName) {
					$currentMethod = explode('->', $rawName[0]);
					$currentMethod = str_replace(';', '', $currentMethod);
					return end($currentMethod);
				}, $list);
				$this->tree->add($methodName, $list);
				//echo '<pre>';var_dump($this->methodsWithSubMethods);
				$this->getSubMethods($list, $code);
			}
    	}	

	private function log(string $text) {
		echo "\0$text ...\n";
    }
	
}
