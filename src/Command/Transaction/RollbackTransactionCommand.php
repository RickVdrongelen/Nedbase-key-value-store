<?php

namespace App\Command\Transaction;

use App\Contract\StorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "ROLLBACK")]
class RollbackTransactionCommand extends Command {
    public function __construct(public StorageInterface $storageInterface)
    {
        parent::__construct();
    }

    public function execute(InputInterface $inputInterface, OutputInterface $outputInterface) : int {
        $this->storageInterface->rollback();
        return Command::SUCCESS;
    }
}