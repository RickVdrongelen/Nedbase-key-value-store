<?php
namespace App\Service;

use App\Contract\FileHandlerInterface;
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

    static function init(string $basePath, string $fileName): static
    {
        $fileSystem = new FileSystem();
        $fileName = $fileName.self::FILE_EXT;
        // Check whether the base path exists
        if(!$fileSystem->exists(Path::normalize($basePath))) {
            $fileSystem->mkdir(Path::normalize($basePath));
        }

        $joinedFilePath = Path::join($basePath, $fileName);
        // Check whether the file exists
        if(!$fileSystem->exists($joinedFilePath)) {
            $fileSystem->touch($joinedFilePath);
        }

        return (new static())
            ->setFilePointer(fopen($joinedFilePath, "r+"))
            ->setFilePath($joinedFilePath);
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
        return null;
    }

    public function setValue(string $key, mixed $value): bool
    {
        $newJson = json_encode([
            "key" => $key,
            "value" => $value
        ])."\n";

        if($this->getValue($key) === null) {
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