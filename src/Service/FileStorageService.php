<?php

namespace App\Service;

use App\Contract\FileHandlerInterface;
use App\Contract\StorageInterface;

class FileStorageService implements StorageInterface {
    const DIR = 'var/storage';
    const FILE_NAME = 'key_value';

    protected FileHandlerInterface $persistentFileHandler;

    public function __construct(FileHandlerInterface $persistentFileHandler, protected FileHandlerInterface $transactionFileHandler)
    {
        $this->persistentFileHandler = $persistentFileHandler->init(self::DIR, self::FILE_NAME);
    }

    public function get(string $name) {
        return $this->persistentFileHandler->getValue($name);
    }

    public function set(string $name, $value) : void {
        $this->persistentFileHandler->setValue($name, $value);
    }

    public function exists(string $name): bool
    {
        return $this->persistentFileHandler->doesExist($name);
    }

    public function rollback(): void
    {
        
    }

    public function startTransaction(): void
    {
        
    }

    public function delete(string $name): void
    {
        $this->persistentFileHandler->removeValue($name);
    }

    public function commit(): void
    {
        
    }
}