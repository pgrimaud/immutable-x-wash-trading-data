<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Asset $asset = null;

    #[ORM\Column]
    private ?float  $quantity = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $seller = null;

    #[ORM\Column(length: 255)]
    private ?string $buyer = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?int $sellInternalId = null;

    #[ORM\Column]
    private ?int $buyInternalId = null;

    #[ORM\Column(unique: true)]
    private ?int $transactionId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getSeller(): ?string
    {
        return $this->seller;
    }

    public function setSeller(string $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getBuyer(): ?string
    {
        return $this->buyer;
    }

    public function setBuyer(string $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSellInternalId(): ?int
    {
        return $this->sellInternalId;
    }

    public function setSellInternalId(int $sellInternalId): self
    {
        $this->sellInternalId = $sellInternalId;

        return $this;
    }

    public function getBuyInternalId(): ?int
    {
        return $this->buyInternalId;
    }

    public function setBuyInternalId(int $buyInternalId): self
    {
        $this->buyInternalId = $buyInternalId;

        return $this;
    }

    public function getTransactionId(): ?int
    {
        return $this->transactionId;
    }

    public function setTransactionId(int $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }
}
