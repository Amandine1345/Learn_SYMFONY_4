<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BlogController
 * @package App\Controller
 * @Route("/blog", name="blog_")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @return Response
     */
    public function index(): Response
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)
           ->findAllWithCategoriesTagsAndAuthor();

        if (!$articles) {
            throw $this->createNotFoundException(
                'No article found in article\'s table.'
            );
        }

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route({
     *     "fr": "/voir/{slug}",
     *     "en": "/show/{slug}",
     *     "es": "/ver/{slug}",
     * }, name="show",
     *     methods={"GET"},
     *     requirements={"slug" = "^[a-z0-9-]+"},
     *     defaults={"slug" = "article-sans-titre"}
     *     )
     * @param string $slug
     * @return Response
     */
    public function show(string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find an article in article\'s table.');
        }

        $article = $this->getDoctrine()->getRepository(Article::class)
            ->findOneBy(['slug' => mb_strtolower($slug)]);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article with '.$slug.' title, found in article\'s table.'
            );
        }

        return $this->render('blog/show.html.twig', [
                'article' => $article,
                'slug' => $slug,
        ]);
    }

    /**
     * @Route({
     *     "fr": "/categorie/{name}",
     *     "en": "/category/{name}",
     *     "es": "/categorías/{name}",
     * }, name="category",
     *     methods={"GET"},
     *     requirements={"name" = "^[a-z0-9-]+"},
     *     defaults={"name" = "category"}
     *     )
     * @param Category $category
     * @return Response
     */
    public function showByCategory(Category $category): Response
    {
        if (!$category) {
            throw $this
                ->createNotFoundException('No category name has been sent to find articles from category\'s table.');
        }

        return $this->render('blog/category.html.twig', [
            'articles' => $category->getArticles(),
            'category' => $category,
        ]);
    }
}
