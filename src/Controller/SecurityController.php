<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Categorie;
use App\Form\LoginType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Services\Mailer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;




class SecurityController extends AbstractController
{
       
    /**
     * @throws Exception
     */
    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    #[Route('/register', name: 'app_register')]
    public function registration(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher , Mailer $mail,EntityManagerInterface $entityManager ): Response
    {
        $user = new User();
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $token = $this->generateToken();
            $user->setToken($token);
            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash("success", "Pour vous connecter, valider votre email");
             $mail->send($user->getEmail(), $token, $user->getUsername());
             
            return $this->redirectToRoute("app_login");
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
            'categories' => $this->getCategories($doctrine),
            
        ]);
    }

    #[Route('/confirmer-mon-compte/{token}', name: 'confirm_account')]
    public function confirmAccount(string $token, ManagerRegistry $doctrine): RedirectResponse
    {
        $userRepository = $doctrine->getRepository(User::class);
        $user = $userRepository->findOneBy(["token" => $token]);
        $manager = $doctrine->getManager();
        if($user  && !$user->getIsVerified()){
            $user->setToken(null);
            $user->setIsVerified(true);
            $manager->persist($user);
            $manager->flush();

        }else{
            $this->addFlash("error", "Ce compte n'existe pas");
        }
        return $this->redirectToRoute("app_login");
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, ManagerRegistry $doctrine, EntityManagerInterface $em, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }
        $user = new User();
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
  
        $form = $this->createForm(LoginType::class, $user, [
            'action' => $this->generateUrl('loginForm')]);
        $this->formError = $form;
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $em->flush();

            return $this->redirectToRoute("home");
        }

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'categories' => $this->getCategories($doctrine),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/loginForm', name: 'loginForm')]
    public function loginForm(AuthenticationUtils $authenticationUtils): RedirectResponse
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if($error !== null){
            $this->addFlash("error", "Adresse email ou mot de passe invalide !");
            return $this->redirectToRoute("app_login");
        }
   

        return $this->redirectToRoute("home");
    }

    private function getCategories(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Categorie:: class);
        $categories = $repository->findAll();
        return $categories;
    }

}
