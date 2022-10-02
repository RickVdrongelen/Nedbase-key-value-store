<?php
namespace App\Command;

use App\Contract\StorageInterface;
use App\Exception\KeyNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "GET")]
class GetValueCommand extends Command {
    public function __construct(protected StorageInterface $storageInterface)
    {
        parent::__construct();    
    }

    protected function configure()
    {
        $this->addArgument('value', InputArgument::REQUIRED, 'What value do you need?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       try {
            $value = $this->storageInterface->get($input->getArgument("value"));
            $output->writeln($value);
            return Command::SUCCESS;
       } catch(KeyNotFoundException $e) {
            $output->writeln("<error>ERR: {$e}</error>");
            return Command::FAILURE;
       }
    }
}