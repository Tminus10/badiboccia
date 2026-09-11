<?php

final class Admin
{
    public static function all(): array
    {
        return Db::pdo()->query('SELECT id, username, display_name, created_at FROM admins ORDER BY display_name ASC')->fetchAll();
    }

    public static function findByUsername(string $username): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $username, string $password, string $displayName): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, display_name) VALUES (?, ?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $displayName]);
        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Db::pdo()->prepare('DELETE FROM admins WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function count(): int
    {
        return (int) Db::pdo()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    }
}
