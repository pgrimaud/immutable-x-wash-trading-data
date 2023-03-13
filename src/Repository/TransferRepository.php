<?php

namespace App\Repository;

use App\Entity\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transfer>
 *
 * @method Transfer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transfer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transfer[]    findAll()
 * @method Transfer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transfer::class);
    }

    public function save(Transfer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transfer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function optimizedSave(
        string $sender,
        string $receiver,
        string $timestamp,
        int $transactionId,
        int $assetId = null,
        string $quantity = '1',
        string $token = ''
    ): void {

        $connection = $this->getEntityManager()->getConnection();

        $sql = 'INSERT IGNORE INTO `transfer` 
                (`asset_id`, `quantity`, `token`, `sender`, `receiver`, `date`, `internal_id`) 
                VALUES (:assetId, :quantity, :token, :sender, :receiver, :date, :transaction_id)';

        $stmt = $connection->prepare($sql);
        $stmt->executeQuery([
            'assetId' => $assetId,
            'quantity' => $quantity,
            'token' => $token,
            'sender' => $sender,
            'receiver' => $receiver,
            'date' => $timestamp,
            'transaction_id' => $transactionId,
        ]);
    }
}
