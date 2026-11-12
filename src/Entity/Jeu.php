<?php

namespace App\Entity;

use App\Repository\JeuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JeuRepository::class)]
#[UniqueEntity(fields: ['titre'], message: 'Ce jeu existe déjà.')]
class Jeu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 150)]
    private ?string $titre = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $editeur = null;

    /** @var Collection<int, Machine> */
    #[ORM\ManyToMany(targetEntity: Machine::class, mappedBy: 'jeux')]
    private Collection $machines;

    public function __construct()
    {
        $this->machines = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->titre;
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

    public function getEditeur(): ?string
    {
        return $this->editeur;
    }

    public function setEditeur(?string $editeur): static
    {
        $this->editeur = $editeur;

        return $this;
    }

    /** @return Collection<int, Machine> */
    public function getMachines(): Collection
    {
        return $this->machines;
    }
}
