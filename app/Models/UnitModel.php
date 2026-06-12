<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Entities\Unit;

// UnitModel handles all database queries for the units table.
class UnitModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // Returns every unit as an array of Unit entities.
    // Returns: Unit[]
    public function getAll(): array
    {
        $rows = $this->db->query('SELECT id, name FROM units');

        $units = [];
        foreach ($rows as $row) {
            $unit       = new Unit();
            $unit->id   = (int)    $row['id'];
            $unit->name = (string) $row['name'];
            $units[]    = $unit;
        }

        return $units;
    }
}
