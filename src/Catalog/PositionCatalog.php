<?php

namespace App\Catalog;

final class PositionCatalog extends ArrayCatalog
{
    public function __construct()
    {
        parent::__construct(['engineer' => 'Software Engineer', 'designer' => 'Product Designer', 'manager' => 'Manager']);
    }
}
