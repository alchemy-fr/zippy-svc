<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

class BuildArchive
{
    public function __construct(private string $id = '')
    {
    }
    
    public function getId(): string
    {
        return $this->id;
    }
}
