<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class CategoryModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    public function getAll(): array
    {
        return $this->db->query(
            'SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC'
        );
    }
}