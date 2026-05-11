<?php

namespace App\Controller\IArooms;

use App\Entity\IArooms\TimetableUpload;
use App\Form\IArooms\AvailabilitySearchType;
use App\Repository\IArooms\TimetableUploadRepository;
use App\Service\IArooms\RoomAvailabilityService;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvailabilityController extends AbstractController
{
    #[Route('/availability', name: 'app_availability_index', methods: ['GET'])]
    public function index(TimetableUploadRepository $timetableUploadRepository, Request $request): Response
    {
        $latestUpload = $timetableUploadRepository->findLatest();
        $today = new DateTimeImmutable('today');

        $form = $this->createForm(AvailabilitySearchType::class, [
            'upload' => $latestUpload,
            'date' => $today,
            'startTime' => new DateTimeImmutable('today 09:00'),
            'endTime' => new DateTimeImmutable('today 10:30'),
            'roomFilter' => '',
            'buildingFilter' => '',
        ]);

        $requestedUploadId = $request->query->get('upload');
        $selectedUpload = is_numeric($requestedUploadId) ? (int) $requestedUploadId : ($latestUpload?->getId());

        return $this->render('IArooms/availability/index.html.twig', [
            'form' => $form->createView(),
            'latestUpload' => $latestUpload,
            'selectedUploadId' => $selectedUpload,
            'selectedDate' => $today->format('Y-m-d'),
            'presetSlots' => RoomAvailabilityService::STANDARD_SLOTS,
        ]);
    }

    #[Route('/availability/results', name: 'app_availability_results', methods: ['GET'])]
    public function results(Request $request, TimetableUploadRepository $timetableUploadRepository, RoomAvailabilityService $availabilityService): Response
    {
        $latestUpload = $timetableUploadRepository->findLatest();
        $form = $this->createForm(AvailabilitySearchType::class, [
            'upload' => $latestUpload,
            'date' => new DateTimeImmutable('today'),
            'startTime' => new DateTimeImmutable('today 09:00'),
            'endTime' => new DateTimeImmutable('today 10:30'),
            'roomFilter' => '',
            'buildingFilter' => '',
        ]);
        $form->handleRequest($request);

        $upload = $this->resolveUpload($request, $timetableUploadRepository);

        $dateValue = trim((string) $request->query->get('date', ''));
        $startTimeValue = trim((string) $request->query->get('startTime', ''));
        $endTimeValue = trim((string) $request->query->get('endTime', ''));
        $roomFilter = trim((string) $request->query->get('roomFilter', '')) ?: null;
        $buildingFilter = trim((string) $request->query->get('buildingFilter', '')) ?: null;

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateValue) ?: new DateTimeImmutable('today');
        $startTime = DateTimeImmutable::createFromFormat('!H:i', $startTimeValue) ?: new DateTimeImmutable('today 09:00');
        $endTime = DateTimeImmutable::createFromFormat('!H:i', $endTimeValue) ?: new DateTimeImmutable('today 10:30');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData() ?? [];
            $upload = $data['upload'] ?? $upload;
            $date = $data['date'] ?? $date;
            $startTime = $data['startTime'] ?? $startTime;
            $endTime = $data['endTime'] ?? $endTime;
            $roomFilter = trim((string) ($data['roomFilter'] ?? '')) ?: null;
            $buildingFilter = trim((string) ($data['buildingFilter'] ?? '')) ?: null;
        }

        if (!$upload instanceof TimetableUpload) {
            $this->addFlash('warning', 'Scrape the Esprit timetable first to search availability.');

            return $this->render('IArooms/availability/results.html.twig', [
                'form' => $form->createView(),
                'upload' => null,
                'availability' => null,
                'presetSlots' => RoomAvailabilityService::STANDARD_SLOTS,
            ]);
        }

        $availability = $availabilityService->getAvailabilityForSlot(
            $date,
            $startTime->format('H:i'),
            $endTime->format('H:i'),
            $upload,
            $roomFilter,
            $buildingFilter,
        );

        return $this->render('IArooms/availability/results.html.twig', [
            'form' => $form->createView(),
            'upload' => $upload,
            'availability' => $availability,
            'presetSlots' => RoomAvailabilityService::STANDARD_SLOTS,
            'selectedDate' => $date->format('Y-m-d'),
            'selectedStartTime' => $startTime->format('H:i'),
            'selectedEndTime' => $endTime->format('H:i'),
            'selectedRoomFilter' => $roomFilter,
            'selectedBuildingFilter' => $buildingFilter,
        ]);
    }

    #[Route('/availability/day/{date}', name: 'app_availability_day', methods: ['GET'])]
    public function day(string $date, Request $request, TimetableUploadRepository $timetableUploadRepository, RoomAvailabilityService $availabilityService): Response
    {
        $upload = $this->resolveUpload($request, $timetableUploadRepository);
        if (!$upload instanceof TimetableUpload) {
            $this->addFlash('warning', 'Scrape the Esprit timetable first to use the day view.');

            return $this->redirectToRoute('app_timetable_scrape');
        }

        $day = new DateTimeImmutable($date);
        $roomFilter = trim((string) $request->query->get('roomFilter', '')) ?: null;
        $buildingFilter = trim((string) $request->query->get('buildingFilter', '')) ?: null;

        return $this->render('IArooms/availability/day.html.twig', [
            'upload' => $upload,
            'day' => $day,
            'slots' => $availabilityService->getAvailabilityForDay($day, $upload, $roomFilter, $buildingFilter),
        ]);
    }

    private function resolveUpload(Request $request, TimetableUploadRepository $timetableUploadRepository): ?TimetableUpload
    {
        $uploadId = $request->query->get('upload');
        if (is_numeric($uploadId)) {
            $upload = $timetableUploadRepository->find((int) $uploadId);
            if ($upload instanceof TimetableUpload) {
                return $upload;
            }
        }

        return $timetableUploadRepository->findLatest();
    }
}
