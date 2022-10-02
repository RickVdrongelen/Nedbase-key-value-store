<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SetValueCommandTest extends AbstractKernelTestCase {
    const TEST_KEY = "test";

    private Command $command;
    private CommandTester $commandTester;

    public function setUp() : void {
        parent::setUp();

        // Setup the command and command Tester
        $this->command = $this->application->find('set');
        $this->commandTester = new CommandTester($this->command);
    }

    public function test_it_sets_normal_value() {
        $setValue = rand(1, 1000000);
        $this->storageInterfaceStub->expects($this->once())->method('set')->with(self::TEST_KEY, $setValue);
        $this->commandTester->execute(["value" => $setValue, "key" => self::TEST_KEY]);
        $this->commandTester->assertCommandIsSuccessful();
    }
}