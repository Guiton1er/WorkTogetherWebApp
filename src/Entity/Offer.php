<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $promotionPercentage = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $unitLimit = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPromotionPercentage(): ?int
    {
        return $this->promotionPercentage;
    }

    public function setPromotionPercentage(int $promotionPercentage): static
    {
        $this->promotionPercentage = $promotionPercentage;

        return $this;
    }

    public function getUnitLimit(): ?int
    {
        return $this->unitLimit;
    }

    public function setUnitLimit(?int $unitLimit): static
    {
        $this->unitLimit = $unitLimit;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
