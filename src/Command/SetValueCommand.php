<?php
namespace App\Command;

use App\Contract\StorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "SET")]
class SetValueCommand extends Command {
    public function __construct(public StorageInterface $storageInterface)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this   
            ->addArgument("key", InputArgument::REQUIRED, "What is the key to save the value to?")
            ->addArgument("value", InputArgument::REQUIRED, "What value do you want to save?");
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->storageInterface->set($input->getArgument("key"), $input->getArgument("value"));
        return Command::SUCCESS;
    }
}