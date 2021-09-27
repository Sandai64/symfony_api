<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_post_get_posts", methods="GET")
     */
    public function get_posts(PostRepository $post_repo, SerializerInterface $serializer): Response
    {
        // Dependency injection, Symfony will automatically handle the routes, parameters and requests
        $all_posts = $post_repo->findAll();

        // Queried Posts need to be normalized before being json_encoded
        $posts_json = $serializer->serialize($all_posts, 'json');

        return new Response($posts_json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
