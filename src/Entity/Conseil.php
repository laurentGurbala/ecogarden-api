<?php

namespace App\Entity;

use App\Repository\ConseilRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConseilRepository::class)]
class Conseil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["read"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu du conseil est obligatoire")]
    #[Groups(["read", "write"])]
    private ?string $content = null;

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank(message: "la liste de mois du conseil est obligatoire")]
    #[Assert\All([
        new Assert\Type(type: 'integer', message: "Chaque mois doit être un entier."),
        new Assert\Range(
            notInRangeMessage: "Chaque mois doit être compris entre {{ min }} et {{ max }}.",
            min: 1,
            max: 12
        )
    ])]
    #[OA\Property(
    type: "array",
    items: new OA\Items(
        type: "integer",
        format: "int32",
        description: "Un mois entre 1 et 12")
    )]
    #[Groups(["read", "write"])]
    private array $mois = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getMois(): array
    {
        return $this->mois;
    }

    public function setMois(array $mois): static
    {
        $this->mois = $mois;

        return $this;
    }
}
