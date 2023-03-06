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
use Tobento\Service\Database\Storage\StorageDatabase;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Storage\StorageInterface;
use Tobento\Service\Storage\InMemoryStorage;

/**
 * StorageDatabaseTest
 */
class StorageDatabaseTest extends TestCase
{
    public function testThatImplementsDatabaseInterface()
    {
        $database = new StorageDatabase(
            storage: new InMemoryStorage([]),
            name: 'name',
        );
        
        $this->assertInstanceOf(DatabaseInterface::class, $database);
    }
    
    public function testThatImplementsStorageDatabaseInterface()
    {
        $database = new StorageDatabase(
            storage: new InMemoryStorage([]),
            name: 'name',
        );
        
        $this->assertInstanceOf(StorageDatabaseInterface::class, $database);
    }
    
    public function testNameMethod()
    {
        $database = new StorageDatabase(
            storage: new InMemoryStorage([]),
            name: 'name',
        );
        
        $this->assertSame('name', $database->name());
    }
    
    public function testConnectionMethodReturnsStorageInterface()
    {
        $database = new StorageDatabase(
            storage: new InMemoryStorage([]),
            name: 'name',
        );
        
        $this->assertInstanceOf(
            StorageInterface::class,
            $database->connection()
        );
    }
    
    public function testParameterMethod()
    {
        $database = new StorageDatabase(
            storage: new InMemoryStorage([]),
            name: 'name',
        );
        
        $this->assertSame(
            null,
            $database->parameter('foo')
        );
        
        $this->assertSame(
            'default',
            $database->parameter('foo', 'default')
        );
    }
    
    public function testStorageMethod()
    {
        $database = new StorageDatabase(
            storage: new InMemoryStorage([]),
            name: 'name',
        );
        
        $this->assertInstanceOf(
            StorageInterface::class,
            $database->storage()
        );
    }
}