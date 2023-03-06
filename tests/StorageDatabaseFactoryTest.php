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
use Tobento\Service\Database\Storage\StorageDatabaseInterface;
use Tobento\Service\Database\Storage\StorageDatabaseFactory;
use Tobento\Service\Database\DatabaseFactoryInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\DatabaseException;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\Storage\JsonFileStorage;
use Tobento\Service\Storage\PdoMySqlStorage;
use Tobento\Service\Storage\PdoMariaDbStorage;
use Tobento\Service\Storage\StorageException;
use PDO;

/**
 * StorageDatabaseFactoryTest
 */
class StorageDatabaseFactoryTest extends TestCase
{
    public function testThatImplementsDatabaseFactoryInterface()
    {
        $factory = new StorageDatabaseFactory(
            databases: new Databases(),
        );
        
        $this->assertInstanceOf(DatabaseFactoryInterface::class, $factory);
    }
    
    public function testCreateDatabaseMethodWithJsonFileStorage()
    {
        $factory = new StorageDatabaseFactory(
            databases: new Databases(),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => JsonFileStorage::class,
                'dir' => __DIR__.'/json-file/',
            ],
        );
        
        $this->assertInstanceOf(DatabaseInterface::class, $database);
        $this->assertInstanceOf(StorageDatabaseInterface::class, $database);
    }
    
    public function testCreateDatabaseMethodWithInMemoryStorage()
    {
        $factory = new StorageDatabaseFactory(
            databases: new Databases(),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => InMemoryStorage::class,
            ],
        );
        
        $this->assertInstanceOf(DatabaseInterface::class, $database);
        $this->assertInstanceOf(StorageDatabaseInterface::class, $database);
    }
    
    public function testCreateDatabaseMethodWithPdoMySqlStorageThrowsDatabaseExceptionIfDatabaseConfigIsMissing()
    {
        $this->expectException(DatabaseException::class);
        
        $factory = new StorageDatabaseFactory(
            databases: new Databases(),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => PdoMySqlStorage::class,
            ],
        );
    }
    
    public function testCreateDatabaseMethodWithPdoMySqlStorageThrowsDatabaseExceptionIfDatabaseNotFound()
    {
        $this->expectException(DatabaseException::class);
        
        $factory = new StorageDatabaseFactory(
            databases: new Databases(),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => PdoMySqlStorage::class,
                'database' => 'mysql',
            ],
        );
    }
    
    public function testCreateDatabaseMethodWithPdoMySqlStorage()
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL')) {
            $this->markTestSkipped('PdoMySqlStorage tests are disabled');
        }

        $pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_DSN'),
            username: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_USERNAME'),
            password: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_PASSWORD'),
        );
        
        $factory = new StorageDatabaseFactory(
            databases: new Databases(
                new PdoDatabase(pdo: $pdo, name: 'mysql'),
            ),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => PdoMySqlStorage::class,
                'database' => 'mysql',
            ],
        );
        
        $this->assertInstanceOf(DatabaseInterface::class, $database);
        $this->assertInstanceOf(StorageDatabaseInterface::class, $database);
    }
    
    public function testCreateDatabaseMethodWithPdoMariaDbStorageThrowsDatabaseExceptionIfDatabaseConfigIsMissing()
    {
        $this->expectException(DatabaseException::class);
        
        $factory = new StorageDatabaseFactory(
            databases: new Databases(),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => PdoMariaDbStorage::class,
            ],
        );
    }
    
    public function testCreateDatabaseMethodWithPdoMariaDbStorageThrowsDatabaseExceptionIfDatabaseNotFound()
    {
        $this->expectException(DatabaseException::class);
        
        $factory = new StorageDatabaseFactory(
            databases: new Databases(),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => PdoMariaDbStorage::class,
                'database' => 'mysql',
            ],
        );
    }
    
    public function testCreateDatabaseMethodWithPdoMariaDbStorage()
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL')) {
            $this->markTestSkipped('PdoMariaDbStorage tests are disabled');
        }

        $pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_DSN'),
            username: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_USERNAME'),
            password: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_PASSWORD'),
        );
        
        $factory = new StorageDatabaseFactory(
            databases: new Databases(
                new PdoDatabase(pdo: $pdo, name: 'mysql'),
            ),
        );
        
        $database = $factory->createDatabase(
            name: 'storage',
            config: [
                'storage' => PdoMariaDbStorage::class,
                'database' => 'mysql',
            ],
        );
        
        $this->assertInstanceOf(DatabaseInterface::class, $database);
        $this->assertInstanceOf(StorageDatabaseInterface::class, $database);
    }
}