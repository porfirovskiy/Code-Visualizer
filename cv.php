<?php

include_once 'Parser.php';
include_once 'Visualizer.php';

$parser = new Parser();
$parser->analyze('TestClass.php');