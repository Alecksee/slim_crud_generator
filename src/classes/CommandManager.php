<?php

namespace SlimGenerator;

class CommandManager
{
    public $name;
    public $is_available;

    public function __construct($command_name)
    {
        $this->name = $command_name;
        $this->is_available = $this->isAvailable();
    }

    public function getCommandInstance($path_to_slim)
    {
        $class_name = $this->getCommandClassName();
        return new $class_name($path_to_slim);
    }

    protected function getCommandClassName()
    {
        $path_to_command_class = explode('/', $this->name);
        $path_to_command_class = array_map('ucfirst', $path_to_command_class);

        return 'SlimGenerator\\Command\\' . implode('\\', $path_to_command_class);
    }

    protected function isAvailable()
    {
        return class_exists($this->getCommandClassName());
    }
}