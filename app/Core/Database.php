<?php
declare(strict_types=1);

namespace App\Core;

use mysqli;
use mysqli_result;

class Database
{
    private mysqli $connection;

    public function __construct(string $host, string $username, string $password, string $database)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->connection = new mysqli($host, $username, $password, $database);
        $this->connection->set_charset('utf8mb4');
    }

    public function query(string $sql, array $params = []): array
    {
        $result = $this->connection->execute_query($sql, $params);

        if ($result instanceof mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }

    public function execute(string $sql, array $params = []): int
    {
        $this->connection->execute_query($sql, $params);

        return $this->connection->affected_rows;
    }

    public function lastInsertId(): int
    {
        return (int) $this->connection->insert_id;
    }
}
