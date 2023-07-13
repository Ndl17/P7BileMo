<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phones', methods: ['GET'])]
public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer): JsonResponse
    {
    $phones = $phoneRepository->findAll();
    $jsonPhonesList = $serializer->serialize($phones, 'json');
    return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);

}


#[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]
function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        $jsonPhonesList = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhonesList, Response::HTTP_OK, [], true);
    
}
}
