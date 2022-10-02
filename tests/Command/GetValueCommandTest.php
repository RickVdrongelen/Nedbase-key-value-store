<?php
namespace App\Tests\Command;

use App\Exception\KeyNotFoundException;
use App\Tests\Command\AbstractKernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GetValueCommandTest extends AbstractKernelTestCase {
    const TEST_KEY = 'test';

    private Command $command;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        parent::setUp();

        // Setup the command and command Tester
        $this->command = $this->application->find('get');
        $this->commandTester = new CommandTester($this->command);
    }

    public function test_it_shows_normal_get_value_test() {
        $returnValue = rand(1, 100000);
        $this->storageInterfaceStub
            ->expects($this->once())
            ->method('get')
            ->with(self::TEST_KEY)
            ->willReturn($returnValue);

        $this->commandTester->execute([
            "value" => self::TEST_KEY
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString($returnValue, $output);
    }

    public function test_it_shows_error_when_key_not_found_test() {
        $exception = new KeyNotFoundException(self::TEST_KEY);
        $this->storageInterfaceStub 
            ->expects($this->once())
            ->method('get')
            ->with(self::TEST_KEY)
            ->willThrowException($exception);
        
            $this->commandTester->execute([
                "value" => self::TEST_KEY
            ]);

            $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
            $output = $this->commandTester->getDisplay();
            $this->assertStringContainsString("ERR: $exception", $output);
    }
}