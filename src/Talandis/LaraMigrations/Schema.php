<?php

namespace Talandis\LaraMigrations;

class Schema extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string $name
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function connection($name)
    {
        global $container;

        return $container['connection']->getSchemaBuilder();
    }

    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function getFacadeAccessor()
    {
        global $container;

        return $container['connection']->getSchemaBuilder();
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
}


