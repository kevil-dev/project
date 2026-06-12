<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Factories\ProductFactory;

// ProductModel is the ONLY place in the whole app that runs SQL for the products table.
// Controllers ask "give me the products" — they don't know or care how SQL works.
// That separation is the core idea of MVC.
class ProductModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // Searches products with optional filters and returns up to 50 results.
    // Any filter whose value is empty / zero is simply skipped — only provided
    // filters are added to the WHERE clause.
    // Returns: Product[]
    public function search(string $name, int $categoryId, int $unitId): array
    {
        // Start with the base SELECT — we'll add WHERE conditions below only as needed
        $sql    = 'SELECT id, name, price, category_id, unit_id, image_path FROM products';
        $params = [];   // values bound to the ? placeholders in the same order
        $where  = [];   // individual WHERE conditions, joined with AND later

        // Add a name filter only when the user typed something in the search box.
        // "name%" matches "Neem Balm" but NOT "Amla Neem" — skipping the left-side
        // wildcard lets MySQL use an index on the name column, which is much faster.
        if ($name !== '') {
            $where[]  = 'name LIKE ?';
            $params[] = $name . '%';
        }

        // Add a category filter only when the user picked a specific category (0 = "All")
        if ($categoryId !== 0) {
            $where[]  = 'category_id = ?';
            $params[] = $categoryId;
        }

        // Add a unit filter only when the user picked a specific unit (0 = "All")
        if ($unitId !== 0) {
            $where[]  = 'unit_id = ?';
            $params[] = $unitId;
        }

        // If we collected any conditions, join them with AND and append to the query
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Always sort results predictably and cap at 50 rows — never return a million rows
        $sql .= ' ORDER BY id LIMIT 50';

        // Database::query() passes $params to execute_query() as a prepared statement.
        // User input never touches the SQL string directly — no SQL injection possible.
        $rows = $this->db->query($sql, $params);

        $products = [];
        foreach ($rows as $row) {
            $products[] = ProductFactory::fromRow($row);
        }

        return $products;
    }
}
