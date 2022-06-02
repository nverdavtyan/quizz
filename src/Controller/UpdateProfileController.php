<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Categorie;
use App\Form\RegistrationFormType;
use App\Services\Mailer;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class UpdateProfileController extends AbstractController
{

    

    #[Route('/informations/{id}/update', name: 'app_update_profile')]
    public function index(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher , User $user ,  Mailer $mail, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === null) {
            return $this->redirectToRoute('home');
        }
        $userEmail = $this->getUser()->email;
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);

            $requestEmail = $form->get('email')->getData();
            if ($userEmail !== $requestEmail) {
                $token = $this->generateToken();
                $user->setToken($token);
                $user->setIsVerified(false);
                $user->setPassword($hash);
                $entityManager->flush();
                $this->addFlash("success", "Pour vous connecter, valider votre email");
                $mail->send($user->getEmail(), $token, $user->getUsername());
                return $this->redirectToRoute('app_logout');
            }

            $entityManager->flush();
          
        }           

        return $this->render('update_profile/index.html.twig', [
            'form' => $form->createView(),
            'categories' => $this->getCategories($doctrine),
        ]);
    }
    private function getCategories(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Categorie:: class);
        $categories = $repository->findAll();
        return $categories;
    }
    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
