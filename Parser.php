<?php

class Parser {
	
	public $methods = [];
	public $methodsWithSubMethods = [];
	
	public function analyze(string $className) {
		$this->log("start analiyzing");
		$code = $this->getCodeFromFile($className);
		$this->getAllMethodsFromCode($code);
		$this->getSubMethods($code);
		//echo '<pre>';var_dump($this->methodsWithSubMethods);die();
	}
	
	private function getCodeFromFile(string $file): string {
        $code = file_get_contents($file);
		$code = str_replace(array("\r","\n"),"",$code);
		$this->log("get code from file");
        return $code;
    }
	
	private function getAllMethodsFromCode(string $code) {
		preg_match_all("/function[a-zA-Z0-9 ,\n()\$_]+\{/", $code, $methods, PREG_SET_ORDER);
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
	
	private function getSubMethods(string $code) {
		foreach($this->methods as $method) {
			$methodName = $method;
			$method = preg_replace(['/\(/', '/\)/'], ['\(', '\)'], $method);
			$method = str_replace('$', '\$', $method);
			preg_match_all('/function[ ]+'.$method.'[a-zA-Z0-9 ,\n()\$_\->\{\;\\$\->=\[\]}+\/"\%.!:@?\'\t*\\\]+?(public|private|protected|static|function)/', $code, $subMethods, PREG_SET_ORDER);
			if (!empty($subMethods)) {
				$subMethodsString = $subMethods[0][0];
				preg_match_all("/\\$[a-zA-Z0-9 ,\n()\$_]+->[a-zA-Z0-9 ,\n()\$_\->]+\);/", $subMethodsString, $list, PREG_SET_ORDER);
				if (!empty($list)) {
					$this->methodsWithSubMethods[$methodName] = $list;
				}
			}
		}
    }
	
	private function log(string $text) {
		echo "\0$text ...\n";
    }
	
}
