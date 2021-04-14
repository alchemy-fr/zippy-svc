<?php

declare(strict_types=1);

namespace App\Download;

use App\Entity\File;

interface DownloadAdapterInterface
{
    /**
     * @param File[]|iterable $files
     */
    public function downloadFiles(iterable $files, string $dest): void;

    public function supportsUri(string $uri): bool;
}
