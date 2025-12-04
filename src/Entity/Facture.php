<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montantHT = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montantTVA = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montantTTC = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column]
    private ?bool $isPaid = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->numero = $this->generateNumero();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

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

    public function __toString(): string
    {
        return 'Facture ' . $this->numero . ' - ' . $this->commande?->getReference();
    }

    public function getMontantHT(): ?string
    {
        return $this->montantHT;
    }

    public function setMontantHT(string $montantHT): static
    {
        $this->montantHT = $montantHT;

        return $this;
    }

    public function getMontantTVA(): ?string
    {
        return $this->montantTVA;
    }

    public function setMontantTVA(string $montantTVA): static
    {
        $this->montantTVA = $montantTVA;

        return $this;
    }

    public function getMontantTTC(): ?string
    {
        return $this->montantTTC;
    }

    public function setMontantTTC(string $montantTTC): static
    {
        $this->montantTTC = $montantTTC;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getFormattedMontantHT(): string
    {
        return number_format($this->montantHT, 2, '.', ' ') . ' TND';
    }

    public function getFormattedMontantTVA(): string
    {
        return number_format($this->montantTVA, 2, '.', ' ') . ' TND';
    }

    public function getFormattedMontantTTC(): string
    {
        return number_format($this->montantTTC, 2, '.', ' ') . ' TND';
    }

    private function generateNumero(): string
    {
        return 'FAC-' . date('Y') . '-' . str_pad((int)date('z') + 1, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(uniqid());
    }

    public function calculateAmounts(): void
    {
        if ($this->commande) {
            $tvaRate = 0.19; // 19% TVA en Tunisie
            $montantHT = (float) $this->commande->getTotalAmount();
            $montantTVA = $montantHT * $tvaRate;
            $montantTTC = $montantHT + $montantTVA;

            $this->montantHT = number_format($montantHT, 2, '.', '');
            $this->montantTVA = number_format($montantTVA, 2, '.', '');
            $this->montantTTC = number_format($montantTTC, 2, '.', '');
        }
    }
}
