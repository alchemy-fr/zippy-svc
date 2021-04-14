<?php

declare(strict_types=1);

namespace App\Controller;

use App\Archive\ArchiveManager;
use App\Entity\Archive;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
{
    /**
     * @Route("/archives/{id}/download", name="download_archive")
     */
    public function __invoke(string $id, Request $request, ArchiveManager $archiveManager): Response
    {
        $archive = $archiveManager->getArchive($id);
        if (!$archive instanceof Archive) {
            throw new NotFoundHttpException(sprintf('Archive %s not found', $id));
        }

        if (!$archive->isReady()) {
            return $this->render('pending.html.twig');
        }

        $path = $archiveManager->getArchivePath($archive);

        return new StreamedResponse(function () use($path): void {
            readfile($path);
        }, 200, [
            'Content-Type' => 'application/zip',
        ]);
    }
}
