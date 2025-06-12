<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class RegistrationController extends AbstractController
{
    #[Route('/api/user', name: 'create_user', methods: ["POST"])]
    #[OA\Post(summary: "Crée un nouvel utilisateur")]
    #[OA\RequestBody(
        required: true,
        description: "Données de l'utilisateur à créer",
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ["write"]),
            example: [
                "email" => "your@email.com",
                "password" => "yourPassword",
                "ville" => "Paris"
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Utilisateur créé avec succès",
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ["read"]),
            example: [
                "email" => "your@email.com",
                "ville" => "Paris"
            ]
        )
    )]
    #[OA\Tag(name: 'utilisateur')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            /** @var User $user */
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        } catch (NotNormalizableValueException $e) {
            return $this->json(['error' => 'Donnée invalide : ' . $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validation
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifie que le mot de passe est bien fourni (et pas vide par défaut)
        $data = json_decode($request->getContent(), true);
        $plainPassword = $data['password'] ?? null;

        if (!$plainPassword) {
            return $this->json(['error' => 'Le mot de passe est obligatoire.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Hash du mot de passe
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $user->setRoles(["ROLE_USER"]);

        $em->persist($user);
        $em->flush();

        return $this->json($user, JsonResponse::HTTP_CREATED, [], ['groups' => ['read']]);
    }

    #[Route("/api/user/{id}", name: "update_user", methods: ["PUT"])]
    #[IsGranted("ROLE_ADMIN", message: "Vous n'avez pas les droits suffisants")]
    #[OA\Put(summary: "Mettre à jour un utilisateur")]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "L'id de l'utilisateur à mettre à jour",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        description: "Données de l'utilisateur à mettre à jour",
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ["write"]),
            example: [
                "email" => "your@email.com",
                "password" => "yourPassword",
                "ville" => "Paris"
            ]
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Utilisateur mis à jour avec succès"
    )]
    #[OA\Tag(name: "utilisateur")]
    public function updateUser(
        Request $request,
        SerializerInterface $serializer,
        User $currentUser,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $serializer->deserialize(
                $request->getContent(),
                User::class,
                "json",
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
            );
        } catch (NotNormalizableValueException $e) {
            return $this->json(['error' => 'Donnée invalide : ' . $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($currentUser);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($currentUser);
        $em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route("/api/user/{id}", name: "delete_user", methods: ["DELETE"])]
    #[IsGranted("ROLE_ADMIN", message: "Vous n'avez pas les droits suffisants")]
    #[OA\Delete(summary: "Supprime un utilisateur")]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "L'id de l'utilisateur à supprimer",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 204,
        description: "Utilisateur supprimé avec succès"
    )]
    #[OA\Tag(name: 'utilisateur')]
    public function deleteConseil(User $conseil, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($conseil);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
