<?php

namespace SlimGenerator;

use SlimGenerator\CommandManager;
use Exception;

class SlimGenerator
{
    protected $command;
    protected $path_to_slim;

    /**
     * @param array $cli_params
     */
    public function __construct($cli_params)
    {
        // check parameters
        if (count($cli_params) < 3)
            throw new Exception('Missing parameters');

        // check if command is available
        $commandManager = new CommandManager($cli_params[1]);
        if (!$commandManager->is_available)
            throw new Exception('Command not found.');

        // check if path_to_slim is valid
        if (!$this->isPathToSlim($cli_params[2]))
            throw new Exception('Invalid path_to_slim parameter.');

        $this->path_to_slim = realpath($cli_params[2]);
        $this->command = $commandManager->getCommandInstance($this->path_to_slim);
    }

    public function process()
    {
        $this->command->process();
    }

    protected function isPathToSlim($path)
    {
        if (!is_dir($path))
            return false;

        // to be sure the path end without "/"
        $path = realpath($path);

        if (!is_file($path . '/app/settings.php'))
            return false;

        return true;
    }
}