<?php

namespace App\Entity;

use App\Repository\StockMovementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockMovementRepository::class)]
class StockMovement
{
    public const TYPE_INCREASE = 'increase';
    public const TYPE_DECREASE = 'decrease';
    public const TYPE_SALE = 'sale';
    public const TYPE_RETURN = 'return';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_INITIAL = 'initial';

    public const REASON_ORDER = 'order';
    public const REASON_RETURN = 'return';
    public const REASON_ADJUSTMENT = 'adjustment';
    public const REASON_INVENTORY = 'inventory';
    public const REASON_DAMAGE = 'damage';
    public const REASON_NEW_STOCK = 'new_stock';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Livre::class, inversedBy: 'stockMovements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Livre $livre = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $type = null;

    #[ORM\Column(type: 'integer')]
    private ?int $quantity = null;

    #[ORM\Column(type: 'integer')]
    private ?int $stockBefore = null;

    #[ORM\Column(type: 'integer')]
    private ?int $stockAfter = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Commande $commande = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLivre(): ?Livre
    {
        return $this->livre;
    }

    public function setLivre(?Livre $livre): self
    {
        $this->livre = $livre;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getStockBefore(): ?int
    {
        return $this->stockBefore;
    }

    public function setStockBefore(int $stockBefore): self
    {
        $this->stockBefore = $stockBefore;
        return $this;
    }

    public function getStockAfter(): ?int
    {
        return $this->stockAfter;
    }

    public function setStockAfter(int $stockAfter): self
    {
        $this->stockAfter = $stockAfter;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getFormattedType(): string
    {
        $types = [
            self::TYPE_INCREASE => 'Augmentation',
            self::TYPE_DECREASE => 'Diminution',
            self::TYPE_SALE => 'Vente',
            self::TYPE_RETURN => 'Retour',
            self::TYPE_ADJUSTMENT => 'Ajustement',
            self::TYPE_INITIAL => 'Stock Initial',
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getFormattedReason(): string
    {
        $reasons = [
            self::REASON_ORDER => 'Commande',
            self::REASON_RETURN => 'Retour Client',
            self::REASON_ADJUSTMENT => 'Ajustement Manuel',
            self::REASON_INVENTORY => 'Inventaire',
            self::REASON_DAMAGE => 'Produit Endommagé',
            self::REASON_NEW_STOCK => 'Nouveau Stock',
        ];

        return $reasons[$this->reason] ?? $this->reason;
    }

    public function isPositive(): bool
    {
        return in_array($this->type, [self::TYPE_INCREASE, self::TYPE_RETURN, self::TYPE_INITIAL]);
    }

    public function isNegative(): bool
    {
        return in_array($this->type, [self::TYPE_DECREASE, self::TYPE_SALE, self::TYPE_DAMAGE]);
    }
}
