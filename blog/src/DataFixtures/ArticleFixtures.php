<?php
namespace App\DataFixtures;

use App\Entity\Article;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var Slugify
     */
    private $slugify;

    /**
     * AppFixtures constructor.
     * @param Slugify $slugify
     */
    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [CategoryFixtures::class, TagFixtures::class, UserFixtures::class];
    }
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 50; $i++) {
            $article = new Article();
            $article->setTitle(mb_strtolower($faker->sentence()));

            $slug =  $this->slugify->generate($article->getTitle());
            $article->setSlug($slug);

            $article->setContent($faker->paragraph(10));
            $manager->persist($article);
            $article->setCategory($this->getReference('category_' . rand(0,4)));
            $article->addTag($this->getReference('tag_' . rand(0,4)));
            $article->setAuthor($this->getReference('user_author'));
        }
        $manager->flush();
    }
}