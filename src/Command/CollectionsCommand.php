<?php

namespace App\Command;

use App\Repository\CollectionRepository;
use App\Service\Http\ImmutableXClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:collections',
    description: 'Update collections names',
)]
class CollectionsCommand extends Command
{
    public function __construct(
        private readonly CollectionRepository $collectionRepository,
        private readonly ImmutableXClient $immutableXClient
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $collections = $this->collectionRepository->findWithoutName();

        if (count($collections) === 0) {
            $io->warning('Nothing to update');
            return Command::SUCCESS;
        }

        $progressBar = new ProgressBar($output, count($collections));
        $progressBar->setFormat("<fg=green>%message%</>\n %current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s%) %memory:6s%\n");
        $progressBar->setMessage('Starting');
        $progressBar->start();

        foreach ($collections as $collection) {

            $progressBar->setMessage($collection->getAddress());

            $apiResult = $this->immutableXClient->getCollection($collection->getAddress());

            $collection->setName($apiResult['name']);
            $this->collectionRepository->save($collection, true);

            $progressBar->advance();
        }

        $progressBar->finish();

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
