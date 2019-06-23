<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();

        if (!$articles) {
            throw $this->createNotFoundException(
                'No article found in article\'s table.'
            );
        }

        return $this->render('blog/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route(
     *     "/show/{slug}",
     *     name="show",
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

        $slug = preg_replace('/-/', ' ', ucwords(trim(strip_tags($slug)), "-"));

        $article = $this->getDoctrine()->getRepository(Article::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

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
     * @Route(
     *     "/category/{categoryName}",
     *     name="category",
     *     methods={"GET"},
     *     requirements={"slug" = "^[a-z0-9-]+"},
     *     defaults={"slug" = "category"}
     *     )
     * @param string $categoryName
     * @return Response
     */
    public function showByCategory(string $categoryName): Response
    {
        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No category name has been sent to find articles from category\'s table.');
        }

        $categoryName = preg_replace('/-/', ' ', ucwords(trim(strip_tags($categoryName)), "-"));

        $category = $this->getDoctrine()->getRepository(Category::class)
            ->findOneBy(['name' => mb_strtolower($categoryName)]);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category with '.$categoryName.' name, found in category\'s table.'
            );
        }

        return $this->render('blog/category.html.twig', [
            'articles' => $category->getArticles(),
            'category' => $category,
        ]);
    }
}
