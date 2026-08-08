<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "db_migrations")]
class DbMigration
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 255)]
    protected $version;

    #[ORM\Column(type: "datetime", name: "executed_at", nullable: true)]
    protected $executedAt;

    #[ORM\Column(type: "integer", name: "execution_time", nullable: true)]
    protected $executionTime;
}
