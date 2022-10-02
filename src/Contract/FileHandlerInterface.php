<?php
namespace App\Contract;

interface FileHandlerInterface {
    /**
     * Create or read the file and return a FileHandlerInterface
     */
    static function init(string $basePath, string $fileName) : static;
    public function setValue(string $key, mixed $value) : bool;
    public function removeValue(string $key) : bool;
    public function getValue(string $key) : mixed;
    public function doesExist(string $key) : bool;
}

?>