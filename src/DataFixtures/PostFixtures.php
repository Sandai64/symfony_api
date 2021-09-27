<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Initialize Faker
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i <= 6; $i++)
        {
            $post = new Post();
            
            // We can chain object setters like so
            $post->setTitle($faker->sentence())
                 ->setContent($faker->paragraphs(8), true)
                 ->setCreatedAt($faker->dateTimeBetween('-6 months'));

            $manager->persist($post);
        }

        $manager->flush();
    }
}
