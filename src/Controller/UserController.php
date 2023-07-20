<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
/**
 * Route pour récupérer tous les utilisateurs
 * @param \App\Repository\UserRepository $userRepository
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
    $users = $userRepository->findAll();
    $jsonUserList = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
    return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
}


#[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]

/**
 * Route pour récupérer un utilisateur par son id
 * @param \App\Entity\User $user
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUserList = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    
}

}
