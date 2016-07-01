# Laravel migrations for using outside Laravel

This is not an official Laravel package.
This package was built when I wished to have Laravel migrations in any other projects that is not using Laravel.

### Installation

1. Require this package with composer:
```
composer require talandis/lara-migrations
```

2. Create `migrations` folder in you project's root directory

### Configuration

1. After updating composer, create an executable file that runs migrator in your project's root folder. Sample file named `artisan` is included in this repository.
2. Create folder named `migrations`. All migration files will be stored here. You may change path to this folder in `artisan` file.
3. Set database configuration

#### Database configuration
    
For migrations to work you have to setup database credentials.
To do that you should call registerContainerItem with first parameter 'db-config' and second parameter should return an array with configuration

Sample array of database configuration
```php
[
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'sample_db',
    'username' => 'root',
    'password' => 'super_secret_pass',
    'charset' => 'utf8',
    'prefix' => '',
    'collation' => 'utf8_general_ci',
    'schema' => 'public'
]
```

### Usage

There are only two commands that looks completely the same as in Laravel

#### Create new migration

```
php artisan make:migration my_new_migration_name
```

#### Execute migrations

```
php artisan migrate
```

Migration with custom database

```
php artisan migrate --database=development
```

### Environments support

There is an optional argument for migrate command named `--database=...`. 
When using this argument you might want to modify your `db-config` item to reflect to that variable.

#### Sample

Sample with environments in separate configuration files.
The following sample presumes your configuration file defines 4 constants.

```php
$migrator->registerContainerItem( 'db-config', function ($c) {

    require_once( $c['config-path'] . $c['environment'] . '.php');

    return [
        'driver' => 'mysql',
        'host' => DB_HOST,
        'database' =>  DB_NAME,
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'charset' => 'utf8',
        'prefix' => '',
        'collation' => 'utf8_general_ci',
        'schema' => 'public'
    ];
} );
``` 
