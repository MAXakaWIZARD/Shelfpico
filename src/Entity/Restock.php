<?php

namespace App\Entity;

use App\Repo\RestocksRepo;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestocksRepo::class)]
#[ORM\Table(name: "restocks")]
class Restock
{
    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\Column(type: "datetime", name: "created_at")]
    protected $createdAt;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected $product;

    #[ORM\Column(type: "decimal", precision: 20, scale: 2, options: ["comment" => "Price, UAH"])]
    protected $price = '';

    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    protected $quantity;

    #[ORM\Column(type: "string", length: 255)]
    protected $note = '';

    #[ORM\Column(type: "string", length: 255)]
    protected $url = '';

    public function __construct(?Product $product = null)
    {
        $this->setCreatedAt(new DateTime());
        $this->setProduct($product);
        $this->setPrice('0');
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

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function setNote($note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->getPrice() * $this->getQuantity();
    }
}
