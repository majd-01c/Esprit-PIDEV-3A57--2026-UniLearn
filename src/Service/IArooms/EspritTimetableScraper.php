<?php

namespace App\Service\IArooms;

use DateTimeImmutable;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EspritTimetableScraper
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $backendBaseUrl,
    )
    {
    }

    public function scrape(?string $studentId, ?string $password, ?string $captcha, float $timeoutSeconds = 20.0): array
    {
        try {
            $scrapeResponse = $this->httpClient->request('POST', rtrim($this->backendBaseUrl, '/') . '/scrape/esprit', [
                'json' => [
                    'student_id' => $studentId,
                    'password' => $password,
                    'captcha' => $captcha,
                    'timeout_seconds' => $timeoutSeconds,
                ],
                'timeout' => $timeoutSeconds + 10,
            ]);

            $scrapePayload = $scrapeResponse->toArray(false);
            if ($scrapeResponse->getStatusCode() >= 400) {
                $detail = is_array($scrapePayload) ? ($scrapePayload['detail'] ?? 'Unknown backend error') : 'Unknown backend error';

                throw new RuntimeException('Esprit scraping backend returned an error: ' . (string) $detail);
            }
        } catch (\Throwable $exception) {
            throw new RuntimeException('Failed to trigger the Esprit scraping backend: ' . $exception->getMessage(), 0, $exception);
        }

        try {
            $bookings = $this->httpClient->request('GET', rtrim($this->backendBaseUrl, '/') . '/bookings', [
                'timeout' => $timeoutSeconds + 10,
            ])->toArray()['bookings'] ?? [];
        } catch (\Throwable $exception) {
            throw new RuntimeException('Failed to retrieve scraped bookings: ' . $exception->getMessage(), 0, $exception);
        }

        $mappedBookings = [];
        $rooms = [];
        $minDate = null;
        $maxDate = null;

        foreach ($bookings as $booking) {
            $bookingDate = new DateTimeImmutable($booking['date']);
            $startTime = new DateTimeImmutable($booking['date'] . ' ' . $booking['start_time']);
            $endTime = new DateTimeImmutable($booking['date'] . ' ' . $booking['end_time']);

            $minDate = $minDate === null || $bookingDate < $minDate ? $bookingDate : $minDate;
            $maxDate = $maxDate === null || $bookingDate > $maxDate ? $bookingDate : $maxDate;

            $roomName = (string) ($booking['room_name'] ?? '');
            if ($roomName !== '') {
                $rooms[$roomName] = $roomName;
            }

            $mappedBookings[] = [
                'groupName' => (string) ($booking['group_name'] ?? 'Unknown group'),
                'courseName' => (string) ($booking['course_name'] ?? 'Unknown course'),
                'roomName' => $roomName,
                'bookingDate' => $bookingDate,
                'dayName' => (string) ($booking['day_name'] ?? ''),
                'startTime' => $startTime,
                'endTime' => $endTime,
                'sourcePage' => (int) ($booking['source_page'] ?? 1),
            ];
        }

        if ($minDate === null) {
            $warningMessage = implode(' ', array_values(array_map('strval', $scrapePayload['warnings'] ?? [])));

            throw new RuntimeException(trim('No bookings returned from Esprit scrape. ' . $warningMessage));
        }

        return [
            'weekStart' => $minDate,
            'weekEnd' => $maxDate,
            'totalPages' => (int) ($scrapePayload['total_pages_read'] ?? 0),
            'totalBookings' => count($mappedBookings),
            'totalRooms' => count($rooms),
            'ignoredOnlineSessions' => (int) ($scrapePayload['ignored_online_sessions'] ?? 0),
            'bookings' => $mappedBookings,
            'rooms' => array_values($rooms),
            'warnings' => array_values(array_map('strval', $scrapePayload['warnings'] ?? [])),
            'sourceUrl' => (string) ($scrapePayload['source_url'] ?? ''),
        ];
    }
}
