<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Storage\SupabaseStorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StorageController extends AbstractController
{
    #[Route('/storage/supabase', name: 'app_supabase_storage_proxy', methods: ['GET'])]
    public function proxy(Request $request, SupabaseStorageService $supabaseStorageService): Response
    {
        $reference = $request->query->get('reference', '');
        if ($reference === '') {
            return new Response('Missing reference parameter.', Response::HTTP_BAD_REQUEST);
        }
        try {
            $file = $supabaseStorageService->downloadStoredFile($reference);

            return new Response($file['content'], Response::HTTP_OK, [
                'Content-Type' => $file['mimeType'],
                'Content-Disposition' => 'inline; filename="' . addslashes($file['fileName']) . '"',
                'Cache-Control' => 'private, max-age=300',
            ]);
        } catch (\RuntimeException $e) {
            return new Response('File not found.', Response::HTTP_NOT_FOUND);
        }
    }
}