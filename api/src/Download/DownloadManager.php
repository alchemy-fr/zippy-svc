<?php

declare(strict_types=1);

namespace App\Download;

use App\Download\Adapter\HTTPDownloadAdapter;
use App\Download\Adapter\LocalStorageAdapter;
use App\Entity\File;

class DownloadManager
{
    /**
     * @var DownloadAdapterInterface[]
     */
    private array $adapters = [];

    public function __construct()
    {
        // TODO make CompilerPass
        $this->adapters[] = new HTTPDownloadAdapter();
        $this->adapters[] = new LocalStorageAdapter();
    }

    /**
     * @param File[]|iterable $files
     */
    public function downloadFiles(iterable $files, string $dest): void
    {
        $groups = [];
        foreach ($files as $file) {
            foreach ($this->adapters as $i => $adapter) {
                if ($adapter->supportsUri($file->getUri())) {
                    $groups[$i][] = $file;

                    $dirname = $dest.DIRECTORY_SEPARATOR.dirname($file->getPath());
                    if (!is_dir($dirname)) {
                        mkdir($dirname, 0755, true);
                    }

                    continue 2;
                }
            }

            throw new \Exception(sprintf('Unsupported URI "%s"', $file->getUri()));
        }

        foreach ($groups as $i => $group) {
            $this->adapters[$i]->downloadFiles($group, $dest);
        }
    }
}
