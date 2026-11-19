<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Entity\User;
use App\Enum\StatutMachine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use App\Repository\VoucherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des machines par le personnel : catégorie, jeux installés, statut.
 */
#[Route('/personnel/machines')]
#[IsGranted(User::ROLE_PERSONNEL)]
class MachineController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'app_machine_index', methods: ['GET'])]
    public function index(MachineRepository $machines): Response
    {
        return $this->render('personnel/machine/index.html.twig', [
            'machines' => $machines->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/nouvelle', name: 'app_machine_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($machine);
            $this->em->flush();
            $this->addFlash('success', 'La machine a été créée.');

            return $this->redirectToRoute('app_machine_index');
        }

        return $this->render('personnel/machine/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/modifier', name: 'app_machine_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Machine $machine, Request $request): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'La machine a été modifiée.');

            return $this->redirectToRoute('app_machine_index');
        }

        return $this->render('personnel/machine/edit.html.twig', ['form' => $form, 'machine' => $machine]);
    }

    /**
     * Supprime la machine, ou la met hors service si des vouchers l'ont déjà utilisée (on garde l'historique).
     */
    #[Route('/{id}/supprimer', name: 'app_machine_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Machine $machine, Request $request, VoucherRepository $vouchers): Response
    {
        if (!$this->isCsrfTokenValid('supprimer_machine_'.$machine->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action refusée : jeton de sécurité invalide. Rechargez la page et réessayez.');

            return $this->redirectToRoute('app_machine_index');
        }

        if ($vouchers->count(['machine' => $machine]) > 0) {
            if ($machine->getStatut() === StatutMachine::HorsService) {
                $this->addFlash('error', 'Cette machine a déjà servi et elle est déjà hors service : elle est conservée pour l\'historique.');
            } else {
                $machine->setStatut(StatutMachine::HorsService);
                $this->em->flush();
                $this->addFlash('success', 'Cette machine a déjà servi : elle a été mise hors service au lieu d\'être supprimée.');
            }

            return $this->redirectToRoute('app_machine_index');
        }

        $this->em->remove($machine);
        $this->em->flush();
        $this->addFlash('success', 'La machine a été supprimée.');

        return $this->redirectToRoute('app_machine_index');
    }
}
