<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

#[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
/**
 * Route pour supprimer un utilisateur par son id
 * @param \App\Entity\User $user
 * @param \Doctrine\ORM\EntityManagerInterface $entityManager
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

}

#[Route('/api/users', name: 'addUser', methods: ['POST'])]
/**
 * Route pour ajouter un utilisateur
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @param \Doctrine\ORM\EntityManagerInterface $entityManager
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function addUser(Request $request,ClientRepository $clientRepository ,SerializerInterface $serializer, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');


        $content = $request->toArray();
        $idClient = $content['client_id'] ?? -1;
        $user->setClient($clientRepository->find($idClient));
        $entityManager->persist($user);
        $entityManager->flush();
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser,Response::HTTP_CREATED,["location"=>$location],true);
}

}
