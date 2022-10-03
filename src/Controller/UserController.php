<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\QuestionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
#[IsGranted("ROLE_USER")]
class UserController extends AbstractController
{
    #[Route('/questions/{id}', name: 'app_user_questions')]
    public function questions(User $user, QuestionRepository $questionRepo): Response
    {
        $questions = $questionRepo->findBy(
            ['user' => $user],
            [
                'createdAt' => 'DESC',
                'rating' => 'DESC'
            ]
        );

        return $this->render('user/questions.html.twig', [
            'questions' => $questions,
            'user' => $user
        ]);
    }

    #[Route('/comments/{id}', name: 'app_user_comments')]
    public function comments(User $user, CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(
            ['user' => $user],
            [
                'createdAt' => 'DESC',
                'rating' => 'DESC'
            ]
        );

        return $this->render('user/comments.html.twig', [
            'comments' => $comments,
            'user' => $user
        ]);
    }
}
