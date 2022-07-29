<?php
include_once "Parsers/Parser.php";
include_once "Parsers/ZavansaParser.php";

$parser=new ZavansaParser("zavansa.csv",10);
$parser->parse();
