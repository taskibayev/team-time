<?php

namespace App\Catalog;

final class DepartmentCatalog extends ArrayCatalog
{
    public function __construct()
    {
        parent::__construct(['product' => 'Product', 'engineering' => 'Engineering', 'operations' => 'Operations']);
    }
}
