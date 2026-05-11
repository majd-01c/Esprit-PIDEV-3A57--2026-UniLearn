<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\JobOfferMeeting;
use PHPUnit\Framework\TestCase;

final class JobOfferMeetingTest extends TestCase
{
    public function testCanJoinAtAllowsOnlyScheduledWindow(): void
    {
        $meeting = new JobOfferMeeting();
        $start = new \DateTimeImmutable('2026-05-24 07:00:00');
        $end = new \DateTimeImmutable('2026-05-24 07:30:00');

        $meeting->reschedule($start, $end);

        self::assertFalse($meeting->canJoinAt($start->modify('-1 second')));
        self::assertTrue($meeting->canJoinAt($start));
        self::assertTrue($meeting->canJoinAt($start->modify('+15 minutes')));
        self::assertTrue($meeting->canJoinAt($end));
        self::assertFalse($meeting->canJoinAt($end->modify('+1 second')));
    }

    public function testCanJoinAtBlocksEndedAndCancelledMeetings(): void
    {
        $meeting = new JobOfferMeeting();
        $start = new \DateTimeImmutable('2026-05-24 07:00:00');
        $end = new \DateTimeImmutable('2026-05-24 07:30:00');

        $meeting->reschedule($start, $end);
        $meeting->end();

        self::assertFalse($meeting->canJoinAt($start->modify('+15 minutes')));

        $meeting->reschedule($start, $end);
        $meeting->cancel();

        self::assertFalse($meeting->canJoinAt($start->modify('+15 minutes')));
    }

    public function testRescheduleRequiresEndAfterStart(): void
    {
        $meeting = new JobOfferMeeting();
        $start = new \DateTimeImmutable('2026-05-24 07:00:00');

        $this->expectException(\InvalidArgumentException::class);

        $meeting->reschedule($start, $start);
    }
}
