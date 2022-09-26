<?php
declare(strict_types=1);

interface StorageInterface
{
    /**
     * Return a stored value
     * Throws an exception when the given name does not exist
     *
     * @param string $name
     * @return mixed
     *
     * @throws \Exception
     */
    public function get(string $name);

    /**
     * Save $value with the given name
     *
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, $value): void;

    /**
     * Return true if the given value exists
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool;

    /**
     * Remove a value
     *
     * @param string $name
     */
    public function delete(string $name): void;

    /**
     * Start a new transaction
     */
    public function startTransaction(): void;

    /**
     * Persist all actions done in the current transaction
     * Throws an exception when there is no active transaction to commit
     *
     * @throws \Exception
     */
    public function commit(): void;

    /**
     * Revert all actions done in the current transaction
     * Throws an exception when there is no active transaction to rollback
     *
     * @throws \Exception
     */
    public function rollback(): void;

}