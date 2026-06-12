<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Entities\Category;

// CategoryModel handles all database queries for the categories table.
class CategoryModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // Returns every category as an array of Category entities.
    // Returns: Category[]
    public function getAll(): array
    {
        $rows = $this->db->query('SELECT id, name FROM categories');

        $categories = [];
        foreach ($rows as $row) {
            $category       = new Category();
            $category->id   = (int)    $row['id'];
            $category->name = (string) $row['name'];
            $categories[]   = $category;
        }

        return $categories;
    }
}
