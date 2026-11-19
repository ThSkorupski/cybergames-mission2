<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_CLIENT)]
class AccueilController extends AbstractController
{
    /**
     * Page d'accueil après connexion : le personnel est envoyé vers son tableau de bord,
     * le client voit son espace.
     */
    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->estPersonnel()) {
            return $this->redirectToRoute('app_personnel_accueil');
        }

        return $this->render('accueil/client.html.twig');
    }
}
