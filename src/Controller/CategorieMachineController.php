<?php

namespace App\Controller;

use App\Entity\CategorieMachine;
use App\Entity\User;
use App\Form\CategorieMachineType;
use App\Repository\CategorieMachineRepository;
use App\Repository\ForfaitRepository;
use App\Repository\MachineRepository;
use App\Repository\VoucherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des catégories de machines (ex. « PC gaming », « Console »).
 */
#[Route('/personnel/categories')]
#[IsGranted(User::ROLE_PERSONNEL)]
class CategorieMachineController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'app_categorie_index', methods: ['GET'])]
    public function index(CategorieMachineRepository $categories): Response
    {
        return $this->render('personnel/categorie/index.html.twig', [
            'categories' => $categories->findBy([], ['libelle' => 'ASC']),
        ]);
    }

    #[Route('/nouvelle', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $categorie = new CategorieMachine();
        $form = $this->createForm(CategorieMachineType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($categorie);
            $this->em->flush();
            $this->addFlash('success', 'La catégorie a été créée.');

            return $this->redirectToRoute('app_categorie_index');
        }

        return $this->render('personnel/categorie/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/modifier', name: 'app_categorie_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(CategorieMachine $categorie, Request $request): Response
    {
        $form = $this->createForm(CategorieMachineType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'La catégorie a été modifiée.');

            return $this->redirectToRoute('app_categorie_index');
        }

        return $this->render('personnel/categorie/edit.html.twig', ['form' => $form, 'categorie' => $categorie]);
    }

    /**
     * Une catégorie encore utilisée (machines, forfaits ou vouchers) ne peut pas être supprimée.
     */
    #[Route('/{id}/supprimer', name: 'app_categorie_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        CategorieMachine $categorie,
        Request $request,
        MachineRepository $machines,
        ForfaitRepository $forfaits,
        VoucherRepository $vouchers,
    ): Response {
        if (!$this->isCsrfTokenValid('supprimer_categorie_'.$categorie->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action refusée : jeton de sécurité invalide. Rechargez la page et réessayez.');

            return $this->redirectToRoute('app_categorie_index');
        }

        if (
            $machines->count(['categorie' => $categorie]) > 0
            || $forfaits->count(['categorie' => $categorie]) > 0
            || $vouchers->count(['categorie' => $categorie]) > 0
        ) {
            $this->addFlash('error', 'Cette catégorie est encore utilisée par des machines, des forfaits ou des vouchers : elle ne peut pas être supprimée.');

            return $this->redirectToRoute('app_categorie_index');
        }

        $this->em->remove($categorie);
        $this->em->flush();
        $this->addFlash('success', 'La catégorie a été supprimée.');

        return $this->redirectToRoute('app_categorie_index');
    }
}
