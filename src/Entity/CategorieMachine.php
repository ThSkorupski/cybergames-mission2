<?php

namespace App\Entity;

use App\Repository\CategorieMachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Type de machine (ex. « PC gaming », « Console »). Un forfait donne accès à une catégorie.
 */
#[ORM\Entity(repositoryClass: CategorieMachineRepository::class)]
#[UniqueEntity(fields: ['libelle'], message: 'Cette catégorie existe déjà.')]
class CategorieMachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /** @var Collection<int, Machine> */
    #[ORM\OneToMany(targetEntity: Machine::class, mappedBy: 'categorie')]
    private Collection $machines;

    /** @var Collection<int, Forfait> */
    #[ORM\OneToMany(targetEntity: Forfait::class, mappedBy: 'categorie')]
    private Collection $forfaits;

    public function __construct()
    {
        $this->machines = new ArrayCollection();
        $this->forfaits = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->libelle;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

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

    /** @return Collection<int, Machine> */
    public function getMachines(): Collection
    {
        return $this->machines;
    }

    /** @return Collection<int, Forfait> */
    public function getForfaits(): Collection
    {
        return $this->forfaits;
    }
}
