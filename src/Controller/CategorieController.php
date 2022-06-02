<?php

namespace App\Controller;

use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class CategorieController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $userId = $this->getUser() === null ? false : $this->getUser()->id;
        $connected = (bool)$userId;
        $categories = $this->getCategories($doctrine);
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'userId' => $userId,
            'roles' => $this->VerifyStatus($request),
            'connected' => $connected,

        ]);
    }

    #[Route('/categorie', name: 'categorie.list')]
    public function categories(ManagerRegistry $doctrine): Response
    {
        //get categories data
        $categories = $this->getCategories($doctrine);
        return $this->render('categorie/categories.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    #[Route('/categorie/{id}/{name}', name: 'categorie.index',methods:['GET', 'HEAD'])]
    public function category(int $id, string $name, ManagerRegistry $doctrine): Response
    {
        $categories = $this->getCategories($doctrine);
        //get category data
        $categorie = $doctrine->getRepository(Categorie:: class)->find($id);
        $number = (intval($id) - 1) * 10 + 1;

        return $this->render('categorie/quizz.html.twig', [
            'categories' => $categories,
            'categorie' => $categorie,
            'name' => $name,
            'number' => $number,
        ]);
    }



    #[Route('/categorie/{id}/{name}/{count}', name: 'quiz.count', methods:['GET', 'HEAD'])]

    public function showQuiz(int $id, string $name, ManagerRegistry $doctrine): Response
    {
        $categories = $this->getCategories($doctrine);

        $repository = $doctrine->getRepository(Categorie:: class);
        $categorie = $repository->find($id);
        return $this->render('quiz/index.html.twig', [
            'categories' => $categories,
            'categorie' => $categorie,
            'name' => $name,
        ]);
    }

    
    private function getCategories(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Categorie:: class);
        $categories = $repository->findAll();
        return $categories;
    }

    private function getStatus(Request $request)
    {
        $userId = $this->getUser() === null ? false : $this->getUser()->id;
        if ($userId === null) {
            return false;
        }

        $user = $this->getUser();

        if(!isset($user->status)){
            return false;
        }
        return $user->status;
    }
    private function VerifyStatus(Request $request ){
        $roles = $this->getStatus($request);
        return $roles === 'ROLE_ADMIN';
    }
}
