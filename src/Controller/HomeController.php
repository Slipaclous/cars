<?php

namespace App\Controller;

use App\Repository\MarquesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(MarquesRepository $repo): Response
    {   
        //Permet d'aller chercher toutes les marques
        $marque= $repo->findAll();
        return $this->render('home/index.html.twig', [
            //on donne un nom a marque pour l'appeler dans la vue
            'marques' => $marque
        ]);
    }
}
