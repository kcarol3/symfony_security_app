<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiController extends AbstractController
{
    #[Route('/api/posts', name: 'app_api', methods: ["GET"])]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $posts = $em->getRepository(Post::class)->findAll();
        $returnData = [];
        foreach ($posts as $post) {
            $returnData[] = [
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'createdAt' => $post->getCreatedAt(),
                'author' => $post->getCreatedBy()->getEmail()
            ];
        }

        return new JsonResponse($returnData);
    }

    #[Route('/api/post', name: 'app_post_api_new', methods: ['POST'])]
    public function apiNew(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'], $data['content'])) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $post = new Post();
        $post->setTitle($data['title'])
            ->setContent($data['content'])
            ->setCreatedBy($this->getUser())
            ->setCreatedAt(new \DateTimeImmutable('now'));

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Post created successfully', 'id' => $post->getId()], Response::HTTP_CREATED);
    }

}
