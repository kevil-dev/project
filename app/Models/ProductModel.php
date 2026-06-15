<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ProductModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAll(int $page): array
    {
        $offset = ($page - 1) * 20;

        $sql = '
            SELECT
                p.id,
                p.name,
                p.price,
                p.category_id,
                p.unit_id,
                p.image_path,
                (SELECT COALESCE(SUM(quantity), 0)
                 FROM inventory_transactions
                 WHERE product_id = p.id) AS stock
            FROM products p
            WHERE p.is_active = 1
            ORDER BY p.id ASC
            LIMIT 20 OFFSET ?
        ';

        return $this->db->query($sql, [$offset]);
    }

    public function countAll(): int
    {
        $rows = $this->db->query(
            'SELECT COUNT(*) AS total FROM products WHERE is_active = 1'
        );

        return (int) $rows[0]['total'];
    }
    
}
