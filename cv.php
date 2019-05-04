<?php

include_once 'Parser.php';
include_once 'Visualizer.php';

$parser = new Parser();
$result = $parser->analyze('TestClass.php');
Visualizer::showClassesMethods('TestClass.php', $result);

/**
Add:
 - namespaes
 - commenst
 
Make refactoring

*/ 
