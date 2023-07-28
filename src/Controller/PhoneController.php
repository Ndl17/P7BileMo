<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phones', methods: ['GET'])]
/**
 * route pour récupérer tous les téléphones
 * @param \App\Repository\PhoneRepository $phoneRepository
 * @param \Symfony\Component\Serializer\SerializerInterface $serializer
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 */
public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer, Request $request,TagAwareCacheInterface $cache): JsonResponse
    {
    $page = $request->query->get('page', 1);
    $limit = $request->query->get('limit', 5);

    $idCache = 'getAllPhones_' . $page . '_' . $limit;
    $jsonPhonesList = $cache->get($idCache, function (ItemInterface $item) use ($phoneRepository, $page, $limit, $serializer) {
        echo ('mise en cache');
        $item->tag('phoneListCache');
        $phoneList = $phoneRepository->findAllPhonePagination($page, $limit);

         $item->expiresAfter(1);
        return $serializer->serialize($phoneList, 'json');

    });
  //  $jsonPhonesList = $serializer->serialize($phones, 'json');
    return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);

}

#[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]
/**
 * Route pour récupérer un téléphone par son id
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
