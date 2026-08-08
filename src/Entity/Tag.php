<?php

namespace App\Entity;

use App\Enum\LabelClass;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "tags")]
#[ORM\UniqueConstraint(name: "tags_title_idx", columns: ["title"])]
class Tag implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\Column(type: "datetime", name: "created_at")]
    protected $createdAt;

    #[ORM\Column(type: "string")]
    protected $title = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: "string", enumType: LabelClass::class)]
    protected LabelClass $color = LabelClass::Secondary;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
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

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getColor(): LabelClass
    {
        return $this->color;
    }

    public function setColor(LabelClass $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'color' => $this->getColor()->value,
        ];
    }
}
