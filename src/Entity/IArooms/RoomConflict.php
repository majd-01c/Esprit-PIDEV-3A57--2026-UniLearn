<?php

namespace App\Entity\IArooms;

use App\Repository\IArooms\RoomConflictRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomConflictRepository::class)]
class RoomConflict
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'roomConflicts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?TimetableUpload $timetableUpload = null;

    #[ORM\ManyToOne(inversedBy: 'roomConflicts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Room $room = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $bookingDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    private ?string $bookingAGroupName = null;

    #[ORM\Column(length: 255)]
    private ?string $bookingACourseName = null;

    #[ORM\Column]
    private int $bookingASourcePage = 0;

    #[ORM\Column(length: 100)]
    private ?string $bookingBGroupName = null;

    #[ORM\Column(length: 255)]
    private ?string $bookingBCourseName = null;

    #[ORM\Column]
    private int $bookingBSourcePage = 0;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $bookingAStartTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $bookingAEndTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $bookingBStartTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $bookingBEndTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimetableUpload(): ?TimetableUpload
    {
        return $this->timetableUpload;
    }

    public function setTimetableUpload(?TimetableUpload $timetableUpload): static
    {
        $this->timetableUpload = $timetableUpload;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getBookingDate(): ?\DateTimeImmutable
    {
        return $this->bookingDate;
    }

    public function setBookingDate(\DateTimeImmutable $bookingDate): static
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBookingAGroupName(): ?string
    {
        return $this->bookingAGroupName;
    }

    public function setBookingAGroupName(string $bookingAGroupName): static
    {
        $this->bookingAGroupName = $bookingAGroupName;

        return $this;
    }

    public function getBookingACourseName(): ?string
    {
        return $this->bookingACourseName;
    }

    public function setBookingACourseName(string $bookingACourseName): static
    {
        $this->bookingACourseName = $bookingACourseName;

        return $this;
    }

    public function getBookingASourcePage(): int
    {
        return $this->bookingASourcePage;
    }

    public function setBookingASourcePage(int $bookingASourcePage): static
    {
        $this->bookingASourcePage = $bookingASourcePage;

        return $this;
    }

    public function getBookingBGroupName(): ?string
    {
        return $this->bookingBGroupName;
    }

    public function setBookingBGroupName(string $bookingBGroupName): static
    {
        $this->bookingBGroupName = $bookingBGroupName;

        return $this;
    }

    public function getBookingBCourseName(): ?string
    {
        return $this->bookingBCourseName;
    }

    public function setBookingBCourseName(string $bookingBCourseName): static
    {
        $this->bookingBCourseName = $bookingBCourseName;

        return $this;
    }

    public function getBookingBSourcePage(): int
    {
        return $this->bookingBSourcePage;
    }

    public function setBookingBSourcePage(int $bookingBSourcePage): static
    {
        $this->bookingBSourcePage = $bookingBSourcePage;

        return $this;
    }

    public function getBookingAStartTime(): ?\DateTimeImmutable
    {
        return $this->bookingAStartTime;
    }

    public function setBookingAStartTime(?\DateTimeImmutable $bookingAStartTime): static
    {
        $this->bookingAStartTime = $bookingAStartTime;

        return $this;
    }

    public function getBookingAEndTime(): ?\DateTimeImmutable
    {
        return $this->bookingAEndTime;
    }

    public function setBookingAEndTime(?\DateTimeImmutable $bookingAEndTime): static
    {
        $this->bookingAEndTime = $bookingAEndTime;

        return $this;
    }

    public function getBookingBStartTime(): ?\DateTimeImmutable
    {
        return $this->bookingBStartTime;
    }

    public function setBookingBStartTime(?\DateTimeImmutable $bookingBStartTime): static
    {
        $this->bookingBStartTime = $bookingBStartTime;

        return $this;
    }

    public function getBookingBEndTime(): ?\DateTimeImmutable
    {
        return $this->bookingBEndTime;
    }

    public function setBookingBEndTime(?\DateTimeImmutable $bookingBEndTime): static
    {
        $this->bookingBEndTime = $bookingBEndTime;

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
}
