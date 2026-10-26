<?php
declare(strict_types=1);

final class Database
{
    public static function connection(): PDO
    {
        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $name = getenv('DB_NAME') ?: '';
        $user = getenv('DB_USER') ?: '';
        $password = getenv('DB_PASSWORD') ?: '';

        if ($name === '' || $user === '' || $password === '') {
            throw new RuntimeException('Database configuration is incomplete');
        }

        $pdo = new PDO(
            "pgsql:host={$host};port={$port};dbname={$name}",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS messages (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                message VARCHAR(2000) NOT NULL,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )'
        );

        return $pdo;
    }
}
