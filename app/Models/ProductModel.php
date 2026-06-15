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

    public function getAll(int $page, string $search, int $categoryId, int $unitId): array
    {
        $offset = ($page - 1) * 20;
        $params = [];
        $where  = ['p.is_active = 1'];

        if ($search !== '') {
            $where[]  = 'p.name LIKE ?';
            $params[] = $search . '%';
        }

        if ($categoryId !== 0) {
            $where[]  = 'p.category_id = ?';
            $params[] = $categoryId;
        }

        if ($unitId !== 0) {
            $where[]  = 'p.unit_id = ?';
            $params[] = $unitId;
        }

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
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY p.id ASC
        LIMIT 20 OFFSET ?
    ';

        $params[] = $offset;

        $rows = $this->db->query($sql, $params);

        foreach ($rows as &$row) {
            $row['id']          = (int)   $row['id'];
            $row['price']       = (float) $row['price'];
            $row['category_id'] = (int)   $row['category_id'];
            $row['unit_id']     = (int)   $row['unit_id'];
            $row['stock']       = (int)   $row['stock'];
        }
        return $rows;
    }

    public function countAll(string $search, int $categoryId, int $unitId): int
    {
        $params = [];
        $where  = ['is_active = 1'];

        if ($search !== '') {
            $where[]  = 'name LIKE ?';
            $params[] = $search . '%';
        }

        if ($categoryId !== 0) {
            $where[]  = 'category_id = ?';
            $params[] = $categoryId;
        }

        if ($unitId !== 0) {
            $where[]  = 'unit_id = ?';
            $params[] = $unitId;
        }

        $rows = $this->db->query(
            'SELECT COUNT(*) AS total FROM products WHERE ' . implode(' AND ', $where),
            $params
        );

        return (int) $rows[0]['total'];
    }

    public function getById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT
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
        WHERE p.id = ? AND p.is_active = 1',
            [$id]
        );

        if (empty($rows)) {
            return null;
        }

        $product                = $rows[0];
        $product['id']          = (int)   $product['id'];
        $product['price']       = (float) $product['price'];
        $product['category_id'] = (int)   $product['category_id'];
        $product['unit_id']     = (int)   $product['unit_id'];
        $product['stock']       = (int)   $product['stock'];

        return $product;
    }

    public function softDelete(int $id): bool
    {
        $affected = $this->db->execute(
            'UPDATE products SET is_active = 0 WHERE id = ? AND is_active = 1',
            [$id]
        );

        return $affected > 0;
    }

    public function create(string $name, float $price, int $categoryId, int $unitId, string $imagePath, int $initialStock): int
    {
        $this->db->beginTransaction();

        try {
            $this->db->execute(
                'INSERT INTO products (name, price, category_id, unit_id, image_path)
             VALUES (?, ?, ?, ?, ?)',
                [$name, $price, $categoryId, $unitId, $imagePath]
            );

            $productId = $this->db->lastInsertId();

            $this->db->execute(
                'INSERT INTO inventory_transactions (product_id, quantity, transaction_type, reason)
             VALUES (?, ?, ?, ?)',
                [$productId, $initialStock, 'restock', 'Initial stock']
            );

            $this->db->commit();

            return $productId;
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    public function update(int $id, string $name, float $price, int $categoryId, int $unitId, ?string $imagePath): bool
    {
        if ($imagePath !== null) {
            $affected = $this->db->execute(
                'UPDATE products
             SET name = ?, price = ?, category_id = ?, unit_id = ?, image_path = ?
             WHERE id = ? AND is_active = 1',
                [$name, $price, $categoryId, $unitId, $imagePath, $id]
            );
        } else {
            $affected = $this->db->execute(
                'UPDATE products
             SET name = ?, price = ?, category_id = ?, unit_id = ?
             WHERE id = ? AND is_active = 1',
                [$name, $price, $categoryId, $unitId, $id]
            );
        }

        return $affected > 0;
    }
    public function getImagePath(int $id): ?string
    {
        $rows = $this->db->query(
            'SELECT image_path FROM products WHERE id = ? AND is_active = 1',
            [$id]
        );

        return empty($rows) ? null : $rows[0]['image_path'];
    }
    public function getStock(int $id): int
{
    $rows = $this->db->query(
        'SELECT COALESCE(SUM(quantity), 0) AS stock
         FROM inventory_transactions
         WHERE product_id = ?',
        [$id]
    );

    return (int) $rows[0]['stock'];
}

public function addTransaction(int $productId, int $quantity, string $type, string $reason): void
{
    $this->db->execute(
        'INSERT INTO inventory_transactions (product_id, quantity, transaction_type, reason)
         VALUES (?, ?, ?, ?)',
        [$productId, $quantity, $type, $reason]
    );
}
}
