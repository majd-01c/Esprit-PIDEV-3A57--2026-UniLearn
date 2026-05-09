<?php

namespace App\Controller\IArooms;

use App\Entity\IArooms\Room;
use App\Entity\IArooms\RoomBooking;
use App\Entity\IArooms\TimetableUpload;
use App\Form\IArooms\EspritScrapeType;
use App\Repository\IArooms\RoomRepository;
use App\Repository\IArooms\TimetableUploadRepository;
use App\Service\IArooms\ConflictDetectorService;
use App\Service\IArooms\EspritTimetableScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TimetableController extends AbstractController
{
    #[Route('/timetable/scrape', name: 'app_timetable_scrape', methods: ['GET', 'POST'])]
    public function scrape(
        Request $request,
        EntityManagerInterface $entityManager,
        RoomRepository $roomRepository,
        EspritTimetableScraper $scraper,
        ConflictDetectorService $conflictDetector,
    ): Response {
        $form = $this->createForm(EspritScrapeType::class, [
            'studentId' => '',
            'password' => '',
            'timeoutSeconds' => 20,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData() ?? [];

            try {
                $parsed = $scraper->scrape(
                    studentId: trim((string) ($data['studentId'] ?? '')) ?: null,
                    password: trim((string) ($data['password'] ?? '')) ?: null,
                    captcha: null,
                    timeoutSeconds: (float) ($data['timeoutSeconds'] ?? 20),
                );

                $upload = new TimetableUpload();
                $this->persistParsedUpload(
                    upload: $upload,
                    parsed: $parsed,
                    entityManager: $entityManager,
                    roomRepository: $roomRepository,
                    conflictDetector: $conflictDetector,
                    originalFilename: 'ESPRIT Emplois direct scrape',
                    storedFilename: 'esprit-scrape-' . bin2hex(random_bytes(12)) . '.json',
                );

                foreach (($parsed['warnings'] ?? []) as $warning) {
                    $this->addFlash('warning', $warning);
                }

                $this->addFlash('success', 'Esprit timetable scraped and stored successfully.');

                return $this->redirectToRoute('app_timetable_show', ['id' => $upload->getId()]);
            } catch (\Throwable $exception) {
                $this->addFlash('error', sprintf('Scrape failed: %s', $exception->getMessage()));
            }
        }

        return $this->render('IArooms/timetable/scrape.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/timetable/{id}', name: 'app_timetable_show', methods: ['GET'])]
    public function show(TimetableUpload $upload, TimetableUploadRepository $timetableUploadRepository): Response
    {
        $latestUpload = $timetableUploadRepository->findLatest();

        return $this->render('IArooms/timetable/show.html.twig', [
            'upload' => $upload,
            'latestUpload' => $latestUpload,
        ]);
    }

    #[Route('/timetable/{id}/delete', name: 'app_timetable_delete', methods: ['POST'])]
    public function delete(TimetableUpload $upload, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_timetable', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Delete request expired. Try again.');

            return $this->redirectToRoute('app_availability_index');
        }

        $label = $upload->getOriginalFilename() ?? 'Esprit scrape';
        $entityManager->remove($upload);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Deleted %s and its bookings.', $label));

        return $this->redirectToRoute('app_availability_index');
    }

    private function persistParsedUpload(
        TimetableUpload $upload,
        array $parsed,
        EntityManagerInterface $entityManager,
        RoomRepository $roomRepository,
        ConflictDetectorService $conflictDetector,
        string $originalFilename,
        string $storedFilename,
    ): void {
        $upload->setOriginalFilename($originalFilename)
            ->setStoredFilename($storedFilename)
            ->setWeekStart($parsed['weekStart'])
            ->setWeekEnd($parsed['weekEnd'])
            ->setTotalPages((int) $parsed['totalPages'])
            ->setTotalBookings((int) $parsed['totalBookings'])
            ->setTotalRooms((int) $parsed['totalRooms'])
            ->setIgnoredOnlineSessions((int) $parsed['ignoredOnlineSessions'])
            ->setUsesMasterRoomList(false);

        $entityManager->persist($upload);

        $roomNames = array_values(array_unique(array_filter(array_map(
            static fn (array $row): string => trim((string) ($row['roomName'] ?? '')),
            $parsed['bookings']
        ))));
        $roomsByName = [];

        if ($roomNames !== []) {
            foreach ($roomRepository->findBy(['name' => $roomNames]) as $existingRoom) {
                if ($existingRoom instanceof Room && $existingRoom->getName() !== null) {
                    $roomsByName[strtolower($existingRoom->getName())] = $existingRoom;
                }
            }
        }

        foreach ($parsed['bookings'] as $row) {
            $roomName = trim((string) ($row['roomName'] ?? ''));
            if ($roomName === '') {
                continue;
            }

            $roomKey = strtolower($roomName);
            $room = $roomsByName[$roomKey] ?? null;
            if (!$room instanceof Room) {
                $room = (new Room())->setName($roomName);
                $entityManager->persist($room);
                $roomsByName[$roomKey] = $room;
            }

            $booking = (new RoomBooking())
                ->setTimetableUpload($upload)
                ->setRoom($room)
                ->setGroupName($row['groupName'])
                ->setCourseName($row['courseName'])
                ->setBookingDate($row['bookingDate'])
                ->setDayName($row['dayName'])
                ->setStartTime($row['startTime'])
                ->setEndTime($row['endTime'])
                ->setSourcePage($row['sourcePage']);

            $entityManager->persist($booking);
        }

        $entityManager->flush();

        foreach ($conflictDetector->detectConflicts($upload) as $conflict) {
            $entityManager->persist($conflict);
        }

        $entityManager->flush();
    }
}
