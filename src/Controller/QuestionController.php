<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Question;
use App\Form\CommentType;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/question')]
class QuestionController extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, QuestionRepository $questionRepository): Response
    {
        if ($this->getUser()) {

            $question = new Question();
            $form = $this->createForm(QuestionType::class, $question);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $question->setCreatedAt(new DateTimeImmutable())
                    ->setUser($this->getUser())
                    ->setRating(0);
                $questionRepository->add($question, true);

                return $this->redirectToRoute('app_question_show', ['id' => $question->getId()], Response::HTTP_SEE_OTHER);
            }
        }


        return $this->renderForm('question/new.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_question_show', methods: ['GET', 'POST'])]
    public function show(int $id, QuestionRepository $questionRepo, Request $request, EntityManagerInterface $em): Response
    {
        $question = $questionRepo->findOneWithUsersAndComments($id);


        $user = $this->getUser();


        $options = [
            'question' => $question,
            'date' => new DateTimeImmutable()
        ];

        if ($user) {

            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment->setCreatedAt(new DateTimeImmutable())
                    ->setRating(0)
                    ->setUser($user)
                    ->setQuestion($question);

                $em->persist($comment);
                $em->flush();

                return $this->redirect($request->getUri());
            }

            $options['formComment'] = $commentForm->createView();
            $options['user'] = $user;
        }
        return $this->render('question/show.html.twig', $options);
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/delete/{id}', name: 'app_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, QuestionRepository $questionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $questionRepository->remove($question, true);
        }

        return $this->redirectToRoute('app_user', [], Response::HTTP_SEE_OTHER);
    }
}
