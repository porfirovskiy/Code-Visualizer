<?php

include_once 'Parser.php';
include_once 'Visualizer.php';
include_once 'Tree.php';

$parser = new Parser();
$parser->analyze('TestClass2.php');
Visualizer::showClassesMethods('TestClass2.php', $parser->methods, $parser->tree);

/**
Add:

 - исправить поиск методов(переменные могут отличаться названием)
 make_words_pairs($first_letter, $land, $revers)
make_words_pairs($first_letter, $land_code, $revers)
 
 - namespaes
 - make refactoring
 - comments
 - preg regex move out in class const!!
 - add Recursion for methods in dept
*/ 
