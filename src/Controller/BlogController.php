<?php
namespace App\Controller;

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
        return $this->render('blog/index.html.twig', [
            'owner' => 'Thomas',
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
        $slug = ucwords(str_replace('-', ' ', $slug));

        return $this->render('blog/show.html.twig', [
            'slug' => $slug
        ]);
    }
}
