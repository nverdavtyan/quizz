<?php

namespace App\Controller\Admin;


use App\Entity\Categorie;
use App\Form\CategoryType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminController extends AbstractController
{

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var UserInterface|null
     */
    private $current_user;

    public function __construct(CategorieRepository $userRepository, EntityManagerInterface $em, UserInterface $current_user = null)
    {
        $this->categoryRepository = $userRepository;
        $this->em = $em;
        $this->current_user = $current_user;
    }


    // READ CATEGORY
    #[Route('/admin/category', name: 'admin.category.index',methods:['GET', 'HEAD'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
        $categories = $this->getCategories();
        return $this->render('admin/category/index.html.twig', compact(
            'categories'
        ));
    }

    //CREATE CATEGORY

    #[Route('/admin/category/create', name: 'admin.category.new')]
    
    public function newCategory(Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
        $categories = $this->getCategories();
        $category = new Categorie();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($category);
            $this->em->flush();
            $this->addFlash('success', 'La catégorie ' . $category->getName() . ' à bien été crée avec succès');
            return $this->redirectToRoute('admin.category.index');
        }
        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }


    // UPDATE CATEGORY

    #[Route('/admin/category/{id}/edit', name: 'admin.category.edit',methods:['GET', 'POST'])]
     
    public function editCategory(Categorie $category, Request $request, ManagerRegistry $doctrine): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
        $categories = $this->getCategories($doctrine);
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'La catégorie ' . $category->getName() . ' à bien été modifié avec succès');
            return $this->redirectToRoute('admin.category.index');
        }
        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }

    // DELETE CATEGORY
    
    #[Route('/admin/category/{id}/delete', name: 'admin.category.delete', methods:['GET','DELETE'])]

    public function deleteCategory(Categorie $category, Request $request): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
            $this->em->remove($category);
            $this->em->flush();
            $this->addFlash('success', 'La catégorie ' . $category->getName() . ' à bien été supprimée avec succès');

        return $this->redirectToRoute('admin.category.index');
    }


    //Méthode Private pour simplifier le code
    private function getCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    private function verifyCsrfToken($token, $request): bool
    {
        if ($this->isCsrfTokenValid($token, $request->get('_token'))) {
            return true;
        }
        return false;
    }

    private function VerifyStatus(){
        $roles = $this->getStatus();
        return $roles === 'ROLE_ADMIN';
    }

    private function getStatus()
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
}
