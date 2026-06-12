<?php
declare(strict_types=1);

namespace App\Factories;

use App\Entities\Product;

// ProductFactory's only job is to turn a raw database row (an associative array)
// into a typed Product entity.
//
// Why a separate class for this?
// — The Model shouldn't care about type-casting rules.
// — The Entity shouldn't know anything about how the database stores data.
// — Keeping it here means if the DB column names ever change, you fix it in one place.
class ProductFactory
{
    // Takes one row returned by mysqli (e.g. ['id' => '3', 'name' => 'Flour', ...])
    // and returns a clean, typed Product entity.
    //
    // Notice the explicit casts: mysqli returns everything as strings, so we cast
    // to the right type here so the rest of the app never has to think about it.
    public static function fromRow(array $row): Product
    {
        $product = new Product();

        $product->id          = (int)    $row['id'];
        $product->name        = (string) $row['name'];
        $product->price       = (float)  $row['price'];
        $product->category_id = (int)    $row['category_id'];
        $product->unit_id     = (int)    $row['unit_id'];
        $product->image_path  = (string) ($row['image_path'] ?? '');

        return $product;
    }
}
