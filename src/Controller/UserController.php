<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\CommentRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
#[IsGranted("ROLE_USER")]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    public function index(Request $request, EntityManagerInterface $em) {

        $user = $this->getUser();

        $userForm = $this->createForm(RegistrationFormType::class, $user);
        $userForm->remove('email')
                ->remove('password');

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {

            $newPicture = $userForm->get('picture')->getData();
            
            if ($newPicture) {
                
                $oldPicture = $user->getPicture();
                $extension = $newPicture->guessExtension();
                $filename = bin2hex(random_bytes(6)) . '.' . $extension;
                $folder = $this->getParameter('pictures.folder');

                $user->setPicture($filename);
                $newPicture->move($folder, $filename);

                if($oldPicture !== 'default.jpg') {
                    unlink($folder .'/'. $oldPicture);
                } 
            } 

            $em->flush();
        }

        return $this->render('user/index.html.twig', ['form' => $userForm->createView()]);
    }

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
