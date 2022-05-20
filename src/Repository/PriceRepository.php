<?php

namespace App\Repository;

use App\Entity\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Price>
 *
 * @method Price|null find($id, $lockMode = null, $lockVersion = null)
 * @method Price|null findOneBy(array $criteria, array $orderBy = null)
 * @method Price[]    findAll()
 * @method Price[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    /**
     * @return Price[]
     */
    public function findPricesByProductId(string $productId): array
    {
//        $conn = $this->getEntityManager()->getConnection();
//
//        $sql = 'SELECT pr.stripe_id
//                FROM price pr JOIN product p on p.id = pr.product_id
//                WHERE p.stripe_id = :productId';
//        $stmt = $conn->prepare($sql);
//
//        return $stmt->executeQuery(['productId' => $productId])->fetchAssociative();
        return $this->createQueryBuilder('price')
            ->select('price.stripeId')
            ->innerJoin('price.product', 'product')
            ->where('product.stripeId = :productId')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $lookupkey
     * @return Price | null
     */
    public function findPricesByLookupKey(string $lookupkey): ?Price
    {
        return $this->findOneBy(['lookupkey' => $lookupkey]);
    }
}
