<?php

namespace App\Controller;
use App\Form\QuizType;
use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
class CreateQuizController extends AbstractController
{
    public function __construct(CategorieRepository $categoryRepository, EntityManagerInterface $em)
    {
        $this->categoryRepository = $categoryRepository;

    }


    #[Route('/create/quiz', name: 'app_create_quiz')]
    public function index(Request $request,  CategorieRepository $categorieRepository, ManagerRegistry $doctrine): Response
    {
        $categories = $this->getCategories();
          
        
               $em = $doctrine->getManager();
                     $quiz = new Categorie();
            

                 $form = $this->createForm(QuizType::class, $quiz);
            $form->handleRequest($request);

    
            if ($form->isSubmitted() && $form->isValid()) {
                    $quiz->setName('name');
            $em->persist($quiz);
            $em->flush();
      

            }
        
        return $this->render('create_quiz/index.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),

            
        ]);
    }

    private function getCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
}
