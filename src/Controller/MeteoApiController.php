<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use OpenApi\Attributes as OA;
use phpDocumentor\Reflection\Types\This;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
    #[OA\Tag("météo")]
    public function getMeteo(HttpClientInterface $httpClient, CacheInterface $cache): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $ville = $user->getVille();

        return $this->getMeteoByCity($ville, $httpClient, $cache);
    }

    #[Route("api/meteo/{ville}", name: "meteo_ville", methods: ["GET"])]
    #[OA\Parameter(
        name: "ville",
        in: "path",
        required: true,
        description: "Nom de la ville à rechercher",
        schema: new OA\Schema(type: "string", example: "Paris")
    )]
    #[OA\Response(
        response: 200,
        description: "Météo de la ville demandée",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "ville", type: "string", example: "Paris"),
                new OA\Property(property: "température", type: "number", format: "float", example: 22.8),
                new OA\Property(property: "description", type: "string", example: "nuageux"),
            ]
        )
    )]
    #[OA\Tag("météo")]
    public function getMeteoByCity(string $ville, HttpClientInterface $httpClient, CacheInterface $cache): JsonResponse
    {
        $meteo = $this->fetchMeteoData($ville, $httpClient, $cache);

        if (!$meteo) {
            return $this->json(["erreur" => "Ville inconnue ou requête invalide"], 404);
        }

        return $this->json($meteo);
    }

    private function fetchMeteoData(string $ville, HttpClientInterface $httpClient, CacheInterface $cache): ?array
    {
        $ville = strtolower($ville);
        $cacheKey = "meteo_{$ville}";

        return $cache->get($cacheKey, function (ItemInterface $item) use ($ville, $httpClient) {
            $item->expiresAfter(600); // 10 minutes

            $apiKey = $_ENV["OPENWEATHER_API_KEY"];
            $url = "https://api.openweathermap.org/data/2.5/weather?q={$ville}&appid={$apiKey}&units=metric&lang=fr";

            try {
                $response = $httpClient->request("GET", $url);
                $data = $response->toArray();

                return [
                    "ville" => $data["name"],
                    "température" => $data["main"]["temp"],
                    "description" => $data["weather"][0]["description"],
                ];
            } catch (\Exception $e) {
                return null; // important : le cache ne sera pas enregistré si null est retourné
            }
        });
    }
}
