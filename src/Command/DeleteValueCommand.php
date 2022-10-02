<?php

namespace App\Command;

use App\Contract\StorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand("DEL")]
class DeleteValueCommand extends Command {
    public function __construct(public StorageInterface $storageInterface)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this   
            ->addArgument("key", InputArgument::REQUIRED, "What key needs to be deleted?");
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->storageInterface->delete($input->getArgument("key"));
        return Command::SUCCESS;
    }
}