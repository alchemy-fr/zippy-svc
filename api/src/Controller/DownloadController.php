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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        if ($archive->hasError()) {
            $response = new Response(null, 500);

            return $this->render('error.html.twig', [], $response);
        } elseif (!$archive->isReady()) {
            return $this->render('pending.html.twig');
        }

        if (!$request->query->has('c')) {
            $url = $this->generateUrl('download_archive', [
                'id' => $id,
                'c' => '1',
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return $this->render('download.html.twig', [
                'download_url' => $url,
            ]);
        }

        $path = $archiveManager->getArchivePath($archive);

        return new StreamedResponse(function () use ($path): void {
            $resource = fopen($path, 'rb');
            while (!feof($resource)) {
                echo fread($resource, 8192);
            }
            fclose($resource);
        }, 200, [
            'Content-Description' => 'File Transfer',
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="download.zip"',
            'Content-Length' => (string) filesize($path),
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
