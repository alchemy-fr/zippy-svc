<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

class DeleteArchive
{
    public function __construct(private string $id)
    {
    }
    
    public function getId(): string
    {
        return $this->id;
    }
}
