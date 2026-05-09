<?php

namespace App\Entity\IArooms;

use App\Repository\IArooms\TimetableUploadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimetableUploadRepository::class)]
class TimetableUpload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $storedFilename = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $weekStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $weekEnd = null;

    #[ORM\Column]
    private int $totalPages = 0;

    #[ORM\Column]
    private int $totalBookings = 0;

    #[ORM\Column]
    private int $totalRooms = 0;

    #[ORM\Column]
    private int $ignoredOnlineSessions = 0;

    #[ORM\Column(options: ['default' => false])]
    private bool $usesMasterRoomList = false;

    /**
     * @var Collection<int, RoomBooking>
     */
    #[ORM\OneToMany(mappedBy: 'timetableUpload', targetEntity: RoomBooking::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $roomBookings;

    /**
     * @var Collection<int, RoomConflict>
     */
    #[ORM\OneToMany(mappedBy: 'timetableUpload', targetEntity: RoomConflict::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $roomConflicts;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
        $this->roomBookings = new ArrayCollection();
        $this->roomConflicts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getStoredFilename(): ?string
    {
        return $this->storedFilename;
    }

    public function setStoredFilename(string $storedFilename): static
    {
        $this->storedFilename = $storedFilename;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getWeekStart(): ?\DateTimeImmutable
    {
        return $this->weekStart;
    }

    public function setWeekStart(?\DateTimeImmutable $weekStart): static
    {
        $this->weekStart = $weekStart;

        return $this;
    }

    public function getWeekEnd(): ?\DateTimeImmutable
    {
        return $this->weekEnd;
    }

    public function setWeekEnd(?\DateTimeImmutable $weekEnd): static
    {
        $this->weekEnd = $weekEnd;

        return $this;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function setTotalPages(int $totalPages): static
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    public function getTotalBookings(): int
    {
        return $this->totalBookings;
    }

    public function setTotalBookings(int $totalBookings): static
    {
        $this->totalBookings = $totalBookings;

        return $this;
    }

    public function getTotalRooms(): int
    {
        return $this->totalRooms;
    }

    public function setTotalRooms(int $totalRooms): static
    {
        $this->totalRooms = $totalRooms;

        return $this;
    }

    public function getIgnoredOnlineSessions(): int
    {
        return $this->ignoredOnlineSessions;
    }

    public function setIgnoredOnlineSessions(int $ignoredOnlineSessions): static
    {
        $this->ignoredOnlineSessions = $ignoredOnlineSessions;

        return $this;
    }

    public function isUsesMasterRoomList(): bool
    {
        return $this->usesMasterRoomList;
    }

    public function setUsesMasterRoomList(bool $usesMasterRoomList): static
    {
        $this->usesMasterRoomList = $usesMasterRoomList;

        return $this;
    }

    /**
     * @return Collection<int, RoomBooking>
     */
    public function getRoomBookings(): Collection
    {
        return $this->roomBookings;
    }

    public function addRoomBooking(RoomBooking $roomBooking): static
    {
        if (!$this->roomBookings->contains($roomBooking)) {
            $this->roomBookings->add($roomBooking);
            $roomBooking->setTimetableUpload($this);
        }

        return $this;
    }

    public function removeRoomBooking(RoomBooking $roomBooking): static
    {
        if ($this->roomBookings->removeElement($roomBooking) && $roomBooking->getTimetableUpload() === $this) {
            $roomBooking->setTimetableUpload(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, RoomConflict>
     */
    public function getRoomConflicts(): Collection
    {
        return $this->roomConflicts;
    }

    public function addRoomConflict(RoomConflict $roomConflict): static
    {
        if (!$this->roomConflicts->contains($roomConflict)) {
            $this->roomConflicts->add($roomConflict);
            $roomConflict->setTimetableUpload($this);
        }

        return $this;
    }

    public function removeRoomConflict(RoomConflict $roomConflict): static
    {
        if ($this->roomConflicts->removeElement($roomConflict) && $roomConflict->getTimetableUpload() === $this) {
            $roomConflict->setTimetableUpload(null);
        }

        return $this;
    }
}
