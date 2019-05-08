<?php

include_once 'Parser.php';
include_once 'Visualizer.php';

$parser = new Parser();
$parser->analyze('TestClass2.php');
Visualizer::showClassesMethods('TestClass2.php', $parser->methods, $parser->methodsWithSubMethods);

/**
Add:
 - namespaes
 - make refactoring
 - comments
 - preg regex move out in class const!!
 - add Recursion for methods in dept
*/ 
