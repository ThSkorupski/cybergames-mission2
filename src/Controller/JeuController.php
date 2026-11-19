<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Entity\User;
use App\Form\JeuType;
use App\Repository\JeuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Catalogue des jeux installables sur les machines.
 * Supprimer un jeu le retire simplement des machines (la table de liaison est nettoyée par la base).
 */
#[Route('/personnel/jeux')]
#[IsGranted(User::ROLE_PERSONNEL)]
class JeuController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'app_jeu_index', methods: ['GET'])]
    public function index(JeuRepository $jeux): Response
    {
        return $this->render('personnel/jeu/index.html.twig', [
            'jeux' => $jeux->findBy([], ['titre' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'app_jeu_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $jeu = new Jeu();
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($jeu);
            $this->em->flush();
            $this->addFlash('success', 'Le jeu a été ajouté.');

            return $this->redirectToRoute('app_jeu_index');
        }

        return $this->render('personnel/jeu/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/modifier', name: 'app_jeu_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Jeu $jeu, Request $request): Response
    {
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Le jeu a été modifié.');

            return $this->redirectToRoute('app_jeu_index');
        }

        return $this->render('personnel/jeu/edit.html.twig', ['form' => $form, 'jeu' => $jeu]);
    }

    #[Route('/{id}/supprimer', name: 'app_jeu_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Jeu $jeu, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('supprimer_jeu_'.$jeu->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action refusée : jeton de sécurité invalide. Rechargez la page et réessayez.');

            return $this->redirectToRoute('app_jeu_index');
        }

        $this->em->remove($jeu);
        $this->em->flush();
        $this->addFlash('success', 'Le jeu a été supprimé.');

        return $this->redirectToRoute('app_jeu_index');
    }
}
