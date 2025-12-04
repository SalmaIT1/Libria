<?php

namespace App\Entity;

use App\Repository\EmpruntRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpruntRepository::class)]
#[ORM\Table(name: 'emprunt')]
class Emprunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Livre::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Livre $livre = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateEmprunt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateRetourPrevu = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateRetourEffectif = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private ?string $statut = 'en_cours'; // en_cours, retourne, en_retard

    public function __construct()
    {
        $this->dateEmprunt = new \DateTimeImmutable();
        // Date de retour prévue : 30 jours après l'emprunt
        $this->dateRetourPrevu = (new \DateTimeImmutable())->modify('+30 days');
        $this->statut = 'en_cours';
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

    public function getLivre(): ?Livre
    {
        return $this->livre;
    }

    public function setLivre(?Livre $livre): static
    {
        $this->livre = $livre;

        return $this;
    }

    public function getDateEmprunt(): ?\DateTimeInterface
    {
        return $this->dateEmprunt;
    }

    public function setDateEmprunt(\DateTimeInterface $dateEmprunt): static
    {
        $this->dateEmprunt = $dateEmprunt;

        return $this;
    }

    public function getDateRetourPrevu(): ?\DateTimeInterface
    {
        return $this->dateRetourPrevu;
    }

    public function setDateRetourPrevu(?\DateTimeInterface $dateRetourPrevu): static
    {
        $this->dateRetourPrevu = $dateRetourPrevu;

        return $this;
    }

    public function getDateRetourEffectif(): ?\DateTimeInterface
    {
        return $this->dateRetourEffectif;
    }

    public function setDateRetourEffectif(?\DateTimeInterface $dateRetourEffectif): static
    {
        $this->dateRetourEffectif = $dateRetourEffectif;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function estEnRetard(): bool
    {
        if ($this->statut === 'retourne') {
            return false;
        }
        
        if ($this->dateRetourPrevu && $this->dateRetourPrevu < new \DateTime()) {
            return true;
        }
        
        return false;
    }

    public function __toString(): string
    {
        return $this->livre?->getTitre() . ' - ' . $this->user?->getFirstName() . ' ' . $this->user?->getLastName();
    }
}
