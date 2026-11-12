<?php

namespace App\Entity;

use App\Enum\StatutMachine;
use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
#[UniqueEntity(fields: ['nom'], message: 'Une machine porte déjà ce nom.')]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le nom de la machine est obligatoire.')]
    #[Assert\Length(max: 50)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'machines')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Choisissez une catégorie.')]
    private ?CategorieMachine $categorie = null;

    #[ORM\Column(length: 20, enumType: StatutMachine::class)]
    private StatutMachine $statut = StatutMachine::Disponible;

    /** @var Collection<int, Jeu> */
    #[ORM\ManyToMany(targetEntity: Jeu::class, inversedBy: 'machines')]
    #[ORM\JoinTable(name: 'machine_jeu')]
    private Collection $jeux;

    public function __construct()
    {
        $this->jeux = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCategorie(): ?CategorieMachine
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieMachine $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getStatut(): StatutMachine
    {
        return $this->statut;
    }

    public function setStatut(StatutMachine $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /** @return Collection<int, Jeu> */
    public function getJeux(): Collection
    {
        return $this->jeux;
    }

    public function addJeu(Jeu $jeu): static
    {
        if (!$this->jeux->contains($jeu)) {
            $this->jeux->add($jeu);
        }

        return $this;
    }

    public function removeJeu(Jeu $jeu): static
    {
        $this->jeux->removeElement($jeu);

        return $this;
    }
}
