<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Reponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
class ShowReponseController extends AbstractController
{

    public function showReponses($id, $count, ManagerRegistry $doctrine)
    {
            $response = $doctrine
                ->getRepository(Reponse:: class)
                ->findBy(["question" => $id], ["id" => "ASC"]);


            return $response;
  
        
      
     

    }
}
