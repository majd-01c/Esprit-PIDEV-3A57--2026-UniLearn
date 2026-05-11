<?php

namespace App\Controller\IArooms;

use App\Entity\IArooms\TimetableUpload;
use App\Repository\IArooms\RoomConflictRepository;
use App\Repository\IArooms\TimetableUploadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConflictController extends AbstractController
{
    #[Route('/conflicts', name: 'app_conflict_index', methods: ['GET'])]
    public function index(Request $request, TimetableUploadRepository $timetableUploadRepository, RoomConflictRepository $roomConflictRepository): Response
    {
        $uploadId = $request->query->get('upload');
        $upload = is_numeric($uploadId) ? $timetableUploadRepository->find((int) $uploadId) : $timetableUploadRepository->findLatest();

        return $this->render('IArooms/conflict/index.html.twig', [
            'upload' => $upload,
            'conflicts' => $roomConflictRepository->findOrdered($upload instanceof TimetableUpload ? $upload : null),
        ]);
    }
}
