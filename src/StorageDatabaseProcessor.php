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

use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Processor\ProcessException;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Storage\StorageInterface;
use Tobento\Service\Storage;
use Tobento\Service\Iterable\Iter;
use Throwable;

/**
 * StorageDatabaseProcessor
 */
class StorageDatabaseProcessor implements ProcessorInterface
{
    /**
     * The supported storages. If no proceesor is specified
     * it will use use just process the table items
     * using the storeItems() method from the storage.
     *
     * @var array<string, null|string>
     */
    protected array $supportedStorages = [
        Storage\PdoMySqlStorage::class => PdoMySqlProcessor::class,
        Storage\PdoMariaDbStorage::class => PdoMySqlProcessor::class,
        Storage\JsonFileStorage::class => null,
        Storage\InMemoryStorage::class => null,
    ];
    
    /**
     * Returns true if the processor supports the database, otherwise false.
     *
     * @param DatabaseInterface $database
     * @return bool
     */
    public function supportsDatabase(DatabaseInterface $database): bool
    {
        if (! $database instanceof StorageDatabaseInterface) {
            return false;
        }
        
        return in_array($database->storage()::class, array_keys($this->supportedStorages));
    }
    
    /**
     * Process a table schema for the specified database.
     *
     * @param Table $table
     * @param DatabaseInterface $database
     * @return void
     *
     * @throws ProcessException
     * @psalm-suppress UndefinedInterfaceMethod Gets handled by supportsDatabase check
     */
    public function process(Table $table, DatabaseInterface $database): void
    {
        if (! $this->supportsDatabase($database)) {
            throw new ProcessException('Unsupported Storage Database');
        }
        
        $storage = $database->storage();
        
        if (! array_key_exists($storage::class, $this->supportedStorages)) {
            throw new ProcessException('Unsupported Storage Database');
        }
        
        $processor = $this->supportedStorages[$storage::class];
        
        if (is_null($processor)) {
            $this->processStorage($table, $storage);
            return;
        }
        
        if ($processor === PdoMySqlProcessor::class) {
            (new PdoMySqlProcessor())->process(
                table: $table,
                database: new PdoDatabase(pdo: $storage->pdo(), name: 'name'),
            );
            
            return;
        }
        
        throw new ProcessException('Unsupported storage database processor');
    }
    
    /**
     * Process storage.
     *
     * @param Table $table
     * @param StorageInterface $storage
     * @return void
     *
     * @throws ProcessException
     */
    protected function processStorage(Table $table, StorageInterface $storage): void
    {
        if ($table->dropping()) {
            $this->processDeleteTable($table, $storage);
            return;
        }
        
        if (is_null($table->getItems())) {
            return;
        }
        
        // if not forcing insert, insert only if there are not items yet.
        if (
            ! $table->getItems()->forcingInsert()
            && ($storage->table($table->getName())->count() > 0)
        ) {
            return;
        }        
        
        try {
            
            if ($table->getItems()->withTransaction()) {
                
                $storage->transaction(function(StorageInterface $storage) use ($table) {
                    
                    if ($table->getItems()->forcingInsert()) {
                        $items = $this->mergeTableItemsWithStoredItems($table, $storage);
                        $storage->storeItems(table: $table->getName(), items: $items);
                        return;
                    }
                    
                    $storage->storeItems(table: $table->getName(), items: $table->getItems());
                });
                
                return;
            }
            
            if ($table->getItems()->forcingInsert()) {
                $items = $this->mergeTableItemsWithStoredItems($table, $storage);
                $storage->storeItems(table: $table->getName(), items: $items);
                return;
            }
            
            $storage->storeItems(table: $table->getName(), items: $table->getItems());
            
        } catch (Throwable $e) {
            throw new ProcessException($e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Process delete table from storage.
     *
     * @param Table $table
     * @param StorageInterface $storage
     * @return void
     *
     * @throws ProcessException
     */
    protected function processDeleteTable(Table $table, StorageInterface $storage): void
    {
        try {
            
            if ($table->getItems()?->withTransaction()) {
                
                $storage->transaction(function(StorageInterface $storage) use ($table) {
                    $storage->deleteTable($table->getName());
                });
                
                return;
            }
            
            $storage->deleteTable($table->getName());

        } catch (Throwable $e) {
            throw new ProcessException($e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Returns the merged the table items with the fetched storage items.
     *
     * @param Table $table
     * @param StorageInterface $storage
     * @return array
     */
    protected function mergeTableItemsWithStoredItems(Table $table, StorageInterface $storage): array
    {
        $items = Iter::toArray($storage->fetchItems($table->getName()));
        $tableItems = Iter::toArray($table->getItems());
        return array_merge($items, $tableItems);
    }
}