<?php

class Visualizer {
	
	static public function showClassesMethods(string $className, array $methods, array $subMethods) {
		$title = "Class $className has methods structure:";
		echo "Class $className has methods:\n";//use log method
		$obj = new Visualizer();
        $obj->createImage($title, $methods, $subMethods);
		//echo '<pre>';var_dump($methods);die();
	}
	
	public function createImage(string $title, array $methods,  array $subMethods) {
		$image = @imagecreate(500, 1700) or die("Error");
		$backgroundColor = imagecolorallocate($image, 0, 0, 0);
		$textColor = imagecolorallocate($image, 233, 14, 91);
		
		//add text to img
		imagestring($image, 5, 50, 5, $title, $textColor);
		$y = 70;
		foreach ($methods as $method) {
			$textColor = imagecolorallocate($image, 0, 102, 204);
			imagestring($image, 3, 10, $y, $method, $textColor);
			//render sub methods
			if (isset($subMethods[$method])) {
				$yy = $y + 10;
				foreach ($subMethods[$method] as $subMethod) {
					$textColor = imagecolorallocate($image, 0, 255, 0);
					imagestring($image, 2, 25, $yy, $subMethod, $textColor);
					$yy += 10;
				}
			}
			echo "\0\0$method\n";
			$y += 55;
		}
		//end add text
		
		imagepng($image, 'structure.png');
	}
	
	public function addTextToImage(string $title, $image) {
		
		return $image;
	}

}
