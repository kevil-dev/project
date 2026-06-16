<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class UserModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $rows = $this->db->query(
            'SELECT id, email, password_hash, is_active FROM users WHERE email = ?',
            [$email]
        );

        return empty($rows) ? null : $rows[0];
    }
}