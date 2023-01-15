<?php

namespace App\Entity;

use App\Repository\ImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagesRepository::class)]
class Images
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imgCar = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?cars $imgCars = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImgCar(): ?string
    {
        return $this->imgCar;
    }

    public function setImgCar(string $imgCar): self
    {
        $this->imgCar = $imgCar;

        return $this;
    }

    public function getCars(): ?cars
    {
        return $this->imgCars;
    }

    public function setCars(?cars $imgCars): self
    {
        $this->imgCars = $imgCars;

        return $this;
    }
}
