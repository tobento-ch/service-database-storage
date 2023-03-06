<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Database\Storage\Test;;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\ProcessException;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Storage\StorageDatabaseProcessor;
use Tobento\Service\Database\Storage\StorageDatabase;
use Tobento\Service\Storage\Tables\Tables;
use Tobento\Service\Storage\PdoMariaDbStorage;
use Tobento\Service\Storage\PdoMySqlStorage;
use Tobento\Service\Storage\JsonFileStorage;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\Storage\QueryException;
use Tobento\Service\Storage\Grammar\GrammarException;
use Tobento\Service\Filesystem\Dir;
use PDO;

/**
 * StorageDatabaseProcessorTest
 */
class StorageDatabaseProcessorTest extends TestCase
{
    public function testThatImplementsProcessorInterface()
    {
        $this->assertInstanceOf(
            ProcessorInterface::class,
            new StorageDatabaseProcessor()
        );
    }
    
    public function testSupportsDatabaseMethod()
    {
        $processor = new StorageDatabaseProcessor();
        
        $database = new StorageDatabase(new JsonFileStorage(__DIR__.'/tmp'));
        $this->assertTrue($processor->supportsDatabase($database));
        
        $database = new StorageDatabase(new InMemoryStorage([]));
        $this->assertTrue($processor->supportsDatabase($database));
    }
    
    public function testSupportsDatabaseMethodWithPdoMySqlStorage()
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL')) {
            $this->markTestSkipped('Storage database processor tests are disabled');
        }

        $pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_DSN'),
            username: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_USERNAME'),
            password: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_PASSWORD'),
        );
        
        $processor = new StorageDatabaseProcessor();

        $database = new StorageDatabase(new PdoMySqlStorage($pdo));
        $this->assertTrue($processor->supportsDatabase($database));
    }
    
    public function testSupportsDatabaseMethodWithPdoMariaDbStorage()
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB')) {
            $this->markTestSkipped('Storage database processor tests are disabled');
        }

        $pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB_DSN'),
            username: getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB_USERNAME'),
            password: getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB_PASSWORD'),
        );
        
        $processor = new StorageDatabaseProcessor();

        $database = new StorageDatabase(new PdoMariaDbStorage($pdo));
        $this->assertTrue($processor->supportsDatabase($database));
    }
    
    public function testSupportsDatabaseMethodUnsupportedDatabase()
    {
        $processor = new StorageDatabaseProcessor();
        
        $database = new PdoDatabase(new PDO('sqlite::memory:'));
        $this->assertFalse($processor->supportsDatabase($database));
    }
    
    public function testProcessMethodWithJsonFileStorage()
    {
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        $table->items(iterable: [
            ['name' => 'John'],
            ['name' => 'Mia'],
        ]);
        
        $database = new StorageDatabase(
            storage: new JsonFileStorage(
                dir: __DIR__.'/tmp',
                tables: (new Tables())->add('users', ['id', 'name'], 'id'),
            )
        );
        
        (new StorageDatabaseProcessor())->process(
            table: $table,
            database: $database,
        );
        
        $names = $database->storage()->table('users')->column('name')->all();
        
        $this->assertSame(['John', 'Mia'], $names);
        
        // delete
        (new StorageDatabaseProcessor())->process(
            table: (new Table(name: 'users'))->dropTable(),
            database: $database,
        );
        
        try {
            // will fail as table was dropped.
            $deleted = false;
            $database->storage()->table('users')->count();
        } catch (GrammarException $e) {
            $deleted = true;
        }
        
        $this->assertTrue($deleted);        
        
        (new Dir())->delete(__DIR__.'/tmp/');
    }
    
    public function testProcessMethodWithInMemoryStorage()
    {
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        $table->items(iterable: [
            ['name' => 'John'],
            ['name' => 'Mia'],
        ]);
        
        $database = new StorageDatabase(
            storage: new InMemoryStorage(
                items: [],
                tables: (new Tables())->add('users', ['id', 'name'], 'id'),
            )
        );
        
        (new StorageDatabaseProcessor())->process(
            table: $table,
            database: $database,
        );

        $names = $database->storage()->table('users')->column('name')->all();
        
        $this->assertSame(['John', 'Mia'], $names);
        
        // delete
        (new StorageDatabaseProcessor())->process(
            table: (new Table(name: 'users'))->dropTable(),
            database: $database,
        );
        
        try {
            // will fail as table was dropped.
            $deleted = false;
            $database->storage()->table('users')->count();
        } catch (GrammarException $e) {
            $deleted = true;
        }
    }
    
    public function testProcessMethodWithPdoMySqlStorage()
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL')) {
            $this->markTestSkipped('Storage database processor tests are disabled');
        }

        $pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_DSN'),
            username: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_USERNAME'),
            password: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_PASSWORD'),
        );
        
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        $table->items(iterable: [
            ['name' => 'John'],
            ['name' => 'Mia'],
        ]);
        
        $database = new StorageDatabase(
            storage: new PdoMySqlStorage(
                pdo: $pdo,
                tables: (new Tables())->add('users', ['id', 'name'], 'id'),
            )
        );
        
        $processor = new StorageDatabaseProcessor();
        
        $processor->process(table: $table, database: $database);

        $names = $database->storage()->table('users')->column('name')->all();
        
        $this->assertSame(['John', 'Mia'], $names);
        
        $processor->process(
            table: (new Table(name: 'users'))->dropTable(),
            database: $database,
        );
        
        try {
            // will fail as table was dropped.
            $deleted = false;
            $database->storage()->table('users')->count();
        } catch (QueryException $e) {
            $deleted = true;
        }
        
        $this->assertTrue($deleted);
    }
    
    public function testProcessMethodWithPdoMariaDbStorage()
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB')) {
            $this->markTestSkipped('Storage database processor tests are disabled');
        }

        $pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB_DSN'),
            username: getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB_USERNAME'),
            password: getenv('TEST_TOBENTO_DATABASE_PDO_MARIADB_PASSWORD'),
        );
        
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        $table->items(iterable: [
            ['name' => 'John'],
            ['name' => 'Mia'],
        ]);
        
        $database = new StorageDatabase(
            storage: new PdoMariaDbStorage(
                pdo: $pdo,
                tables: (new Tables())->add('users', ['id', 'name'], 'id'),
            )
        );
        
        $processor = new StorageDatabaseProcessor();
        
        $processor->process(table: $table, database: $database);

        $names = $database->storage()->table('users')->column('name')->all();
        
        $this->assertSame(['John', 'Mia'], $names);
        
        $processor->process(
            table: (new Table(name: 'users'))->dropTable(),
            database: $database,
        );
        
        try {
            // will fail as table was dropped.
            $deleted = false;
            $database->storage()->table('users')->count();
        } catch (QueryException $e) {
            $deleted = true;
        }
        
        $this->assertTrue($deleted);
    }
    
    public function testProcessMethodWithoutTransaction()
    {
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        $table->items(iterable: [
            ['name' => 'John'],
            ['name' => 'Mia'],
        ])->useTransaction(false);
        
        $database = new StorageDatabase(
            storage: new InMemoryStorage(
                items: [],
                tables: (new Tables())->add('users', ['id', 'name'], 'id'),
            )
        );
        
        (new StorageDatabaseProcessor())->process(
            table: $table,
            database: $database,
        );

        $names = $database->storage()->table('users')->column('name')->all();
        
        $this->assertSame(['John', 'Mia'], $names);
        
        // delete
        (new StorageDatabaseProcessor())->process(
            table: (new Table(name: 'users'))->dropTable(),
            database: $database,
        );
        
        try {
            // will fail as table was dropped.
            $deleted = false;
            $database->storage()->table('users')->count();
        } catch (GrammarException $e) {
            $deleted = true;
        }
    }    
    
    public function testProcessMethodForceInsertWhenFalseShouldNotInsert()
    {
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        $table->items(iterable: [
            ['name' => 'John'],
            ['name' => 'Mia'],
        ]);
        
        $database = new StorageDatabase(
            storage: new InMemoryStorage(
                items: [],
                tables: (new Tables())->add('users', ['id', 'name'], 'id'),
            )
        );
        
        $processor = new StorageDatabaseProcessor();
        
        $processor->process(table: $table, database: $database);
        
        $this->assertSame(2, $database->storage()->table('users')->count());
        
        $processor->process(table: $table, database: $database);
        
        $this->assertSame(2, $database->storage()->table('users')->count());        
    }
    
    public function testProcessMethodForceInsertWhenTrueShouldInsert()
    {
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        $table->items(iterable: [
            ['name' => 'John'],
            ['name' => 'Mia'],
        ])->forceInsert(true);
        
        $database = new StorageDatabase(
            storage: new InMemoryStorage(
                items: [],
                tables: (new Tables())->add('users', ['id', 'name'], 'id'),
            )
        );
        
        $processor = new StorageDatabaseProcessor();
        
        $processor->process(table: $table, database: $database);
        
        $this->assertSame(2, $database->storage()->table('users')->count());
        
        $processor->process(table: $table, database: $database);
        
        $this->assertSame(4, $database->storage()->table('users')->count());        
    }
    
    public function testProcessMethodThrowsProcessExceptionIfNotSupportedDatabase()
    {
        $this->expectException(ProcessException::class);
        
        $database = new PdoDatabase(new PDO('sqlite::memory:'));
        
        $processor = new StorageDatabaseProcessor();
        
        $table = new Table(name: 'users');
        
        $processor->process(table: $table, database: $database);       
    }    
}