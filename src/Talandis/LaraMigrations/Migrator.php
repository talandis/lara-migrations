<?php
namespace Talandis\LaraMigrations;

use Symfony\Component\Console\Application;
use Illuminate\Database\Console\Migrations;
use Pimple\Container;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;

class Migrator {

	protected $container;

	private static $instance;

	private function __construct() {
		$this->container = new Container();

		$this->registerDefaultContainerItems();
	}

	public static function getInstance() {

		if ( static::$instance == null ) {
			static::$instance = new Migrator();
		}

		return static::$instance;
	}

	public function getContainer()
	{
		return $this->container;
	}

	public function registerContainerItem( $name, $action )
	{
		$this->container[ $name ] = $action;
	}

	protected function registerDefaultContainerItems()
	{
		$this->container['migration-table'] = 'migrations';
		$this->container['migrations-path'] = null;
		$this->container['environment'] = function($c) {

		    global $argv;

		    $environment = 'production';
		    if (!empty($argv[2]) && preg_match('/--database=(.*?)$/si', $argv[2], $matches) ) {
		        $environment = $matches[1];
		        unset($argv[2]);
		    }

		    return $environment;
		};

		$this->container['db-config'] = function ($c) {

		    require_once( $c['config-path'] . $c['environment'] . '.php');

		    return [
		        'driver' => 'mysql',
		        'host' => $db['host'],
		        'database' => $db['database'],
		        'username' => $db['username'],
		        'password' => $db['password'],
		        'charset' => 'utf8',
		        'prefix' => '',
		        'collation' => 'utf8_general_ci',
		        'schema' => 'public'
		    ];
		};

		$this->container['filesystem'] = function ($c) {
		    return new \Illuminate\Filesystem\Filesystem;
		};

		$this->container['composer'] = function ($c) {
		    $composer = new \Illuminate\Support\Composer($c['filesystem']);
		    return $composer;
		};

		$this->container['builder'] = function ($c) {
		    $builder = new \Illuminate\Database\Schema\Builder($c['connection']);
		    return $builder;
		};

		$this->container['connection'] = function ($c) {
		    $manager = new \Illuminate\Database\Capsule\Manager();
		    $manager->addConnection($c['db-config']);
		    $manager->setAsGlobal();
		    $manager->bootEloquent();
		    return $manager->getConnection('default');
		};

		$this->container['resolver'] = function ($c) {
		    $r = new \Illuminate\Database\ConnectionResolver([ $c['environment'] => $c['connection']]);
		    $r->setDefaultConnection( $c['environment'] );
		    return $r;
		};

		$this->container['migration-repo'] = function ($c) {
		    return new \Illuminate\Database\Migrations\DatabaseMigrationRepository($c['resolver'], $c['migration-table']);
		};

		$this->container['migration-creator'] = function ($c) {
		    return new \Illuminate\Database\Migrations\MigrationCreator($c['filesystem']);
		};

		$this->container['migrator'] = function ($c) {
		    return new \Illuminate\Database\Migrations\Migrator($c['migration-repo'], $c['resolver'], $c['filesystem']);
		};

		$this->container['install-command'] = function ($c) {
		    $command = new Migrations\InstallCommand($c['migration-repo']);
		    $command->setLaravel(new FakeLaravel($command, $c['migrations-path']));
		    return $command;
		};

		$this->container['migrate-make-command'] = function ($c) {
		    $command = new Migrations\MigrateMakeCommand($c['migration-creator'], $c['composer']);
		    $command->setLaravel(new FakeLaravel($command, $c['migrations-path']));
		    return $command;
		};

		$this->container['migrate-command'] = function ($c) {
		    $command = new Migrations\MigrateCommand($c['migrator']);
		    $command->setLaravel(new FakeLaravel($command, $c['migrations-path']));
		    return $command;
		};
	}

	public function run() {
		$app = new Application("Artsy, little brother of Artisan", "1.0");
		$app->add($this->container['install-command']);
		$app->add($this->container['migrate-make-command']);
		$app->add($this->container['migrate-command']);
		$app->run();
	}

}