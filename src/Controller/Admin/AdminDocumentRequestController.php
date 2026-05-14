<?php

namespace App\Controller\Admin;

use App\Entity\DocumentRequest;
use App\Repository\DocumentRequestRepository;
use App\Service\Storage\SupabaseStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/document-requests')]
#[IsGranted('ROLE_ADMIN')]
class AdminDocumentRequestController extends AbstractController
{
    #[Route('/', name: 'app_admin_document_requests')]
    public function index(DocumentRequestRepository $documentRequestRepository): Response
    {
        $documentRequests = $documentRequestRepository->createQueryBuilder('d')
            ->orderBy('d.status', 'ASC')
            ->addOrderBy('d.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('gestion_user/admin/document_request/index.html.twig', [
            'documentRequests' => $documentRequests,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_document_request_show', requirements: ['id' => '\d+'])]
    public function show(DocumentRequest $documentRequest): Response
    {
        return $this->render('gestion_user/admin/document_request/show.html.twig', [
            'documentRequest' => $documentRequest,
        ]);
    }

    #[Route('/{id}/update-status', name: 'app_admin_document_request_update_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        DocumentRequest $documentRequest,
        EntityManagerInterface $entityManager
    ): Response {
        $status = $request->request->get('status');

        if ($status) {
            $documentRequest->setStatus($status);
            
            if ($status === 'delivered') {
                $documentRequest->setDeliveredAt(new \DateTime());
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Status updated successfully.');

        return $this->redirectToRoute('app_admin_document_request_show', ['id' => $documentRequest->getId()]);
    }

    #[Route('/{id}/upload', name: 'app_admin_document_request_upload', methods: ['POST'])]
    public function uploadDocument(
        Request $request,
        DocumentRequest $documentRequest,
        EntityManagerInterface $entityManager,
        SupabaseStorageService $supabaseStorageService
    ): Response {
        $uploadedFile = $request->files->get('document_file');

        if (!$uploadedFile) {
            $this->addFlash('error', 'Please select a file.');
            return $this->redirectToRoute('app_admin_document_request_show', ['id' => $documentRequest->getId()]);
        }

        // Validate file type (PDF/DOC/etc.)
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
        ];

        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            $this->addFlash('error', 'File type not allowed. Accepted formats: PDF, DOC, DOCX, JPG, PNG');
            return $this->redirectToRoute('app_admin_document_request_show', ['id' => $documentRequest->getId()]);
        }

        try {
            $original = $uploadedFile->getClientOriginalName() ?: 'file';
            $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original) ?: '_';
            $uuidFolder = bin2hex(random_bytes(16));
            $uuidFile = bin2hex(random_bytes(16));

            $objectPath = sprintf('evaluation-documents/document-request/%s/%s_%s', $uuidFolder, $uuidFile, $sanitized);

            $remotePath = $supabaseStorageService->uploadToObjectPath($uploadedFile, $objectPath);
            // Persist DB contract: supabase:<object_path>
            $documentRequest->setDocumentPath('supabase:' . $remotePath);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Error uploading file.');
            return $this->redirectToRoute('app_admin_document_request_show', ['id' => $documentRequest->getId()]);
        }

        $documentRequest->setStatus('ready');
        $entityManager->flush();

        $this->addFlash('success', 'Document uploaded successfully. Status has been updated to "Ready".');

        return $this->redirectToRoute('app_admin_document_request_show', ['id' => $documentRequest->getId()]);
    }

    #[Route('/{id}/delete', name: 'app_admin_document_request_delete', methods: ['POST'])]
    public function delete(
        DocumentRequest $documentRequest,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($documentRequest);
        $entityManager->flush();

        $this->addFlash('success', 'Request deleted successfully.');

        return $this->redirectToRoute('app_admin_document_requests');
    }
}
