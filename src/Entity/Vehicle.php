<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column(length: 255)]
    private ?string $numberPlate = null;

    #[ORM\Column]
    private ?int $datePurchase = null;

    /**
     * @var Collection<int, Repair>
     */
    #[ORM\OneToMany(targetEntity: Repair::class, mappedBy: 'vehicle')]
    private Collection $repairs;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $service = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $insurance = null;

    public function __construct()
    {
        $this->repairs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getNumberPlate(): ?string
    {
        return $this->numberPlate;
    }

    public function setNumberPlate(string $numberPlate): static
    {
        $this->numberPlate = $numberPlate;

        return $this;
    }

    public function getDatePurchase(): ?int
    {
        return $this->datePurchase;
    }

    public function setDatePurchase(int $datePurchase): static
    {
        $this->datePurchase = $datePurchase;

        return $this;
    }

    /**
     * @return Collection<int, Repair>
     */
    public function getRepairs(): Collection
    {
        return $this->repairs;
    }

    public function addRepair(Repair $repair): static
    {
        if (!$this->repairs->contains($repair)) {
            $this->repairs->add($repair);
            $repair->setVehicle($this);
        }

        return $this;
    }

    public function removeRepair(Repair $repair): static
    {
        if ($this->repairs->removeElement($repair)) {
            // set the owning side to null (unless already changed)
            if ($repair->getVehicle() === $this) {
                $repair->setVehicle(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getService(): ?\DateTimeInterface
    {
        return $this->service;
    }

    public function setService(\DateTimeInterface $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getInsurance(): ?\DateTimeInterface
    {
        return $this->insurance;
    }

    public function setInsurance(\DateTimeInterface $insurance): static
    {
        $this->insurance = $insurance;

        return $this;
    }
}
