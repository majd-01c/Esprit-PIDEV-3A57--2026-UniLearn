<?php

namespace App\Entity;

use App\Repository\JobOfferMeetingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobOfferMeetingRepository::class)]
#[ORM\Table(name: 'job_offer_meeting')]
#[ORM\UniqueConstraint(name: 'uniq_job_offer_meeting_application', columns: ['application_id'])]
#[ORM\Index(name: 'idx_job_offer_meeting_offer', columns: ['offer_id'])]
#[ORM\Index(name: 'idx_job_offer_meeting_student', columns: ['student_id'])]
#[ORM\Index(name: 'idx_job_offer_meeting_partner', columns: ['partner_id'])]
#[ORM\Index(name: 'idx_job_offer_meeting_status', columns: ['status'])]
#[ORM\Index(name: 'idx_job_offer_meeting_scheduled', columns: ['scheduled_at'])]
#[ORM\Index(name: 'idx_job_offer_meeting_window', columns: ['scheduled_at', 'scheduled_end_at'])]
#[ORM\HasLifecycleCallbacks]
class JobOfferMeeting
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_LIVE = 'live';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CANCELLED = 'cancelled';
    public const DEFAULT_DURATION_MINUTES = 30;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: JobApplication::class, inversedBy: 'meeting')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?JobApplication $application = null;

    #[ORM\ManyToOne(targetEntity: JobOffer::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?JobOffer $offer = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $student = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $partner = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $roomCode = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::STATUS_SCHEDULED,
        self::STATUS_LIVE,
        self::STATUS_ENDED,
        self::STATUS_CANCELLED,
    ])]
    private string $status = self::STATUS_SCHEDULED;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $scheduledEndAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->roomCode = $this->generateRoomCode();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt ??= $now;
        $this->roomCode ??= $this->generateRoomCode();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): ?JobApplication
    {
        return $this->application;
    }

    public function setApplication(?JobApplication $application): static
    {
        $this->application = $application;

        if ($application !== null && $application->getMeeting() !== $this) {
            $application->setMeeting($this);
        }

        return $this;
    }

    public function getOffer(): ?JobOffer
    {
        return $this->offer;
    }

    public function setOffer(?JobOffer $offer): static
    {
        $this->offer = $offer;
        return $this;
    }

    public function getStudent(): ?User
    {
        return $this->student;
    }

    public function setStudent(?User $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getPartner(): ?User
    {
        return $this->partner;
    }

    public function setPartner(?User $partner): static
    {
        $this->partner = $partner;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = trim($title);
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $description = $description === null ? null : trim($description);
        $this->description = $description === '' ? null : $description;

        return $this;
    }

    public function getRoomCode(): ?string
    {
        return $this->roomCode;
    }

    public function setRoomCode(string $roomCode): static
    {
        $this->roomCode = $roomCode;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeInterface $scheduledAt): static
    {
        $this->scheduledAt = $this->toImmutable($scheduledAt);
        return $this;
    }

    public function getScheduledEndAt(): ?\DateTimeImmutable
    {
        return $this->scheduledEndAt;
    }

    public function setScheduledEndAt(\DateTimeInterface $scheduledEndAt): static
    {
        $this->scheduledEndAt = $this->toImmutable($scheduledEndAt);
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt === null ? null : $this->toImmutable($startedAt);
        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): static
    {
        $this->endedAt = $endedAt === null ? null : $this->toImmutable($endedAt);
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canJoinNow(): bool
    {
        return $this->canJoinAt(new \DateTimeImmutable());
    }

    public function canJoinAt(\DateTimeInterface $now): bool
    {
        if ($this->scheduledAt === null || $this->scheduledEndAt === null || $this->isEnded() || $this->isCancelled()) {
            return false;
        }

        $now = $this->toImmutable($now);

        return $now >= $this->scheduledAt && $now <= $this->scheduledEndAt;
    }

    public function markLive(): static
    {
        if (!$this->isLive()) {
            $this->status = self::STATUS_LIVE;
            $this->startedAt = new \DateTimeImmutable();
        }

        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function reschedule(\DateTimeInterface $scheduledAt, ?\DateTimeInterface $scheduledEndAt = null): static
    {
        $start = $this->toImmutable($scheduledAt);
        $end = $scheduledEndAt === null
            ? $start->modify('+' . self::DEFAULT_DURATION_MINUTES . ' minutes')
            : $this->toImmutable($scheduledEndAt);

        if ($end <= $start) {
            throw new \InvalidArgumentException('Meeting end time must be after the start time.');
        }

        $this->scheduledAt = $start;
        $this->scheduledEndAt = $end;
        $this->status = self::STATUS_SCHEDULED;
        $this->startedAt = null;
        $this->endedAt = null;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function end(): static
    {
        $this->status = self::STATUS_ENDED;
        $this->endedAt = new \DateTimeImmutable();
        $this->updatedAt = $this->endedAt;

        return $this;
    }

    public function cancel(): static
    {
        $this->status = self::STATUS_CANCELLED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    private function toImmutable(\DateTimeInterface $dateTime): \DateTimeImmutable
    {
        return $dateTime instanceof \DateTimeImmutable
            ? $dateTime
            : \DateTimeImmutable::createFromInterface($dateTime);
    }

    private function generateRoomCode(): string
    {
        return 'unilearn-job-' . bin2hex(random_bytes(8));
    }
}
