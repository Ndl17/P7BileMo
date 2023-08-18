<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
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
/**
 * Route pour récupérer tous les utilisateurs
 * @OA\Response(
 *     response=200,
 *     description="Retourne la liste des utilisateurs",
 *     @OA\JsonContent(
 *        type="array",
 *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
 *     )
 * )
 * @OA\Parameter(
 *     name="page",
 *     in="query",
 *     description="La page que l'on veut récupérer",
 *     @OA\Schema(type="int")
 * )
 *
 * @OA\Parameter(
 *     name="limit",
 *     in="query",
 *     description="Le nombre d'éléments que l'on veut récupérer",
 *     @OA\Schema(type="int")
 * )
 * @OA\Tag(name="Users")
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
): JsonResponse{
    $page = $request->query->get('page', 1);
    $limit = $request->query->get('limit', 5);

    $idCache = 'getAllUsers_' . $page . '_' . $limit;

    $jsonUserList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
        $item->tag('userListCache');
        $item->expiresAfter(1);

        // Fetch user list and total items from UserRepository
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

        $json = $serializer->serialize($paginatedCollection, 'json');

        return $json;
    });

    return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
}

#[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
/**
 * 
 * Route pour récupérer un utilisateur par son id
 * @OA\Response(
 *    response=200,
 *   description="Retourne le détail d'un utilisateur",
 *  @OA\JsonContent(
 *    type="array",
 *   @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
 * )
 * )
 * @OA\Parameter(
 *   name="id",
 *  in="path",
 * description="L'id de l'utilisateur",
 * @OA\Schema(type="string")
 * )
 *
 * @OA\Tag(name="Users")
 * 
 * Route pour récupérer un utilisateur par son id
 * @param \App\Entity\User $user
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
    $jsonUserList = $serializer->serialize($user, 'json' );
    return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
}

#[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
/**
 * Route pour supprimer un utilisateur par son id
 * @OA\Response(
 *  response=204,
 * description="Supprime un utilisateur",
 * )
 * @OA\Parameter(
 * name="id",
 * in="path",
 * description="L'id de l'utilisateur",
 * @OA\Schema(type="string")
 * )
 * @OA\Tag(name="Users")
 * 
 * @param \App\Entity\User $user
 * @param \Doctrine\ORM\EntityManagerInterface $entityManager
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function deleteUser(User $user, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
    $cache->invalidateTags(['userListCache']);
    $entityManager->remove($user);
    $entityManager->flush();
    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}

#[Route('/api/users', name: 'addUser', methods: ['POST'])]
/**
 * Route pour ajouter un utilisateur
 * @OA\Response(
 *   response=201,
 *  description="Ajoute un utilisateur",
 * @OA\JsonContent(
 *  type="array",
 * @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
 * )
 * )
 * @OA\RequestBody(
 * description="Ajoute un utilisateur",
 * @OA\JsonContent(
 * type="array",
 * @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
 * )
 * )
 * @OA\Tag(name="Users")
 * 
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @param \Doctrine\ORM\EntityManagerInterface $entityManager
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function addUser(Request $request, ClientRepository $clientRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
    $user = $serializer->deserialize($request->getContent(), User::class, 'json');
    $content = $request->toArray();
    $idClient = $content['client_id'] ?? -1;
    $user->setClient($clientRepository->find($idClient));

    $errors = $validator->validate($user);

    if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }
    $cache->invalidateTags(['userListCache']);
    $entityManager->persist($user);
    $entityManager->flush();
    $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    $jsonUser = $serializer->serialize($user, 'json');
    return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
}

}
