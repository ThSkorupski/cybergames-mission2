<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ClientType;
use App\Repository\UserRepository;
use App\Repository\VoucherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des comptes clients par le personnel.
 * Ce contrôleur ne touche jamais aux comptes du personnel ni des admins.
 */
#[Route('/personnel/clients')]
#[IsGranted(User::ROLE_PERSONNEL)]
class ClientController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('personnel/client/index.html.twig', [
            'clients' => $this->users->findClients(),
        ]);
    }

    #[Route('/nouveau', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $client = (new User())->setRoles([User::ROLE_CLIENT]);
        $form = $this->createForm(ClientType::class, $client, ['creation' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setPassword($this->hasher->hashPassword($client, $form->get('plainPassword')->getData()));
            $this->em->persist($client);
            $this->em->flush();

            $this->addFlash('success', 'Le compte client a été créé.');

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('personnel/client/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/modifier', name: 'app_client_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $client = $this->trouverClient($id);
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouveauMotDePasse = $form->get('plainPassword')->getData();
            if ($nouveauMotDePasse) {
                $client->setPassword($this->hasher->hashPassword($client, $nouveauMotDePasse));
            }
            $this->em->flush();

            $this->addFlash('success', 'Le compte client a été modifié.');

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('personnel/client/edit.html.twig', ['form' => $form, 'client' => $client]);
    }

    /**
     * Supprime le client, ou le désactive s'il a déjà des vouchers (on garde l'historique).
     */
    #[Route('/{id}/supprimer', name: 'app_client_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request, VoucherRepository $vouchers): Response
    {
        $client = $this->trouverClient($id);

        if (!$this->isCsrfTokenValid('supprimer_client_'.$client->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action refusée : jeton de sécurité invalide. Rechargez la page et réessayez.');

            return $this->redirectToRoute('app_client_index');
        }

        if ($vouchers->count(['client' => $client]) > 0) {
            if ($client->isActif()) {
                $client->setActif(false);
                $this->em->flush();
                $this->addFlash('success', 'Ce client a déjà des vouchers : son compte a été désactivé au lieu d\'être supprimé.');
            } else {
                $this->addFlash('error', 'Ce client a des vouchers et son compte est déjà désactivé : il est conservé pour l\'historique.');
            }

            return $this->redirectToRoute('app_client_index');
        }

        $this->em->remove($client);
        $this->em->flush();
        $this->addFlash('success', 'Le compte client a été supprimé.');

        return $this->redirectToRoute('app_client_index');
    }

    /** Un id qui n'est pas celui d'un client (personnel, admin, inexistant) donne une 404. */
    private function trouverClient(int $id): User
    {
        $client = $this->users->find($id);
        if (!$client || !$client->estClient()) {
            throw $this->createNotFoundException('Client introuvable.');
        }

        return $client;
    }
}
