<?php
namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    #[Assert\NotBlank(message: 'Le code du coupon est obligatoire')]
    #[Assert\Length(max: 20, maxMessage: 'Le code ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/^[A-Z0-9]+$/', message: 'Le code doit contenir uniquement des lettres majuscules et des chiffres')]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\NotBlank(message: 'Le type de réduction est obligatoire')]
    #[Assert\Choice(choices: ['percentage', 'fixed'], message: 'Type de réduction invalide')]
    private ?string $type = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'La valeur de réduction est obligatoire')]
    #[Assert\Positive(message: 'La valeur doit être positive')]
    private ?string $value = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Le montant minimum doit être positif')]
    private ?string $minimumAmount = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive(message: 'Le nombre d\'utilisations doit être positif')]
    private ?int $maxUses = null;

    #[ORM\Column(type: 'integer')]
    private ?int $usedCount = 0;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isActive = true;

    /**
     * @var Collection<int, Panier>
     */
    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'coupon')]
    private Collection $paniers;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->paniers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = strtoupper($code);
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getMinimumAmount(): ?string
    {
        return $this->minimumAmount;
    }

    public function setMinimumAmount(?string $minimumAmount): self
    {
        $this->minimumAmount = $minimumAmount;
        return $this;
    }

    public function getMaxUses(): ?int
    {
        return $this->maxUses;
    }

    public function setMaxUses(int $maxUses): self
    {
        $this->maxUses = $maxUses;
        return $this;
    }

    public function getUsedCount(): ?int
    {
        return $this->usedCount;
    }

    public function setUsedCount(int $usedCount): self
    {
        $this->usedCount = $usedCount;
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

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isValid(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->expiresAt && $this->expiresAt < new \DateTime()) {
            return false;
        }

        if ($this->maxUses && $this->usedCount >= $this->maxUses) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $cartAmount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->minimumAmount && $cartAmount < (float)$this->minimumAmount) {
            return 0;
        }

        if ($this->type === 'percentage') {
            return $cartAmount * ((float)$this->value / 100);
        }

        return min((float)$this->value, $cartAmount);
    }

    public function incrementUsage(): self
    {
        $this->usedCount++;
        return $this;
    }

    public function getFormattedValue(): string
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        }
        
        return $this->value . ' TND';
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function addPanier(Panier $panier): static
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->setCoupon($this);
        }

        return $this;
    }

    public function removePanier(Panier $panier): static
    {
        if ($this->paniers->removeElement($panier)) {
            // set the owning side to null (unless already changed)
            if ($panier->getCoupon() === $this) {
                $panier->setCoupon(null);
            }
        }

        return $this;
    }
}
