<?php

declare(strict_types=1);

namespace App\Archive;

use App\Entity\File;

class IdentifierGenerator
{
    public function generateIdentifier(array $files): string
    {
        foreach ($files as $k => $file) {
            if (!is_array($file) && $file instanceof File) {
                $files[$k] = [
                    'path' => $file->getPath(),
                    'uri' => $file->getUri(),
                ];
            }
        }

        usort($files, function (array $a, array $b): int {
            return strcmp($a['uri'], $b['uri']);
        });

        foreach ($files as &$file) {
            ksort($file);
        }

        return md5(json_encode($files));
    }
}
