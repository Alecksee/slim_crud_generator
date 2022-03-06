<?php

namespace SlimGenerator\Command;

abstract class AbstractCommand
{
    protected $path_to_slim;

    abstract public function process();

    public function __construct($path_to_slim) 
    {
        $this->path_to_slim = $path_to_slim;
    }
}