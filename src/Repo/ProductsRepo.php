<?php

declare(strict_types=1);

namespace App\Repo;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Restock;
use App\Entity\Tag;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\ResultSetMapping;

class ProductsRepo extends EntityRepo
{
    private const DEFAULT_SEARCH_PARAMS = [
        'rawTerm' => '',
        'term' => '',
        'compoundTerm' => '',
        'tagId' => 0,
        'tag' => null,
        'orderBy' => 'a.alias',
        'orderDir' => 'asc',
    ];

    public static function getDefaultSearchParams(): array
    {
        return self::DEFAULT_SEARCH_PARAMS;
    }

    public function getTagsStats(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('title', 'title', 'string');
        $rsm->addScalarResult('color', 'color', 'string');
        $rsm->addScalarResult('cnt', 'cnt', 'integer');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT 
                COUNT(*) as cnt, 
                t.id, 
                t.title,
                t.color
            FROM tags t 
            JOIN products_tags mt ON mt.tag_id = t.id
            GROUP BY mt.tag_id
            ORDER BY t.title ASC',
            $rsm
        );

        return $query->getResult();
    }

    /**
     * @return Product[]
     */
    public function getProductsInStock(): array
    {
        $builder = $this->createQueryBuilder('p')
            ->andWhere('p.quantity <> 0')
            ->addOrderBy('p.id', 'asc')
        ;

        return $builder->getQuery()->getResult();
    }

    /**
     * @return Product[]
     */
    public function getProductsForDashboard(): array
    {
        $builder = $this->createQueryBuilder('p')
            ->where('p.quantity <> 0')
            ->orWhere('p.popular = 1')
            ->addOrderBy('p.id', 'asc')
        ;

        return $builder->getQuery()->getResult();
    }

    /**
     * @param Product[] $products
     */
    public function getSalePriceUah(array $products): float
    {
        $price = 0;
        foreach ($products as $product) {
            $price += $product->getQuantity() * $product->getSalePriceUah();
        }

        return $price;
    }

    /**
     * @param Product[] $products
     */
    public function getBuyPriceUah(array $products): float
    {
        $price = 0;
        foreach ($products as $product) {
            $price += $product->getQuantity() * $product->getBuyPriceUah();
        }

        return $price;
    }

    /**
     * @return Product[]
     */
    public function searchByTerm(string $term): array
    {
        $builder = $this->createQueryBuilder('p')
            ->andWhere('p.title LIKE :term')
            ->setParameter('term', "%$term%", ParameterType::STRING)
        ;

        return $builder->getQuery()->getResult();
    }

    /**
     * @return Product[]
     */
    public function searchByTag(Tag $tag): array
    {
        $builder = $this->createQueryBuilder('p')
            ->addOrderBy('p.id', 'asc')
        ;

        $builder
            ->join('p.tags', 'tag')
            ->andWhere($builder->expr()->in('tag.id', [$tag->getId()]));

        return $builder->getQuery()->getResult();
    }

    public function updateStocks(): void
    {
        $em = $this->getEntityManager();

        /** @var RestocksRepo $restocksRepo */
        $restocksRepo = $em->getRepository(Restock::class);

        /** @var OrdersRepo $ordersRepo */
        $ordersRepo = $em->getRepository(Order::class);

        $products = $this->findAll();

        foreach ($products as $product) {
            /** @var Product $product */

            $restockedQty = $restocksRepo->getTotalRestockQuantity($product);
            $shippedQty = $ordersRepo->getTotalShippedQuantity($product);
            $soldQty = $ordersRepo->getTotalSoldQuantity($product);

            $product->setQuantity($restockedQty - $shippedQty);
            $product->setTotalSoldQuantity($soldQty);
            $product->setLastBuyPriceUah((string) intval($restocksRepo->getLastBuyPriceUah($product)));
        }

        $em->flush();
    }
}
