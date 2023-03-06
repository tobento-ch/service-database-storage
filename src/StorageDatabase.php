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

namespace Tobento\Service\Database\Storage;

use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Storage\StorageInterface;

/**
 * StorageDatabase
 */
class StorageDatabase implements DatabaseInterface, StorageDatabaseInterface
{
    /**
     * Create a new StorageDatabase.
     *
     * @param StorageInterface $storage
     * @param string $name
     */
    public function __construct(
        protected StorageInterface $storage,
        protected string $name = 'storage',
    ) {}
    
    /**
     * Returns the database name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Returns the connection.
     *
     * @return mixed
     */
    public function connection(): mixed
    {
        return $this->storage;
    }
    
    /**
     * Returns the value for the specified parameter name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function parameter(string $name, mixed $default = null): mixed
    {
        return $default;
    }
    
    /**
     * Returns the storage.
     *
     * @return StorageInterface
     */
    public function storage(): StorageInterface
    {
        return $this->storage;
    }
}