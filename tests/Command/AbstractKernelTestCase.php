<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Contract\StorageInterface;
use App\Exception\KeyNotFoundException;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;

abstract class AbstractKernelTestCase extends KernelTestCase {
    protected KernelInterface $kernelInterface;
    protected MockObject $storageInterfaceStub;
    protected Application $application;
    protected ContainerInterface $container;
 
    /**
     * Setup the kernel to execute the console commands command
     */
    public function setUp() : void {
        $this->kernelInterface = self::bootKernel();
        $this->application = new Application($this->kernelInterface);
        $this->container = static::getContainer();
        
        // Add a mock service as StorageInterface
        $this->storageInterfaceStub = $this->createMock(StorageInterface::class);
        $this->container->set(StorageInterface::class, $this->storageInterfaceStub);    

        parent::setUp();
    }
}