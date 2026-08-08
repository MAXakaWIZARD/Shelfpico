<?php

declare(strict_types=1);

namespace App\Repo;

use Doctrine\ORM\EntityRepository;

class EntityRepo extends EntityRepository
{
    protected function splitMultipleModels(string $term): array
    {
        $modelsTerms = [];

        $parts = explode(',', $term);
        foreach ($parts as $part) {
            $part = trim($part);
            if (!$part) {
                continue;
            }

            $subparts = explode('&', $part);
            foreach ($subparts as $subpart) {
                $subpart = trim($subpart);
                if ($subpart) {
                    $modelsTerms[] = $subpart;
                }
            }
        }

        return $modelsTerms;
    }

    public function getTotalCount(): int
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select($qb->expr()->count('m.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function update(array $columnNames, int $id = 0): void
    {
        $builder = $this->createQueryBuilder('a')
            ->update($this->getEntityName(), 'm')
        ;

        foreach ($columnNames as $columnName => $value) {
            $builder->set('m.' . $columnName, $value);
        }

        if ($id) {
            $builder->where('m.id = :id')
                ->setParameter('id', $id)
            ;
        }

        $builder->getQuery()->execute();
    }
}
