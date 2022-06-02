<?php

namespace App\Controller;
use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\Reponse;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
class QuizController extends AbstractController
{


    #[Route('/categorie/{id}/{name}/{count}/{number}', name: 'show_quiz',  requirements:["id"=>"\d+"])]
    
    public function showQuiz($id, $number, $name, $count, ShowReponseController $reponse, Request $request, Reponse $response, ManagerRegistry $doctrine): Response
    {
        $firstIdQuestion = (intval($id) - 1) * 10 + 1;
        $idQuestion = intval($number) - $firstIdQuestion + 1;
        if ($idQuestion === 1) {
            $_SESSION['responseValid'] = 0;
        }
        $categories =$this->getCategories($doctrine);
        
        $question = $doctrine
            ->getRepository(Question:: class)
            ->findOneBy(array("categorie" => $id, "id" => $number));

        $tab = $reponse->showReponses($number, $id, $doctrine);

        $defaultData = [];
   
        foreach ($tab as $item) {
            $defaultData["response$item->id"] = $item->reponse;
        }
        
             
        $form = $this->createFormBuilder($defaultData)
            ->add("response", ChoiceType::class, [
                'choices' => [
                    $defaultData["response" . $tab[0]->id] => $tab[0]->id,
                    $defaultData["response" . $tab[1]->id] => $tab[1]->id,
                    $defaultData["response" . $tab[2]->id] => $tab[2]->id
                ],
                'expanded' => true,
                'label' => false,
                'required' => true,
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestChoice = $request->request->all('form');
            $reponse_expected = $doctrine->getRepository(Reponse::class)
                ->findOneBy(array('reponse_expected' => true, 'question' => $number));
            $isResponseValid = $reponse_expected->id == $requestChoice['response'];
            if ($isResponseValid) {
                $_SESSION['responseValid'] += 1;
            }
            if ($idQuestion < intval($count)) {
                return $this->redirectToRoute('show_quiz',
                    array('id' => $id,
                        'name' => $name,
                        'count' => $count,
                        'number' => strval($number + 1)
                    ));
            }else{
                /*Historique quiz user*/
                $history = [
                    'categories' => [
                        'name' => $name,
                        'id' => $id
                    ],
                    'result' => [
                        'goodResponse' => $_SESSION['responseValid'],
                        'totalQuestion' => $count
                    ]
                ];
                $session = $request->getSession();
           
                $sessionVal = $session->get('history');
                $sessionVal[] = $history;
                $session->set('history', $sessionVal);


                return $this->render('quiz/quiz-result.html.twig', [
                    'category' => $name,
                    'categories' => $categories,
                    'countQuestion' => $count,
                    'countValideResponse' => $_SESSION['responseValid']
                ]);
            }
        }

        return $this->render('quiz/index.html.twig', [
            'question' => $question->getQuestion(),
            'responses' => $tab,
            'categories' => $categories,
            'form' => $form->createView(),
            'currentPage' => $number - $firstIdQuestion + 1,
            'totalPage' => $count,
            'correct' => $idQuestion === 1 ? 0 : $_SESSION['responseValid'],
            'incorrect' => $idQuestion === 1 ? 0 : $idQuestion - $_SESSION['responseValid'] - 1
        ]);

    }

    private function getCategories(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Categorie:: class);
        $categories = $repository->findAll();
        return $categories;
    }
}
