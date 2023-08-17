<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phones', methods: ['GET'])]
/**
 * Route pour récupérer tous les téléphones
 * @OA\Response(
 *     response=200,
 *     description="Retourne la liste des livres",
 *     @OA\JsonContent(
 *        type="array",
 *        @OA\Items(ref=@Model(type=Phone::class, groups={"getPhones"}))
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
 * @OA\Tag(name="Phones")
 *
 * @param \App\Repository\PhoneRepository $phoneRepository
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
public function getAllPhones(
    PhoneRepository $phoneRepository,
    SerializerInterface $serializer,
    Request $request,
    TagAwareCacheInterface $cache
): JsonResponse {
    $page = $request->query->get('page', 1);
    $limit = $request->query->get('limit', 5);

    $idCache = 'getAllPhones_' . $page . '_' . $limit;
    $jsonPhonesList = $cache->get($idCache, function (ItemInterface $item) use ($phoneRepository, $page, $limit, $serializer) {
        //echo ('mise en cache');
        $item->tag('phoneListCache');
        $item->expiresAfter(20);
        $phonesPaginated = $phoneRepository->findAllPhonePagination($page, $limit);
        $allPhones = $phoneRepository->findAll();
        $totalItems = count($allPhones);
        // Create Hateoas PaginatedRepresentation
        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation($phonesPaginated),
            'phones', // Route name
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

    return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);

}

#[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]
/**
 * Route pour récupérer un téléphone par son id
 * @OA\Response(
 *    response=200,
 *   description="Retourne le détail d'un téléphone",
 *  @OA\JsonContent(
 *    type="array",
 *   @OA\Items(ref=@Model(type=Phone::class, groups={"getPhones"}))
 * )
 * )
 * @OA\Parameter(
 *   name="id",
 *  in="path",
 * description="L'id du téléphone",
 * @OA\Schema(type="string")
 * )
 *
 * @OA\Tag(name="Phones")
 *
 * @param \App\Entity\Phone $phone
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {

    $jsonPhonesList = $serializer->serialize($phone, 'json');
    return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);

}

}
