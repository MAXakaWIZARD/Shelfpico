<?php

namespace App\Entity;

use App\Repo\CustomersRepo;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
#[ORM\Entity(repositoryClass: CustomersRepo::class)]
#[ORM\Table(name: "customers")]
class Customer
{
    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\Column(type: "datetime", name: "created_at")]
    protected $createdAt;

    #[ORM\Column(type: "string", length: 255)]
    protected $name = '';

    #[ORM\Column(type: "string", length: 20)]
    protected $phone = '';

    #[ORM\Column(type: "string", name: "shipment_info", length: 255)]
    protected $shipmentInfo = '';

    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    protected $deposit;

    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    protected $debt;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
        $this->setDebt(0);
        $this->setDeposit(0);
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

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeposit(): int
    {
        return $this->deposit;
    }

    public function setDeposit(int $deposit): self
    {
        $this->deposit = $deposit;
        return $this;
    }

    public function getDebt(): int
    {
        return $this->debt;
    }

    public function setDebt(int $debt): self
    {
        $this->debt = $debt;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getShipmentInfo(): string
    {
        return $this->shipmentInfo;
    }

    public function setShipmentInfo(string $shipmentInfo): self
    {
        $this->shipmentInfo = $shipmentInfo;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
