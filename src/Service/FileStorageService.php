<?php

namespace App\Service;

use App\Contract\FileHandlerInterface;
use App\Contract\StorageInterface;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class FileStorageService implements StorageInterface {
    const DIR = 'var/storage';
    const FILE_NAME = 'key_value';

    protected FileHandlerInterface $activeFileHandler;
    protected bool $inTransaction = false;

    public function __construct(FileHandlerInterface $fileHandler, protected Filesystem $filesystem)
    {
        $this->activeFileHandler = $this->getActiveFileHandler($fileHandler);
    }

    public function get(string $name) {
        return $this->activeFileHandler->getValue($name);
    }

    public function set(string $name, $value) : void {
        $this->activeFileHandler->setValue($name, $value);
    }

    public function exists(string $name): bool
    {
        return $this->activeFileHandler->doesExist($name);
    }

    public function rollback(): void
    {
        if($this->inTransaction) {
            $this->activeFileHandler->removeData();
            $this->activeFileHandler = $this->getActiveFileHandler($this->activeFileHandler);
        }
    }

    public function startTransaction(): void
    {
        $finder = $this->getFilesInTransactionFinder();
        // Create a unique temporary transaction file name
        $newTransactionNumber = $finder ? $finder->count()+1 : 1;
        $tmpTransactionName = Path::join($this->getTransactionsFolder(), self::FILE_NAME."_$newTransactionNumber");
        $this->activeFileHandler->init($tmpTransactionName);        
        $this->activeFileHandler = $this->getActiveFileHandler($this->activeFileHandler);
    }

    public function delete(string $name): void
    {
        $this->activeFileHandler->removeValue($name);
    }

    public function commit(): void
    {
        if($this->inTransaction) {
            $this->activeFileHandler->merge(Path::join(self::DIR, self::FILE_NAME.$this->activeFileHandler->getFileExtension()));
            $this->activeFileHandler->removeData();
            $this->activeFileHandler = $this->getActiveFileHandler($this->activeFileHandler);
        }
    }

    private function getActiveFileHandler(FileHandlerInterface $fileHandler) {
        $finder = $this->getFilesInTransactionFinder();
        // When the count of files in the transactions folder is higher than zero it means that a transaction is active
        if($finder && $finder->count() > 0) {
            $files = iterator_to_array($finder->sort(function(SplFileInfo $a, SplFileInfo $b) {
                $aExploded = explode("_", $a->getFilename());
                $bExploded = explode("_", $b->getFilename());

                return intval(end($aExploded) > end($bExploded));
            }));

            /** @var SplFileInfo $file */
            $file = end($files);
            $this->inTransaction = true;
            // Set the active file handler to the latest transaction file
            return $fileHandler->init(Path::join($file->getPath(), $file->getBasename($fileHandler->getFileExtension())));
        }

        return $fileHandler->init(Path::join(self::DIR, self::FILE_NAME));
    }

    private function getTransactionsFolder() {
        return Path::join(self::DIR, "transactions");
    }

    private function getFilesInTransactionFinder() : bool|Finder {
        $finder = new Finder();
        try {
            return $finder->files()->in($this->getTransactionsFolder());
        } catch(DirectoryNotFoundException $e) {
            return false;
        }
    }
}