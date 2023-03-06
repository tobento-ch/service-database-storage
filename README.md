# Database Storage Service

Database implementation for storages.

## Table of Contents

- [Getting started](#getting-started)
	- [Requirements](#requirements)
	- [Highlights](#highlights)
- [Documentation](#documentation)
    - [Storage Database](#storage-database)
    - [Storage Database Factory](#storage-database)
    - [Storage Database Processor](#storage-database-processor)
- [Credits](#credits)
___

# Getting started

Add the latest version of the database storage service running this command.

```
composer require tobento/service-database-storage
```

## Requirements

- PHP 8.0 or greater

## Highlights

- Framework-agnostic, will work with any project
- Decoupled design

# Documentation

## Storage Database

```php
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Storage\StorageDatabaseInterface;
use Tobento\Service\Database\Storage\StorageDatabase;
use Tobento\Service\Storage\StorageInterface;
use Tobento\Service\Storage\InMemoryStorage;

$database = new StorageDatabase(
    storage: new InMemoryStorage([]),
    name: 'inmemory',
);

var_dump($database instanceof DatabaseInterface);
// bool(true)

var_dump($database instanceof StorageDatabaseInterface);
// bool(true)

var_dump($database->storage() instanceof StorageInterface);
// bool(true)
```

Check out the [Database Service](https://github.com/tobento-ch/service-database) to learn more about it.

Check out the [Storage Service](https://github.com/tobento-ch/service-storage) to learn more about it.

## Storage Database Factory

```php
use Tobento\Service\Database\DatabaseFactoryInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\Storage\StorageDatabaseFactory;
use Tobento\Service\Database\Storage\StorageDatabaseInterface;
use Tobento\Service\Storage\StorageInterface;
use Tobento\Service\Storage\JsonFileStorage;

$factory = new StorageDatabaseFactory(
    databases: new Databases(),
);

var_dump($factory instanceof DatabaseFactoryInterface);
// bool(true)

// create json file storage:
$database = $factory->createDatabase(
    name: 'file',
    config: [
        'storage' => JsonFileStorage::class,
        'dir' => __DIR__.'/json-file/',
    ],
);

var_dump($database instanceof DatabaseInterface);
// bool(true)

var_dump($database instanceof StorageDatabaseInterface);
// bool(true)

var_dump($database->storage() instanceof StorageInterface);
// bool(true)
```

**PdoMySqlStorage or PdoMariaDbStorage**

```php
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Storage\StorageDatabaseFactory;
use Tobento\Service\Storage\PdoMySqlStorage;
use Tobento\Service\Storage\PdoMariaDbStorage;

$factory = new StorageDatabaseFactory(
    databases: new Databases(
        new PdoDatabase(
            pdo: new \PDO('sqlite::memory:'),
            name: 'mysql'
        ),
    ),
);

$database = $factory->createDatabase(
    name: 'storage',
    config: [
        'storage' => PdoMySqlStorage::class,
        //'storage' => PdoMariaDbStorage::class,
        
        // must be a PdoDatabaseInterface database with mysql driver
        'database' => 'mysql',
    ],
);

// Will throw an exception as
// PdoMySqlStorage::class only supports mysql driver
```

**InMemoryStorage**

```php
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\Storage\StorageDatabaseFactory;
use Tobento\Service\Storage\InMemoryStorage;

$factory = new StorageDatabaseFactory(
    databases: new Databases(),
);

$database = $factory->createDatabase(
    name: 'inmemory',
    config: [
        'storage' => InMemoryStorage::class,
    ],
);

var_dump($database instanceof DatabaseInterface);
// bool(true)
```

## Storage Database Processor

```php
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Storage\StorageDatabase;
use Tobento\Service\Database\Storage\StorageDatabaseProcessor;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\Storage\Tables\Tables;

// Create table to be processed:
$table = new Table(name: 'users');
$table->primary('id');
$table->string('name');
$table->items(iterable: [
    ['name' => 'John'],
    ['name' => 'Mia'],
]);

// Create the database to be processed on:
$database = new StorageDatabase(
    storage: new InMemoryStorage(
        items: [],
        tables: (new Tables())->add('users', ['id', 'name'], 'id'),
    )
);

// Create the database processor supporting storages:
$processor = new StorageDatabaseProcessor();

var_dump($processor instanceof ProcessorInterface);
// bool(true)

// Process:
$processor->process(table: $table, database: $database);

// The items were successfully inserted
// into the storage by the processor:
$names = $database->storage()->table('users')->column('name')->all();
var_dump($names);
// { [0]=> string(4) "John" [1]=> string(3) "Mia" }
```

You may check out the [Database Service - Processors](https://github.com/tobento-ch/service-database#processors) to learn more about database processors in general.

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)