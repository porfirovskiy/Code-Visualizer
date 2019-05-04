<?php

class Parser {
	
	public function analyze($className) {
		$this->log("start analiyzing");
		$code = $this->getCodeFromFile($className);
		$methods = $this->getAllMethodsFromCode($code);
		return $methods;
	}
	
	private function getCodeFromFile(string $file): string {
        $code = file_get_contents($file);
		$this->log("get code from file");
        return $code;
    }
	
	private function getAllMethodsFromCode(string $code): array {
		$code = str_replace(array("\r","\n"),"",$code);
		preg_match_all("/function[a-zA-Z0-9 ,\n()\$_]+\{/", $code, $methods, PREG_SET_ORDER);
        if (!empty($methods)) {
            return $this->cleanMethods($methods);
        }
		return [];
    }
	
	private function cleanMethods(array $methods): array {
		return array_map(function (array $rawName) {
			$name = preg_replace(['/function/', '/{/'], '', $rawName[0]);
			return trim($name);
		}, $methods);
    }
	
	private function log(string $text) {
		echo "\0$text ...\n";
    }

}