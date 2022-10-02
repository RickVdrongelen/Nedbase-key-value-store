<?php
namespace App\Service;

use App\Contract\FileHandlerInterface;
use App\Exception\KeyNotFoundException;
use Generator;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Filesystem\Filesystem;

use function PHPSTORM_META\map;

class JsonlFileHandler implements FileHandlerInterface {
    const FILE_EXT = ".jsonl";
    private mixed $filePointer;
    private string $path;

    public function setFilePointer(mixed $filePointer) {
        $this->filePointer = $filePointer;
        return $this;
    }

    public function setFilePath(string $path) {
        $this->path = $path;
        return $this;
    }

    static function init(string $path): static
    {
        $fileSystem = new FileSystem();
        $fileName = $path.self::FILE_EXT;
        // Check whether the base path exists
        if(!$fileSystem->exists(Path::normalize(dirname($fileName)))) {
            $fileSystem->mkdir(Path::normalize(dirname($fileName)));
        }

        // Check whether the file exists
        if(!$fileSystem->exists($fileName)) {
            $fileSystem->touch($fileName);
        }

        return (new static())
            ->setFilePointer(fopen($fileName, "r+"))
            ->setFilePath($fileName);
    }

    public function getValue(string $key): mixed
    {
        if(!$this->filePointer) {
            return null;
        }

        while(($line = fgets($this->filePointer)) !== false) {
            $json = json_decode($line, true);
            if(isset($json["value"]) && isset($json["key"]) && $json["key"] == $key) {
                rewind($this->filePointer);
                return $json["value"];
            }
        }

        rewind($this->filePointer);
        throw new KeyNotFoundException($key);
    }

    public function setValue(string $key, mixed $value): bool
    {
        $newJson = json_encode([
            "key" => $key,
            "value" => $value
        ])."\n";

        if(!$this->doesExist($key)) {
            // value does not yet exists, append to the file
            fseek($this->filePointer, 0, SEEK_END);
            $res = fputs($this->filePointer, $newJson) > 0;
            rewind($this->filePointer);
            return $res;
        }

        // Data does already exist, overwrite old value
        if(flock($this->filePointer, LOCK_EX)) {
            // We need to create a temporary file, so open another writing file
            $writing  = $this->getTempFile();

            while(!(feof($this->filePointer))) {
                $line = fgets($this->filePointer);
                $json = json_decode($line, true);
                if(isset($json["key"]) && $json["key"] === $key) {
                    $line = $newJson;
                }
                fputs($writing, $line);
            }

            $this->resetTempFile($writing);
        } 

        return true;
    }

    public function removeValue(string $key): bool
    {
        if(flock($this->filePointer, LOCK_SH)) {
            $writing = $this->getTempFile();
            while(($line = fgets($this->filePointer)) !== false) {
                $json = json_decode($line, true);
                if(!(isset($json["key"]) && $json["key"] === $key)) {
                    $res = fputs($writing, json_encode($json)."\n") > 0;
                }
            };

            $this->resetTempFile($writing);
        }

        return $res ?? false;
    }

    public function doesExist(string $key): bool
    {
        if(!$this->filePointer) {
            return false;
        }
        
        while(($line = fgets($this->filePointer)) !== false) {
            $json = json_decode($line, true);
            if(isset($json["key"]) && $json["key"] === $key) {
                rewind($this->filePointer);
                return true;
            }
        }

        return false;
    }

    public function getFileExtension() {
        return self::FILE_EXT;
    }

    public function removeData() {
        $fileSystem = new Filesystem();
        
        $fileSystem->remove($this->path);
    }

    public function merge(string $toFileName)
    {
        if(!$this->filePointer) {
            return false;
        }

        $writing = fopen($toFileName, "r+");
        fseek($writing, 0, SEEK_END);

        while(($line = fgets($this->filePointer)) !== false) {
            fputs($writing, $line);
        }

        fclose($this->filePointer);
    }

    private function getTempFile() {
        return fopen($this->path.".tmp", 'w');
    }

    private function resetTempFile(mixed $writing) {
        fclose($this->filePointer);
        fclose($writing);
        rename($this->path.".tmp", $this->path);
        $this->filePointer = fopen($this->path, "r+");
    }
}