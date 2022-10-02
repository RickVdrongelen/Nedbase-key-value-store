<?php
namespace App\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteValueCommandTest extends AbstractKernelTestCase {
    const TEST_KEY = 'test';

    private Command $command;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        parent::setUp();

        $this->command = $this->application->find('DEL');
        $this->commandTester = new CommandTester($this->command);
    }

    public function test_it_deletes_normal_value() {
        $this->storageInterfaceStub->expects($this->once())->method('delete')->with(self::TEST_KEY);
        $this->commandTester->execute(["key" => self::TEST_KEY]);

        $this->commandTester->assertCommandIsSuccessful();
    }
}