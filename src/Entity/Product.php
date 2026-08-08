<?php

namespace App\Entity;

use App\Entity\Extra\HasTags;
use App\Repo\ProductsRepo;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
#[ORM\Entity(repositoryClass: ProductsRepo::class)]
#[ORM\Table(name: "products")]
#[ORM\UniqueConstraint(name: "products_sku_idx", columns: ["sku"])]
class Product
{
    use HasTags;

    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\Column(type: "datetime", name: "created_at")]
    protected $createdAt;

    #[ORM\Column(type: "string", length: 100)]
    protected $title = '';

    #[ORM\Column(type: "string", name: "sku", length: 100)]
    protected $sku = '';

    /**
     * Original product URL
     */
    #[ORM\Column(type: "string")]
    protected $url = '';

    /**
     * Atomic URL
     */
    #[ORM\Column(type: "string", name: "atomic_url")]
    protected $atomicUrl = '';

    #[ORM\Column(type: "smallint", options: ["comment" => "Quantity in stock"])]
    protected $quantity = 0;

    #[ORM\Column(type: "smallint", options: ["comment" => "Total sold qty"])]
    protected $totalSoldQuantity = 0;

    /**
     * Last buy price, UAH
     * Calculated from restocks
     */
    #[ORM\Column(
        type: "decimal",
        name: "last_buy_price_uah",
        precision: 20,
        scale: 2,
        options: ["comment" => "Last buy price, UAH"]
    )]
    protected $lastBuyPriceUah = '';

    /**
     * Supplier sale price, UAH
     */
    #[ORM\Column(
        type: "decimal",
        name: "supplier_sale_price_uah",
        precision: 20,
        scale: 2,
        options: ["comment" => "Supplier sale price, UAH"]
    )]
    protected $supplierSalePriceUah = '';

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    protected $popular = false;

    /**
     * @var ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\JoinTable(name: "products_tags")]
    #[ORM\OrderBy(["title" => "ASC"])]
    protected $tags;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
        $this->setTags(new ArrayCollection());
        $this->setLastBuyPriceUah('0');
        $this->setSupplierSalePriceUah('0');
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

    public function setSku(string $sku): self
    {
        $this->sku = $sku;
        return $this;
    }

    public function getSku(): string
    {
        return $this->sku;
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getAtomicUrl(): string
    {
        return $this->atomicUrl;
    }

    public function setAtomicUrl(string $atomicUrl): self
    {
        $this->atomicUrl = $atomicUrl;
        return $this;
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

    public function getLastBuyPriceUah(): string
    {
        return $this->lastBuyPriceUah;
    }

    public function setLastBuyPriceUah(string $lastBuyPriceUah): self
    {
        $this->lastBuyPriceUah = $lastBuyPriceUah;
        return $this;
    }

    public function getSupplierSalePriceUah(): string
    {
        return $this->supplierSalePriceUah;
    }

    public function setSupplierSalePriceUah(string $supplierSalePrice): self
    {
        $this->supplierSalePriceUah = $supplierSalePrice;
        return $this;
    }

    protected function getCommissionRatio(): float
    {
        return (100 + (int) SETTING_PAYMENT_COMISSION_PERCENT) / 100;
    }

    protected function getSupplierDiscountRatio(): float
    {
        return (100 - (int) SETTING_SUPPLIER_DISCOUNT_PERCENT) / 100;
    }

    public function getBuyPriceUah(): string
    {
        return $this->getSupplierSalePriceUah()
            * $this->getSupplierDiscountRatio()
            * $this->getCommissionRatio()
            + SETTING_ITEM_DELIVERY_PRICE_UAH;
    }

    public function getSalePriceUah(): string
    {
        return $this->getSupplierSalePriceUah();
    }

    public function getProfitUah(): int
    {
        return $this->getSalePriceUah() - $this->getBuyPriceUah();
    }

    public function setPopular(bool $popular): self
    {
        $this->popular = $popular;
        return $this;
    }

    public function isPopular(): bool
    {
        return $this->popular;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getTotalSoldQuantity(): int
    {
        return $this->totalSoldQuantity;
    }

    public function setTotalSoldQuantity(int $qty): self
    {
        $this->totalSoldQuantity = $qty;
        return $this;
    }

    public function getPhotoDirPath(bool $absolute = false): string
    {
        return $absolute ? PRODUCT_IMAGES_PATH : '';
    }

    public function getPhotoPath(bool $absolute = false): string
    {
        return $this->getPhotoDirPath($absolute) . '/' . $this->getPhotoFilename();
    }

    public function getPhotoFilename(): string
    {
        return $this->getId() . '.jpg';
    }

    public function hasPhoto(): bool
    {
        return file_exists($this->getPhotoPath(true));
    }

    public function isTuningPart(): bool
    {
        return $this->title === 'Tuning part';
    }

    public function needsRestock(): bool
    {
        if ($this->quantity < 0) {
            return true;
        }

        if ($this->quantity == 0 && $this->isPopular()) {
            return true;
        }

        return false;
    }
}
