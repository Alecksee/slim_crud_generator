<?php

define('SLIM_GENERATOR_SCHEMAS_DIR', __DIR__ . '/src/schemas/');

spl_autoload_register(function ($raw_class_name) {
    $namespaces = explode('\\', $raw_class_name);

    if ($namespaces[0] != 'SlimGenerator')
        return false;

    array_shift($namespaces);

    require 'src/classes/' . implode('/', $namespaces) . '.php';
});