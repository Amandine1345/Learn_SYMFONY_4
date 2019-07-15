<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\Slugify;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "fr": "/article",
 *     "en": "/article",
 *     "es": "/articulo",
 * })
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index", methods={"GET"})
     * @param ArticleRepository $articleRepository
     * @return Response
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAllWithCategoriesAndTags(),
        ]);
    }

    /**
     * @Route({
     *     "fr": "/creer",
     *     "en": "/new",
     *     "es": "/crear",
     * }, name="article_new", methods={"GET","POST"})
     * @param Request $request
     * @param Slugify $slugify
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function new(Request $request, Slugify $slugify, \Swift_Mailer $mailer, TranslatorInterface $translator): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($article->getTitle());
            $article->setSlug($slug);
            $author = $this->getUser();
            $article->setAuthor($author);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            // Send email to administrator
            $administratorEmail = $this->getParameter('mailer_from');
            $message = (new \Swift_Message($translator->trans('admin.email.object')))
                ->setFrom( $administratorEmail)
                ->setTo( $administratorEmail)
                ->setBody(
                    $this->renderView('emails/notification.html.twig', ['article' => $article]),
                    'text/html'
                );
            $mailer->send($message);
            // --

            $this->addFlash('success', $translator->trans('admin.article.add'));

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_show", methods={"GET"})
     * @param Article $article
     * @return Response
     */
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'isFavorite' => $this->getUser()->isFavorite($article)
        ]);
    }

    /**
     * @Route({
     *     "fr": "/{id}/edition",
     *     "en": "/{id}/edit",
     *     "es": "/{id}/editar",
     * }, name="article_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Article $article
     * @param Slugify $slugify
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function edit(Request $request, Article $article, Slugify $slugify, TranslatorInterface $translator): Response
    {
        // Access only if ROLE_ADMIN, or if it's  the article author
        if ($article->getAuthor() != $this->getUser() &&  !($this->isGranted("ROLE_ADMIN"))) {
           //throw $this->createAccessDeniedException();
            $this->addFlash('danger', $translator->trans('admin.accessonlyauthor'));
            return $this->redirectToRoute('article_index');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($article->getTitle());
            $article->setSlug($slug);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('admin.article.edit'));

            return $this->redirectToRoute('article_index', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     * @param Request $request
     * @param Article $article
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete(Request $request, Article $article, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('danger', $translator->trans('admin.article.delete'));
        }

        return $this->redirectToRoute('article_index');
    }

    /**
     * @Route("/{id}/favorite", name="article_favorite", methods={"GET","POST"})
     * @param Article $article
     * @param ObjectManager $manager
     * @return Response
     */
    public function favorite(Article $article, ObjectManager $manager): Response
    {
        if ($this->getUser()->getFavorites()->contains($article)) {
            $this->getUser()->removeFavorite($article)   ;
        }
        else {
            $this->getUser()->addFavorite($article);
        }

        $manager->flush();

        return $this->json([
            'isFavorite' => $this->getUser()->isFavorite($article)
        ]);
    }
}
