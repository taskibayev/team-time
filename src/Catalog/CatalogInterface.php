<?php

namespace App\Catalog;

interface CatalogInterface
{
    /** @return array<string, string> */
    public function all(): array;

    public function has(string $id): bool;

    public function label(string $id): string;
}
