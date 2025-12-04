<?php

namespace App\Entity;

use App\Repository\LivreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivreRepository::class)]
class Livre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Title must be at least {{ min }} characters', maxMessage: 'Title cannot exceed {{ max }} characters')]
    private ?string $titre = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Number of pages is required')]
    #[Assert\Positive(message: 'Number of pages must be positive')]
    private ?int $nbPages = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Number of copies is required')]
    #[Assert\PositiveOrZero(message: 'Number of copies cannot be negative')]
    private ?int $nbExemplaires = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Price is required')]
    #[Assert\Positive(message: 'Price must be positive')]
    private ?string $prix = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'ISBN is required')]
    #[Assert\Regex(pattern: '/^[0-9]{13}$/', message: 'ISBN must be exactly 13 digits')]
    private ?string $isbn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEdition = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'livres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Editeur $editeur = null;

    /**
     * @var Collection<int, Auteur>
     */
    #[ORM\ManyToMany(targetEntity: Auteur::class, inversedBy: 'livres')]
    #[ORM\JoinTable(name: 'livre_auteur')]
    #[ORM\JoinColumn(name: 'livre_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'auteur_id', referencedColumnName: 'id')]
    private Collection $auteurs;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'livres')]
    private Collection $categories;

    /**
     * @var Collection<int, StockMovement>
     */
    #[ORM\OneToMany(targetEntity: StockMovement::class, mappedBy: 'livre')]
    private Collection $stockMovements;

    /**
     * @var Collection<int, \App\Entity\Commentaire>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Commentaire::class, mappedBy: 'livre', cascade: ['remove'])]
    private Collection $commentaires;

    /**
     * @var Collection<int, LignePanier>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\LignePanier::class, mappedBy: 'livre')]
    private Collection $lignePaniers;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\LigneCommande::class, mappedBy: 'livre')]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->auteurs = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->lignePaniers = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
        $this->stockMovements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getNbPages(): ?int
    {
        return $this->nbPages;
    }

    public function setNbPages(int $nbPages): static
    {
        $this->nbPages = $nbPages;

        return $this;
    }

    public function getDateEdition(): ?\DateTimeInterface
    {
        return $this->dateEdition;
    }

    public function setDateEdition(?\DateTimeInterface $dateEdition): static
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    public function getNbExemplaires(): ?int
    {
        return $this->nbExemplaires;
    }

    public function setNbExemplaires(int $nbExemplaires): static
    {
        $this->nbExemplaires = $nbExemplaires;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getEditeur(): ?Editeur
    {
        return $this->editeur;
    }

    public function setEditeur(?Editeur $editeur): static
    {
        $this->editeur = $editeur;

        return $this;
    }

    /**
     * @return Collection<int, Auteur>
     */
    public function getAuteurs(): Collection
    {
        return $this->auteurs;
    }

    public function addAuteur(Auteur $auteur): static
    {
        if (!$this->auteurs->contains($auteur)) {
            $this->auteurs->add($auteur);
        }

        return $this;
    }

    public function removeAuteur(Auteur $auteur): static
    {
        $this->auteurs->removeElement($auteur);

        return $this;
    }

    /**
     * @param Collection<int, Auteur>|array $auteurs
     */
    public function setAuteurs(Collection|array $auteurs): static
    {
        if (is_array($auteurs)) {
            $this->auteurs->clear();
            foreach ($auteurs as $auteur) {
                $this->addAuteur($auteur);
            }
        } else {
            $this->auteurs = $auteurs;
        }

        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categories->contains($categorie)) {
            $this->categories->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        $this->categories->removeElement($categorie);

        return $this;
    }

    /**
     * @param Collection<int, Categorie>|array $categories
     */
    public function setCategories(Collection|array $categories): static
    {
        if (is_array($categories)) {
            $this->categories->clear();
            foreach ($categories as $categorie) {
                $this->addCategorie($categorie);
            }
        } else {
            $this->categories = $categories;
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, \App\Entity\Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(\App\Entity\Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setLivre($this);
        }

        return $this;
    }

    public function removeCommentaire(\App\Entity\Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getLivre() === $this) {
                $commentaire->setLivre(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
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
            $lignePanier->setLivre($this);
        }

        return $this;
    }

    public function removeLignePanier(LignePanier $lignePanier): static
    {
        if ($this->lignePaniers->removeElement($lignePanier)) {
            // set the owning side to null (unless already changed)
            if ($lignePanier->getLivre() === $this) {
                $lignePanier->setLivre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setLivre($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getLivre() === $this) {
                $ligneCommande->setLivre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }

    public function addStockMovement(StockMovement $stockMovement): static
    {
        if (!$this->stockMovements->contains($stockMovement)) {
            $this->stockMovements->add($stockMovement);
            $stockMovement->setLivre($this);
        }

        return $this;
    }

    public function removeStockMovement(StockMovement $stockMovement): static
    {
        if ($this->stockMovements->removeElement($stockMovement)) {
            // set the owning side to null (unless already changed)
            if ($stockMovement->getLivre() === $this) {
                $stockMovement->setLivre(null);
            }
        }

        return $this;
    }


    public function isLowStock(int $threshold = 5): bool
    {
        return $this->nbExemplaires <= $threshold;
    }

    public function getStockStatus(): string
    {
        if ($this->nbExemplaires === 0) {
            return 'out_of_stock';
        } elseif ($this->nbExemplaires <= 2) {
            return 'critical';
        } elseif ($this->nbExemplaires <= 5) {
            return 'low';
        }
        return 'available';
    }

    public function isAvailableForSale(int $quantity = 1): bool
    {
        // Reserve at least 2 copies for borrowing
        $reservedForBorrowing = 2;
        $availableForSale = max(0, $this->nbExemplaires - $reservedForBorrowing);
        return $availableForSale >= $quantity;
    }

    public function isAvailableForBorrowing(): bool
    {
        // Allow borrowing only if we have at least 3 copies total
        // and at least 1 copy not reserved for sales
        $minTotalStock = 3;
        $reservedForSales = max(1, $this->nbExemplaires - 2);
        
        return $this->nbExemplaires >= $minTotalStock && 
               ($this->nbExemplaires - $reservedForSales) > 0;
    }

    public function getAvailableForSaleCount(): int
    {
        $reservedForBorrowing = 2;
        return max(0, $this->nbExemplaires - $reservedForBorrowing);
    }

    public function getAvailableForBorrowingCount(): int
    {
        if (!$this->isAvailableForBorrowing()) {
            return 0;
        }
        
        $reservedForSales = max(1, $this->nbExemplaires - 2);
        return max(0, $this->nbExemplaires - $reservedForSales);
    }

    public function getStockStatusLabel(): string
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'Rupture de stock',
            'critical' => 'Stock critique',
            'low' => 'Stock bas',
            'available' => 'Disponible',
            default => 'Inconnu'
        };
    }
}

