<?php

namespace App\Controller;

use App\Repository\ConseilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ConseilController extends AbstractController
{
    #[Route('/api/conseil', name: 'conseil', methods: ["GET"])]
    public function getConseilList(ConseilRepository $conseilRepository, SerializerInterface $serializer): JsonResponse
    {
        $conseilList = $conseilRepository->findByCurrentMonth();
        $jsonConseilList = $serializer->serialize($conseilList, "json");

        return new JsonResponse($jsonConseilList, Response::HTTP_OK, [], true);
    }
}
