<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ForfaitRepository;
use App\Repository\MachineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/personnel')]
#[IsGranted(User::ROLE_PERSONNEL)]
class PersonnelController extends AbstractController
{
    #[Route('', name: 'app_personnel_accueil')]
    public function index(MachineRepository $machines, ForfaitRepository $forfaits): Response
    {
        return $this->render('personnel/accueil.html.twig', [
            'nb_machines' => $machines->count([]),
            'nb_forfaits' => $forfaits->count(['actif' => true]),
        ]);
    }
}
