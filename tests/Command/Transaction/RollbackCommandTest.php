<?php
namespace App\Tests\Command\Transaction;

use App\Tests\Command\AbstractKernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RollbackCommandTest extends AbstractKernelTestCase {
    const TEST_KEY = 'test';

    private Command $command;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        parent::setUp();

        $this->command = $this->application->find('ROLLBACK');
        $this->commandTester = new CommandTester($this->command);
    }

    public function test_it_commits() {
        $this->storageInterfaceStub->expects($this->once())->method('rollback');
        $this->commandTester->execute([]);
        $this->commandTester->assertCommandIsSuccessful();
    }
}