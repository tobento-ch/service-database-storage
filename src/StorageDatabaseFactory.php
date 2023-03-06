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

use Tobento\Service\Database\DatabaseFactoryInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\DatabasesInterface;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\DatabaseException;
use Tobento\Service\Storage\JsonFileStorage;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\Storage\PdoMySqlStorage;
use Tobento\Service\Storage\PdoMariaDbStorage;

/**
 * StorageDatabaseFactory
 */
class StorageDatabaseFactory implements DatabaseFactoryInterface
{
    /**
     * Create a new StorageDatabaseFactory.
     *
     * @param DatabasesInterface $databases
     */
    public function __construct(
        protected DatabasesInterface $databases,
    ) {}
    
    /**
     * Create a new Database based on the configuration.
     *
     * @param string $name Any database name.
     * @param array $config Configuration data.
     * @return DatabaseInterface
     * @throws DatabaseException
     */
    public function createDatabase(string $name, array $config = []): DatabaseInterface
    {
        if (!isset($config['storage'])) {
            throw new DatabaseException($name, 'Missing "storage" config.');
        }
        
        if ($config['storage'] === JsonFileStorage::class) {
            
            if (!isset($config['dir'])) {
                throw new DatabaseException($name, 'Missing "dir" config.');
            }
            
            $storage = new JsonFileStorage(dir: $config['dir']);
            
            return new StorageDatabase($storage, $name);
        }
        
        if ($config['storage'] === PdoMySqlStorage::class) {
            if (!isset($config['database'])) {
                throw new DatabaseException($name, 'Missing "database" config.');
            }
            
            $database = $this->databases->get($config['database']);
            
            if (!$database instanceof PdoDatabaseInterface) {
                throw new DatabaseException(
                    $name,
                    'Storage "database" config needs to be a PdoDatabase!'
                );
            }

            $storage = new PdoMySqlStorage($database->pdo());
            
            return new StorageDatabase($storage, $name);
        }
        
        if ($config['storage'] === PdoMariaDbStorage::class) {
            if (!isset($config['database'])) {
                throw new DatabaseException($name, 'Missing "database" config.');
            }
            
            $database = $this->databases->get($config['database']);
            
            if (!$database instanceof PdoDatabaseInterface) {
                throw new DatabaseException(
                    $name,
                    'Storage "database" config needs to be a PdoDatabase!'
                );
            }

            $storage = new PdoMariaDbStorage($database->pdo());
            
            return new StorageDatabase($storage, $name);
        }
        
        if ($config['storage'] === InMemoryStorage::class) {
            return new StorageDatabase(new InMemoryStorage(items: []), $name);
        }
        
        throw new DatabaseException($name, 'Could not create storage database!');
    }
}