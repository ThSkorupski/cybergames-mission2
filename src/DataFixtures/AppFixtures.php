<?php

namespace App\DataFixtures;

use App\Entity\CategorieMachine;
use App\Entity\Forfait;
use App\Entity\Jeu;
use App\Entity\Machine;
use App\Entity\User;
use App\Enum\StatutMachine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Données de test : à charger avec « php bin/console doctrine:fixtures:load ».
 * Attention : cette commande VIDE les tables avant de recharger (base de développement uniquement).
 *
 * Comptes créés (mot de passe commun : « motdepasse123 ») :
 *  - admin@cybergames.test      (ROLE_ADMIN)
 *  - personnel@cybergames.test  (ROLE_PERSONNEL)
 *  - client@cybergames.test     (ROLE_CLIENT)
 *  - ancien@cybergames.test     (ROLE_CLIENT, compte désactivé : sert à tester le refus de connexion)
 */
class AppFixtures extends Fixture
{
    private const MOT_DE_PASSE = 'motdepasse123';

    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Comptes ---
        $this->creerUser($manager, 'admin@cybergames.test', 'Durand', 'Alice', [User::ROLE_ADMIN], 'alice_admin');
        $this->creerUser($manager, 'personnel@cybergames.test', 'Martin', 'Bruno', [User::ROLE_PERSONNEL], 'bruno_accueil');
        $this->creerUser($manager, 'client@cybergames.test', 'Lefebvre', 'Chloé', [User::ROLE_CLIENT], 'chloe_gameuse');
        $this->creerUser($manager, 'client2@cybergames.test', 'Petit', 'David', [User::ROLE_CLIENT], 'david_fps');
        $this->creerUser($manager, 'ancien@cybergames.test', 'Moreau', 'Emma', [User::ROLE_CLIENT], 'emma_old', false);

        // --- Catégories de machines ---
        $gaming = (new CategorieMachine())->setLibelle('PC gaming')->setDescription('PC haute performance pour les jeux récents.');
        $standard = (new CategorieMachine())->setLibelle('PC standard')->setDescription('PC polyvalent pour les jeux légers et la bureautique.');
        $console = (new CategorieMachine())->setLibelle('Console')->setDescription('Consoles de salon avec manettes.');
        foreach ([$gaming, $standard, $console] as $categorie) {
            $manager->persist($categorie);
        }

        // --- Jeux ---
        $jeux = [];
        foreach ([
            ['Counter-Strike 2', 'Valve'],
            ['League of Legends', 'Riot Games'],
            ['Rocket League', 'Psyonix'],
            ['Minecraft', 'Mojang'],
            ['FC 26', 'EA Sports'],
        ] as [$titre, $editeur]) {
            $jeu = (new Jeu())->setTitre($titre)->setEditeur($editeur);
            $manager->persist($jeu);
            $jeux[$titre] = $jeu;
        }

        // --- Machines ---
        $machines = [
            ['GAMING-01', $gaming, StatutMachine::Disponible, ['Counter-Strike 2', 'League of Legends', 'Rocket League']],
            ['GAMING-02', $gaming, StatutMachine::Disponible, ['Counter-Strike 2', 'Rocket League']],
            ['GAMING-03', $gaming, StatutMachine::Maintenance, ['Counter-Strike 2']],
            ['STD-01', $standard, StatutMachine::Disponible, ['League of Legends', 'Minecraft']],
            ['STD-02', $standard, StatutMachine::Disponible, ['Minecraft']],
            ['CONSOLE-01', $console, StatutMachine::Disponible, ['FC 26', 'Rocket League']],
            ['CONSOLE-02', $console, StatutMachine::HorsService, ['FC 26']],
        ];
        foreach ($machines as [$nom, $categorie, $statut, $titresJeux]) {
            $machine = (new Machine())->setNom($nom)->setCategorie($categorie)->setStatut($statut);
            foreach ($titresJeux as $titre) {
                $machine->addJeu($jeux[$titre]);
            }
            $manager->persist($machine);
        }

        // --- Forfaits ---
        foreach ([
            ['Gaming 1 heure', 60, '5.00', $gaming],
            ['Gaming 3 heures', 180, '12.00', $gaming],
            ['Standard 1 heure', 60, '3.00', $standard],
            ['Console 2 heures', 120, '8.00', $console],
        ] as [$nom, $duree, $prix, $categorie]) {
            $manager->persist(
                (new Forfait())->setNom($nom)->setDureeMinutes($duree)->setPrix($prix)->setCategorie($categorie)
            );
        }

        $manager->flush();
    }

    /** @param list<string> $roles */
    private function creerUser(
        ObjectManager $manager,
        string $email,
        string $nom,
        string $prenom,
        array $roles,
        string $pseudo,
        bool $actif = true,
    ): void {
        $user = (new User())
            ->setEmail($email)
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setPseudo($pseudo)
            ->setRoles($roles)
            ->setActif($actif);
        $user->setPassword($this->hasher->hashPassword($user, self::MOT_DE_PASSE));
        $manager->persist($user);
    }
}
