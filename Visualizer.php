<?php

class Visualizer {
	
	static public function showClassesMethods(string $className, array $methods) {
		$title = "Class $className has methods:";
		echo "Class $className has methods:\n";//use log method
		$obj = new Visualizer();
        $obj->createImage($title, $methods);
		//echo '<pre>';var_dump($methods);die();
	}
	
	public function createImage(string $title, array $methods) {
		$image = @imagecreate(500, 300) or die("Error");
		$backgroundColor = imagecolorallocate($image, 0, 0, 0);
		$textColor = imagecolorallocate($image, 233, 14, 91);
		
		//add text to img
		imagestring($image, 5, 50, 5, $title, $textColor);
		$y = 70;
		foreach ($methods as $method) {
			imagestring($image, 3, 5, $y, $method, $textColor);
			echo "\0\0$method\n";
			$y += 15;
		}
		//end add text
		
		imagepng($image, 'structure.png');
	}
	
	public function addTextToImage(string $title, $image) {
		
		return $image;
	}

}