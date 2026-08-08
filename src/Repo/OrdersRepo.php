<?php

declare(strict_types=1);

namespace App\Repo;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\ResultSetMapping;

class OrdersRepo extends EntityRepo
{
    public function getTotalProfit(): float
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('profit', 'profit', 'float');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                SUM(o.profit) as profit
            FROM orders o',
            $rsm
        );

        return $query->getSingleResult()['profit'];
    }

    /**
     * Amount of product that left the warehouse
     * Includes maintenance orders (for myself)
     */
    public function getTotalShippedQuantity(Product $product): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('quantity', 'quantity', 'integer');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                SUM(o.quantity) as quantity
            FROM orders o
            WHERE o.product_id = :productId',
            $rsm
        )->setParameter('productId', $product->getId());

        return (int) $query->getSingleResult()['quantity'];
    }

    /**
     * Amount of product that was sold from warehouse
     * Does not include maintenance orders (for myself)
     */
    public function getTotalSoldQuantity(Product $product): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('quantity', 'quantity', 'integer');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                SUM(o.quantity) as quantity
            FROM orders o
            WHERE o.product_id = :productId AND o.profit > 0',
            $rsm
        )->setParameter('productId', $product->getId());

        return (int) $query->getSingleResult()['quantity'];
    }

    public function getTotalSpentAmount(): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount', 'decimal');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                SUM(o.quantity * o.price) as amount
            FROM orders o',
            $rsm
        );

        return (int) $query->getSingleResult()['amount'];
    }

    /**
     * @return Order[]
     */
    public function findByKey(string $key): array
    {
        @list($customerId, $date) = explode('_', $key);

        if (!$customerId || !$date) {
            throw new \InvalidArgumentException('Wrong order key');
        }

        $builder = $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customerId')
            ->andWhere('o.createdAt LIKE :dt')
            ->setParameter('customerId', $customerId, ParameterType::INTEGER)
            ->setParameter('dt', "$date%", ParameterType::STRING)
            ->addOrderBy('o.createdAt', 'desc')
        ;

        return $builder->getQuery()->getResult();
    }
}
