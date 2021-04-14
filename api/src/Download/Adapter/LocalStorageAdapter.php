<?php

declare(strict_types=1);

namespace App\Download\Adapter;

use App\Download\DownloadAdapterInterface;

class LocalStorageAdapter implements DownloadAdapterInterface
{
    private const PREFIX = 'file://';

    public function supportsUri(string $uri): bool
    {
        return 0 === strpos($uri, self::PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function downloadFiles(iterable $files, string $dest): void
    {
        foreach ($files as $file) {
            $from = substr($file->getUri(), strlen(self::PREFIX));
            $to  = $dest.DIRECTORY_SEPARATOR.$file->getPath();
            copy($from, $to);
        }
    }
}
