<?php

namespace App\Controller;

use App\Entity\Conseil;
use App\Repository\ConseilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ConseilController extends AbstractController
{
    #[Route('/api/conseil', name: 'conseil', methods: ["GET"])]
    public function getConseilList(ConseilRepository $conseilRepository, SerializerInterface $serializer): JsonResponse
    {
        $conseilList = $conseilRepository->findByCurrentMonth();
        $jsonConseilList = $serializer->serialize($conseilList, "json");

        return new JsonResponse($jsonConseilList, Response::HTTP_OK, [], true);
    }

    #[Route("/api/conseil/{mois}", name: "conseil_by_month", methods: ["GET"], requirements: ['mois' => '\d+'])]
    public function getConseilByMonth(
        int $mois, 
        ConseilRepository $conseilRepository, 
        SerializerInterface $serializer): JsonResponse
    {
        if ($mois < 1 || $mois > 12) {
            throw new BadRequestHttpException("Le mois doit être compris entre 1 et 12.");
        }

        $conseilList = $conseilRepository->findByMonth($mois);
        $jsonConseilList = $serializer->serialize($conseilList, "json");

        return new JsonResponse($jsonConseilList, Response::HTTP_OK, [], true);
    }

    #[Route("/api/conseil/{id}", name: "delete_conseil", methods: ["DELETE"])]
    public function deleteConseil(Conseil $conseil, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($conseil);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/api/conseil", name: "create_conseil", methods:["POST"])]
    public function createConseil(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator): JsonResponse
    {
        $conseil = $serializer->deserialize($request->getContent(), Conseil::class, "json");

        $errors = $validator->validate($conseil);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($conseil);
        $em->flush();

        $jsonConseil = $serializer->serialize($conseil, "json");

        return new JsonResponse($jsonConseil, Response::HTTP_CREATED, [], true);
    }

    #[Route("/api/conseil/{id}", name: "update_conseil", methods: ["PUT"])]
    public function updateConseil(
        Request $request,
        SerializerInterface $serializer,
        Conseil $currentConseil,
        EntityManagerInterface $em,
        ValidatorInterface $validator) : JsonResponse
    {
        $serializer->deserialize($request->getContent(), Conseil::class, 'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE => $currentConseil]);

        $em->persist($currentConseil);
        $em->flush();

        $errors = $validator->validate($currentConseil);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
