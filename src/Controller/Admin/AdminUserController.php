<?php

namespace App\Controller\Admin;



use App\Entity\User;
use App\Form\NewUserType;
use App\Form\UsersType;
use App\Repository\CategorieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class AdminUserController extends AbstractController
{
    public function __construct(CategorieRepository $categoryRepository, EntityManagerInterface $em, UserInterface $current_user = null, UserRepository $userRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->userRepository = $userRepository;
        $this->current_user = $current_user;
        $this->em = $em;
    }


    // READ USERS


    #[Route('/admin/users', name: 'admin.users.index',  methods:['GET', 'HEAD'])]
    public function index(): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
        $categories = $this->getCategories();
        $users = $this->getUserList();
        return $this->render('admin/users/index.html.twig', [
                'categories' => $categories,
                'users' => $users
            ]
        );
    }


    #[Route('/admin/user/create', name: 'admin.user.new')]
    public function newUser(Request $request, UserPasswordHasherInterface  $hash): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
        $categories = $this->getCategories();
        $user = new User();
        $form = $this->createForm(NewUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $hash->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getUsername() . ' à bien été crée avec succès');
            return $this->redirectToRoute('admin.users.index');
        }
        return $this->render('admin/users/new.html.twig', [
            '$user' => $user,
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }


    // UPDATE USER

    

    #[Route('/admin/user/{id}/edit', name: 'admin.user.edit', methods:['GET', 'POST'])]
    public function editUser(User $user, Request $request, UserPasswordHasherInterface $hash): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
        $categories = $this->getCategories();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $hash = $hash->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getUsername() . ' à bien été modifié avec succès');
            return $this->redirectToRoute('admin.users.index');
        }
        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }

    // DELETE USER
    #[Route('/admin/users/{id}/delete', name: 'admin.user.delete', methods:['POST','DELETE'])]
    public function deleteUser(User $user, Request $request): Response
    {
        if(!$this->VerifyStatus()){
            return $this->redirectToRoute('home');
        }
        if ($this->verifyCsrfToken('delete_user' . $user->getId(), $request)) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getUsername() . ' à bien été supprimée avec succès');
        }
        return $this->redirectToRoute('admin.users.index');
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

    private function getUserList(): array
    {
        return $this->userRepository->findAll();
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

