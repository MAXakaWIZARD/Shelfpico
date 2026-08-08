<?php

declare(strict_types=1);

namespace App\Repo;

use App\Entity\DbVersion;

class DbVersionsRepo extends EntityRepo
{
    public function getCurrentVersion(): string
    {
        $builder = $this->createQueryBuilder('d')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(1)
        ;

        $versions = $builder->getQuery()->getResult();
        if (count($versions) > 0) {
            /** @var DbVersion $currentVersion */
            $currentVersion = $versions[0];

            return $currentVersion->getVersion();
        }

        return '';
    }
}
