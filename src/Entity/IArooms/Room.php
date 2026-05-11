<?php

namespace App\Entity\IArooms;

use App\Repository\IArooms\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ORM\Table(name: '`room`')]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $building = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $capacity = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, RoomBooking>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomBooking::class)]
    private Collection $roomBookings;

    /**
     * @var Collection<int, RoomConflict>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomConflict::class)]
    private Collection $roomConflicts;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->roomBookings = new ArrayCollection();
        $this->roomConflicts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBuilding(): ?string
    {
        return $this->building;
    }

    public function setBuilding(?string $building): static
    {
        $this->building = $building;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, RoomBooking>
     */
    public function getRoomBookings(): Collection
    {
        return $this->roomBookings;
    }

    /**
     * @return Collection<int, RoomConflict>
     */
    public function getRoomConflicts(): Collection
    {
        return $this->roomConflicts;
    }
}
