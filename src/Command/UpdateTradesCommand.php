<?php

namespace App\Command;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Http\ImmutableXClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-trades',
    description: 'Update trades to fetch sellers and buyers',
)]
class UpdateTradesCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ImmutableXClient $immutableXClient
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('date', InputArgument::REQUIRED, 'Date (format Y-m-d)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dateArg = $input->getArgument('date');

        $date = new \DateTime($dateArg);

        $cursor = null;
        $hasRemaining = 1;
        $loop = 1;
        $timestamp = '';

        $tradesToUpdate = $this->orderRepository->findTradesToUpdate($date);

        $io->warning('Fetching orders... This can take some time.');
        $section = $output->section();

        while ($hasRemaining === 1) {

            $apiResults = $this->immutableXClient->getOrders($date, $cursor);

            $progressBar = new ProgressBar($output, count($apiResults['result']));
            $progressBar->setFormat("<fg=green>%message%</>\n %current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s%) %memory:6s%\n");
            $progressBar->setMessage('Starting');
            $progressBar->start();

            foreach ($apiResults['result'] as $apiResult) {
                $progressBar->advance();
                $progressBar->setMessage($apiResult['order_id']);
                $progressBar->display();

                if (isset($tradesToUpdate[$apiResult['order_id']])) {
                    $this->orderRepository->updateOrder($apiResult['user'], $tradesToUpdate[$apiResult['order_id']]);
                }

                $timestamp = $apiResult['timestamp'];
            }

            $progressBar->finish();
            $progressBar->clear();

            $section->overwrite((100 * $loop) . ' transfers fetched (last : ' . $timestamp . ')');

            $cursor = $apiResults['cursor'];
            $hasRemaining = $apiResults['remaining'];

            $loop++;
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
