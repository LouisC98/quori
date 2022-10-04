<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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

    #[Route('/test', name: 'test')]
    public function testMail(MailerInterface $mailer): JsonResponse
    {
        $email = (new Email())
        ->from('contact@carvalho-louis.fr')
        ->to('louxor78310@gmail.com')
        ->subject('TEST MAIL')
        ->text('TEST MAIL');

        $mailer->send($email);

        return $this->json(json_encode('TEST'));
    }
}
