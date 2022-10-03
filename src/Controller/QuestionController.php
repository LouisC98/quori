<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Question;
use App\Entity\Vote;
use App\Form\CommentType;
use App\Form\QuestionType;
use App\Repository\CommentRepository;
use App\Repository\QuestionRepository;
use App\Repository\VoteRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function show(Question $question, Request $request, EntityManagerInterface $em, CommentRepository $commentRepo): Response
    {
        $options = [
            'question' => $question,
            'comments' => $commentRepo->findBy(
                ['question' => $question],
                [
                    'rating' => 'DESC',
                    'createdAt' => 'DESC'
                ]
            )
        ];

        if ($this->getUser()) {

            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);

            if ($commentForm->isSubmitted() && $commentForm->isValid()) {

                $comment->setCreatedAt(new DateTimeImmutable())
                    ->setRating(0)
                    ->setUser($this->getUser())
                    ->setQuestion($question);

                $em->persist($comment);
                $em->flush();

                return $this->redirect($request->getUri());
            }

            $options['formComment'] = $commentForm->createView();
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

        return $this->redirectToRoute('app_user_questions', ['id' => $question->getUser()], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('question/{id}/{score}', name: 'question_rating', methods: ['GET', 'POST'])]
    public function questionRating(Question $question, int $score, VoteRepository $voteRepo, EntityManagerInterface $em, Request $request)
    {
        $currentUser = $this->getUser();

        // je vérifie que le currentUser est différent de l'auteur de la question
        if ($currentUser !== $question->getUser()) {

            // je vérifie si le currentUser à déja voté à la question en cherchant dans le repository
            $vote = $voteRepo->findOneBy([
                'user' => $currentUser,
                'question' => $question
            ]);

            if ($vote) {
                if (($vote->isIsLiked() && $score > 0) || (!$vote->isIsLiked() && $score < 0)) {
                    $em->remove($vote);
                    $question->setRating($question->getRating() + ($score > 0 ? -1 : 1));
                } else {
                    $vote->setIsLiked(!$vote->isIsLiked());
                    $question->setRating($question->getRating() + ($score > 0 ? 2 : -2));
                }
            } else {
                $vote = new Vote();
                $vote->setUser($currentUser)
                    ->setQuestion($question)
                    ->setIsLiked($score > 0 ? true : false);

                $em->persist($vote);
                $question->setRating($question->getRating() + $score);
            }
            $em->flush();
        }

        $referer = $request->server->get('HTTP_REFERER');

        return $this->redirect($referer);;
    }

    #[IsGranted('ROLE_USER')]
    #[Route('comment/{id}/{score}', name: 'comment_rating', methods: ['GET', 'POST'])]
    public function commentRating(Comment $comment, int $score, VoteRepository $voteRepo, EntityManagerInterface $em, Request $request)
    {
        $currentUser = $this->getUser();

        // je vérifie que le currentUser est différent de l'auteur du commentaire
        if ($currentUser !== $comment->getUser()) {

            // je vérifie si le currentUser à déja voté à la comment en cherchant dans le repository
            $vote = $voteRepo->findOneBy([
                'user' => $currentUser,
                'comment' => $comment
            ]);

            if ($vote) {
                if (($vote->isIsLiked() && $score > 0) || (!$vote->isIsLiked() && $score < 0)) {
                    $em->remove($vote);
                    $comment->setRating($comment->getRating() + ($score > 0 ? -1 : 1));
                } else {
                    $vote->setIsLiked(!$vote->isIsLiked());
                    $comment->setRating($comment->getRating() + ($score > 0 ? 2 : -2));
                }
            } else {
                $vote = new Vote();
                $vote->setUser($currentUser)
                    ->setComment($comment)
                    ->setIsLiked($score > 0 ? true : false);

                $em->persist($vote);
                $comment->setRating($comment->getRating() + $score);
            }
            $em->flush();
        }

        $referer = $request->server->get('HTTP_REFERER');

        return $this->redirect($referer);;
    }
}
