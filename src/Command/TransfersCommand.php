<?php

namespace App\Command;

use App\Entity\Collection;
use App\Helper\TokenHelper;
use App\Repository\AssetRepository;
use App\Repository\TransferRepository;
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
    name: 'app:transfers',
    description: 'Fetch transfers from the Immutable',
)]
class TransfersCommand extends Command
{
    private array $collections;
    private array $assets;

    public function __construct(
        private readonly ImmutableXClient $immutableXClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly AssetRepository $assetRepository,
        private readonly TransferRepository $transferRepository,
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

        $this->collections = $this->entityManager->getRepository(Collection::class)->findOptimizedCollections();
        $this->assets = $this->assetRepository->findOptimizedAssetsByCollection();

        $cursor = null;
        $hasRemaining = 1;
        $loop = 1;
        $timestamp = '';

        $io->warning('Fetching transfers... This can take some time.');
        $section = $output->section();

        while ($hasRemaining === 1) {

            $apiResults = $this->immutableXClient->getTransfers($date, $cursor);

            $progressBar = new ProgressBar($output, count($apiResults['result']));
            $progressBar->setFormat("<fg=green>%message%</>\n %current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s%) %memory:6s%\n");
            $progressBar->setMessage('Starting');
            $progressBar->start();

            foreach ($apiResults['result'] as $apiResult) {
                $progressBar->advance();
                $progressBar->setMessage($apiResult['transaction_id']);
                $progressBar->display();

                // discard burn
                if ($apiResult['receiver'] === '0x0000000000000000000000000000000000000000') {
                    continue;
                }

                $this->saveTransfer($apiResult);

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

    private function saveTransfer(array $apiResult): void
    {
        if ($apiResult['token']['type'] === 'ERC721') {
            // check if collection exists
            if (!isset($this->collections[$apiResult['token']['data']['token_address']])) {
                $collection = new Collection();
                $collection->setName(''); // fetch later
                $collection->setAddress($apiResult['token']['data']['token_address']);

                $this->entityManager->persist($collection);
                $this->entityManager->flush();

                $this->collections[$apiResult['token']['data']['token_address']] = $collection->getId();
            }

            $collectionId = $this->collections[$apiResult['token']['data']['token_address']];

            // check if asset exists
            $assetTokenId = $apiResult['token']['data']['token_id'];

            if (!isset($this->assets[$apiResult['token']['data']['token_address']][$assetTokenId])) {
                $assetId = $this->assetRepository->optimizedSave($collectionId, $assetTokenId);
                $this->assets[$apiResult['token']['data']['token_address']][$assetTokenId] = $assetId;
            } else {
                $assetId = $this->assets[$apiResult['token']['data']['token_address']][$assetTokenId];
            }

            // insert transfer
            $this->transferRepository->optimizedSave(
                $apiResult['user'],
                $apiResult['receiver'],
                $apiResult['timestamp'],
                $apiResult['transaction_id'],
                $assetId,
            );
        } else {
            if ($apiResult['token']['type'] === 'ETH') {
                $token = 'ETH';
            } else {
                $token = TokenHelper::getTokenName($apiResult['token']['data']['token_address']);
            }

            // insert as ERC20 or ETH transfer
            $this->transferRepository->optimizedSave(
                $apiResult['user'],
                $apiResult['receiver'],
                $apiResult['timestamp'],
                $apiResult['transaction_id'],
                null,
                $apiResult['token']['data']['quantity'],
                $token,
            );
        }
    }
}
