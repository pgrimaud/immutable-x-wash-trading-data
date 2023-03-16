<?php

namespace App\Command;

use App\Entity\Collection;
use App\Helper\TokenHelper;
use App\Repository\AssetRepository;
use App\Repository\OrderRepository;
use App\Service\Http\ImmutableXClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:trades',
    description: 'Fetch trades from Immutable for a given date',
)]
class TradesCommand extends Command
{
    private array $collections;
    private array $assets;

    public function __construct(
        private readonly ImmutableXClient $immutableXClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly AssetRepository $assetRepository,
        private readonly OrderRepository $orderRepository,
    ) {
        $this->collections = [];
        $this->assets = [];

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

        $io->warning('Fetching trades... This can take some time.');
        $section = $output->section();

        while ($hasRemaining === 1) {
            $apiResults = $this->immutableXClient->getTrades($date, $cursor);

            $progressBar = new ProgressBar($output, count($apiResults['result']));
            $progressBar->setFormat("<fg=green>%message%</>\n %current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s%) %memory:6s%\n");
            $progressBar->setMessage('Starting');
            $progressBar->start();

            foreach ($apiResults['result'] as $apiResult) {
                $progressBar->advance();
                $progressBar->setMessage($apiResult['transaction_id']);
                $progressBar->display();

                $this->saveTrade($apiResult);

                $timestamp = $apiResult['timestamp'];
            }

            $progressBar->finish();
            $progressBar->clear();

            $section->overwrite((100 * $loop) . ' trades fetched (last : ' . $timestamp . ')');

            $cursor = $apiResults['cursor'];
            $hasRemaining = $apiResults['remaining'];

            $loop++;

        }

        $io->success('Done');

        return Command::SUCCESS;
    }

    private function saveTrade(array $apiResult): void
    {
        $collectionAddress = $apiResult['b']['token_address'];
        $tokenId = $apiResult['b']['token_id'];

        // check if collection exists
        if (!isset($this->collections[$collectionAddress])) {
            $collection = new Collection();
            $collection->setName(''); // fetch later
            $collection->setAddress($collectionAddress);

            $this->entityManager->persist($collection);
            $this->entityManager->flush();

            $this->collections[$collectionAddress] = $collection->getId();
        }

        $collectionId = $this->collections[$collectionAddress];

        if (!isset($this->assets[$collectionAddress][$tokenId])) {
            $assetId = $this->assetRepository->optimizedSave($collectionId, $tokenId);
            $this->assets[$collectionAddress][$tokenId] = $assetId;
        } else {
            $assetId = $this->assets[$collectionAddress][$tokenId];
        }


        if (isset($apiResult['a']['token_address'])) {
            $token = TokenHelper::getTokenName($apiResult['a']['token_address']);
            $decimals = $apiResult['a']['token_address'] === '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48' ? 6 : 18;
        } else {
            $token = 'ETH';
            $decimals = 18;
        }

        $price = (float)bcdiv($apiResult['a']['sold'], (string)(10 ** $decimals), 18);

        // insert transfer
        $this->orderRepository->optimizedTradeSave(
            $assetId,
            $price,
            $token,
            $apiResult['timestamp'],
            $apiResult['b']['order_id'],
            $apiResult['a']['order_id'],
            $apiResult['transaction_id'],
        );
    }
}
