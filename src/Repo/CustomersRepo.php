<?php

declare(strict_types=1);

namespace App\Repo;

use Doctrine\ORM\Query\ResultSetMapping;

class CustomersRepo extends EntityRepo
{
    public function getProfitStats(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('customerId', 'customerId', 'integer');
        $rsm->addScalarResult('ordersCount', 'ordersCount', 'integer');
        $rsm->addScalarResult('spent', 'spent', 'float');
        $rsm->addScalarResult('profit', 'profit', 'float');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                o.customer_id as customerId,
                COUNT(*) as ordersCount,
                SUM(o.profit) as profit,
                SUM(o.quantity * o.price) as spent
            FROM orders o
            GROUP BY o.customer_id',
            $rsm
        );

        $rawData = $query->getResult();
        $result = [];
        foreach ($rawData as $rawItem) {
            $result[$rawItem['customerId']] = $rawItem;
        }

        return $result;
    }
}
