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

    /** Updates username/display name, and the password only if a new one is given. */
    public static function update(int $id, string $username, string $displayName, ?string $password): void
    {
        $pdo = Db::pdo();
        if ($password !== null) {
            $stmt = $pdo->prepare('UPDATE admins SET username = ?, display_name = ?, password_hash = ? WHERE id = ?');
            $stmt->execute([$username, $displayName, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE admins SET username = ?, display_name = ? WHERE id = ?');
            $stmt->execute([$username, $displayName, $id]);
        }
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
