<?php

namespace App\Repository;

use App\Entity\Collection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collection>
 *
 * @method Collection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collection[]    findAll()
 * @method Collection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collection::class);
    }

    public function save(Collection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Collection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOptimizedCollections(): array
    {
        return call_user_func_array(function (...$items) {
            $results = [];
            foreach ($items as $item) {
                $results[$item['address']] = (int)$item['id'];
            }
            return $results;
        }, $this->createQueryBuilder('c')
            ->select('c.id, c.address')
            ->getQuery()
            ->getArrayResult());
    }

    public function findWithoutName()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getResult();
    }
}
