<?php

class Parser {
	
	private $methods = [];
	
	public function analyze($className) {
		$this->log("start analiyzing");
		$code = $this->getCodeFromFile($className);
		$methods = $this->getAllMethodsFromCode($code);
		echo '<pre>';var_dump($methods);
		$methods = $this->getSubMethods($code, $methods);
		return $methods;
	}
	
	private function getCodeFromFile(string $file): string {
        $code = file_get_contents($file);
		$code = str_replace(array("\r","\n"),"",$code);
		$this->log("get code from file");
        return $code;
    }
	
	private function getAllMethodsFromCode(string $code): array {
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
	
	private function getSubMethods(string $code, array $methods): array {
		//preg_match_all("/\\$[a-zA-Z0-9 ,\n()\$_]+->[a-zA-Z0-9 ,\n()\$_\->]+\);/", $code, $list, PREG_SET_ORDER);
		//echo '<pre>';var_dump($list);die();
		
		//function[ ]+fillUniqEntities\(\$khh, \$jk\)[a-zA-Z0-9 ,\n()\$_\->\{\;\\$\->=\[\]}]+}
		foreach($methods as $method) {
			//$method = 'ddff($dff, $ggghh)';
			$methodName = $method;
			$method = preg_replace(['/\(/', '/\)/'], ['\(', '\)'], $method);
			$method = str_replace('$', '\$', $method);
			echo '<pre>';var_dump($method);
			preg_match_all('/function[ ]+'.$method.'[a-zA-Z0-9 ,\n()\$_\->\{\;\\$\->=\[\]}+\/"]+}/', $code, $subMethods, PREG_SET_ORDER);
			if (!empty($subMethods)) {
				$subMethodsString = $subMethods[0][0];
				preg_match_all("/\\$[a-zA-Z0-9 ,\n()\$_]+->[a-zA-Z0-9 ,\n()\$_\->]+\);/", $subMethodsString, $list, PREG_SET_ORDER);
				if (!empty($list)) {
					$this->methods[$methodName] = $list;
				} else {
					echo '<pre>';var_dump($subMethodsString);die();	
				}
			} else {
				if ($methodName == 'getUniqNames($codeString)') {
					echo '<pre>';var_dump($subMethods);die();
				}
			}
		}
		//echo '<pre>';var_dump($this->methods);die();	
		return [];
    }
	
	private function log(string $text) {
		echo "\0$text ...\n";
    }

}