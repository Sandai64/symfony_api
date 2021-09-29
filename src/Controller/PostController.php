<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{
    private $post_repo;
    private $serializer;
    private $entity_manager;

    public function __construct(PostRepository $post_repo, SerializerInterface $serializer, EntityManagerInterface $entity_manager)
    {
        $this->post_repo = $post_repo;
        $this->serializer = $serializer;
        $this->entity_manager = $entity_manager;
    }

    /**
     * @Route("/api/post/get/all", name="api_post_get_all", methods="GET")
     */
    public function get_all(): Response
    {
        $all_posts = $this->serializer->serialize($this->post_repo->findAll(), 'json');
        return new Response($all_posts, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/api/post/get/{post_id}", name="api_post_get_post", methods="GET")
     */
    public function get_post(int $post_id): Response
    {
        try
        {
            $post_object = $this->post_repo->find($post_id);
            
            if ($post_object === null)
            {
                throw new Exception();
            }

            $post_json = $this->serializer->serialize($post_object, 'json');
            return new Response($post_json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }
        catch (\Throwable $th)
        {
            $error = $this->serializer->serialize(['error' => 'Post not found'], 'json');
            return new Response($error, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Route("/api/post/create", name="api_post_new_post", methods="POST")
     */
    public function create_post(Request $request) : Response
    {
        // Fetch the request's body
        try
        {
            $post_object = $this->serializer->deserialize($request->getContent(), Post::class, 'json');
            $post_object->setCreatedAt(new DateTime());

            $this->entity_manager->persist($post_object);
            $this->entity_manager->flush();

            return new Response($this->serializer->serialize($post_object, 'json'), Response::HTTP_CREATED);
        }
        catch (\Throwable $th)
        {
            $error = $this->serializer->serialize(['error' => 'Invalid request body'], 'json');
            return new Response('Invalid API request', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/api/post/delete/{post_id}", name="api_post_delete_post", methods="DELETE")
     */
    public function delete_post(Request $request, int $post_id) : Response
    {
        try
        {
            $this->entity_manager->remove($this->post_repo->find($post_id));
            $this->entity_manager->flush();
            
            return new Response(null, Response::HTTP_NO_CONTENT);
        }
        catch (\Throwable $th)
        {
            $error = $this->serializer->serialize(['error' => 'Post ID not found'], 'json');
            return new Response($error, Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * @Route("/api/post/update/{post_id}", name="api_post_update_post", methods="PUT")
     */
    public function update_post(Request $request, int $post_id)
    {
        try
        {
            $post_json = $request->getContent();
            $post_object = $this->post_repo->find($post_id);

            if ($post_object === null)
            {
                throw new Exception();
            }

            $this->serializer->deserialize($post_json, Post::class, 'json', ['object_to_populate' => $post_object]);
            $this->entity_manager->flush();

            $updated_post_json = $this->serializer->serialize($post_object, 'json');
            return new Response($updated_post_json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }
        catch (\Throwable $th)
        {
            $error = $this->serializer->serialize(['error' => 'Post ID not found'], 'json');
            return new Response($error, Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }
    }
}
