<?php

namespace App\Entity;

use App\Repository\ConseilRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConseilRepository::class)]
class Conseil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu du conseil est obligatoire")]
    private ?string $content = null;

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank(message: "la liste de mois du conseil est obligatoire")]
    // #[Assert\Type('array', message: "Le champ 'mois' doit être un tableau d'entiers")] // Ne fonctionne pas
    #[Assert\All([
        new Assert\Type(type: 'integer', message: "Chaque mois doit être un entier."),
        new Assert\Range(
            notInRangeMessage: "Chaque mois doit être compris entre {{ min }} et {{ max }}.",
            min: 1,
            max: 12
        )
    ])]
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
