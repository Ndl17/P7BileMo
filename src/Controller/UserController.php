<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{

    #[Route('/api/users', name: 'users', methods: ['GET'])]

    #[OA\Response(
        response:200,
        description:"Retourne la liste des utilisateurs",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref:new Model(type: User::class))
        )
     )]

     #[OA\Parameter(
            name:"page",
            in:"query",
            description:"La page que l'on veut récupérer",
            schema: new OA\Schema(type: 'int')            
             )]

         
     #[OA\Parameter(
        name:"limit",
       in:"query",
        description:"Le nombre d'éléments que l'on veut récupérer",
        schema: new OA\Schema(type: 'int')            
        )]

    #[OA\Tag(name: 'Users')]

/**
 * Route pour récupérer tous les utilisateurs

 * @param \App\Repository\UserRepository $userRepository
 * @param \JMS\Serializer\SerializerInterface $serializer
 * @param \Symfony\Component\HttpFoundation\Request $request
 * @param \Symfony\Contracts\Cache\TagAwareCacheInterface $cache
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
public function getAllUsers(
    UserRepository $userRepository,
    SerializerInterface $serializer,
    Request $request,
    TagAwareCacheInterface $cache
): JsonResponse {
    // Récupération des paramètres page et limit
    $page = $request->query->get('page', 1);
    $limit = $request->query->get('limit', 5);
    // création de l'id du cache
    $idCache = 'getAllUsers_' . $page . '_' . $limit;
    // récupération du cache
    $jsonUserList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
        $item->tag('userListCache');
        $item->expiresAfter(1);
        //Récupérer la liste des utilisateurs et le nombre total d'utilisateurs dans le userRepository
        $usersPaginated = $userRepository->findAllUserPagination($page, $limit);
        $allUsers = $userRepository->findAll();
        $totalItems = count($allUsers);

        // Create Hateoas PaginatedRepresentation
        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation($usersPaginated),
            'users', // Route name
            ['page' => $page, 'limit' => $limit], // Route parameters
            $page, // Current page number
            $limit, // Limit per page
            ceil($totalItems / $limit), // Total number of pages
            'page', // Page route parameter name (optional)
            'limit', // Limit route parameter name (optional)
            true, // Generate relative URIs
            $totalItems // Total collection size
        );
        //sérialisation de la liste des utilisateurs
        $json = $serializer->serialize($paginatedCollection, 'json');
        //retourne une réponse json avec la liste des utilisateurs
        return $json;
    });
    //retourne une réponse json avec la liste des utilisateurs avec un code 200
    return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
}

#[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]

#[OA\Response(
    response:200,
    description:"Retourne la liste des téléphones",
    content: new OA\JsonContent(
        type: 'array',
        items: new OA\Items(ref:new Model(type: User::class))
    )
 )]

 #[OA\Parameter(
        name:"id",
        in:"path",
        description:"La page que l'on veut récupérer",
        schema: new OA\Schema(type: 'int')            
         )]

#[OA\Tag(name: 'Users')]
/**
 * 
 * Route pour récupérer un utilisateur par son id
 * @param \App\Entity\User $user
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
    //sérialisation de l'utilisateur
    $jsonUserList = $serializer->serialize($user, 'json');
    //retourne une réponse json avec l'utilisateur
    return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
}

#[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]

#[OA\Response(
    response:204,
    description:"Supprime un utilisateur",
 )]

 #[OA\Parameter(
        name:"id",
        in:"path",
        description:"l'id de l'utilisateur à supprimer",
        schema: new OA\Schema(type: 'int')            
         )]

#[OA\Tag(name: 'Users')]

/**
 * Route pour supprimer un utilisateur par son id
 *
 * @param \App\Entity\User $user
 * @param \Doctrine\ORM\EntityManagerInterface $entityManager
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function deleteUser(User $user, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
    //suppression du cache
    $cache->invalidateTags(['userListCache']);
    //suppression de l'utilisateur
    $entityManager->remove($user);
    $entityManager->flush();
    //retourne une réponse vide
    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}

#[Route('/api/users', name: 'addUser', methods: ['POST'])]

#[OA\Response(
    response:201,
    description:"Ajoute un utilisateur",
    content: new OA\JsonContent(
        type: 'array',
        items: new OA\Items(ref:new Model(type: User::class))
    )
 )]

 #[OA\RequestBody(
    description:"L'utilisateur à ajouter",
    required:true,
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "firstname", type: "string"),
            new OA\Property(property: "lastname", type: "string"),
            new OA\Property(property: "email", type: "string"),
            new OA\Property(property: "client_id", type: "integer"),
        ]
    )
 )]

 #[OA\Tag(name: 'Users')]
/**
 * Route pour ajouter un utilisateur
 *
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @param \Doctrine\ORM\EntityManagerInterface $entityManager
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function addUser(Request $request, ClientRepository $clientRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
    //récupération du contenu de la requête
     // et désérialisation du json en objet User
    $user = $serializer->deserialize($request->getContent(), User::class, 'json');
    //récupération du client
    $content = $request->toArray();
    $idClient = $content['client_id'] ?? -1;
    $user->setClient($clientRepository->find($idClient));
    //récupération des erreurs de validation
    $errors = $validator->validate($user);
    //si il y a des erreurs de validation on les retourne en json avec un code 400 (bad request)
    if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }
    //suppression du cache
    $cache->invalidateTags(['userListCache']);
    //création de l'utilisateur
    $entityManager->persist($user);
    $entityManager->flush();
    //création de l'url de l'utilisateur  et sérialisation de l'utilisateur  en json avec un code 201 (created)
    $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    $jsonUser = $serializer->serialize($user, 'json');
    return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
}

}
