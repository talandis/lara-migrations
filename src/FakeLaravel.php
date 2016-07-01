<?php

namespace Talandis\LaraMigrations;

class FakeLaravel
{
    private $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string $command
     * @param  array $parameters
     * @return int
     */
    public function call($command, array $parameters = array())
    {
        $parameters['command'] = $command;
        $this->lastOutput = new BufferedOutput;
        if (method_exists($this->command, 'fire')) {
            return $this->command->fire(new ArrayInput($parameters), $this->lastOutput);
        } elseif (method_exists($this->command, 'handle')) {
            return $this->command->handle(new ArrayInput($parameters), $this->lastOutput);
        } else {
            $this->command->error('missing "fire" and "handle" ');
        }
    }

    public static function databasePath()
    {
        return dirname(__FILE__) . '/';
    }

    public static function environment()
    {
        return 'local';
    }
}