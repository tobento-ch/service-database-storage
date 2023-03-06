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
 * StorageDatabaseInterface
 */
interface StorageDatabaseInterface
{
    /**
     * Returns the storage.
     *
     * @return StorageInterface
     */
    public function storage(): StorageInterface;
}