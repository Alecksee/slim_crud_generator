<?php

// CLI program for auto create crud routing in Slim

require("src/createCrud/createCrud.php");
require("src/functions/functions.php");

$crudName;

if (empty($argv[1]))
    $crudName = getCrudName();
else
    $crudName = $argv[1];

$crudName = plurialize($crudName);

try {
    new CreateCrud($crudName);
} catch ( Error $e ) {
    echo $e->getMessage()."\n";
}

