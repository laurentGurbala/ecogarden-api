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

final class RegistrationController extends AbstractController
{
    #[Route('/api/user', name: 'create_user', methods: ["POST"])]
    #[OA\RequestBody(
        required: true,
        description: "Données de l'utilisateur à créer",
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ["write"])
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Utilisateur créé avec succès",
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ["read"])
        )
    )]
    #[OA\Tag(name: 'utilisateur')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): JsonResponse
    {
        // Désérialisation du JSON vers un objet User
        /** @var User $user */
        $user = $serializer->deserialize($request->getContent(), User::class, "json");
        
        // Validation
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Vérifie que le mot de passe est bien fourni (et pas vide par défaut)
        $data = json_decode($request->getContent(), true);
        $plainPassword = $data['password'] ?? null;

        if (!$plainPassword) {
            return new JsonResponse(['error' => 'Le mot de passe est obligatoire.'], 400);
        }

        // Hash du mot de passe
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $user->setRoles(["ROLE_USER"]);

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => ['read']]);

        return new JsonResponse($jsonUser, JsonResponse::HTTP_CREATED, [], true);
    }
}
