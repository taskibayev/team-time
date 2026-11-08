<?php

namespace App\Catalog;

final class DescriptionCatalog extends ArrayCatalog
{
    public function __construct()
    {
        parent::__construct(['planning' => 'Project planning', 'development' => 'Feature development', 'review' => 'Code review', 'meeting' => 'Team meeting']);
    }
}
