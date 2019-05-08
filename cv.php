<?php

include_once 'Parser.php';
include_once 'Visualizer.php';

$parser = new Parser();
$parser->analyze('TestClass.php');
Visualizer::showClassesMethods('TestClass.php', $parser->methods, $parser->methodsWithSubMethods);

/**
Add:
 - namespaes
 - comments
 - preg regex move out in class const!!
 
Make refactoring

*/ 
