<?php

declare(strict_types=1);

namespace App\Repo;

use App\Entity\Product;
use Doctrine\ORM\Query\ResultSetMapping;

class RestocksRepo extends EntityRepo
{
    public function getTotalRestockQuantity(Product $product): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('quantity', 'quantity', 'integer');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                SUM(r.quantity) as quantity
            FROM restocks r
            WHERE r.product_id = :productId',
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
                SUM(r.quantity * r.price) as amount
            FROM restocks r',
            $rsm
        );

        return (int) $query->getSingleResult()['amount'];
    }

    public function getLastBuyPriceUah(Product $product): string
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('price', 'price', 'decimal');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                r.price
            FROM restocks r
            WHERE r.product_id = :productId
            ORDER BY r.created_at DESC
            LIMIT 1',
            $rsm
        )->setParameter('productId', $product->getId());

        $result = $query->getResult();

        return count($result) ? $result[0]['price'] : '0';
    }
}
