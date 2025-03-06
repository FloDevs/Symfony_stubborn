<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'float')]
    private ?float $price = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'boolean')]
    private bool $featured = false;

    #[ORM\Column(type: 'json')]
    private array $stock = [
        'XS' => 2,
        'S' => 2,
        'M' => 2,
        'L' => 2,
        'XL' => 2
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;
        return $this;
    }

    public function getStock(): array
    {
        return $this->stock;
    }

    public function setStock(array $stock): self
    {
        $this->stock = $stock;
        return $this;
    }
}
