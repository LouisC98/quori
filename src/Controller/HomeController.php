<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(QuestionRepository $questionRepo): Response
    {
        return $this->render('home/index.html.twig', [
            'questions' => $questionRepo->findBy([], [
                'rating' => 'DESC',
                'createdAt' => 'DESC',
                ])
        ]);
    }
}
