<?php

namespace App\Repository;

use App\Entity\Asset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Asset>
 *
 * @method Asset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asset[]    findAll()
 * @method Asset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    public function save(Asset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Asset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOptimizedAssetsByCollection(): array
    {
        return call_user_func_array(function (...$items) {
            $results = [];
            foreach ($items as $item) {
                $results[$item['address']][$item['tokenId']] = (int)$item['id'];
            }
            return $results;
        }, $this->createQueryBuilder('a')
            ->select('a.id, a.tokenId, c.address')
            ->join('a.collection', 'c')
            ->getQuery()
            ->getArrayResult());
    }

    public function optimizedSave(int $collectionId, string $assetTokenId): int
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = 'INSERT INTO `asset` (`collection_id`, `token_id`) VALUES (:collectionId, :assetTokenId)';

        $stmt = $connection->prepare($sql);
        $stmt->executeQuery([
            'collectionId' => $collectionId,
            'assetTokenId' => $assetTokenId,
        ]);

        return (int)$connection->lastInsertId();
    }
}
