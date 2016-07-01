<?php

namespace Talandis\LaraMigrations;

use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;

class FakeLaravel
{
    private $command;

    protected $databasePath;

    public function __construct($command, $databasePath = null )
    {
        $this->command = $command;
        $this->databasePath = $databasePath;
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

    public function databasePath()
    {
        if ( !empty( $this->databasePath ) ) {
            return $this->databasePath;
        }

        list ( $prefix, $sourceFolder ) = explode('talandis', __DIR__ );

        return dirname( $prefix );
    }

    public static function environment()
    {
        return 'local';
    }
}