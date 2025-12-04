<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'panier', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sessionId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, LignePanier>
     */
    #[ORM\OneToMany(targetEntity: LignePanier::class, mappedBy: 'panier', cascade: ['persist', 'remove'])]
    private Collection $lignePaniers;

    #[ORM\ManyToOne(targetEntity: Coupon::class, inversedBy: 'paniers')]
    private ?Coupon $coupon = null;

    public function __construct()
    {
        $this->lignePaniers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, LignePanier>
     */
    public function getLignePaniers(): Collection
    {
        return $this->lignePaniers;
    }

    public function addLignePanier(LignePanier $lignePanier): static
    {
        if (!$this->lignePaniers->contains($lignePanier)) {
            $this->lignePaniers->add($lignePanier);
            $lignePanier->setPanier($this);
        }

        return $this;
    }

    public function removeLignePanier(LignePanier $lignePanier): static
    {
        if ($this->lignePaniers->removeElement($lignePanier)) {
            // set the owning side to null (unless already changed)
            if ($lignePanier->getPanier() === $this) {
                $lignePanier->setPanier(null);
            }
        }

        return $this;
    }

    public function getTotalItems(): int
    {
        $total = 0;
        foreach ($this->lignePaniers as $lignePanier) {
            $total += $lignePanier->getQuantity();
        }
        return $total;
    }

    public function getTotalAmount(): float
    {
        $total = 0.0;
        foreach ($this->lignePaniers as $lignePanier) {
            $total += (float) $lignePanier->getTotal();
        }
        return $total;
    }

    public function getFormattedTotalAmount(): string
    {
        return number_format($this->getTotalAmount(), 2, '.', ' ') . ' TND';
    }

    public function isEmpty(): bool
    {
        return $this->lignePaniers->isEmpty();
    }

    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(?Coupon $coupon): static
    {
        $this->coupon = $coupon;

        return $this;
    }

    public function getDiscountAmount(): float
    {
        if (!$this->coupon) {
            return 0.0;
        }

        return $this->coupon->calculateDiscount($this->getTotalAmount());
    }

    public function getTotalAmountWithDiscount(): float
    {
        return $this->getTotalAmount() - $this->getDiscountAmount();
    }

    public function getFormattedDiscountAmount(): string
    {
        return number_format($this->getDiscountAmount(), 2, '.', ' ') . ' TND';
    }

    public function getFormattedTotalAmountWithDiscount(): string
    {
        return number_format($this->getTotalAmountWithDiscount(), 2, '.', ' ') . ' TND';
    }
}
