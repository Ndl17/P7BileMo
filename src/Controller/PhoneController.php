<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phones', methods: ['GET'])]

    #[OA\Response(
            response:200,
            description:"Retourne la liste des téléphones",
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref:new Model(type: Phone::class))
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

        #[OA\Tag(name: 'Phones')]

/**
 * Route pour récupérer tous les téléphones
 * @param \App\Repository\PhoneRepository $phoneRepository
 * @param \JMS\Serializer\SerializerInterface $serializer
 * @param \Symfony\Component\HttpFoundation\Request $request
 * @param \Symfony\Contracts\Cache\TagAwareCacheInterface $cache
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
public function getAllPhones(
    PhoneRepository $phoneRepository,
    SerializerInterface $serializer,
    Request $request,
    TagAwareCacheInterface $cache
): JsonResponse {
    // Récupération des paramètres page et limit
    $page = $request->query->get('page', 1);
    $limit = $request->query->get('limit', 5);
    //création d'un id pour le cache
    $idCache = 'getAllPhones_' . $page . '_' . $limit;
    // récupération du cache 
    $jsonPhonesList = $cache->get($idCache, function (ItemInterface $item) use ($phoneRepository, $page, $limit, $serializer) {
        // mise en cache des données
        // Ajout du tag pour le cache
        $item->tag('phoneListCache');
        //delais d'expiration du cache
        $item->expiresAfter(20);
        //Récupérer la liste des téléphones et le nombre total de téléphones dans le phoneRepository
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
        //sérialisation de la liste des téléphones
        $json = $serializer->serialize($paginatedCollection, 'json');
        //retourne la liste des téléphones
        return $json;

    });
    // retourne la liste des téléphones en json avec un code 200
    return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);

}

#[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]

#[OA\Response(
    response:200,
    description:"Retourne le detail d'un téléphone",
    content: new OA\JsonContent(
        type: 'array',
        items: new OA\Items(ref:new Model(type: Phone::class))
    )
 )]

 #[OA\Parameter(
        name:"id",
        in:"path",
        description:"L'id du téléphone",
        schema: new OA\Schema(type: 'string')            
         )]

     
    #[OA\Tag(name: 'Phones')]

/**
 *Route pour récupérer le détail d'un téléphone
 *
 * @param \App\Entity\Phone $phone
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
     //sérialisation du téléphone 
    $jsonPhonesList = $serializer->serialize($phone, 'json');
    //retourne une réponse json avec le téléphone
    return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);

}

}
