<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use OpenApi\Attributes as OA;

final class MeteoApiController extends AbstractController
{
    #[Route('/api/meteo', name: 'meteo', methods: ["GET"])]
    #[OA\Response(
        response: 200,
        description: "Données météo pour la ville de l'utilisateur",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "ville", type: "string", example: "Paris"),
                new OA\Property(property: "température", type: "number", format: "float", example: 18.5),
                new OA\Property(property: "description", type: "string", example: "ciel dégagé")
            ]
        )
    )]
    public function index(HttpClientInterface $httpClient): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        $ville = $user->getVille();

        $apiKey = $_ENV["OPENWEATHER_API_KEY"];
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$ville}&appid={$apiKey}&units=metric&lang=fr";

        try {
            $response = $httpClient->request("GET", $url);
            $data = $response->toArray();

            $meteo = [
                "ville" => $data["name"],
                "température" => $data["main"]["temp"],
                "description" => $data["weather"][0]["description"],
            ];

            return $this->json($meteo);
        } catch (ClientExceptionInterface $e) {
            return $this->json(["erreur" => "requête invalide !"], 404);
        }
    }

    
}
