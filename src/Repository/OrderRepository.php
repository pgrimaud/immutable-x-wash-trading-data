<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findTradesToUpdate(\DateTime $date): array
    {
        return call_user_func_array(function (...$items) {
            $results = [];
            foreach ($items as $item) {
                if ($item['seller'] === '') {
                    $results[$item['sellInternalId']] = [
                        'id' => (int)$item['id'],
                        'type' => 'seller'
                    ];
                }

                if ($item['buyer'] === '') {
                    $results[$item['buyInternalId']] = [
                        'id' => (int)$item['id'],
                        'type' => 'buyer'
                    ];
                }
            }
            return $results;
        }, $this->createQueryBuilder('o')
            ->select('o.sellInternalId, o.buyInternalId, o.id, o.seller, o.buyer')
            ->where('o.date LIKE :date')
            ->andWhere('o.seller = :empty OR o.buyer = :empty')
            ->setParameter('date', $date->format('Y-m-d') . '%')
            ->setParameter('empty', '')
            ->getQuery()
            ->getArrayResult());
    }

    public function optimizedTradeSave(
        mixed $assetId,
        float $price,
        string $token,
        string $timestamp,
        int $sellInternalId,
        int $buyInternalId,
        int $transactionId
    ) {
        $connection = $this->getEntityManager()->getConnection();

        $sql = 'INSERT IGNORE INTO `order` 
                (`asset_id`, `quantity`, `token`, `seller`, `buyer`, `date`, `sell_internal_id`, `buy_internal_id`, `transaction_id`) 
                VALUES (:assetId, :quantity, :token, :seller, :buyer, :date, :sell_internal_id, :buy_internal_id, :transaction_id)';

        $stmt = $connection->prepare($sql);
        $stmt->executeQuery([
            'assetId' => $assetId,
            'quantity' => $price,
            'token' => $token,
            'seller' => '',
            'buyer' => '',
            'date' => $timestamp,
            'sell_internal_id' => $sellInternalId,
            'buy_internal_id' => $buyInternalId,
            'transaction_id' => $transactionId,
        ]);
    }

    public function updateOrder(string $user, array $order): void
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = 'UPDATE `order` 
                SET `' . $order['type'] . '` = :user 
                WHERE `id` = :order_id';

        $stmt = $connection->prepare($sql);
        $stmt->executeQuery([
            'user' => $user,
            'order_id' => $order['id']
        ]);
    }
}
