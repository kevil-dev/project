<?php
declare(strict_types=1);

namespace App\Entities;

// A Product entity represents one row from the products table as a real PHP object.
// Using typed properties instead of a plain array means you get autocomplete,
// and there's no risk of a typo like $row['nmae'] silently returning null.
class Product
{
    public int    $id          = 0;
    public string $name        = '';
    public float  $price       = 0.0;
    public int    $category_id = 0;
    public int    $unit_id     = 0;
    public string $image_path  = '';
}
