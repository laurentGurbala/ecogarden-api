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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class ConseilController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: "Retourne la liste des conseils du mois en cours",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: Conseil::class, groups: ["read"]))
        )
    )]
    #[OA\Tag(name: 'conseil')]
    #[Route('/api/conseil', name: 'conseil', methods: ["GET"])]
    public function getConseilList(ConseilRepository $conseilRepository, SerializerInterface $serializer): JsonResponse
    {
        $conseilList = $conseilRepository->findByCurrentMonth();
        $jsonConseilList = $serializer->serialize($conseilList, "json");

        return new JsonResponse($jsonConseilList, Response::HTTP_OK, [], true);
    }

    #[OA\Parameter(
        name: "mois",
        in: "path",
        required: true,
        description: "Le mois pour lequel récupérer les conseils (1 à 12)",
        schema: new OA\Schema(type: "integer", minimum: 1, maximum: 12)
    )]
    #[OA\Response(
        response: 200,
        description: "Retourne la liste des conseils pour le mois donné",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: Conseil::class, groups: ["read"]))
        )
    )]
    #[OA\Tag(name: 'conseil')]
    #[Route("/api/conseil/{mois}", name: "conseil_by_month", methods: ["GET"])]
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

    #[Route("/api/conseil", name: "create_conseil", methods:["POST"])]
    #[IsGranted("ROLE_ADMIN", message: "Vous n'avez pas les droits suffisants")]
    #[OA\RequestBody(
        required: true,
        description: "Données du conseil à créer",
        content: new OA\JsonContent(
            ref: new Model(type: Conseil::class, groups: ["write"])
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Conseil créé avec succès",
        content: new OA\JsonContent(
            ref: new Model(type: Conseil::class, groups: ["read"])
        )
    )]
    #[OA\Tag(name: 'conseil')]
    public function createConseil(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator): JsonResponse
    {
        try {
            /** @var Conseil $conseil */
            $conseil = $serializer->deserialize($request->getContent(), Conseil::class, "json");
        } catch (NotNormalizableValueException $e) {
            return new JsonResponse([
                'error' => 'Donnée invalide : ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

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
    #[IsGranted("ROLE_ADMIN", message: "Vous n'avez pas les droits suffisants")]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "L'id du conseil à mettre à jour",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        description: "Données du conseil à mettre à jour",
        content: new OA\JsonContent(
            ref: new Model(type: Conseil::class, groups: ["write"])
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Conseil mis à jour avec succès"
    )]
    #[OA\Tag(name: 'conseil')]
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

    #[Route("/api/conseil/{id}", name: "delete_conseil", methods: ["DELETE"])]
    #[IsGranted("ROLE_ADMIN", message: "Vous n'avez pas les droits suffisants")]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "L'id du conseil à supprimer",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 204,
        description: "Conseil supprimé avec succès"
    )]
    #[OA\Tag(name: 'conseil')]
    public function deleteConseil(Conseil $conseil, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($conseil);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
