<?php

namespace App\Controller;
use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
class HistoryController extends AbstractController
{
    #[Route('/history', name: 'history.index')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {

        $session = $request->getSession();
        $history = $session->get('history');
        $empty = !($history === null);
        return $this->render('history/index.html.twig', [
            'categories' => $this->getCategories($doctrine),
            'histories' => $history,
            'empty' => $empty,
        ]);
    }

    private function getCategories(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Categorie:: class);
        $categories = $repository->findAll();
        return $categories;
    }

}
