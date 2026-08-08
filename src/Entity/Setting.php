<?php

namespace App\Entity;

use App\Enum\ValueType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "settings")]
#[ORM\UniqueConstraint(name: "setting_name_idx", columns: ["name"])]
class Setting
{
    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    #[ORM\GeneratedValue]
    protected $id;

    #[Assert\NotBlank]
    #[ORM\Column(type: "string")]
    protected string $name = '';

    #[ORM\Column(type: "string")]
    protected string $value = '';

    #[ORM\Column(type: "string")]
    protected string $description = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: "string", enumType: ValueType::class)]
    protected ValueType $type = ValueType::String;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            ValueType::Int => (int) $this->value,
            ValueType::Float => (float) $this->value,
            ValueType::String => $this->value,
        };
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): ValueType
    {
        return $this->type;
    }

    public function setType(ValueType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
