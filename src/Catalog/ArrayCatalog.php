<?php

namespace App\Catalog;

abstract class ArrayCatalog implements CatalogInterface
{
    /** @param array<string, string> $items */
    public function __construct(private readonly array $items)
    {
    }

    public function all(): array
    {
        return $this->items;
    }

    public function has(string $id): bool
    {
        return isset($this->items[$id]);
    }

    public function label(string $id): string
    {
        return $this->items[$id] ?? $id;
    }
}
