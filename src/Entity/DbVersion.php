<?php

namespace App\Entity;

use App\Repo\DbVersionsRepo;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DbVersionsRepo::class)]
#[ORM\Table(name: "db_versions")]
class DbVersion
{
    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\Column(type: "datetime", name: "created_at")]
    protected $createdAt;

    #[ORM\Column(type: "string")]
    protected $version;

    public function __construct(?string $version = null)
    {
        if (!is_null($version)) {
            $this->setVersion($version);
        }

        $this->setCreatedAt(new DateTime());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getVersion();
    }
}
