<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        return $this->redirectToRoute('app_login');
    }
    
}
