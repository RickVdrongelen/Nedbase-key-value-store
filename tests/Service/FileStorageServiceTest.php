<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Contract\StorageInterface;
use App\Exception\KeyNotFoundException;
use App\Service\FileStorageService;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class FileStorageServiceTest extends KernelTestCase 
{
    protected KernelInterface $kernelInterface;
    protected Application $application;
    protected ContainerInterface $container;
    protected FileStorageService $fileStorageService;

    public function setUp() : void
    {
        // Clear the storage folder
        $fileSystem = new Filesystem();
        $fileSystem->remove(Path::normalize("var/storage"));

        $this->kernelInterface = self::bootKernel();
        $this->application = new Application($this->kernelInterface);
        $this->container = static::getContainer();
        $this->fileStorageService = $this->container->get(FileStorageService::class);
        
        parent::setUp();
    }

    public function test_it_sets_and_gets_value() {
        $key = "test";
        $val = rand(1, 1000000);

        $this->assertNull($this->fileStorageService->set($key, $val));
        $this->assertEquals($val, $this->fileStorageService->get($key));
    }

    public function test_it_updates_value() {
        $key = "test";
        $initValue = rand(1, 100);
        $newValue = rand(100, 10000);

        $this->assertNull($this->fileStorageService->set($key, $initValue));
        $this->assertEquals($initValue, $this->fileStorageService->get($key));
        $this->assertNull($this->fileStorageService->set($key, $newValue));
        $this->assertEquals($newValue, $this->fileStorageService->get($key));
    }

    public function test_it_removes_value() {
        $doNotRemoveKey = "do-not-remove";
        $doNotRemoveVal = rand(1, 100000);
        $removeKey = "remove";
        $removeVal = rand(1, 1000);

        $this->assertNull($this->fileStorageService->set($doNotRemoveKey, $doNotRemoveVal));
        $this->assertEquals($doNotRemoveVal, $this->fileStorageService->get($doNotRemoveKey));
        $this->assertNull($this->fileStorageService->set($removeKey, $removeVal));
        $this->assertEquals($removeVal, $this->fileStorageService->get($removeKey));
        $this->assertNull($this->fileStorageService->delete($removeKey));
        $this->expectException(KeyNotFoundException::class);
        $this->fileStorageService->get($removeKey);
    }

    public function test_exists_return_false() {
        $key = "does-not-exists";

        $this->assertFalse($this->fileStorageService->exists($key));
    }

    public function test_it_starts_and_rollback_new_transaction() {
        $nonTransactionKey = "not-transaction";
        $nonTransactionValue = rand(1, 100);

        $transactionKey = "transaction";
        $transactionValue = rand(100, 1000);
        
        $this->fileStorageService->set($nonTransactionKey, $nonTransactionValue);
        $this->assertEquals($nonTransactionValue, $this->fileStorageService->get($nonTransactionKey));

        $this->fileStorageService->startTransaction();
        $this->fileStorageService->set($transactionKey, $transactionValue);
        $this->assertEquals($transactionValue, $this->fileStorageService->get($transactionKey));
        $this->fileStorageService->rollback();
        $this->assertEquals($nonTransactionValue, $this->fileStorageService->get($nonTransactionKey));
    }

    public function test_it_starts_and_commits_new_transaction() {
        $nonTransactionKey = "not-transaction";
        $nonTransactionValue = rand(1, 100);

        $transactionKey = "transaction";
        $transactionValue = rand(100, 1000);
        
        $this->fileStorageService->set($nonTransactionKey, $nonTransactionValue);
        $this->assertEquals($nonTransactionValue, $this->fileStorageService->get($nonTransactionKey));

        $this->fileStorageService->startTransaction();
        $this->fileStorageService->set($transactionKey, $transactionValue);
        $this->assertEquals($transactionValue, $this->fileStorageService->get($transactionKey));
        $this->fileStorageService->commit();
        $this->assertEquals($nonTransactionValue, $this->fileStorageService->get($nonTransactionKey));
        $this->assertEquals($transactionValue, $this->fileStorageService->get($transactionKey));
    }
}