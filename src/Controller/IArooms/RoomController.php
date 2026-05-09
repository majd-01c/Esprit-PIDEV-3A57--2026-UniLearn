<?php

namespace App\Controller\IArooms;

use App\Entity\IArooms\Room;
use App\Entity\IArooms\TimetableUpload;
use App\Repository\IArooms\RoomBookingRepository;
use App\Repository\IArooms\RoomRepository;
use App\Repository\IArooms\TimetableUploadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoomController extends AbstractController
{
    #[Route('/rooms', name: 'app_room_index', methods: ['GET'])]
    public function index(Request $request, RoomRepository $roomRepository, TimetableUploadRepository $timetableUploadRepository): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $latestUpload = $timetableUploadRepository->findLatest();
        $rooms = $latestUpload instanceof TimetableUpload ? $roomRepository->findObservedForUpload($latestUpload) : [];

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $rooms = array_values(array_filter(
                $rooms,
                static fn (Room $room): bool => str_contains(mb_strtolower($room->getName() ?? ''), $needle)
                    || str_contains(mb_strtolower($room->getBuilding() ?? ''), $needle)
                    || str_contains(mb_strtolower($room->getType() ?? ''), $needle)
            ));
        }

        return $this->render('IArooms/room/index.html.twig', [
            'rooms' => $rooms,
            'query' => $query,
            'latestUpload' => $latestUpload,
        ]);
    }

    #[Route('/rooms/{id}', name: 'app_room_show', methods: ['GET'])]
    public function show(Room $room, Request $request, RoomBookingRepository $roomBookingRepository, TimetableUploadRepository $timetableUploadRepository): Response
    {
        $uploadId = $request->query->get('upload');
        $upload = is_numeric($uploadId) ? $timetableUploadRepository->find((int) $uploadId) : $timetableUploadRepository->findLatest();

        $bookings = $upload instanceof TimetableUpload
            ? array_values(array_filter($roomBookingRepository->findByUploadOrdered($upload), static fn ($booking) => $booking->getRoom()?->getId() === $room->getId()))
            : [];

        usort($bookings, static function ($left, $right): int {
            return [$left->getBookingDate()?->format('Y-m-d'), $left->getStartTime()?->format('H:i')] <=> [$right->getBookingDate()?->format('Y-m-d'), $right->getStartTime()?->format('H:i')];
        });

        return $this->render('IArooms/room/show.html.twig', [
            'room' => $room,
            'bookings' => $bookings,
            'selectedUpload' => $upload,
        ]);
    }
}
