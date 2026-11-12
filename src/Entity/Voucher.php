<?php

namespace App\Entity;

use App\Enum\StatutVoucher;
use App\Repository\VoucherRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Forfait attribué à un client. La durée et la catégorie sont recopiées du forfait
 * au moment de l'attribution, pour que les vouchers déjà donnés ne changent pas
 * si le forfait est modifié ensuite.
 */
#[ORM\Entity(repositoryClass: VoucherRepository::class)]
#[ORM\Index(name: 'idx_voucher_machine_fin', columns: ['machine_id', 'date_fin'])]
class Voucher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private string $code;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $client;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Forfait $forfait;

    /** Copie de la durée du forfait à l'attribution. */
    #[ORM\Column]
    private int $dureeMinutes;

    /** Copie de la catégorie du forfait à l'attribution. */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private CategorieMachine $categorie;

    /** Membre du personnel qui a attribué le voucher (traçabilité). */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $attribuePar;

    #[ORM\Column]
    private \DateTimeImmutable $dateAttribution;

    /** Null = pas d'expiration (règle encore à décider, voir ToDo). */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateExpiration = null;

    /** Null = pas encore démarré. */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateDebut = null;

    /** dateDebut + durée, calculée au démarrage (le voucher ne se met pas en pause). */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\ManyToOne]
    private ?Machine $machine = null;

    public function __construct(User $client, Forfait $forfait, User $attribuePar)
    {
        $this->client = $client;
        $this->forfait = $forfait;
        $this->attribuePar = $attribuePar;
        $this->dureeMinutes = $forfait->getDureeMinutes();
        $this->categorie = $forfait->getCategorie();
        $this->dateAttribution = new \DateTimeImmutable();
        $this->code = self::genererCode();
    }

    /** Code lisible, ex. CG-7F3K-92QA (sans 0/O ni 1/I pour éviter les confusions). */
    private static function genererCode(): string
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 8; ++$i) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return 'CG-'.substr($code, 0, 4).'-'.substr($code, 4);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getClient(): User
    {
        return $this->client;
    }

    public function getForfait(): Forfait
    {
        return $this->forfait;
    }

    public function getDureeMinutes(): int
    {
        return $this->dureeMinutes;
    }

    /**
     * Correction par le personnel, seulement tant que le voucher n'a pas démarré.
     */
    public function modifierDuree(int $dureeMinutes): static
    {
        if ($this->estDemarre()) {
            throw new \LogicException('Impossible de modifier la durée d\'un voucher déjà démarré.');
        }
        if ($dureeMinutes <= 0) {
            throw new \InvalidArgumentException('La durée doit être supérieure à 0.');
        }
        $this->dureeMinutes = $dureeMinutes;

        return $this;
    }

    public function getCategorie(): CategorieMachine
    {
        return $this->categorie;
    }

    public function getAttribuePar(): User
    {
        return $this->attribuePar;
    }

    public function getDateAttribution(): \DateTimeImmutable
    {
        return $this->dateAttribution;
    }

    public function getDateExpiration(): ?\DateTimeImmutable
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeImmutable $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    /**
     * Démarre le voucher sur une machine. Les vérifications qui demandent la base
     * (machine libre, pas de voucher en cours dessus) se font dans le service appelant.
     */
    public function demarrer(Machine $machine, ?\DateTimeImmutable $maintenant = null): static
    {
        $maintenant ??= new \DateTimeImmutable();

        if ($this->estDemarre()) {
            throw new \LogicException('Ce voucher a déjà été utilisé.');
        }
        if ($this->estExpire($maintenant)) {
            throw new \LogicException('Ce voucher a expiré.');
        }
        if ($machine->getCategorie() !== $this->categorie) {
            throw new \LogicException('Cette machine n\'est pas de la catégorie prévue par le voucher.');
        }

        $this->machine = $machine;
        $this->dateDebut = $maintenant;
        $this->dateFin = $maintenant->modify('+'.$this->dureeMinutes.' minutes');

        return $this;
    }

    public function estDemarre(): bool
    {
        return null !== $this->dateDebut;
    }

    public function estExpire(?\DateTimeImmutable $maintenant = null): bool
    {
        $maintenant ??= new \DateTimeImmutable();

        return !$this->estDemarre()
            && null !== $this->dateExpiration
            && $this->dateExpiration <= $maintenant;
    }

    public function getStatut(?\DateTimeImmutable $maintenant = null): StatutVoucher
    {
        $maintenant ??= new \DateTimeImmutable();

        if (!$this->estDemarre()) {
            return $this->estExpire($maintenant) ? StatutVoucher::Expire : StatutVoucher::NonUtilise;
        }

        return $this->dateFin > $maintenant ? StatutVoucher::EnCours : StatutVoucher::Termine;
    }

    /** Minutes restantes (0 si terminé, durée complète si pas encore démarré). */
    public function getMinutesRestantes(?\DateTimeImmutable $maintenant = null): int
    {
        $maintenant ??= new \DateTimeImmutable();

        if (!$this->estDemarre()) {
            return $this->dureeMinutes;
        }

        $secondes = $this->dateFin->getTimestamp() - $maintenant->getTimestamp();

        return max(0, (int) ceil($secondes / 60));
    }
}
