<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\News;
use App\Form\CommentType;
use App\Form\NewsType;
use App\Repository\CommentRepository;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route('/news')]
class NewsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Environment $twig;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }


    #[Route('/', name: 'news_index', methods: ['GET'])]
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('news/index.html.twig', [
            'news' => $newsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'news_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);
        $news->setAuthor($this->getUser()->getUsername());
        $news->setDateAdded(new \DateTime('@' . strtotime('now')));
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($news);
            $entityManager->flush();
            return $this->redirectToRoute('news_show',['id'=>$news->getId()]);
        }

        return $this->render('news/new.html.twig', [
            'news' => $news,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'news_show', methods: ['GET'])]
    public function show(News $news, CommentRepository $commentRepository, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);
        if ($news->getPreviewCounter() != null && $this->getUser() != null) {
            if ($this->getUser()->getUsername() != $news->getAuthor()){
                $news->setPreviewCounter($news->getPreviewCounter() + 1);
            }
        } else {
            $news->setPreviewCounter(1);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($news);
        $entityManager->flush();
        if ($form->isSubmitted()) {
            $comment->setAuthor($this->getUser()->getUsername());
            ;$comment->setCreatedAt(new \DateTime('@' . strtotime('now')));
            $comment->setText($form->get('text')->getData());
            $comment->setNews($news);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('news_show',['id'=>$news->getId()]);
        }
        return $this->render('news/show.html.twig', [
            'form' => $form->createView(),
            'news' => $news,
            'comments' => $commentRepository->findBy(['news' => $news], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}/edit', name: 'news_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, News $news, CommentRepository $commentRepository): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);
        $news->setDateAdded(new \DateTime('@' . strtotime('now')));
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->render('news/show.html.twig', [
                'news' => $news,
                'comments' => $commentRepository->findBy(['news' => $news], ['createdAt' => 'DESC']),
            ]);
        }

        return $this->render('news/edit.html.twig', [
            'news' => $news,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'news_delete', methods: ['POST'])]
    public function delete(Request $request, News $news): Response
    {
        if ($this->isCsrfTokenValid('delete' . $news->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($news);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home_page');
    }
}
